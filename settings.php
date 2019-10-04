<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */
?>
<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<article class="feedback" id="feedback"></article>

<main class="main-body-container">

	<a href="index.php" class="button return">
		<?= $lang->RETURN_INDEX ?>
	</a>

	<article class="box lang-settings" id="languages">
		<h2 class="settings-head"><?= $lang->SETT_LANG_HEAD ?></h2>
		<p><?= $lang->SETT_LANG_INFO ?></p>

		<label for="english">
			<input type="radio" id="english" name="lang" value="en"
				<?= $lang->lang == 'en' ? 'checked' : '' ?>>
			<span class="label"><?= $lang->SETT_LANG_ENG ?></span>
		</label>

		<label for="finnish">
			<input type="radio" id="finnish" name="lang" value="fi"
				<?= $lang->lang == 'fi' ? 'checked' : '' ?>>
			<span class="label"><?= $lang->SETT_LANG_FIN ?></span>
		</label>
	</article>

    <article class="box links">
        <h2>Links</h2>
        <ul>
            <li>
                <a href="https://github.com/juhanj/mymopsi">Github page</a>
            </li>
            <li>
                <a href="./tests/">Tests</a>
            </li>
        </ul>
    </article>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
