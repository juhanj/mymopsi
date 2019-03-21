<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var $db DBConnection
 */

if ( !empty($_GET['id'])
    and sizeof($_GET['id']) == 4
    and ctype_alnum($_GET['id']) ) {
    $_SESSION['feedback'] = "<p class='error'>Please stop accessing pages you don't have access to.<br>Correct ID required.</p>";
	header( "Location:index.php" );
	exit();
}

$feedback = check_feedback_POST();

$id = $_GET['id'];



?>

<!DOCTYPE html>
<html lang="fi">

<?php require './components/html-head.php'; ?>

<body>

<?php require './components/html-header.php'; ?>

<main class="main_body_container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

    <table>
        <thead>
        <tr><th></th></tr>
        </thead>
        <tbody>
        <tr><td></td></tr>
        </tbody>
    </table>

</main>

<?php require './components/html-footer.php'; ?>

<script>
</script>

</body>
</html>
