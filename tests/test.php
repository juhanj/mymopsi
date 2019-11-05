<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
echo '<style>html{background-color: #0f111a;color: #c0c6c5;font-size: larger;}</style>';
/*/////////////////////////////////////////////////*/

$postData = [
	'username' => 'test',
	'password' => 'test',
	'request_type' => 'user_login',
];
$jsonData = json_encode( $postData );
//echo $jsonData;
$curlHandle = curl_init();

$curlOptions = [
	CURLOPT_URL => "https://cs.uef.fi/mopsi/mobile/server.php",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => ["param"=>$jsonData]
];

curl_setopt_array(
	$curlHandle,
	$curlOptions
);

$response = curl_exec( $curlHandle );

curl_close( $curlHandle );

debug(
	json_decode($response),
	true
);