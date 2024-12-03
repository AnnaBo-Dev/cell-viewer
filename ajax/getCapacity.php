<?php
require_once __DIR__ . '../../config/config.php';
$dbconn = openConn();

// Define the response content type to be a json result.
header('Content-Type: application/json');

if (!isset($_GET['typevalue'], $_GET['technology'], $_GET['seriesValue'])) {
    \http_response_code(400);

    exit(\json_encode([
        'success' => false,
        'result' => 'Bad Request on ' . __FILE__,
    ]));
}

$result = null;

try {
    $series = $_GET['seriesValue'];
    // switch technology to change the capacity view
    switch ($_GET['technology']) {
        case 'FNC':
            // if FNC is selected
            $statement = $dbconn->prepare(
                'SELECT
                    `battery`.`capacity_C5` AS `nominal`,
                    `battery`.`capacity_C5` AS `real`,
                    null AS `real2`,
                    "Nominal C5 @ 1,00 V/C, 20°C:" AS `nominal_label`,
                    "Real C5 @ 1,00 V/C, 20°C:" AS `real_label`,
                    null AS `real2_label`
                FROM `battery`
                JOIN `type` ON `battery`.`id` = `type`.`id`
                WHERE `type`.`type` = :typevalue
                ;',
            );
            break;
        case 'VLA':
            if (substr($series, 0, 3 ) === "sun") {
                // if VLA and SUN is selected
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`capacity_C100` AS `nominal`,
                        `battery`.`capacity_C100` AS `real`,
                        `battery`.`capacity_C10_real` AS `real2`,
                        "Nominal C100 @ 1,85 V/C, 20°C:" AS `nominal_label`,
                        "Real C100 @ 1,85 V/C, 20°C:" AS `real_label`,
                        "Real C10 @ 1,80 V/C, 20°C:" AS `real2_label`
                    FROM `battery`
                    JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE `type`.`type` = :typevalue
                    ;',
                );
            } else {
                // if VLA is selected
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`capacity_C10_nominal` AS `nominal`,
                        `battery`.`capacity_C10_real` AS `real`,
                        null AS `real2`,
                        "Nominal C10 @ 1,80 V/C, 20°C:" AS `nominal_label`,
                        "Real C10 @ 1,80 V/C, 20°C:" AS `real_label`,
                        null AS `real2_label`
                    FROM `battery`
                    JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE `type`.`type` = :typevalue
                    ;',
                );
            }            
            break;
        case 'VRLA':
            if (substr($series, 0, 3 ) === "sun") {
                // if VRLA and SUN is selected
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`capacity_C100` AS `nominal`,
                        `battery`.`capacity_C100` AS `real`,
                        `battery`.`capacity_C10_real` AS `real2`,
                        "Nominal C100 @ 1,85 V/C, 20°C:" AS `nominal_label`,
                        "Real C100 @ 1,85 V/C, 20°C:" AS `real_label`,
                        "Real C10 @ 1,80 V/C, 20°C:" AS `real2_label`
                    FROM `battery`
                    JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE `type`.`type` = :typevalue
                    ;',
                );
            } else if (substr($series, 0, 3 ) === "power.com H.C") {
                // if VRLA and POWER.COM H.C is selected
                $statement = $dbconn->prepare(
                    'SELECT
                        null AS `nominal`,
                        `battery`.`capacity_C10_real` AS `real`,
                        `battery`.`capacity_C100` AS `real2`,
                        null AS `nominal_label`,
                        "Real C10 @ 1,80 V/C, 25°C:" AS `real_label`,
                        "Real C20 @ 1,75 V/C, 25°C:" AS `real2_label`
                    FROM `battery`
                    JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE `type`.`type` = :typevalue
                    ;',
                );
            } else {
                // if VRLA is selected
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`capacity_C10_nominal` AS `nominal`,
                        `battery`.`capacity_C10_real` AS `real`,
                        null AS `real2`,
                        "Nominal C10 @ 1,80 V/C, 20°C:" AS `nominal_label`,
                        "Real C10 @ 1,80 V/C, 20°C:" AS `real_label`,
                        null AS `real2_label`
                    FROM `battery`
                    JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE `type`.`type` = :typevalue
                    ;',
                );
            } 
            break;

        default:
            \http_response_code(400);

            exit(\json_encode([
                'success' => false,
                'result' => 
                    'Unknown type: ' . $_GET['typevalue']
                    . 'and: ' . $_GET['technology']
                    . 'and: ' . $_GET['seriesValue'],
            ]));
    }

    $statement->execute([
        'typevalue' => $_GET['typevalue'],
    ]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    \http_response_code(500);
    exit(\json_encode([
        'success' => false,
        'result' => $e->getMessage(),
    ]));
}

// if request isn't success
if (!isset($result)) {
    \http_response_code(404);
    exit(\json_encode([
        'success' => false,
        'result' => 'Not Found',
    ]));
}

// if request is success here is the result
echo \json_encode([
    'success' => true,
    'result' => $result,
]);
