addEventListener("DOMContentLoaded", (event) => {
    GetCourses();
    // GetBlogs();
});

async function GetCourses() {
    let url = `/courses`;
    const body = JSON.stringify({ count: 3 });
    const response = await CallApi(url, body);
    const main = document.querySelector('.coursesDiv .coursesDivList');
    if(main !== undefined){
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(course => {
                    // const course = item.course;
                    htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                    // console.log(htmlContent)
                    if (htmlContent) {
                        main.appendChild(htmlContent);
                        const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        btnEnroll.addEventListener('click', async () => {
                            console.log(`Enrolling for ${course.uuid}`);
                        });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}

async function GetBlogs(){
    let url = `/blogs`;
    const body = JSON.stringify({ count: 3 });
    const response = await CallApi(url, body);
    const main = document.querySelector('.coursesDiv .coursesDivList');
    if(main !== undefined){
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(item => {
                    const course = item.course;
                    htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                    console.log(htmlContent)
                    if (htmlContent) {
                        main.appendChild(htmlContent);
                        const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        btnEnroll.addEventListener('click', async () => {
                            console.log(`Enrolling for ${course.uuid}`);
                        });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}