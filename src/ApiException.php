<?php

namespace Pancake;

use Exception;

/**
 * API Exceptions.<br />
 * Thrown by the SDK when something goes wrong with the API.
 *
 * @package  Pancake
 * @author   Pancake Dev Team <support@pancakeapp.com>
 * @license  http://pancakeapp.com/license Pancake End User License Agreement
 * @link     http://pancakeapp.com
 */
class ApiException extends Exception
{

    protected $response;

    public function __construct($message, $response)
    {
        $this->response = $response;
        parent::__construct($message, 0, null);
    }

    public function getResponse()
    {
        return $this->response;
    }
}
