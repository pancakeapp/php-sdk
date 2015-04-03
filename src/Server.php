<?php

namespace Pancake;

class Server {

    protected $url;
    protected $api_key;
    protected $http;
    protected $debug = false;

    function __construct($url, $api_key) {
        $this->http = new \HTTP_Request();
        $this->api_key = $api_key;

        $url = rtrim($url, "/")."/";

        if (substr($url, -strlen("/index.php/")) != "/index.php/") {
            $url = rtrim($url, "/")."/index.php/";
        }

        if (substr($url, -strlen("/api/1/")) != "/api/1/") {
            $url = rtrim($url, "/")."/api/1/";
        }

        $this->url = $url;
    }

    function setDebug($debug = true) {
        $this->debug = $debug;
    }

    function request($url, $data, $method = "POST") {
        $data['X-API-KEY'] = $this->api_key;
        $original_contents = $this->http->request($this->url.$url, $method, $data);
        $contents = json_decode($original_contents, true);

        if ($contents === null) {
            if (!$this->debug) {
                throw new ApiException("An unknown error occurred on Pancake's side. It was probably logged in your Errors & Diagnostics.");
            } else {
                echo $original_contents;
                die;
            }
        }

        if ($contents['status'] !== true) {
            if (isset($contents['error'])) {
                $message = $contents['error'];
            } elseif (isset($contents['error_message'])) {
                $message = $contents['error_message'];
            } elseif (isset($contents['message'])) {
                $message = $contents['message'];
            }

            throw new ApiException($message);
        }

        return $contents;
    }

    function post($url, $data) {
        return $this->request($url, $data, "POST");
    }

    function get($url, $data) {
        $contents = $this->request($url, $data, "GET");
        unset($contents['status']);
        unset($contents['message']);
        unset($contents['count']);
        return reset($contents);
    }

}
