<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Channel;
use ptv\model\Genre;
use ptv\model\MediaSource;
use ptv\model\ShowChannelPlayCount;
use ptv\model\TitleChannelPlayCount;
use ptv\PlexServer;

class ShowChannelPlayCountService {

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


    public function update(int $channelId, ?int $playId, int $showId, int $episodeIndex, int $playCount) {
        if ($playId != null) {
            $sql = 'select * from show_channel_play_count where id = :id';
            /** @var ShowChannelPlayCount $showCount */
            $showCount = $this->db->getModel(ShowChannelPlayCount::class, $sql, [':id' => $playId]);
            $showCount->playCount = $playCount;
            $showCount->episodeIndex = $episodeIndex;
            $this->db->execute($showCount->getUpdateSQL(), $showCount->getUpdateParameters());
        } else {
            $showCount = new ShowChannelPlayCount();
            $showCount->showId = $showId;
            $showCount->channelId = $channelId;
            $showCount->playCount = $playCount;
            $showCount->episodeIndex = $episodeIndex;
            $this->db->execute($showCount->getInsertSQL(), $showCount->getInsertParameters());
        }
    }
}