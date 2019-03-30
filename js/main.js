"use strict";
//TODO: Change url-paramtere to absolute path. Possible even? --jj190330
async function sendJSON ( data, url = './ajax_handler.php' ) {
    console.log("sendJSON(): Sending request");
    let response = await fetch( url, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify( data )
    });
    return await response.json();
}

//TODO: Change url-paramtere to absolute path. Possible even? --jj190330
async function sendFormData ( data, url = './ajax_handler.php' ) {
    console.log("sendFormData(): Sending Fetch FormData POST request...");
    let response = await fetch( url, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data
    });
    return await response.json();
}
