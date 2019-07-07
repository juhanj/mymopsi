"use strict";

function buildHTMLListLocalCollections ( collections ) {
	let ul = document.createElement( 'ul' );
	collections.forEach( (coll) => {
		let li = document.createElement( 'li' );
		li.innerHTML = `<a href="view.php?id=${coll.uid}">Link to ${coll.name}</a>`
		ul.appendChild( li );
	});

	return ul;
}

function printLocalCollections () {
	let collections = JSON.parse( getCookie( 'collections' ) );
	if ( !collections ) {
		return null;
	}

	localCollectionsDiv.appendChild( buildHTMLListLocalCollections( collections ) );
}

let modalNewCollection = document.getElementById( 'modal-new-collection' );
let openModalNewCollection = document.getElementById( 'open-modal-new-collection' );
let closeModalNewCollection = document.getElementById('close-modal-new-collection');
let newCollectionForm = document.getElementById('new-collection-form');

let localCollectionsDiv = document.getElementById('local-collections');
let publicCollectionsDiv = document.getElementById('public-collections');

//TODO:
// Remove collection from local cookies
// Attach listener to a button next to list item
// function

//TODO:
// Get local collections from cookies
// geCookies ( collList )
// print to localCollectionsDiv

//TODO:
// Fetch public collections from the server
// sendJSON ( getPublicCollections )
// print to publicCollectionsDiv

openModalNewCollection.onclick = () => {
	modalNewCollection.showModal();
};
closeModalNewCollection.onclick = () => {
	modalNewCollection.close();
};

newCollectionForm.onsubmit = (event) => {
	// Prevent default browser behaviour, in this case submitting a form normally (causing page-load)
	event.preventDefault();

	let formData = new FormData( newCollectionForm );

	sendForm( formData )
		.then((response) => {
			console.log(response);
			if ( response.result.success ) {
				newCollectionForm.insertAdjacentHTML(
					'afterend',
					`<a href="view.php?id=${response.result.uid}" class="button">Link to collection.</a>`
				);
				// window.location.href = './upload.php';
				addCollectionToCookies( response.result.name, response.result.uid );
			}
		});
};

printLocalCollections();

let dialog = document.querySelector('dialog');
dialogPolyfill.registerDialog(dialog);
