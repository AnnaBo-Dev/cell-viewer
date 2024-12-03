<?php

session_start();
// Set the inactivity time of 15 minutes (900 seconds)
$inactivity_time = 1 * 60 * 60; # h * min * sec

// Check if the last_timestamp is set
// and last_timestamp is greater then 15 minutes or 9000 seconds
// then unset $_SESSION variable & destroy session data
if (isset($_SESSION['last_timestamp']) 
    && (time() - $_SESSION['last_timestamp']) > $inactivity_time
) {
    session_unset();
    session_destroy();

    //Redirect user to login page
    \http_response_code(504);
    require_once __DIR__ . '/error.php';
    exit;
} else {
    // Regenerate new session id and delete old one to prevent session fixation attack
    session_regenerate_id(true);

    // Update the last timestamp
    $_SESSION['last_timestamp'] = time();
}

//change unit standard by format type button value
if (!isset($_SESSION['format_type'])) {
    $_SESSION['format_type'] = 'EU';
}
if (isset($_REQUEST['format_type']) && \in_array($_REQUEST['format_type'], [
    'EU',
    'US',
], true)) {
    $_SESSION['format_type'] = $_REQUEST['format_type'];
}

require_once __DIR__ . '/config/requestCheck.php';
require_once __DIR__ . '/config/config.php';
$dbconn = openConn();

/**
 * @return [] $result from DB for select options
 */
function getSelectValues($dbconn, $select) { 
    $results = [];

    try {
        $execute = [];

        switch ($select) {
            case 'technology':
                $statement = $dbconn->prepare(
                    'SELECT DISTINCT `technology` 
                    FROM `type` ORDER BY `technology` ASC;'
                    );
                break;
            case 'series':
                $statement = $dbconn->prepare(
                    'SELECT DISTINCT `technology`, `series` 
                    FROM `type` WHERE `aktiv` = 1
                    ORDER BY `series` ASC;'
                    );
                break;
            case 'type':
                $statement = $dbconn->prepare(
                    'SELECT DISTINCT `series`, `type`
                    FROM `type`;'
                    );
                break;
        }

        if (!isset($statement)) {
            return [];
        }

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results[] = $result;
        }

        return $results;
    } catch (PDOException $e) {
        echo $statement . '<br>' . $e->getMessage();
        return [];
    }
}

/**
 * @return $mapped for select title
 */
function groupByField(array $items, string $field): array
{
    $mapped = [];

    foreach ($items as $item) {
        if (!isset($mapped[$item[$field]])) {
            $mapped[$item[$field]] = [];
        }

        $mapped[$item[$field]][] = $item;
    }

    return $mapped;
}

/**
 * @return [] $result dimensions from DB for select options
 */
function getCapacityByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT 
                `battery`.`capacity_C5` AS `capacity_C5`,
                `battery`.`capacity_C8` AS `capacity_C8`,
                `battery`.`capacity_C100` AS `capacity_C100`,
                `battery`.`capacity_C10_nominal` AS `capacity_C10`,
                `battery`.`capacity_C10_real` AS `capacity_C10_real`
            FROM `battery`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result dimensions from DB for select options
 */
function getDimensionsByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT 
                `battery`.`length` AS `length`,
                `battery`.`width` AS `width`,
                `battery`.`height1` AS `height1`,
                `battery`.`height2` AS `height2`
            FROM
                `type`
                JOIN `battery` ON `battery`.`id` = `type`.`id`
            WHERE
                `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        echo $statement . '<br>' . $e->getMessage(); 
        return [];
    }
}


/**
 * @return [] $result weight from DB for select options
 */
function getWeightsByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `battery`.`total_weight` AS `total`,
                `battery`.`electrolyte_weight` AS `of_electrolyte`
            FROM
                `type`
                JOIN `battery` ON `battery`.`id` = `type`.`id`
            WHERE
                `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result weight from DB for select options
 */
function getElectrolyteByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
            `battery`.`density_weight` AS `density`,
            `e`.`e_name` AS `electrolyte_type`
            FROM
                `battery`
            LEFT JOIN `electrolyte` AS `e` ON `battery`.`electrolyte` = `e`.`id`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

// /**
//  * @return [] $result charging from DB for select options
//  */
function getMatnoByBatteryId($dbconn, $id) {
    $results = [];

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT 
                `battery`.`matno_filled_uncharged` AS `filled_uncharged`,
                `battery`.`matno_unfilled` AS `unfilled`,
                `mns`.`matno` AS `seperated`,
                `battery`.`matno_filled_charged` AS `filled_charged`
            FROM `battery`
            LEFT JOIN `matno_seperated` AS `mns` ON `battery`.`matno_seperated` = `mns`.`id`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;
    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getNormByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `vw_norm`.`norm1` AS `norm1`,
                `vw_norm`.`norm2` AS `norm2`
            FROM
                `vw_norm`
            LEFT JOIN `battery` ON `vw_norm`.`id` = `battery`.`norm`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE
                `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result technical data from DB for select options
 */
function getTechnicalDataByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `vw_technical`.`internal_resistance` AS `internal_resistance`,
                `vw_technical`.`short_circuit_current` AS `short_circuit_current`,
                `vw_technical`.`self_discharge` AS `self_discharge`,
                `vw_technical`.`operating_temperature` AS `operating_temperature`,
                `vw_technical`.`design_life` AS `design_life`,
                `vw_technical`.`cycles` AS `cycles`,
                `vw_technical`.`pole_pairs` AS `pole_pairs`,
                `vw_technical`.`cell_openings` AS `cell_openings`
            FROM `type`
            JOIN `vw_technical` ON `vw_technical`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getChargeByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `charge`.`floatC` AS `float_voltage`,
                `charge`.`boost` AS `boost_voltage`,
                `charge`.`floatC_current` AS `float_current`
            FROM
                `charge`
            LEFT JOIN `battery` ON `charge`.`id` = `battery`.`charge`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getAquagenByBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `a`.`ag_name` AS `ag_name`,
                `a`.`ag_no` AS `ag_no`
            FROM `aquagen` as `a`
            LEFT JOIN `battery` ON `a`.`id` = `battery`.`aquagen`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getPackingBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT
                `packing`.`packing_no` AS `packing_no`,
                `packing`.`internal_h` AS `netto`,
                `packing`.`external_h` AS `brutto`,
                `packing`.`weight` AS `packing_weight`,
                `battery`.`layer_pallet` AS `layer`,
                `battery`.`layer_seaworthy` AS `layer_seaworthy`,
                `battery`.`height2` AS `height`,
                `battery`.`safety_distance_height` AS `safety_distance`,
                `battery`.`cell_per_box` AS `cell_per_layer`
            FROM `battery`
            JOIN `packing`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id 
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getPriceBatteryId($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT 
                `battery`.`price_filled` AS `filled`,
                `battery`.`price_unfilled` AS `unfilled`,
                `battery`.`MTZ` AS `mtz`
            FROM `battery`
            JOIN `type` ON `battery`.`id` = `type`.`id`
            WHERE `type`.`id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getDBinsertVersion($dbconn) {
    $results = '';

    try {

        $statement = $dbconn->prepare(
            'SELECT `version`
            FROM `cv_data_versions`
            ORDER BY `id` 
            DESC LIMIT 1
            ',
        );

        $statement->execute();
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] $result charging from DB for select options
 */
function getAlternativeCells($dbconn, $id) {
    $results = '';

    try {
        $execute = ['id' => $id];

        $statement = $dbconn->prepare(
            'SELECT `alternative`
                FROM `type`
                WHERE `id` = :id
            ',
        );

        $statement->execute($execute);
        while (($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $statement . '<br>' . $e->getMessage(); // Wartungs Page einbauen 
        return [];
    }
}

/**
 * @return [] an array as $result data battery with calculation from DB for select options
 */
function getBattery(
    $dbconn,
    $typevalue,
    $quantity,
    $requestType
    ) 
{
    $results = '';

    try {
        $execute = ['typevalue' => $typevalue];
        
        $statement = $dbconn->prepare(
            'SELECT `battery`.`id` AS `id`
            FROM `battery` 
            JOIN `type` ON `battery`.`id` = `type`.`id` 
            WHERE `type`.`type` = :typevalue
            ',
        );

        // fille an battery array with the other function results
        $statement->execute($execute);
        while(($result = $statement->fetch(PDO::FETCH_ASSOC))) {
            $result['capacity'] = getCapacityByBatteryId($dbconn, $result['id']);
            $result['dimensions'] = getDimensionsByBatteryId($dbconn, $result['id']);
            $result['weights'] =  getWeightsByBatteryId($dbconn, $result['id']);
            $result['without_electrolyte'] = $result['weights']['total'] - $result['weights']['of_electrolyte'];
            $result['electrolyte'] = getElectrolyteByBatteryId($dbconn, $result['id']);
            $result['volume'] = ($result['weights']['of_electrolyte'] ?? \intval(0)) / ($result['electrolyte']['density'] ?? \intval(1));
            $result['matno'] = getMatnoByBatteryId($dbconn, $result['id']);
            $result['norms'] = getNormByBatteryId($dbconn, $result['id']);
            $result['technical_data'] = getTechnicalDataByBatteryId($dbconn, $result['id']);
            $result['charging'] = getChargeByBatteryId($dbconn, $result['id']);
            $result['data_battery'] = [
                'total_weight' => $result['weights']['total'] * $quantity,
                'total_without_electrolyte' => $result['without_electrolyte'] * $quantity,
                'total_of_electrolyte' => $result['weights']['of_electrolyte'] * $quantity,
                'total_volume' => $result['volume'] * $quantity,
                'count_drums_30l' => ($result['volume'] * $quantity) / \intval(30),
                'total_pole_pairs' => $result['technical_data']['pole_pairs'] * $quantity,
                'total_cell_openings' => $result['technical_data']['cell_openings'] * $quantity,
            ];
            $result['aquagen'] = getAquagenByBatteryId($dbconn, $result['id']);
            if ($requestType === 'Hp2') {
                $result['pricing'] = getPriceBatteryId($dbconn, $result['id']);
                $result['calculated_pricing'] = [
                    'calculated_without_MTZ' => $result['pricing']['filled'] * $quantity,
                    'calculated_MTZ' => $result['pricing']['mtz'] * $quantity,
                    'calculated_with_MTZ' => ($result['pricing']['filled'] + $result['pricing']['mtz']) * $quantity
                ];
            }
            $result['packing'] = getPackingBatteryId($dbconn, $result['id']);
            $result['packing_calculations'] = [
                'paletts' => ($quantity / $result['packing']['cell_per_layer']) / ($result['packing']['layer'] !== NULL ?: 1),
                'fullyload_package' => $result['packing']['cell_per_layer'] * $result['without_electrolyte'] + 22,
                'total_weight_package' => (round($quantity / $result['packing']['cell_per_layer']) * 22) + $result['data_battery']['total_without_electrolyte'],
                'no_of_paletts_electrolyte' => round($result['data_battery']['count_drums_30l'] / \intval(16)) == 0 ? 1 : round($result['data_battery']['count_drums_30l'] / \intval(16)),
                'total_weight_electrolyte' => (round($result['data_battery']['count_drums_30l']) * \intval(30) * floatval($result['electrolyte']['density']))
                    + ((round($result['data_battery']['count_drums_30l'] / \intval(16)) == 0 ? 1 : round($result['data_battery']['count_drums_30l'] / \intval(16))) * \intval(20)),
            ];
            $result['db_insert_version'] = getDBinsertVersion($dbconn);
            $result['alternativeCell'] = getAlternativeCells($dbconn, $result['id']);

            $results = $result;
        }  
       
        return $results;

    } catch (PDOException $e) {
        echo $statement . '<br>' . $e->getMessage();
        return [];
    }
}