<?php

namespace ptv\model;


class Genre implements ModelHydration {
    var $id;
    var $name;

    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->name = $arr['name'];
    }

    function getInsertSQL(): string {
        return 'insert into genre (name) values (:name)';
    }

    function getInsertParameters(): array {
        return [':name' => $this->name];
    }

    function getUpdateSQL(): string {
        return 'update genre set name = :name where id = :id';
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }


}