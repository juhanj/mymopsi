"use strict";

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let collectionID = deleteButton.dataset.collection;
	if ( confirm( "Delete? Action irreversible." ) ) {
		let request = {
			'request' : 'delete_collection',
			'collection' : collectionID
		};
		sendJSON( request )
			.then( (response) => {
				window.location.replace("./collections.php");
			} );
	}
}
