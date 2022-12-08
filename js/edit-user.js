"use strict";

import {Ajax} from "./modules/export.js";

/********************************************
 * Functions
 ********************************************/

function handleFormSubmit ( event ) {
	event.preventDefault();
	let button = event.target;
	let form = button.parentNode;

	Ajax.sendForm( new FormData(form) )
		.then( (response) => {
			console.log(response)

			if ( response.result.success ) {
				button.value = "💾! ✔";
			}
			else {
				button.value = "💾! ❌";
			}

			button.disabled = true;
		} );

	console.log( event.target );
}

/********************************************
 * Main code
 ********************************************/

let usernameInput = document.getElementById( 'usernameInput' );
let usernameSubmit = document.getElementById( 'usernameSubmit' );
let passwordInput = document.getElementById( 'passwordInput' );
let passwordSubmit = document.getElementById( 'passwordSubmit' );

let deleteButton = document.getElementById( 'deleteButton' );

usernameInput.oninput = () => { usernameSubmit.disabled = false; usernameSubmit.value = "💾 ?"; };
passwordInput.oninput = () => { passwordSubmit.disabled = false; usernameSubmit.value = "💾 ?"; };

usernameSubmit.onclick = handleFormSubmit;
passwordSubmit.onclick = handleFormSubmit;

deleteButton.onclick = () => {
	let userID = deleteButton.dataset.user;
	if ( prompt( "Type 'delete' to cofirm",'' ) === 'delete' ) {
		let request = {
			'class' : 'user',
			'request' : 'delete_user',
			'user' : userID
		};
		Ajax.sendJSON( request )
			.then( () => {
				// Send back to previous page
				// This could be done better? But I dunno how for now.
				//TODO: check this comment --jj-22-07-19
				window.location.href = "./index.php";
			} )
	}
}
