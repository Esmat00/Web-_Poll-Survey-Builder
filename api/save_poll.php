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

$title = trim($data["title"] ?? "");
$description = trim($data["description"] ?? "");
$questions = $data["questions"] ?? [];

if ($title === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Poll title is required."
    ]);
    exit;
}

if (!is_array($questions) || count($questions) === 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "At least one question is required."
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $pollStmt = $pdo->prepare("INSERT INTO polls (title, description) VALUES (:title, :description)");
    $pollStmt->execute([
        ":title" => $title,
        ":description" => $description
    ]);

    $pollId = $pdo->lastInsertId();

    $questionStmt = $pdo->prepare("
        INSERT INTO questions (poll_id, question_text, question_type, order_num)
        VALUES (:poll_id, :question_text, :question_type, :order_num)
    ");

    $optionStmt = $pdo->prepare("
        INSERT INTO question_options (question_id, option_text, order_num)
        VALUES (:question_id, :option_text, :order_num)
    ");

    foreach ($questions as $questionIndex => $question) {
        $questionText = trim($question["question_text"] ?? "");
        $questionType = $question["question_type"] ?? "single";
        $questionOrder = (int) ($question["order_num"] ?? ($questionIndex + 1));
        $options = $question["options"] ?? [];

        if ($questionText === "") {
            throw new Exception("Each question must have text.");
        }

        if (!in_array($questionType, ["single", "multiple"])) {
            throw new Exception("Invalid question type.");
        }

        if (!is_array($options) || count($options) < 2) {
            throw new Exception("Each question must have at least two options.");
        }

        $questionStmt->execute([
            ":poll_id" => $pollId,
            ":question_text" => $questionText,
            ":question_type" => $questionType,
            ":order_num" => $questionOrder
        ]);

        $questionId = $pdo->lastInsertId();

        foreach ($options as $optionIndex => $option) {
            $optionText = trim($option["option_text"] ?? "");
            $optionOrder = (int) ($option["order_num"] ?? ($optionIndex + 1));

            if ($optionText === "") {
                throw new Exception("Options cannot be empty.");
            }

            $optionStmt->execute([
                ":question_id" => $questionId,
                ":option_text" => $optionText,
                ":order_num" => $optionOrder
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Poll saved successfully.",
        "poll_id" => $pollId,
        "vote_link" => "vote.php?id=" . $pollId,
        "results_link" => "results.php?id=" . $pollId
    ]);
} catch (Exception $e) {
    $pdo->rollBack();

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}