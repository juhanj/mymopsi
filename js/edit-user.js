"use strict";

import {Ajax} from "./modules/export.js";

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let userID = deleteButton.dataset.user;
	if ( prompt( "Type 'delete' to cofirm",'' ) === 'delete' ) {
		let request = {
			'class' : 'user',
			'request' : 'delete_user',
			'user' : userID
		};
		Ajax.sendJSON( request )
			.then( (response) => {
				// Send back to previous page
				// This could be done better? But I dunno how for now.
				window.location.href = "./index.php";
			} )
	}
}
