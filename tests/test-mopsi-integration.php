<?php declare(strict_types=1);
require '../components/_start.php';
/** @var DBConnection $db */
/*/////////////////////////////////////////////////*/

//
// LOGIN
//
$jsonData = json_encode( [
	'username' => '',
	'password' => '',
	'request_type' => 'user_login',
] );
// We send it as a POST-request, but Mopsi-server still wants the data in JSON

$curlHandle = curl_init();

$curlOptions = [
	CURLOPT_URL => "https://cs.uef.fi/mopsi/mobile/server.php",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => [ "param" => $jsonData ],
	// Mopsi server wants the "param", and the JSON, in this specific format
	// Not my fault.
];

curl_setopt_array(
	$curlHandle,
	$curlOptions
);

$responseJSON = curl_exec( $curlHandle );
$response = json_decode( $responseJSON );

curl_close( $curlHandle );

debug( $response );

//
// GEO CODING
//
$param = [
	'request_type' => 'get_address',
	'lat' => 62.5913800,
	'lon' => 29.7796980,

	// Berlin: 52.51500498885672, 13.40485769825909
	// Joensuun keskusairaala: 62.5913800, 29.7796980
];

$mopsiServer = 'https://cs.uef.fi/mopsi/mobile/server.php?param=' . urlencode( json_encode( $param ) );

$json_response = file_get_contents( $mopsiServer );

$result = json_decode( $json_response );

debug( $result );