<?php
function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

$userId = isset($_GET['id']) ? cleanInput($_GET['id']) : null;

$accessTokenFile = 'tokenEAAD6V7.txt';
$accessTokenList = [];

$fileHandle = fopen($accessTokenFile, "r");
if ($fileHandle) {
    while (($line = fgets($fileHandle)) !== false) {
        $accessTokenList[] = trim($line);
    }
    fclose($fileHandle);
}

$success = false;
$apiResponse = null;

if (empty($userId)) {
    $responseArray = array(
        "status" => "error",
        "result" => array(
            "message" => "Thiếu tham số. Vui lòng nhập ID facebook hoặc key để sử dụng.",
            "type" => "?id={id}"
        ),
        "Admin" => array(
            "Author" => "Đinh Duy Vinh",
            "Facebook" => "https://www.facebook.com/duyvinh09",
            "Zalo" => "https://zalo.me/duyvinh09"
        )
    );

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($responseArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

foreach ($accessTokenList as $accessToken) {
    $queryParams = [
        'fields' => 'id,is_verified,picture.width(500).height(500),cover,about,education,first_name,middle_name,hometown,last_name,link,location,locale,name,created_time,work,username,website,birthday,gender,relationship_status,significant_other,quotes,subscribers.limit(0),updated_time,timezone',
        'access_token' => $accessToken,
    ];
    $queryString = http_build_query($queryParams);

    $apiUrl = "https://graph.facebook.com/{$userId}?{$queryString}";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($apiResponse && $httpStatus === 200) {
        $success = true;
        break;
    }
}

$decodedResponse = json_decode($apiResponse, true);

if (!isset($decodedResponse['error'])) {
    if (isset($decodedResponse['created_time'])) {
        $createdTime = DateTime::createFromFormat("Y-m-d\TH:i:sP", $decodedResponse['created_time']);
        $decodedResponse['created_time'] = $createdTime->format('H:i:s | d/m/Y');
    }

    if (isset($decodedResponse['subscribers']['summary']['total_count'])) {
        $decodedResponse['followers'] = number_format($decodedResponse['subscribers']['summary']['total_count'], 0, '.', '.');
        unset($decodedResponse['subscribers']);
    }

    if (isset($decodedResponse['updated_time'])) {
        $updatedTime = DateTime::createFromFormat("Y-m-d\TH:i:sP", $decodedResponse['updated_time']);
        $decodedResponse['updated_time'] = $updatedTime->format('H:i:s | d/m/Y');
    }

    if (isset($decodedResponse['birthday'])) {
        $birthday_parts = explode("/", $decodedResponse['birthday']);
        if (count($birthday_parts) >= 2 && count($birthday_parts) <= 3) {
            $format = (count($birthday_parts) == 3) ? "m/d/Y" : "m/d";
            $birthday = DateTime::createFromFormat($format, $decodedResponse['birthday']);
            if ($birthday !== false) {
                $decodedResponse['birthday'] = $birthday->format('d/m' . ((count($birthday_parts) == 3) ? '/Y' : ''));
            } else {
                $decodedResponse['birthday'] = "Ngày sinh không hợp lệ";
            }
        }
    }
} else {
    $success = false;
}

$responseArray = array(
    "status" => ($success ? "success" : "error"),
    "result" => ($success ? $decodedResponse : "ID không tồn tại hoặc đã bị gỡ trên Facebook"),
    "Admin" => array(
        "Author" => "Đinh Duy Vinh",
        "Facebook" => "https://www.facebook.com/duyvinh09",
        "Zalo" => "https://zalo.me/duyvinh09"
    )
);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($responseArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>