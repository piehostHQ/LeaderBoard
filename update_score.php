<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit();
}

// Read JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

// Log received data for debugging
file_put_contents("log.txt", print_r($data, true), FILE_APPEND);

if (!isset($data['player']['name']) || !isset($data['player']['score'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$playerName = $data['player']['name'];
$playerScore = intval($data['player']['score']);

// PieSocket API Credentials
$apiKey = "B9UKgvptNTWrZxfCUTquFp7nKVsYqu2LtmBao5Jg";
$apiSecret = "yv17FqeuEhCsy3vHMYbtYIZUtav6KZzA";
$roomId = "leaderboard-channel";

// Prepare the data to send
$postData = [
    "key" => $apiKey,
    "secret" => $apiSecret,
    "roomId" => $roomId,
    "message" => [
        "event" => "scoreUpdate",
        "data" => [
            "name" => $playerName,
            "score" => $playerScore
        ]
    ]
];

// Initialize cURL request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://free.blr2.piesocket.com/api/publish",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($postData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response
echo json_encode([
    "status" => "success",
    "http_code" => $httpCode,
    "piesocket_response" => $response
]);
