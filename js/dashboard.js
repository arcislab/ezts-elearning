addEventListener("DOMContentLoaded", (event) => {
    const buttons = document.getElementsByClassName("getStartedBtn");
    Array.from(buttons).forEach(button => {
        button.addEventListener('click', function () {
            window.location.assign("./login.html");
        });
    });
    LoadDash();
});

function LoadDash() {
    localStorage.setItem("u", 2)
    //Get courses types
    GetCourseTypes();
    //Greet user
    GreetUser(localStorage.getItem("u"));

    //Get courses
    GetCourses();
    GetEnrolledCourses();
    GetCoursesAll(1)
    GetBookmarkedCourses(localStorage.getItem("u"));
    GetOrders(localStorage.getItem("u"));
}

async function GetCourseTypes() {
    let url = `/courses-types`

    try {
        const main = document.querySelector('.body .categories .categoriesChild');
        const response = await CallApi(url);
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(type => {
                    //Populate courses in stack control
                    const heading = document.createElement('p');
                    // console.log(htmlContent)
                    if (heading) {
                        heading.innerHTML = type.type;
                        main.appendChild(heading);
                        heading.addEventListener('click', async () => {
                            window.location.href = `./courses.html?type=${type.uuid}`;
                        });
                        // const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        // btnEnroll.addEventListener('click', async () => {
                        //     console.log(`Enrolling for ${course.uuid}`);
                        // });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }

    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

async function GreetUser($userid) {
    let url = `/user/info`

    try {
        const main = document.querySelector('.greet');
        if (main !== null) {
            const body = JSON.stringify({ user: $userid });
            const response = await CallApi(url, body);
            if (response.status === 401) {
                console.error(`Error: ${response.status} - ${response.statusText}`);
                return;
            }

            if (response) {
                if (response.data.data?.length > 0) {
                    response.data.data.forEach(user => {
                        //Populate courses in stack control
                        const txtGreet = document.createElement('h2');
                        // console.log(htmlContent)
                        if (txtGreet != undefined) {
                            localStorage.setItem("u_name", user.first_name);
                            txtGreet.innerHTML = `Hi ${user.first_name}`;
                            main.appendChild(txtGreet);
                            // const btnEnroll = htmlContent.querySelector(".enrollBtn");
                            // btnEnroll.addEventListener('click', async () => {
                            //     console.log(`Enrolling for ${course.uuid}`);
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

async function GetEnrolledCourses() {
    let url = `/courses/enrolled`;
    const body = JSON.stringify({ user: localStorage.getItem("u") });
    const response = await CallApi(url, body);
    const main = document.querySelector('.enrollment .body');
    if (main !== null) {
        let htmlContent = '';
        if (response) {
            ChangeAnaly(response.data.total, response.data.active, response.data.completed);
            if (response.data.data?.length > 0) {
                response.data.data.forEach(course => {
                    htmlContent = GetEnrollmentCard(course.name, course.start_date, course.end_date);
                    if (htmlContent) {
                        main.appendChild(htmlContent);
                        const btnTraining = htmlContent.querySelector('.courseInfo button');
                        if (btnTraining !== undefined) {
                            btnTraining.addEventListener('click', function () {
                                window.location.assign(`./courseInformation.html?course=${encodeURIComponent(course.uuid)}`);
                            });
                        }
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}

function ChangeAnaly(total, active, completed){
    const totalEle = document.querySelector('.totalCourses .courseBody h1');
    const activeEle = document.querySelector('.activeCourses .courseBody h1');
    const completedEle = document.querySelector('.completedCourses .courseBody h1');

    if(totalEle !== undefined){
        totalEle.innerHTML = total;
    }
    if(activeEle !== undefined){
        activeEle.innerHTML = active;
    }
    if(completedEle !== undefined){
        completedEle.innerHTML = completed;
    }
}


async function GetCourses() {
    let url = `/courses`;
    const body = JSON.stringify({ count: 3 });
    const response = await CallApi(url, body);
    const main = document.querySelector('.popular .coursesDivList');
    if (main !== null) {
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(course => {
                    htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                    if (htmlContent) {
                        main.appendChild(htmlContent);
                        const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        btnEnroll.addEventListener('click', async () => {
                            window.location.assign(`./orderPlace.php?user=${localStorage.getItem("u")}&course=${course.uuid}&name=${course.name}`);
                        });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}

async function GetCoursesAll($courseType) {
    let url = `/courses`;
    const body = JSON.stringify({ type: $courseType });
    const response = await CallApi(url, body);
    const popularCoursesDiv = document.querySelector('.selectedPopularCourses .coursesContainer');
    const featuredCoursesDiv = document.querySelector('.selectedFeaturedCourses .coursesContainer');
    const allCoursesDiv = document.querySelector('.selectedAllCourses .coursesContainer');
    console.log(allCoursesDiv)
    if (popularCoursesDiv !== null && featuredCoursesDiv !== null && allCoursesDiv !== null) {
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(item => {
                    const course = item.course; // Access the course object within the item
                    // Populate courses in stack control
                    htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                    if (htmlContent) {
                        popularCoursesDiv.appendChild(htmlContent);
                        let featuredCourseClone = htmlContent.cloneNode(true);
                        featuredCoursesDiv.appendChild(featuredCourseClone);

                        // Clone for allCoursesDiv and append
                        let allCourseClone = htmlContent.cloneNode(true);
                        allCoursesDiv.appendChild(allCourseClone);
                        const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        btnEnroll.addEventListener('click', async () => {
                            // Enrollment logic goes here
                        });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}

async function GetBookmarkedCourses($userid) {
    let url = `/bookmarks`;
    const body = JSON.stringify({ user: $userid });
    const response = await CallApi(url, body);
    const bookmarkedCoursesDiv = document.querySelector('.bookMarkedCourses .coursesContainer');
    if (bookmarkedCoursesDiv !== null) {
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(course => {
                    htmlContent = GetCourseCard(course.uuid, course.name, course.author, course.duration, 4, course.ap, course.dp);
                    if (htmlContent) {
                        bookmarkedCoursesDiv.appendChild(htmlContent);
                        const btnEnroll = htmlContent.querySelector(".enrollBtn");
                        btnEnroll.addEventListener('click', async () => {
                            // Enrollment logic goes here
                        });
                    }
                });
            } else {
                console.log('No courses found.');
            }
        }
    }
}

async function GetOrders($userid) {
    let url = `/orders`;
    const body = JSON.stringify({ user: $userid });
    const response = await CallApi(url, body);
    const bookmarkedCoursesDiv = document.querySelector('.ordersMain');
    if (bookmarkedCoursesDiv !== null) {
        let htmlContent = '';
        if (response) {
            if (response.data.data?.length > 0) {
                response.data.data.forEach(order => {
                    htmlContent = GetOrdersCard(order.course, order.date, 2, order.author, order.ap, order.discount, order.total);
                    if (htmlContent) {
                        bookmarkedCoursesDiv.appendChild(htmlContent);
                        const btnEnroll = htmlContent.querySelector("#btnDownloadInvoice");
                        btnEnroll.addEventListener('click', async () => {
                            // Invoice download logic goes here
                        });
                    }
                });
            } else {
                console.log('No orders found.');
            }
        }
    }
}

const lgt = document.getElementById('Btnlgt');
lgt.addEventListener('click', function(){
    Lgt();
});

async function Lgt(){
    let url = `/logout`;

    try {
        const response = await CallApi(url, null, 'GET');
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            alert(response.data.message);
            return;
        }

        if (response) {
            if (response.status === 200) {
                window.location.href = 'http://148.135.137.222'
            } else {
                console.error(`Error: ${response.status} - ${response.statusText}`);
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}
