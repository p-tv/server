<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\api\CurrentlyPlayingResponse;
use ptv\model\api\NextProgramsForChannelResponse;
use ptv\model\api\PlayingProgram;
use ptv\PlexServer;
use ptv\Utils;

class APIService {

    /**
     * @var Database
     */
    private $db;

    /**
     * @var ChannelService
     */
    private $channelService;

    /**
     * @var ProgramService
     */
    private $programService;

    /**
     * @var TitleService
     */
    private $titleService;

    /**
     * @var PlexServer
     */
    private $plexServer;

    /**
     * ChannelService constructor.
     * @param Database $db
     * @param ChannelService $channelService
     * @param ProgramService $programService
     * @param TitleService $titleService
     * @param PlexServer $plexServer
     */
    public function __construct(Database $db, ChannelService $channelService, ProgramService $programService,
                                TitleService $titleService, PlexServer $plexServer) {
        $this->db = $db;
        $this->channelService = $channelService;
        $this->programService = $programService;
        $this->titleService = $titleService;
        $this->plexServer = $plexServer;
    }

    public function getNextPrograms(int $channelId, ?string $maxTime = null): NextProgramsForChannelResponse {
        $response = new NextProgramsForChannelResponse();

        if ($maxTime == null) {
            $maxTimeUnix = time() + 24 * 60 * 60;
        } else {
            $maxTimeUnix = strtotime($maxTime);
        }

        $ret = [];
        $currentTimeUnix = time();
        $response->currentTimeUnix = $currentTimeUnix;
        $response->currentTime = Utils::UnixTimeToDateTime($currentTimeUnix);

        # Add first program
        $currentProgram = $this->programService->getCurrentProgramForChannel($channelId, $response->currentTime);
        $ret[] = $this->populateProgram($currentProgram, $currentTimeUnix);
        $currentTimeUnix = strtotime($currentProgram->endTime);

        # Add programs after
        while ($currentTimeUnix < $maxTimeUnix) {
            $currentTime = Utils::UnixTimeToDateTime($currentTimeUnix);
            $program = $this->programService->getUpNextIncludingFillers($channelId, $currentTime);
            if ($program != null) {
                $playingProgram = $this->populateProgram($program, $currentTimeUnix);
                $ret[] = $playingProgram;
                $currentTimeUnix = strtotime($program->endTime);
            } else {
                # Break out
                $currentTimeUnix = $maxTimeUnix;
            }
        }

        $response->programs = $ret;
        return $response;
    }

    public function getWhatsOnNow(): CurrentlyPlayingResponse {
        $currentUnixTime = time();

        $currentTime = Utils::UnixTimeToDateTime($currentUnixTime);
        $programs = $this->programService->getCurrentPrograms($currentTime);

        $playingPrograms = [];
        foreach ($programs as $program) {

            $playingProgram = $this->populateProgram($program, $currentUnixTime);
            $playingProgram->upNext = $this->getUpNext($program->channelId, $playingProgram->endTime, $currentUnixTime);
            $playingPrograms[] = $playingProgram;

        }

        $ret = new CurrentlyPlayingResponse();
        $ret->currentTime = $currentTime;
        $ret->currentTimeUnix = $currentUnixTime;
        $ret->programs = $playingPrograms;

        return $ret;
    }

    private function populateProgram(\ptv\model\Program $program, int $currentUnixTime): PlayingProgram {
        $title = $this->titleService->getById($program->titleId);
        $currentSeconds = $currentUnixTime - strtotime($program->startTime);
        $secondsLeft = strtotime($program->endTime) - $currentUnixTime;
        $plexUrl = $this->plexServer->getPlayURL($title);
        $thumbUrl = $this->plexServer->getBaseUrl() . $title->thumbUrl;
        $channelName = $this->channelService->getById($program->channelId, true)->name;

        $playingProgram = new PlayingProgram($title, $program, $channelName);
        $playingProgram->remainingSeconds = $secondsLeft;
        $playingProgram->remainingTime = Utils::GetReadableDuration($secondsLeft);
        $playingProgram->thumbUrl = $thumbUrl;
        $playingProgram->playUrl = $plexUrl;
        $playingProgram->currentTimeSeconds = $currentSeconds;
        $playingProgram->isFiller = (bool) $program->isFiller;
        return $playingProgram;
    }

    private function getUpNext(int $channelId, string $startTime, int $currentTimeUnix) {
        $program = $this->programService->getUpNext($channelId, $startTime);
        $playingProgram = $this->populateProgram($program, $currentTimeUnix);
        $playingProgram->startsIn = Utils::GetReadableDuration(strtotime($program->startTime) - $currentTimeUnix);
        return $playingProgram;
    }



}