<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

/**
 * @param string $id
 * @return string
 */
function check_id ( string $id ) : string {
	if ( empty($_GET['id']) ) {
		return "<p class='error'>No ID given.<br>
            Please enter ID of collection into the box below.</p>";
	}

	return '';

	if ( strlen($_GET['id']) != 4 ) {
		return "<p class='error'>ID must the four (4) characters long.</p>";
	}
	elseif ( !ctype_alnum($_GET['id']) ) {
		return "<p class='error'>Only aplhanumeric characters.</p>";
	}
	return '';
}

if ( $_SESSION['feedback'] = check_id($_GET['id']) ) {
	header( "Location:index.php" );
	exit();
}

$feedback = check_feedback_POST();

$collection = new Collection( $db, $_GET['id'] );

if ( !$collection->id ) {
	$_SESSION['feedback'] = "<p class='error'>No collection found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}
//debug( $collection );
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body>

<?php require 'html-header.php'; ?>

<main class="main-body-container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

	<a href="upload.php?id=<?= $collection->id ?>" class="button"><?= $lang->ADD_NEW_IMG ?></a>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>IMG</th> <!-- //TODO: Use material icon here? --jj190331 -->
                <th>Filename</th>
                <th>Location</th>
                <th>Location on map (link)</th> <!-- //TODO: Use material icon here? --jj190331 -->
            </tr>
        </thead>
        <tbody>
        <?php foreach( $collection->imgs as $img ) : ?>

            <tr id="">
	            <td><?= $img->id ?></td>
                <td>
<!--                    <i class="material-icons">broken_image</i>-->
		            <img src="<?= WEB_PATH ?>/img/img.php?cid=<?= $collection->id ?>&iid=<?= $img->id ?>" height="25px">
                </td>
                <td><?= $img->name ?></td>
                <td><?= $img->latitude?>
                    <br><?= $img->longitude ?>
                </td>
                <td>
                    <a href="<?= WEB_PATH ?>/img/img.php?cid=<?= $collection->id ?>&iid=<?= $img->id ?>">
                        <?= "link to img" ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
