<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Channel;

class ChannelService {

    /**
     * @var Database
     */
    private $db;

    /**
     * @var Channel[] Keyed by ID
     */
    private $cache;

    /**
     * ChannelService constructor.
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->cache = [];
    }


    /**
     * Gets all channels in the system
     * @return Channel[]
     */
    function getAll(): array {
        return $this->db->getModelArray(Channel::class, 'select * from channel order by name');
    }

    /**
     * Gets all channels in the system
     * @return Channel[]
     */
    function getEnabledChannels(): array {
        return $this->db->getModelArray(Channel::class, 'select * from channel where enabled = 1 order by name');
    }


    /**
     * Gets the channel by ID
     * @param int $id
     * @param bool $useCache
     * @return Channel|null
     */
    function getById(int $id, bool $useCache = false): ?Channel {
        if ($useCache == true) {
            if (array_key_exists($id, $this->cache)) {
                return $this->cache[$id];
            }
        }

        /** @var Channel $ret */
        $ret =  $this->db->getModel(Channel::class, 'select * from channel where id = :id', [':id' => $id]);
        if ($ret != null) {
            $this->cache[$id] = $ret;

        }
        return $ret;
    }



}