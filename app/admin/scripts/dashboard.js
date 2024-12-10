
let currentSelection;
const bodyElement = document.getElementsByClassName('body')[0];
const API_BASE_URL = 'http://148.135.137.222/api/v1';

const btnAdd = document.querySelector('.head #btnAdd');
const btnBack = document.querySelector('.head #btnBack');
const dialogMain = document.querySelector('.dialogMain');
let dialogHeading = document.querySelector('.dialogHeading');
let elementsholder = document.querySelector('.dialogMain .body .child');
const title = document.querySelector('.head h2');

//Selections ids
let courseTypeSelected;
let courseSelected;
let topicSelected;
let subtopicSelected;
let uuidSelectedForAction;

btnBack.addEventListener('click', function () {
    Back();
});

async function CallApi(url, jsonBody, method = null) {
    try {
        url = `${API_BASE_URL}${url}`;
        const response = await fetch(url, {
            credentials: 'same-origin', // the fix
            method: method == null ? 'POST' : method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: jsonBody,
        });

        console.log(`Calling: ${url}\nbody: ${jsonBody}`)
        if (response.status === 401) {
            alert(response.statusText)
            window.location.href = 'http://148.135.137.222/login.html';
        }

        if (!response.ok) {
            const errorData = await response.json();
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return {
                data: errorData,
                status: response.status,
            };
        }

        const rawResponseBody = await response.text();
        // console.log(`Raw Res: ${rawResponseBody}`);
        const data = JSON.parse(rawResponseBody);
        console.log(data)
        return {
            data: data,
            status: response.status,
        };
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error);

        // Return null for data and null status (or a placeholder if needed)
        return {
            data: null,
            status: null, // or undefined if no response is received from the server
        };
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    console.log('Load')
    if (currentSelection === undefined) {
        CoursesTypeLoad();
    }

    btnAdd.addEventListener('click', function () {
        btnSendData.innerHTML = "Add"
        // alert(currentSelection)
        if (currentSelection === "course types") {
            dialogMain.style.visibility = 'visible';
            dialogHeading.innerHTML = "Add course type";
        } else if (currentSelection === "course") {
            dialogMain.style.visibility = 'visible';
            dialogHeading.innerHTML = "Add course";
        } else if (currentSelection === "topics") {
            dialogMain.style.visibility = 'visible';
            dialogHeading.innerHTML = "Add topic";
        } else if (currentSelection === "sub topics") {
            dialogMain.style.visibility = 'visible';
            dialogHeading.innerHTML = "Add sub topic";
        }
        elementsholder.innerHTML = GetCRUDElements();
    });
});

function ViewCRUD(response = null) {
    btnSendData.innerHTML = "Update"
    if (currentSelection === "course types") {
        dialogMain.style.visibility = 'visible';
        dialogHeading.innerHTML = "Add course type";
    } else if (currentSelection === "course") {
        dialogMain.style.visibility = 'visible';
        dialogHeading.innerHTML = "Add course";
    } else if (currentSelection === "topics") {
        dialogMain.style.visibility = 'visible';
        dialogHeading.innerHTML = "Add course";
    } else if (currentSelection === "sub topics") {
        dialogMain.style.visibility = 'visible';
        dialogHeading.innerHTML = "Add course";
    }
    elementsholder.innerHTML = response === null ? GetCRUDElements() : GetCRUDElements(response);
}

const divElement = document.getElementsByClassName('dialogMain')[0];
const dialogElement = document.getElementsByClassName('dialog')[0];

divElement.addEventListener('click', function () {
    divElement.style.visibility = 'hidden';
});

dialogElement.addEventListener('click', function (event) {
    event.stopPropagation();
});

// Dynamic controls i.e. panel that holds the selectors
function GetStackControl(selectionType) {
    const div = document.createElement('div');
    div.className = 'selectors';

    div.innerHTML = `
        <div class="info">
            <p>${selectionType}</p>
        </div>
        <div class="controls">
            <div class="controlsChild">
                <button class="btnEdit">Edit</button>
                <button class="btnDelete">Delete</button>
            </div>
        </div>`;
    return div;
}

async function CoursesTypeLoad() {
    //Get course types
    let url = `/courses-types`
    const body = JSON.stringify({ count: 5 });
    const response = await CallApi(url, body);
    ClearBody();
    let htmlContent = '';
    if (response) {
        if (response.data.data?.length > 0) {
            response.data.data.forEach(courseType => {
                htmlContent = GetStackControl(courseType.type);
                if (htmlContent) {
                    CoursesTypeClickControl(htmlContent, courseType.uuid)
                    bodyElement.appendChild(htmlContent);
                }
            });
        } else {
            console.log('No courses types found.');
        }
        ChangeAddButton("Add course type");
    }

    currentSelection = "course types";
}

function CoursesTypeClickControl(div, uuid) {
    div.addEventListener('click', async function (event) {
        title.innerHTML = "Select Course";
        if (event.target.classList.contains('info')) {
            // console.log(`Clicked for UUID: ${uuid}`);
            courseTypeSelected = uuid;
            CoursesLoad();
        } else if (event.target.classList.contains('btnEdit')) {
            const response = await GetCoursesTypes(uuid);
            uuidSelectedForAction = uuid;
            ViewCRUD(response);
        } else if (event.target.classList.contains('btnDelete')) {
            uuidSelectedForAction = uuid;
            Delete();
        }
    });
}

async function CoursesLoad() {
    let url = `/courses`;
    const body = JSON.stringify({ type: courseTypeSelected });
    const response = await CallApi(url, body);
    ClearBody();

    let htmlContent = '';
    if (response) {
        if (response.data.data?.length > 0) {
            response.data.data.forEach(course => {
                //Populate courses in stack control
                htmlContent = GetStackControl(course.name);
                if (htmlContent) {
                    CoursesClickControl(htmlContent, course.uuid);
                    bodyElement.appendChild(htmlContent);
                }
            });
        } else {
            console.log('No courses found.');
        }
        ChangeAddButton("Add course");
    }
    currentSelection = "course";
}

function CoursesClickControl(div, uuid) {
    div.addEventListener('click', async function (event) {
        title.innerHTML = "Select Topic";
        if (event.target.classList.contains('info')) {
            // console.log(`Clicked for UUID: ${uuid}`);
            courseSelected = uuid;
            TopicsLoad();
        } else if (event.target.classList.contains('btnEdit')) {
            const response = await GetCourses(uuid);
            uuidSelectedForAction = uuid;
            ViewCRUD(response);
        } else if (event.target.classList.contains('btnDelete')) {
            uuidSelectedForAction = uuid;
            Delete();
        }
    });
}

async function TopicsLoad() {
    let url = `/courses/topics`;
    const body = JSON.stringify({ course: courseSelected });
    const response = await CallApi(url, body);
    ClearBody();

    let htmlContent = '';
    if (response) {
        if (response.data.topics?.length > 0) {
            response.data.topics.forEach(topic => {
                //Populate topics in stack control
                htmlContent = GetStackControl(topic.name);
                if (htmlContent) {
                    CoursesTopicControl(htmlContent, topic.id);
                    bodyElement.appendChild(htmlContent);
                }
            });
        } else {
            console.log('No topics found.');
        }
        ChangeAddButton("Add topic");
    }
    currentSelection = "topics";
}

function CoursesTopicControl(div, uuid) {
    div.addEventListener('click', async function (event) {
        title.innerHTML = "Select Sub-Topic";
        if (event.target.classList.contains('info')) {
            console.log(`Clicked for UUID: ${uuid}`);
            topicSelected = uuid;
            SubTopicsLoad();
        } else if (event.target.classList.contains('btnEdit')) {
            const response = await GetTopics(uuid);
            uuidSelectedForAction = uuid;
            ViewCRUD(response);
        } else if (event.target.classList.contains('btnDelete')) {
            uuidSelectedForAction = uuid;
            Delete();
        }
    });
}

async function SubTopicsLoad() {
    let url = `/courses/sub-topics`;
    const body = JSON.stringify({ course_topic: topicSelected });
    const response = await CallApi(url, body);
    ClearBody();

    let htmlContent = '';
    if (response) {
        if (response.data.sub_topics?.length > 0) {
            response.data.sub_topics.forEach(subtopic => {
                //Populate topics in stack control
                htmlContent = GetStackControl(subtopic.topic_name);
                if (htmlContent) {
                    bodyElement.appendChild(htmlContent);
                    subtopicSelected = subtopic.uuid;

                    const btnEdit = htmlContent.querySelector('.btnEdit');
                    const btnDelete = htmlContent.querySelector('.btnDelete');

                    btnEdit.addEventListener('click', async () => {
                        const response = await GetSubTopics(subtopic.uuid);
                        uuidSelectedForAction = subtopic.uuid;
                        ViewCRUD(response);
                        // BindCourseFile(elementsholder.innerHTML);
                    });

                    btnDelete.addEventListener('click', () => {
                        uuidSelectedForAction = subtopic.uuid;
                        Delete();
                    });
                }
            });
        } else {
            console.log('No sub topics found.');
        }
        ChangeAddButton("Add sub topic");
    }
    currentSelection = "sub topics";
}

function ClearBody() {
    while (bodyElement.firstChild) {
        bodyElement.removeChild(bodyElement.firstChild);
    }
}

function ChangeAddButton(value) {
    btnAdd.innerHTML = value;
    if (value === "course types") {
        btnBack.style.display = 'none';
    }
    else {
        btnBack.style.display = 'block';
    }
}

function GetCRUDElements(response = null) {
    if (response === null) {
        btnAction = 'Add';
    } else {
        btnAction = 'Update';
    }

    if (currentSelection === "course types") {
        return `<div class="child">
                    <div>
                        <p>Name</p>
                        <input type="text" id="txtCourseType" placeholder="Enter course type" value="${response === null ? "" : response}">
                    </div>
                </div>`;
    } else if (currentSelection === "course") {
        return `<div>
                        <p>Name</p>
                        <input type="text" id="txtCourseName" placeholder="Enter course name" value="${response?.name ?? ''}">
                    </div>
                    <div>
                        <p>Author</p>
                        <input type="text" id="txtAuthor" placeholder="Enter author" value="${response?.author ?? ''}">
                    </div>
                    <div>
                        <p>Duration</p>
                        <input type="number" id="txtDuration" placeholder="Enter duration in hours" value="${response?.duration ?? ''}">
                    </div>
                    <div>
                        <p>Actual price</p>
                        <input type="number" id="txtActualPrice" placeholder="Enter actual price" value="${response?.ap ?? ''}">
                    </div>
                    <div>
                        <p>Discount price</p>
                        <input type="number" id="txtDiscountPrice" placeholder="Enter discount price" value="${response?.dp ?? ''}">
                    </div>
                    <div>
                        <p>Expiry(in days)</p>
                        <input type="number" id="txtExpiry" placeholder="Enter expiry" value="${response?.expiry ?? ''}">
                    </div>`
    } else if (currentSelection === "topics") {
        return `<div>
                        <p>Name</p>
                        <input type="text" id="txtName" placeholder="Enter topic name" value="${response?.topic_name ?? ''}">
                    </div>
                    <div>
                        <p>Content URL</p>
                        <input type="text" id="txtContent" placeholder="Enter downloadable content url" value="${response?.content_url ?? ''}">
                    </div>`
    } else if (currentSelection === "sub topics") {
        return `<div>
                        <p>Name</p>
                        <input type="text" id="txtName" placeholder="Enter name of sub topic" value="${response?.topic_name ?? ''}">
                    </div>
                    <div>
                        <p>Video</p>
                        <input type="file" id="txtVideo" onchange="BindCourseFile(event)" value="${response?.video_url ?? ''}" accept=".mp4">
                    </div>
                    <div>
                        <p>Project</p>
                        <input type="file" id="txtProject" placeholder="Enter project url" value="${response?.project_url ?? ''}">
                    </div>
                    <div>
                        <p>Duration</p>
                        <input type="text" id="txtDuration" placeholder="Enter duration in hours" value="${response?.duration ?? ''}">
                    </div>
                    <div>
                        <p>Demo</p>
                        <input type="radio" name="rad" id="demoYes" ${response?.demo === 1 ? "checked" : response?.demo === null ? "checked" : ""}>Yes</input>
                        <input type="radio" name="rad" id="demoNo" ${response?.demo === 0 ? "checked" : ""}>No</input>
                    </div>`
    }
}

function BindCourseFile(event) {
    // console.log(code);
    // code.querySelector("#fileInput").addEventListener("change", function(event) {
    // });
    const file = event.target.files[0];
    if (file && file.type === "video/mp4") {
        const video = document.createElement("video");
        video.preload = "metadata";

        video.onloadedmetadata = function () {
            window.URL.revokeObjectURL(video.src); // Release the object URL after getting metadata
            const duration = video.duration;
            const hours = Math.floor(duration / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((duration % 3600) / 60).toString().padStart(2, '0');
            const seconds = Math.floor(duration % 60).toString().padStart(2, '0');
            const parent = event.target.parentElement.parentElement;
            if (parent) {
                console.log(parent);
                if (parent.querySelector("#txtDuration")) {
                    parent.querySelector("#txtDuration").value = `${hours}:${minutes}:${seconds}`;
                }
            }
        };

        video.src = URL.createObjectURL(file); // Set the video source
    } else {
        event.target.value = null;
        alert("Please select a valid .mp4 file.");
    }
}

async function Delete() {
    const jsonBody = {
        uuid: uuidSelectedForAction
    };
    const response = await CallApi(GetEP('d'), JSON.stringify(jsonBody));
    if (response.status === 200) {
        Refresh();
        alert(`Success`);
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        // alert(`Failed`);
    }
}

function Refresh() {
    if (currentSelection === "course types") {
        CoursesTypeLoad();
    } else if (currentSelection === "course") {
        CoursesLoad();
    } else if (currentSelection === "topics") {
        TopicsLoad();
    } else if (currentSelection === "sub topics") {
        SubTopicsLoad();
    }
}

function Back() {
    if (currentSelection === "course types") {
        title.innerHTML = "Select Course Type";
        currentSelection = "course types";
    } else if (currentSelection === "course") {
        title.innerHTML = "Select Course Type";
        currentSelection = "course types";
    } else if (currentSelection === "topics") {
        title.innerHTML = "Select Course";
        currentSelection = "course";
    } else if (currentSelection === "sub topics") {
        title.innerHTML = "Select Topic";
        currentSelection = "topics";
    }

    Refresh();
}

let btnAction = 'Add';
const btnSendData = document.querySelector('#btnSendData');
btnSendData.addEventListener('click', function (event) {
    SendData(event);
});

async function SendData(event) {
    const jsonBody = SerializeData();
    const response = await CallApi(GetEP(btnAction === 'Add' ? 'i' : 'u'), JSON.stringify(jsonBody));
    const before = event.target.innerHTML;
    event.target.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    if (response.status === 200) {
        Refresh();
        event.target.innerHTML = before;
        alert(`Success`);
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        // alert(`Failed`);
        event.target.innerHTML = before;
    }
}

function SerializeData() {
    if (currentSelection === "course types") {
        console.log(document.getElementById("txtCourseType"));
        const courseType = document.getElementById("txtCourseType").value;
        const result = {
            type: courseType
        };

        // Conditionally add the UUID if btnAction is "Update"
        if (btnAction === "Update") {
            result.uuid = uuidSelectedForAction;
        }

        return result;
    } else if (currentSelection === "course") {
        const nameInput = document.getElementById("txtCourseName");
        const authorInput = document.getElementById("txtAuthor");
        const durationInput = document.getElementById("txtDuration");
        const apInput = document.getElementById("txtActualPrice");
        const dpInput = document.getElementById("txtDiscountPrice");
        const expiryInput = document.getElementById("txtExpiry");
        const result = {
            type: courseTypeSelected,
            name: nameInput.value,
            author: authorInput.value,
            duration: durationInput.value,
            actual_price: parseFloat(apInput.value),
            discount_price: parseFloat(dpInput.value),
            expiry: parseInt(expiryInput.value)
        };

        if (btnAction === "Update") {
            result.uuid = uuidSelectedForAction;
        }

        return result;
    } else if (currentSelection === "topics") {
        const topicInput = document.getElementById('txtName');
        const result = {
            course: courseSelected,
            topic: topicInput.value
        };
        if (btnAction === "Update") {
            result.uuid = uuidSelectedForAction;
        }

        return result;
    } else if (currentSelection === "sub topics") {
        const nameInput = document.getElementById("txtName");
        const videoInput = document.getElementById("txtVideo");
        const projectInput = document.getElementById("txtProject");
        const durationInput = document.getElementById("txtDuration");
        const demoInput = document.getElementById("demoYes").checked ? true : false;
        console.log(videoInput);
        const result = {
            topic: topicSelected,
            name: nameInput.value,
            video: videoInput.files[0],
            project: projectInput.value,
            duration: durationInput.value,
            demo: demoInput
        };
        if (btnAction === "Update") {
            result.uuid = uuidSelectedForAction;
        }

        return result;
    }
}

function GetEP(func) {
    //func - i: insert, u:update, d:delete
    if (currentSelection === "course types") {
        if (func === "i") {
            return '/courses-types/add';
        } else if (func === "u") {
            return '/courses-types/update';
        } else if (func === "d") {
            return '/courses-types/delete';
        }
    } else if (currentSelection === "course") {
        if (func === "i") {
            return '/courses/add';
        } else if (func === "u") {
            return '/courses/update';
        } else if (func === "d") {
            return '/courses/delete';
        }
    } else if (currentSelection === "topics") {
        if (func === "i") {
            return '/courses/topics/add';
        } else if (func === "u") {
            return '/courses/topics/update';
        } else if (func === "d") {
            return '/courses/topics/delete';
        }
    } else if (currentSelection === "sub topics") {
        if (func === "i") {
            return '/courses/sub-topics/add';
        } else if (func === "u") {
            return '/courses/sub-topics/update';
        } else if (func === "d") {
            return '/courses/sub-topics/delete';
        }
    }
}