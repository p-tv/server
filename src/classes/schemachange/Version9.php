<?php

namespace ptv\schemachange;


use ptv\Database;

class Version9 extends SchemaChange {


    function versionUp(Database $database) {
        $sql = "create table title_channel_play_count (
            id integer not null primary key autoincrement,
            channelId integer not null,
            titleId integer,
            showId integer,
            episodeIndex integer,
            playCount integer not null,
            foreign key (channelId) references channel(id),
            foreign key (showId) references tvshow(id),
            foreign key (titleId) references title(id)
            )";
        $database->execute($sql);

        $this->setCreation($database, 'create_title_channel_play_count_table');
    }

    function getVersionNumber(): int {
        return 9;
    }
}