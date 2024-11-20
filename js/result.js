const mainBody = document.getElementById('main-body');

document.addEventListener("DOMContentLoaded", async () => {
    const result = localStorage.getItem('result');
    const json = JSON.parse(result);
    if (json !== null) {
        localStorage.removeItem('result');
        json.result[0].forEach(element => {
            const resultCard = GetResultCard(element.question, element.options);
            if (resultCard !== null) {
                mainBody.appendChild(resultCard);
            }
        });
    }
    else{
        window.location.assign('../login.html')
    }
});