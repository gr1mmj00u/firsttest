<h1>Некое подобие Query Builder :3</h1>
<p>Конструктор запросов предоставляет удобный, выразительный интерфейс для создания и выполнения запросов к базе данных. Он может использоваться для выполнения большинства типов операций и работает со всеми поддерживаемыми СУБД.<br/>
Этот конструктор запросов использует привязку параметров к запросам средствами PDO для защиты вашего приложения от SQL-инъекций. Нет необходимости экранировать строки перед их передачей в запрос.
</p>
<h2>Поддержка СУБД</h2>
<ul>
	<li>PDO_CUBRID ( CUBRID )</li>
	<li>PDO_DBLIB ( FreeTDS / Microsoft SQL Server / Sybase )</li>
	<li>PDO_FIREBIRD ( Firebird/Interbase 6 )</li>
	<li>PDO_IBM ( IBM DB2 )</li>
	<li>PDO_INFORMIX ( IBM Informix Dynamic Server )</li>
	<li>PDO_MYSQL ( MySQL 3.x/4.x/5.x )</li>
	<li>PDO_OCI ( Oracle Call Interface )</li>
	<li>PDO_ODBC ( ODBC v3 (IBM DB2, unixODBC and win32 ODBC) )</li>
	<li>PDO_PGSQL ( PostgreSQL )</li>
	<li>PDO_SQLITE ( SQLite 3 and SQLite 2 )</li>
	<li>PDO_SQLSRV ( Microsoft SQL Server )</li>
	<li>PDO_4D ( 4D )</li>
</ul>
<h2>Подключение</h2>
<p>Подключение происходит после инициализации обьекта конструктора. Способы подключения к разным СУБД могут незначительно отличаться, поэтому при создании обьекта на вход подаются разные массивы. Для подключения MS SQL Server, Sybase, MySQL используется:</p>
<pre>
	$config = array(
	"dbtype"=>"mysql",
	"login"=>"root",
	"dbpass"=>"",
	"dbhost"=>"192.168.1.2:3306",
	"dbname"=>"library",
	);
</pre>
<p>А для SQLite</p>
<pre>
	$config = array(
	"dbtype"=>"sqlite",
	"path"=>"/domain/bd.sqlite",
	);
</pre>
<p>Пример создания экземпляра конструктора:</p>
<pre>
	$db = new MyQueryBuilder($config);
</pre>
<h2>Выборка (SELECT)</h2>
<h3>function select()</h3>
<p>При использовании метода select(), на вход подаётся массив полей таблицы которые мы хотим увидеть или строка.</p>
<pre>
	$columns = array('surname','name');
	$db->select($columns);
	SELECT surname, name

	$columns = '*';
	$db->select($columns);
	SELECT *
</pre>
<h3>function from()</h3>
<p>При использовании метода from(), на вход подаётся строка с названием таблицы.</p>
<pre>
	$columns = array('surname','name');
	$table = 'client';

	$db->select($columns)->from($table)
	SELECT surname, name FROM client
</pre>
<h3>function where()</h3>
<p>При использовании метода where(), на вход подаётся три параметра. Первый параметр - строка, второй параметр - условие состоящие только из (=><!), третий параметр - строка или число  </p>
<pre>
	$columns = array('surname','name');
	$table = 'client';
	$a = 'id';

	$db->select($columns)->from($table)->where($a, '=', 10);
	SELECT surname, name FROM client WHERE id = 10

	$db->select($columns)->from($table)->where($a, '=', 'Djon');
	SELECT surname, name FROM client WHERE id = Djon
</pre>
<h3>function limit()</h3>
<pre>
	$columns = array('surname','name');
	$table = 'client';
	$a = 'id';

	$db->select($columns)->from($table)->where($a, '=', 10)->limit(1);
	SELECT surname, name FROM client WHERE id = 10 LIMIT 1

	$db->select($columns)->from($table)->where($a, '=', 'Djon')->limit(1,6);
	SELECT surname, name FROM client WHERE id = Djon LIMIT 1,6
</pre>
<h2>Вставка (INSERT)</h2>
<pre>
	$a = 'id';
	$table = 'client';
	$values_query = array(
		"name"=>"nick",
		"surname"=>"travolta"
		);

	$db->insert($table)->values($values_query);
	INSERT INTO stas (`name`,`surname`) VALUES ('nick','travolta')
</pre>
<h2>Удаление (DELETE)</h2>
<pre>
	$table = 'client';
	$a = 'id';
	$db->delete()->from($table)->where($a, '>=', 15);
	DELETE  FROM stas WHERE id >= 15
</pre>
<h2>Обновление (UPDATE)</h2>
<pre>
	$a = "id";
	$table = 'client';
	$low_priority = false;
	$ignore = false;
	$values_update = array(
		"name"=>"hugo",
		"surname"=>"boss"
		);

	$db->update($table, $low_priority, $ignore)->set($values_update)->where($a, '>=', 80);
	UPDATE client SET name = hugo, surname = boss WHERE id >= 80

	$low_priority = true;
	$ignore = true;
	$db->update($table, $low_priority, $ignore)->set($values_update)->where($a, '>=', 80);
	UPDATE LOW_PRIORITY IGNORE client SET name = hugo, surname = boss WHERE id >= 80
</pre>
<h2>Объединения (JOIN)</h2>
<pre>
	$columns = array('table1.surname','table1.name','table2.mail'); //Если стобцы называются одинаково необходимо указывать имя таблицы!

	$db->select($columns)->from($table)->join('contacts', 'table1.id', 'table2.id');
	$db->select($columns)->from($table)->rightJoin('contacts', 'table1.id', 'table2.id'); 
	$db->select($columns)->from($table)->fullOuterJoin('contacts', 'table1.id', 'table2.id');//В MySql не работает
	$db->select($columns)->from($table)->crossJoin('contacts'); //Один параметр, таблица которую объединяем
	
	$db->select($columns)->from($table)->leftJoin('contacts', 'table1.id', 'table2.id');
	SELECT table1.surname, table1.name, table2.mail FROM client LEFT JOIN contacts  ON table1.id = table2.id

</pre>
<h2>Сырые выражения</h2>
<p>Иногда вам может быть нужно использовать уже готовое SQL-выражение в вашем запросе. Такие выражения вставляются в запрос напрямую в виде строк, поэтому будьте внимательны и не создавайте возможных точек для SQL-инъекций. Для создания сырого выражения используется метод db->raw()</p>
<pre>
	$db->raw('SELECT Name
				FROM Production.Product
				WHERE ListPrice >
    				(SELECT MIN (ListPrice)
     				FROM Production.Product
     				GROUP BY ProductSubcategoryID
     				HAVING ProductSubcategoryID = 14)');
</pre>
<h2>Методы execute() и fetchAll()</h2>
<p>Метод execute запускает подготовленный запрос на выполнение</p>
<p>Метод fetchAll возвращает массив данных выполненного запроса(работает только после метода execute).</p>
<pre>
	$db->select($columns)->from($table)->crossJoin('contacts');
	print_r($db->execute()->fetchAll());
</pre>
<h2>Транзакции и автоматическая фиксация изменений</h2>
<p>Если база данных не поддерживает механизм транзакций, запрос обрабатывается без транзакции. Чтобы явно обозначить начало транзакции, вы должны использовать метод beginTransaction(). Однако, в этом случае, если драйвер не поддерживает механизм транзакций, будет выброшено исключение PDOException. Будучи внутри границ транзакции, ее можно зафиксировать методом commit() или откатить методом rollBack(), в зависимости от того, успешно выполнен ваш код внутри транзакции или нет.</p>
<p>Пример работы конструктора и использованием транзакций</p>
<pre>
	try {
	$config = array("dbtype"=>"mysql",
					"login"=>"root",
					"dbpass"=>"",
					"dbhost"=>"192.168.1.2:3306",
					"dbname"=>"library",
					);
	$columns = array('surname','name');
	$table = "client";
	$a = "id";

	$db = new MyQueryBuilder($config);

	$db->beginTransaction();

	db->select($columns)->from($table)->where($a,'=', 5);

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
</pre>