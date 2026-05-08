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
$answers = $data["answers"] ?? [];

if ($pollId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid poll ID."
    ]);
    exit;
}

if (!is_array($answers) || count($answers) === 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No answers submitted."
    ]);
    exit;
}

$pollStmt = $pdo->prepare("SELECT id FROM polls WHERE id = :id");
$pollStmt->execute([
    ":id" => $pollId
]);

$poll = $pollStmt->fetch();

if (!$poll) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Poll not found."
    ]);
    exit;
}

if (empty($_COOKIE["voter_token"])) {
    $voterToken = bin2hex(random_bytes(32));
    setcookie("voter_token", $voterToken, time() + (365 * 24 * 60 * 60), "/");
} else {
    $voterToken = $_COOKIE["voter_token"];
}

$checkVoterStmt = $pdo->prepare("
    SELECT id
    FROM poll_voters
    WHERE poll_id = :poll_id AND voter_token = :voter_token
");

$checkVoterStmt->execute([
    ":poll_id" => $pollId,
    ":voter_token" => $voterToken
]);

if ($checkVoterStmt->fetch()) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "You have already voted in this poll."
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $questionStmt = $pdo->prepare("
        SELECT id, question_type
        FROM questions
        WHERE id = :question_id AND poll_id = :poll_id
    ");

    $optionStmt = $pdo->prepare("
        SELECT question_options.id
        FROM question_options
        INNER JOIN questions ON question_options.question_id = questions.id
        WHERE question_options.id = :option_id
        AND question_options.question_id = :question_id
        AND questions.poll_id = :poll_id
    ");

    $voteStmt = $pdo->prepare("
        INSERT INTO votes (poll_id, question_id, option_id, voter_token)
        VALUES (:poll_id, :question_id, :option_id, :voter_token)
    ");

    foreach ($answers as $answer) {
        $questionId = isset($answer["question_id"]) ? (int) $answer["question_id"] : 0;
        $selectedOptions = $answer["selected_options"] ?? [];

        if ($questionId <= 0) {
            throw new Exception("Invalid question ID.");
        }

        $questionStmt->execute([
            ":question_id" => $questionId,
            ":poll_id" => $pollId
        ]);

        $question = $questionStmt->fetch();

        if (!$question) {
            throw new Exception("Question does not belong to this poll.");
        }

        if (!is_array($selectedOptions) || count($selectedOptions) === 0) {
            throw new Exception("Each question must have at least one selected option.");
        }

        if ($question["question_type"] === "single" && count($selectedOptions) !== 1) {
            throw new Exception("Single choice questions must have exactly one selected option.");
        }

        foreach ($selectedOptions as $optionId) {
            $optionId = (int) $optionId;

            if ($optionId <= 0) {
                throw new Exception("Invalid option ID.");
            }

            $optionStmt->execute([
                ":option_id" => $optionId,
                ":question_id" => $questionId,
                ":poll_id" => $pollId
            ]);

            $option = $optionStmt->fetch();

            if (!$option) {
                throw new Exception("Selected option does not belong to the correct question.");
            }

            $voteStmt->execute([
                ":poll_id" => $pollId,
                ":question_id" => $questionId,
                ":option_id" => $optionId,
                ":voter_token" => $voterToken
            ]);
        }
    }

    $voterStmt = $pdo->prepare("
        INSERT INTO poll_voters (poll_id, voter_token)
        VALUES (:poll_id, :voter_token)
    ");

    $voterStmt->execute([
        ":poll_id" => $pollId,
        ":voter_token" => $voterToken
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Vote submitted successfully.",
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