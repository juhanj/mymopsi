<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-back-button.php'; ?>

<main class="main-body-container width-90">

	<form enctype='multipart/form-data' id="upload-form" class="box">
		<!-- File type input -->
		<label id="fileinput-label" hidden>
			<span class="label center" id="file-input-label-text">
				<i class="material-icons">save_alt</i>
				<?= $lang->FILE_INPUT ?>
			</span>
			<input type="file" name="image" accept="image/*" id="fileInput">
		</label>

		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="MAX_FILE_SIZE" value="10048576">
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="singe_image_metadata">
	</form>

	<div class="image-container">
		<img src="" id="imagePreview" alt="Image preview" hidden class="image">
	</div>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
