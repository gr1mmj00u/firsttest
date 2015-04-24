<?php
	require_once 'models.php';

 	$config = array(
	"dbtype"=>"mysql",
	"login"=>"root",
	"dbpass"=>"",
	"dbhost"=>"192.168.1.2:3306",
	"dbname"=>"library"
	);

	$db = new MyActiveRecord($config);
	$columns = array("id", "name");
	$table = "stas";
	$a = "name";
	$low_priority = false;
	$ignore = false;

	$values_query = array(
		"name"=>"djon",
		"surname"=>"boriawdawdsov"
		);

	$values_update = array(
		"name45234"=>"stasik",
		"surname"=>"pupok"
		);

try {
	echo $db->select($columns)->from($table)->where($a,'=', 'stasik')->limit(4,4);//Пример Запроса SELECT
	echo "<pre>";
	print_r($db->execute()->fetchAll()); //извлечение результата запроса	
	echo "<pre/>";
//----------------------------------------------------------------------------------------------------
	print_r($db->insert($table)->values($values_query));// Пример Запроса INSERT
	$db->execute();
//----------------------------------------------------------------------------------------------------
	$db->delete()->from($table)->where($a, '>=', 15);//Пример запроса DELETE
	$db->execute();
//----------------------------------------------------------------------------------------------------
	echo $db->update($table, $low_priority, $ignore)->set($values_update)->where($a, '=', 'Stasik');//Пример запроса UPDATE
	$db->execute()->fetchAll();

} catch (Exception $e) {
	 	echo $e->getMessage();
}

?>