<?php

namespace ptv\model;


class ShowChannelPlayCount implements ModelHydration {
    var $titleId = null;
    var $showId = null;
    var $episodeIndex = null;
    var $channelId;
    var $playCount = 0;
    var $id;

    function fromArray(array $arr) {
        $this->channelId = $arr['channelId'];
        $this->showId = $arr['showId'];
        $this->episodeIndex = $arr['episodeIndex'];
        $this->id = $arr['id'];
        $this->playCount = $arr['playCount'];

    }

    function getInsertSQL(): string {
        return 'insert into show_channel_play_count (channelId, showId, episodeIndex, playCount)
                values (:channelId, :showId, :episodeIndex, :playCount)';
    }

    function getInsertParameters(): array {
        return [
            ':channelId' => $this->channelId,
            ':playCount' => $this->playCount,
            ':showId' => $this->showId,
            ':episodeIndex' => $this->episodeIndex];
    }

    function getUpdateSQL(): string {
        return 'update show_channel_play_count set channelId = :channelId, playCount = :playCount, showId = :showId, 
                                   episodeIndex = :episodeIndex where id = :id';
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }


}