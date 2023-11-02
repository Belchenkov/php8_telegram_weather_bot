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

debug($update);

