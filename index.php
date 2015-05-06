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
	$columns = array('stas.id','surname','mail','name');
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
	$values_query = array(
		"name"=>"djon",
		"surname"=>"boriawdawdsov"
		);



try {
	/*$db->select($columns)->from($table)->where($a,'=', 'stasik')->limit(4,4);//Пример Запроса SELECT
	echo "<pre>";
	print_r($db->execute()->fetchAll()); //извлечение результата запроса	
	echo "<pre/>";*/
//----------------------------------------------------------------------------------------------------
	/*print_r($db->insert($table)->values($values_query));// Пример Запроса INSERT
	$db->execute();*/
//----------------------------------------------------------------------------------------------------
	/*$db->delete()->from($table)->where($a, '>=', 15);//Пример запроса DELETE
	$db->execute();*/
//----------------------------------------------------------------------------------------------------
	/*echo $db->update($table, $low_priority, $ignore)->set($values_update)->where($a, '=', 'Stasik');//Пример запроса UPDATE
	$db->execute()->fetchAll();*/
//----------------------------------------------------------------------------------------------------
	echo $db->select($columns)->from($table)->join('contacts', 'stas.id', 'contacts.id');//->orOn('stas.id','=', 'contacts.id');//Пример запроса join
	echo $db->select($columns)->from($table)->leftJoin('contacts', 'stas.id', 'contacts.id');
	echo $db->select($columns)->from($table)->rightJoin('contacts', 'stas.id', 'contacts.id'); 
	echo $db->select($columns)->from($table)->fullOuterJoin('contacts', 'stas.id', 'contacts.id');//В MySql не работает
	echo $db->select($columns)->from($table)->crossJoin('contacts', 'stas.id', 'contacts.id');
	print_r($db->execute()->fetchAll());

//----------------------------------------------------------------------------------------------------
	/*$db->raw("SELECT * FROM stas");Пример raw запроса 
	print_r($db->execute()->fetchAll());


} catch (Exception $e) {
	 	echo $e->getMessage();
}

?>