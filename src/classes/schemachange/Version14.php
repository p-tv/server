<?php

namespace ptv\schemachange;


use ptv\Database;

class Version14 extends SchemaChange {

    function versionUp(Database $database) {
        $sql = "alter table tvshow add year integer not null default 0";
        $database->execute($sql);


        # Data migrate.
        $findMinEpIndexSql = 'select min(year) from title where showId = :showId';
        $showIds = $database->getColumn('select id from tvshow', []);
        $database->beginTrans();
        foreach ($showIds as $showId) {
            $params = [':showId' => $showId];
            $year = $database->getOne($findMinEpIndexSql, $params);
            if ($year == null) {
                $year = 0;
            }
            $params[':year'] = $year;
            $database->execute('update tvshow set year = :year where id = :showId', $params);
        }
        $database->commit();
        $this->setCreation($database, 'add_year_to_tvshow');


    }

    function getVersionNumber(): int {
        return 14;
    }
}