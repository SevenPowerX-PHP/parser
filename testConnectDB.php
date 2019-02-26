<?php /** @noinspection PhpStatementHasEmptyBodyInspection */
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 26.02.2019
	 * Time: 21:10
	 */

	require_once __DIR__ . '/config/db_config.php';

	//Подключаем библиотеку
	require_once 'lib/simple_html_dom.php';
	require_once 'src/DbConnectMysql.php';

	/*$db = new DbConnectMysql(HOST, USER, PASS, DB_NAME);
	$showTable = 'SELECT * FROM product';
	var_dump($db->query('show tables'));*/
	$mysqli = new mysqli("localhost", "root", "", "parser");

	/* проверка соединения */
	if ($mysqli->connect_errno) {
		printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
		//exit();
	} else {
		printf("OK! удалось подключиться: %s\n", $mysqli->connect_error);
	}
	if ($result = $mysqli->query("SELECT * FROM workers LIMIT 10")) {

		printf("Select вернул %d строк.\n", $result->num_rows);
		for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row) ;
		var_dump($data);
		/* очищаем результирующий набор */
		$result->close();
	}

	$mysqli->query("INSERT INTO workers (name, age, salary) VALUES ('test', 77, 19999)");
	die('<br>Стоп!!!');
