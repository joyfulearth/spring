<?php
include_once __DIR__ . '/all-functions.php';

$allInfo = allInfo(ALLSITEPATH);

define('ALLNAME', $allInfo['name']);
define('ALLIN', $allInfo['in']);

define('SITEPATH', ALLSITEPATH);

include_once __DIR__ . '/../1-entry.php';

runFrameworkFile('site/begin');
