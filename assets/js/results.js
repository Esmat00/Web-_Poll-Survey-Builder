const params = new URLSearchParams(window.location.search);
const pollId = Number(params.get("id"));

function loadResultsData() {
    if (!pollId) {
        return;
    }

    fetch(`api/get_results.php?id=${pollId}`)
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                return;
            }
        })
        .catch(function () {
            return;
        });
}

loadResultsData();

setInterval(loadResultsData, 5000);