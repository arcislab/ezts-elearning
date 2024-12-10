const API_BASE_URL = 'http://148.135.137.222/api/v1';

async function CallApi(url, reqBody = null, met = null, contentType = null) {
    let rawResponseBody;
    try {
        url = `${API_BASE_URL}${url}`;
        console.log(`CallingK: ${met} : ${url}\nbody: ${reqBody}`)
        const response = await fetch(url, {
            credentials: 'include', // the fix
            method: met == null ? 'POST' : met,
            body: reqBody === null ? null : reqBody,
        });
        
        if(reqBody instanceof FormData){
            for (let [key, value] of reqBody.entries()) {
                console.log(key, value);
            }
        }
        
        if (response.status !== null && response.status >= 400) {
            // Handle the error response if needed
            console.error(`Error: ${response.status}`);
            if(response.status === 401){
                window.location.href = 'http://148.135.137.222/login.html';
            }
        }
        // if (!response.ok) {
        //     const errorData = await response.json();
        //     console.error(`Error: ${response.status} - ${response.statusText}`);
        //     return {
        //         data: errorData,
        //         status: response.status,
        //     };
        // }

        rawResponseBody = await response.text();
        // console.log(`Raw Res: ${rawResponseBody}`);
        const data = JSON.parse(rawResponseBody);
        console.log(data)
        return {
            data: data,
            status: response.status,
        };
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error.message);
        console.error('Raw: ' , rawResponseBody);
        return {
            data: null,
            status: null,
        };
    }
}