<?php

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

// DB connection
require_once __DIR__ . '/config/requestCheck.php';
require_once __DIR__ . '/selectDBfunctions.php';
$dbconn = openConn();

define('INCHES', 0.0393701);    // in   -> mm
define('GALLON', 0.264172);     // gal  -> l
define('POUNDS', 2.20462);      // lbs  -> kg
$aktivFormatType = (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') ? 'US' : ($_REQUEST['format_type'] ?? 'EU');

// fill the select fields of user input to select a battery
$technologies = getSelectValues($dbconn, 'technology');
$series = groupByField(getSelectValues($dbconn, 'series'), 'technology');

$serie = getSelectValues($dbconn, 'series');
$type = groupByField(getSelectValues($dbconn, 'type'), 'series');
if (isset($_POST['tech']) ? $typevalue = $_POST['battery'] : '');

$quantity = isset($_POST['quantity']) ? \intval($_POST['quantity']) : 1;
if (isset($_POST['submit']) ? $battery = getBattery($dbconn, $typevalue, $quantity, $requestType) : '');
if (isset($_POST['format_type']) ? $battery = getBattery($dbconn, $typevalue, $quantity, $requestType) : '');

// field label arrays
$capacityLabel = [
    'FNC' => [ 
        'C5 @ 1,00 V/C, 20°C',
    ],
    'VLA' => [
        'C10 @ 1,80 V/C, 20°C',
    ],
    'VRLA' => [
        'C10 @ 1,85 V/C, 20°C',
    ],
];

$filledStatus = [
    'FNC' => [
        'filled and uncharged',
        'unfilled',
        'filled and charged',
    ],
    'VLA' => [
        'unfilled',
        'filled and charged',
    ],
    'VRLA' => [
        'filled and charged',
    ],
];

$packingType = [
    'Palett',
    'Cardboard (Carton)',
    'Chipboard (Wood)',
];

$claculationType = [
    'Standard',
    'Reverse',
];
?>

<!-- START HTML -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cell-Viewer</title>
    <link rel="icon" type="image/x-icon" href="/img/Favicon_HOPPECKE_CellViewer.ico">
    <link rel="stylesheet" href="assets/css/hoppeckeCD.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<!-- BODY -->
<body class="p-3 m-auto" style="max-width: 1920px;">
<!-- FORM -->
<form action="" method="post" id="cellviewer">
    <!-- HEADER -->
    <header class="d-print-none">
        <div class="d-lg-flex d-sm-flex align-items-center d-flex flex-sm-column mb-3">
            <div class="col mb-3">
                <div class="text-center">
                    <a href="#">
                        <img alt="HOPPECKE Cell-Viewer" src="./img/Logo_HOPPECKE.png" class="logo">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="text-center">
                    <a class="navbar-brand" href="index.php">
                        <img src="./img/Favicon_HOPPECKE_CellViewer.ico" alt="Logo" width="50" height="45" class="me-2 align-middle">                
                        <h1 class="d-inline-block align-middle"> Cell-Viewer</h1>
                    </a>
                </div>
            </div>
            <div class="col d-sm-none d-lg-block">
                <div class="text-center" id="time_label">Time until automatic logout:</div> 
                <div class="text-center" id="timer" style="color: #14775A; font-weight: 600;"></div> 
            </div>
            <div class="col d-sm-none d-lg-block">
                <div class="p-3 text-center">
                    <table class="outputtabel">
                        <tr>
                            <td class="outputlabel" id="db_insert_version_label" style="text-align: right;">Version:</td>
                            <td class="outputvalue p-2" name="db_insert_version" id="db_insert_version" style="text-align: left;"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col d-lg-block" style="width: 100%;">
                <div class="p-3 text-center">
                    Standard:</br>
                    <input type="hidden" name="aktiv_format_type" id="aktiv_format_type" value="<?= $aktivFormatType ?>">
                    <input class="btn-lt-green p-1" name="format_type" id="us_standard" type="submit" style="width: 40%;" value="US">
                    <input class="btn-lt-green aktive p-1" name="format_type" id="eu_standard" type="submit" style="width: 40%;" value="EU">
                </div>
            </div>
        </div>
    </header>

    <!-- PRINT header -->
    <header class="d-none d-print-flex">
        <div class="d-print-flex align-items-center mb-3">
            <div class="col">
                <div class="text-center">
                    <a href="index.php">
                        <img alt="cell-viewer" src="img/Logo_HOPPECKE_CMYK.png" class="d-none d-print-inline" style="width: 100%;">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="text-center">
                    <a href="index.php" style="width: 100%; text-decoration: none;">
                        <h1>Cell-Viewer</h1>
                    </a>
                </div>
            </div>
            <div class="col d-sm-none d-lg-block d-print-inline"></div>
            <div class="col d-lg-block">
                <div class="d-none d-print-inline">
                    Version: <div class="d-print-font"><?php echo $battery['db_insert_version']['version'] ?></div>          
                </div>
            </div>
            <div class="col d-lg-block">
                <div class="d-none d-print-inline">
                    Standard: <div class="d-print-font"><?= $aktivFormatType ?></div>          
                </div>
            </div>
        </div>
    </header>

    <!-- select options -->
    <div class="d-lg-flex d-sm-flex align-items-center d-flex flex-sm-column p-1 mb-3 d-print-none" style="background-color: #14775A;">
        <!-- select technolgy -->
        <div class="col my-2" style="width: 90%;">
            <div class="p-0" style="background-color: #fff;">
                <select class="btn btn-sm select-box" name="tech" id="tech" title="tech" style="width: 100%; text-align:left" required>
                    <option class="d-none">Technology</option>
                    <?php foreach ($technologies as $tech) { ?>
                        <option value="<?= $tech['technology'] ?>" <?= isset($_POST['tech']) && $tech['technology'] === $_POST['tech'] ? 'selected' : '' ?>><?= $tech['technology'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- select series -->
        <div class="col my-2" style="width: 90%;">
            <div class="p-0" style="background-color: #fff;">
                <select class="btn btn-sm select-box" name="series" id="series" title="series" style="width: 100%; text-align:left" required>
                    <option class="d-none">Series</option>
                    <?php foreach ($series as $technology => $items) { ?> 
                        <optgroup label="<?= $technology ?>" <?= isset($_POST['tech']) && $technology === $_POST['tech'] ? '' : 'style="display:none;"' ?>>
                            <?php foreach ($items as $item) { ?>
                                <option value="<?= $item['series'] ?>" <?= isset($_POST['series']) && $item['series'] === $_POST['series'] ? 'selected' : '' ?>><?= $item['series'] ?></option>    
                            <?php } ?>
                        </optgroup>    
                    <?php } ?>
                </select> 
            </div>
        </div> 
        <!-- select type -->
        <div class="col my-2" style="width: 90%;">
            <div class="p-0" style="background-color: #fff;">
                <select class="btn btn-sm select-box" name="type" id="type" title="type" style="width: 100%; text-align:left" onchange="selectValue();" required>
                    <option class="d-none">Type</option>
                    <?php foreach ($type as $serie => $items) { ?> 
                        <optgroup label="<?= $serie ?>" <?= isset($_POST['series']) && $serie === $_POST['series'] ? '' : 'style="display:none;"' ?>>
                            <?php foreach ($items as $item) { ?>
                                <option value="<?= $item['type'] ?>" <?= isset($_POST['type']) && $item['type'] === $_POST['type'] ? 'selected' : '' ?>><?= $item['type'] ?></option> 
                            <?php } ?>
                        </optgroup>    
                    <?php } ?>
                </select>
            </div>
        </div>   
        <!-- input quantity -->
        <div class="col my-2" style="width: 90%;">
            <div class="p-0 input-group input-group-sm" style="background-color: #FAFAFA">
                <span class="p-1 input-group-text d-print-none" id="inputGroup-sizing-sm">Quantity:</span>
                <input type="number" inputmode="numeric" name="quantity" id="quantity" class="form-control" style="border-left: 1px solid #14775A" min="1" required
                    value="<?= htmlspecialchars(isset($_POST['quantity']) ? \intval($_POST['quantity']) : 1)?>"
                    oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
            </div>
        </div>
        <!-- battery input -->
        <div class="col my-2" style="width: 90%;">
            <div class="p-0 input-group input-group-sm" style="background-color: #FAFAFA">
                <span class="p-1 input-group-text d-print-none" id="inputGroup-sizing-sm">Cell:</span>
                <input type="text" name="battery" id="battery" class="form-control" readonly="readonly"
                    value="<?= htmlspecialchars(isset($_POST['type']) ? $_POST['type'] : '') ?>">
            </div>
        </div>
    </div>

    <!-- PRINT select options -->
    <div class="d-none d-print-flex p-1 mb-3">
        <!-- select technolgy -->
        <div class="col my-2" style="width: 90%;">
            <div class="d-print-inline">
                <div>Technology:</div>
                <div class="d-print-font"><?= $_POST['tech'] ?></div>          
            </div>
        </div>
        <!-- select series -->
        <div class="col my-2" style="width: 90%;">
            <div class="d-print-inline">
                <div>Series:</div>
                <div class="d-print-font"><?= $_POST['series'] ?></div>          
            </div>
        </div> 
        <!-- select type -->
        <div class="col my-2" style="width: 90%;">
            <div class="d-print-inline">
                <div>Type:</div>
                <div class="d-print-font"><?= $_POST['type'] ?></div>          
            </div>
        </div>   
        <!-- input quantity -->
        <div class="col my-2" style="width: 90%;">
            <div class="d-print-inline">
                <div>Quantity:</div>
                <div class="d-print-font"><?= $_POST['quantity'] ?></div>          
            </div>
        </div>
        <!-- battery input -->
        <div class="col my-2" style="width: 90%;">
            <div class="d-print-inline">
                <div>Cell:</div>
                <div class="d-print-font"><?= $_POST['type'] ?></div>          
            </div>
        </div>
    </div>

    <!-- submit button line -->
    <div class="d-lg-flex d-sm-flex align-items-center my-2 d-print-none">
        <?php if (isset($battery['alternativeCell']['alternative'])) { ?>
            <!-- alternative cells --> 
            <div class="col my-2 alert-secondary" style="width: 90%; font-size: 15px; color:#14775A;"><strong>This cell is no longer available, <br> click on the alternative Series:</strong> </div>
            <div class="col my-2" style="width: 90%;">
                <div class="p-0 input-group input-group-sm" style="background-color: #FAFAFA">
                    <span class="p-1 input-group-text d-print-none" id="inputGroup-sizing-sm">alternative Series:</span>
                    <button type="button" name="alternative" id="alternative" class="form-control btn-green btn-sm p-1"
                        value="<?= $battery['alternativeCell']['alternative']?>"><?= $battery['alternativeCell']['alternative'] ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="col d-sm-none d-lg-block"></div>
            <div class="col d-sm-none d-lg-block"></div>
        <?php } ?>

        <div class="col d-sm-none d-lg-block"></div>
        <div class="col d-sm-none d-lg-block"></div>
        <div class="col mb-2">
            <div class="p-0">
                <button class="btn-green btn-sm p-1" for="battery" name="submit" id="submit" type="submit" style="width: 100%;" disabled>
                    SUBMIT
                </button>
            </div>
        </div>
    </div>
    
    <!-- output -->
    <div class="d-lg-flex flex-row d-print-flex" id="outputbattery">
        <!-- output column 1 (capacity, dimensions, weight, electrolyte) -->
        <div class="col">
            <!-- capacity | AJAX output -->
            <div class="p-2 mb-2 box" id="capacity">
                <div class="box-header">
                    <h3>Capacity</h3>
                </div>
                <!-- Nominal | AJAX output -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel" id="nominal_label"></td>
                        <td class="outputvalue" name="nominal" id="nominal"></td>
                        <td class="outputunit">Ah</td>
                    </tr>
                </table>
                <!-- Real | AJAX output -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel" id="real_label"></td>
                        <td class="outputvalue" name="real" id="real"></td>
                        <td class="outputunit">Ah</td>
                    </tr>
                </table>
                <!-- Real 2 | AJAX output -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel" id="real2_label"></td>
                        <td class="outputvalue" name="real2" id="real2"></td>
                        <td class="outputunit">Ah</td>
                    </tr>
                </table>
            </div>
            <!-- dimensions -->
            <div class="p-2 mb-2 box" id="dimensions">
                <div class="box-header">
                    <h3>Dimensions</h3>
                </div>
                <table class="outputtabel">
                    <!-- length -->
                    <tr><?php if (isset($battery['dimensions']['length'])) { ?>
                        <td class="outputlabel">Length:</td>
                        <td class="outputvalue" name="length" id="length">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['dimensions']['length'] * INCHES;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo $battery['dimensions']['length'];?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- width -->
                    <tr><?php if (isset($battery['dimensions']['width'])) { ?>
                        <td class="outputlabel">Width:</td>
                        <td class="outputvalue" name="width" id="width">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['dimensions']['width'] * INCHES;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo $battery['dimensions']['width'];?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- height 1 -->
                    <tr><?php if (isset($battery['dimensions']['height1'])) { ?>
                        <td class="outputlabel">Height 1:</td>
                        <td class="outputvalue" name="height1" id="height1">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['dimensions']['height1'] * INCHES;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo $battery['dimensions']['height1'];?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- height 2 -->
                    <tr><?php if (isset($battery['dimensions']['height2'])) { ?>
                        <td class="outputlabel">Height 2:</td>
                        <td class="outputvalue" name="height2" id="height2">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['dimensions']['height2'] * INCHES;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo $battery['dimensions']['height2'];?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                </table>
            </div>
            <!-- weight -->
            <div class="p-2 mb-2 box" id="weight">
                <div class="box-header">
                    <h3>Weight</h3>
                </div>
                <table class="outputtabel">
                    <!-- total weight with electrolyte -->
                    <tr><?php if (isset($battery['weights']['total'])) { ?>
                        <td class="outputlabel">Total:</td>
                        <td class="outputvalue" name="total" id="total">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['weights']['total'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['weights']['total'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- weight without electrolyte -->
                    <tr><?php if (isset($battery['weights']) && isset($battery['weights']['of_electrolyte'])) { ?>
                        <td class="outputlabel">without Electrolyte:</td>
                        <td class="outputvalue" name="without_electrolyte" id="without_electrolyte">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['without_electrolyte'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['without_electrolyte'], 2, ',', '.')?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- weight of electrolyte -->
                    <tr><?php if (isset($battery['weights']['of_electrolyte'])) { ?>
                        <td class="outputlabel">of Electrolyte:</td>
                        <td class="outputvalue" name="of_electrolyte" id="of_electrolyte">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['weights']['of_electrolyte'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['weights']['of_electrolyte'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                </table>
            </div>
            <!-- electrolyte -->
            <div class="p-2 mb-2 box" id="electrolyte">
                <div class="box-header">
                    <h3>Electrolyte</h3>
                </div>
                <table class="outputtabel">
                    <!-- volumen liter -->
                    <tr><?php if (isset($battery['volume']) && isset($battery['electrolyte']['density']) && $battery['volume'] != 0) {?>
                        <td class="outputlabel">Volume:</td>
                        <td class="outputvalue" name="volume" id="volume">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['volume'] * GALLON;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="liter">gal</td>
                            <?php } else {
                                echo number_format(\doubleval($battery['volume']), 2, ',', '.');?></td>
                                <td class="outputunit" id="liter">l</td>                            
                                <?php } ?></td>
                    <?php } ?></tr>
                    <!-- electrolyte type -->
                    <tr><?php if (isset($battery['electrolyte']['electrolyte_type'])) { ?>
                        <td class="outputlabel">Type:</td>
                        <td class="outputvalue" name="electrolyte_type" id="electrolyte_type">
                            <?= $battery['electrolyte']['electrolyte_type'] ?></td>
                    <?php } ?></tr>
                    <!-- density -->
                    <tr><?php if (isset($battery['electrolyte']['density'])) { ?>
                        <td class="outputlabel">Density (20°C):</td>
                        <td class="outputvalue" name="density" id="density">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['electrolyte']['density'], 2, '.', ',');
                            } else {
                                echo number_format($battery['electrolyte']['density'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">kg/l</td>
                    <?php } ?></tr>
                </table>
            </div>
        </div>
        <!-- output column 2 (commercial data, technical data, charging) -->
        <div class="col">
            <!-- commercial data -->
            <div class="p-2 mb-2 box" id="commercialdata">
                <div class="box-header">
                    <h3>Commercial Data</h3>
                </div>
                <!-- material number | AJAX depending on filld status -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel">Mat. No.:</td>
                        <td class="outputvalue" name="matno" id="matno"></td>
                    </tr>
                </table>
                <!-- norm -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['norms']['norm1'])) { ?>
                        <td class="outputlabel">Norm:</td>
                        <td class="outputvalue" name="norms" id="norms">
                            <?= $battery['norms']['norm1']; isset($battery['norms']['norm2']) ? ',' . $battery['norms']['norm2'] : '' ?></td>
                    <?php } ?></tr>
                </table>
            </div>
            <!-- technical data -->
            <div class="p-2 mb-2 box" id="technicaldata">
                <div class="box-header">
                    <h3>Technical Data</h3>
                </div>
                <table class="outputtabel">
                    <!-- internal resistance -->
                    <tr><?php if (isset($battery['technical_data']['internal_resistance'])) { ?>
                        <td class="outputlabel">Internal Resistance:</td>
                        <td class="outputvalue" name="internal_resistance" id="internal_resistance">
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['technical_data']['internal_resistance'], 3, '.', ',');
                            } else {
                                echo number_format($battery['technical_data']['internal_resistance'], 3, ',', '.');
                            } ?></td>
                        <td class="outputunit">mΩ</td>
                    <?php } ?></tr>
                    <!-- short circuit current -->
                    <tr><?php if (isset($battery['technical_data']['short_circuit_current'])) { ?>
                        <td class="outputlabel">Short Circuit Current:</td>
                        <td class="outputvalue" name="short_circuit_current" id="short_circuit_current">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo str_replace('.', ',', $battery['technical_data']['short_circuit_current']);
                            } else {
                                echo str_replace(',', '.', $battery['technical_data']['short_circuit_current']);
                            } ?></td>
                        <td class="outputunit">A</td>
                    <?php } ?></tr>
                    <!-- self discharge -->
                    <tr><?php if (isset($battery['technical_data']['self_discharge'])) { ?>
                        <td class="outputlabel">Self Discharge at 20°C:</td>
                        <td class="outputvalue" name="self_discharge" id="self_discharge">
                            <?= $battery['technical_data']['self_discharge'] ?></td>
                        <td class="outputunit">%</td>
                    <?php } ?></tr>
                    <!-- operating temperature -->
                    <tr><?php if (isset($battery['technical_data']['operating_temperature'])) { ?>
                        <td class="outputlabel">Operating Temperature:</td>
                        <td class="outputvalue" name="operating_temperature" id="operating_temperature">
                            <?= $battery['technical_data']['operating_temperature'] ?></td>
                        <td class="outputunit">°C</td>
                    <?php } ?></tr>
                </table>
                <!-- design life -->
                <?php if ((isset($battery['technical_data']['design_life'])
                    && isset($_POST['series']) ? substr($_POST['series'], 0, 3 ) !== "sun" : '')
                   || (isset($_POST['tech']) ? substr( $_POST['tech'], 0, 3 ) === "FNC" : '')
                ) { ?>
                    <!-- if !sun then echo -->
                    <table class="outputtabel">
                        <tr>
                            <td class="outputlabel">Design Life:</td>
                            <td class="outputvalue" name="design_life" id="design_life">
                                <?= $battery['technical_data']['design_life'] ?></td>
                            <td class="outputunit">Years</td>
                        </tr>
                    </table>
                <?php } ?>
                <!-- cycles -->
                <?php if ((isset($battery['technical_data']['cycles'])
                    && isset($_POST['series']) ? substr($_POST['series'], 0, 3 ) === "sun" : '')
                ) { ?>
                    <!-- if sun then echo -->
                    <table class="outputtabel">
                        <tr>
                            <td class="outputlabel">Cycles (80% DOD):</td>
                            <td class="outputvalue" name="cycles" id="cycles">
                                <?= $battery['technical_data']['cycles'] ?></td>
                            <td class="outputunit">Cycles</td>
                        </tr>
                    </table>
                <?php } ?>
                <table class="outputtabel">
                    <!-- pole pairs -->
                    <tr><?php if (isset($battery['technical_data']['pole_pairs'])) { ?>
                        <td class="outputlabel">Pole Pairs:</td>
                        <td class="outputvalue" name="pole_pairs" id="pole_pairs">
                            <?= $battery['technical_data']['pole_pairs'] ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                    <!-- cell openings -->
                    <tr><?php if (isset($battery['technical_data']['cell_openings'])) { ?>
                        <td class="outputlabel">Cell Openings:</td>
                        <td class="outputvalue" name="cell_openings" id="cell_openings">
                            <?= $battery['technical_data']['cell_openings'] ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                </table>
            </div>
            <!-- charging -->
            <div class="p-2 mb-2 box" id="charging">
                <div class="box-header">
                    <h3>Charging</h3>
                </div>
                <table class="outputtabel">
                    <!-- float charge voltage -->
                    <tr><?php if (isset($battery['charging']['float_voltage'])) { ?>
                        <td class="outputlabel">Float Voltage:</td>
                        <td class="outputvalue" name="float_voltage" id="float_voltage">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo str_replace(',', '.', $battery['charging']['float_voltage']);
                            } else {
                                echo str_replace('.', ',', $battery['charging']['float_voltage']);
                            } ?></td>
                        <td class="outputunit">V/C</td>
                    <?php } ?></tr>
                    <!-- boost charge voltage -->
                    <tr><?php if (isset($battery['charging']['boost_voltage'])) { ?>
                        <td class="outputlabel">Boost Voltage:</td>
                        <td class="outputvalue" name="boost_voltage" id="boost_voltage">
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo str_replace(',', '.', $battery['charging']['boost_voltage']);
                            } else {
                                echo str_replace('.', ',', $battery['charging']['boost_voltage']);
                            } ?></td>
                        <td class="outputunit">V/C</td>
                    <?php } ?></tr>
                    <!-- float charge current -->
                    <tr><?php if (isset($battery['charging']['float_current'])) { ?>
                        <td class="outputlabel">Float Current / 100 Ah:</td>
                        <td class="outputvalue" name="float_current" id="float_current">
                            <?= $battery['charging']['float_current'] ?></td>
                        <td class="outputunit">mA</td>
                    <?php } ?></tr>
                </table>
            </div>
        </div>
        <!-- output column 3 (data battery, aquagen) -->
        <div class="col">
            <!-- data battery -->
            <div class="p-2 mb-2 box" id="databattery">
                <div class="box-header">
                    <h3>Data Battery</h3>
                </div>
                <table class="outputtabel">
                    <!-- total quantity weight -->
                    <tr><?php if (isset($battery['data_battery']['total_weight'])) { ?>
                        <td class="outputlabel">Total Weight:</td>
                        <td class="outputvalue" name="total_weight" id="total_weight">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['data_battery']['total_weight'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['data_battery']['total_weight'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- total quantity weight without electrolyte -->
                    <tr><?php if (isset($battery['data_battery']['total_without_electrolyte'])) { ?>
                        <td class="outputlabel">Weight without Electrolyte:</td>
                        <td class="outputvalue" name="total_without_electrolyte" id="total_without_electrolyte">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['data_battery']['total_without_electrolyte'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['data_battery']['total_without_electrolyte'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- total quantity weight of electrolyte -->
                    <tr><?php if (isset($battery['data_battery']['total_of_electrolyte']) 
                        && isset($battery['weights']['of_electrolyte'])
                        ) { ?>
                        <td class="outputlabel">Weight of Electrolyte:</td>
                        <td class="outputvalue" name="total_of_electrolyte" id="total_of_electrolyte">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['data_battery']['total_of_electrolyte'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['data_battery']['total_of_electrolyte'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- total quantity volume of electrolyte -->
                    <tr><?php if (isset($battery['data_battery']['total_volume']) && isset($battery['electrolyte']['density']) && $battery['volume'] != 0
                    ) { ?>
                        <td class="outputlabel">Volume of Electrolyte:</td>
                        <td class="outputvalue" name="total_volume" id="total_volume">
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['data_battery']['total_volume'] * GALLON;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="liter">gal</td>
                            <?php } else {
                                echo number_format($battery['data_battery']['total_volume'], 2, ',', '.');?></td>
                                <td class="outputunit" id="liter">l</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                    <!-- count drums 30 liter -->
                    <tr><?php if (isset($battery['data_battery']['count_drums_30l'])
                        && isset($battery['electrolyte']['density'])
                        ) { ?>
                        <td class="outputlabel">No. of 30 ltr. Drums:</td>
                        <td class="outputvalue" name="count_drums_30l" id="count_drums_30l">
                            <?= round(ceil($battery['data_battery']['count_drums_30l'])) ?></td>
                    <?php } ?></tr>
                </table>
                <!-- material number seperated | AJAX only if filld status = unfilled -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel">Mat. No. of Electrolyte:</td>
                        <td class="outputvalue" name="matno_seperated" id="matno_seperated"></td>
                    </tr>
                </table>
                <!-- total pole pairs -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['data_battery']['total_pole_pairs'])) { ?>
                        <td class="outputlabel">Pole Pairs System:</td>
                        <td class="outputvalue" name="total_pole_pairs" id="total_pole_pairs">
                            <?= $battery['data_battery']['total_pole_pairs'] ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                </table>
            </div>
            <!-- aquagen -->
            <div class="p-2 mb-2 box" id="aquagen">
                <div class="box-header">
                    <h3>AquaGen</h3>
                </div>
                <!-- number of plugs / total cell openings -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['data_battery']['total_cell_openings'])) { ?>
                        <td class="outputlabel">No. of Plugs:</td>
                        <td class="outputvalue" name="total_cell_openings" id="total_cell_openings">
                            <?= $battery['data_battery']['total_cell_openings'] ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                </table>
                <table class="outputtabel">
                    <!-- aquagen type name -->
                    <tr><?php if (isset($battery['aquagen']['ag_name'])) { ?>
                        <td class="outputlabel">Type:</td>
                        <td class="outputvalue" name="ag_name" id="ag_name">
                            <?= $battery['aquagen']['ag_name'] ?></td>
                    <?php } ?></tr>
                    <!-- aquagen material number -->
                    <tr><?php if (isset($battery['aquagen']['ag_no'])) { ?>
                        <td class="outputlabel">Mat. No:</td>
                        <td class="outputvalue" name="ag_no" id="ag_no">
                            <?= $battery['aquagen']['ag_no'] ?></td>
                    <?php } ?></tr>
                </table>
            </div>
        </div>
        <!-- output column 4 (packing data)-->
        <div class="col">
            <!-- packing data -->
            <div class="p-2 mb-2 box" id="packingdata">
                <div class="box-header">
                    <h3>Packing Data</h3>
                </div>
                <!-- paletts count -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['packing_calculations']['paletts'])) { ?>
                        <td class="outputlabel">No. of Paletts f. Cells:</td>
                        <td class="outputvalue" name="paletts_count" id="paletts_count">
                            <?= ceil($battery['packing_calculations']['paletts']) ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                </table>
                <!-- select packing type -->
                <div class="p-0 d-print-none">
                    <br>
                    <select class="btn btn-sm select-box" name="packing_type" id="packing_type" title="packing_type" style="text-align:left">
                        <option class="d-none">Packing Type</option>
                        <?php foreach ($packingType as $pack) { ?> 
                            <option value="<?= $pack ?>" ?><?= $pack ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="d-none" id="packing_type_print">
                    <br>    
                    <div>Packing Type:</div>
                    <div class="d-print-font" id="packing_type_print_value"></div>
                </div>
                <!-- packing type name number -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputvalue" name="packing_no" id="packing_no"></td>
                        <td class="outputunit-null"></td>
                    </tr>
                </table>
                <!-- packing dimensions -->
                <table class="outputtabel" id="packing_dimensions">
                    <!-- label - brutto = external / netto = internal -->
                    <tr>
                        <td class="outputlabel"></td>
                        <td class="outputvalue">Brutto</td>
                        <td class="outputunit-null"></td>
                        <td class="outputvalue">Netto</td>
                        <td class="outputunit-null"></td>
                    </tr>
                    <!-- length - brutto external / netto = internal -->
                    <tr>
                        <td class="outputlabel">Length:</td>
                        <td class="outputvalue" name="brutto_length" id="brutto_length">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = 1200 * INCHES;
                                echo number_format($usvalue, 0, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo 1200;?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                        <td class="outputvalue" name="netto_length" id="netto_length">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = 1160 * INCHES;
                                echo number_format($usvalue, 0, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo 1160;?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    </tr>
                    <!-- width - brutto external / netto = internal -->
                    <tr>
                        <td class="outputlabel">Width:</td>
                        <td class="outputvalue" name="brutto_width" id="brutto_width">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = 800 * INCHES;
                                echo number_format($usvalue, 0, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo 800;?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                        <td class="outputvalue" name="netto_width" id="netto_width">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = 760 * INCHES;
                                echo number_format($usvalue, 0, '.', ',');?></td>
                                <td class="outputunit" id="millimeter">in</td>
                            <?php } else {
                                echo 760;?></td>
                                <td class="outputunit" id="millimeter">mm</td>
                            <?php } ?></td>
                    </tr>
                    <!-- height - brutto external / netto = internal | AJAX calculation -->
                    <tr>
                        <td class="outputlabel">Height:</td>
                        <td class="outputvalue" name="brutto_height" id="brutto_height"></td>
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') { ?>
                            <td class="outputunit" id="millimeter">in</td>
                        <?php } else { ?>
                            <td class="outputunit" id="millimeter">mm</td>
                        <?php } ?>
                        <td class="outputvalue" name="netto_height" id="netto_height"></td>
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') { ?>
                            <td class="outputunit" id="millimeter">in</td>
                        <?php } else { ?>
                            <td class="outputunit" id="millimeter">mm</td>
                        <?php } ?>
                    </tr>
                </table>
                <!-- select filled status -->
                <div class="p-0 d-print-none">
                    <br>
                    <select class="btn btn-sm select-box" name="filled_status" id="filled_status" title="filled_status" style="width: 100%; text-align:left">
                        <option class="d-none">Filled Status</option>
                        <?php foreach ($filledStatus as $status => $items) { ?> 
                            <?php foreach ($items as $item) { ?> 
                                <option value="<?= $item ?>"
                                <?= isset($_POST['tech']) && $status === $_POST['tech'] ? '' : 'style="display:none;"' ?>>
                                    <?= $item ?>
                                </option>    
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="d-none" id="filled_status_print">
                    <br>    
                    <div>Filled Status:</div>
                    <div class="d-print-font" id="filled_status_print_value"></div>
                </div>
                <br>
                <table class="outputtabel">
                    <!-- layers -->
                    <tr><?php if (isset($battery['packing']['layer']) || isset($battery['packing']['layer_seaworthy'])){ ?>
                        <td class="outputlabel">Layers:</td>
                        <td class="outputvalue" name="layer" id="layer">
                            <?= $battery['packing']['layer'] !== NULL ?: 1 ?></td>
                        <td class="outputunit-null"></td>
                    <?php } ?></tr>
                </table>
                <br>
                <!-- weight per fully load package -->
                <table class="outputtabel" id="weight_p_fullyload_package">
                    <tr><?php if (isset($battery['packing_calculations']['fullyload_package'])) { ?>
                        <td class="outputlabel">Weight per fully load package:</td>
                        <td class="outputvalue" name="fullyload_package" id="fullyload_package">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['packing_calculations']['fullyload_package'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['packing_calculations']['fullyload_package'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?>
                        </td>
                    <?php } ?></tr>
                </table>
                <!-- total quantity weight per fully load package -->
                <table class="outputtabel" id="total_package_weight">
                    <tr><?php if (isset($battery['packing_calculations']['total_weight_package'])) { ?>
                        <td class="outputlabel">Total Package Weight:</td>
                        <td class="outputvalue" name="total_weight_package" id="total_weight_package">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                $usvalue = $battery['packing_calculations']['total_weight_package'] * POUNDS;
                                echo number_format($usvalue, 2, '.', ',');?></td>
                                <td class="outputunit" id="kilogramm">lbs</td>
                            <?php } else {
                                echo number_format($battery['packing_calculations']['total_weight_package'], 2, ',', '.');?></td>
                                <td class="outputunit" id="kilogramm">kg</td>
                            <?php } ?></td>
                    <?php } ?></tr>
                </table>
                <br>
                <!-- no. of paletts electrolyte -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel">No. of Paletts Electrolyte:</td>
                        <td class="outputvalue" name="paletts_electrolyte" id="paletts_electrolyte"></td>
                        <td class="outputunit-null"></td>
                    </tr>
                </table>
                <!-- total weight electrolyte -->
                <table class="outputtabel">
                    <tr>
                        <td class="outputlabel">Total Weight Electrolyte:</td>
                        <td class="outputvalue" name="total_weight_electrolyte" id="total_weight_electrolyte"></td>
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') { ?>
                                <td class="outputunit" id="millimeter">lbs</td>
                            <?php } else { ?>
                                <td class="outputunit" id="millimeter">kg</td>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col d-print-none" id="pricing_column">
            <!-- pricing -->
            <div class="p-2 mb-2 box" id="pricing">
                <div class="box-header">
                    <h3>Pricing</h3>
                </div>
                <!-- gross price per cell | AJAX calculation -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['pricing']['filled'])) { ?>
                        <td class="outputlabel">Gross Price p. Cell:</td>
                        <td class="outputvalue" name="gross_price" id="gross_price">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['pricing']['filled'], 2, '.', ',');
                            } else {
                                echo number_format($battery['pricing']['filled'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">€</td>
                    <?php } ?></tr>
                </table>
                <!-- Lead Surcharge (MTZ) -->
                <table class="outputtabel" id="table_pure_mtz">
                    <tr><?php if (isset($battery['pricing']['mtz'])) { ?>
                        <td class="outputlabel">Lead Surcharge (MTZ):</td>
                        <td class="outputvalue" name="pure_mtz" id="pure_mtz">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['pricing']['mtz'], 2, '.', ',');
                            } else {
                                echo number_format($battery['pricing']['mtz'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">€</td>
                    <?php } ?></tr>
                </table>
                <!-- select calculation type -->
                <div class="p-0 d-print-none">
                    <br>
                    <select class="btn btn-sm select-box" name="calculation_type" id="calculation_type" title="calculation_type" style="width: 100%; text-align:left">
                        <option class="d-none">Calculation Type</option>
                        <?php foreach ($claculationType as $calculation) { ?> 
                            <option value="<?= $calculation ?>" ?><?= $calculation ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="d-none" id="calculation_type_print">
                    <br>    
                    <div>Calculation Type:</div>
                    <div class="d-print-font" id="calculation_type_print_value"></div>
                </div>
                <!-- tooltip to use an point instead of comma  -->
                <table class="outputtabel" >
                    <tr>
                        <td class="alert alert-secondary" role="alert" id="decimalTooltipInsert" style="font-size: small; color:#14775A;">
                            Please use a <strong>point ( . )</strong> instead of a <strong>comma ( , )</strong> !
                        </td>
                    </tr>
                </table>
                <!-- standard calculation -->
                <table class="outputtabel" id="standard_discount">
                    <!-- discount input -->
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_without_MTZ'])) { ?>
                        <td class="outputlabel">Discount p. Cell:</td>
                        <td class="input-group-sm d-print-none" style="width: 30%">
                            <input type="number" inputmode="numeric" name="standard_discount_input" id="standard_discount_input" 
                                class="form-control" style="border: 1px solid #14775A; text-align: right"
                                value="<?=htmlspecialchars(0.0)?>" step="0.1" min="0" max="100" 
                                onKeyDown="{(e)=>['e', 'E', ','].includes(e.key) && e.preventDefault()}"
                                onfocus="showTooltip(); this.select();" onblur="hideTooltip()" onClick="this.select();">
                        </td>
                        <td class="d-none d-print-font" id="standard_discount_input_print"></td>                         
                        <td class="outputunit-null">%</td>
                    <?php } ?></tr>
                    <!-- discount output | AJAX calculation -->
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_MTZ'])) { ?>
                        <td class="outputlabel">Discount Price p. Cell:</td>
                        <td class="outputvalue" name="standard_discount_price" id="standard_discount_price"></td>
                        <td class="outputunit-null">€</td>
                    <?php } ?></tr>
                </table>                
                <!-- reverse calculation -->
                <table class="outputtabel" id="reverse_discount"> 
                    <!-- discount input -->
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_without_MTZ'])) { ?>
                        <td class="outputlabel">Discount Price p. Cell:</td>
                        <td class="input-group-sm d-print-none" style="width: 30%">
                            <input type="number" inputmode="numeric" name="reverse_discount_input" id="reverse_discount_input"
                                class="form-control" style="border: 1px solid #14775A; text-align: right"
                                value="<?=htmlspecialchars(0.00)?>" step="0.10" min="0" max="9999"
                                onKeyDown="{(e)=>['e', 'E', ','].includes(e.key) && e.preventDefault()}"
                                onfocus="showTooltip(); this.select();" onblur="hideTooltip()" onclick="this.select();">
                        </td>
                        <td class="d-none d-print-font" id="reverse_discount_input_print"></td>
                        <td class="outputunit-null">€</td>
                    <?php } ?></tr>
                    <!-- discount output -->
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_MTZ'])) { ?>
                        <td class="outputlabel">Discount p. Cell:</td>
                        <td class="outputvalue" name="reverse_discount_price" id="reverse_discount_price"></td>
                        <td class="outputunit-null">%</td>
                    <?php } ?></tr>
                </table>
                <!-- total quantity price without MTZ | AJAX calculation -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_without_MTZ'])
                        && $battery['calculated_pricing']['calculated_without_MTZ'] != NULL
                    ) { ?>
                        <td class="outputlabel">Total Price without MTZ:</td>
                        <td class="outputvalue" name="calculated_without_MTZ" id="calculated_without_MTZ">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['calculated_pricing']['calculated_without_MTZ'], 2, '.', ',');
                            } else {
                                echo number_format($battery['calculated_pricing']['calculated_without_MTZ'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">€</td>
                    <?php } ?></tr>
                </table>
                <!-- total quantity MTZ | AJAX calculation -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_MTZ'])
                        && $battery['calculated_pricing']['calculated_MTZ'] != NULL
                        && isset($_POST['tech']) && $_POST['tech'] != 'FNC'
                    ) { ?>
                        <td class="outputlabel">Total MTZ:</td>
                        <td class="outputvalue" name="calculated_MTZ" id="calculated_MTZ">
                            <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['calculated_pricing']['calculated_MTZ'], 2, '.', ',');
                            } else {
                                echo number_format($battery['calculated_pricing']['calculated_MTZ'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">€</td>
                    <?php } ?></tr>
                </table>
                <!-- total quantity price | AJAX calculation -->
                <table class="outputtabel">
                    <tr><?php if (isset($battery['calculated_pricing']['calculated_with_MTZ'])
                        && $battery['calculated_pricing']['calculated_with_MTZ'] != NULL
                    ) { ?>
                        <td class="outputlabel">Total Price:</td>
                        <td class="outputvalue" name="total_price" id="total_price">
                        <?php if (isset($_SESSION['format_type']) && $_SESSION['format_type'] === 'US') {
                                echo number_format($battery['calculated_pricing']['calculated_with_MTZ'], 2, '.', ',');
                            } else {
                                echo number_format($battery['calculated_pricing']['calculated_with_MTZ'], 2, ',', '.');
                            } ?></td>
                        <td class="outputunit">€</td>
                    <?php } ?></tr>
                </table>
            </div>
        </div>
    </div>
</form>

<!-- if the JS in Browser is deaktivated -->
<noscript class="align-items-center d-print-none">
    The JavaScript in your browser is disabled or your browser does not support JavaScript!<br>
    Please enable JavaScript in your browser settings or use a different browser!<br>
    </br>
    <strong>How to aktivate JavaScript:</strong><br>
    <a href="https://proton.me/support/enabling-javascript" target="_blank" style="width: 100%; text-decoration: none;">https://proton.me/support/enabling-javascript</a><br>
    </br>
    
    <style>div { display:none; }</style>
</noscript>

<!-- FOOTER -->
<footer class="d-print-none" id="cv_footer">
    <div class="d-lg-flex d-sm-flex align-items-center p-1 mb-3 d-print-none" style="background-color: #A1C9BD; padding: 0.25% 0">
        <div class="col my-2">
            <div class="p-0">
                <a href="https://www.hoppecke.com/de/kundenportal/meine-kontakte/" target="_blank" class="btn-lt-green btn-sm p-1" name="contact" type="contact" style="width: 100%;">
                    CONTACT
                </a>
            </div>
        </div>
        <div class="col my-2 d-sm-none d-lg-block">
            <div class="p-3 text-center">
            </div>
        </div>
        <div class="col my-2 d-sm-none d-lg-block">
            <div class="p-3 text-center">
            </div>
        </div>
        <div class="col my-2">
            <div class="p-0">
                <button class="btn-lt-green btn-sm p-1" name="clear" id="clear" style="width: 100%;" onclick="clearForm();">
                    CLEAR
                </button>
            </div>
        </div>
        <div class="col my-2">
            <div class="p-0">
                <button class="btn-lt-green btn-sm p-1" name="print" id="print" style="width: 100%;" onclick="print();" disabled>
                    PRINT
                </button>
            </div>
        </div>
    </div>
</footer>

<!-- JS AJAX -->
<script type="text/javascript">
    /** trigger click on fileds by SUBMIT click  */
    window.afterLoadingCompleted = function () {
        <?php if (isset($_SERVER['REQUEST_METHOD'])
            && \strtoupper($_SERVER['REQUEST_METHOD']) === 'POST'
        ) { ?> 
            document.getElementById('tech').dispatchEvent(new CustomEvent('click'));
            document.getElementById('series').dispatchEvent(new CustomEvent('click'));
            document.getElementById('battery').dispatchEvent(new CustomEvent('click'));
        <?php } ?>
    };
    <?php if (isset($requestType) && $requestType === 'ct3') { ?>
        document.querySelector('#pricing_column').remove('pricing_column');
    <?php } ?>
</script>

<!-- timestamp counter for auto logout -->
<script> 
    let countdown; 
    let countdownTimer; 
    function updateTimer(timer) { 
        let hourse = Math.floor(timer / 60 / 60); 
        let minutes = Math.floor(timer / 60 % 60); 
            if (minutes < 10) { 
                minutes = '0'+ minutes; 
            }
        let seconds = timer % 60;
            if (seconds < 10) { 
                seconds = '0'+ seconds; 
            }
        timerDisplay.textContent = hourse + ':' + minutes + ':' + seconds;
        if (hourse < 1) {
            timerDisplay.textContent = minutes + ':' + seconds; 
        }
        if ((Math.floor(timer / 60 % 60)) <= 0 && (timer % 60) <= 0) {
            timerDisplay.textContent = '00:00';
        }
    }
    async function startTimer() { 
        // countdown = <?php echo $decrypted ?>;  // 30 minutes in seconds
        endOfTime = <?php echo $decrypted?>;
        difference = <?php echo $timeRange?>;
        // console.log(countdown, endOfTime, difference);
        countdown = difference;
        updateTimer(countdown); 
        countdownTimer = setInterval(async () => { 
            if (countdown <= 0) { 
                clearInterval(countdownTimer);
                if (window.confirm('You have been logged out automatically. \nPlease open this page again via the customer portal! \n\nIf you click "ok" you would be redirected.')) {
                    window.location.href ='https://www.hoppecke.com/de/kundenportal/dashboard/tools/';
                };
            } 
            countdown--; 
            updateTimer(countdown);
        }, 1000); 
    } 
    const timerDisplay = document.getElementById('timer'); 
    startTimer(); 
</script> 

<!-- tooltip JS for decimal number insert-->
<script type="text/javascript">
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    const appendAlert = (message, type) => {
    const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<td class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <tr>${message}</tr>`,
            '</td>'
    ].join('')}

    var decimalTooltip = document.getElementById("decimalTooltipInsert");
    window.addEventListener('load', function () {
        decimalTooltip.style.display = 'none';
    });
    function showTooltip() {
        decimalTooltip.style.display = ''; //block
    }
    function hideTooltip() {
        decimalTooltip.style.display = 'none';
    }
</script>

<script src="assets/js/jquery.min.js" type="text/javascript" async></script>
<script src="assets/js/ajaxScript.js" async></script>
<script src="assets/js/alternativeCell.js" async></script>

<!-- matomo analytics -->
<script>
    window.addEventListener('load', function () {
        var _paq = window._paq = window._paq || []; 
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */ 
        _paq.push(['trackPageView']); 
        _paq.push(['enableLinkTracking']); 
        (function() { 
            var u="https://stats.hoppecke.com/"; 
            _paq.push(['setTrackerUrl', u+'matomo.php']); 
            _paq.push(['setSiteId', '30']); 
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; 
            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s); 
        })(); 
    });
</script>

</body>
</html>
