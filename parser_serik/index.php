<?php
/**
 * Created by PhpStorm.
 * User: serik_lav
 * Date: 14.11.2018
 * Time: 5:49 PM
 */

// Languages
ini_set('max_execution_time', 0);

// Connect Config
require_once("config.php");
// 1 - russian, 2 - ukrainian

// PARSE site
require_once(DIR_MODEL . "parser.php");
require_once(DIR_SYSTEM . "library/phpQuery.php");


// Clear All Data
clearTable();

$document = initPhpQuery(PARSE_URL);

// Get categories
$products = $document->find('url');

// Iterator
$metr = 1;

// Data product
$data = array();
$category = array();

// Each products
foreach ($products as $el_product) {
    $pq_product = pq($el_product);

    if ($metr % 2 != 0) {
        $ru = $pq_product->find('loc')->eq(0)->text();
        $ua = str_replace(PARSE_SITE, PARSE_SITE . "ua/", $ru);

        // Link to image
        $image_src = str_replace(".jpg", "_small12.jpg", $pq_product->find('loc')->eq(1)->text());

        // Get Product main field
        $data = getProductMainInformation($ru);

        // Get Product russian language
        $data['product_description'][1] = getProduct($ru);

        // Get Product ukrainian language
        $data['product_description'][2] = getProduct($ua);

        // Get images
        $data['images'] = $image_src;

        // Get Attr
        $data['attribute_description'][1] = getAtrributes($ru);
        $data['attribute_description'][2] = getAtrributes($ua);

        // Get Category Information
        $link_category_ru = get_link_category($ru);
        $link_category_ua = get_link_category($ua);

        $category['category_description'][1] = getCategory($link_category_ru);
        $category['category_description'][2] = getCategory($link_category_ua);

        // Add Category
        $data['category_id'] = addCategory($category);

        // Add Product
        addProduct($data);
    }
    $metr++;
}

// Functions
function initPhpQuery($link) {
    // Sleep 2
    sleep(2);
    // Init site and get contents
    $contents = file_get_contents($link);

    // Init library phpQuery
    $document = phpQuery::newDocument($contents);

    return $document;
}

// Get all information about product
function getProductMainInformation($link) {
    global $log;

    $data = array();

    // Go to product page
    $product_page = initPhpQuery($link);

    $data['model'] = str_replace("Артикул: ", "", $product_page->find('.product-code')->text());
    $data['price'] = (float)str_replace(array(" ", " грн"), "", $product_page->find('.productCard-price')->text());
    $data['stock_status'] = $product_page->find('.productCard-availability')->text();

    // Log
    $log->write("Парсится товар: " . $link);
    // End Log

    return $data;
}

// Get Product information
function getProduct($link) {
    global $log;

    $data = array();

    // Go to product Page
    $product_page = initPhpQuery($link);

    $data['name'] = $product_page->find('.main-h')->text();
    $data['description'] = $product_page->find('.product-description')->text();
    $data['tag'] = $product_page->find('meta[name="keywords"]')->attr('content');
    $data['meta_title'] = $product_page->find('title')->text();
    $data['meta_h1'] = $product_page->find('.main-h')->text();
    $data['meta_description'] = str_replace(array("Пирамида24", "Піраміда24"), "SIMAG", $product_page->find('meta[name="description"]')->attr('content'));
    $data['meta_keyword'] = $product_page->find('meta[name="keywords"]')->attr('content');

    // Log
    $log->write("----Парсится описание товар----:" . $link);
    // End Log

    return $data;
}

// Get all information about category
function getCategory($link) {
    global $log;

    $data = array();

    // Go to product Page
    $category_page = initPhpQuery($link);

    $category_page->find('.text iframe')->remove();
    $category_page->find('.text img')->remove();
    $data['description'] = str_replace("Пирамида24", "SIMAG", $category_page->find('.text')->html());
    $data['name'] = $category_page->find('.main-h')->text();
    $data['meta_title'] = $category_page->find('title')->text();
    $data['meta_h1'] = $category_page->find('.main-h')->text();
    $data['meta_description'] = str_replace(array("Пирамида24", "Піраміда24"), "SIMAG", $category_page->find('meta[name="description"]')->attr('content'));
    $data['meta_keyword'] = $category_page->find('meta[name="keywords"]')->attr('content');
    $data['image'] = str_replace(".jpg", "_small12.jpg", $category_page->find('meta[property="og:image"]')->attr('content'));

    // Log
    $log->write("----Парсится категория----:" . $link);
    // End Log

    return $data;
}

function getAtrributes($link) {
    global $log;

    $data = array();

    // Go to product Page
    $product_page = initPhpQuery($link);

    $attributes = $product_page->find('.product-features-row');

    foreach ($attributes as $el_attributes) {
        $pq_attr = pq($el_attributes);

        $data[] = array('name' => $pq_attr->find('.__h')->text(), 'text' => $pq_attr->find('.product-features-cell')->eq(1)->text());
    }

    // Log
    $log->write("----Парсится атрибуты----:" . $link);
    // End Log

    return $data;
}

function get_link_category($link) {
    global $log;

    // Init link variable
    $link_category = '';

    // Go to product Page
    $category_page = initPhpQuery($link);

    $link_category = $category_page->find('.breadcrumbs-i:last-child')->prev()->find('a')->attr('href');

    // Log
    $log->write("----Получение ссылки категории----:" . $link);
    // End Log

    return $link_category;
}

// Translate API
/*
 * https://multillect.com/apidoc
 * lavrinyuk.serik@gmail.com
 * serik1997
 * echo "<pre>";print_r(gtranslate("Hello", "en", "ru"));exit;
 */
function gtranslate($str, $lang_from, $lang_to) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.multillect.com/translate/json/1.0/987?method=translate/api/translate&from=' . urlencode($lang_from) . '&to=' . urlencode($lang_to) . '&text=' . urlencode($str) . '&politeness=&sig=7af031c46b9a323e41301ea2a40d9440');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    $out = curl_exec($curl);
    curl_close($curl);

    $decode = json_decode($out);

    if (!$decode->error) {
        return $decode->result->translated;
    }

    return false;
}