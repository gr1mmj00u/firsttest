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

<h2>function select()</h2>
<p>При использовании метода select(), на вход подаётся массив полей таблицы которые мы хотим увидеть или строка.</p>
<pre>
	$columns = array('surname','name');
	$db->select($columns);
	SELECT surname, name

	$columns = '*';
	$db->select($columns);
	SELECT *
</pre>
<h2>function from()</h2>
<p>При использовании метода from(), на вход подаётся строка с названием таблицы.</p>
<pre>
	$columns = array('surname','name');
	$table = 'client';

	$db->select($columns)->from($table)
	SELECT surname, name FROM client
</pre>
<h2>function where()</h2>
<p>При использовании метода where(), на вход подаётся три параметра. Первый параметр - строка, второй параметр - условие состоящие только из (=><!), третий параметр - строка или число  </p>
<pre>
	$a = 'id'
	$db->select($columns)->from($table)->where($a, '=', 10)
	SELECT surname, name FROM client WHERE id = 10
</pre>
