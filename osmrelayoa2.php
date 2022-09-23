<?php 
header("Access-Control-Allow-Origin: *");


// was used when API address moved but apps were live on app store.
$osm = "https://www.onlinescoutmanager.com/";
$ch = curl_init();
 
$path = $_SERVER['REQUEST_URI'];
$parameters = $_GET;
 
$path_add = $parameters['osmpath'];
 
unset($parameters['osmpath']);
 
 
//$path = http_build_query($parameters);
 
$headers = apache_request_headers();
$header = array("Authorization: {$headers['Authorization']}");
 
curl_setopt_array($ch, array(
		CURLOPT_URL => 'https://www.onlinescoutmanager.co.uk/'.$path_add.'?'.$path,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
 
	));
	if ( http_build_query($_POST)!="") {
	  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));  
	};

	$response = curl_exec($ch);
	list($headers, $body) = explode("\n\n", $response, 2);
	$headers = explode("\n", $headers);
foreach ($headers as $header) {
    list($key, $value) = explode(':', $header, 2);
    $headers[trim($key)] = trim($value);
}

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); 
header("X-RateLimit-Remaining: ".$headers['x-ratelimit-remaining']);
header("X-RateLimit-Reset: ".$headers['x-ratelimit-reset']);
header("X-RateLimit-Limit: ".$headers['x-ratelimit-limit']);
if (isset($headers['X-Blocked'])) {
header("X-Blocked: ".$headers['X-Blocked']);
}
if (isset($headers['retry-after'])) {
header("retry-after: ".$headers['retry-after']);
} 
//header("retry-after: ".$headers['retry-after']);
echo( substr($response, $header_size));
curl_close($ch);

 
exit;
 
?>