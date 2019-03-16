<?php

namespace ptv\model;


class TitleChannelPlayCount implements ModelHydration {
    var $titleId = null;
    var $showId = null;
    var $episodeIndex = null;
    var $channelId;
    var $playCount = 0;
    var $id;

    function fromArray(array $arr) {
        $this->titleId = $arr['titleId'];
        $this->channelId = $arr['channelId'];
        $this->showId = $arr['showId'];
        $this->episodeIndex = $arr['episodeIndex'];
        $this->id = $arr['id'];
        $this->playCount = $arr['playCount'];

    }

    function getInsertSQL(): string {
        return 'insert into title_channel_play_count (channelId, titleId, playCount, showId, episodeIndex) 
                values (:channelId, :titleId, :playCount, :showId, :episodeIndex)';
    }

    function getInsertParameters(): array {
        return [
            ':titleId' => $this->titleId,
            ':channelId' => $this->channelId,
            ':playCount' => $this->playCount,
            ':showId' => $this->showId,
            ':episodeIndex' => $this->episodeIndex];
    }

    function getUpdateSQL(): string {
        return 'update title_channel_play_count set channelId = :channelId, titleId = :titleId, 
                playCount = :playCount, showId = :showId, episodeIndex = :episodeIndex where id = :id';
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }


}