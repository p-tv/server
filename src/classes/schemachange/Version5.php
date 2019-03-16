<?php

namespace ptv\schemachange;


use ptv\Database;

class Version5 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table if not exists genre (
            id integer not null primary key autoincrement,
            name varchar not null,
            unique (name)
        )";
        $database->execute($sql);
        $sql = "create table if not exists title_genre (
            titleId integer not null,
            genreId integer not null,
            foreign key (genreId) references genre(id),
            foreign key (titleId) references title(id)
        )";
        $database->execute($sql);

        $this->setCreation($database, 'create_genre_tables');
    }

    function getVersionNumber(): int {
        return 5;
    }
}