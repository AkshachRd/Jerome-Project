<?php
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

require_once 'getWordInfo.php';

$telegram = new Api('861121918:AAE1caaPhjPytqAhgEWdXaG9azEQIyVmcJs'); //Устанавливаем токен, полученный у BotFather
$result = $telegram -> getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Последние статьи"],["Картинка"],["Гифка"]]; //Клавиатура

if (isset($text))
{
    if ($text == "/start") {
        $reply = "Добро пожаловать в бота!";
        $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
    /*}elseif ($text == "/help") {
        $reply = "Информация с помощью.";
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply ]);
    }elseif ($text == "Картинка") {
        $url = "https://68.media.tumblr.com/6d830b4f2c455f9cb6cd4ebe5011d2b8/tumblr_oj49kevkUz1v4bb1no1_500.jpg";
        $telegram->sendPhoto([ 'chat_id' => $chat_id, 'photo' => $url, 'caption' => "Описание." ]);
    }elseif ($text == "Гифка") {
        $url = "https://68.media.tumblr.com/bd08f2aa85a6eb8b7a9f4b07c0807d71/tumblr_ofrc94sG1e1sjmm5ao1_400.gif";
        $telegram->sendDocument([ 'chat_id' => $chat_id, 'document' => $url, 'caption' => "Описание." ]);
    }elseif ($text == "Последние статьи") {
        $html=simplexml_load_file('http://netology.ru/blog/rss.xml');
        foreach ($html->channel->item as $item) {
            $reply .= "\xE2\x9E\xA1 ".$item->title." (<a href='".$item->link."'>читать</a>)\n";
        }
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);*/
    }else{
        $text = strtolower($text);
        $pronunciations = getWordInfo($text);

        if (!empty($pronunciations["transcriptionUK"]))
        {
            $transcriptionUK = "\xF0\x9F\x87\xAC\xF0\x9F\x87\xA7:" . $pronunciations["transcriptionUK"];
        }
        else
        {
            $transcriptionUK = "";
        }
        if (!empty($pronunciations["transcriptionUS"]))
        {
            $transcriptionUS = "\xF0\x9F\x87\xBA\xF0\x9F\x87\xB8:" . $pronunciations["transcriptionUS"];
        }
        else
        {
            $transcriptionUS = "";
        }

        $text[0] = strtoupper($text[0]);
        $reply = "$text $transcriptionUK $transcriptionUS";
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply ]);
        //TODO Сдеалть преобразование mp3 в ogg, и передавать их как sendVoice
        if (!empty($pronunciations["audioUK"]))
        {
            $telegram->sendAudio([ 'chat_id' => $chat_id, 'audio' => $pronunciations["audioUK"], 'title' => "\xF0\x9F\x87\xAC\xF0\x9F\x87\xA7" ]);
        }
        if (!empty($pronunciations["audioUS"]))
        {
            $telegram->sendAudio([ 'chat_id' => $chat_id, 'audio' => $pronunciations["audioUS"], 'title' => "\xF0\x9F\x87\xBA\xF0\x9F\x87\xB8" ]);
        }
    }
}else{
    $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение." ]);
}
?>







