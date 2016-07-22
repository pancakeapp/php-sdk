<?php

namespace Pancake;

use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;

class Server
{

    protected $api_key;
    protected $http;

    public function __construct($url, $api_key)
    {
        $this->api_key = $api_key;

        $url = rtrim($url, "/") . "/";

        if (substr($url, -strlen("/index.php/")) != "/index.php/") {
            $url = rtrim($url, "/") . "/index.php/";
        }

        if (substr($url, -strlen("/api/1/")) != "/api/1/") {
            $url = rtrim($url, "/") . "/api/1/";
        }

        $this->http = new Client([
            "base_uri" => $url,
            "verify" => CaBundle::getSystemCaRootBundlePath(),
        ]);
    }

    public function request($url, $data, $method = "POST")
    {
        $data['X-API-KEY'] = $this->api_key;

        if ($method == "POST") {
            $response = $this->http->request($method, $url, [
                "form_params" => $data,
            ]);
        } elseif ($method == "GET") {
            $response = $this->http->request($method, $url, [
                "query" => $data,
            ]);
        } else {
            $response = $this->http->request($method, $url);
        }

        $original_contents = $response->getBody()->getContents();
        $contents = json_decode($original_contents, true);

        if ($contents === null) {
            $error = "An error occurred on Pancake's side. It was probably logged in your Errors & Diagnostics.";
            throw new ApiException($error, $original_contents);
        }

        if ($contents['status'] !== true) {
            if (isset($contents['error'])) {
                $message = $contents['error'];
            } elseif (isset($contents['error_message'])) {
                $message = $contents['error_message'];
            } elseif (isset($contents['message'])) {
                $message = $contents['message'];
            } else {
                $message = "Unknown Error";
            }

            throw new ApiException($message, $contents);
        }

        return $contents;
    }

    public function post($url, $data)
    {
        return $this->request($url, $data, "POST");
    }

    public function get($url, $data = array())
    {
        $contents = $this->request($url, $data, "GET");
        unset($contents['status']);
        unset($contents['message']);
        unset($contents['count']);

        return reset($contents);
    }
}
