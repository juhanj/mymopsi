let form = document.getElementById( 'create' );
let password = document.getElementById( 'pw' );
let pwConfirm = document.getElementById( 'confirm-pw' );
let error = document.getElementById( 'error' );

form.onsubmit = (event) => {
	if ( password.value !== pwConfirm.value ) {
		event.preventDefault();
		error.innerHTML = "Password confirmation does not match."
	}
}
