<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

$feedback = check_feedback_POST();

$collection = Collection::fetchCollection( $db, $_GET['id'] );

if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>No collection found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}

$collection->getCollectionImgs( $db );
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

	<a href="index.php" class="button">
		<?= $lang->FRONTPAGE ?>
	</a>

	<a href="upload.php?id=<?= $collection->random_uid ?>" class="button"><?= $lang->ADD_NEW_IMG ?></a>

	<a href="map.php?cid=<?= $collection->random_uid ?>" class="button">
		<i class="material-icons">map</i><?= $lang->TO_MAP ?>
	</a>

    <table>
        <thead>
            <tr>
                <th class="number">#</th>
                <th>
	                <i class="material-icons">image</i>
                </th>
                <th><?= $lang->IMG_NAME ?></th>
                <th>
	                <i class="material-icons">map</i>
                </th>
                <th>
	                <i class="material-icons">location_on</i>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach( $collection->imgs as $index => $img ) : ?>

            <tr id="">
	            <td class="number"><?= $index+1 ?></td>
                <td>
	                <a href="<?= WEB_PATH ?>/img/img.php?id=<?= $img->random_uid ?>">
		                <img src="<?= WEB_PATH ?>/img/img.php?id=<?= $img->random_uid ?>&thumb" height="25px">
	                </a>
                </td>
                <td><?= $img->name ?></td>
                <td><?= $img->latitude?>
                    <br><?= $img->longitude ?>
                </td>
                <td>
                    <a href="<?= WEB_PATH ?>/map.php?cid=<?= $collection->random_uid ?>&iid=<?= $img->random_uid ?>">
	                    <i class="material-icons">link</i>
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
