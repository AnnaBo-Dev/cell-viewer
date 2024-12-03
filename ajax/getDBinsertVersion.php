<?php
require_once __DIR__ . '../../config/config.php';
$dbconn = openConn();

// Define the response content type to be a json result.
header('Content-Type: application/json');

$result = null;

try {
    $statement = $dbconn->prepare(
        'SELECT `version`
        FROM `cv_data_versions`
        ORDER BY `id` 
        DESC LIMIT 1
        ',
    );

    $statement->execute();
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
