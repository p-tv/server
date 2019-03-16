<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Genre;

class GenreService {

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
     * @return Genre[]
     */
    function getAll(): array {
        return $this->db->getModelArray(Genre::class, 'select * from genre');
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
     * @param string $name
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
            return $this->addGenre($name);
        }
        return $ret;
    }

    private function initCache() {
        if ($this->idMap === null) {
            $genres = $this->getAll();
            $this->idMap = [];
            $this->nameMap = [];
            foreach ($genres as $genre) {
                $this->idMap[$genre->id] = $genre->name;
                $this->nameMap[$genre->name] = $genre->id;
            }
        }
    }

    /**
     * Adds a genre
     * @param string $name
     * @return int
     */
    public function addGenre(string $name): int {
        $id = $this->getIdForName($name);
        if ($id !== null) {
            return $id;
        }
        $this->db->execute('insert into genre (name) values (:name)', [':name' => $name]);
        $id = $this->db->getLastInsertedID();
        $this->idMap[$id] = $name;
        $this->nameMap[$name] = $id;
        return $id;
    }

    /**
     * Gets the list of Genre object for a list of Ids
     * @param array $ids
     * @return Genre[]
     */
    public function getForIds(array $ids): array {
        $inString = $this->db->buildInString($ids);
        return $this->db->getModelArray(Genre::class, "select * from genre where id in $inString");
    }

}