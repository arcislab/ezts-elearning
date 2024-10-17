//Courses types
async function GetCoursesTypes(uuid) {
    const url = `/courses-types`;
    const body = JSON.stringify({ uuid: uuid });

    try {
        const response = await CallApi(url, body);

        // Check if response exists and has the expected structure
        if (response?.data?.data?.length > 0) {
            // Map and return all course types
            const firstCourseType = response.data.data[0].type;
            console.log('Returning first course type:', firstCourseType);
            return firstCourseType;
        } else {
            console.log('No course types found.');
            return null;
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

//Courses
async function GetCourses(uuid) {
    let url = `/courses`
    const body = JSON.stringify({ uuid: uuid });
    const response = await CallApi(url, body);

    try {
        const response = await CallApi(url, body);

        // Check if response exists and has the expected structure
        if (response?.data?.data?.length > 0) {
            // Map and return all course types
            const firstCourseType = response.data.data[0];
            console.log('Returning first course type:', firstCourseType);
            return firstCourseType;
        } else {
            console.log('No course types found.');
            return null;
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

//Topics
async function GetTopics(uuid) {
    let url = `/courses/topics`
    const body = JSON.stringify({ uuid: uuid });
    const response = await CallApi(url, body);
    try {
        const response = await CallApi(url, body);

        // Check if response exists and has the expected structure
        if (response?.data?.topics?.length > 0) {
            // Map and return all course types
            const firstCourseType = response.data.topics[0];
            console.log('Returning first course type:', firstCourseType);
            return firstCourseType;
        } else {
            console.log('No course types found.');
            return null;
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}

//Sub topics
async function GetSubTopics(uuid) {
    let url = `/courses/sub-topics`
    const body = JSON.stringify({ uuid: uuid });
    const response = await CallApi(url, body);
    try {
        const response = await CallApi(url, body);

        // Check if response exists and has the expected structure
        if (response?.data?.sub_topics?.length > 0) {
            // Map and return all course types
            const firstCourseType = response.data.sub_topics[0];
            console.log('Returning first course type:', firstCourseType);
            return firstCourseType;
        } else {
            console.log('No course types found.');
            return null;
        }
    } catch (error) {
        console.error('Error fetching course types:', error);
        return null;
    }
}