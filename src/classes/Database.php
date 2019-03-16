<?php


namespace ptv;


use ptv\model\ModelHydration;
use ptv\service\DatabaseUpgradeService;
use SQLite3;

class Database {

    const DB_FILE = "ptv.sqlite";

    private $db;

    /**
     * Database constructor.
     *
     * @param DatabaseUpgradeService $databaseUpgradeService
     * @throws \Exception
     */
    public function __construct(DatabaseUpgradeService $databaseUpgradeService) {
        $dbFile = __DIR__ . '/../../' . self::DB_FILE;
        $this->db = new SQLite3($dbFile, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        if (!$this->db) {
            throw new \Exception('Unable to load/create database');
        }
        $this->db->enableExceptions(true);
        $databaseUpgradeService->upgradeDB($this);
    }


    /**
     * Checks if a table exists in the database.
     *
     * @param string $tableName Table to check
     * @return bool
     */
    public function tableExist(string $tableName): bool {
        $result = $this->getOne("SELECT name FROM sqlite_master WHERE type='table' AND name=:table", [ ':table' => $tableName]);
        if ($result === $tableName) {
            return true;
        }
        return false;
    }

    /**
     * Gets a single item from the the database from the first row.
     * @param string $sql SQL to execute
     * @param array $params Map of parameters
     * @return |null The value or null if no results
     */
    public function getOne(string $sql, array $params) {
        $query = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $query->bindValue($key, $val);
        }
        $result = $query->execute();
        $row = $result->fetchArray(SQLITE3_NUM);
        if (is_array($row) === false || count($row) == 0) {
            $result->finalize();
            return null;
        }
        $result->finalize();
        return $row[0];
    }

    private function prepareAndRunQuery(string $sql, ?array $params) {
        $query = $this->db->prepare($sql);
        if ($params != null) {
            foreach ($params as $key => $val) {
                $query->bindValue($key, $val);
            }
        }
        return $query->execute();
    }

    public function execute(string $sql, ?array $params = null) {
        $result = $this->prepareAndRunQuery($sql, $params);
        $result->finalize();
    }

    public function getModel(string $clazz, string $sql, array $params = null, $mode = SQLITE3_ASSOC) {
        $result = $this->prepareAndRunQuery($sql, $params);
        /** @var ModelHydration $object */
        if ($result == null) {
            $result->finalize();
            return null;
        }
        $rowArray = $result->fetchArray(SQLITE3_ASSOC);
        if ($rowArray === false) {
            $result->finalize();
            return null;
        }
        $object = new $clazz();
        $object->fromArray($rowArray);
        $result->finalize();
        return $object;
    }

    public function getModelArray(string $clazz, string $sql, array $params = null): array {
        $result = $this->prepareAndRunQuery($sql, $params);
        /** @var ModelHydration $object */
        if ($result == null) {
            $result->finalize();
            return [];
        }
        $ret = [];
        $rowArray = $result->fetchArray(SQLITE3_ASSOC);
        while ($rowArray !== false) {
            $object = new $clazz();
            $object->fromArray($rowArray);
            $ret[]  = $object;
            $rowArray = $result->fetchArray(SQLITE3_ASSOC);
        }
        return $ret;
    }

    public function beginTrans() {
        $this->db->exec('BEGIN TRANSACTION');
    }

    public function commit() {
        $this->db->exec('COMMIT');
    }


    public function rollback() {
        $this->db->exec('ROLLBACK');
    }

    public function getLastInsertedID() {
        return $this->db->lastInsertRowID();
    }

    /**
     * Builds an escaped in string including surround parenthesis.
     * @param array $values
     * @return string
     */
    public function buildInString(array $values): string {
        $ret = '(';
        $count = 0;
        $max = count($values);
        foreach ($values as $val) {
            $ret .= "'" . SQLite3::escapeString($val) . "'";
            if ($count < $max) {
                $val .= ',';
            }
            $count++;
        }
        $ret .= ')';
        return $ret;
    }

    public function escapeString(string $value): string {
        if ($value === null) {
            return 'null';
        }
        return SQLite3::escapeString($value);
    }

    public function getColumn(string $sql, array $params): array {
        $result = $this->prepareAndRunQuery($sql, $params);
        if ($result == null) {
            $result->finalize();
            return [];
        }
        $ret = [];
        $rowArray = $result->fetchArray(SQLITE3_NUM);
        while ($rowArray !== false) {
            $ret[]  = $rowArray[0];
            $rowArray = $result->fetchArray(SQLITE3_NUM);
        }
        $result->finalize();
        return $ret;
    }

    public function getRow(string $sql, array $params = null): ?array {
        $result = $this->prepareAndRunQuery($sql, $params);
        if ($result == null) {
            $row = null;
        } else {
            $row = $result->fetchArray(SQLITE3_ASSOC);
            if ($row == null) {
                $row = null;
            }
        }
        $result->finalize();
        return $row;
    }

}