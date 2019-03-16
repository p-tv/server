<?php

namespace ptv\model\api;


class APIResponse {
    var $status;

    var $errorMsg;

    /**
     * APIResponse constructor.
     * @param $status
     * @param $errorMsg
     */
    public function __construct($status = 'success', $errorMsg = '') {
        $this->status = $status;
        $this->errorMsg = $errorMsg;
    }

    public function setFromException(\Exception $exception) {
        $this->status = 'error';
        $this->errorMsg = $exception->getMessage();
    }

}