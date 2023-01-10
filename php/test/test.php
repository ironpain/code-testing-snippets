<?php

require_once join(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'vendor', 'autoload.php']);

use function Date_Time\strftimeA;



echo ini_get("upload_max_filesize") . PHP_EOL;
echo strftimeA('YYYY') . PHP_EOL;
