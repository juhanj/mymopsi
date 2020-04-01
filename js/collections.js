let headerUserNameLink = document.getElementById( 'header-user-link' );
let headerUserNameName = document.getElementById( 'header-user-name' );

headerUserNameName.innerText = userName;
headerUserNameLink.href = `edit-user.php?id=${userRUID}`;
