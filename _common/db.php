<?php 

include_once  $_SERVER['DOCUMENT_ROOT']."/_common/ezsql/shared/ez_sql_core.php"; 
include_once  $_SERVER['DOCUMENT_ROOT']."/_common/ezsql/mysql/ez_sql_mysql.php"; 

date_default_timezone_set('America/Chicago');


// Initialise database object and establish a connection
// at the same time - db_user / db_password / db_name / db_host
$db = new ezSQL_mysql(getenv('DB_USER'),getenv('DB_PASS'),'stat','localhost');

?>
