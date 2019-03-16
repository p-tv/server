<?php

namespace ptv\schemachange;


use ptv\Database;

class Version6 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "alter table title add showName varchar not null default ''";
        $database->execute($sql);

        $sql = "alter table title add episodeIndex integer not null default 0";
        $database->execute($sql);

        $this->setCreation($database, 'add_show_name_and_episode_index');
    }

    function getVersionNumber(): int {
        return 6;
    }
}