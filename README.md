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
<p>Способы подключения к разным СУБД могут незначительно отличаться. Ниже приведены примеры подключения к наиболее популярным из них. Можно заметить, что первые три имеют идентичный синтаксис, в отличие от SQLite.</p>
<pre>
	try {  
	  # MS SQL Server и Sybase через PDO_DBLIB  
	  $DBH = new PDO("mssql:host=$host;dbname=$dbname", $user, $pass);  
	  $DBH = new PDO("sybase:host=$host;dbname=$dbname", $user, $pass);  
	  
	  # MySQL через PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);  
	  
	  # SQLite  
	  $DBH = new PDO("sqlite:my/database/path/database.db");  
}  
catch(PDOException $e) {  
    echo $e->getMessage();  
}
</pre>