const courseName = document.getElementById('courseName');
const nameOfUser = document.getElementById("nameOfUser");
const endDate = document.getElementById('endDate');
const duration = document.getElementById('duration');
const totalVideos = document.getElementById('totalVideos');
const totalProjects = document.getElementById('totalProjects');
const downloadableContent = document.getElementById('downloadableContent');
const btnBack = document.getElementById('btnBack');
const progressReport = document.getElementById('progressReport');

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

addEventListener("DOMContentLoaded", (event) => {
    btnBack.addEventListener('click', function () {
        history.back();
    });
    LoadInfo();
});

async function LoadInfo() {
    if (nameOfUser !== undefined) {
        nameOfUser.innerHTML = localStorage.getItem('u_name');
    }
    let url = `/courses/info`

    try {
        const body = JSON.stringify({ course: getQueryParam('course'), user: getQueryParam('user') });
        const response = await CallApi(url, body);
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
                                btnExpand.addEventListener('click', async() => {
                                    LoadCourse(topic.id, topicInfo);
                                });
                            }
                        }
                    });
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

async function LoadCourse(uuid, topicInfo) {
    let url = `/courses/sub-topics`

    try {
        const body = JSON.stringify({ course_topic: uuid });
        const response = await CallApi(url, body);
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
            if (response.data.sub_topics?.length > 0) {
                response.data.sub_topics.forEach(subtopic => {
                    const topicInfo = GetSubTopicsCard(subtopic.topic_name);
                    if (courseContainer !== undefined) {
                        courseContainer.appendChild(topicInfo);
                    }
                });

                if (courseContainer !== undefined) {
                    console.log(courseContainer.style.display)
                    if(courseContainer.style.display === '' || courseContainer.style.display === 'none'){
                        courseContainer.style.display = 'flex';
                    }
                    else{
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

function LoadTopics() {

}