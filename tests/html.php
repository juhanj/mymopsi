<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	.main_body_container {
		background-color: white;
		width: max-content;
		margin: auto;
	}
</style>

<body>

<?php require 'html-header.php'; ?>

<main class="main_body_container">

	<div id="test-div">
		This is a test <code>&lt;div&gt;</code>.
	</div>

	<hr>

	<form>
		<label>
			New collection of images:
			<input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple" required>
		</label>

		<input type="submit" value="Submit images" class="button" id="submitButton">

		<p class="side-note">Drag & Drop works too.</p>
	</form>

	<hr>

	<table>
		<thead>
			<tr>
				<th>✔/⚠/❌</th>
				<th>Name</th>
				<th>Size</th>
				<th>Date</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1 ✔</td>
				<td>Rnaomd image</td>
				<td>1,5MB</td>
				<td>15.05.2019 14:00:00</td>
			</tr>
			<tr>
				<td>2 ❌</td>
				<td>Longn ame-___blaah_blaah_blaah-tadaa</td>
				<td>500KB</td>
				<td>15.05.2019 14:00:00</td>
			</tr>
		</tbody>
	</table>

	<hr>

	<div class="feedback">
		<p class="error">This is an error. ❌✖</p>
		<p class="warning">This is a warning. ⚠❕🏮🚨</p>
		<p class="success">This is: HUGE SUCCESS.<br>(It's hard to overstate my satisfaction.)</p>
		<p class="info">This is just info.</p>
	</div>

	<hr>

	<button class="btn">Normal button</button>
	<button class="btn red">Bad button</button>
	<button class="btn disabled" disabled>Disabled w/ class</button>
	<button class="btn" disabled>Disabled w/o class</button>

	<hr>

	<button class="btn" id="open-test-modal">Open test modal</button>

	<hr>

	<p class="loading"></p>
	<label>Testing the march of progress:
		<progress id="test-progress"></progress>
	</label>

	<hr>
</main>

<?php require 'html-footer.php'; ?>

<dialog id="modal-test">
	<header>
		<h1>Test modal title</h1>
		<button id="close-test-modal">❌</button>
	</header>

	<div>
		Content for the modal!
	</div>

	<footer>
		Footer
	</footer>
</dialog>

<script>
	window.addEventListener("load", ()=>{

		let modal = document.getElementById('modal-test');
		let modalOpen = document.getElementById('open-test-modal');
		let modalClose = document.getElementById('close-test-modal');
		modalOpen.onclick = () => {
			modal.showModal();
		};
		modalClose.onclick = () => {
			modal.close();
		};

		let foo = document.createElement("p");
		foo.innerHTML = "some random <strong>content</span.";
		document.getElementById( 'test-div' ).appendChild( foo );

		let progress = document.getElementById( 'test-progress' );
		progress.max = 9999;
		progress.value = 6666;
	});
</script>

</body>
</html>
