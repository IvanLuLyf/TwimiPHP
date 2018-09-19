<?php
if (!defined("IN_TWIMI_PHP")) die('{"status":"forbidden access"}');
date_default_timezone_set('PRC');
define("DB_HOST", "localhost");
define("DB_NAME", "database");
define("DB_USER", "username");
define("DB_PASS", "password");

define("TP_SITE_NAME", "Your Site Name");
define("TP_SITE_URL", "example.com");

define("QQ_LOGIN", true);
define("QQ_APP_KEY", '');
define("QQ_APP_SECRET", '');
define("QQ_CALLBACK", 'http://example.com/index.php?mod=qqconnect&action=callback');

define("WB_LOGIN", true);
define("WB_APP_KEY", '');
define("WB_APP_SECRET", '');
define("WB_CALLBACK", 'http://example.com/index.php?mod=sinaconnect&action=callback');