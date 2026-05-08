<?php
$pageTitle = "Vote";
require_once "config/database.php";

$pollId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($pollId <= 0) {
    die("Invalid poll ID.");
}

$pollStmt = $pdo->prepare("SELECT * FROM polls WHERE id = :id");
$pollStmt->execute([
    ":id" => $pollId
]);

$poll = $pollStmt->fetch();

if (!$poll) {
    die("Poll not found.");
}

$questionsStmt = $pdo->prepare("
    SELECT *
    FROM questions
    WHERE poll_id = :poll_id
    ORDER BY order_num ASC
");

$questionsStmt->execute([
    ":poll_id" => $pollId
]);

$questions = $questionsStmt->fetchAll();

$optionsStmt = $pdo->prepare("
    SELECT question_options.*
    FROM question_options
    INNER JOIN questions ON question_options.question_id = questions.id
    WHERE questions.poll_id = :poll_id
    ORDER BY question_options.order_num ASC
");

$optionsStmt->execute([
    ":poll_id" => $pollId
]);

$options = $optionsStmt->fetchAll();

$optionsByQuestion = [];

foreach ($options as $option) {
    $optionsByQuestion[$option["question_id"]][] = $option;
}

include "includes/header.php";
?>

<section class="page-section">
    <div class="page-header">
        <h1><?= htmlspecialchars($poll["title"]) ?></h1>

        <?php if (!empty($poll["description"])): ?>
            <p><?= htmlspecialchars($poll["description"]) ?></p>
        <?php endif; ?>
    </div>

    <form class="vote-form" id="voteForm" data-poll-id="<?= $pollId ?>">
        <?php foreach ($questions as $question): ?>
            <article
                class="question-card"
                data-question-id="<?= $question["id"] ?>"
                data-question-type="<?= htmlspecialchars($question["question_type"]) ?>"
            >
                <h2><?= htmlspecialchars($question["question_text"]) ?></h2>

                <div class="vote-options">
                    <?php foreach ($optionsByQuestion[$question["id"]] ?? [] as $option): ?>
                        <label class="vote-option">
                            <input
                                type="<?= $question["question_type"] === "single" ? "radio" : "checkbox" ?>"
                                name="question_<?= $question["id"] ?><?= $question["question_type"] === "multiple" ? "[]" : "" ?>"
                                value="<?= $option["id"] ?>"
                            >
                            <span><?= htmlspecialchars($option["option_text"]) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <div class="form-actions">
            <button type="submit">Submit Vote</button>
            <a href="results.php?id=<?= $pollId ?>">View Results</a>
        </div>
    </form>
</section>

<script src="assets/js/vote.js"></script>

<?php
include "includes/footer.php";
?>