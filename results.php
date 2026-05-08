<?php
$pageTitle = "Results";
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
    $questionId = $option["question_id"];

    if (!isset($optionsByQuestion[$questionId])) {
        $optionsByQuestion[$questionId] = [];
    }

    if (!isset($totalVotesByQuestion[$questionId])) {
        $totalVotesByQuestion[$questionId] = 0;
    }

    $optionsByQuestion[$questionId][] = $option;
    $totalVotesByQuestion[$questionId] += (int) $option["vote_count"];
}

include "includes/header.php";
?>

<section class="page-section results-page">
    <div class="results-header">
        <h1><?= htmlspecialchars($poll["title"]) ?> Results</h1>

        <?php if (!empty($poll["description"])): ?>
            <p><?= htmlspecialchars($poll["description"]) ?></p>
        <?php endif; ?>
    </div>

    <div class="results-list">
        <?php foreach ($questions as $question): ?>
            <?php
                $questionOptions = $optionsByQuestion[$question["id"]] ?? [];
                $questionTotalVotes = $totalVotesByQuestion[$question["id"]] ?? 0;
                $maxVotes = 0;

                foreach ($questionOptions as $option) {
                    if ((int) $option["vote_count"] > $maxVotes) {
                        $maxVotes = (int) $option["vote_count"];
                    }
                }
            ?>

            <article class="result-card">
                <div class="result-card-head">
                    <div>
                        <h2><?= htmlspecialchars($question["question_text"]) ?></h2>
                        <p class="result-type"><?= htmlspecialchars(ucfirst($question["question_type"])) ?> Choice</p>
                    </div>

                    <div class="result-total-box">
                        <span>Total Selections</span>
                        <strong><?= $questionTotalVotes ?></strong>
                    </div>
                </div>

                <?php if ($questionTotalVotes == 0): ?>
                    <div class="empty-note">No votes have been submitted for this question yet.</div>
                <?php endif; ?>

                <div class="result-options">
                    <?php foreach ($questionOptions as $option): ?>
                        <?php
                            $voteCount = (int) $option["vote_count"];
                            $percentage = $questionTotalVotes > 0 ? ($voteCount / $questionTotalVotes) * 100 : 0;
                            $percentageText = rtrim(rtrim(number_format($percentage, 1), "0"), ".");
                            $isTopChoice = $maxVotes > 0 && $voteCount === $maxVotes;
                        ?>

                        <div class="result-option">
                            <div class="result-option-top">
                                <div class="result-option-label-wrap">
                                    <span class="result-option-label"><?= htmlspecialchars($option["option_text"]) ?></span>

                                    <?php if ($isTopChoice): ?>
                                        <span class="top-badge">Top Choice</span>
                                    <?php endif; ?>
                                </div>

                                <div class="result-option-stats">
                                    <span><?= $voteCount ?> <?= $voteCount == 1 ? "vote" : "votes" ?></span>
                                    <span><?= $percentageText ?>%</span>
                                </div>
                            </div>

                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="form-actions results-actions">
        <a href="vote.php?id=<?= $pollId ?>">Back to Vote Page</a>
        <a href="index.php">Back Home</a>
    </div>
</section>
<script src="assets/js/results.js"></script>
<?php
include "includes/footer.php";
?>