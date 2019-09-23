<?php
//
// db.php - common DB stuff.
//

// Perform DB query - returns array of rows.
// Courtesy of M. Arronax.

function simple_query(
    $sql,
    $params = array(),
    $remove_extra_spaces = true
) {
    $out = array();
    $pdo = $GLOBALS['pdo'];
    if ($remove_extra_spaces) {
        $sql = trim(preg_replace('/\s\s+/', ' ', $sql));
    }
	//print "sql=".$sql."<br>\n";
    $sth = $pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $sth->execute($params);
    for ($i=0; $row = $sth->fetch(); $i++) { $out[$i] = $row; }

    return $out;
}

//
// Create DSN.
//
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

//print "db_host=$db_host<BR>";
//print "db_name=$db_name<BR>";
//print "db_user=$db_user<BR>";
//print "db_pass=$db_pass<BR>";

$dsn = "mysql:host=${db_host};dbname=${db_name};charset=utf8mb4";
//print "dsn=$dsn<br>";

// Get a PDO instance.
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // jdw
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

?>