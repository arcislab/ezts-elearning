const topicName = document.getElementById('txtTopicName');
const qCount = document.getElementById('txtCount');
const timer = document.getElementById('txtTimer');
const exit = document.getElementById('btnExit');

const question = document.getElementById('txtQuestion');
const answers = document.querySelector('.answers');
const selectedTopic = localStorage.getItem('t');
let selectedQuiz = localStorage.getItem('qId');

addEventListener("DOMContentLoaded", () => {
    GetQuizInfo();
    GetQuiz();
    if (selectedQuiz != null) {
        localStorage.removeItem('qId');
    }
    exit.addEventListener('click', ExitQuiz);
    // localStorage.removeItem('t');
});

let totalQ;
let solvedQ = 1;

async function GetQuizInfo() {
    let url = `/quiz/info`

    try {
        const body = JSON.stringify({ course_topic: selectedTopic });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data.quiz !== undefined) {
                topicName.innerHTML = response.data.quiz.name;
                totalQ = response.data.quiz.questions;
                qCount.innerHTML = `${solvedQ}/${totalQ}`;
                startCountdown(response.data.quiz.time)
            } else {
                console.log('No information found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function ExitQuiz() {
    let url = `/quiz/exit`

    try {
        const body = JSON.stringify({ quiz: selectedQuiz });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response.status === 200) {
            history.back();
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

let selectedQuestion;
async function GetQuiz() {
    let url = `/quiz`

    try {
        const body = JSON.stringify({ course_topic: selectedTopic });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data !== undefined) {
                question.innerHTML = response.data.question;
                selectedQuestion = response.data.uuid;
                if (response.data.options !== undefined) {
                    response.data.options.forEach(item => {
                        const mcqElement = GetMcq(item.option);
                        answers.appendChild(mcqElement);
                        OptionsClickEvent(mcqElement, item.uuid);
                    });
                    const btnNextDiv = GetBtnNext();
                    const btnNext = btnNextDiv.querySelector('#btnNext');
                    answers.appendChild(btnNextDiv);
                    btnNext.removeEventListener('click', NextButtonBinderEvent);
                    btnNext.addEventListener('click', NextButtonBinderEvent);
                } else {
                    alert('Options not found. Please contact support!');
                    history.back();
                }
            } else {
                alert('No information found.');
                history.back();
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

// Set up a MutationObserver to detect when btnNextDiv is appended
const observer = new MutationObserver((mutationsList, observer) => {
    if (solvedQ >= totalQ) {
        const btnNext = document.querySelector('#btnNext');
        console.log(answers)
        console.log(btnNext)
        if (btnNext !== null) {
            btnNext.removeEventListener('click', NextButtonBinderEvent);
            btnNext.addEventListener('click', SendQuiz);
            btnNext.innerHTML = 'Submit';
            console.log('Submit')
            observer.disconnect(); // Stop observing once done
        }
    }
});

// Observe changes in the child list of the answers container
observer.observe(answers, { childList: true });

function NextButtonBinderEvent() {
    const btnNextDiv = answers.querySelector('#btnNextDiv');
    console.log(btnNextDiv)
    if (btnNextDiv !== null) {
        NextButtonEvent(btnNextDiv);
    }
    else {
        console.log(btnNextDiv)
    }
}

function NextButtonEvent(btnNextDiv) {
    StoreResponse(selectedQuestion, selectedOption);
    RequestNextQ();
    solvedQ++;
    qCount.innerHTML = `${solvedQ}/${totalQ}`;
}

function RequestNextQ() {
    //clear the old question and options
    question.innerHTML = '';
    answers.innerHTML = '';

    //Request next question
    GetQuiz();
}

let selectedOption;
function OptionsClickEvent(option, id) {
    option.addEventListener('click', function () {
        OptionsDefault();
        option.style.border = '1px solid #00A98F';
        option.style.color = '#00A98F';
        selectedOption = id;
    });
}

function OptionsDefault() {
    const mcqElements = document.querySelectorAll('.mcq');
    if (mcqElements.length > 0) {
        mcqElements.forEach(element => {
            element.style.border = '1px solid #919191';
            element.style.color = 'black';
        });
    }
}

const userResponses = [];
function StoreResponse(questionId, selectedOption) {
    const existingResponse = userResponses.find(response => response.questionId === questionId);

    if (existingResponse) {
        existingResponse.selectedOption = selectedOption;
    } else {
        userResponses.push({ questionId, selectedOption });
    }
}

async function SendQuiz() {
    StoreResponse(selectedQuestion, selectedOption);
    let url = `/quiz/send`

    try {
        const body = JSON.stringify({ data: userResponses });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data !== undefined) {
                localStorage.setItem('result', JSON.stringify(response.data));
                window.location.assign('quiz_result.html');
            } else {
                alert('No information found.');
                history.back();
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

function startCountdown(minutes) {
    let time = minutes * 60; // Convert minutes to seconds

    const countdownInterval = setInterval(() => {
        let minutes = Math.floor(time / 60);
        let seconds = time % 60;

        // Format the minutes and seconds to mm:ss
        minutes = minutes < 10 ? `0${minutes}` : minutes;
        seconds = seconds < 10 ? `0${seconds}` : seconds;

        // Update the innerHTML of 'txtCount'
        timer.innerHTML = `${minutes}:${seconds}`;

        // Decrease time by 1 second
        time--;

        // Stop countdown when time reaches 0
        if (time < 0) {
            clearInterval(countdownInterval);
            alert('Quiz time over!');
            history.back();
        }
    }, 1000);
}