"use strict";

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let collectionID = deleteButton.dataset.collection;
	if ( prompt( "Type 'delete' to cofirm",'' ) === 'delete' ) {
		let request = {
			'class' : 'collection',
			'request' : 'delete_collection',
			'collection' : collectionID
		};
		sendJSON( request )
			.then( (response) => {
				window.location.replace("./collections.php");
			} );
	}
}
