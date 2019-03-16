<?php

namespace ptv\schemachange;


use ptv\Database;

class Version4 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "alter table media_source add numTitles integer not null default 0";
        $database->execute($sql);
        $this->setCreation($database, 'media_source_num_titles');
    }

    function getVersionNumber(): int {
        return 4;
    }
}