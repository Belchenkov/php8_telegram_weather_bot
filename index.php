<?php

// https://api.telegram.org/bot6701436362:AAH4eAuc5bNty2zn9DAtbvf5JqQQVBhGFVw/setWebhook?url=https://php-bot-weather.sharedwithexpose.com
error_reporting(-1);

ini_set('display_errors', 0);
ini_set('log_errors', 0);
ini_set('error_log', __DIR__ . '/errors.log');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$telegram = new \Telegram\Bot\Api(TOKEN);
$update = $telegram->getWebhookUpdate();

$chat_id = $update['message']['chat']['id'] ?? 0;
$text = $update['message']['text'] ?? '';
$name = $update['message']['from']['first_name'] ?? 'Guest';

if (!$chat_id) {
    die;
}

match ($text) {
    '/start' => $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Привет, {$name}! 🙋" . PHP_EOL . "Я бот-синоптик, который подскажет вам погоду в любом городе мира. Для получения погоды отправьте геолокацию (доступно с мобильных устройств). \nТакже возможно указать город в формате: <b>Город</b> или в формате <b>Город,код страны</b>. \nПримеры: <b>London</b>, <b>London,uk</b>, <b>Kiev,ua</b>, <b>Киев</b>",
        'parse_mode' => 'HTML',
    ]),
    '/help' => $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Я бот-синоптик, который подскажет вам погоду в любом городе мира. Для получения погоды отправьте геолокацию (доступно с мобильных устройств). \nТакже возможно указать город в формате: <b>Город</b> или в формате <b>Город,код страны</b>. \nПримеры: <b>London</b>, <b>London,uk</b>, <b>Kiev,ua</b>, <b>Киев</b>",
        'parse_mode' => 'HTML',
    ]),
    default => false
};

if (!empty($text)) {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Запрашиваю данные...",
    ]);

    $weather_url .= "&q={$text}";
    $weather = send_request($weather_url);

    debug($weather);
} elseif (isset($update['message']['location'])) {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Запрашиваю данные...",
    ]);

    $weather_url .= "&lat={$update['message']['location']['latitude']}&lon={$update['message']['location']['longitude']}";
    $weather = send_request($weather_url);

    debug($weather);
} else {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Укажите корректный формат локации",
    ]);
}

if (isset($weather)) {
    switch ($weather->cod) {
        case 200:
            // https://openweathermap.org/weather-conditions#Icon-list
            $temp = round($weather->main->temp);
            $answer = "<u>Информация о погоде:</u>\nГород: <b>{$weather->name}</b>\nСтрана: <b>{$weather->sys->country}</b>\nПогода: <b>{$weather->weather[0]->description}</b>\nТемпература: <b>{$temp}℃</b>";
            $telegram->sendPhoto([
                'chat_id' => $chat_id,
                'photo' => \Telegram\Bot\FileUpload\InputFile::create(DIR . "/img/{$weather->weather[0]->icon}.png"),
                'caption' => $answer,
                'parse_mode' => 'HTML',
            ]);
            break;
        case 400:
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "Укажите корректный формат локации",
            ]);
            break;
        default:
            debug($weather);
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "Возникла ошибка. Попробуйте позже",
            ]);
            break;
    }
}


