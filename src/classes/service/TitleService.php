<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\MediaSource;
use ptv\model\Title;
use ptv\PlexServer;

class TitleService {

    /**
     * @var PlexServer
     */
    private $plexServer;

    /**
     * @var Database
     */
    private $db;


    /**
     * @var TitleGenreService
     */
    private $titleGenreService;
    /**
     * @var TvShowService
     */
    private $tvShowService;
    /**
     * @var TvShowGenreService
     */
    private $tvShowGenreService;

    /**
     * MediaSourceService constructor.
     * @param PlexServer $plexServer
     * @param Database $db
     * @param TitleGenreService $titleGenreService
     * @param TvShowService $tvShowService
     * @param TvShowGenreService $tvShowGenreService
     */
    public function __construct(PlexServer $plexServer, Database $db, TitleGenreService $titleGenreService,
                                TvShowService $tvShowService, TvShowGenreService $tvShowGenreService) {
        $this->plexServer = $plexServer;
        $this->db = $db;
        $this->titleGenreService = $titleGenreService;
        $this->tvShowService = $tvShowService;
        $this->tvShowGenreService = $tvShowGenreService;
    }

    /**
     * Gets a title by its ID
     * @param int $id
     * @return Title|null
     */
    function getById(int $id): ?Title {
        $sql = 'select * from title where id = :id';
        /** @var Title $obj */
        $obj =  $this->db->getModel(Title::class, $sql, [':id' => $id]);
        return $obj;
    }

    /**
     * Gets a title by its plex key
     * @param int $plexKey Plex key to load by
     * @return Title|null
     */
    function getByPlexKey(int $plexKey): ?Title {
        $sql = 'select * from title where plexKey = :plexKey';
        /** @var Title $obj */
        $obj =  $this->db->getModel(Title::class, $sql, [':plexKey' => $plexKey]);
        return $obj;
    }


    /**
     * Gets all media sources in the system
     * @return array
     */
    function getAll(): array {
        return $this->db->getModelArray(TitleService::class, 'select * from title');
    }

    /**
     * Updates the list of titles for a media source from plex.
     * @param MediaSource $source
     * @param bool $isTVSource
     * @return int Number of titles in this media source
     */
    public function updateTitles(MediaSource $source, bool $isTVSource = false): int {
        $numTitles = 0;
        if ($isTVSource) {
            $titles = $this->plexServer->getTvTitles($source);
        } else {
            $titles = $this->plexServer->getTitles($source);
        }
        $foundIDs = [];
        $total = count($titles);
        $count = 0;
        $this->db->beginTrans();
        foreach ($titles as $title) {
            echo "Update $count/$total - " . $title->name . PHP_EOL;
            $count++;
            $existing = $this->getByPlexKey($title->plexKey);
            if ($existing == null) {
                $newId = $this->addNewTitle($title, $isTVSource);
                $title->id = $newId;
                $foundIDs[] = $newId;
	    } else {
		if ($isTVSource) {
	            $title->showId = $this->tvShowService->getIdForName($title->showName, true);
	        }
                $foundIDs[] = $existing->id;
                $title->id = $existing->id;
            }
            # Update genres
	    if ($isTVSource) {
                $this->tvShowGenreService->updateGenresForTvShow($title->showId, $title->genres);
            } else {
                $this->titleGenreService->updateGenresForTitle($title, $title->genres);
            }

            $numTitles++;
        }
        $this->disableMissingTitles($source, $foundIDs);
        $this->db->commit();
        if ($isTVSource) {
            $this->updateShowMaxIndexes();
        }
        return $numTitles;
    }

    /**
     * Gets a random title that is less then maximum seconds from the lsit of media sources
     * Will find a title over that if it is too large and requires con
     * @param MediaSource[] $sources
     * @param int $maxSeconds
     * @param array $excludeTitleIds
     * @return Title
     */
    public function getRandomTitleWithMaxLength(array $sources, int $maxSeconds, array $excludeTitleIds): ?Title {
        return $this->getRandomTitleImpl($sources, $maxSeconds, $excludeTitleIds);
    }

    /**
     * Gets a random title from the list of media sources
     * Will find a title over that if it is too large and requires con
     * @param MediaSource[] $sources
     * @param array $excludeTitleIds
     * @return Title
     */
    public function getRandomTitle(array $sources, array $excludeTitleIds): ?Title {
        return $this->getRandomTitleImpl($sources, 0, $excludeTitleIds);
    }

    /**
     * Gets a random title that is less then maximum seconds from the lsit of media sources
     * Will find a title over that if it is too large and requires con
     * @param MediaSource[] $sources
     * @param int $maxSeconds
     * @param array $excludeTitleIds
     * @return Title
     */
    private function getRandomTitleImpl(array $sources, int $maxSeconds = 0, array $excludeTitleIds = []): ?Title {
        $sourceIDs = [];
        $params = [];
        foreach ($sources as $source) {
            $sourceIDs[] = $source->id;
        }
        $sourcesInSQL = $this->db->buildInString($sourceIDs);
        $excludeSQL = '';
        if (count($excludeTitleIds) > 0) {
            $excludeSQL = ' and id not in (' . $this->db->buildInString($excludeTitleIds) . ') ';
        }
        $maxSecondsSQL = '';
        if ($maxSeconds > 0 ) {
            $params = [':maxSeconds' => $maxSeconds];
            $maxSecondsSQL = ' and durationSeconds < :maxSeconds ';
        }



        $sql = "select * from title where mediaSourceId in $sourcesInSQL  $maxSecondsSQL $excludeSQL order by random() limit 1";
        /** @var Title $title */
        $title = $this->db->getModel(Title::class, $sql, $params);
        return $title;
    }


    /**
     * Adds a new title to the database
     *
     * @param Title $title
     * @param bool $isTvSource
     * @return int The inserted ID
     */
    private function addNewTitle(Title $title, bool $isTvSource): int {
        if ($isTvSource) {
		$title->showId = $this->tvShowService->getIdForName($title->showName, true);
        }
        $this->db->execute($title->getInsertSQL(), $title->getInsertParameters());
        return $this->db->getLastInsertedID();
    }

    /**
     * Sets all titles as disabled not found in the list of title IDs
     * @param MediaSource $source
     * @param array $titleIDs
     */
    private function disableMissingTitles(MediaSource $source, array $titleIDs) {
        $escapedIDs = $this->db->buildInString($titleIDs);
        $sql = "update title set enabled = 0 where mediaSourceId = :mediaSourceId and id not in $escapedIDs";
        $this->db->execute($sql, [':mediaSourceId' => $source->id]);
    }

    /**
     * Gets the maximum episode index for a show
     * @param int $showId
     * @return int
     */
    private function getMaxEpisodeIndexForShowId(int $showId): int {
        $ret = $this->db->getOne('select max(episodeIndex) from title where showId = :showId', [':showId' => $showId]);
        if ($ret === null) {
            return 0;
        }
        return $ret;
    }


    /**
     * Gets the first episode for a show
     * @param int $showId
     * @return Title
     */
    public function getFirstEpisodeForShow(int $showId): Title {
        $sql = 'select min(episodeIndex) from title where showId = :showId';
        $params = [':showId' => $showId];
        $episodeIndex = $this->db->getOne($sql, $params);
        return $this->getShowEpisode($showId, $episodeIndex);
    }

    /**
     * Gets the title of the next episode to watch. If we have reached the end, start at the beginning
     * @param int $showId Show being watched
     * @param int $lastEpisodeIndex Last watched episode index
     * @return Title
     */
    public function getNextEpisodeForShow(int $showId, int $lastEpisodeIndex): Title {
        $sql = 'select min(episodeIndex) from title where showId = :showId and episodeIndex > :episodeIndex';
        $params = [':showId' => $showId, ':episodeIndex' => $lastEpisodeIndex];
        $wantedIndex = $this->db->getOne($sql, $params);

        if ($wantedIndex == null) {
            return $this->getFirstEpisodeForShow($showId);
        }
        return $this->getShowEpisode($showId, $wantedIndex);
    }

    /**
     * Gets a title by show ID and episode index
     * @param int $showId Show ID
     * @param int $episodeIndex Episode index
     * @return Title
     */
    public function getShowEpisode(int $showId, int $episodeIndex): Title {
        $params = [':showId' => $showId, ':episodeIndex' => $episodeIndex];
        $sql = 'select * from title where episodeIndex = :episodeIndex and showId = :showId limit 1';
        /** @var Title $ret */
        $ret = $this->db->getModel(Title::class, $sql, $params);
        return $ret;

    }

    /**
     * Update all show maximum indexes.
     */
    public function updateShowMaxIndexes() {
        $shows = $this->tvShowService->getAll();
        $count = 1;
        $max = count($shows);
        $this->db->beginTrans();
        foreach ($shows as $show) {
            $show->maxIndex = $this->getMaxEpisodeIndexForShowId($show->id);
            $this->tvShowService->updateShow($show);
            echo "$count / $max - " . $show->name . ' - count: ' .$show->maxIndex . PHP_EOL;
            $count++;
        }
        $this->db->commit();
    }

}
