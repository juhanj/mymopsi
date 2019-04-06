<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

function check_id ( string $id ) : string {
	if ( empty($_GET['id']) ) {
		return "<p class='error'>No ID given.<br>
            Please enter ID of collection into the box below.</p>";
	}
	elseif ( strlen($_GET['id']) != 4 ) {
		return "<p class='error'>ID must the four (4) characters long.</p>";
	}
	elseif ( !ctype_alnum($_GET['id']) ) {
		return "<p class='error'>Only aplhanumeric characters.</p>";
	}
	return '';
}

if ( $_SESSION['feedback'] = check_id($_GET['id']) ) {
	$_SESSION['feedback'] = "<p class='error'>No ID given.<br>
        Please enter ID of collection into the box below.</p>";
	header( "Location:index.php" );
	exit();
}

if ( empty($_GET['id']) ) {
	$_SESSION['feedback'] = "<p class='error'>No ID given.<br>
        Please enter ID of collection into the box below.</p>";
	header( "Location:index.php" );
	exit();
}
elseif ( strlen($_GET['id']) != 4 ) {
	$_SESSION['feedback'] = "<p class='error'>ID must the four (4) characters long.</p>";
	header( "Location:index.php" );
	exit();
}
elseif ( !ctype_alnum($_GET['id']) ) {
    $_SESSION['feedback'] = "<p class='error'>Only aplhanumeric characters.</p>";
	header( "Location:index.php" );
	exit();
}

$feedback = check_feedback_POST();

$collection = new Collection( $db, $_GET['id'] );

if ( !$collection->exists ) {
	$_SESSION['feedback'] = "<p class='error'>No collection found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body>

<?php require 'html-header.php'; ?>

<main class="main_body_container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

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
                <td>000
                <td>
                    <i class="material-icons">broken_image</i>
                </td>
                <td><?= $img['filename'] ?></td>
                <td><?= $img['lat']?>
                    <br><?= $img['long'] ?>
                </td>
                <td>
                    <a href="<?= ENV ?>/img/img.php?cid=<?= $collection->id ?>&iid=<?= $img['id'] ?>">
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
