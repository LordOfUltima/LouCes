<?php
/**
 * User: elkuku
 * Date: 07.02.13
 * Time: 17:11
 */

// The "bot user" data
define('BOT_EMAIL', 'your@email.com');
define('BOT_PASSWORD', 'yourpassword');
define('BOT_OWNER', 'YourInGameNick');

// your world server!
define('BOT_SERVER', 'http://prodgameXX.lordofultima.com/XXX/');

// your prefered language for login
define('BOT_LANG', 'de');

// prefix for commands to the bot
define('PRE', '!');

// time settings
date_default_timezone_set("Europe/Berlin"); // server default timezone
setlocale(LC_TIME, "de_DE");
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

//
// after this, no changes needed!
// edit: for the first run....
//
//
// stats
define('STATS_URL', 'stats.localhost/player.php?name=%s');
// alice
define('ALICETTL', 3);
define('ALICEID', 'youralice_id'); // need an published alicebot id

// server pain barrier
// IMPORTANT: lower this value if LoU gets kicked =;)
define('MAX_PARALLEL_REQUESTS', 16);

// redis database
##define('REDIS_CONNECTION', ((CLI) ? '/var/run/redis/redis.sock' : '127.0.0.1')); // localhost or socket
define('REDIS_CONNECTION', '127.0.0.1');
define('REDIS_NAMESPACE', 'lou:'); // use custom prefix on all keys
define('REDIS_DB', 1);
define('REDIS_LOG_FILE', LOG_PATH . 'redis.txt');

// OK, maybe no further changes needed from here on....
