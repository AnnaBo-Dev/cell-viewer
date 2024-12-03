<?php
require_once __DIR__ . '../../config/config.php';
$dbconn = openConn();

// Define the response content type to be a json result.
header('Content-Type: application/json');

if (!isset($_GET['typevalue'], $_GET['filledstatus'])) {
    \http_response_code(400);

    exit(\json_encode([
        'success' => false,
        'result' => 'Bad Request on ' . __FILE__,
    ]));
}

$result = null;

try {
    // switch to shange the filles status and material numbers
    switch ($_GET['filledstatus']) {
        case 'filled and uncharged':
            $statement = $dbconn->prepare(
                'SELECT 
                    `battery`.`matno_filled_uncharged` AS `matno`,
                    `battery`.`price_filled` AS `price`,
                    `battery`.`MTZ` AS `mtz`
                FROM `battery`
                JOIN `type` ON `battery`.`id` = `type`.`id`
                WHERE `type`.`type` = :typevalue
                ',
            );
            break;
        case 'unfilled':
            $statement = $dbconn->prepare(
                'SELECT 
                    `battery`.`matno_unfilled` AS `matno`,
                    `mns`.`matno` AS `seperated`,
                    `battery`.`price_unfilled` AS `price`,
                    `battery`.`MTZ` AS `mtz`
                FROM `battery`
                LEFT JOIN `matno_seperated` AS `mns` ON `battery`.`matno_seperated` = `mns`.`id`
                JOIN `type` ON `battery`.`id` = `type`.`id`
                WHERE `type`.`type` = :typevalue
                ',
            );
            break;
        case 'filled and charged':
            $statement = $dbconn->prepare(
                'SELECT 
                    `battery`.`matno_filled_charged` AS `matno`,
                    `battery`.`price_filled` AS `price`,
                    `battery`.`MTZ` AS `mtz`
                FROM `battery`
                JOIN `type` ON `battery`.`id` = `type`.`id`
                WHERE `type`.`type` = :typevalue
                ',
            );
            break;

        default:
            \http_response_code(400);

            exit(\json_encode([
                'success' => false,
                'result' => 'Unknown type: ' . $_GET['filledstatus'] . 'and: ' . $_GET['typevalue'],
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
