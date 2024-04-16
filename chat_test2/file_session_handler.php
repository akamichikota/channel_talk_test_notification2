<?php


function startFileSession($userId) {
    $session_id = bin2hex(random_bytes(5));  // 例えば10文字のヘキサ値
    $sessionData = [
        'user_id' => $userId,
        'start_time' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ];
    file_put_contents(__DIR__ . "/sessions/{$session_id}.json", json_encode($sessionData));
    notifyChannelTalk($session_id, "アンケートが開始されました。");
    return ['success' => true, 'session_id' => $session_id];
}

function updateFileSession($sessionId, $step) {
    $sessionFile = __DIR__ . "/sessions/{$sessionId}.json";
    if (!file_exists($sessionFile)) {
        return ['success' => false, 'message' => 'Session does not exist'];
    }
    $sessionData = json_decode(file_get_contents($sessionFile), true);
    $sessionData['last_step'] = $step;
    $sessionData['last_update'] = date('Y-m-d H:i:s');

    file_put_contents($sessionFile, json_encode($sessionData));
    return ['success' => true];
}

function endFileSession($sessionId) {
    $sessionFile = __DIR__ . "/sessions/{$sessionId}.json";
    if (!file_exists($sessionFile)) {
        return ['success' => false, 'message' => 'Session does not exist'];
    }
    $sessionData = json_decode(file_get_contents($sessionFile), true);
    $sessionData['status'] = 'completed';
    $sessionData['end_time'] = date('Y-m-d H:i:s');

    file_put_contents($sessionFile, json_encode($sessionData));
    
    // セッションファイルを削除
    if (unlink($sessionFile)) {
        notifyChannelTalk($sessionId, "アンケートが途中で終了されました。");
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to delete session file'];
    }
}


function notifyChannelTalk($session_id, $message) {
    $url = "https://api.channel.io/open/v5/user-chats/661df20311ad25fb4185/messages";
    $headers = [
        'Content-Type: application/json',
        'X-Access-Key: 661b221782915c8bc762',
        'X-Access-Secret: 3580f8e89e85879c260ab394d1779035'
    ];

    $fullMessage = "セッションID {$session_id}: {$message}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["blocks" => [["type" => "text", "value" => $fullMessage]]]));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
?>
