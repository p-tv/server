<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\TvShow;

class TvShowService {

    /**
     * @var Database
     */
    private $db;

    private $idMap;

    private $nameMap;

    /**
     * GenreService constructor.
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->idMap = null;
        $this->nameMap = null;
    }


    /**
     * Gets all media sources in the system
     * @return TvShow[]
     */
    function getAll(): array {
        return $this->db->getModelArray(TvShow::class, 'select * from tvshow');
    }

    /**
     * Gets the genre name for an ID if known
     * @param int $id
     * @return string|null
     */
    function getNameForId(int $id): ?string {
        $this->initCache();
        return $this->idMap[$id];
    }

    /**
     * Gets the genre id for name if known
     * @param string $name Name to add
     * @param bool $addIfMissing Add a new genre if missing
     * @return int|null
     */
    function getIdForName(string $name, bool $addIfMissing = false): ?int {
        $this->initCache();
        $ret = null;
        if (isset($this->nameMap[$name])) {
            $ret = $this->nameMap[$name];
        }
        if ($ret === null && $addIfMissing === true) {
            return $this->addTvShow($name);
        }
        return $ret;
    }

    private function initCache() {
        if ($this->idMap === null) {
            $shows = $this->getAll();
            $this->idMap = [];
            $this->nameMap = [];
            foreach ($shows as $show) {
                $this->idMap[$show->id] = $show->name;
                $this->nameMap[$show->name] = $show->id;
            }
        }
    }

    /**
     * Adds a tv show
     * @param string $name
     * @param int $maxEpisodes
     * @return int
     */
    public function addTvShow(string $name, int $maxEpisodes = 1): int {
        $id = $this->getIdForName($name);
        if ($id !== null) {
            return $id;
        }
        $this->db->execute('insert into tvshow (name, maxIndex) values (:name, :maxIndex)', [':name' => $name, ':maxIndex' => $maxEpisodes]);
        $id = $this->db->getLastInsertedID();
        $this->idMap[$id] = $name;
        $this->nameMap[$name] = $id;
        return $id;
    }

    public function updateShow(TvShow $show) {
        $this->db->execute($show->getUpdateSQL(), $show->getUpdateParameters());
    }


}