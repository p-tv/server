<?php

namespace ptv\schemachange;


use ptv\Database;

class Version11 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table channel_restriction (
            id integer not null primary key autoincrement,
            channelId integer not null,
            enabled boolean not null,
            name varchar not null,
            value varchar not null,
            foreign key (channelId) references channel(id)
            )";
        $database->execute($sql);
        $this->setCreation($database, 'create_channel_restriction');
    }

    function getVersionNumber(): int {
        return 11;
    }
}