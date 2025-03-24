<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['player']['name']) || !isset($data['player']['score'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$playerName = $data['player']['name'];
$playerScore = intval($data['player']['score']);

$conn = new mysqli("localhost", "user", "pass", "leaderboard");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "INSERT INTO scores (player_name, score) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE score = VALUES(score)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $playerName, $playerScore);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(["status" => "success"]);
