let topicSelected;
let subTopicSelected;
let quizSelected;
let questionSelected;
let answerSelected;
let currentDialogSelection;
let currentSelection;

const courses = document.getElementById('courses');
const courseTopics = document.getElementById('courseTopics');
const courseSubTopics = document.getElementById('courseSubTopics');
const proccedEle = document.getElementById('btnProceed');
const questionsList = document.getElementsByClassName('questionsList')[0];
const quizzesMain = document.getElementsByClassName('quizesMain')[0];
const main = document.getElementsByClassName('main')[0];
const btnCreateQuiz = document.getElementById('btnCreateQuiz');
const btnAdd = document.getElementById('btnAdd');
const btnBack = document.getElementById('btnBack');
const btnDialogAdd = document.getElementById('btnDialogAdd');
const dialogMain = document.getElementsByClassName('dialogMain')[0];
const dialog = document.getElementsByClassName('dialog')[0];
const dialogBody = document.getElementById('dialogBody');
const btnAddAnswer = document.getElementById('btnAddAnswer');

document.addEventListener("DOMContentLoaded", async () => {
    await FetchCourses();
    if (courses != undefined) {
        if (courses.options.length > 0) {
            const firstOptionValue = courses.options[0].value;
            FetchCourseTopics(firstOptionValue);
        }
    }

    const selectElement = document.getElementById('courses');
    if (selectElement != undefined) {
        selectElement.addEventListener('change', (event) => {
            console.log('changes')
            const selectedValue = event.target.value;
            FetchCourseTopics(selectedValue);
        });
    }

    if (btnAdd != undefined) {
        btnAdd.addEventListener('click', LoadQuestionsDOMInDialog);
    }

    if (btnCreateQuiz != undefined) {
        btnCreateQuiz.addEventListener('click', async () => {
            dialogBody.innerHTML = '';
            dialog.style.height = "50%";
            dialogBody.appendChild(LoadQuizDOM());
            currentDialogSelection = "Quiz";
            AutoFillQuizDetails();
            dialogMain.style.visibility = 'visible';
            // btnDialogAdd.removeEventListener()
            RebindAddEvents();
        });
    }

    if (dialogMain != undefined) {
        dialogMain.addEventListener('click', function () {
            dialogMain.style.visibility = 'hidden';
            if (btnAddAnswer.style.visibility == 'visible') {
                btnAddAnswer.style.visibility = 'hidden';
            }
        });
    }

    if (dialog != undefined) {
        dialog.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    }

    BindGetQuizzes();
});

function GetQuiz() {

}

function LoadQuestionsDOMInDialog(updating = false) {
    dialog.style.height = '40%';
    dialogMain.style.visibility = 'visible';
    dialogBody.innerHTML = '';
    dialogBody.appendChild(LoadQuestionsDOM());
    currentDialogSelection = "Question";
    btnDialogAdd.innerHTML = 'Add';
    if (updating === true) {
        AutoFillQuestionDetails();
    }
    dialogMain.style.visibility = 'visible';
    RebindAddEvents();
}

async function AutoFillQuestionDetails() {
    let url = `/quiz/question`
    const body = JSON.stringify({ question: questionSelected });
    const response = await CallApi(url, body);

    try {
        const txtQuestion = dialogBody.querySelector('.questionDiv textarea');
        if (response) {
            if (response.data.question) {
                txtQuestion.value = response.data.question.value;
                btnDialogAdd.innerHTML = 'Update';
            } else {
                console.log('No question found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

function RebindAddEvents() {
    if (currentDialogSelection === "Quiz") {
        btnDialogAdd.addEventListener('click', QuizDialogEvent, { once: true }); // once: true helps with preventing recurring events binding
        btnDialogAdd.removeEventListener('click', QuestionsDialogEvent);
        btnDialogAdd.removeEventListener('click', AnswersDialogEvent);
    } else if (currentDialogSelection === "Question") {
        btnDialogAdd.addEventListener('click', QuestionsDialogEvent, { once: true });
        btnDialogAdd.removeEventListener('click', QuizDialogEvent);
        btnDialogAdd.removeEventListener('click', AnswersDialogEvent);
    } else if (currentDialogSelection === "Answer") {
        btnDialogAdd.addEventListener('click', AnswersDialogEvent, { once: true });
        btnDialogAdd.removeEventListener('click', QuizDialogEvent);
        btnDialogAdd.removeEventListener('click', QuestionsDialogEvent);
    }
}

btnBack.addEventListener('click', function () {
    if (currentSelection == "Answers") {
        btnAdd.removeEventListener('click', LoadAnswersDOMInDialog);
        btnAdd.addEventListener('click', LoadQuestionsDOMInDialog);
        GetQuestions(topicSelected);
        btnAdd.innerHTML = 'Add New Question';
        currentDialogSelection = "Question";
        RebindAddEvents();
    }
});

async function QuestionsDialogEvent() {
    const txtQuestion = dialogBody.querySelector('.questionDiv textarea');
    if (txtQuestion != undefined) {
        let response;
        if (btnDialogAdd.innerHTML == 'Add') {
            const jsonBody = {
                quiz: quizSelected,
                question: txtQuestion.value
            };
            response = await CallApi('/quiz/questions/manage', JSON.stringify(jsonBody));
        } else {
            const jsonBody = {
                uuid: questionSelected,
                question: txtQuestion.value
            };
            response = await CallApi('/quiz/questions/manage', JSON.stringify(jsonBody), 'PUT');
        }
        if (response.status === 200) {
            GetQuestions(topicSelected);
            Refresh();
            alert(`Success`);
        }
        else {
            console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
            // alert(`Failed`);
        }
    }
    else {
        alert('Question is empty')
    }
}

async function QuizDialogEvent() {
    const gtYes = document.getElementById('radioYes');
    const txtTime = document.getElementById('txtTime');
    let time = txtTime.value;
    if (topicSelected != undefined) {
        let response;
        if (btnDialogAdd.innerHTML == 'Add') {
            const jsonBody = {
                topic: topicSelected,
                gt: gtYes.checked,
                time: time.value = (time == null || time == "") ? 1 : time
            };
            response = await CallApi('/quiz/manage', JSON.stringify(jsonBody));
        } else {
            const jsonBody = {
                uuid: quizSelected,
                gt: gtYes.checked,
                time: time.value = (time == null || time == "") ? 1 : time
            };
            response = await CallApi('/quiz/manage', JSON.stringify(jsonBody), 'PUT');
        }
        if (response.status === 200) {
            Refresh();
            alert(`Success`);
        }
        else {
            console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
            // alert(`Failed`);
        }
    }
    else {
        alert('Course topic not selected!')
    }
}

async function BindGetQuizzes() {
    if (proccedEle != undefined) {
        proccedEle.addEventListener('click', async () => {
            topicSelected = courseTopics.value;
            // subTopicSelected = courseSubTopics.value;
            GetQuestions(topicSelected);
        });
    }
}

async function AutoFillQuizDetails() {
    let url = `/quiz`
    const body = JSON.stringify({ topic: topicSelected });
    const response = await CallApi(url, body);

    try {
        const gtYes = document.getElementById('radioYes');
        const gtNo = document.getElementById('radioNo');
        const txtTime = document.getElementById('txtTime');
        if (response) {
            if (response.data.quiz) {
                quizSelected = response.data.quiz.uuid;
                if (response.data.quiz.gt == true) {
                    gtYes.checked = true;
                }
                else {
                    gtNo.checked = true;
                }

                txtTime.value = response.data.quiz.time;
                btnDialogAdd.innerHTML = 'Update';
            } else {
                console.log('No courses found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

function Refresh() {
    if (dialogMain != undefined) {
        dialogMain.style.visibility = 'hidden';
    }
    if (btnCreateQuiz != undefined) {
        btnCreateQuiz.innerHTML = 'Update Quiz'
        btnAdd.style.display = 'block';
    }
}

async function FetchCourses() {
    let url = `/courses`
    const response = await CallApi(url);

    try {
        if (courses !== null) {
            let htmlContent = '';
            if (response) {
                if (response.data.data?.length > 0) {
                    response.data.data.forEach(item => {
                        const course = item.course;
                        //Populate courses in stack control
                        htmlContent = GetCourses(course.uuid, course.name);
                        if (htmlContent) {
                            courses.innerHTML += htmlContent;
                            // const btnEnroll = htmlContent.querySelector(".enrollBtn");
                            // btnEnroll.addEventListener('click', async () => {

                            // });
                        }
                    });
                } else {
                    console.log('No courses found.');
                }
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function FetchCourseTopics(id) {
    let url = `/courses/topics`
    const body = JSON.stringify({ course: id });
    const response = await CallApi(url, body);

    try {
        courseTopics.innerHTML = '';
        if (courseTopics !== null) {
            let htmlContent = '';
            if (response) {
                if (response.data.topics?.length > 0) {
                    response.data.topics.forEach(topic => {
                        //Populate courses in stack control
                        htmlContent = GetCourses(topic.uuid, topic.topic_name);
                        if (htmlContent) {
                            courseTopics.innerHTML += htmlContent;
                            // const btnEnroll = htmlContent.querySelector(".enrollBtn");
                            // btnEnroll.addEventListener('click', async () => {

                            // });
                        }
                    });
                    const firstOptionValue = courseTopics.options[0].value;
                    // FetchCourseSubTopics(firstOptionValue);
                } else {
                    console.log('No topics found.');
                }
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function FetchCourseSubTopics(id) {
    let url = `/courses/sub-topics`
    const body = JSON.stringify({ course_topic: id });
    const response = await CallApi(url, body);

    try {
        courseSubTopics.innerHTML = '';
        if (courseSubTopics !== null) {
            let htmlContent = '';
            if (response) {
                if (response.data.sub_topics?.length > 0) {
                    response.data.sub_topics.forEach(topic => {
                        //Populate courses in stack control
                        htmlContent = GetCourses(topic.uuid, topic.topic_name);
                        if (htmlContent) {
                            courseSubTopics.innerHTML += htmlContent;
                            // const btnEnroll = htmlContent.querySelector(".enrollBtn");
                            // btnEnroll.addEventListener('click', async () => {

                            // });
                        }
                    });
                } else {
                    console.log('No sub topics found.');
                }
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function GetQuestions(id) {
    currentSelection = "Questions";
    let url = `/quiz`
    const body = JSON.stringify({ topic: id });
    const response = await CallApi(url, body);

    try {
        if (questionsList != undefined) {
            questionsList.innerHTML = '';
            if (questionsList !== null) {
                quizzesMain.style.display = 'flex';
                main.style.display = 'none';
                let htmlContent = '';
                if (response) {
                    if (response.data.quiz) {
                        btnAdd.style.display = 'block';
                        quizSelected = response.data.quiz.uuid;
                        if (btnCreateQuiz != undefined) {
                            btnCreateQuiz.innerHTML = 'Update Quiz'
                        }
                        if (response.data.questions.length > 0) {
                            response.data.questions.forEach(question => {
                                //Populate courses in stack control
                                htmlContent = GetQuestionsSelectors(question.question);
                                if (htmlContent) {
                                    questionsList.appendChild(htmlContent);

                                    const btnViewAnswers = htmlContent.querySelector(".btnViewAnswers");
                                    btnViewAnswers.addEventListener('click', async () => {
                                        questionSelected = question.uuid;
                                        GetAnswers(question.uuid);
                                        btnAdd.innerHTML = 'Add New Answer';

                                        btnAdd.removeEventListener('click', LoadQuestionsDOMInDialog);
                                        btnAdd.addEventListener('click', LoadAnswersDOMInDialog);
                                        currentSelection = "Answers";
                                    });
                                    const btnEdit = htmlContent.querySelector(".btnEdit");
                                    btnEdit.addEventListener('click', async () => {
                                        questionSelected = question.uuid;
                                        LoadQuestionsDOMInDialog(true);
                                    });
                                    const btnDelete = htmlContent.querySelector(".btnDelete");
                                    btnDelete.addEventListener('click', async () => {
                                        DeleteQuestion(question.uuid);
                                    });
                                }
                            });
                        }
                    }
                    else {
                        if (btnAdd != undefined) {
                            btnAdd.style.display = 'none';
                        }
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error fetching quiz:', error);
        return null;
    }
}

async function DeleteQuestion(id) {
    const jsonBody = {
        uuid: id
    };
    const response = await CallApi('/quiz/questions/manage', JSON.stringify(jsonBody), 'DELETE');
    if (response.status === 200) {
        GetQuestions(topicSelected);
        Refresh();
        alert(`Success`);
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        // alert(`Failed`);
    }
}

function LoadAnswersDOMInDialog(updating = false) {
    dialog.style.height = '80%';
    dialogMain.style.visibility = 'visible';
    dialogBody.innerHTML = '';
    dialogBody.appendChild(LoadAnswersDOM());
    currentDialogSelection = "Answer";
    btnDialogAdd.innerHTML = 'Add';
    if (updating === true) {
        AutoFillAnswerDetails();
    }
    dialogMain.style.visibility = 'visible';
    RebindAddEvents();
}

async function AutoFillAnswerDetails() {
    let url = `/quiz/answer`
    const body = JSON.stringify({ uuid: answerSelected });
    const response = await CallApi(url, body);

    try {
        const txtAnswer = dialogBody.querySelector('.questionDiv textarea');
        const correctAnswer = dialogBody.querySelector('#correctAnswer');
        const txtExplaination = dialogBody.querySelector('#txtExplaination');
        if (response) {
            if (response.data.answer) {
                txtAnswer.value = response.data.answer.value;
                correctAnswer.checked = response.data.answer.correct;
                txtExplaination.value = response.data.answer.explaination;
                btnDialogAdd.innerHTML = 'Update';
            } else {
                console.log('No question found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function AnswersDialogEvent() {
    const txtAnswer = dialogBody.querySelector('.questionDiv textarea');
    const correct = dialogBody.querySelector('#correctAnswer');
    const explaination = dialogBody.querySelector('#txtExplaination');
    console.log(dialogBody)
    if (txtAnswer != undefined) {
        let response;
        if (btnDialogAdd.innerHTML == 'Add') {
            const jsonBody = {
                question: questionSelected,
                answer: txtAnswer.value,
                sub_topic: topicSelected,
                correct: correct.checked,
                explaination: explaination.value
            };
            response = await CallApi('/quiz/answers/manage', JSON.stringify(jsonBody));
        } else {
            const jsonBody = {
                uuid: answerSelected,
                answer: txtAnswer.value,
                correct: correct.checked,
                explaination: explaination.value
            };
            response = await CallApi('/quiz/answers/manage', JSON.stringify(jsonBody), 'PUT');
        }
        if (response.status === 200) {
            await GetAnswers(questionSelected);
            Refresh();
            alert(`Success`);
        }
        else {
            console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
            // alert(`Failed`);
        }
    }
    else {
        alert('Question is empty')
    }
}

async function GetAnswers(id) {
    let url = `/quiz/answers`
    const body = JSON.stringify({ question: id });
    const response = await CallApi(url, body);

    try {
        if (questionsList != undefined) {
            questionsList.innerHTML = '';
            if (questionsList !== null) {
                quizzesMain.style.display = 'flex';
                main.style.display = 'none';
                let htmlContent = '';
                if (response) {
                    if (response.data.answers?.length > 0) {
                        response.data.answers.forEach(answer => {
                            //Populate courses in stack control

                            htmlContent = GetAnswersSelectors(answer.answer);
                            if (htmlContent) {
                                questionsList.appendChild(htmlContent);
                                const btnEdit = htmlContent.querySelector(".btnEdit");
                                btnEdit.addEventListener('click', async () => {
                                    answerSelected = answer.uuid;
                                    LoadAnswersDOMInDialog(true);

                                });
                                const btnDelete = htmlContent.querySelector(".btnDelete");
                                btnDelete.addEventListener('click', async () => {
                                    DeleteAnswer(answer.uuid);
                                });
                            }
                        });
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error fetching quiz:', error);
        return null;
    }
}

async function DeleteAnswer(id) {
    const jsonBody = {
        uuid: id
    };
    const response = await CallApi('/quiz/answers/manage', JSON.stringify(jsonBody), 'DELETE');
    if (response.status === 200) {
        await GetAnswers(questionSelected);
        Refresh();
        alert(`Success`);
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        // alert(`Failed`);
    }
}

function GetQuestionsSelectors(question) {
    const div = document.createElement('div');
    div.className = "courseType";
    div.innerHTML = `
                    <div class="info">
                        <p>${question}</p>
                    </div>
                    <div class="controls">
                        <div class="controlsChild">
                            <button class="btnViewAnswers">View Answers</button>
                            <button class="btnEdit">Edit</button>
                            <button class="btnDelete">Delete</button>
                        </div>
                    </div>`;
    return div;
}

function GetAnswersSelectors(answer) {
    const div = document.createElement('div');
    div.className = "courseType";
    div.innerHTML = `
                    <div class="info">
                        <p>${answer}</p>
                    </div>
                    <div class="controls">
                        <div class="controlsChild">
                            <button class="btnEdit">Edit</button>
                            <button class="btnDelete">Delete</button>
                        </div>
                    </div>`;
    return div;
}

function LoadQuizDOM() {
    const div = document.createElement('div');
    div.className = "newQuizDOM";
    div.innerHTML = `<h2>Create Quiz</h2>
                    <br>
                    <h3>Grand Quiz?</h3>
                    <div style="display: flex;">
                        <label for="radioYes">Yes</label><input id="radioYes" type="radio" name="grandTest" value="Yes">
                        <label for="radioNo" style="margin-left: 10px;">No</label><input id="radioNo" type="radio" name="grandTest" value="No" checked>
                    </div>
                    <h3>Time (in minutes)</h3>
                    <input type="number" id="txtTime">`;
    return div;
}

function LoadQuestionsDOM() {
    const div = document.createElement('div');
    div.className = "newQuestionDOM";
    div.innerHTML = `<div class="questionDiv">
                        <h3>Enter question</h3>
                        <textarea type="text" rows="10"></textarea>
                    </div>`;
    return div;
}

function LoadAnswersDOM() {
    const div = document.createElement('div');
    div.className = "newQuestionDOM";
    div.innerHTML = `<div class="questionDiv">
                        <h3>Enter answer</h3>
                        <textarea type="text"></textarea>
                    </div>
                    <div>
                        <label for="">Correct Answer</label>
                        <input type="checkbox" name="correctAnswer" id="correctAnswer">
                    </div>
                    <div>
                        <h3>Concept Video</h3>
                        <select name="txtConceptVideo" id="txtConceptVideo">
                            <option value="Option 1">Option 1</option>
                            <option value="Option 2">Option 2</option>
                            <option value="Option 3">Option 3</option>
                        </select>
                    </div>
                    <div>
                        <h3>Explaination</h3>
                        <textarea type="text" id="txtExplaination"></textarea>
                    </div>`;
    return div;
}