<?php

use Lib\Core\App;

global $startTime;
$startTime = floor(microtime(true) * 1000);

require_once dirname(__DIR__) . '/lib/Core/bootstrap.php';

App::run();
