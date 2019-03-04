<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 22.02.2019
	 * Time: 12:54
	 */

    ini_set('max_execution_time', 0);

	require_once __DIR__ . '/config/main_config.php';

	//Подключаем библиотеку
	require_once 'lib/phpQuery.php';
	require_once 'src/DbConnectMysql.php';

	$db = new DbConnectMysql(HOST, USER, PASS, DB_NAME);

	function checkProductInDB($url) {
	    global $db;

	    $query = $db->query("SELECT * FROM product WHERE url_product = '" . $db->escape($url) .  "'");

	    if (count($query) == 1) {
            $query = array_pop($query);
	        return $query['id'];
        } else {
	        false;
        }
    }

    function getProducts() {
        global $db;

        $query = $db->query("SELECT id FROM product");

        return $query;
    }

	/**
	 * @param $url
	 * @param $selectors_category
	 * @throws Exception
	 */
	function getCategoryAndProductLinks($url, $selectors_category)
	{
	    global $db;

		//Get page
		$html = initPhpQuery($url);

		foreach ($html->find($selectors_category) as $links) {
            $link_to_category = pq($links);

            $url_category = 'http://www.hendi.pl' . $link_to_category->attr('href');

			/** @var string $url_category */
			$html = initPhpQuery($url_category);
			$selectors_product = "ul.products_list li a";
			foreach ($html->find($selectors_product) as $links_product) {
                $link_to_product = pq($links_product);

                $url_product = 'http://www.hendi.pl' . $link_to_product->attr('href');
                $url_product = $db->escape($url_product);

                $db->query("INSERT ignore INTO product (url_product) VALUES ('{$url_product}')");

                // TODO:: maybe thinking about another algorithm
                getProduct($url_product);

                echo $url_product . '<br>' . PHP_EOL;
			}
		}
	}

	/**
	 * @param $url_product
	 * @return
	 * @throws Exception
	 */
	function getProduct($url_product)
	{
        global $db;

		//Get page
		$html = initPhpQuery($url_product);

		// Remove Other div's
        $other_divs = '.alior_payment, script';
        $html->find($other_divs)->remove();

		// get h1
		$h1_selectors_product = ".product__data .product__data__name";
		$h1 = $html->find($h1_selectors_product, 0)->text();

        //get content
        $content_selector_product = ".product__desc__content";
        $content_pl = $html->find($content_selector_product)->html();

		$content_ru = gtranslate($content_pl, 'pl', 'ru');
		$content_en = gtranslate($content_pl, 'pl', 'en');

        // Get images
        $sliders = $html->find('li.slider__list__item:not(.img_movie)');
        $images = '';
        foreach ($sliders as $slider) {
            $img = pq($slider);
            $images .= 'http://www.hendi.pl' . $img->find('a')->attr('href') . ',';
        }

        // Get price
        $price = (float)$html->find('.nett_price')->text();

        // Get status itemprop="availability"
        $status_text = (string)$html->find('link[itemprop="availability"]')->attr('content');
        if ($status_text == 'Dostępny') {
            $status = 1;
        } else {
            $status = 0;
        }

        // Get tags
        $tags = '';

        // Get Attributes
        $attributes = $html->find('.product_description_table tr');
        $attributes_json = [];
        foreach ($attributes as $attribute) {
            $elem = pq($attribute);
            $attributes_json[] = [
                'name'  => $elem->find('th')->text(),
                'value' => $elem->find('td')->text()
            ];
        }


        $product_id = checkProductInDB($url_product);
        if ($product_id) {
            $db->query("UPDATE product SET 
                                            h1='{$db->escape($h1)}', 
                                            content_pl='{$db->escape($content_pl)}', 
                                            content_ru='{$db->escape($content_ru)}', 
                                            content_en='{$db->escape($content_en)}',  
                                            img='{$db->escape($images)}', 
                                            price='{$db->escape($price)}', 
                                            status='{$db->escape($status)}', 
                                            tags='{$db->escape($tags)}', 
                                            attribute='{$db->escape(json_encode($attributes_json))}',
                                            data_parsing='" . date("Y-m-d") ."' 
    
              WHERE id = '{$product_id}'");

            return $product_id;
        } else {
            $db->query("INSERT INTO product SET 
                                            url_product = '{$url_product}',
                                            h1='{$db->escape($h1)}', 
                                            content_pl='{$db->escape($content_pl)}', 
                                            content_ru='{$db->escape($content_pl)}', 
                                            content_en='{$db->escape($content_pl)}',  
                                            img='{$db->escape($images)}', 
                                            price='{$db->escape($price)}', 
                                            status='{$db->escape($status)}', 
                                            tags='{$db->escape($tags)}', 
                                            attribute='{$db->escape(json_encode($attributes_json))}',
                                            data_parsing='" . date("Y-m-d") ."'");

            return $db->getLastId();
        }
	}


	// Translate API
	function gtranslate($str, $lang_from, $lang_to)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.multillect.com/translate/json/1.0/987?method=translate/api/translate&from=' . urlencode($lang_from) . '&to=' . urlencode($lang_to) . '&text=' . urlencode($str) . '&politeness=&sig=7af031c46b9a323e41301ea2a40d9440');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$out = curl_exec($curl);
		curl_close($curl);
		$decode = json_decode($out);
		if (!$decode->error) {
			return $decode->result->translated;
		}
		return false;
	}


	//Parse Site Init
	$url = 'http://www.hendi.pl/site_map';
	$selector_category = ".content.page li.category span a";

	getCategoryAndProductLinks($url, $selector_category);