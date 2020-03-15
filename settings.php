<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */
?>
<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<section class="feedback" id="feedback"></section>

<main class="main-body-container">

	<a href="javascript:history.go(-1)" class="button return">
		<?= $lang->RETURN ?>
	</a>

	<article class="box settings">
		<h2 class="settings-head"><?= $lang->SETT_LANG_HEAD ?></h2>
		<p><?= $lang->SETT_LANG_INFO ?></p>

		<label for="english">
			<input type="radio" name="lang" value="en" id="english"
				<?= $lang->lang === 'en' ? 'checked' : '' ?>
			>
			<span class="label">🇬🇧 English</span>
		</label>

		<label for="finnish">
			<input type="radio" name="lang" value="fi" id="finnish"
				<?= $lang->lang == 'fi' ? 'checked' : '' ?>
			>
			<span>🇫🇮 Suomi</span>
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
	        <li>
		        <a href="./logout.php">Logout</a>
	        </li>
        </ul>
    </article>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
