<?php
$pageTitle = "Manage Polls";
require_once "config/database.php";

$pollsStmt = $pdo->query("
    SELECT
        polls.id,
        polls.title,
        polls.description,
        polls.created_at,
        COUNT(DISTINCT questions.id) AS question_count,
        COUNT(DISTINCT poll_voters.id) AS voter_count
    FROM polls
    LEFT JOIN questions ON polls.id = questions.poll_id
    LEFT JOIN poll_voters ON polls.id = poll_voters.poll_id
    GROUP BY polls.id
    ORDER BY polls.created_at DESC
");

$polls = $pollsStmt->fetchAll();

include "includes/header.php";
?>

<section class="page-section admin-page">
    <div class="page-header">
        <h1>Manage Polls</h1>
        <p>View all created polls, open voting pages, check results, and manage your surveys easily.</p>
    </div>

    <?php if (count($polls) === 0): ?>
        <div class="empty-state">
            <h2>No polls available yet</h2>
            <p>You have not created any polls so far. Start by creating your first one.</p>
            <a href="create.php" class="btn-primary">Create First Poll</a>
        </div>
    <?php else: ?>
        <div class="admin-list">
            <?php foreach ($polls as $poll): ?>
                <article class="admin-card" data-poll-id="<?= $poll["id"] ?>">
                    <div class="admin-card-top">
                        <div class="admin-card-title">
                            <h2><?= htmlspecialchars($poll["title"]) ?></h2>
                            <?php if (!empty($poll["description"])): ?>
                                <p><?= htmlspecialchars($poll["description"]) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="admin-stats">
                            <div class="stat-box">
                                <span class="stat-label">Questions</span>
                                <strong><?= (int) $poll["question_count"] ?></strong>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Voters</span>
                                <strong><?= (int) $poll["voter_count"] ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="admin-meta">
                        <span class="meta-badge">Poll ID: <?= (int) $poll["id"] ?></span>
                        <span class="meta-badge">Created: <?= htmlspecialchars($poll["created_at"]) ?></span>
                    </div>

                    <div class="admin-actions">
                        <a href="vote.php?id=<?= $poll["id"] ?>" class="btn-primary">Open Vote Page</a>
                        <a href="results.php?id=<?= $poll["id"] ?>" class="btn-secondary">View Results</a>
                        <button type="button" class="btn-danger delete-poll-btn" data-poll-id="<?= $poll["id"] ?>">Delete</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<script>




const deleteButtons = document.querySelectorAll(".delete-poll-btn");

deleteButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        const pollId = Number(button.dataset.pollId);
        const pollCard = button.closest(".admin-card");

        const confirmed = confirm("Are you sure you want to delete this poll? This action cannot be undone.");

        if (!confirmed) {
            return;
        }

        fetch("api/delete_poll.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                poll_id: pollId
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (result.success) {
                    alert("Poll deleted successfully.");
                    pollCard.remove();
                } else {
                    alert(result.message);
                }
            })
            .catch(function (error) {
                console.error(error);
                alert("Something went wrong while deleting the poll.");
            });
    });
});
</script>

<?php include "includes/footer.php"; ?>