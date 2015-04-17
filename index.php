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
		//$this->query = "SELECT ".$columns;
		$this->query = "SELECT $columns";
		//$this->prepare_query["columns"] = $columns;
		return $this;
	}

	function from($table){
		//$this->query = $this->query." FROM ".$table;
		$this->query = $this->query." FROM $table";
		//$this->prepare_query["table"] = $table;
		return $this;
	}

	function where($a, $b, $c){
		//$this->query = $this->query." WHERE ".$a." ".$b." ".$c;
		$this->query = $this->query." WHERE $a $b :c";
		//$this->prepare_query = "a"=>"$a","b"=>"$b","c"=>"$c";
		//$this->prepare_query['a'] = $a;
		//$this->prepare_query["b"] = $b;
		$this->prepare_query['c'] = $c;
		return $this;
	}

	function limit($num){
		//$this->query = $this->query." limit ".$num;
		$this->query = $this->query." LIMIT $num";
		//$this->prepare_query['num'] = $num;
		return $this->prepare_query;
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
$columns = "id, name";
$table = "stas";
$a = "id";

print_r($db->select($columns)->from($table)->where($a,'>', 2)->limit(2));


echo "<pre>";
print_r($db->save());
echo "<pre/>";

echo $db->query;

/*$db->select($columns)->from($table)->where($a,'>',1)->limit(1);
$db->save();*/

?>''