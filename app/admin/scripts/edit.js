const main = document.getElementById('main-content');
const btnAction = document.querySelector('#main-foot button');
let currentStat;

let type;
let uuid;

addEventListener("DOMContentLoaded", async () => {
    if (main) {
        type = getQueryParam('type');
        uuid = getQueryParam('uuid');
        if (uuid) {
            main.innerHTML = await GetAutofill();
            if (btnAction) {
                btnAction.innerHTML = "Edit";
            }
        } else {
            main.innerHTML = GetElements();
        }
        if (type === "sub-topics") {
            const courses = document.querySelector('#txtCourse');
            if (courses) {
                courses.addEventListener('change', function (event) {
                    const id = event.target.value;
                    if (id) {
                        GetTopics(id);
                    }
                });
            }
        }
        await LoadDDs();
    }
});

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}
btnAction.addEventListener('click', function () {
    if (type === "sub-topics") {
        if (document.getElementById("txtVideo").files.length > 0) {
            InitiateDialog(0, 'Uploading course video');
            GSU();
        } else {
            InitiateDialog(0, 'Uploading course');
            SendData();
        }
    } else {
        SendData();
    }
});

const dialogMain = document.querySelector('.dialogMain');
const btnOk = document.getElementById('btnOK');
btnOk.addEventListener('click', function () {
    dialogMain.style.visibility = 'hidden';
    if (currentStat === 1) {
        window.close();
    }
});

function GetElements(response = null) {
    if (type === "types") {
        return `<div>
                        <p>Name</p>
                        <input type="text" id="txtCourseType" placeholder="Enter course type" value="${response?.type ?? ''}">
                    </div>`;
    } else if (type === "courses") {
        return `<div>
                        <p>Course Type</p>
                        <select id="txtCourseType" value="${response?.uuid ?? ''}"></select>
                    </div>
                    <div>
                        <p>Name</p>
                        <input type="text" id="txtCourseName" placeholder="Enter course name" value="${response?.name ?? ''}">
                    </div>
                    <div>
                        <p>Author</p>
                        <input type="text" id="txtAuthor" placeholder="Enter author" value="${response?.author ?? ''}">
                    </div>
                    <div>
                        <p>Duration</p>
                        <input type="number" style="cursor: default;" id="txtDuration" placeholder="Enter duration in hours" value="${response?.duration ?? ''}">
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
                    </div>`;
    } else if (type === "topics") {
        return `<div>
                        <p>Course</p>
                        <select id="txtCourse" value="${response?.uuid ?? ''}"></select>
                    </div>
                    <div>
                        <p>Name</p>
                        <input type="text" id="txtName" placeholder="Enter name of topic" value="${response?.name ?? ''}">
                    </div>
                    <div>
                        <p>Downloadable Content</p>
                        <input type="file" id="txtContent">
                    </div>`;
    } else if (type === "sub-topics") {
        return `<div>
                        <p>Course</p>
                        <select id="txtCourse" value="${response?.uuid ?? ''}"></select>
                    </div>
                    <div>
                        <p>Topic</p>
                        <select id="txtTopic" value="${response?.uuid ?? ''}"></select>
                    </div>
                    <div>
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
                        <input type="text" id="txtDuration" value="${response?.duration ?? ''}" readonly>
                    </div>
                    <div>
                        <p>Demo</p>
                        <input type="radio" name="rad" id="demoYes" ${response?.demo === 1 ? "checked" : response?.demo === null ? "checked" : ""}>Yes</input>
                        <input type="radio" name="rad" id="demoNo" ${response?.demo === 0 ? "checked" : ""}>No</input>
                    </div>`;
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

async function GetAutofill() {
    if (uuid) {
        const body = JSON.stringify({ uuid: uuid });
        const response = await CallApi(GetEP('g'), body);
        if (response) {
            if (response.data.data?.length > 0) {
                return GetElements(response.data.data[0]);
            } else {
                console.log('No data found.');
            }
        }
    } else {
        console.log('selection is invalid!');
    }
    return null;
}

async function SendData() {
    const body = SerializeData();
    const contentType = type === 'sub-topics' ? 'multipart/form-data' : null;
    const response = await CallApi(GetEP(uuid ? 'u' : 'i'), body, null, contentType);
    if (response.status === 200) {
        InitiateDialog(1, response.data.message, false);
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        InitiateDialog(-1, response.data.message, false);
    }
    return response;
}

async function GSU() {
    const videoInput = document.getElementById("txtVideo");
    const response = await CallApi(`/get-sign?filekey=${encodeURIComponent(videoInput.files[0].name)}`, null, 'GET');
    if (response.status === 200) {
        try {
            console.log(response);
            console.log(response.data.sign);
            const uploadResponse = await fetch(response.data.sign, {
                method: 'PUT',
                headers: {
                    'Content-Type': videoInput.files[0].type // Set file type
                },
                body: videoInput.files[0] // Actual file blob
            });

            if (!uploadResponse.ok) {
                InitiateDialog(-1, 'Upload failed', false);
                // throw new Error('Upload failed: ' + uploadResponse.statusText);
                // throw new Error('Upload failed');
            }

            InitiateDialog(0, 'Uploading course data', false);
            SendData();
        } catch (error) {
            console.error('Error uploading file:', error);
        }
    }
    else {
        console.log(`Code: ${response.status}\nResponse: ${response.data.message}`);
        InitiateDialog(-1, response.data.message, false);
    }
    return response;
}

async function InitiateDialog(type, message, show = true) {
    const dialogTitle = document.querySelector('#dialogBody h2');
    const stat = document.getElementById('statIcon');
    if (dialogTitle) {
        if (type === 1) {
            dialogTitle.innerHTML = "Success";
            if (stat) {
                stat.className = `fa-solid fa-check`;
                stat.style.color = "#28cc33";
            }
            btnOk.style.display = "block";
        } else if (type === 0) {
            dialogTitle.innerHTML = "Please wait";
        } else if (type === -1) {
            dialogTitle.innerHTML = "Error";
            if (stat) {
                stat.className = `fa-solid fa-triangle-exclamation`;
                stat.style.color = "#e31616";
            }
            btnOk.style.display = "block";
        }
    }

    const dialogMain = document.querySelector(".dialogMain");
    if (show) {
        dialogMain.style.visibility = 'visible';
    }
    const dialogMessage = document.querySelector('#dialogBody p');
    if (dialogMessage) {
        dialogMessage.innerHTML = message;
    }
    currentStat = type;
}

async function GetTypes() {
    const url = '/courses-types';
    const response = await CallApi(url);
    if (response) {
        if (response.status === 200) {
            let selectValues;
            const courseTypes = document.querySelector('#txtCourseType');
            response.data.data.forEach(item => {
                if (courseTypes) {
                    selectValues += BuildSelectElement(item.uuid, item.type);
                }
            });
            courseTypes.innerHTML = selectValues;
        } else {
            alert('Error fetching course types!');
        }
    }
}

async function GetCourses() {
    const url = '/courses';
    const response = await CallApi(url);
    if (response) {
        if (response.status === 200) {
            let selectValues;
            const courses = document.querySelector('#txtCourse');
            response.data.data.forEach((item, index) => {
                if (courses) {
                    selectValues += BuildSelectElement(item.uuid, item.name);
                    if (index === 0) {
                        GetTopics(item.uuid);
                    }
                }
            });
            courses.innerHTML = selectValues;
        } else {
            alert('Error fetching course types!');
        }
    }
}

async function GetTopics(courseId) {
    const topics = document.querySelector('#txtTopic');
    if (topics) {
        const url = '/courses/topics';
        const body = JSON.stringify({ course: courseId });
        const response = await CallApi(url, body);
        if (response) {
            if (response.status === 200) {
                let selectValues;
                response.data.data.forEach(item => {
                    if (topics) {
                        selectValues += BuildSelectElement(item.uuid, item.name);
                    }
                });
                topics.innerHTML = selectValues;
            } else {
                alert('Error fetching course types!');
            }
        }
    }
}

function BuildSelectElement(id, value) {
    return `<option value="${id}">${value}</option>`;
}

async function LoadDDs() {
    if (type === "courses") {
        await GetTypes();
    } else if (type === "topics" || type === "sub-topics") {
        await GetCourses();
    }
}

function SerializeData() {
    if (type === "types") {
        InitiateDialog(0, 'Sending values');
        const courseType = document.getElementById("txtCourseType").value;
        const result = {
            type: courseType
        };

        // Conditionally add the UUID if btnAction is "Update"
        if (uuid) {
            result.uuid = uuid;
        }

        return JSON.stringify(result);
    } else if (type === "courses") {
        InitiateDialog(0, 'Sending values');
        const typeId = document.getElementById("txtCourseType");
        const nameInput = document.getElementById("txtCourseName");
        const authorInput = document.getElementById("txtAuthor");
        const durationInput = document.getElementById("txtDuration");
        const apInput = document.getElementById("txtActualPrice");
        const dpInput = document.getElementById("txtDiscountPrice");
        const expiryInput = document.getElementById("txtExpiry");
        const result = {
            type: typeId.value,
            name: nameInput.value,
            author: authorInput.value,
            duration: durationInput.value,
            actual_price: parseFloat(apInput.value),
            discount_price: parseFloat(dpInput.value),
            expiry: parseInt(expiryInput.value)
        };

        if (uuid) {
            result.uuid = uuid;
        }

        return JSON.stringify(result);
    } else if (type === "topics") {
        InitiateDialog(0, 'Sending values');
        const courseId = document.getElementById('txtCourse');
        const topicInput = document.getElementById('txtName');
        const result = {
            course: courseId.value,
            topic: topicInput.value
        };
        if (uuid) {
            result.uuid = uuid;
        }

        return JSON.stringify(result);
    } else if (type === "sub-topics") {
        const topicId = document.getElementById("txtTopic");
        const nameInput = document.getElementById("txtName");
        const videoInput = document.getElementById("txtVideo");
        const projectInput = document.getElementById("txtProject");
        const durationInput = document.getElementById("txtDuration");
        const demoInput = document.getElementById("demoYes").checked ? 1 : 0;

        const formData = new FormData();
        formData.append('topic', topicId.value);
        formData.append('name', nameInput.value);
        if(videoInput.files.length > 0){
            formData.append('video', encodeURIComponent(videoInput.files[0].name));
        }
        if(projectInput.files.length > 0){
            formData.append('project', encodeURIComponent(projectInput.files[0].name));
        }
        formData.append('duration', durationInput.value);
        formData.append('demo', demoInput);
        if (uuid) {
            formData.append('uuid', uuid);
        }
        // for (let [key, value] of formData.entries()) {
        //     console.log(key, value);
        // }
        return formData;
    }
}

function GetEP(func) {
    //func - i: insert, u:update, d:delete
    if (type === "types") {
        if (func === "g") {
            return '/courses-types';
        } else if (func === "i") {
            return '/courses-types/add';
        } else if (func === "u") {
            return '/courses-types/update';
        } else if (func === "d") {
            return '/courses-types/delete';
        }
    } else if (type === "courses") {
        if (func === "g") {
            return '/courses';
        } else if (func === "i") {
            return '/courses/add';
        } else if (func === "u") {
            return '/courses/update';
        } else if (func === "d") {
            return '/courses/delete';
        }
    } else if (type === "topics") {
        if (func === "g") {
            return '/courses/topics';
        } else if (func === "i") {
            return '/courses/topics/add';
        } else if (func === "u") {
            return '/courses/topics/update';
        } else if (func === "d") {
            return '/courses/topics/delete';
        }
    } else if (type === "sub-topics") {
        if (func === "g") {
            return '/courses/sub-topics';
        } else if (func === "i") {
            return '/courses/sub-topics/add';
        } else if (func === "u") {
            return '/courses/sub-topics/update';
        } else if (func === "d") {
            return '/courses/sub-topics/delete';
        }
    }
}