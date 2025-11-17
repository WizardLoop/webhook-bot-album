<?php
/*
Bot created by @wizardloop
Fully ready for distribution
*/
ob_start();

define('API_KEY', 'YOUR_BOT_TOKEN'); // Your bot token here
$adminx = "123456789"; // Chat ID of the admin to receive the messages

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}

// Get update from Telegram
$update = json_decode(file_get_contents('php://input'));

// Determine message or callback_query
$message = $update->message ?? $update->callback_query->message ?? null;
$callback_query = $update->callback_query ?? null;

if(!$message) exit;

$chat_id = $message->chat->id;
$message_id = $message->message_id;
$from_id = $message->from->id;
$first_name = $message->from->first_name;
$username = $message->from->username ?? '';
$text = $message->text ?? '';
$caption = $message->caption ?? '';
$media_group_id = $message->media_group_id ?? null;

// Files to store user progress
$userMediaFile = "media$from_id.json";
$check_file = "var$from_id.txt";
$msgeditfile = "msgfile$from_id.txt";

// Command to start album collection
if($text === "/album"){
    file_put_contents($check_file, "check");

    $msg_id = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "*Now send the messages, you can send single messages and albums.*\nSupported message types: Text/Photo/Video/Animation/Document",
        'parse_mode' => 'Markdown'
    ])->result->message_id;

    file_put_contents($msgeditfile, $msg_id);
}

// Load stored media
$userMedia = file_exists($userMediaFile) ? json_decode(file_get_contents($userMediaFile), true) : [];
$check = file_exists($check_file) ? trim(file_get_contents($check_file)) : null;

// Collect media/text from user
if($check === "check" && isset($message)) {
    $currentMedia = [];

    // Check text length if it's a caption or text
    $msgText = $message->text ?? $message->caption ?? '';
    if(mb_strlen($msgText) > 1024){
        bot('deleteMessage', ['chat_id'=>$chat_id,'message_id'=>$message_id]);
        $msgtodel = bot('sendMessage', [
            'chat_id'=>$chat_id,
            'text'=>"✖️ *Do not send text with more than 1024 characters.*\nCharacters sent: ".mb_strlen($msgText),
            'parse_mode'=>'Markdown'
        ])->result->message_id;
        sleep(3);
        bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$msgtodel]);
        exit;
    }

    // Collect media
    if(isset($message->photo) && is_array($message->photo)){
        $currentMedia[] = [
            'type'=>'photo',
            'file_id'=>end($message->photo)->file_id,
            'caption'=>$caption
        ];
    }
    if(isset($message->video)){
        $currentMedia[] = ['type'=>'video','file_id'=>$message->video->file_id,'caption'=>$caption];
    }
    if(isset($message->animation)){
        $currentMedia[] = ['type'=>'animation','file_id'=>$message->animation->file_id,'caption'=>$caption];
    }
    if(isset($message->video_note)){
        $currentMedia[] = ['type'=>'video_note','file_id'=>$message->video_note->file_id,'caption'=>$caption];
    }
    if(isset($message->document)){
        $currentMedia[] = ['type'=>'document','file_id'=>$message->document->file_id,'caption'=>$caption];
    }
    if(isset($message->text) && !preg_match('/^\/([Aa]lbum)/', $message->text)){
        $currentMedia[] = ['type'=>'text','text'=>$message->text];
    }

    // Merge with previous media
    $userMedia = array_merge($userMedia, $currentMedia);
    file_put_contents($userMediaFile, json_encode($userMedia));

    $count = count($userMedia);
    $msgidfile = file_exists($msgeditfile) ? trim(file_get_contents($msgeditfile)) : null;

    // Edit message to show progress
    bot('editMessageText', [
        'chat_id'=>$chat_id,
        'message_id'=>$msgidfile,
        'text'=>"📨 $count message(s) have been sent to the system. When finished, click ✅ Done to send.",
        'parse_mode'=>'HTML',
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>"✅ Done ✅",'callback_data'=>"send_now"]]
            ]
        ])
    ]);

    // Delete the user's message
    bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id]);
}

// Sending messages to admin
if($callback_query && $callback_query->data === "send_now") {
    if(file_exists($userMediaFile)){
        $userMedia = json_decode(file_get_contents($userMediaFile), true);

        $media_group = [];
        $text_messages = [];

        foreach($userMedia as $m){
            if($m['type']==='text'){
                $text_messages[] = $m['text'];
            } else {
                $media_group[] = [
                    'type'=>$m['type'],
                    'media'=>$m['file_id'],
                    'caption'=>$m['caption'] ?? ''
                ];
            }
        }

        // Send media in chunks of 10 (Telegram limit)
        $chunks = array_chunk($media_group, 10);
        foreach($chunks as $chunk){
            if(!empty($chunk)){
                bot('sendMediaGroup',[
                    'chat_id'=>$adminx,
                    'media'=>json_encode($chunk),
                    'disable_notification'=>true
                ]);
            }
        }

        // Send text messages separately
        foreach($text_messages as $text){
            bot('sendMessage',[
                'chat_id'=>$adminx,
                'text'=>$text,
                'parse_mode'=>'HTML',
                'disable_notification'=>true
            ]);
        }

        unlink($userMediaFile);
    }

    if(file_exists($check_file)) unlink($check_file);
    if(file_exists($msgeditfile)){
        $msgidfile = trim(file_get_contents($msgeditfile));
        bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$msgidfile]);
        unlink($msgeditfile);
    }

    bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"<b>Messages sent successfully ☑️</b>",
        'parse_mode'=>'HTML'
    ]);
}
