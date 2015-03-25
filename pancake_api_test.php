<?php


class Pancake {

	protected $api_key;
	protected $server_url;

	public function __construct($server_url, $api_key) {
		$server_url = rtrim($server_url, "/");
		if (substr($server_url, -strlen("index.php")) != "index.php") {
			$server_url .= "/index.php";
		}
		
		$this->server_url = $server_url."/api/1/";
		$this->api_key = $api_key;
	}
	
	public function get($url) {
		return $this->curl($url."?limit=".PHP_INT_MAX."&start=0");
	}
	
	public function post($url, $data) {
	
	}
	
	protected function curl($url, $is_post = false, $data = null) {
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			#CURLOPT_HEADER => 1,
			CURLOPT_HTTPHEADER => array(
				"X-API-KEY: {$this->api_key}",
			),
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_URL => $this->server_url.$url,
    		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8'
		));
	
		if ($is_post) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
	
		$resp = curl_exec($curl);

		if (!$resp) {
			throw new Exception(curl_error($curl), curl_errno($curl));
		}

		curl_close($curl);
		
		$json = json_decode($resp, true);
		
		if ($json === NULL) {
			echo "<h1>Could not decode JSON response</h1>";
			echo $resp;
			die;
		}
		
		return $json;
	}

}

$pancake = new Pancake("http://localhost/PancakePayments/", "1ehm28je025q907592t161bvyt3ied08jhvcknop");
$response = $pancake->get("clients");

var_dump($response);
die;