<?php

namespace ptv\model\api;


use ptv\model\Program;
use ptv\model\Title;

/**
 * Data sent to the client about the currently playing client.
 * Class PlayingProgram
 * @package ptv\model\api
 */
class PlayingProgram {

    /**
     * @var string
     */
    var $channelName;

    /**
     * @var string
     */
    var $channelId;

    /**
     * @var string
     */
    var $titleName;

    /**
     * @var int
     */
    var $titleId;

    /**
     * @var string
     */
    var $titleDescription;

    /**
     * @var int
     */
    var $programId;

    /**
     * @var string
     */
    var $startTime;

    /**
     * @var string
     */
    var $endTime;

    /**
     * @var string
     */
    var $thumbUrl;

    /**
     * @var string
     */
    var $currentTimeSeconds;

    /**
     * @var string
     */
    var $playUrl;

    /**
     * @var int
     */
    var $remainingSeconds;

    /**
     * @var string
     */
    var $remainingTime;

    /**
     * @var PlayingProgram
     */
    var $upNext;

    /**
     * @var string
     */
    var $startsIn;

    /**
     * @var boolean
     */
    var $isFiller;

    /**
     * PlayingProgram constructor.
     * @param Title $title
     * @param Program $program
     * @param string $channelName
     */
    public function __construct(Title $title, Program $program, string $channelName) {
        if ($title->isShow()) {
            $this->titleName = $title->showName . ': ' . $title->name;
        } else {
            $this->titleName = $title->name;
        }
        $this->titleId = $title->id;
        $this->titleDescription = $title->summary;
        $this->channelId = $program->channelId;
        $this->channelName = $channelName;
        $this->programId = $program->id;
        $this->endTime = $program->endTime;
        $this->startTime = $program->startTime;
    }

}