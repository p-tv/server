<?php


namespace ptv\service;


use ptv\Database;
use ptv\model\MediaSource;
use ptv\PlexServer;

class PTVService {

    private $db;

    private $plexServer;

    private $titleService;
    private $mediaSourceService;

    /**
     * PTVService constructor.
     * @param Database $db
     * @param PlexServer $plexServer
     * @param TitleService $titleService
     */
    public function __construct(Database $db, PlexServer $plexServer, TitleService $titleService, MediaSourceService $mediaSourceService) {
        $this->db = $db;
        $this->plexServer = $plexServer;
        $this->titleService = $titleService;
        $this->mediaSourceService = $mediaSourceService;
    }

    public function refreshMediaSources() {
        $this->mediaSourceService->updateMediaSources();
    }

    public function refreshSources() {
        $this->refreshSourcesArray($this->mediaSourceService->getAllFillerSources());
        $this->refreshSourcesArray($this->mediaSourceService->getAllMovieSources());
        $this->refreshSourcesArray($this->mediaSourceService->getAllTVSources(), true);
    }

    /**
     * Updates each of the media sources
     * @param MediaSource[] $mediaSources
     * @param bool $isTVSource Is this a TV source
     */
    private function refreshSourcesArray(array $mediaSources, bool $isTVSource = false) {
        foreach ($mediaSources as $source) {
            $numTitles = $this->titleService->updateTitles($source, $isTVSource);
            $this->mediaSourceService->updateMediaLastUpdated($source, $numTitles);
        }
    }




}