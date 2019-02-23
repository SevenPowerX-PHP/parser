<?php
/**
 * Created by PhpStorm.
 * User: serik_lav
 * Date: 14.11.2018
 * Time: 5:50 PM
 */

function addProduct($data) {
	global $db;

	if ($data['model']) {
		$isset_product = $db->query("SELECT * FROM product WHERE model = '" . $db->escape($data['model']) . "'");

		if (!$isset_product->num_rows) {
			$db->query("INSERT INTO product SET category_id = '" . (int)$data['category_id'] . "', model = '" . $db->escape($data['model']) . "',  price = '" . (float)$data['price'] . "',  stock_status = '" . $db->escape($data['stock_status']) . "', date_added = NOW(), date_modified = NOW()");

			$product_id = $db->getLastId();

			foreach ($data['product_description'] as $language_id => $value) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "', description = '" . $db->escape($value['description']) . "', tag = '" . $db->escape($value['tag']) . "', meta_title = '" . $db->escape($value['meta_title']) . "', meta_h1 = '" . $db->escape($value['meta_h1']) . "', meta_description = '" . $db->escape($value['meta_description']) . "', meta_keyword = '" . $db->escape($value['meta_keyword']) . "'");
			}

			if ($data['images']) {
				//foreach ($data['images'] as $key => $img) {
					$db->query("INSERT INTO product_image SET product_id = " . (int)$product_id . ", image = '" . $db->escape($data['images']) . "', sort_order = '0'");
				//}
			}

			// Add Atribute
			if (!empty($data['attribute_description'])) {
				foreach ($data['attribute_description'] as $language_id => $product_attribute_description) {
					foreach ($product_attribute_description as $key => $pad) {
						$attribute_id = addAttribute($pad['name'], $data['attribute_description'][2][$key]['name']);

						if ($attribute_id) {
							$db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', text = '" .  $db->escape($pad['text']) . "'");
						}
					}
				}
			}
		} else {
			$db->query("UPDATE product SET category_id = '" . (int)$data['category_id'] . "', model = '" . $db->escape($data['model']) . "',  price = '" . (float)$data['price'] . "',  stock_status = '" . $db->escape($data['stock_status']) . "', date_modified = NOW() WHERE product_id = '" . (int)$isset_product->row['product_id'] . "'");

			$db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$isset_product->row['product_id'] . "'");

			foreach ($data['product_description'] as $language_id => $value) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$isset_product->row['product_id'] . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "', description = '" . $db->escape($value['description']) . "', tag = '" . $db->escape($value['tag']) . "', meta_title = '" . $db->escape($value['meta_title']) . "', meta_h1 = '" . $db->escape($value['meta_h1']) . "', meta_description = '" . $db->escape($value['meta_description']) . "', meta_keyword = '" . $db->escape($value['meta_keyword']) . "'");
			}

			$db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$isset_product->row['product_id'] . "'");

			if (isset($data['images'])) {
				//foreach ($data['images'] as $key => $img) {
					$db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$isset_product->row['product_id'] . "', image = '" . $db->escape($data['images']) . "', sort_order = '0'");
				//}
			}

			// Add Atribute
			$db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$isset_product->row['product_id'] . "'");

			if (!empty($data['attribute_description'])) {
				foreach ($data['attribute_description'] as $language_id => $product_attribute_description) {
					foreach ($product_attribute_description as $key => $pad) {
						$attribute_id = addAttribute($pad['name'], $data['attribute_description'][2][$key]['name']);

						if ($attribute_id) {
							$db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$isset_product->row['product_id'] . "', attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', text = '" .  $db->escape($pad['text']) . "'");
						}
					}
				}
			}
		}
	}

	return $db->getLastId();
}

function addCategory($data) {
	global $db;

	// Get Category ID
	$category_id = 0;

	$isset_category = $db->query("SELECT * FROM category_description WHERE name = '" . $db->escape($data['category_description'][1]['name']) . "'");

	if (!$isset_category->num_rows) {
		$db->query("INSERT INTO " . DB_PREFIX . "category SET image = '" . $db->escape($data['category_description'][1]['image']) . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $db->getLastId();

		foreach ($data['category_description'] as $language_id => $value) {
			$db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "', description = '" . $db->escape($value['description']) . "', meta_title = '" . $db->escape($value['meta_title']) . "', meta_h1 = '" . $db->escape($value['meta_h1']) . "', meta_description = '" . $db->escape($value['meta_description']) . "', meta_keyword = '" . $db->escape($value['meta_keyword']) . "'");
		}
	} else {
		$category_id = $isset_category->row['category_id'];
	}

	return $category_id;
}

function addAttribute($name_ru, $name_ua) {
	global $db;
	$attribute_id = checkAttr($name_ru);

	if (!$attribute_id) {
		$db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = 1, sort_order = 0");
		$attribute_id = $db->getLastId();

		$db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '1', name = '" . $db->escape($name_ru) . "'");
		$db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '2', name = '" . $db->escape($name_ua) . "'");
	}

	return $attribute_id;
}

function checkAttr($name) {
	global $db;

	$check = 0;

	$isset_attribute = $db->query("SELECT * FROM attribute_description WHERE name = '" . $db->escape($name) . "'");

	if ($isset_attribute->num_rows) {
		$check = $isset_attribute->row['attribute_id'];
	}

	return $check;
}

function clearTable() {
	global $db;

	$db->query("TRUNCATE TABLE " . DB_PREFIX . "product");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "product_description");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "product_image");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "product_attribute");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "category");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "category_description");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "attribute");
	$db->query("TRUNCATE TABLE " . DB_PREFIX . "attribute_description");
}




