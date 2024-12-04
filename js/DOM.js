function GetCourseCard(uuid, name, author, duration, rating, ap, dp) {
    const div = document.createElement('div');
    div.className = 'courseDiv';

    div.innerHTML = `
    <div class="courseThumbnail">
        <img src="../assets/images/course.png" alt="">
    </div>
    <div class="courseInformation">
        <h3>${name}</h3>
        <p>By ${author}</p>
        <div>
            <img src="../assets/icons/clock.png" alt="clock">
            <p>${duration} week course</p>
            <p>|</p>
            <img src="assets/icons/star.png" alt="star">
            <p>${rating}</p>
        </div>
        <h3>₹${dp} <s>${ap}</s></h3>
        <button class="enrollBtn">
            Enroll now
        </button>
    </div>`;

    return div;
}

function GetOrdersCard(courseName, orderDate, orderWeeks, author, ap, dp, total) {
    const div = document.createElement('div');
    div.className = 'ordersList';

    div.innerHTML = `
    <div class="orderDetails">
        <div class="orderInfo">
            <h2 id="txtCourseName">${courseName}</h2>
            <p id="txtOrderDate">${orderDate}</p>
            <p id="txtOrderWeek">${orderWeeks} weeks</p>
        </div>
        <p id="txtAuthor">by ${author}</p>
        <div class="paymentDetails">
            <h3>Payment details</h3>
            <div>
                <p>Course Fee</p>
                <p id="courseFeeAmount">${ap}</p>
            </div>
            <div>
                <p>Discount</p>
                <p id="courseFeeAmount">${dp}</p>
            </div>
            <hr>
            <div>
                <p>Total</p>
                <p id="courseFeeAmount">${total}</p>
            </div>
        </div>
        <div class="downloadInvoiceContainer">
            <button id="btnDownloadInvoice">Download Invoice</button>
        </div>
    </div>`;

    return div;
}

function GetEnrollmentCard(courseName, startDate, endDate) {
    const div = document.createElement('div');
    div.className = 'courseInfo';

    div.innerHTML = `
        <img src="../assets/icons/course.png" alt="course">
        <h4>${courseName}</h4>
        <h4>${startDate}</h4>
        <h4>${endDate === null ? "-" : endDate}</h4>
        <button>Go To Training</button>`;

    return div;
}

function GetCourseInfoCard(topicName, duration, noOfSubTopics, noOfDemo) {
    const div = document.createElement('div');
    div.className = 'progressReportChild';

    div.innerHTML = `
    <div class="topicInfo">
        <div class="topicInfoLeft">
            <p class="topicName">${topicName}</p>
        </div>
        <div class="topicInfoRight">
            <div id="topicDurationGroup">
                <img src="../assets/icons/clock.png" alt="clock">
                <p id="txtTopicDuration">${duration}</p>
            </div>
            <div id="topicsNumberGroup">
                <img src="../assets/icons/chart.png" alt="chart">
                <p id="txtNoOfTopics">${noOfSubTopics} Topics</p>
            </div>
            <div id="topicDemoGroup">
                <img src="../assets/icons/play.png" alt="play">
                <p id="txtNoOfDemos">${noOfDemo} Demo videos inside</p>
            </div>
            <img id="btnExpand" src="../assets/icons/arrowDown.png" alt="arrow down">
        </div>
    </div>
    <div id="courseContainer">
    </div>`;

    return div;
}

function GetSubTopicsCard(title, id, v) {
    const div = document.createElement('div');
    div.className = 'course';

    div.innerHTML = `
        <video class="courseMain" controls controlsList="nodownload" oncontextmenu="return false;" onplay="CheckTopic(${id})">
            <source src="${v}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="title">
            <p>${title}</p>
        </div>`;

    return div;
}

function GetCourseContainer() {
    const div = document.createElement('div');
    div.className = 'courseContainer';
    return div;
}

function GetMcq($option) {
    const div = document.createElement('div');
    div.className = 'mcq';

    div.innerHTML = `<h3>${$option}</h3>`;

    return div;
}

function GetBtnNext() {
    const div = document.createElement('div');
    div.id = 'btnNextDiv';

    div.innerHTML = `<button id="btnNext">Next</button>`;

    return div;
}

function GetResultCard(question, answers) {
    const div = document.createElement('div');
    div.id = 'main-containers';
    let correctAnswer = '-';
    let explaination = '-';

    let htmlContent = `<div id="questions">
                    <div id="q-container">
                        <div id="question">
                            <p>${question}</p>
                        </div>
                        <div id="options">`;

    answers.forEach(item => {
        if (item.correct === 1) {
            correctAnswer = item.answer;
            explaination = item.explaination;
        }
        htmlContent += `<div ${(item.correct === 0 && item.selected !== undefined) ? `style="border-color: red"` : ``} ${item.correct === 1 ? `style="border-color: green;"` : ``} id="option">
            <p ${(item.correct === 0 && item.selected !== undefined) ? `style="color: red"` : ``} ${item.correct === 1 ? `style="color: green;"` : ``} >${item.answer}</p>
        </div>`
    });

    htmlContent += `</div>
                    </div>
                </div>
                <div id="explainations">
                    <div id="e-container">
                        <p style="color: green; font-size: 22px;">Correct Answer</p>
                        <p>${correctAnswer}</p>
                        <p style="color: green; font-size: 22px;">Explaination</p>
                        <p>${explaination}</p>
                        <button id="btnConceptVid">Concept Video</button>
                    </div>
                </div>`;

    div.innerHTML = htmlContent;

    return div;
}