# PHP Telegram Bot template

### Get start
1. Create a new repo using this template or clone this repo
2. Install required packages via [composer](https://getcomposer.org). `composer install`
3. Copy the `config/example.params.php` file and save it as `config/params.php`
4. `config/params.php` is a configuration file
    1. Configure the database data from `db->sqlite` 
    2. Configure the default language from `defaultLanguage` 
    3. Configure the default languages list from `languages`. To use multiple languages, I used the [`mrmuminov/php-i18n`](https://github.com/mrmuminov/php-i18n) package  
    4. Type the telgram bot token in the `telegram->token` field. You need to get the token via [@BotFather](https://t.me/botfather)
5. To use the bot, configure webhook via `setWebhook` mthod. [`Docs`](https://core.telegram.org/bots/api) [`setWebhook doc`](https://core.telegram.org/bots/api#setwebhook)
6. Log in to [Telegram](https://telegram.org) and send the `/start` command. Finish

### Requirements
* PHP version 7.0 or higher
* https://github.com/TelegramBot/Api for telegram bot methods. (`telegram-bot/api`)
* https://github.com/gabordemooij/redbean for woking database (`gabordemooij/redbean`)
* https://github.com/mrmuminov/php-i18n for internationalization (`mrmuminov/php-i18n`)
