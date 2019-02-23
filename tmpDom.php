<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 22.02.2019
	 * Time: 12:54
	 */

//Подключаем библиотеку
	require_once 'lib/simple_html_dom.php';

//URL for page
	$url = 'http://www.hendi.pl/site_map';


	function getCategoryLinks($url)
	{
		//Get page
		$html = file_get_html($url);
//		$html->find("li[id=category_685]");

		foreach ($html->find("div ul li.category") as $link_to_category) {
			echo $link_to_category . '<br>' . PHP_EOL;
		}


	}

	getCategoryLinks($url);


	// Без Html тегов
	//print_r($html->plaintext);


	// C Html тегами
	//	print_r($html->innertext);