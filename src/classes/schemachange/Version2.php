<?php

namespace ptv\schemachange;


use ptv\Database;

class Version2 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "alter table media_source add lastUpdated datetime default '1970-01-01 00:00:00'";
        $database->execute($sql);
        $this->setCreation($database, 'media_source_add_last_updated');
    }

    function getVersionNumber(): int {
        return 2;
    }
}