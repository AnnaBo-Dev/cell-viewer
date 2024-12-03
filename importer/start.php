<?php
// ini_set('display_errors', '1');
// ini_set('error_reporting', E_ALL);

// Load files and the database configuration
require_once __DIR__ . '/config/configImport.php'; 
$dbconn = openConn();

$success = null;
if (isset($_POST['import'])) { 
    require_once __DIR__ . '/go.php'; 
}
require_once __DIR__ . '/selectVersionsFromDB.php';

$versions = getDBinsertVersion($dbconn);

?>

<!-- START HTML -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer for Cell-Viewer</title>
    <link rel="icon" type="image/x-icon" href="../img/Favicon_HOPPECKE_CellViewerImporter.ico">
    <link rel="stylesheet" href="../assets/css/hoppeckeCD.css">
    <link rel="stylesheet" href="assets/css/import.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">  
</head>

<!-- BODY -->
<body class="p-3 m-auto" style="max-width: 1920px;">
<!-- FORM -->
<form action="" method="post" id="cellviewer_CSV_importer" enctype="multipart/form-data">

    <!-- <?php if(!empty($statusMsg)) { ?>
        <div class="col-xs-12">
            <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
        </div>
    <?php } ?> -->
    
    <!-- HEADER -->
    <header>
        <div class="d-lg-flex d-sm-flex align-items-center d-flex flex-sm-column mb-3">
            <div class="col mb-3">
                <div class="text-center">
                    <a href="start.php">
                        <img alt="HOPPECKE Cell-Viewer" src="/img/Logo_HOPPECKE.jpg" class="logo">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="container-fluid">
                    <a class="navbar-brand" href="start.php">
                        <img src="/img/Favicon_HOPPECKE_CellViewerImporter.ico" alt="Logo" width="50" height="45" class="me-2 align-middle">
                        <h1 class="d-inline-block align-middle"> Cell-Viewer Importer</h1>
                    </a>
                </div>
            </div>
            <div class="col d-sm-none d-lg-block">
                <div class="p-3 text-center"></div>
            </div>
            <div class="col d-sm-none d-lg-block">
                <div class="p-3 text-center"></div>
            </div>
        </div>
    </header>

    <!-- header line -->
    <div class="d-lg-flex d-sm-flex align-items-center p-1 mb-3" style="background-color: #A1C9BD; padding: 0.25% 0"></div>

    <!-- file import -->
    <div class="d-lg-flex flex-row" style="margin: auto;">
        <div class="col">
            <div class="p-2 mb-2 box align-middle">
                <div class="box-header mb-3">
                    <h3>Choose CSV file</h3>
                </div>
                <div>
                    <input type="file" name="file" id="file" accept=".csv" onchange="updateImportButton()">
                    <button class="btn btn-green" title="import" type="submit" name="import" id="import-btn" disabled>Import</button>
                </div>
            </div>
        </div>
    </div>

    <!-- version update -->
    <div class="d-lg-flex flex-row" id="version_update" style="margin: auto;">
        <div class="col">
            <table>
                <tr>
                    <th>VERSION</th>
                    <th>COMMENT</th>
                    <th>USER</th>
                    <th>DATE</th>
                </tr>
                <tr>
                    <?php if (isset($versions['id'])) { ?>
                        <td><?= $versions['version'] ?></td>
                        <td><?= $versions['comment'] ?></td>
                        <td><?= $versions['user'] ?></td>
                        <td><?= $versions['date'] ?></td>
                    <?php } ?>
                </tr>
                <tr>
                    <td><input type="text" class="form-control" id="version_number" name="version_number" onchange="updateImportButton()"
                        placeholder="<?php echo htmlspecialchars($versions['version'])?>" value="" required>
                    </td>
                    <td><input type="text" class="form-control" id="comment_text" name="comment_text" onchange="updateImportButton()"
                        placeholder="<?php echo htmlspecialchars('Please enter the changes from this data update')?>" value="" required>
                    </td>
                    <td><input type="text" class="form-control" id="user_name" name="user_name" onchange="updateImportButton()"
                        placeholder="<?php echo htmlspecialchars('F. Lastname')?>" value="" required>
                    </td>
                    <td><input type="text" class="form-control" id="update_date" name="update_date" readonly="readonly"
                        value="<?php $timestamp = time(); $currentDate = gmdate('Y-m-d', $timestamp); echo htmlspecialchars($currentDate)?>">
                    </td>
                </tr>
            </table>
        </div>
        <div class="col text-center">
            <img id="loader" width="100px" src="../img/fade-stagger-squares.svg" hidden>
            <?php if (!empty($success) && !empty($success) === true) { ?>
                <img id="check" width="50px" src="../img/icons8-check.gif">
            <?php } else if ($success === false) { ?>
                <img id="cross" width="50px" src="../img/icons8-cross.gif">
                <div id="errorMessage"><?= $errorMessage ?></div>
            <?php } ?>
        </div>
    </div>

    
</form>

<!-- footer line -->
<footer id="cv_footer">
    <div class="d-lg-flex d-sm-flex align-items-center p-1 mb-3" style="background-color: #A1C9BD; padding: 0.25% 0">
    </div>
</footer>
</body>
</html>

<!-- load the js files -->
<script src="../assets/js/jquery.min.js" type="text/javascript" async></script>
<script src="assets/js/ajaxExtension.js" async></script>

<script type="text/javascript" async>
   
    /** disable default the import button */
    const importBtn = document.querySelector('#import-btn');
    document.addEventListener("DOMContentLoaded", function(event) {
        importBtn.disabled = true;
    });

    /**
     * if field filed, then you can click import
     * else get an alert
     */
    function updateImportButton() {
        var versionNumber = document.querySelector('#version_number').value;
        var commentText = document.querySelector('#comment_text').value;
        var userName = document.querySelector('#user_name').value;
        var updateDate = document.querySelector('#update_date').value;
        var uploadedFile = document.querySelector('#file').value;
        console.log(uploadedFile);
        
        if (versionNumber === ''
            || commentText === ''
            || userName === ''
            || updateDate === ''
            || uploadedFile === ''
        ) {
            importBtn.disabled = true;
        } else {
            importBtn.disabled = false;
        }
    }
</script>
