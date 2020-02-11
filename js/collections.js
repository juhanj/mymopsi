let headerUserNameLink = document.getElementById( 'header-user-name' );

headerUserNameLink.innerText = userName;
headerUserNameLink.href = `edit-user.php?id=${userRUID}`;
