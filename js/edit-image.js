"use strict";

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let imageID = deleteButton.dataset.image;
	if ( confirm( "Delete? Action irreversible." ) ) {
		let request = {
			'request' : 'delete_image',
			'image' : imageID,
		};
		sendJSON( request )
			.then( (response) => {
				// Send back to previous page
				// This could be done better? But I dunno how for now.
				history.back();
			} )
	}
}
