<?php

namespace ptv\model\api;


class NextProgramsForChannelResponse extends APIResponse {


    /**
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