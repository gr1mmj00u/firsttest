<?php

class MyActiveRecord{

	//public $dbtype;
	//public $login;
	//public $dbpass;
	public $dbhost;
	public $dbname;
   
	private $config = array();
	public $query = "";
	public $pdo;
	public $prepare_query;

	function __construct($config){/*Соединение с БД*/
		$this->config = $config;
		print_r($this->config);
		$this->pdo = new PDO("$config[dbtype]:host=$config[dbname];dbname=$config[dbname]", $config['login'], $config['dbpass']);
		
	}

	function select($columns){
		if (gettype($columns)=="string" AND strlen($columns) < 100 AND preg_match("/^[A-Za-zА-Яа-я0-9ё\s,`.:_-]+$/u",$columns)) {//Проверка переменной 
			//if (preg_match("#^[a-zA-Zа-яА-Я0-9\-_ `,]+$#",$str)) {
				$this->query = "SELECT ".$columns;
				return $this;
		} 
		else {
			break;
		}
	}

	function from($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-zА-Яа-я0-9ё\s,`.:_-]+$/u",$table)) {
			$this->query = $this->query." FROM $table";
			return $this;
		}
		else {
			break;
		}
	}

	function where($a, $b, $c){
		if (gettype($a)=="string" AND strlen($a) < 100 AND preg_match("/^[=><!]+$/",$b) AND gettype($c)=="integer") {
			$this->query = $this->query." WHERE $a $b :c";
			$this->prepare_query['c'] = $c;
			return $this;
		} else {
			break;
		}
	}

	function limit($num){
		if (gettype($num) == "integer")
		{
			$this->query = $this->query." LIMIT $num";
			return $this;
		}
		else { 
			break;
		}
	}

	function insert(){

	}

	function update(){

	}

	function delete(){

	}

	function save(){
		$result = $this->pdo->prepare($this->query);
		$result->execute($this->prepare_query);
		$data = $result->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

	function __destruct(){
    	$this->pdo = null;
    }
}


$config = array("dbtype"=>"mysql","login"=>"root","dbpass"=>"","dbhost"=>"192.168.1.2:3306","dbname"=>"library");

$db = new MyActiveRecord($config);
$columns = "`id`, `name`";
$table = "stas";
$a = "id";

print_r($db->select($columns)->from($table)->where($a,'>=', 1)->limit(3));


echo "<pre>";
print_r($db->save());
echo "<pre/>";

echo $db->query;

?>