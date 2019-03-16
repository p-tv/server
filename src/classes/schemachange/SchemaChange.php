<?php

namespace ptv\schemachange;


use ptv\Database;
use ptv\Utils;

abstract class SchemaChange {


    abstract function versionUp(Database $database);

    abstract function getVersionNumber(): int;

    protected function setCreation(Database $db, string $name) {
        $now = Utils::GetCurrentDateTimeString();
        $id = $this->getVersionNumber();
        $sql = "insert into version (id, name, creationTime) values (:id, :name, :now)";
        $db->execute($sql, [':id' => $id, ':name' => $name, ':now' => $now ]);
    }
}