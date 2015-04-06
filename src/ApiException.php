<?php

namespace Pancake;

use Exception;

class ApiException extends Exception {

    protected $request;
    protected $response;

    public function __construct($message, $request, $response) {
        $this->request  = $request;
        $this->response = $response;
        parent::__construct($message, 0, null);
    }


    function getRequest() {
        return $this->request;
    }

    function getResponse() {
        return $this->response;
    }

}
