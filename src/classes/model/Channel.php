<?php

namespace ptv\model;


class Channel implements ModelHydration {

    var $id;
    var $name;
    var $enabled = true;
    var $description = '';
    var $useTv = true;
    var $useMovies = true;
    var $padWithFiller = false;

    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->enabled = $arr['enabled'];
        $this->description = $arr['description'];
        $this->useTv = $arr['useTv'];
        $this->useMovies = $arr['useMovies'];
        $this->padWithFiller = $arr['padWithFiller'];
    }

    function getInsertSQL(): string {
        return 'insert into channel (name, enabled, description, useTv, useMovies, padWithFiller) values (:name, :enabled, :description, :useTv, :useMovies, :padWithFiller)';
    }

    function getInsertParameters(): array {
        return [
            ':name' => $this->name,
            ':enabled' => $this->enabled,
            ':description' => $this->description,
            ":useTv" => $this->useTv,
            ':useMovies' => $this->useMovies,
            ':padWithFiller' => $this->padWithFiller
        ];
    }

    /**
     * Gets the update SQL
     * @return string
     */
    function getUpdateSQL():string {
        return "update channel set name = :name, enabled = :enabled, description = :description, useTv = :useTv, useMovies = :useMoviesv, padWithFiller = :padWithFiller where id = :id";
    }

    /**
     * Array of parameters mapping to the updateSQL
     * @return array
     */
    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }


}