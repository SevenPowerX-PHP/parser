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
		//	$html->find("li[id=category_685]");

		foreach ($html->find("div[class='content page'] ul li.category span a") as $link_to_category) {

			$val_links = mb_strtolower($link_to_category->innertext);
			$val_links = str_replace(' ', '-', $val_links);
			$val_links .= '.html';
			if ($val_links == $link_to_category->href) {
				$status = 'Yes';
			} else
				$status = 'No';
			//	echo PHP_EOL . '<br> Link '.$status.':' . $val_links .'<br>' . PHP_EOL;
			echo PHP_EOL . '<br> Link ' . $link_to_category->href . ':' . $val_links . '<br>' . PHP_EOL;


			if (isset($link_to_category->href))
				$value = $link_to_category->href = 'http://www.hendi.pl' . $link_to_category->href;


			/** @var string $value */
			//var_dump($value);

			//echo $link_to_category . '<br>' . PHP_EOL;
			//echo $link_to_category . '<br>' . PHP_EOL;
			//var_dump($link_to_category);

			getCategoryLinks($value);

		}
	}

	getCategoryLinks($url);
