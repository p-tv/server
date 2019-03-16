<?php

namespace ptv;

/**
 * Extension on top of \jc21\PlexApi to expose the tv show children / all leaves (subchildren) API calls
 * Class PlexApi
 * @package ptv
 */
class PlexApi extends \jc21\PlexApi {


    public function getChildren($key) {
        return $this->call('/library/metadata/' . $key . '/children');
    }

    public function getAllLeaves($key) {
        return $this->call('/library/metadata/' . $key . '/allLeaves');
    }

}