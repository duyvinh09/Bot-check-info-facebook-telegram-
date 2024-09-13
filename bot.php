<?php
// BẢN QUYỀN THUỘC BỞI ĐINH DUY VINH | ZALO: zalo.me/duyvinh09 | FB: fb.com/duyvinh09 | KHÔNG SỬ DỤNG MÃ NGUỒN CỦA BÊN KHÁC CUNG CẤP! CHÚNG TÔI SẼ KHÔNG BẢO HÀNH LỖI MÀ BẠN GẶP.
define('BOT_TOKEN', '6829186156:AAE0W0M3joIs-MQoQfaxrl_hLt6VxH8PRLo'); // token bot để chạy

$data = file_get_contents('php://input');
$json = json_decode($data, true);

$allowedGroupIds = ['-1002006685178']; // List ID group chat ['ID nhóm 1', 'ID nhóm 2']
$adminChatIds = ['5316151847']; // List ID admin (cách lấy id thì vào @MissRose_bot sài lệnh /id @duyvinh09)

if (isset($json['message']['text'])) {
    $message = $json['message']['text'];
    $chatId = $json['message']['chat']['id'];
    $messageId = $json['message']['message_id'];
    $fromId = $json['message']['from']['id'];
    $isAdmin = in_array(strval($fromId), $adminChatIds);
    $isAllowedGroup = in_array(strval($chatId), $allowedGroupIds);

    if ($isAdmin || $isAllowedGroup) {
        if ($isAdmin && isset($json['message']['text'])) {
            $adminCommand = $json['message']['text'];
        
            if (stripos($adminCommand, '/thongbao') === 0) {
                $notificationText = trim(str_ireplace('/thongbao', '', $adminCommand));
                if (!empty($notificationText)) {
                    foreach ($allowedGroupIds as $allowedGroupId) {
                        sendMessage($allowedGroupId, $notificationText);
                    }
                } else {
                    foreach ($adminChatIds as $adminChatId) {
                        sendMessage($adminChatId, '🚫 Vui lòng nhập nội dung thông báo: /thongbao +  nội dung', $messageId);
                    }
                }
            }
        }
        
        if (stripos($message, 'info') === 0) {
            if ($isAllowedGroup || $isAdmin) {
                $url = trim(substr($message, $urlStartIndex + 1));
                if (!empty($url)) {
                    $input = trim(str_ireplace('info', '', $message));
                    if (is_numeric($input) || stripos($input, 'facebook.com') !== false || !empty($input)) {
                        if (stripos($input, 'facebook.com') !== false || !is_numeric($input)) {
                            if (stripos($input, 'facebook.com') !== false) {
                                $input = preg_replace('/^(?:https?:\/\/|http:\/\/)?(?:www\.|m\.|mobile\.|d\.|touch\.|mbasic\.)?(?:facebook\.com)\/(?:profile\.php\?id=)?(\w+)(?:[?&]mibextid=[a-zA-Z0-9]+)?/i', 'https://www.facebook.com/$1', $input);
                                $input = preg_replace('/^(?:https?:\/\/|http:\/\/)?(?:www\.|m\.|mobile\.|d\.|touch\.|mbasic\.)?(?:facebook\.com)\/([^\/?]+)(?:[?&]mibextid=[a-zA-Z0-9]+)?/i', 'https://www.facebook.com/$1', $input);
                                $matches = [];
                                preg_match('/facebook.com\/(?:[^\/]*\/)*([^\/?]+)/i', $input, $matches);
                                $facebookId = isset($matches[1]) ? $matches[1] : null;
                                $messageSentId = sendMessage($chatId, "🔎   Đang lấy thông tin...", $messageId);
                                $apiUrl = 'https://scaninfo.vn/api/convertID.php?url=' . urlencode($input); // đường link dẫn tới file convertID.php, thay đoạn này phù hợp với đường dẫn tới file convertID.php của bạn
                            } else {
                                $messageSentId = sendMessage($chatId, "🔎  Đang lấy thông tin...", $messageId);
                                $apiUrl = 'https://scaninfo.vn/api/convertID.php?url=' . urlencode($input);
                            }
                            
                            $response = file_get_contents($apiUrl);
                            $dataFromApi = json_decode($response, true);
                            if (isset($dataFromApi['id'])) {
                                $userId = $dataFromApi['id'];
                                $apiUrl = 'https://scaninfo.vn/api/apiCheck.php?id=' . urlencode($userId); // đường link dẫn tới file apiCheck.php, thay đoạn này phù hợp với đường dẫn tới file apiCheck.php của bạn
                                $response = file_get_contents($apiUrl);
                                $dataFromApi = json_decode($response, true);
                                if (isset($dataFromApi['status']) && $dataFromApi['status'] === 'error') {
                                    editMessage($chatId, $messageSentId, "❌ Không tìm thấy thông tin liên quan đến link này trên Facebook.");
                                } else {
                                    if (isset($dataFromApi['result'])) {
                                        $formattedUserData = formatUserData($dataFromApi['result']);
                                        editMessage($chatId, $messageSentId, $formattedUserData);
                                    }
                                }
                            } else {
                                editMessage($chatId, $messageSentId, "❌ Vui lòng kiểm tra lại, có thể link bạn check đã sai định dạng hoặc không tồn tại trên Facebook.");
                            }
                        } else {
                            $messageSentId = sendMessage($chatId, "🔎  Đang lấy thông tin...", $messageId);
                            $apiUrl = 'https://scaninfo.vn/api/apiCheck.php?id=' . urlencode($input);
                            $response = file_get_contents($apiUrl);
                            $dataFromApi = json_decode($response, true);
                            if (isset($dataFromApi['status']) && $dataFromApi['status'] === 'error') {
                                editMessage($chatId, $messageSentId, "❌ Không tìm thấy thông tin liên quan đến ID này trên Facebook.");
                            } else {
                                if (isset($dataFromApi['result'])) {
                                    $formattedUserData = formatUserData($dataFromApi['result']);
                                    editMessage($chatId, $messageSentId, $formattedUserData);
                                }
                            }
                        }
                    }
                } else {
                    sendMessage($chatId, "⚠️ Vui lòng nhập một ID, Facebook link, hoặc username sau info.", $messageId);
                }
            } else {
                sendMessage($chatId, "❌ Bạn không có quyền sử dụng lệnh này. Vui lòng truy cập nhóm @tienich để sử dụng lệnh info.", $messageId);
            }
            exit;
        }
    } else {
        sendMessage($chatId, "❌ Bạn không có quyền sử dụng BOT này. Vui lòng inbox cho @duyvinh09 để được hỗ trợ.", $messageId);
    }
}

function formatUserData($userData) {
    $id = $userData['id'];
    $name = $userData['name'];
    $username = isset($userData['username']) ? $userData['username'] : ' ';
    $verified = $userData['is_verified'] ? 'Đã xác minh' : 'Chưa xác minh';
    // $link = $userData['link'];
    $avatarURL = $userData['picture']['data']['url'];
    $hometown = isset($userData['hometown']['name']) ? $userData['hometown']['name'] : 'Không công khai';
    $location = isset($userData['location']['name']) ? $userData['location']['name'] : 'Không công khai';
    $locale = isset($userData['locale']) ? $userData['locale'] : 'Không công khai';
    $created_time = isset($userData['created_time']) ? $userData['created_time'] : 'Không công khai';
    $work = isset($userData['work']) ? $userData['work'][0]['employer']['name'] : 'Không công khai';
    $birthday = isset($userData['birthday']) ? $userData['birthday'] : 'Không công khai';
    $gender = isset($userData['gender']) ? ($userData['gender'] == 'male' ? 'Nam' : 'Nữ') : 'Không công khai';
    $relationship_status = isset($userData['relationship_status']) ? $userData['relationship_status'] : 'Không công khai';
    $followers = isset($userData['followers']) ? $userData['followers'] . ' người' : 'Không công khai';
    // $website = isset($userData['website']) ? $userData['website'] : ' ';
    $updated_time = isset($userData['updated_time']) ? $userData['updated_time'] : 'Không công khai';
    $timezone = isset($userData['timezone']) ? $userData['timezone'] : 'Không công khai';
    $message = "╭─────────────⭓\n│ 𝗜𝗗: $id\n│ 𝗡𝗮𝗺𝗲: $name\n│ 𝗨𝘀𝗲𝗿𝗻𝗮𝗺𝗲: $username\n│ 𝗩𝗲𝗿𝗶𝗳𝗶𝗲𝗱: $verified\n│ 𝗖𝗿𝗲𝗮𝘁𝗲𝗱 𝗧𝗶𝗺𝗲: $created_time\n│ 𝗚𝗲𝗻𝗱𝗲𝗿: $gender\n│ 𝗥𝗲𝗹𝗮𝘁𝗶𝗼𝗻𝘀𝗵𝗶𝗽𝘀: $relationship_status\n│ 𝗛𝗼𝗺𝗲𝘁𝗼𝘄𝗻: $hometown\n│ 𝗟𝗼𝗰𝗮𝘁𝗶𝗼𝗻: $location\n│ 𝗪𝗼𝗿𝗸: $work\n│ 𝗕𝗶𝗿𝘁𝗵𝗱𝗮𝘆: $birthday\n│ 𝗙𝗼𝗹𝗹𝗼𝘄𝘀: $followers\n├─────────────⭔\n│ 𝗟𝗼𝗰𝗮𝗹𝗲: $locale\n│ 𝗨𝗽𝗱𝗮𝘁𝗲 𝗧𝗶𝗺𝗲: $updated_time\n│ 𝗧𝗶𝗺𝗲 𝗭𝗼𝗻𝗲: GMT $timezone\n╰─────────────⭓\n";
    $image_url = $avatarURL;
    $image_link = "<a href=\"$image_url\"> ‏ </a>";
    $message .= $image_link;
    return $message;
}

function sendMessage($chatId, $message, $replyToMessageId = null, $parseMode = 'HTML') {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage?chat_id=$chatId&text=" . urlencode($message) . "&parse_mode=$parseMode";
    if ($replyToMessageId !== null) {
        $url .= "&reply_to_message_id=$replyToMessageId";
    }
    $result = file_get_contents($url);
    $resultJson = json_decode($result, true);
    return isset($resultJson['result']['message_id']) ? $resultJson['result']['message_id'] : null;
}

// hàm này là dùng để chỉnh lại tin nhắn "🔎  Đang lấy thông tin..." mỗi lần check
function editMessage($chatId, $messageId, $message, $parseMode = 'HTML') {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/editMessageText?chat_id=$chatId&message_id=$messageId&text=" . urlencode($message) . "&parse_mode=$parseMode";
    file_get_contents($url);
}
// https://api.telegram.org/bot<tokenbot>/setWebhook?url=https://scaninfo.vn/api/bot.php
// Set admin cho bot trong group tele, cấp all quyền để bot mới hoạt động và yêu cầu bật công khai group
?>