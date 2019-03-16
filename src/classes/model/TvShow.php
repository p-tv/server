<?php

namespace ptv\model;


class TvShow implements ModelHydration {
    var $id;
    var $name;
    var $maxIndex = 1;

    var $genres;

    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->maxIndex = $arr['maxIndex'];
    }

    function getInsertSQL(): string {
        return 'insert into tvshow (name, maxIndex) 
                values (:name, :maxIndex)';
    }

    function getInsertParameters(): array {
        return [
            ':name' => $this->name,
            ':maxIndex' => $this->maxIndex
        ];

    }

    function getUpdateSQL(): string {
        return 'update tvshow set name = :name, maxIndex = :maxIndex where id = :id';
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }
}