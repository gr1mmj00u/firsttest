<?php

class MyActiveRecord{

	private $config = array();
	private $query = ""; // Строка запроса
	private $pdo; //Обьект подключения PDO
	private $prepare_query; //Массив для подготовки параметров запроса
	private $result; //обьект для подготовки запроса

	private function __construct($config){/*Соединение с БД*/
		$this->config = $config;
		$this->pdo = new PDO("$config[dbtype]:host=$config[dbname];dbname=$config[dbname]", $config['login'], $config['dbpass']);
	}

	public function select($columns){
		$this->query .= "SELECT ";
		if (gettype($columns)=="array") {//Проверка переменной 
			foreach ($columns as $key => $value) {
				if (gettype($value)=="string" AND strlen($value) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$value)) {
					$this->query .= "`$value`,";
				}
				else {
					throw new Exception("Название колонок не верно", 1);
				}
			}
			$this->query = trim($this->query, ',');
			return $this;
		} 
		else {
			throw new Exception("Массив с колонками не корректен", 1);	
		}
	}

	public function from($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {//Проверка переменной
			$this->query .= " FROM $table";
			return $this;
		}
		else {
			throw new Exception("Название таблиц не верно", 1);
		}
	}

	public function where($a, $b, $c){
		if (gettype($a)=="string" AND strlen($a) < 100 AND preg_match("/^[=><!]+$/",$b) AND (gettype($c)=="integer" OR gettype($c)=="string")) {//Проверка переменных
			$this->query = $this->query." WHERE $a $b :c";
			$this->prepare_query['c'] = $c;
			return $this;
		} else {
			throw new Exception("Условие введено не верно", 1);
		}
	}

	public function limit($a, $b=null){
		$this->query .= " LIMIT ";
		if(gettype($a)=='integer' AND $a >= 0){
			$this->query .="$a"; 
			if (gettype($b)=='integer' AND $a > 0) {
				$this->query .=",$b"; 
			}
			else {
				throw new Exception("2ая переменная LIMIT не корректна", 1);
			}
			return $this;
		}
		else {
			throw new Exception("1ая переменная LIMIT не корректна", 1);
		}
	}

	public function insert($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {
			$this->query = "INSERT INTO `$table`";
			return $this;
		}
		else {
			throw new Exception("Название таблицы не верно", 1);
		}
	}

	public function values($values_query){
		$mas_column = "("; //Дополнительный массив для названий колонок
		$mas_value = "VALUES ("; //Дополнительный массив для значений запроса
		if (gettype($values_query)=="array") { //Проверка входной переменной
			foreach ($values_query as $key => $value) {
				if (strlen($key) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$key)) {//Проверка названий колонок
					$mas_column .= "`$key`,";
					$mas_value .= ":$key,";
					$this->prepare_query[$key] = $value;
				}
				else {
					throw new Exception("Название столбца(ов) не верно", 1);
				}
			}
			$mas_column = trim($mas_column, ',').')';
			$mas_value = trim($mas_value, ',').')';
			$this->query .= " $mas_column $mas_value";//Итоговое склеивание запроса
			return $this; 
		} else {
			throw new Exception("Входной массив значений некорректен", 1);
		}
	}

	public public function update($table, $a=null, $b=null){
		$this->query .= "UPDATE ";
		if ($a==true){
			$this->query .= "LOW_PRIORITY ";
		}
		if ($b==true){
			$this->query .= "IGNORE ";
		}
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {//Проверка переменной
			$this->query .= "`$table` ";
			return $this;
		}
		else {
			throw new Exception("Название таблиц не верно", 1);
		}
	}

	public function set($values_update){
		$this->query .= "SET";
		if (gettype($values_update)=='array') {
			foreach ($values_update as $key => $value) {
				if (strlen($key) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$key)) {
					$this->query .= " `$key` = :$key,";
					$this->prepare_query[$key] = $value;
				}
				else {
					throw new Exception("Название колонки неверно", 1);
				}
			}
			$this->query = trim($this->query, ',');
			return $this;
		}
		else {
			throw new Exception("Входной массив значений UPDATE некорректен", 1);
		}
	}

	public function delete(){
		$this->query .= "DELETE ";
		return $this;
	}

	public function execute(){ //Выполняет запрос
		$this->result = $this->pdo->prepare($this->query);
		$this->result->execute($this->prepare_query);
		return $this;
	}

	public function fetchAll(){ // Возвращает массив
		return $this->result->fetchAll(PDO::FETCH_ASSOC);
	}

	private function __destruct(){
    	$this->pdo = null;
    }
}
?>