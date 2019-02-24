<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 22.02.2019
	 * Time: 12:54
	 */

//Подключаем библиотеку
	require_once 'lib/simple_html_dom.php';


	function getCategoryLinks($url, $selectors_category)
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

					echo $url_product . PHP_EOL;
				}


			}
		}
	}


	//URL for page
	$url = 'http://www.hendi.pl/site_map';
	$selector_category = "div[class='content page'] ul li ul li.category span a";
	getCategoryLinks($url, $selector_category);
