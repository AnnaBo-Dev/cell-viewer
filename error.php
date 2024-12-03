<?php
    $errornumber = \http_response_code();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $errornumber ?> </title>
    <link rel="icon" type="image/x-icon" href="/img/Favicon_HOPPECKE_CellViewer.ico">
    <link rel="stylesheet" href="assets/css/hoppeckeCD.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<!-- BODY -->
<body class="pt-4 px-3 pb-3 m-auto" style="max-width: 1920px;">
<!-- FORM -->
<form action="" method="post" id="error" class="align-items-center">
    <!-- HEADER Cell-Viewer -->
    <header>
        <div class="d-lg-flex d-sm-flex align-items-center d-flex flex-sm-column mb-3">
            <!-- logo -->
            <div class="col mb-3">
                <div class="text-center">
                    <a href="index.php">
                        <img alt="cell_viewer" src="img/Logo_HOPPECKE.jpg" class="logo" style="width: 100%;">
                    </a>
                </div>
            </div>
            <!-- tool name-->
            <div class="col">
                <div class="text-center">
                    <a href="index.php" style="width: 100%; text-decoration: none;">
                        <h1>Cell-Viewer</h1>
                    </a>
                </div>
            </div>
            <div class="col d-sm-none d-lg-block"></div>
            <div class="col d-sm-none d-lg-block"></div>
            <div class="col d-sm-none d-lg-block"></div>
        </div>
    </header>

    <!-- error img + message -->
    <div class="col p-5 my-auto text-center side-error" style="background-color: #14775A;">
        <section class="position-relative">
            <?php if ($errornumber === 405) { ?>
                <img src="./img/405.png" class="side-error-image img-fluid">
                <h1 class="my-5">Timeout!</h1>
                <h4>You have been logged out automatically.<br>
                    Please open this page again via the customer portal!<br>
                    <br>
                    If you click "RELOAD" you would be redirected.
                    <br><br>
                    <button id="reload" class="btn-lt-green" onclick="openCP()">RELOAD</button>
                </h4> 
            <?php } else if ($errornumber === 404 || $errornumber !== 200) { ?>
                <img src="./img/404.png" class="side-error-image img-fluid">
                <h1 class="my-5">Page could not be found.</h1>
                <button id="reload" class="btn-lt-green" onclick="openCPLogin()">RELOAD</button>
            <?php } ?>
        </section>
    </div>
</form>

</body>
</html>

<script type="text/javascript">
    function openCP() {
        window.open('https://www.hoppecke.com/de/kundenportal/dashboard/tools/');
    }
    function openCPLogin() {
        window.open('https://www.hoppecke.com/de/kundenportal/login/');
    }
</script>