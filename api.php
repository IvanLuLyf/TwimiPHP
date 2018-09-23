<?php
header("Content-Type: application/json; charset=UTF-8");
define('APP_PATH', __DIR__ . '/');
define("IN_TWIMI_PHP", "True", TRUE);
define("IN_TWIMI_API", "True", TRUE);
include "config/config.php";
include "core/core.php";
tp_run();