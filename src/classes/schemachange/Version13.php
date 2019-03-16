<?php

namespace ptv\schemachange;


use ptv\Database;

class Version13 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "create table tvshow_genre (
            showId integer not null,
            genreId integer not null,
            foreign key (genreId) references genre(id),
            foreign key (showId) references tvshow(id)
            )";
        $database->execute($sql);


        # Data migrate.
        $sql = 'select distinct g.genreId from title_genre g, title t where t.id = g.titleId and t.showId = :showId';
        $showIds = $database->getColumn('select distinct showId from title where showId is not null', []);
        $database->beginTrans();
        foreach ($showIds as $showId) {
            $params = [':showId' => $showId];
            $genreIds = $database->getColumn($sql, $params);
            foreach ($genreIds as $genreId) {
                $params[':genreId'] = $genreId;
                $database->execute('insert into tvshow_genre (showId, genreId) values (:showId, :genreId)', $params);
            }
        }
        $database->commit();
        $this->setCreation($database, 'create_show_genre_id');


    }

    function getVersionNumber(): int {
        return 13;
    }
}