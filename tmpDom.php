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

	/**
	 * @param $url
	 * @param $selectors_category
	 * @param $db
	 */
	function getCategoryAndProductLinks($url, $selectors_category, $db)
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

					$db->query("INSERT ignore INTO product (url_product) VALUES ('{$url_product}')");
					echo $url_product . '<br>' . PHP_EOL;
				}


			}
		}
	}

	/**
	 * @param $url_product
	 * @param $db
	 */
	function getProduct($url_product, $db)
	{

		//Get page
		$html = file_get_html($url_product);
		// get h1
		$h1_selectors_product = "div[class=product__data] h1[class=product__data__name]";
		$h1 = $html->find($h1_selectors_product, 0)->plaintext;
		//$db->query("UPDATE product SET h1='{$db->escape($h1)}' WHERE url_product = '{$db->escape($url_product)}'");

		//get content
		$content_selector_product = "div[class=product__desc__content] p";
//		$content_pl = $html->find($content_selector_product, 0)
//			. $html->find($content_selector_product, 1)
//			. $html->find($content_selector_product, 2);
		$content_pl = '';
		$html->find('div[class=alior_payment]')->innertext = '';
		foreach ($html->find($content_selector_product) as $content) {
			$content_pl .= $content;
		}
		//$db->query("UPDATE product SET content_pl='{$db->escape($content_pl)}' WHERE url_product = '{$db->escape($url_product)}'");

		//get img
		/*		$img_selector_product = "div[class='product__images__thumbs js--product-slider-nav'] img";
				$imgs = $html->find($img_selector_product);
				foreach($imgs as $img) {
					var_dump($img);
					echo $img->src . '<br>';
		}*/
		//var_dump($content_pl);

		echo $content_pl;
	}


	//URL for page
	$url = 'http://www.hendi.pl/site_map';
	$selector_category = "div[class='content page'] ul li ul li.category span a";

	$url_product = "https://www.hendi.pl/obrobka-mechaniczna/nadziewarka-do-kielbas-profi-line-pionowa-7-litrow-kod-282090.html";
	getProduct($url_product, $db);
