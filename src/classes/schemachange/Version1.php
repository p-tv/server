<?php

namespace ptv\schemachange;


use ptv\Database;

class Version1 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = 'create table if not exists media_source (
              id integer primary key autoincrement, 
              name varchar not null,
              plexKey int not null,
              tvSource boolean not null, 
              movieSource boolean not null, 
              fillerSource boolean not null,
              unique (name))';
        $database->execute($sql);
        $this->setCreation($database, 'media_source_create');
    }

    function getVersionNumber(): int {
        return 1;
    }
}