<?php

class MyQueryBuilder{

	private $config = array();
	private $query = ""; // Строка запроса
	private $pdo; //Обьект подключения PDO
	private $prepare_query; //Массив для подготовки параметров запроса
	private $result; //обьект для подготовки запроса
	private $checkQuery = array();


	const EXCEPTION_1 = 'Column Name (s) is not true';//Название столбца(ов) не верно
	const EXCEPTION_2 = 'An array of speakers is not correct';//Массив с колонками не корректен
	const EXCEPTION_3 = 'The table name is not true';//Название таблицы не верно
	const EXCEPTION_4 = 'Conditions are not entered correctly';//Условие введено не верно
	const EXCEPTION_5 = 'Second variable LIMIT is not correct';//2ая переменная LIMIT не корректна
	const EXCEPTION_6 = 'First variable LIMIT is not correct';//1ая переменная LIMIT не корректна
	const EXCEPTION_7 = 'The input array of values is incorrect';//Входной массив значений некорректен
	const EXCEPTION_8 = 'Input array values UPDATE incorrect';//Входной массив значений UPDATE некорректен
	const EXCEPTION_9 = 'The parameter in the function join() is invalid';//Параметр в функции join() некорректен
	const EXCEPTION_10 = 'The parameter in the function leftJoin() is invalid';//Параметр в функции leftJoin() некорректен
	const EXCEPTION_11 = 'The parameter in the function rightJoin() is invalid';//Параметр в функции rightJoin() некорректен
	const EXCEPTION_12 = 'The parameter in the function fullOuterJoin() is invalid';//Параметр в функции fullOuterJoin() некорректен
	const EXCEPTION_13 = 'The parameter in the function crossJoin() is invalid';//Параметр в функции crossJoin() некорректен
	const EXCEPTION_14 = 'The request not formed correctly';//Запрос сформирован некорректно
	const EXCEPTION_15 = 'Failed to connect.';//Не удалось подключиться
	const EXCEPTION_16 = 'The request did not return';//Запрос ничего не вернул
	const EXCEPTION_17 = 'an error in the configuration,the connection is not established';//Ошибка в конфиге, соединение не создано

	function __construct($config){/*Соединение с БД*/
		if ($config['dbtype']=='mysql' OR $config['dbtype']=='sybase' OR $config['dbtype']=='mssql'){
			$this->pdo = new PDO("$config[dbtype]:host=$config[dbname];dbname=$config[dbname]", $config['login'], $config['dbpass']);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// СМ документацию PDO
		}	
		elseif ($config['dbtype']=='sqlite') {
			$this->pdo = new PDO("sqlite:$config[path]");
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// СМ документацию PDO
		}
		else {
			throw new Exception(self::EXCEPTION_17);
		}	
	}

	public function beginTransaction(){//Начало транзакции
		$this->pdo->beginTransaction();
	}

	public function rollBack(){//Откат транзакции
		$this->pdo->rollBack();
	}

	public function commit(){//Фиксирует транзакцию
		$this->pdo->commit();
	}

	public function select($columns){
		$this->query .= "SELECT ";
		if (gettype($columns)=="array") {//Проверка переменной 
			foreach ($columns as $key => $value) {
				if (gettype($value)=="string" AND strlen($value) < 100 AND preg_match("/^[A-Za-z0-9\s,*`.:_-]+$/",$value)) {
					$this->query .= " $value,";
				}
				else {
					throw new Exception(self::EXCEPTION_1);
				}
			}
			$this->query = trim($this->query, ',');
			$this->checkQuery[] = 1; 
			return $this;
		} 
		elseif (gettype($columns)=="string" AND strlen($columns) < 100 AND preg_match("/^[A-Za-z0-9\s,*`.:_-]+$/",$columns)) {
					$this->query .= "$columns";
					return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_2);	
		}
	}

	public function from($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {//Проверка переменной
			$this->query .= " FROM $table";
			$this->checkQuery[] = 2;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_3);
		}
	}

	public function where($a, $b, $c){
		if (gettype($a)=="string" AND strlen($a) < 100 AND preg_match("/^[=><!]+$/",$b) AND (gettype($c)=="integer" OR gettype($c)=="string")) {//Проверка переменных
			$this->query = $this->query." WHERE $a $b :c";
			$this->prepare_query['c'] = $c;
			$this->checkQuery[] = 3;
			return $this;
		} else {
			throw new Exception(self::EXCEPTION_4);
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
				throw new Exception(self::EXCEPTION_5);
			}
			$this->checkQuery[] = 4;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_6);
		}
	}

	public function insert($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {
			$this->query = "INSERT INTO $table";
			$this->checkQuery[] = 1;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_3);
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
					throw new Exception(self::EXCEPTION_1);
				}
			}
			$mas_column = trim($mas_column, ',').')';
			$mas_value = trim($mas_value, ',').')';
			$this->query .= " $mas_column $mas_value";//Итоговое склеивание запроса
			$this->checkQuery[] = 2;
			return $this; 
		} else {
			throw new Exception(self::EXCEPTION_7);
		}
	}

	public function update($table, $a=null, $b=null){
		$this->query .= "UPDATE ";
		if ($a==true){
			$this->query .= "LOW_PRIORITY ";
		}
		if ($b==true){
			$this->query .= "IGNORE ";
		}
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {//Проверка переменной
			$this->query .= "$table ";
			$this->checkQuery[] = 1;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_3);
		}
	}

	public function set($values_update){
		$this->query .= "SET";
		if (gettype($values_update)=='array') {
			foreach ($values_update as $key => $value) {
				if (strlen($key) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$key)) {
					$this->query .= " $key = :$key,";
					$this->prepare_query[$key] = $value;
				}
				else {
					throw new Exception(self::EXCEPTION_1);
				}
			}
			$this->query = trim($this->query, ',');
			$this->checkQuery[] = 2;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_8);
		}
	}

	public function join($rightTable, $leftColumnName, $rightColumnName){//Первый параметр - таблица которую присоединяем; 2-ой и 3-ий - параметры по которым сшиваем таблицы
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " INNER JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_9);
		}
	}

	public function leftJoin($rightTable, $leftColumnName, $rightColumnName){//Первый параметр - таблица которую присоединяем; 2-ой и 3-ий - параметры по которым сшиваем таблицы
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " LEFT JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_10);
		}
	}

	public function rightJoin($rightTable, $leftColumnName, $rightColumnName){//Первый параметр - таблица которую присоединяем; 2-ой и 3-ий - параметры по которым сшиваем таблицы
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " RIGHT JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_11);
		}
	}

	public function fullOuterJoin($rightTable, $leftColumnName, $rightColumnName){//Первый параметр - таблица которую присоединяем; 2-ой и 3-ий - параметры по которым сшиваем таблицы
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " FULL OUTER JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_12);
		}
	}
 
	public function crossJoin($rightTable){
		if (gettype($rightTable)=="string" AND strlen($rightTable) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$rightTable)){
				$this->query .= " CROSS JOIN $rightTable";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_13);
		}
	}

	private function checkVariable($rightTable, $leftColumnName, $rightColumnName){//Функция для проверки входных данных функций: join(), leftjoin() и тд. =)
		if ((gettype($rightTable)=="string" AND strlen($rightTable) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$rightTable)) AND 
			(gettype($leftColumnName)=="string" AND strlen($leftColumnName) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$leftColumnName)) AND 
			(gettype($rightColumnName)=="string" AND strlen($rightColumnName) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$rightColumnName))) {
				return true;
		}
		else {
			return false;
		}
	}

	private function checkQuery(){
		$bool = false;
		$lenght = count($this->checkQuery);
		if ($lenght < 2) { return false; }

		for ($i=1; $i < $lenght; $i++) { 
			if ($this->checkQuery[$i] > $this->checkQuery[$i-1]){
				$bool = true;
			}
			else {
				$bool = false;
				break;
			}
		}
		if($bool == true) {
			return true;
		}
		else {
			return false;
		}
	}

	public function raw($a){
		if (gettype($a)=='string') {
			$this->query .="$a";
			return $this;
		}
		else {
			throw new Exception("Параметр запроса некорректен", 1);
		}
	}

	public function delete(){
		$this->query .= "DELETE ";
		$this->checkQuery[] = 1;
		return $this;
	}

	public function execute(){ //Выполняет запрос
		if ($this->checkQuery() == true){
			$this->result = $this->pdo->prepare($this->query);
			$this->result->execute($this->prepare_query);
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_14);
		}
	}

	public function fetchAll(){ // Возвращает массив
		if ($this->result != null){
			return $this->result->fetchAll(PDO::FETCH_ASSOC);
		} 
		else {
			throw new Exception(self::EXCEPTION_16);
		}
	}

	function __destruct(){
    	$this->pdo = null;
    }
}
?>