<?php

header("Content-Type: application/json");

require_once "../config/database.php";

$pollId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($pollId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid poll ID."
    ]);
    exit;
}

$pollStmt = $pdo->prepare("SELECT id, title, description, created_at FROM polls WHERE id = :id");
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

$questionsStmt = $pdo->prepare("
    SELECT id, question_text, question_type, order_num
    FROM questions
    WHERE poll_id = :poll_id
    ORDER BY order_num ASC
");

$questionsStmt->execute([
    ":poll_id" => $pollId
]);

$questions = $questionsStmt->fetchAll();

$optionsStmt = $pdo->prepare("
    SELECT
        question_options.id,
        question_options.question_id,
        question_options.option_text,
        question_options.order_num,
        COUNT(votes.id) AS vote_count
    FROM question_options
    INNER JOIN questions ON question_options.question_id = questions.id
    LEFT JOIN votes ON question_options.id = votes.option_id
    WHERE questions.poll_id = :poll_id
    GROUP BY question_options.id, question_options.question_id, question_options.option_text, question_options.order_num
    ORDER BY question_options.question_id ASC, question_options.order_num ASC
");

$optionsStmt->execute([
    ":poll_id" => $pollId
]);

$options = $optionsStmt->fetchAll();

$optionsByQuestion = [];
$totalVotesByQuestion = [];

foreach ($options as $option) {
    $questionId = (int) $option["question_id"];

    if (!isset($optionsByQuestion[$questionId])) {
        $optionsByQuestion[$questionId] = [];
    }

    if (!isset($totalVotesByQuestion[$questionId])) {
        $totalVotesByQuestion[$questionId] = 0;
    }

    $optionsByQuestion[$questionId][] = $option;
    $totalVotesByQuestion[$questionId] += (int) $option["vote_count"];
}

$resultQuestions = [];

foreach ($questions as $question) {
    $questionId = (int) $question["id"];
    $totalVotes = $totalVotesByQuestion[$questionId] ?? 0;

    $questionData = [
        "id" => $questionId,
        "question_text" => $question["question_text"],
        "question_type" => $question["question_type"],
        "order_num" => (int) $question["order_num"],
        "total_selections" => $totalVotes,
        "options" => []
    ];

    foreach ($optionsByQuestion[$questionId] ?? [] as $option) {
        $voteCount = (int) $option["vote_count"];
        $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;

        $questionData["options"][] = [
            "id" => (int) $option["id"],
            "option_text" => $option["option_text"],
            "order_num" => (int) $option["order_num"],
            "vote_count" => $voteCount,
            "percentage" => $percentage
        ];
    }

    $resultQuestions[] = $questionData;
}

echo json_encode([
    "success" => true,
    "poll" => [
        "id" => (int) $poll["id"],
        "title" => $poll["title"],
        "description" => $poll["description"],
        "created_at" => $poll["created_at"]
    ],
    "questions" => $resultQuestions
]);