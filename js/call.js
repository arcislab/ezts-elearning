const API_BASE_URL = 'http://localhost/api/v1';

async function CallApi(url, jsonBody = null) {
    try {
        url = `${API_BASE_URL}${url}`;
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'include', // the fix
            headers: {
                'Content-Type': 'application/json',
            },
            body: jsonBody === null ? null : jsonBody,
        });
        
        console.log(`Calling: ${url}\nbody: ${jsonBody}`)
        
        if (response.status !== null && response.status >= 400) {
            // Handle the error response if needed
            console.error(`Error: ${response.status} - ${response.statusText}`);
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
        if(url.includes('login')){
            // alert(rawResponseBody);
        }
        const data = JSON.parse(rawResponseBody);
        console.log(data)
        return {
            data: data,
            status: response.status,
        };
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
        return {
            data: null,
            status: null,
        };
    }
}

// function CallApi(url, jsonBody = null) {
//     return new Promise((resolve, reject) => {
//         const xhr = new XMLHttpRequest();
//         url = `${API_BASE_URL}${url}`;

//         // Initialize the request
//         xhr.open('POST', url, true);
//         xhr.withCredentials = true; // Include credentials (cookies)

//         // Set the content type for the request
//         xhr.setRequestHeader('Content-Type', 'application/json');

//         // Log the request details
//         console.log(`Calling: ${url}\nbody: ${jsonBody}`);

//         // Handle the response
//         xhr.onreadystatechange = function () {
//             if (xhr.readyState === 4) { // Request is complete
//                 if (xhr.status >= 400) {
//                     console.error(`Error: ${xhr.status} - ${xhr.statusText}`);
//                     reject({ data: null, status: xhr.status });
//                 } else {
//                     try {
//                         console.log(`OP: ${xhr.responseText}`);
//                         const data = JSON.parse(xhr.responseText);
//                         resolve({ data: data, status: xhr.status });
//                     } catch (error) {
//                         console.error('Error parsing response:', error);
//                         reject({ data: null, status: xhr.status });
//                     }
//                 }
//             }
//         };

//         // Send the request with the body if provided
//         xhr.send(jsonBody === null ? null : jsonBody);
//     });
// }