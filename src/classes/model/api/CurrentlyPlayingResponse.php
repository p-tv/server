<?php

namespace ptv\model\api;


class CurrentlyPlayingResponse extends APIResponse {


    /**
     * List of programs, one per channel
     * @var PlayingProgram[]
     */
    var $programs;

    /**
     * Current time in YYYY-MM-DD HH:ii:ss format
     * @var string
     */
    var $currentTime;

    /**
     * Current time as unix time
     * @var int
     */
    var $currentTimeUnix;
}