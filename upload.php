<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

/**
 * <p>Creates a new folder for the new collection.
 * <p>Uses either <code>bin2hex( random_bytes(2) )</code> or <code>rand(1000,9999)</code> for name creation,
 * depending on if <code>random_bytes()</code> is available.
 * End result is four character name.
 * <p>Also checks that directory name is unique.
 *
 * @return array|bool Returns <code>['path', 'id']</code>, or false on failure.
 */
function create_new_directory () : array {
    $new_coll_folder = INI['Misc']['path_to_collections'];
    $uid = '';

	while ( true ) {
        try {
	        $uid = bin2hex( random_bytes( 2 ) );
        }
        catch ( Exception $e ) {
            print_r($e);
            $uid = (string)rand(1000,9999);
        }

		/**
		 * Multiple slashes in paths should not cause problems.
		 */
        $new_coll_folder = "{$new_coll_folder}/{$uid}";

        if ( !file_exists( $new_coll_folder ) ) {
            break;
        }
	}

    return ( mkdir( $new_coll_folder, 0755, true )
        ? array( 'path'=>$new_coll_folder, 'id'=>$uid )
        : array() );
}

/**
 * Checks the PHP error code and returns either false, or a specific error message.
 * False is good, true is bad.
 *
 * @param int $error Error code from <code>$_FILES['userfile']['error']</code>.
 * @return bool|string Either false (no errors found), or specific error message.
 */
function check_file_errors ( int $error ) {
    if ( $error == UPLOAD_ERR_OK ) {
        return false;
    }
    else {
	    switch ( $error ) {
		    case UPLOAD_ERR_INI_SIZE:
			    return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
		    case UPLOAD_ERR_FORM_SIZE:
			    return  "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
		    case UPLOAD_ERR_PARTIAL:
			    return "The uploaded file was only partially uploaded";
		    case UPLOAD_ERR_NO_FILE:
			    return "No file was uploaded";
		    case UPLOAD_ERR_NO_TMP_DIR:
			    return "Missing a temporary folder";
		    case UPLOAD_ERR_CANT_WRITE:
			    return "Failed to write file to disk";
		    case UPLOAD_ERR_EXTENSION:
			    return "File upload stopped by extension";
		    default:
			    return "Unknown upload error";
	    }
    }
}

/**
 * @param array $failure
 * @param array $success
 * @return string
 */
function create_complex_and_fancy_feedback ( array $failure, array $success ) : string {
    $fail_String = "";
    $succes_string = "";
    $return_string = "";
    $fails_count = count($failure);
	$success_count = count($success);
	$total = $fails_count + $success_count;

    foreach ( $failure as $key => $fail) {
        $fail_String .= "<li>{$key} - {$fail['name']} - {$fail['error']}</li>";
    }
	foreach ( $success as $key => $yes) {
		$succes_string .= "<li>{$yes['name']}</li>";
	}

	$return_string .=
		"<p class='error'>
            <details>
                <summary>Failed uploads: {$fails_count} / {$total}</summary>
                <ul>{$fail_String}</ul>
            </details>
        </p>
        <p class='success'>
            <details>
                <summary>Successful uploads: {$success_count} / {$total}</summary>
                <ul>{$succes_string}</ul>
            </details>
        </p>";

	return $return_string;
}

if ( !empty($_FILES['images']) ) {
    // ini_set('max_execution_time', '30');

	$img_array = $_FILES['images'];

    $new_dir = create_new_directory();

    if ( $new_dir ) {
	    $successful_uploads = array();
	    $failed_uploads = array();
	    $_SESSION['new_coll_id'] = $new_dir['id'];

	    foreach ( $img_array['error'] as $key => $error ) {
            // Check for possible upload errors. If any, leave a note and skip to next file.
	        if ( $error != UPLOAD_ERR_OK ) {
                $failed_uploads[$key] = array(
	                'name' => $img_array['name'][$key],
	                'error' => $error
                );
		        continue;
            }

		    // Check if the uploaded file is an image according to PHP.
		    //TODO: Not perfect, might give false results. Check this? exif_imagetype() --JJ190313
		    if ( !exif_imagetype( $img_array['tmp_name'][$key] ) ) {
			    $failed_uploads[$key] = array(
				    'name' => $img_array['name'][$key],
				    'error' => 'Not an image, according to PHP.'
			    );
			    continue;
		    }

		    $new_path = $new_dir['path'] . '/' . sprintf('%04d', $key) . "."
                . pathinfo($img_array['name'][$key],PATHINFO_EXTENSION );
		    move_uploaded_file( $img_array['tmp_name'][$key], $new_path );

		    $successful_uploads[$key] = array(
			    'name' => $img_array['name'][$key]
            );
        }

	    if ( count($failed_uploads) >= count($img_array['name']) ) {
		    rmdir($new_dir['path']);
		    $_SESSION['feedback'] .= "<p class='error'>Failed all the uploads.</p>";
		    $_SESSION['new_coll_id'] = false;
        }
	    elseif ( count($failed_uploads) <= 0 ) {
		    $_SESSION['feedback'] .= "<p class='success'>All uploads successful, and saved on the server.</p>";
        }
	    else {
		    $_SESSION['feedback'] .= "<p class='info'>Some images failed to upload, but a collection was created nonetheless.</p>";
        }

	    $_SESSION['feedback'] .= create_complex_and_fancy_feedback( $failed_uploads, $successful_uploads );
    }
    else {
	    $_SESSION['feedback'] = "<p class='error'>Could not create directory. Could not create collection.</p>";
    }
}

$feedback = check_feedback_POST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body>

<?php require 'html-header.php'; ?>

<main class="main_body_container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

    <?php if ( !empty($_SESSION['new_coll_id']) ) : ?>
    <div class="" id="success-redirect">
        <p>Collection ID: <span id="new-collection-id"><?= $_SESSION['new_coll_id'] ?></span></p>
        <a href="./view.php?id=<?= $_SESSION['new_coll_id'] ?>" class="button">

            <span class="loading-icon" id="collection-loading"></span>
            Link to newly created collection

        </a>
    </div>
    <?php endif; ?>

    <form method="post" enctype='multipart/form-data'>
        <label>
            New collection of images:
            <input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple">
        </label>

        <input type="submit" name="submit" value="Submit images" class="button" id="submitButton" disabled>

        <p class="side-note">Drag & Drop works too.</p>
    </form>

    <p id="filesInfo">
        <!-- For info on files to be uploaded. -->
    </p>
</main>

<?php require 'html-footer.php'; ?>

<script>
    "use strict";
    let fileInput = document.getElementById('fileInput');
    let submitButton = document.getElementById('submitButton');
    let filesInfo = document.getElementById('filesInfo');

    fileInput.onchange = ( event ) => {
        let target = event.target;
        let tempHTMLlist = '';

        tempHTMLlist = "<ul>";

        Array.from(target.files).forEach( file => {
            tempHTMLlist += `<li>${file.name} - ${file.size/1000}kb - ${new Date(file.lastModifiedDate).toLocaleString()}</li>`;
        });

        tempHTMLlist += "</ul>";
        filesInfo.innerHTML = tempHTMLlist;
        submitButton.disabled = false;
    };

    <?php if ( !empty($_SESSION['new_coll_id']) ) : ?>
        window.onload = () => {

            sendJSON( { "req" : "runExiftool", id: "<?= $_SESSION[ 'new_coll_id' ] ?>"} )
                .then( (jsonResponse) => {
                    /*
                        TODO: Should only be receiving a boolean from here.
                        Show loading icon? Need a callback function for that, not in the ajax-function.
                        Show ID somewhere, outside just a link.
                     */
                    console.log(jsonResponse);
                } );
        };
    <?php
        unset( $_SESSION['new_coll_id']);
        endif;
    ?>
</script>

</body>
</html>
