<?php
/**
 * Created by PhpStorm.
 * User: serik_lav
 * Date: 14.11.2018
 * Time: 5:50 PM
 */

function getProducts($limit = 5) {
	global $db;

	$sql = "SELECT * FROM product p LIMIT " . $limit;

	$products = $db->query($sql);

	return $products->rows;
}

function getImages($product_id) {
	global $db;

	$images = '';

	$sql = "SELECT * FROM product_image WHERE product_id = '" . (int)$product_id . "'";

	$product_images = $db->query($sql);

	foreach ($product_images->rows as $key => $img) {
		$images .= $img['image'];

		if ($product_images->num_rows != ($key+1))
			$images .= ', ';
	}

	return $images;
}

function getProductDescription($product_id) {
	global $db;

	$product_description_data = array();

	$query = $db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

	foreach ($query->rows as $result) {
		$product_description_data[$result['language_id']] = array(
			'name'             => $result['name'],
			'description'      => $result['description'],
			'meta_title'       => $result['meta_title'],
			'meta_h1'          => $result['meta_h1'],
			'meta_description' => $result['meta_description'],
			'meta_keyword'     => $result['meta_keyword'],
			'tag'              => $result['tag']
		);
	}

	return $product_description_data;
}

function getProductAttributes($product_id) {
	global $db;

	$product_attribute_group_data = array();

	$product_attribute_data = array();

	$product_attribute_query = $db->query("SELECT DISTINCT a.attribute_id, ad.name, pa.text, ad.language_id FROM " . DB_PREFIX . "product_attribute pa 
	LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) 
	LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) 
	WHERE pa.product_id = '" . (int)$product_id . "' ORDER BY a.sort_order, ad.name");

	foreach ($product_attribute_query->rows as $product_attribute) {
		$product_attribute_data[$product_attribute['language_id']][] = array(
			'name'         => $product_attribute['name'],
			'text'         => $product_attribute['text']
		);
	}

	$product_attribute_group_data = array(
		1   => array(
			'name'               => "Характеристики",
			'attribute'          => $product_attribute_data[1]
		),
		2   => array(
			'name'               => "Характеристики",
			'attribute'          => $product_attribute_data[2]
		)
	);

	return $product_attribute_group_data;
}