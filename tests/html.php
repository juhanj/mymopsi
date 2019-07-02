<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	/*.main-body-container {*/
	/*	background-color: var(--site-bg-white);*/
	/*	width: max-content;*/
	/*	margin: auto;*/
	/*	padding: .5rem;*/
	/*}*/
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<hr>

	<form>
		<label>
			New collection of images:
			<input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple" required>
		</label>
		<p class="side-note">Drag & Drop works too.</p>
		<input type="submit" value="Submit images" class="button" id="submitButton">
	</form>
	<form>
		<label class="required">
			Text
			<input type="text" name="text" required>
		</label>

		<label for="numberInput" class="required">Number</label>
		<input type="number" name="number" id="numberInput" required>

		<label for="dateInput">Date</label>
		<input type="date" id="dateInput">

		<label for="datetimeInput">Date</label>
		<input type="datetime-local" id="datetimeInput">

		<label>
			<span class="required">Colours!</span>
			<input type="color">
		</label>

		<label>
			Check out this box!
			<input type="checkbox">
		</label>
		<label>
			<input type="checkbox">
			<span class="required">This box is required</span>
		</label>
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
	<p class="loading small"></p>
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
	});
</script>

</body>
</html>
