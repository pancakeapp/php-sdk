<?php

namespace Pancake;

use GuzzleHttp\Client;
use JetBrains\PhpStorm\ArrayShape;

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

            $error_details = [];
            $regex = "/<span class=\"message\">([^<]+?)<br \\/>([^<]+?)<br \\/>([^<]+?)<\\/span>/us";
            if (preg_match($regex, $original_contents, $error_details)) {
                $error_type = [];
                preg_match("/<span class=\"type\">([^<]+?)<\\/span>/us", $original_contents, $error_type);

                $error = "Pancake-side {$error_type[1]} {$error_details[1]}\n{$error_details[2]}\n{$error_details[3]}";
            }

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

    public function convertDocBlockToProperties(string $docblock): array
    {
        $matches = [];
        preg_match_all('/^\s*\*\s+@property(?:-read)?\s+([\S]+)\s+\$([\S]+)$/uim', $docblock, $matches);
        $properties = [];
        foreach ($matches[2] as $key => $property_name) {
            $properties[$property_name] = $matches[1][$key];
        }
        return $properties;
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
