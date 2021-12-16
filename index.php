<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use RedBeanPHP\R;
use TelegramBot\Api\Client;
use MrMuminov\PhpI18n\I18n;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

require_once "vendor/autoload.php";

$params = require "config/params.php";
$i18n = new I18n([
    'languages' => $params['languages'],
    'language' => $params['defaultLanguage'],
    'path' => __DIR__ . '/i18n/',
]);

try {

    R::setup($params['db']['sqlite'], '', '', false);

    /** @var $bot TelegramBot\Api\BotApi|TelegramBot\Api\Client */
    $bot = new Client($params['telegram']['token']);

    $promptLanguage = function($bot, $message, $langList) use ($i18n) {
        /** @var $message TelegramBot\Api\Types\Message */
        $keyboard = [];
        $text = '';
        foreach ($langList as $lang) {
            $text .= $i18n->get('Select language', $lang) . PHP_EOL;
            $i18n->setLanguage($lang);
            $keyboard[] = [
                [
                    'text' => $i18n->get('language_name'),
                    'callback_data' => 'lang:' . $lang,
                ],
            ];
        }
        $keyboard = new InlineKeyboardMarkup($keyboard);

        $bot->sendMessage($message->getChat()->getId(), $text, null, false, null, $keyboard);
    };

    $startLambda = function($bot, $message, $user) use ($i18n) {
        /** @var $message TelegramBot\Api\Types\Message */
        $text = $i18n->get('Hello %s', $message->getChat()->getFirstName() . " " . $message->getChat()->getLastName()) . PHP_EOL;
        $keyboard = new InlineKeyboardMarkup([
            [
                [
                    'text' => $i18n->get('Change language', $user->language),
                    'callback_data' => 'button:change-language',
                ],
            ],
        ]);

        $bot->sendMessage($message->getChat()->getId(), $text, null, false, null, $keyboard);
    };

    $bot->command('start', function($message) use ($startLambda, $i18n, $promptLanguage, $bot) {
        $user = R::findOne('user', "chat_id = ?", [$message->getChat()->getId()]);
        if (empty($user) || $user->isEmpty()) {
            $user = R::dispense('user');
            $user->chat_id = $message->getChat()->getId();
            $user->language = '';
            $user->created_at = date('Y-m-d H:i:s');
            R::store($user);
            $promptLanguage($bot, $message, $i18n->getLanguages());
            exit;
        }
        $startLambda($bot, $message, $user);
    });

    $bot->command('statistics', function($message) use ($i18n,$bot) {
        $user = R::count('user');
        $bot->sendMessage($message->getChat()->getId(), $i18n->get('Users count') . ': ' . $user);
    });

    $bot->callbackQuery(function($callbackQuery) use ($promptLanguage, $i18n, $bot, $startLambda) {
        $user = R::findOne('user', "chat_id = ?", [$callbackQuery->getMessage()->getChat()->getId()]);
        $data = explode(':', $callbackQuery->getData());
        if (empty($data)) {
            $bot->answerCallbackQuery($callbackQuery->getId(), $i18n->get('Invalid query'));
        }
        switch ($data[0]) {
            case 'button':
                switch ($data[1]) {
                    case 'change-language':
                        $bot->deleteMessage($callbackQuery->getMessage()->getChat()->getId(), $callbackQuery->getMessage()->getMessageId());
                        $promptLanguage($bot, $callbackQuery->getMessage(), $i18n->getLanguages());
                        break;
                    case 'home':
                        $bot->deleteMessage($callbackQuery->getMessage()->getChat()->getId(), $callbackQuery->getMessage()->getMessageId());
                        $startLambda($bot, $callbackQuery->getMessage(), $user);
                        break;
                }
                break;
            case 'lang':
                $user->language = $data[1];
                R::store($user);
                $bot->answerCallbackQuery($callbackQuery->getId(), $i18n->get('Language changed'));
                $bot->deleteMessage($callbackQuery->getMessage()->getChat()->getId(), $callbackQuery->getMessage()->getMessageId());
                $startLambda($bot, $callbackQuery->getMessage(), $user);
                break;
        }
    });

    $bot->run();


} catch (\TelegramBot\Api\Exception $e) {
    $bot->sendMessage(1053696039, json_encode([
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getCode(),
        $e->getMessage(),
    ], JSON_PRETTY_PRINT));
}
