<?php declare(strict_types=1);
require '../components/_start.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	:root {
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<article class="feedback compact" hidden>
	<p class="error">This is an error. ❌✖</p>
	<p class="warning">This is a warning. ⚠❕🏮🚨</p>
	<p class="success">This is: HUGE SUCCESS.<br>(It's hard to overstate my satisfaction.)</p>
	<p class="info">This is just info.</p>
</article>

<main class="main-body-container">

	<article class="box" id="buttons-test">
		<details>
			<summary>Buttons</summary>
			<section>
				<button class="button">Normal button</button>
				<button class="button hover">Normal button w/ hover</button>
				<button class="button active">Normal button w/ active</button>
				<button disabled class="button">Normal disabled</button>
			</section>
			<hr>
			<section>
				<button class="button red">Red button</button>
				<button class="button red hover">Red button w/ hover</button>
				<button class="button red active">Red button w/ active</button>
				<button disabled class="button red">Red disabled</button>
			</section>
			<hr>
			<section>
				<button class="button">
					<i class="material-icons">check</i>
					✔ ❤Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
					labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
					nisi ut aliquip ex ea commodo.
				</button>
			</section>

		</details>
	</article>

	<article class="box" id="upload-test">
		<details>
			<summary>File upload form</summary>
			<form>
				<label>
					<span class="label">New collection of images:</span>
					<input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple" required>
				</label>
				<p class="side-note">Drag & Drop works too.</p>
				<input type="submit" value="Submit images" class="button" id="submitButton">
			</form>
		</details>
	</article>

	<article class="box" id="form-inputs-test">
		<details>
			<summary>All form inputs</summary>
			<form>
				<label>
					<span class="label">Text</span>
					<input type="text" placeholder="This is a placeholder">
				</label>

				<label>
					<span class="label required">Number</span>
					<input type="number" required>
				</label>

				<label>
					<span class="label">Date</span>
					<input type="date">
				</label>

				<label>
					<span class="label">Datetime-local</span>
					<input type="datetime-local">
				</label>

				<label>
					<span class="label">Month</span>
					<input type="month">
				</label>

				<label>
					<span class="label">Week</span>
					<input type="week">
				</label>

				<label>
					<span class="label">Time</span>
					<input type="time">
				</label>

				<label>
					<span class="label">Colours</span>
					<input type="color">
				</label>

				<label>
					<span class="label">Checkbox</span>
					<input type="checkbox">
				</label>
				<label>
					<input type="checkbox">
					<span class="label required">Checkbox</span>
				</label>

				<label>
					<span class="label">Radio 1</span>
					<input type="radio" name="radio">
				</label>
				<label>
					<input type="radio" name="radio">
					<span class="label">Radio 2</span>
				</label>

				<label>
					<span class="label">File</span>
					<input type="file">
				</label>

				<label>
					<span class="label">Range</span>
					<input type="range">
				</label>

				<p class="required-input side-note">
					<span class="required"></span> = Required
				</p>
			</form>
		</details>
	</article>

	<article class="box">
		<details>
			<summary>Table</summary>
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
		</details>
	</article>

	<article class="box">
		<details>
			<summary>Loading</summary>
			<p class="loading"></p>
			<label>
				<span class="label">Testing the march of progress:</span>
				<progress id="test-progress"></progress>
			</label>
		</details>
	</article>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
