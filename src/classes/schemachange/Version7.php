<?php

namespace ptv\schemachange;


use ptv\Database;

class Version7 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table channel (
            id integer not null primary key autoincrement,
            name varchar not null,
            enabled boolean not null default true,
            description varchar not null default '',
            useTv boolean not null default true,
            useMovies boolean not null default true,
            unique (name)
            )";
        $database->execute($sql);

        $this->setCreation($database, 'create_channel_table');
    }

    function getVersionNumber(): int {
        return 7;
    }
}