<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 22.02.2019
	 * Time: 12:54
	 */

	require_once __DIR__ . '/config/db_config.php';

	//Подключаем библиотеку
	require_once 'lib/simple_html_dom.php';
	require_once 'src/DbConnectMysql.php';
//	global $db;

	$db = new DbConnectMysql(HOST, USER, PASS, DB_NAME);
//	$db->escape();
//	$showTable = 'show tables';
//	var_dump($db->query($showTable));
//	var_dump($db->query('SELECT * FROM product'));
//
//	$res = $db->query("INSERT INTO product (url_product) VALUES ('test2.999')");
//	var_dump($res);
//	$url_product = 'http://www.hendi.pl';
//					$db->escape($url_product);
//	$db->query("INSERT INTO product (url_product) VALUES ('{$url_product}')");
//	die('Стоп!!!');

	/**
	 * @param $url
	 * @param $selectors_category
	 * @param $db
	 */
	function getCategoryLinks($url, $selectors_category, $db)
	{

		//Get page
		$html = file_get_html($url);

		foreach ($html->find($selectors_category) as $link_to_category) {

			if (isset($link_to_category->href)) {
				$url_category = $link_to_category->href = 'http://www.hendi.pl' . $link_to_category->href;

				//	echo $url_category . '<br>';
			}

			/** @var string $url_category */
			$html = file_get_html($url_category);
			$selectors_product = "ul.products_list li a";
			foreach ($html->find($selectors_product) as $link_to_product) {

				if (isset($link_to_product->href)) {
					$url_product = $link_to_product->href = 'http://www.hendi.pl' . $link_to_product->href;
					$url_product = $db->escape($url_product);

					$db->query("INSERT INTO product (url_product) VALUES ('{$url_product}')");
					echo $url_product . '<br>' . PHP_EOL;
				}


			}
		}
	}


	//URL for page
	$url = 'http://www.hendi.pl/site_map';
	$selector_category = "div[class='content page'] ul li ul li.category span a";
	getCategoryLinks($url, $selector_category, $db);
