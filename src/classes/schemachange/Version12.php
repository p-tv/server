<?php

namespace ptv\schemachange;


use ptv\Database;

class Version12 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table show_channel_play_count (
            id integer not null primary key autoincrement,
            channelId integer not null,
            showId integer not null,
            episodeIndex integer not null,
            playCount varchar not null,
            foreign key (channelId) references channel(id),
            foreign key (showId) references tvshow(id)
            )";
        $database->execute($sql);
        $this->setCreation($database, 'create_show_channel_play_count');
    }

    function getVersionNumber(): int {
        return 12;
    }
}