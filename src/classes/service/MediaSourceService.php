<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\MediaSource;
use ptv\PlexServer;

class MediaSourceService {

    /**
     * @var PlexServer
     */
    private $plexServer;

    /**
     * @var Database
     */
    private $db;

    /**
     * MediaSourceService constructor.
     * @param PlexServer $plexServer
     * @param Database $db
     */
    public function __construct(PlexServer $plexServer, Database $db) {
        $this->plexServer = $plexServer;
        $this->db = $db;
    }

    function getByName(string $name): ?MediaSource {
        $sql = 'select * from media_source where name = :name';
        /** @var MediaSource $obj */
        $obj =  $this->db->getModel(MediaSource::class, $sql, [':name' => $name]);
        return $obj;
    }

    /**
     * Gets all media sources in the system
     * @return array
     */
    function getAll(): array {
        return $this->db->getModelArray(MediaSource::class, 'select * from media_source');
    }

    /**
     * Returns a list of media sources that are filler sources
     * @return MediaSource[]
     */
    function getAllFillerSources(): array {
        $sql = 'select * from media_source where fillerSource = true';
        return $this->db->getModelArray(MediaSource::class, $sql);
    }

    /**
     * Returns a list of media sources that are movie sources
     * @return MediaSource[]
     */
    function getAllMovieSources(): array {
        $sql = 'select * from media_source where movieSource = true';
        return $this->db->getModelArray(MediaSource::class, $sql);
    }

    /**
     * Returns a list of media sources that are tv sources
     * @return MediaSource[]
     */
    function getAllTVSources(): array {
        $sql = 'select * from media_source where tvSource = true';
        return $this->db->getModelArray(MediaSource::class, $sql);
    }


    /**
     * Updates the list of media sources from plex.
     */
    public function updateMediaSources() {
        $sections = $this->plexServer->getSections();
        $this->db->beginTrans();
        foreach ($sections as $section) {
            $existing = $this->getByName($section->name);
            if ($existing == null) {
                $this->addNewMediaSource($section);
            }
        }
        $this->db->commit();
    }

    public function updateMediaLastUpdated(MediaSource $source, int $numTitles) {
        $source->lastUpdated = date('Y-m-d H:i:s');
        $source->numTitles = $numTitles;
        $this->db->execute($source->getUpdateSQL(), $source->getUpdateParameters());
    }

    /**
     * Adds a new media source to the database
     * @param MediaSource $source
     */
    private function addNewMediaSource(MediaSource $source) {
        $this->db->execute($source->getInsertSQL('media_source'), $source->getInsertParameters());
    }
}