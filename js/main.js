"use strict";
function ajax( data, callback ) {
    fetch( 'ajax_handler.php', {
        method: 'post',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify( data )
    })
        .then(function(response) {
            return response.json();
        })
        .then(
            callback
        );
}
