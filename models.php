<?php

class MyQueryBuilder{

	private $config = array();
	private $query = ""; // Строка запроса
	private $pdo; //Обьект подключения PDO
	private $prepare_query; //Массив для подготовки параметров запроса
	private $result; //обьект для подготовки запроса
	private $checkQuery = array();//Вспомогательный массив для проверки корректности построения запроса


	const EXCEPTION_COLUMN_NAME_INCORRECT = 'Column Name (s) is not true';//Название столбца(ов) не верно
	const EXCEPTION_COLUMN_ARRAY_INCORRECT = 'An array of speakers is not correct';//Массив с колонками не корректен
	const EXCEPTION_TABLE_NAME_INCORRECT = 'The table name is not true';//Название таблицы не верно
	const EXCEPTION_CONDITION_INCORRECT = 'Conditions are not entered correctly';//Условие введено не верно
	const EXCEPTION_SECOND_VARIABLE_LIMIT_INCORRECT = 'Second variable LIMIT is not correct';//2ая переменная LIMIT не корректна
	const EXCEPTION_FIRST_VARIABLE_LIMIT_INCORRECT = 'First variable LIMIT is not correct';//1ая переменная LIMIT не корректна
	const EXCEPTION_VALUES_ARRAY_INCORRECT = 'The input array of values is incorrect';//Входной массив значений некорректен
	const EXCEPTION_ARRAY_UPDATE_VALUES_INCORRECT = 'Input array values UPDATE incorrect';//Входной массив значений UPDATE некорректен
	const EXCEPTION_PARAMETER_JOIN_INVALID = 'The parameter in the function join() is invalid';//Параметр в функции join() некорректен
	const EXCEPTION_PARAMETER_LEFTJOIN_INVALID = 'The parameter in the function leftJoin() is invalid';//Параметр в функции leftJoin() некорректен
	const EXCEPTION_PARAMETER_RIGHTJOIN_INVALID = 'The parameter in the function rightJoin() is invalid';//Параметр в функции rightJoin() некорректен
	const EXCEPTION_PARAMETER_FULLOUTERJOIN_INVALID = 'The parameter in the function fullOuterJoin() is invalid';//Параметр в функции fullOuterJoin() некорректен
	const EXCEPTION_PARAMETER_CROSSJOIN_INVALID = 'The parameter in the function crossJoin() is invalid';//Параметр в функции crossJoin() некорректен
	const EXCEPTION_REQUEST_INCORRECT = 'The request not formed correctly';//Запрос сформирован некорректно
	const EXCEPTION_CONNECT_ERROR = 'Failed to connect.';//Не удалось подключиться 
	const EXCEPTION_REQUEST_IS_NULL = 'The request did not return';//Запрос ничего не вернул


	/**
	*Метод создания подключения к бд
	*@param array $config
	*/
	public function __construct($config){/*Соединение с БД*/
		if ($config['dbtype']=='mysql' OR $config['dbtype']=='sybase' OR $config['dbtype']=='mssql'){
			$this->pdo = new PDO("$config[dbtype]:host=$config[dbname];dbname=$config[dbname]", $config['login'], $config['dbpass']);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// СМ документацию PDO
		}	
		elseif ($config['dbtype']=='sqlite') {
			$this->pdo = new PDO("sqlite:$config[path]");
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// СМ документацию PDO
		}
		else {
			throw new Exception(self::EXCEPTION_CONNECT_ERROR);
		}	
	}

	/**
	*Метод создания начала транзакции
	*/
	public function beginTransaction(){
		$this->pdo->beginTransaction();
	}

	/**
	*Откат транзакции
	*/
	public function rollBack(){
		$this->pdo->rollBack();
	}

	/**
	*Фиксирует транзакцию
	*/
	public function commit(){
		$this->pdo->commit();
	}

	/**
	*Метод формирует запрос типа "SELECT".В ходе работы метода проверяется входной параметр на строку или массив. Затем подставляет названия
	*колонок (на выборку) в строку запроса.
	*@param array or string $columns 
	*@return $this
	*/
	public function select($columns){
		$this->query .= "SELECT ";
		if (gettype($columns)=="array") {//Проверка переменной 
			foreach ($columns as $key => $value) {
				if (gettype($value)=="string" AND strlen($value) < 100 AND preg_match("/^[A-Za-z0-9\s,*`.:_-]+$/",$value)) {
					$this->query .= " $value,";
				}
				else {
					throw new Exception(self::EXCEPTION_COLUMN_NAME_INCORRECT);
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
			throw new Exception(self::EXCEPTION_COLUMN_ARRAY_INCORRECT);	
		}
	}

	/**
	*Метод продолжает формирование запроса. В ходе работы метод проверяет и подставляет название таблицы в запрос.
	*@param string $table
	*@return $this
	*/
	public function from($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {//Проверка переменной
			$this->query .= " FROM $table";
			$this->checkQuery[] = 2;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_TABLE_NAME_INCORRECT);
		}
	}

	/**
	*Метод продолжает формирование запроса. В ходе которого проверяются все входные параметры условия, а затем подставляется в строку запроса.
	*@param string $a
	*@param string $b
	*@param string or integer $c
	*@return $this
	*/
	public function where($a, $b, $c){
		if (gettype($a)=="string" AND strlen($a) < 100 AND strlen($b) < 4 AND preg_match("/^[=><!]+$/",$b) AND (gettype($c)=="integer" OR gettype($c)=="string")) {//Проверка переменных
			$this->query = $this->query." WHERE $a $b :c";
			$this->prepare_query['c'] = $c;
			$this->checkQuery[] = 3;
			return $this;
		} else {
			throw new Exception(self::EXCEPTION_CONDITION_INCORRECT);
		}
	}

	/**
	*Метод продолжает формирование запроса. Метод подставляет значение лимита в строку запроса.
	*@param integer $a
	*@param integer $b
	*@return $this
	*/
	public function limit($a, $b=null){
		$this->query .= " LIMIT ";
		if(gettype($a)=='integer' AND $a >= 0){
			$this->query .="$a"; 
			if (gettype($b)=='integer' AND $a > 0) {
				$this->query .=",$b"; 
			}
			else {
				throw new Exception(self::EXCEPTION_SECOND_VARIABLE_LIMIT_INCORRECT);
			}
			$this->checkQuery[] = 4;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_FIRST_VARIABLE_LIMIT_INCORRECT);
		}
	}

	/**
	*Метод начинает формирование запроса. Метод вставляет название таблицы аналогично методу from().
	*@param string $table
	*@return $this
	*/
	public function insert($table){
		if (gettype($table)=="string" AND strlen($table) < 100 AND preg_match("/^[A-Za-z0-9,`.:_-]+$/",$table)) {
			$this->query = "INSERT INTO $table";
			$this->checkQuery[] = 1;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_TABLE_NAME_INCORRECT);
		}
	}

	/**
	*Метод продолжает формирование запроса. В ходе которого в строку запроса подставляются ключи значений, а сами значения записываются prepare_query для экранирования в ходе отправки запроса
	*@param array $values_query
	*@return $this
	*/
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
					throw new Exception(self::EXCEPTION_COLUMN_NAME_INCORRECT);
				}
			}
			$mas_column = trim($mas_column, ',').')';
			$mas_value = trim($mas_value, ',').')';
			$this->query .= " $mas_column $mas_value";//Итоговое склеивание запроса
			$this->checkQuery[] = 2;
			return $this; 
		} else {
			throw new Exception(self::EXCEPTION_VALUES_ARRAY_INCORRECT);
		}
	}

	/**
	*Метод начинает формирование запроса. Метод вставляет название таблицы и ключи, аналогично методу from().
	*@param array $config
	*@param boolean $a
	*@param boolean $b
	*@return $this
	*/
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
			throw new Exception(self::EXCEPTION_TABLE_NAME_INCORRECT);
		}
	}

	/**
	*Метод продолжает формирование запроса UPDATE, подставляя значения в строку запроса.
	*@param array $values_update
	*@return $this
	*/
	public function set($values_update){
		$this->query .= "SET";
		if (gettype($values_update)=='array') {
			foreach ($values_update as $key => $value) {
				if (strlen($key) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$key)) {
					$this->query .= " $key = :$key,";
					$this->prepare_query[$key] = $value;
				}
				else {
					throw new Exception(self::EXCEPTION_COLUMN_NAME_INCORRECT);
				}
			}
			$this->query = trim($this->query, ',');
			$this->checkQuery[] = 2;
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_ARRAY_UPDATE_VALUES_INCORRECT);
		}
	}

	/**
	*Метод продолжает формирования запроса типа JOIN. $rightTable - таблица которую присоединяем; $leftColumnName и $rightColumnName - параметры по которым сшиваем таблицы
	*@param string $rightTable
	*@param string $leftColumnName
	*@param string $rightColumnName
	*@return $this
	*/
	public function join($rightTable, $leftColumnName, $rightColumnName){
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " INNER JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_PARAMETER_JOIN_INVALID);
		}
	}

	/**
	*Метод продолжает формирования запроса типа JOIN. $rightTable - таблица которую присоединяем; $leftColumnName и $rightColumnName - параметры по которым сшиваем таблицы
	*@param string $rightTable
	*@param string $leftColumnName
	*@param string $rightColumnName
	*@return $this
	*/
	public function leftJoin($rightTable, $leftColumnName, $rightColumnName){
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " LEFT JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_PARAMETER_LEFTJOIN_INVALID);
		}
	}

	/**
	*Метод продолжает формирования запроса типа JOIN. $rightTable - таблица которую присоединяем; $leftColumnName и $rightColumnName - параметры по которым сшиваем таблицы
	*@param string $rightTable
	*@param string $leftColumnName
	*@param string $rightColumnName
	*@return $this
	*/
	public function rightJoin($rightTable, $leftColumnName, $rightColumnName){
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " RIGHT JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_PARAMETER_RIGHTJOIN_INVALID);
		}
	}

	/**
	*Метод продолжает формирования запроса типа JOIN. $rightTable - таблица которую присоединяем; $leftColumnName и $rightColumnName - параметры по которым сшиваем таблицы
	*@param string $rightTable
	*@param string $leftColumnName
	*@param string $rightColumnName
	*@return $this
	*/
	public function fullOuterJoin($rightTable, $leftColumnName, $rightColumnName){
		if ($this->checkVariable($rightTable, $leftColumnName, $rightColumnName) == true) {
				$this->query .= " FULL OUTER JOIN $rightTable  ON $leftColumnName = $rightColumnName";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_PARAMETER_FULLOUTERJOIN_INVALID);
		}
	}
 	
 	/**
	*Метод продолжает формирования запроса типа JOIN. $rightTable - таблица которую присоединяем;
	*@param string $rightTable
	*@return $this
	*/
	public function crossJoin($rightTable){
		if (gettype($rightTable)=="string" AND strlen($rightTable) < 100 AND preg_match("/^[A-Za-z0-9\s,`.:_-]+$/",$rightTable)){
				$this->query .= " CROSS JOIN $rightTable";
				$this->checkQuery[] = 3;
				return $this;
		} 
		else {
			throw new Exception(self::EXCEPTION_PARAMETER_CROSSJOIN_INVALID);
		}
	}

	/**
	*Метод проверки входных параметров для всех функций: join(), leftJoin(), rightJoin(), fullOuterJoin().
	*@param string $rightTable
	*@param string $leftColumnName
	*@param string $rightColumnName
	*@return boolean true or false
	*/
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

	/**
	*Метод проверки корректности построения запроса.(Находится в разработке)
	*@param array $config
	*@return boolean true or false
	*/
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
		if($bool) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	*Метод создания запроса. Входной параметр строка (как правило сложный запрос).
	*@param string $a
	*@return $this
	*/
	public function raw($a){
		if (gettype($a)=='string') {
			$this->query .="$a";
			return $this;
		}
		else {
			throw new Exception("Параметр запроса некорректен", 1);
		}
	}

	/**
	*Метод начала создания запроса типа DELETE.
	*@return $this
	*/
	public function delete(){
		$this->query .= "DELETE ";
		$this->checkQuery[] = 1;
		return $this;
	}

	/**
	*Метод сначало проверяет правильность построения запроса, а затем выполняет этот запрос.
	*@return $this
	*/
	public function execute(){ //Выполняет запрос
		if ($this->checkQuery() == true){
			$this->result = $this->pdo->prepare($this->query);
			$this->result->execute($this->prepare_query);
			return $this;
		}
		else {
			throw new Exception(self::EXCEPTION_REQUEST_INCORRECT);
		}
	}

	/**
	*Метод возвращает результат запроса в массиве
	*@return array
	*/
	public function fetchAll(){ // Возвращает массив
		if ($this->result != null){
			return $this->result->fetchAll(PDO::FETCH_ASSOC);
		} 
		else {
			throw new Exception(self::EXCEPTION_REQUEST_IS_NULL);
		}
	}

	/**
	*Метод закрывает подключение к бд
	*/
	public function __destruct(){
    	$this->pdo = null;
    }
}
?>