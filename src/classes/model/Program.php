<?php

namespace ptv\model;


class Program implements ModelHydration {

    var $id;

    var $channelId;

    var $titleId;

    var $startTime;

    var $endTime;

    var $isFiller;

    var $fillerCutSeconds = 0;


    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->channelId = $arr['channelId'];
        $this->titleId = $arr['titleId'];
        $this->startTime = $arr['startTime'];
        $this->endTime = $arr['endTime'];
        $this->isFiller = $arr['isFiller'];
        $this->fillerCutSeconds = $arr['fillerCutSeconds'];
    }

    function getInsertSQL(): string {
        return "insert into program (channelId, titleId, startTime, endTime, isFiller, fillerCutSeconds) values 
                (:channelId, :titleId, :startTime, :endTime, :isFiller, :fillerCutSeconds)";
        // TODO: Implement getInsertSQL() method.
    }

    function getInsertParameters(): array {
        return [
            ':channelId' => $this->channelId,
            ':titleId' => $this->titleId,
            ':startTime' => $this->startTime,
            ':endTime' => $this->endTime,
            ':isFiller' => $this->isFiller,
            ':fillerCutSeconds' => $this->fillerCutSeconds
        ];
    }

    function getUpdateSQL(): string {
        return "update program set channelId = :channelId, titleId = :titleId, startTime = :startTime, endTime = :endTime,
                isFiller = :isFiller, fillerCutSeconds = :fillerCutSeconds where id = :id";
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }
}