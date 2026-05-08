const questionsContainer = document.getElementById("questionsContainer");
const addQuestionBtn = document.getElementById("addQuestionBtn");
const pollForm = document.getElementById("pollForm");

let questionCount = 0;

function createQuestionBlock() {
    questionCount++;

    const questionBlock = document.createElement("div");
    questionBlock.className = "question-card";
    questionBlock.dataset.questionIndex = questionCount;

    questionBlock.innerHTML = `
        <div class="question-card-header">
            <h2>Question ${questionCount}</h2>
            <button type="button" class="remove-question-btn">Remove Question</button>
        </div>

        <div class="form-card">
            <label>Question Text</label>
            <input type="text" class="question-text" placeholder="Example: What is your favorite language?" required>
        </div>

        <div class="form-card">
            <label>Question Type</label>
            <select class="question-type">
                <option value="single">Single Choice</option>
                <option value="multiple">Multiple Choice</option>
            </select>
        </div>

        <div class="options-container">
            <label>Options</label>

            <div class="option-row">
                <input type="text" class="option-text" placeholder="Option 1" required>
                <button type="button" class="remove-option-btn">Remove</button>
            </div>

            <div class="option-row">
                <input type="text" class="option-text" placeholder="Option 2" required>
                <button type="button" class="remove-option-btn">Remove</button>
            </div>
        </div>

        <button type="button" class="add-option-btn">Add Option</button>
    `;

    questionsContainer.appendChild(questionBlock);
}

addQuestionBtn.addEventListener("click", createQuestionBlock);

questionsContainer.addEventListener("click", function (event) {
    if (event.target.classList.contains("remove-question-btn")) {
        event.target.closest(".question-card").remove();
    }

    if (event.target.classList.contains("add-option-btn")) {
        const questionCard = event.target.closest(".question-card");
        const optionsContainer = questionCard.querySelector(".options-container");
        const optionCount = optionsContainer.querySelectorAll(".option-row").length + 1;

        const optionRow = document.createElement("div");
        optionRow.className = "option-row";

        optionRow.innerHTML = `
            <input type="text" class="option-text" placeholder="Option ${optionCount}" required>
            <button type="button" class="remove-option-btn">Remove</button>
        `;

        optionsContainer.appendChild(optionRow);
    }

    if (event.target.classList.contains("remove-option-btn")) {
        const optionsContainer = event.target.closest(".options-container");
        const optionRows = optionsContainer.querySelectorAll(".option-row");

        if (optionRows.length > 2) {
            event.target.closest(".option-row").remove();
        } else {
            alert("Each question must have at least two options.");
        }
    }
});

pollForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const pollTitle = document.getElementById("pollTitle").value.trim();
    const pollDescription = document.getElementById("pollDescription").value.trim();
    const questionCards = document.querySelectorAll(".question-card");

    if (pollTitle === "") {
        alert("Poll title is required.");
        return;
    }

    if (questionCards.length === 0) {
        alert("Please add at least one question.");
        return;
    }

    const pollData = {
        title: pollTitle,
        description: pollDescription,
        questions: []
    };

    for (let questionIndex = 0; questionIndex < questionCards.length; questionIndex++) {
        const questionCard = questionCards[questionIndex];
        const questionText = questionCard.querySelector(".question-text").value.trim();
        const questionType = questionCard.querySelector(".question-type").value;
        const optionInputs = questionCard.querySelectorAll(".option-text");

        if (questionText === "") {
            alert("Each question must have text.");
            return;
        }

        if (optionInputs.length < 2) {
            alert("Each question must have at least two options.");
            return;
        }

        const questionData = {
            question_text: questionText,
            question_type: questionType,
            order_num: questionIndex + 1,
            options: []
        };

        for (let optionIndex = 0; optionIndex < optionInputs.length; optionIndex++) {
            const optionText = optionInputs[optionIndex].value.trim();

            if (optionText === "") {
                alert("Options cannot be empty.");
                return;
            }

            questionData.options.push({
                option_text: optionText,
                order_num: optionIndex + 1
            });
        }

        pollData.questions.push(questionData);
    }

    fetch("api/save_poll.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify(pollData)
})
    .then(function (response) {
        return response.json();
    })
    .then(function (result) {
       

        if (result.success) {
            alert("Poll saved successfully.");

            window.location.href = result.vote_link;
        } else {
            alert(result.message);
        }
    })
    .catch(function (error) {
        console.error(error);
        alert("Something went wrong while saving the poll.");
    });
});