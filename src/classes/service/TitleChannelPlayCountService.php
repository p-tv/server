<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\TitleChannelPlayCount;

class TitleChannelPlayCountService {

    /**
     * @var Database
     */
    private $db;

    /**
     * GenreService constructor.
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function update(int $channelId, int $titleId, ?int $showId, ?int $playId) {
        if ($playId != null) {
            $sql = 'select * from title_channel_play_count where id = :id';
            /** @var TitleChannelPlayCount $titlePlayCount */
            $titlePlayCount = $this->db->getModel(TitleChannelPlayCount::class, $sql, [':id' => $playId]);
            $titlePlayCount->playCount = $titlePlayCount->playCount + 1;
            $this->db->execute($titlePlayCount->getUpdateSQL(), $titlePlayCount->getUpdateParameters());
        } else {
            $titlePlayCount = new TitleChannelPlayCount();
            $titlePlayCount->titleId = $titleId;
            $titlePlayCount->channelId = $channelId;
            $titlePlayCount->playCount = 1;
            $titlePlayCount->showId = $showId;
            $this->db->execute($titlePlayCount->getInsertSQL(), $titlePlayCount->getInsertParameters());
        }
    }


}