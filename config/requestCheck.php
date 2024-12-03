<?php
$PASS = '';
$requestType = '';
if (isset($_GET['rt'])) {
    $requestType = htmlspecialchars($_GET['rt']);
} else {
    \http_response_code(404);
    require_once __DIR__ . '/../error.php';
    exit;
}

$decrypted = '';
$boolTimestamp = '';
if (isset($_GET['t'])) {
    $decrypted = decrypt($_GET['t'], checkUser($requestType));
} else {
    \http_response_code(404);
    require_once __DIR__ . '/../error.php';
    exit;
}

$endOfTime = time() - 2 * 60 * 60; // h * min * sec
$timeRange = $decrypted - $endOfTime;

$boolTimestamp = controlTimestamp($decrypted, $endOfTime);

/** if timestamp is correct, return website,
 * else show error side
 */
if ($boolTimestamp === true
    || ($boolTimestamp === true && isset($_GET['t']) 
    && isset($_GET['rt']))
) {
    require_once __DIR__ . '/../index.php';
} else if ($boolTimestamp !== true) {
    \http_response_code(405);
    require_once __DIR__ . '/../error.php';
    exit;
} else {
    \http_response_code(404);
    require_once __DIR__ . '/../error.php';
    exit;
}

if (!isset($_GET)) {
    \http_response_code(404);
    require_once __DIR__ . '/../error.php';
    exit;
}

/** check User parameter to get the correct PASS
 * @param string $requestType
 */
function checkUser($requestType){
    $PASS1 = 'AgEG46DF=deT3fsu-juz4';
    $PASS2 = 'Wr58yM-CTVQjkSUz!KtM7';
    // extern
    if ($requestType === 'ct3'){
        $PASS = $PASS1;
        return $PASS;
    }
    // intern
    else if ($requestType === 'Hp2'){
        $PASS = $PASS2;
        return $PASS;
    }
    //error
    else {
        \http_response_code(404);
        require_once __DIR__ . '/../error.php';
        exit;
    }
}

/** decrypt the timestamp and return it
 * @param string $encryptedText
 * @param string $PASS
 */
function decrypt($encryptedText, $PASS) {
    $encryptedText = base64_decode($encryptedText);
    $iv = substr($encryptedText, 0, 16);
    $hash = substr($encryptedText, 16, 32);
    $ciphertext = substr($encryptedText, 48);
    $key = hash('sha256', $PASS, true);
    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

/** check is timestamp in time range, return bool
 * @param string $timeStamp
 * @return bool
 */
function controlTimestamp($timeStamp, $endOfTime) : bool {
    $current = time() + 15 * 60; // min * sec
    // $endOfTime = time() - 2 * 60 * 60; //  h * min * sec

    if ($timeStamp <= $current
        && $timeStamp >= $endOfTime
        || $timeStamp == $current
        || $timeStamp == $endOfTime
    ) { 
        return true;
    }
    return false;
}
