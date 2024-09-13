<?php
// https://ffb.vn/api/tool/get-id-fb?idfb=
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = isset($_GET['url']) ? $_GET['url'] : '';
    if (filter_var($input, FILTER_VALIDATE_URL)) {
        $url = $input;
    } else {
        $url = 'https://www.facebook.com/' . $input;
    }
    $apiResponse = getID($url);
    header('Content-Type: application/json; charset=UTF-8');
    echo $apiResponse;
}
function getID($urlFb) {
    if (strpos($urlFb, 'https://www.facebook.com/profile.php?=') !== false) {
        $urlFb = 'https://www.facebook.com/duyvinh09';
    }
    $apiUrl = "https://ffb.vn/api/tool/get-id-fb?idfb=" . urlencode($urlFb);
    $apiResponse = file_get_contents($apiUrl);
    $decodedApiResponse = json_decode($apiResponse, true);
    header('Content-Type: application/json; charset=UTF-8');
    echo $apiResponse;
}
?>