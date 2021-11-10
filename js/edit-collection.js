"use strict";

import { Ajax } from './modules/export.js';

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

let nameInput = document.getElementById( 'nameInput' );
let nameSubmit = document.getElementById( 'nameSubmit' );
let descriptionInput = document.getElementById( 'descriptionInput' );
let descriptionSubmit = document.getElementById( 'descriptionSubmit' );

nameInput.oninput = () => { nameSubmit.disabled = false; nameSubmit.value = "💾 ?"; };
descriptionInput.oninput = () => { descriptionSubmit.disabled = false; descriptionSubmit.value = "💾 ?"; };

nameSubmit.onclick = handleFormSubmit;
descriptionSubmit.onclick = handleFormSubmit;

let deleteButton = document.getElementById( 'deleteButton' );

deleteButton.onclick = () => {
	let collectionID = deleteButton.dataset.collection;
	if ( prompt( "Type 'delete' to cofirm",'' ) === 'delete' ) {
		let request = {
			'class' : 'collection',
			'request' : 'delete_collection',
			'collection' : collectionID
		};
		Ajax.sendJSON( request )
			.then( (response) => {
				window.location.replace("./collections.php");
			} );
	}
}
