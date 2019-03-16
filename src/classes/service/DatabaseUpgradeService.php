<?php

namespace ptv\service;


use ptv\Database;
use ptv\schemachange\SchemaChange;

class DatabaseUpgradeService {

    const VERSION_TABLE = 'version';

    const MAX_VERSION = 14;

    private $db;

    /**
     * DatabaseUpgradeService constructor.
     */
    public function __construct() {
    }

    public function checkUpgrades(int $version) {


        while ($version <= self::MAX_VERSION) {
            $this->upgradeToVersion($version);
            $version++;
        }
    }

    private function upgradeToVersion(int $version) {
        $className = "ptv\schemachange\Version$version";
        /** @var SchemaChange $clz */
        $clz = new $className();
        $clz->versionUp($this->db);
    }

    /**
     * Upgrades the database schemas
     * @param Database $db
     */
    public function upgradeDB(Database $db) {
        $this->db = $db;
        $versionLevel = 1;
        if (!$this->db->tableExist(self::VERSION_TABLE)) {
            $this->initVersionTable();
        } else {
            $versionLevel = $this->db->getOne('select max(id) from version', []) + 1;
            if ($versionLevel == null) {
                $versionLevel = 1;
            }
        }
        $this->checkUpgrades($versionLevel);
    }

    /**
     * Inits the version table
     */
    private function initVersionTable() {
        $this->db->execute('create table "' . self::VERSION_TABLE . '" (
        id integer primary key not null,
        name varchar not null,
        creationTime datetime not null
        )');
    }
}