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

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<div class="buttons margins-off">
		<a href="index.php" class="button light">
			<i class="material-icons">arrow_back</i>
			<?= $lang->FRONTPAGE ?>
		</a>

		<a href="edit-collection.php" class="button">
			<i class="material-icons">edit</i>
		</a>

		<a href="upload.php?id=<?= $collection->random_uid ?>" class="button margins-off">
			<i class="material-icons">add</i>
			<i class="material-icons">photo_library</i>

			<?= ''// $lang->ADD_NEW_IMG ?>
		</a>

		<a href="map.php?cid=<?= $collection->random_uid ?>" class="button margins-off">
			<i class="material-icons">map</i>
			<?= $lang->TO_MAP ?>
			<i class="material-icons">arrow_forward</i>
		</a>
	</div>

	<div class="box">
	    <table>
		    <colgroup>
			    <col class="number">
			    <col class="img">
			    <col>
			    <col class="coordinates">
		    </colgroup>
	        <thead>
		        <tr class="table-title">
			        <th colspan="4">
				        <?= $lang->TABLE_HEADER ?>:
				        <span class="collection-name" id="collection-name"
				              data-name="<?= $collection->name ?>" data-uid="<?= $collection->random_uid ?>">
					        <?= $collection->name ?>
				        </span>
			        </th>
		        </tr>
	            <tr>
	                <th class="number">#</th>
	                <th class="center">
		                <i class="material-icons">image</i>
	                </th>
	                <th><?= $lang->IMG_NAME ?></th>
	                <th class="center">
		                <i class="material-icons">map</i>
	                </th>
	            </tr>
	        </thead>
	        <tbody>
	        <?php foreach( $collection->imgs as $index => $img ) : ?>

	            <tr id="">
		            <td class="number"><?= $index+1 ?></td>
	                <td class="center">
		                <a href="<?= WEB_PATH ?>/img/img.php?id=<?= $img->random_uid ?>">
			                <img src="<?= WEB_PATH ?>/img/img.php?id=<?= $img->random_uid ?>&thumb" height="25px">
		                </a>
	                </td>
	                <td><?= $img->name ?></td>
	                <td class="center">
		                <?php if ( $img->latitude ) : ?>
	                    <a href="<?= WEB_PATH ?>/map.php?cid=<?= $collection->random_uid ?>&iid=<?= $img->random_uid ?>"
	                        class="image-link margins-off">
		                    <span>
		                        <i class="material-icons">link</i>
		                    </span>
		                    <span>
			                    <?= fNumber($img->latitude, 4) ?><br>
			                    <?= fNumber($img->longitude, 4) ?>
		                    </span>
	                    </a>
		                <?php endif; ?>
	                </td>
	            </tr>
	        <?php endforeach; ?>
	        </tbody>
	    </table>
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
