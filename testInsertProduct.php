<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 27.02.2019
	 * Time: 0:11
	 */
//	require_once __DIR__ . '/config/db_config.php';
	require_once 'lib/simple_html_dom.php';
//	require_once 'src/DbConnectMysql.php';

//	$db1 = new DbConnectMysql(HOST, USER, PASS, DB_NAME);
	/**
	 * @param $url_product
	 * @param DbConnectMysql $db
	 * @return array
	 */


	$url_product = "https://www.hendi.pl/obrobka-mechaniczna/nadziewarka-do-kielbas-profi-line-pionowa-7-litrow-kod-282090.html";

	$html = file_get_html($url_product);
	$selectors_product = "div[class=product__data] h1[class=product__data__name]";
	$h1 = $html->find($selectors_product, 0)->plaintext;
	var_dump($res);


