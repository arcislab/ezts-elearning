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

function GetSubTopicsCard(title) {
    const div = document.createElement('div');
    div.className = 'course';

    div.innerHTML = `
        <div class="courseMain">
        </div>
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