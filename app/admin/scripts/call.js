const API_BASE_URL = 'http://localhost/api/v1';

async function CallApi(url, jsonBody = null, met = null) {
    try {
        url = `${API_BASE_URL}${url}`;
        console.log(`Calling: ${met} : ${url}\nbody: ${jsonBody}`)
        const response = await fetch(url, {
            credentials: 'same-origin', // the fix
            method: met == null ? 'POST' : met,
            headers: {
                'Content-Type': 'application/json',
            },
            body: jsonBody === null ? null : jsonBody,
        });

        if (response.status !== null && response.status >= 400) {
            // Handle the error response if needed
            console.error(`Error: ${response.status}`);
        }
        // if (!response.ok) {
        //     const errorData = await response.json();
        //     console.error(`Error: ${response.status} - ${response.statusText}`);
        //     return {
        //         data: errorData,
        //         status: response.status,
        //     };
        // }
        
        const rawResponseBody = await response.text();
        // console.log(`Raw Res: ${rawResponseBody}`);
        const data = JSON.parse(rawResponseBody);
        console.log(data)
        return {
            data: data,
            status: response.status,
        };
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error.message);
        return {
            data: null,
            status: null,
        };
    }
}