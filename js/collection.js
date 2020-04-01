let headerCollectionNameLink = document.getElementById( 'header-coll-link' );
let headerCollectionNameName = document.getElementById( 'header-coll-name' );

headerCollectionNameName.innerText = collectionName;
headerCollectionNameLink.href = `edit-collection.php?id=${collectionRUID}`;