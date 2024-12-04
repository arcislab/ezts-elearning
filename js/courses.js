const coursesList = document.querySelector('.coursesList');

addEventListener("DOMContentLoaded", async () => {
    if (coursesList) {
        url = `/courses`;
        const t = getQueryParam('type');
        const body = JSON.stringify({type: t});

        try {
            const response = await CallApi(url, body);
            if (response.status === 401) {
                console.error(`Error: ${response.status} - ${response.statusText}`);
                alert(response.data.message);
                return;
            }

            let htmlContent = '';
            if (response) {
                if (response.status === 200) {
                    if (response.data.data?.length > 0) {
                        response.data.data.forEach(course => {
                            htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                            if (htmlContent) {
                                coursesList.appendChild(htmlContent);
                                const btnEnroll = htmlContent.querySelector(".enrollBtn");
                                console.log(htmlContent);
                                btnEnroll.addEventListener('click', async () => {
                                    window.location.assign(`./orderPlace.php?user=${localStorage.getItem("u")}&course=${course.uuid}&name=${course.name}`);
                                });
                            }
                        });
                    } else {
                        console.log('No courses found.');
                    }
                } else {
                    console.error(`Error: ${response.status} - ${response.statusText}`);
                }
            }
        } catch (error) {
            console.error('Error fetching course types:', error);
            return null;
        }
    }
});

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}