<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Genre;

class TvShowGenreService {

    /**
     * @var Database
     */
    private $db;
    /**
     * @var GenreService
     */
    private $genreService;

    /**
     * GenreService constructor.
     * @param Database $db
     * @param GenreService $genreService
     */
    public function __construct(Database $db, GenreService $genreService) {
        $this->db = $db;
        $this->genreService = $genreService;
    }


    /**
     * @param int $showId
     * @return Genre[]
     */
    public function getGenresForShowId(int $showId) {
        $ids = $this->getIdsForShowId($showId);
        return $this->genreService->getForIds($ids);
    }

    public function updateGenresForTvShow(int $showId, ?array $genreList) {
        if ($genreList == null) {
            $genreList = [];
        }
        $existingGenreIds = $this->getIdsForShowId($showId);
        foreach ($genreList as $genreName) {
            $id = $this->genreService->getIdForName($genreName, true);
            $existingArrayIndex = array_search($id, $existingGenreIds);
            if ($existingArrayIndex !== false) {
                # Remove it still there
                array_splice($existingGenreIds, $existingArrayIndex, 1);
            } else {
                # Add it as new
                $this->db->execute('insert into tvshow_genre (showId, genreId) VALUES (:showId, :genreId)', [':showId' => $showId, ':genreId' => $id]);
            }
        }
        # Clear out any genres that are still present.
        if (count($existingGenreIds) > 0) {
            $inString = $this->db->buildInString($existingGenreIds);
            $this->db->execute("delete from tvshow_genre where showId = :id and genreId in $inString", [':id' => $showId]);
        }
    }

    private function getIdsForShowId(int $showId) {
        return $this->db->getColumn('select genreId from tvshow_genre where showId = :showId', [':showId ' => $showId]);
    }
}