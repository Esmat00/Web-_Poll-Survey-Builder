<?php
$pageTitle = "Create Poll";
include "includes/header.php";
?>

<section class="page-section">
    <div class="page-header">
        <h1>Create New Poll</h1>
        <p>Build a poll with multiple questions and custom answer options.</p>
    </div>

    <form class="poll-form" id="pollForm">
        <div class="form-card">
            <label for="pollTitle">Poll Title</label>
            <input type="text" id="pollTitle" name="title" placeholder="Example: Favorite Programming Language" required>
        </div>

        <div class="form-card">
            <label for="pollDescription">Poll Description</label>
            <textarea id="pollDescription" name="description" placeholder="Write a short description for this poll"></textarea>
        </div>

        <div id="questionsContainer"></div>

        <div class="form-actions">
            <button type="button" id="addQuestionBtn">Add Question</button>
            <button type="submit">Save Poll</button>
        </div>
    </form>
</section>

<script src="assets/js/create.js"></script>

<?php
include "includes/footer.php";
?>