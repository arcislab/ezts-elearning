const courseName = document.getElementById('courseName');
const nameOfUser = document.getElementById("nameOfUser");
const endDate = document.getElementById('endDate');
const duration = document.getElementById('duration');
const totalVideos = document.getElementById('totalVideos');
const totalProjects = document.getElementById('totalProjects');
const downloadableContent = document.getElementById('downloadableContent');
const btnBack = document.getElementById('btnBack');
const progressReport = document.getElementById('progressReport');
const btnQuizProceed = document.getElementById('btnQuizProceed');
const dialogMain = document.getElementsByClassName('dialogMain')[0];
const btnEnroll = document.getElementById('btnEnroll');

let selectedTopic;

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

btnEnroll.addEventListener('click', function () {
    if (UserLoggedIn()) {
        window.location.assign(`./orderPlace.php?user=${localStorage.getItem("u")}&course=${getQueryParam('course')}&name=${courseName.innerHTML}`);
    } else {
        window.location.assign(`../login.html?redirect=orderPlace.php&user=${localStorage.getItem("u")}&course=${getQueryParam('course')}&name=${courseName.innerHTML}`);
    }
});

addEventListener("DOMContentLoaded", (event) => {
    btnBack.addEventListener('click', function () {
        history.back();
    });
    btnQuizProceed.addEventListener('click', function () {
        RequestQuiz();
    });
    if (dialogMain != undefined) {
        dialogMain.addEventListener('click', function () {
            dialogMain.style.visibility = 'hidden';
        });
    }
    LoadInfo();
});

async function CheckQuiz() {
    let url = `/quiz/check`

    try {
        const body = JSON.stringify({ course_topic: selectedTopic });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data.allowed !== undefined) {
                const qAllow = response.data.allowed;
                if (qAllow) {
                    if (dialogMain.style.visibility == 'hidden' || dialogMain.style.visibility == '') {
                        dialogMain.style.visibility = 'visible';
                    }
                    console.log(response.data.time);
                    if (response.data.time !== null) {
                        const txtQuizTime = dialogMain.querySelector('#txtQuizTime');
                        txtQuizTime.innerHTML = response.data.time;
                    }
                }
                else {
                    if (dialogMain != undefined) {
                        dialogMain.style.visibility = 'hidden';
                    }
                }
            } else {
                console.log('No information found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function RequestQuiz() {
    let url = `/quiz/request`

    try {
        const body = JSON.stringify({ course_topic: selectedTopic });
        const response = await CallApi(url, body);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response.status === 200) {
            if (response.data.uuid != null) {
                localStorage.setItem('qId', response.data.uuid);
            }
            window.location.assign('quiz.html');
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function LoadInfo() {
    if (nameOfUser !== undefined) {
        nameOfUser.innerHTML = localStorage.getItem('u_name');
    }
    let url = `/courses/info`

    try {
        let response;
        if (UserLoggedIn()) {
            const body = JSON.stringify({ course: getQueryParam('course') });
            response = await CallApi(url, body);
        } else {
            url += `?course=${getQueryParam('course')}`;
            response = await CallApi(url, null, 'GET');
        }
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data.data !== null) {
                const info = response.data.data;
                if (courseName !== undefined) {
                    courseName.innerHTML = info.course_name;
                }
                if (endDate !== undefined) {
                    endDate.innerHTML = info.expiry;
                }
                if (duration !== undefined) {
                    duration.innerHTML = `${info.duration} hours of learning`;
                }
                if (totalVideos !== undefined) {
                    totalVideos.innerHTML = `${info.total_videos} video tutorials`;
                }
                if (totalProjects !== undefined) {
                    totalProjects.innerHTML = `${info.total_projects} projects`;
                }

                if (info.downloadable_content !== null || info.downloadable_content <= 0) {
                    if (downloadableContent != undefined) {
                        downloadableContent.style.display = 'none';
                    }
                }
                if (response.data.data?.topics?.length > 0) {
                    response.data.data.topics.forEach(topic => {
                        const topicInfo = GetCourseInfoCard(topic.name, topic.duration, topic.total_sub_topics, topic.demo_videos);
                        if (progressReport !== undefined && topicInfo !== undefined) {
                            progressReport.appendChild(topicInfo);
                            const btnExpand = topicInfo.querySelector('#btnExpand');
                            if (btnExpand !== undefined) {
                                if (topic.access !== null) {
                                    if (topic.access) {
                                        btnExpand.style.cursor = 'pointer';
                                        btnExpand.addEventListener('click', async () => {
                                            LoadCourse(topic.uuid, topicInfo, topic.provide_quiz);
                                        });
                                    }
                                    else {
                                        btnExpand.style.cursor = 'no-drop';
                                        const cardTitle = topicInfo.querySelector('.topicName');
                                        cardTitle.style.color = '#969696';
                                    }
                                }
                            }
                        }
                    });
                }
                else {
                    console.log('No topics found')
                }
            } else {
                console.log('No information found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function LoadCourse(uuid, topicInfo, qAllow) {
    let url = `/courses/sub-topics`

    try {
        let response;
        if (UserLoggedIn()) {
            const body = JSON.stringify({ course_topic: uuid });
            response = await CallApi(url, body);
        } else {
            url += `?course_topic=${uuid}`;
            response = await CallApi(url, null, 'GET');
        }
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            console.log(topicInfo)
            const courseContainer = topicInfo.querySelector('#courseContainer');
            //Clear existing elements to avoid duplicate placement
            while (courseContainer.firstChild) {
                courseContainer.removeChild(courseContainer.firstChild);
            }
            if (response.data.data?.length > 0) {
                response.data.data.forEach(subtopic => {
                    const topicInfo = GetSubTopicsCard(subtopic.topic_name, subtopic.uuid, subtopic.video_url);
                    if (courseContainer !== undefined) {
                        courseContainer.appendChild(topicInfo);
                    }
                });

                const btnQuiz = document.createElement('button');
                btnQuiz.id = 'btnTakeQuiz'
                btnQuiz.innerHTML = 'Take Quiz';
                courseContainer.appendChild(btnQuiz);
                if (!qAllow) {
                    btnQuiz.style.cursor = 'not-allowed';
                    btnQuiz.disabled = true;
                }
                else {
                    btnQuiz.addEventListener('click', function () {
                        selectedTopic = uuid;
                        localStorage.setItem('t', selectedTopic);
                        CheckQuiz();
                    });
                }

                if (courseContainer !== undefined) {
                    console.log(courseContainer.style.display)
                    if (courseContainer.style.display === '' || courseContainer.style.display === 'none') {
                        courseContainer.style.display = 'flex';
                    }
                    else {
                        courseContainer.style.display = 'none';
                    }
                }
            } else {
                console.log('No information found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function ManageVideo(element) {
    if (element) {
        const subtopicId = element.getAttribute('v');
        if (subtopicId) {
            if (!element.paused && !element.ended) {
                // Declare intervalId outside to manage clearInterval later
                const intervalId = setInterval(() => {
                    SendTimeUpdate(element.currentTime.toFixed(1), subtopicId);
                }, 15000);

                // Clear the interval when video pauses or ends
                element.addEventListener('pause', () => clearInterval(intervalId));
                element.addEventListener('ended', () => clearInterval(intervalId));
            }
        }
    }
}

async function SendTimeUpdate(time, subtopic) {
    let url = `/courses/t`

    try {
        const body = JSON.stringify({ t: time, s: subtopic });
        const response = await CallApi(url, body);
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}