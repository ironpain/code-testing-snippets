<?php
$dbUser = null;
$dbPW = null;

$db_conn = new PDO("mysql:host=localhost;dbname=information_schema", $dbUser, $dbPW, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$query = $db_conn->prepare('SELECT `COLUMN_NAME` FROM `COLUMNS` WHERE `TABLE_NAME`=:table and `TABLE_SCHEMA`=:db', [PDO::FETCH_ASSOC]);
$query->execute(['table' => 'person_phases', 'db' => 'shop']);

$col_list = '';

while (($row = $query->fetch()) !== false) {
  $col_list .= "'\"${row['COLUMN_NAME']}\":\"', COALESCE(${row['COLUMN_NAME']}, ''),'\",',\n";
}

$query->closeCursor();
$db_conn = null;

$col_list = substr($col_list, 0, -4);
$out = <<<OUT
CONCAT(
       '[',
       GROUP_CONCAT(
           CONCAT(
            '{',
${col_list}',
            '}'
           )
        ),
       ']'
) as json
OUT;


echo $out . PHP_EOL;

// echo '`' . join('`,`', ['foo', 'bar', 'hh']) . '`';
