const btns = document.querySelectorAll('#main-nav button');
const tableHead = document.querySelector('#documentTable thead');
const tableBody = document.querySelector('#documentTable tbody');
const actionButton = document.querySelector('#action button');
let currentSelection;

addEventListener("DOMContentLoaded", () => {
    if (btns) {
        if (btns.length > 0) {
            // Course types
            btns[1].addEventListener('click', async () => {
                const type = "types";
                tableHead.innerHTML = await GetColumns(type);
                const response = await GData(type);
                currentSelection = type;
            });
            // Courses
            btns[2].addEventListener('click', async () => {
                const type = "courses";
                tableHead.innerHTML = await GetColumns(type);
                const response = await GData(type);
                currentSelection = type;
            });
            // Topics
            btns[3].addEventListener('click', async () => {
                const type = "topics";
                tableHead.innerHTML = await GetColumns(type);
                const response = await GData(type);
                currentSelection = type;
            });
            // Sub topics
            btns[4].addEventListener('click', async () => {
                const type = "sub-topics";
                tableHead.innerHTML = await GetColumns(type);
                const response = await GData(type);
                currentSelection = type;
            });
            // Logout
            btns[5].addEventListener('click', async () => {
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
                            window.location.href = 'http://ezts.local/login.html'
                        } else {
                            console.error(`Error: ${response.status} - ${response.statusText}`);
                        }
                    }
                } catch (error) {
                    console.error('Error fetching course types:', error);
                    return null;
                }
            });
        }
    }

    if (actionButton) {
        actionButton.addEventListener('click', function () {
            window.open(`http://ezts.local/app/admin/manage.html?type=${currentSelection}`, '_blank').focus();
        });
    }
});

async function loadTableData(data, selection) {
    const tableBody = document.getElementById('documentTable').querySelector('tbody');
    data.data.forEach((item, index) => {
        const row = tableBody.insertRow();
        if (selection === "types") {
            const cellSrNo = row.insertCell(0);
            cellSrNo.textContent = index + 1;

            const cellTypes = row.insertCell(1);
            cellTypes.textContent = item.type;

            const cellAction = row.insertCell(2);
            PopulateActionColumn(cellAction, item, selection);
        } else if (selection === "courses") {
            const cellSrNo = row.insertCell(0);
            cellSrNo.textContent = index + 1;

            const cellTypes = row.insertCell(1);
            cellTypes.textContent = item.type;

            const cellCourses = row.insertCell(2);
            cellCourses.textContent = item.name;

            const cellAuthors = row.insertCell(3);
            cellAuthors.textContent = item.author;

            const cellDuration = row.insertCell(4);
            cellDuration.textContent = item.duration;

            const cellAP = row.insertCell(5);
            cellAP.textContent = item.ap;

            const cellDP = row.insertCell(6);
            cellDP.textContent = item.dp;

            const cellExpiry = row.insertCell(7);
            cellExpiry.textContent = item.expiry;

            const cellAction = row.insertCell(8);
            PopulateActionColumn(cellAction, item, selection);
        } else if (selection === "topics") {
            const cellSrNo = row.insertCell(0);
            cellSrNo.textContent = index + 1;

            const cellTypes = row.insertCell(1);
            cellTypes.textContent = item.type;

            const cellCourses = row.insertCell(2);
            cellCourses.textContent = item.course;

            const cellTopics = row.insertCell(3);
            cellTopics.textContent = item.name;

            const cellAction = row.insertCell(4);
            PopulateActionColumn(cellAction, item, selection);
        } else if (selection === "sub-topics") {
            const cellSrNo = row.insertCell(0);
            cellSrNo.textContent = index + 1;

            const cellTypes = row.insertCell(1);
            cellTypes.textContent = item.type;

            const cellCourses = row.insertCell(2);
            cellCourses.textContent = item.course;

            const cellTopics = row.insertCell(3);
            cellTopics.textContent = item.topic;

            const cellSubTopics = row.insertCell(4);
            cellSubTopics.textContent = item.topic_name;

            const cellVideo = row.insertCell(5);
            cellVideo.textContent = item.video_url === null || item.video_url === '' ? 'No' : 'Yes';

            const cellProject = row.insertCell(6);
            cellProject.textContent = item.project_url === null || item.project_url === '' ? 'No' : 'Yes';

            const cellDuration = row.insertCell(7);
            cellDuration.textContent = item.duration;

            const cellDemo = row.insertCell(8);
            cellDemo.textContent = item.demo === 0 || item.demo === null ? 'No' : "Yes";

            const cellAction = row.insertCell(9);
            PopulateActionColumn(cellAction, item, selection);
        }
    });
}

function PopulateActionColumn(cellAction, item, selection) {
    const updateIcon = document.createElement('img');
    updateIcon.src = "../../assets/icons/edit.png"; // Replace with actual path to update icon
    updateIcon.alt = "Update";
    updateIcon.title = "Update";
    updateIcon.addEventListener('click', async () => {
        const selectedUUID = item.uuid;
        window.open(`http://ezts.local/app/admin/manage.html?type=${selection}&uuid=${selectedUUID}`, '_blank').focus();
    });

    const deleteIcon = document.createElement('img');
    deleteIcon.src = "../../assets/icons/cancel.png"; // Replace with actual path to delete icon
    deleteIcon.alt = "Delete";
    deleteIcon.title = "Delete";
    deleteIcon.addEventListener('click', function () {
        UDocument(item.uuid, "d");
    });

    cellAction.appendChild(updateIcon);
    cellAction.appendChild(deleteIcon);
}

async function GetColumns(selection) {
    tableBody.innerHTML = '';
    if (selection === "types") {
        return `<tr>
                    <th style="width: 50px;">Sr No.</th>
                    <th>Course Types</th>
                    <th style="width: 80px;">Action</th>
                </tr>`;
    } else if (selection === "courses") {
        return `<tr>
                    <th style="width: 50px;">Sr No.</th>
                    <th>Types</th>
                    <th>Courses</th>
                    <th>Authors</th>
                    <th>Duration</th>
                    <th>Actual Price</th>
                    <th>Discount Price</th>
                    <th>Expiry</th>
                    <th style="width: 80px;">Action</th>
                </tr>`;
    } else if (selection === "topics") {
        return `<tr>
                    <th style="width: 50px;">Sr No.</th>
                    <th>Types</th>
                    <th>Courses</th>
                    <th>Topics</th>
                    <th style="width: 80px;">Action</th>
                </tr>`;
    } else if (selection === "sub-topics") {
        return `<tr>
                    <th style="width: 50px;">Sr No.</th>
                    <th>Types</th>
                    <th>Courses</th>
                    <th>Topics</th>
                    <th>Sub Topics</th>
                    <th>Video</th>
                    <th>Project</th>
                    <th>Duration</th>
                    <th>Demo</th>
                    <th style="width: 80px;">Action</th>
                </tr>`;
    }
}

async function GData(selection) {
    try {
        const response = await CallApi(GetEP(selection));
        if (response.status === 401) {
            console.error(`Error: ${response.status} - ${response.statusText}`);
            return;
        }

        if (response) {
            if (response.data !== undefined) {
                await loadTableData(response.data, selection);
            } else {
                alert('No information found.');
            }
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

function GetEP(selection) {
    if (selection === "types") {
        return `/courses-types`;
    } else if (selection === "courses") {
        return `/courses`;
    } else if (selection === "topics") {
        return `/courses/topics`;
    } else if (selection === "sub-topics") {
        return `/courses/sub-topics`;
    }
}