<?php

namespace ptv\schemachange;


use ptv\Database;

class Version3 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table tvshow (
            id integer not null primary key autoincrement,
            name varchar not null,
            maxIndex integer not null,
            unique(name)
            )";
        $database->execute($sql);

        $sql = "create table if not exists title (
            id integer not null primary key autoincrement,
            name varchar not null,
            durationSeconds integer not null,
            mediaSourceId integer not null,
            plexKey varchar not null,
            thumbUrl varchar not null default '',
            summary varchar not null default '',
            tagLine varchar not null default '',
            rating varchar not null default '',
            contentRating varchar not null default '',
            year integer not null,
            enabled boolean not null,
            showId integer,
            foreign key (mediaSourceId) references media_source(id),
            foreign key (showId) references tvshow(id)            
        )";
        $database->execute($sql);
        $this->setCreation($database, 'create_tvshow_and_title_tables');
    }

    function getVersionNumber(): int {
        return 3;
    }
}