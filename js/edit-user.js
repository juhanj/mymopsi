"use strict";

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let userID = deleteButton.dataset.user;
	if ( confirm( "Delete? Action irreversible." ) ) {
		let request = {
			'request' : 'delete_user',
			'user' : userID
		};
		sendJSON( request )
			.then( (response) => {
				// Send back to previous page
				// This could be done better? But I dunno how for now.
				history.back();
			} )
	}
}
