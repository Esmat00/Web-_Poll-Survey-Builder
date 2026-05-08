<?php

header("Content-Type: application/json");

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
    exit;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);
    exit;
}

$pollId = isset($data["poll_id"]) ? (int) $data["poll_id"] : 0;

if ($pollId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid poll ID."
    ]);
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM polls WHERE id = :id");
$checkStmt->execute([
    ":id" => $pollId
]);

$poll = $checkStmt->fetch();

if (!$poll) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Poll not found."
    ]);
    exit;
}

try {
    $deleteStmt = $pdo->prepare("DELETE FROM polls WHERE id = :id");
    $deleteStmt->execute([
        ":id" => $pollId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Poll deleted successfully."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not delete poll."
    ]);
}