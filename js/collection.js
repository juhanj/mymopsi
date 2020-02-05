let headerCollectionNameLink = document.getElementById( 'header-coll-name' );

headerCollectionNameLink.innerText = collectionName;
headerCollectionNameLink.href = `edit-collection.php?id=${collectionRUID}`;