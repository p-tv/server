<?php

namespace ptv\schemachange;


use ptv\Database;

class Version10 extends SchemaChange {


    function versionUp(Database $database) {
        $sql = "alter table channel add padWithFiller boolean not null default false";
        $database->execute($sql);

        $this->setCreation($database, 'add_use_filler_to_channel');
    }

    function getVersionNumber(): int {
        return 10;
    }
}