const voteForm = document.getElementById("voteForm");

voteForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const pollId = Number(voteForm.dataset.pollId);
    const questionCards = document.querySelectorAll(".question-card");

    const voteData = {
        poll_id: pollId,
        answers: []
    };

    for (const questionCard of questionCards) {
        const questionId = Number(questionCard.dataset.questionId);
        const questionType = questionCard.dataset.questionType;

        let selectedOptions = [];

        if (questionType === "single") {
            const selectedOption = questionCard.querySelector("input[type='radio']:checked");

            if (!selectedOption) {
                alert("Please answer all questions before submitting your vote.");
                return;
            }

            selectedOptions.push(Number(selectedOption.value));
        }

        if (questionType === "multiple") {
            const checkedOptions = questionCard.querySelectorAll("input[type='checkbox']:checked");

            if (checkedOptions.length === 0) {
                alert("Please answer all questions before submitting your vote.");
                return;
            }

            checkedOptions.forEach(function (option) {
                selectedOptions.push(Number(option.value));
            });
        }

        voteData.answers.push({
            question_id: questionId,
            question_type: questionType,
            selected_options: selectedOptions
        });
    }

    fetch("api/submit_vote.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(voteData)
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
           

            if (result.success) {
                alert("Vote submitted successfully.");
                window.location.href = result.results_link;
            } else {
                alert(result.message);
            }
        })
        .catch(function (error) {
            console.error(error);
            alert("Something went wrong while submitting your vote.");
        });
});