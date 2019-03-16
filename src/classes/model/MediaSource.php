<?php

namespace ptv\model;


class MediaSource implements ModelHydration {

    const NEVER_UPDATED_TIME = '1970-01-01 00:00:00';
    var $id;
    var $name;
    var $plexKey;
    var $tvSource = false;
    var $movieSource = false;
    var $fillerSource = false;
    var $lastUpdated = self::NEVER_UPDATED_TIME;
    var $numTitles = 0;

    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->plexKey = $arr['plexKey'];
        $this->tvSource = $arr['tvSource'];
        $this->movieSource = $arr['movieSource'];
        $this->fillerSource = $arr['fillerSource'];
        $this->lastUpdated = $arr['lastUpdated'];
        $this->numTitles = $arr['numTitles'];
    }

    function getInsertSQL(): string {
        return 'insert into media_source (name, plexKey, tvSource, movieSource, fillerSource, lastUpdated, numTitles) values (:name, :plexKey, :tvSource, :movieSource, :fillerSource, :lastUpdated, :numTitles)';
    }

    function getInsertParameters(): array {
        return [
            ':name' => $this->name,
            ':plexKey' => $this->plexKey,
            ':tvSource' => $this->tvSource,
            ':movieSource' => $this->movieSource,
            ':fillerSource' => $this->fillerSource,
            ':lastUpdated' => $this->lastUpdated,
            ':numTitles' => $this->numTitles
        ];
    }

    function hasNeverUpdated(): bool {
        if ($this->lastUpdated === self::NEVER_UPDATED_TIME) {
            return true;
        }
        return false;
    }

    /**
     * Gets the update SQL
     * @return string
     */
    function getUpdateSQL():string {
        return "update media_source set name = :name, plexKey = :plexKey, tvSource = :tvSource, movieSource = :movieSource, fillerSource = :fillerSource, lastUpdated = :lastUpdated, numTitles = :numTitles where id = :id";
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