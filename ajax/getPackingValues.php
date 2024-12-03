<?php
require_once __DIR__ . '../../config/config.php';
$dbconn = openConn();

// Define the response content type to be a json result.
header('Content-Type: application/json');

if (!isset($_GET['typevalue'], $_GET['packing'], $_GET['technology'], $_GET['seriesValue'])) {
    \http_response_code(400);

    exit(\json_encode([
        'success' => false,
        'result' => 'Bad Request on ' . __FILE__,
    ]));
}

$result = null;

try {
    $tech = $_GET['technology'];
    $series = $_GET['seriesValue'];
    // switch for packaging types
    switch ($_GET['packing']) {
        case 'Palett':
            $statement = $dbconn->prepare(
                'SELECT
                    `battery`.`layer_pallet` AS `layer`,
                    `battery`.`height2` AS `height`,
                    `battery`.`cell_per_box` AS `cell_per_layer`
                FROM `battery`
                JOIN `type` ON `battery`.`id` = `type`.`id`
                WHERE `type`.`type` = :typevalue
                ',
            );
            break;
        case 'Cardboard (Carton)':
            // if TECHNOLOGY = VRLA
            if ($tech === 'VRLA') {
                // if SERIES = grid | Xtreme
                if ($series === 'grid | Xtreme') {
                    $statement = $dbconn->prepare(
                        'SELECT
                            `battery`.`layer_seaworthy` AS `layer`,
                            `battery`.`cell_per_box` AS `cell_per_layer`,
                            `packing`.`packing_no` AS `packing_no`,
                            `packing`.`internal_h` AS `netto`,
                            `packing`.`external_h` AS `brutto`,
                            `packing`.`weight` AS `packing_weight`
                        FROM `battery`
                        JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                        WHERE
                            `type`.`type` = :typevalue
                            AND `packing`.`id` = 2
                        ',
                    );
                // all other SERIES
                } else {
                    $statement = $dbconn->prepare(
                        'SELECT
                            `battery`.`layer_seaworthy` AS `layer`,
                            `battery`.`cell_per_box` AS `cell_per_layer`,
                            `packing`.`packing_no` AS `packing_no`,
                            `packing`.`internal_h` AS `netto`,
                            `packing`.`external_h` AS `brutto`,
                            `packing`.`weight` AS `packing_weight`
                        FROM `battery`
                        JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                        WHERE
                            `type`.`type` = :typevalue 
                            AND `packing`.`id` <= 5
                            AND NOT `packing`.`id` = 2
                            AND (`battery`.`layer_seaworthy` * `battery`.`height2`)
                                + `battery`.`safety_distance_height` < `packing`.`internal_h`
                        ',
                    );
                }
            // if TECHNOLOGY = FNC or VLA
            } else {
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`layer_pallet` AS `layer`,
                        `battery`.`cell_per_box` AS `cell_per_layer`,
                        `packing`.`packing_no` AS `packing_no`,
                        `packing`.`internal_h` AS `netto`,
                        `packing`.`external_h` AS `brutto`,
                        `packing`.`weight` AS `packing_weight`
                    FROM `battery`
                    JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE
                        `type`.`type` = :typevalue 
                        AND `packing`.`id` <= 5
                        AND NOT `packing`.`id` = 2
                        AND (`battery`.`layer_pallet` * `battery`.`height2`)
                            + `battery`.`safety_distance_height` < `packing`.`internal_h`
                    ',
                );
            }
            break;
        case 'Chipboard (Wood)':
            // if TECHNOLOGY = VRLA
            if ($tech === 'VRLA') {
                // if SERIES = grid | Xtreme
                if ($series === 'grid | Xtreme') {
                    $statement = $dbconn->prepare(
                        'SELECT
                            `battery`.`layer_seaworthy` AS `layer`,
                            `battery`.`cell_per_box` AS `cell_per_layer`,
                            `packing`.`packing_no` AS `packing_no`,
                            `packing`.`internal_h` AS `netto`,
                            `packing`.`external_h` AS `brutto`,
                            `packing`.`weight` AS `packing_weight`
                        FROM `battery`
                        JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                        WHERE
                            `type`.`type` = :typevalue
                            AND `packing`.`id` = 7
                        ',
                    );
                // all other SERIES
                } else {
                    $statement = $dbconn->prepare(
                        'SELECT
                            `battery`.`layer_seaworthy` AS `layer`,
                            `battery`.`cell_per_box` AS `cell_per_layer`,
                            `packing`.`packing_no` AS `packing_no`,
                            `packing`.`internal_h` AS `netto`,
                            `packing`.`external_h` AS `brutto`,
                            `packing`.`weight` AS `packing_weight`
                        FROM `battery`
                        JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                        WHERE
                            `type`.`type` = :typevalue 
                            AND `packing`.`id` >= 6 
                            AND NOT `packing`.`id` = 7 
                            AND (`battery`.`layer_seaworthy` * `battery`.`height2`) 
                                + `battery`.`safety_distance_height` < `packing`.`internal_h`
                        ',
                    );
                }
            // if TECHNOLOGY = FNC or VLA
            } else {
                $statement = $dbconn->prepare(
                    'SELECT
                        `battery`.`layer_pallet` AS `layer`,
                        `battery`.`cell_per_box` AS `cell_per_layer`,
                        `packing`.`packing_no` AS `packing_no`,
                        `packing`.`internal_h` AS `netto`,
                        `packing`.`external_h` AS `brutto`,
                        `packing`.`weight` AS `packing_weight`
                    FROM `battery`
                    JOIN `packing` JOIN `type` ON `battery`.`id` = `type`.`id`
                    WHERE
                        `type`.`type` = :typevalue 
                        AND `packing`.`id` >= 6 
                        AND NOT `packing`.`id` = 7 
                        AND (`battery`.`layer_pallet` * `battery`.`height2`) 
                            + `battery`.`safety_distance_height` < `packing`.`internal_h`
                    ',
                );
            }
            break;

        default:
            \http_response_code(400);

            exit(\json_encode([
                'success' => false,
                'result' =>
                'Unknown type: ' . $_GET['packing']
                    . 'and: ' . $_GET['typevalue']
                    . 'and: ' . $_GET['technology']
                    . 'and: ' . $_GET['series'],
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