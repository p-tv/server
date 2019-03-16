<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Channel;
use ptv\model\Program;

class ProgramService {

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

    public function getLastScheduledItem(Channel $channel): ?Program {
        $sql = 'select * from program where channelId = :channelId order by endTime desc limit 1';
        $params = [ ':channelId' => $channel->id];
        /** @var Program $ret */
        $ret = $this->db->getModel(Program::class, $sql, $params);
        return $ret;
    }

    public function addProgram(Program $program) {
        $this->db->execute($program->getInsertSQL(), $program->getInsertParameters());
        $program->id = $this->db->getLastInsertedID();
    }

    /**
     * Gets the list of progams currently playing
     * @param string $currentTime
     * @return Program[]
     */
    public function getCurrentPrograms(string $currentTime): array {
        $sql = 'select * from program where startTime <= :currentTime and endTime >= :currentTime';
        $items = $this->db->getModelArray(Program::class, $sql, [':currentTime' => $currentTime]);
        return $items;
    }


    /**
     * Gets the currently playing program for a channel ID
     * @param string $currentTime
     * @return Program|null
     */
    public function getCurrentProgramForChannel(int $channelId, string $currentTime): ?Program {
        $sql = 'select * from program where startTime <= :currentTime and endTime >= :currentTime and channelId = :channelId';
        $params = [':currentTime' => $currentTime, ':channelId' => $channelId];
        /** @var Program $item */
        $item = $this->db->getModel(Program::class, $sql, $params);
        return $item;
    }


    /**
     * Gets the next scheduled program after $startTime
     * @param int $channelId
     * @param string $startTime
     * @return Program|null
     */
    public function getUpNext(int $channelId, string $startTime): ?Program {
        $sql = 'select * from program where channelId = :channelId and startTime >= :startTime and isFiller = 0 order by startTime limit 1';
        $params = [':channelId' => $channelId, ':startTime' => $startTime];
        return $this->getProgramList($sql, $params);
    }

    public function getUpNextIncludingFillers(int $channelId, string $startTime) {
        $sql = 'select * from program where channelId = :channelId and startTime >= :startTime order by startTime limit 1';
        $params = [':channelId' => $channelId, ':startTime' => $startTime];
        return $this->getProgramList($sql, $params);
    }

    /**
     * Execute SQL / params to get a array of programs
     * @param string $sql
     * @param array $params
     * @return Program|null
     */
    private function getProgramList(string $sql, array $params): ?Program {
        /** @var Program $ret */
        $ret = $this->db->getModel(Program::class, $sql, $params);
        return $ret;
    }

}
