<?php
	require_once 'models.php';

 	$config = array(
	"dbtype"=>"mysql",
	"login"=>"root",
	"dbpass"=>"",
	"dbhost"=>"192.168.1.2:3306",
	"dbname"=>"library",
	);

	/*$config = array(
	"dbtype"=>"sqlite",
	"path"=>"/domain/bd.sqlite",
	);*/


	$columns = array('surname','name');
	$table = "stas";
	$a = "id";
	$low_priority = true;
	$ignore = true;



	$values_update = array(
		"name"=>"stasik",
		"surname"=>"pupok"
		);
	$values_query = array(
		"name"=>"nick",
		"surname"=>"travolta"
		);

try {
	$db = new MyQueryBuilder($config);

	$db->beginTransaction();

	$db->select($columns)->from($table)->where($a,'=', 'stasik')->limit(1,6);//Пример Запроса SELECT
	print_r($db->execute()->fetchAll()); //извлечение результата запроса	
//----------------------------------------------------------------------------------------------------
	$db->insert($table)->values($values_query);// Пример Запроса INSERT
	$db->execute();
//----------------------------------------------------------------------------------------------------
	$db->delete()->from($table)->where($a, '>=', 90);//Пример запроса DELETE
	$db->execute();
//----------------------------------------------------------------------------------------------------
	$db->update($table, $low_priority, $ignore)->set($values_update)->where($a, '>=', 80);//Пример запроса UPDATE
	$db->execute();
//----------------------------------------------------------------------------------------------------
	$db->select($columns)->from($table)->join('contacts', 'stas.id', 'contacts.id');//Пример запроса join
	$db->select($columns)->from($table)->leftJoin('contacts', 'stas.id', 'contacts.id');
	$db->select($columns)->from($table)->rightJoin('contacts', 'stas.id', 'contacts.id'); 
	$db->select($columns)->from($table)->fullOuterJoin('contacts', 'stas.id', 'contacts.id');//В MySql не работает
	$db->select($columns)->from($table)->crossJoin('contacts');
	print_r($db->execute()->fetchAll());	
//----------------------------------------------------------------------------------------------------
	$db->raw("SELECT * FROM stas");//Пример raw запроса 
	print_r($db->execute()->fetchAll());

	$db->commit();

	unset($db);
} 
catch (PDOException $e) {
	echo $e->getMessage();
	$db->rollBack();
}
catch (Exception $e) {
	 	echo $e->getMessage();
}

?>