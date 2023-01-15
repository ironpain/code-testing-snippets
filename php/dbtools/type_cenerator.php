<?php
define('DDL_TABLE_QUERY', 'SHOW CREATE TABLE :table_name;');

define('TABLE_QUERY', 'SELECT TABLE_NAME FROM information_schema.`TABLES` is_t
	WHERE
		is_t.TABLE_TYPE="BASE TABLE" AND
		is_t.TABLE_SCHEMA=:db_name;');

define('COLUMN_QUERY', 'SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.`COLUMNS` is_c
	WHERE
		is_c.TABLE_SCHEMA=:db_name AND
		is_c.TABLE_NAME=:table_name;');

define('TABLE_CHECKSUM', 'CHECKSUM table :table_name EXTENDED;');

define('ALL_TRIGGERS', 'SELECT
	is_tr.TRIGGER_NAME AS TRIGGER,
	is_tr.EVENT_MANIPULATION AS EVENT,
	is_tr.EVENT_OBJECT_TABLE AS TABLE,
	is_tr.ACTION_STATEMENT AS Statement,
	is_tr.ACTION_TIMING AS Timing,
	is_tr.CREATED,
	is_tr.SQL_MODE,
	is_tr.`DEFINER`,
	is_tr.CHARACTER_SET_CLIENT,
	is_tr.COLLATION_CONNECTION,
	is_tr.DATABASE_COLLATION
FROM information_schema.`TRIGGERS` AS is_tr
WHERE TRIGGER_SHEMA = :db_name');

define('TABULATOR', str_repeat(" ", 4));


// require_once 'ConsoleTable.php';

// use LucidFrame\Console\ConsoleTable;


function getChechsumDDLTable(PDO $db_conn, string $table_name)
{
	$return = ['0' => '', '1' => ''];
	$stmt = $db_conn->query(str_replace(':table_name', $table_name, DDL_TABLE_QUERY));
	if ($stmt) {
		$row2 = $stmt->fetch();
		$return['0'] = $row2->Table;
		$return['1'] = hash('sha512', $table_name . $row2->{'Create Table'});
	}
	$stmt->closeCursor();
	unset($stmt);
	return $return;
}

function getAllTable(PDO $db_conn, string $db_name, callable $callback)
{
	$stmt2 = $db_conn->prepare(TABLE_QUERY);
	$current = getCurrentDatabase($db_conn);
	$stmt2->execute(['db_name' => $db_name]);
	$db_conn->exec('USE ' . $db_name . ';');

	$check_sums = [];
	# $table = new ConsoleTable();

	while (($row = $stmt2->fetch()) !== FALSE) {
		# $table->addRow(getChechsumDDLTable($db_conn, $row->TABLE_NAME));
	 	$_checkT = $callback($db_conn, $row->TABLE_NAME);  // getChechsumDDLTable($db_conn, $row->TABLE_NAME);
		$check_sums = array_merge($check_sums,[$_checkT['0']=> $_checkT['1']] );
		$_checkT = null;
	}

	$db_conn->exec('USE ' . $current . ';');
	$stmt2->closeCursor();
	unset($stmt2, $current);

	return ($check_sums);
	// $table->hideBorder()
	// 	->display();
}

function getCurrentDatabase(PDO $db_conn)
{
	$stmt = $db_conn->query('SELECT DATABASE() as db FROM DUAL;');
	try {
		if ($stmt) {
			$row = $stmt->fetch();
			return $row->db;
		}

		throw new Exception(error_get_last()['message']);
	} finally {
		if ($stmt)  $stmt->closeCursor();
		unset($stmt);
	}
}

function getChechsumTableContent(PDO $db_conn, string $table_name)
{
	$return = ['0' => '', '1' => ''];
	$stmt = $db_conn->query(str_replace(':table_name', $table_name, TABLE_CHECKSUM));
	if ($stmt) {
		$row2 = $stmt->fetch();
		$return['0'] = $row2->Table;
		$return['1'] = $row2->Checksum;
	}
	$stmt->closeCursor();
	unset($stmt);
	return $return;
}

function getAllDBTriggers(PDO $db_conn, string $db_name) {
	$stmt = $db_conn->prepare(ALL_TRIGGERS);
	$stmt->bindValue('db_name', $db_name, PDO::PARAM_STR);
	$stmt->execute();

	$result = [];
	while(($row = $stmt->fetch()) !== false) {
		if (!isset($result[$row->TABLE])) $result[$row->TABLE] = [];
		array_push($result[$row->TABLE],[
			'TRIGGER' => $row->TRIGGER,
			'EVENT' => $row->EVENT,
			'TABLE' => $row->TABLE,
			'Statement' => $row->Statement,
			'Timing' => $row->Timing,
			'CREATED' => $row->CREATED,
			'SQL_MODE' => $row->SQL_MODE,
			'DEFINER' => $row->DEFINER,
			'CHARACTER_SET_CLIENT' => $row->CHARACTER_SET_CLIENT,
			'COLLATION_CONNECTION' => $row->COLLATION_CONNECTION,
			'DATABASE_COLLATION' => $row->DATABASE_COLLATION
		]);
	}
	$stmt->closeCursor();
	return $result;
}

function createDatabaseTablePHPTypes(PDO $db_conn, string $db_name)
{
	$tables = $db_conn->prepare(TABLE_QUERY);
	$tables->execute(['db_name' => $db_name]);

	$columns = $db_conn->prepare(COLUMN_QUERY);

	while (($row = $tables->fetch()) !== false) {
		$columns->execute(['db_name' => $db_name, 'table_name' => $row->TABLE_NAME]);

		$file_content = "<?php" . PHP_EOL . PHP_EOL;
		$clsname = function () use ($row) {
			return str_replace('_', '', ucwords($row->TABLE_NAME, '__'));
		};
		// $clsname =

		$file_content .= "class " . $clsname() . " {" . str_repeat(PHP_EOL, 2);
		$getter_setter = "";

		while (($row_c = $columns->fetch()) !== false) {
			switch ($row_c->DATA_TYPE) {
				case 'int':
					$file_content .= TABULATOR . "/** @var int */" . PHP_EOL;
					break;
				default:
					$file_content .= TABULATOR . "/** @var string */" . PHP_EOL;
					break;
			}

			$file_content .= TABULATOR . "private \$_" . $row_c->COLUMN_NAME . ";" . PHP_EOL;
			// $var = "$" . $row_c->COLUMN_NAME;

			$getter_setter .= TABULATOR . "public function set" . ucfirst($row_c->COLUMN_NAME) . "(\$var) {" . PHP_EOL;
			$getter_setter .= TABULATOR . TABULATOR . "\$this->_" . $row_c->COLUMN_NAME . " = \$var;" . PHP_EOL;
			$getter_setter .= TABULATOR . "}" . PHP_EOL . PHP_EOL;

			$getter_setter .= TABULATOR . "public function get" . ucfirst($row_c->COLUMN_NAME) . "() {" . PHP_EOL;
			$getter_setter .= TABULATOR . TABULATOR . "return \$this->_" . $row_c->COLUMN_NAME . ";" . PHP_EOL;
			$getter_setter .= TABULATOR . "}" . PHP_EOL . PHP_EOL;
		}


		$file_content .= PHP_EOL . $getter_setter . PHP_EOL . "}" . PHP_EOL . PHP_EOL;

		echo $file_content;
		unset($file_content, $getter_setter);
	}

	$tables->closeCursor();
	$columns->closeCursor();
}

function print_mem()
{
	/* Currently used memory */
	$mem_usage = memory_get_usage();

	/* Peak memory usage */
	$mem_peak = memory_get_peak_usage();
	echo "The script is now using: \033[31m" . round($mem_usage / 1024) . " KB\033[39m of memory." . PHP_EOL;
	echo 'Peak usage: ' . "\033[31m" . round($mem_peak / 1024) . " KB\033[39m" . 'of memory.' . PHP_EOL . PHP_EOL;
}


$db_conn=null;
$db_conf=false;
print_mem();
try{
	$db_conf = parse_ini_file('./.ini',true, INI_SCANNER_NORMAL);

	$db_conn = new PDO($db_conf['db1']['dns'], $db_conf['db1']['user'], $db_conf['db1']['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_LAZY | PDO::FETCH_OBJ]);


	print_r(getAllTable($db_conn, 'test', 'getChechsumDDLTable'));  // Table definition
	print_r(getAllTable($db_conn, 'test', 'getChechsumTableContent')); // Table Content
} catch(Exception $ex) {
	echo $ex->getMessage() . PHP_EOL;
}



# createDatabaseTablePHPTypes($db_conn, 'phpmyadmin');
$db_conn = null;
print_mem();
unset($db_conn, $db_conf);


