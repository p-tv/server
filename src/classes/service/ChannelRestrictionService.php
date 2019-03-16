<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Channel;
use ptv\model\ChannelRestriction;

class ChannelRestrictionService {

    /**
     * @var Database
     */
    private $db;

    /**
     * ChannelService constructor.
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Gets all channels in the system
     * @return ChannelRestriction[]
     */
    function getAll(): array {
        return $this->db->getModelArray(ChannelRestriction::class, 'select * from channel_restriction order by name');
    }

    /**
     * Gets all channel restrictions in the system that are enabled
     * @return ChannelRestriction[]
     */
    function getEnabledChannels(): array {
        return $this->db->getModelArray(ChannelRestriction::class, 'select * from channel_restriction where enabled = true order by name');
    }

    /**
     * Gets all channels in the system for a channel that are enabled
     * @param Channel $channel Channel to receive for
     * @return ChannelRestriction[]
     */
    function getAllEnabledForChannel(Channel $channel): array {
        return $this->getAllEnabledForChannelID($channel->id);
    }

    /**
     * Gets all channels in the system for a channel that are enabled
     * @param int $channelId
     * @return ChannelRestriction[]
     */
    function getAllEnabledForChannelID(int $channelId): array {
        $sql = 'select * from channel_restriction where enabled = 1 and channelId = :channelId order by name';
        $params = ['channelId' => $channelId];
        return $this->db->getModelArray(ChannelRestriction::class, $sql, $params);
    }

}