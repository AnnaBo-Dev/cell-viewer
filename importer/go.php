<?php
set_time_limit(500);
// Define the response content type to be a json result.
// header('Content-Type: application/json; charset=UTF-8');

// DB connection
require_once __DIR__ . '/config/configImport.php';
$dbconn = openConn();

// create temporare file path for uploadinbg file 
$csvFile = $_FILES['file']['tmp_name'];
$csv = file($csvFile);
// $csv = file('./CV_battery_basic_data_all.csv');

// reformat the CSV data into an array of rows 
$rows = explode(PHP_EOL, implode($csv));
// count number of rows
function countRowsOfCSV($rows): ?int
{
    return count($rows);
}

// craete an array for the first line from array and remove all nonASCII values
$headline = array_map(function (string $column) {
    return trim($column);
}, explode(';', (preg_replace('/[^\x20-\x7E]/', '', $rows[0]))));

$data = csvToData($rows);
$connectorIds = [];

try {
    // delete all data from battery (main table)
    $deleteStmt = 'DELETE FROM `battery`';
    $stmt = $dbconn->prepare($deleteStmt);
    $stmt->execute();
    // reset table ID
    $resetIdStmt = 'ALTER TABLE `battery` AUTO_INCREMENT = 1';
    $stmt = $dbconn->prepare($resetIdStmt);
    $stmt->execute();

    // remove foreign key check
    $keyCheck = 'SET FOREIGN_KEY_CHECKS = 0';
    $stmt = $dbconn->prepare($keyCheck);
    $stmt->execute();
    // delete all data from type (2nd main table)
    $deleteStmt = 'DELETE FROM `type`';
    $stmt = $dbconn->prepare($deleteStmt);
    $stmt->execute();
    // reset table ID
    $resetIdStmt = 'ALTER TABLE `type` AUTO_INCREMENT = 1';
    $stmt = $dbconn->prepare($resetIdStmt);
    $stmt->execute();
    // add foreign key check 
    $keyCheck = 'SET FOREIGN_KEY_CHECKS = 1';
    $stmt = $dbconn->prepare($keyCheck);
    $stmt->execute();

    // go thouch the multidimentional data array
    foreach ($data as $index => $value) {
        # connectors
        $connector = md5($value['connectors']);
        // check if already exists in database
        $connectorIds[$connector] = valueExists($dbconn, 'connectors', [
            'c_name' => $value['connectors'],
        ]);
        // insert values in subtable if not existing
        if (!isset($connectorIds[$connector])) {
            $columnsToFill = [
                'c_name' => $value['connectors'],
            ];
            // if a value in CSV is string null, write the value null in array
            if (isset($value['connectors']) && isNull($value['connectors'])) {
                $columnsToFill['c_name'] = null;
            }

            $insertStmt = buildInsertQuery('connectors', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $connectorIds[$connector] = $dbconn->lastInsertId(); # pdo id
        }

        # electrolyte
        $electrolyte = md5($value['electrolyte']);
        // check if already exists in database
        $electrolyteIds[$electrolyte] = valueExists($dbconn, 'electrolyte', [
            'e_name' => $value['electrolyte'],
        ]);
        // insert values in subtable if not existing
        if (!isset($electrolyteIds[$electrolyte])) {
            $columnsToFill = [
                'e_name' => $value['electrolyte'],
            ];
            // if a value in CSV is string null, write the value null in array
            if (isset($value['electrolyte']) && isNull($value['electrolyte'])) {
                $columnsToFill['e_name'] = null;
            }

            $insertStmt = buildInsertQuery('electrolyte', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $electrolyteIds[$electrolyte] = $dbconn->lastInsertId(); # pdo id
        }

        # aquagen, aquagen_no
        $aquagen = md5($value['aquagen']. $value['aquagen_no']);
        // check if already exists in database
        $aquagenIds[$aquagen] = valueExists($dbconn, 'aquagen', [
            'ag_name' => $value['aquagen'],
            'ag_no' => $value['aquagen_no'],
        ]);
        // insert values in subtable if not existing
        if (!isset($aquagenIds[$aquagen])) {
            $columnsToFill = [
                'ag_name' => $value['aquagen'],
                'ag_no' => $value['aquagen_no'],
            ];
            // if a value in CSV is string null, write the value null in array
            foreach ($columnsToFill as $key => $val) {
                if (isset($val) && isNull($val)) {
                    $columnsToFill[$key] = null;
                }
            }

            $insertStmt = buildInsertQuery('aquagen', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $aquagenIds[$aquagen] = $dbconn->lastInsertId(); # pdo id
        }

        # matno_seperated
        $matnoSeperated = md5($value['matno_seperated']);
        // check if already exists in database
        $matnoSeperatedIds[$matnoSeperated] = valueExists($dbconn, 'matno_seperated', [
            'matno' => $value['matno_seperated'],
        ]);
        // insert values in subtable if not existing
        if (!isset($matnoSeperatedIds[$matnoSeperated])) {
            $columnsToFill = [
                'matno' => $value['matno_seperated'],
            ];
            // if a value in CSV is string null, write the value null in array
            if (isset($value['matno_seperated']) && isNull($value['matno_seperated'])) {
                $columnsToFill['matno'] = null;
            }

            $insertStmt = buildInsertQuery('matno_seperated', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $matnoSeperatedIds[$matnoSeperated] = $dbconn->lastInsertId(); # pdo id
        }

        # charge > floatC, boost, floatC_current, self_discharge, operating_temperature
        $charge = md5($value['floatC']. $value['boost']. $value['floatC_current']. $value['self_discharge']. $value['operating_temperature']);
        // check if already exists in database
        $chargeIds[$charge] = valueExists($dbconn, 'charge', [
            'floatC' => $value['floatC'],
            'boost' => $value['boost'],
            'floatC_current' => $value['floatC_current'],
            'self_discharge' => $value['self_discharge'],
            'operating_temperature' => $value['operating_temperature'],
        ]);
        // insert values in subtable if not existing
        if (!isset($chargeIds[$charge])) {
            $columnsToFill = [
                'floatC' => $value['floatC'],
                'boost' => $value['boost'],
                'floatC_current' => $value['floatC_current'],
                'self_discharge' => $value['self_discharge'],
                'operating_temperature' => $value['operating_temperature'],
            ];
            // if a value in CSV is string null, write the value null in array
            foreach ($columnsToFill as $key => $val) {
                if (isset($val) && isNull($val)) {
                    $columnsToFill[$key] = null;
                }
            }

            $insertStmt = buildInsertQuery('charge', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $chargeIds[$charge] = $dbconn->lastInsertId(); # pdo id
        }

        # norms > norm1
        $norm1 = md5($value['norm1']);
        // check if already exists in database
        $norm1Ids[$norm1] = valueExists($dbconn, 'norms', [
            'norm' => $value['norm1']
        ]);
        // insert values in subtable if not existing
        if (!isset($norm1Ids[$norm1])) {
            $columnsToFill = [
                'norm' => $value['norm1'],
            ];
            // if a value in CSV is string null, write the value null in array
            if (isset($value['norm1']) && isNull($value['norm1'])) {
                $columnsToFill['norm'] = null;
            }

            $insertStmt = buildInsertQuery('norms', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $norm1Ids[$norm1]  = $dbconn->lastInsertId(); # pdo id
        }
        # norms > norm2
        $norm2 = md5($value['norm2']);
        // check if already exists in database
        $norm2Ids[$norm2] = valueExists($dbconn, 'norms', [
            'norm' => $value['norm2']
        ]);
        // insert values in subtable if not existing
        if (!isset($norm2Ids[$norm2])) {
            $columnsToFill = [
                'norm' => $value['norm2'],
            ];
            // if a value in CSV is string null, write the value null in array
            if (isset($value['norm2']) && isNull($value['norm2'])) {
                $columnsToFill['norm'] = null;
            }

            $insertStmt = buildInsertQuery('norms', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $norm2Ids[$norm2]  = $dbconn->lastInsertId(); # pdo id
        }
        # bat_norms > norms (norm1, norm2)
        $norms = md5($value['norm1']. $value['norm2']);
        // check if already exists in database
        $batNormIds[$norms] = valueExists($dbconn, 'bat_norms', [
            'norm1' => $norm1Ids[$norm1],
            'norm2' => $norm2Ids[$norm2],
        ]);
        // insert values in subtable if not existing
        if (!isset($batNormIds[$norms])) {
            $stmt = $dbconn->prepare("INSERT INTO `bat_norms` (`norm1`, `norm2`) VALUES (:newValue1, :newValue2)");
            $stmt->execute([
                'newValue1' => $norm1Ids[$norm1],
                'newValue2' => $norm2Ids[$norm2],
            ]);
            $batNormIds[$norms] = $dbconn->lastInsertId(); # pdo id
        }

        # type > technology, series, type, part_no, aktiv
        $type = md5($value['id']. $value['technology']. $value['series']. $value['type']. $value['part_no']. $value['aktiv']);
        // check if already exists in database
        $typeIds[$type] = valueExists($dbconn, 'type', [
            'id' => $value['id'],
            'technology' => $value['technology'],
            'series' => $value['series'],
            'type' => $value['type'],
            'partno' => $value['part_no'],
            'aktiv' => filter_var($value['aktiv'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'alternative' => $value['alternative'],
        ]);
        
        // insert values in subtable, table is default empty
        if (!isset($typeIds[$type])) {
            $columnsToFill = [
                'id' => $value['id'],
                'technology' => $value['technology'],
                'series' => $value['series'],
                'type' => $value['type'],
                'partno' => $value['part_no'],
                'aktiv' => filter_var($value['aktiv'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'alternative' => $value['alternative'],
            ];
            // if a value in CSV is string null, write the value null in array
            foreach ($columnsToFill as $key => $val) {
                if (isset($val) && isNull($val)) {
                    $columnsToFill[$key] = null;
                }
            }

            $insertStmt = buildInsertQuery('type', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $typeIds[$type] = $dbconn->lastInsertId(); # pdo id
        }

        # battery >
            # matno_filled_charged, matno_filled_uncharged, price_filled ,
            # matno_unfilled, price_unfilled, MTZ, pole_pairs, cell_openings,
            # internal_resistance, short_circuit_current, design_life, cycles,
            # length, width, height1, height2,
            # total_weight, electrolyte_weight, density_weight,
            # capacity_C5, capacity_C8, capacity_C100, capacity_C10_real, capacity_C10_nominal,
            # cell_per_box, layer_seaworthy, layer_pallet, packing_height, safety_distance_height
            # matno_seperated | FK, norm | FK, charge | FK, connectors | FK, electrolyte | FK, aquagen | FK
        $battery = md5($typeIds[$type]. $value['matno_filled_charged']);
        // check if already exists in database
        $batteryIds[$battery] = valueExists($dbconn, 'battery', [
            'id' => $typeIds[$type],
            'matno_filled_charged' => $value['matno_filled_charged'],
        ]);
        $columnsToFill = [
            // db => csv
            'id' => $typeIds[$type],
            'matno_filled_charged' => $value['matno_filled_charged'],
            'matno_filled_uncharged' => $value['matno_filled_uncharged'],
            'price_filled' => $value['price_filled'],
            'matno_unfilled' => $value['matno_unfilled'],
            'price_unfilled' => $value['price_unfilled'],
            'MTZ' => $value['MTZ'],
            'pole_pairs' => $value['pole_pairs'],
            'cell_openings' => $value['cell_openings'],
            'internal_resistance' => $value['internal_resistance'],
            'short_circuit_current' => $value['short_circuit_current'],
            'design_life' => $value['design_life'],
            'cycles' => $value['cycles'],
            'length' => $value['length'],
            'width' => $value['width'],
            'height1' => $value['height1'],
            'height2' => $value['height2'],
            'total_weight' => $value['total_weight'],
            'electrolyte_weight' => $value['electrolyte_weight'],
            'density_weight' => $value['density_weight'],
            'capacity_C5' => $value['capacity_C5'],
            'capacity_C8' => $value['capacity_C8'],
            'capacity_C100' => $value['capacity_C100'],
            'capacity_C10_real' => $value['capacity_C10_real'],
            'capacity_C10_nominal' => $value['capacity_C10_nominal'],
            'cell_per_box' => $value['cell_per_box'],
            'layer_seaworthy' => $value['layer_seaworthy'],
            'layer_pallet' => $value['layer_pallet'],
            'packing_height' => $value['packing_height'],
            'safety_distance_height' => $value['safety_distance_height'],
            'matno_seperated' => $matnoSeperatedIds[$matnoSeperated],
            'norm' => $batNormIds[$norms],
            'charge' => $chargeIds[$charge],
            'connectors' => $connectorIds[$connector],
            'electrolyte' => $electrolyteIds[$electrolyte],
            'aquagen' => $aquagenIds[$aquagen],
        ];

        // if a value in CSV is string null, write the value null in array
        foreach ($columnsToFill as $key => $val) {
            if (isset($val) && isNull($val)) {
                $columnsToFill[$key] = null;
            }
        }
        // insert values in subtable, table is default empty
        if (!isset($batteryIds[$battery])) {
            $insertStmt = buildInsertQuery('battery', $columnsToFill);
            $stmt = $dbconn->prepare($insertStmt);
            $stmt->execute($columnsToFill);

            $batteryIds[$battery] = $dbconn->lastInsertId(); # pdo id
        }

        // array of simple subtables
        $tablesToCheck = [
            'matno_seperated' => 'matno_seperated',
            'charge' => 'charge',
            'connectors' => 'connectors',
            'electrolyte' => 'electrolyte',
            'aquagen' => 'aquagen',
        ];
        foreach ($tablesToCheck as $table => $column) {
            if (selectNotUsedFromSubtable($table, 'battery', $column, $dbconn) === true) {
                // delete not used data in main tables from subtable
                $deleteStmt = deleteNotUsedFromSubtable($table, 'battery', $column);
                $stmt = $dbconn->prepare($deleteStmt);
                $stmt->execute([$table]);
            }
        }
        if (selectNotUsedFromSubtable('bat_norms', 'battery', 'norm', $dbconn) === true) {
            // delete not used data in main tables from further inherited subtable
            $deleteStmt = deleteNotUsedFromSubtable('bat_norms', 'battery', 'norm');
            $stmt = $dbconn->prepare($deleteStmt);
            $stmt->execute();
        }

    }
    // set value succes of true
    return $success = true;

} catch (PDOException $e) {
    $success = false;
    $errorMessage = 'The importer has run into an error: ' .$e->getMessage() . '<br>' .
        'Please contact your admin for more information!';
    // set value succes of false
    return $success;
}



/** @return string mySQL to insert values */
function buildInsertQuery(string $table, array $data): string
{
    $columnNames = \array_keys($data);
    $placeholders = \array_map(static function (string $name) { return ':' . $name; }, $columnNames);
    $columnNames = \array_map(static function (string $name) { return '`' . $name . '`'; }, $columnNames);

    return 'INSERT INTO `' . $table . '` (' . \implode(', ', $columnNames) . ') VALUES (' . \implode(', ', $placeholders) . ')';
}

/** @return string mySQL to update values in table with spezific id */
function buildUpdateQuery(string $table, array $data, string $idColumn): string
{
    $id = $data[$idColumn];
    $setStatements = [];
    $placeholders = [];
    
    unset($data[$idColumn]);
    foreach ($data as $column => $value) {
        $setStatements[] = '`' . $column . '` = :' . $column;
        $placeholders[':' . $column] = $value;
    }

    $placeholders[':id'] = $id;
    return 'UPDATE `' . $table . '` SET ' . \implode(', ', $setStatements) . ' WHERE ' . $idColumn .' = :id';
}

/** @return string mySQL to update values in table */
function buildUpdateQueryType(string $table, array $data): string
{
    $setStatements = [];
    $placeholders = [];
    
    foreach ($data as $column => $value) {
        $setStatements[] = '`' . $column . '` = :' . $column;
        $placeholders[':' . $column] = $value;
    }

    return 'UPDATE `' . $table . '` SET ' . \implode(', ', $setStatements);
}

/** @return string mySQL to delete data from subtable, which not used in @param maintable */
function deleteNotUsedFromSubtable(string $tableToCheck, string $maintable, string $columnName): string
{
    return 'DELETE FROM `' . $tableToCheck . '` WHERE NOT EXISTS (select * from `' . $maintable . '` where `' . $maintable . '`.`' .  $columnName . '` = `' .  $tableToCheck . '`.`id`)';
}

function selectNotUsedFromSubtable(string $tableToCheck, string $maintable, string $columnName, $dbconn): bool
{
    $selectNotUsed = 'SELECT * FROM `' . $tableToCheck . '` WHERE NOT EXISTS (SELECT * FROM `' . $maintable . '` WHERE `' . $maintable . '`.`' .  $columnName . '` = `' .  $tableToCheck . '`.`id`)';
    $stmt = $dbconn->prepare($selectNotUsed);
    $stmt->execute();
    if ($stmt === null) {
        return true;
    }
    return false;
}

/** @return int partno from battery type */
function getPartNoFromDB($dbconn, $dataIndex): array
{
    try {
        $stmt = $dbconn->prepare("SELECT `id`, `partno` FROM `type` WHERE `id` = :dataIndex");
        $stmt->execute(['dataIndex' => $dataIndex]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;

    } catch (PDOException $e) {
        echo $stmt . '<br>' . $e->getMessage();
        return [];
    }

    //if request isn't success
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
}

/** @return bool if value is null or not */
function isNull($value): bool
{
    return !isset($value) || (\is_string($value) && \in_array(\strtolower($value), ['', 'null', 'NULL'], true));
}

/** check if value allready exists and @return value if it's not null */
function valueExists($dbconn, $tblname, array $where): ?int
{
    try {
        $whereSql = \implode(' AND ', \array_map(static function (string $column) use ($where) {
            if (isNull($where[$column])) {
                return '`' . $column . '` IS NULL';
            }
            return '`' . $column . '` = :' . $column;
        }, \array_keys($where)));

        $stmt = $dbconn->prepare("SELECT id FROM $tblname WHERE $whereSql");
        $stmt->execute(\array_filter($where, static function ($value) {
            return !isNull($value);
        }));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($result['id'])) {
            return \intval($result['id']);
        }
        return null;

    } catch (PDOException $e) {
        \http_response_code(500);
        exit(\json_encode([
            'success' => false,
            'result' => $e->getMessage(),
        ]));
    }
}

/** @return array data with all CSV elements in an multidimentional array */
function csvToData(array $rows) : array
{
    global $headline;

    $data = [];
    foreach ($rows as $index => $row) {
        if ($index === 0) {
            continue;
        }

        $parsedRow = [];
        if (!empty($row)) {
            foreach (explode(';', $row) as $column => $value) {
                if (strpos($value, ',')) {
                    $value = str_replace(',', '.', $value);
                }
                $parsedRow[$headline[$column]] = ltrim($value);
            }
            $data[] = $parsedRow;
        }
    }
    return $data;
}
