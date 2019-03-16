<?php

namespace ptv\schemachange;


use ptv\Database;

class Version8 extends SchemaChange {


    function versionUp(Database $database) {
        $sql = "create table program (
            id integer not null primary key autoincrement,
            channelId integer not null,
            titleId integer not null,
            startTime datetime not null,
            endTime datetime not null,
            isFiller boolean not null default false,
            fillerCutSeconds integer not null default 0,
            foreign key (channelId) references channel(id),
            foreign key (titleId) references title(id)
            )";
        $database->execute($sql);

        $this->setCreation($database, 'create_program_table');
    }

    function getVersionNumber(): int {
        return 8;
    }
}