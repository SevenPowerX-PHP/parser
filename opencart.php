<?php
/**
 * Created by PhpStorm.
 * User: serik_lav
 * Date: 2019-03-06
 * Time: 09:19
 */

ini_set('max_execution_time', 0);

require_once __DIR__ . '/config/main_config.php';

//Подключаем библиотеку
require_once 'lib/phpQuery.php';
require_once 'src/DbConnectMysql.php';

class OpencartParse {
    public $db_parse;
    public $db;
    public $products;

    public function __construct() {
        $this->db_parse = new DbConnectMysql(HOST, USER, PASS, DB_NAME_OPENCART);
        $this->db = new DbConnectMysql(HOST, USER, PASS, DB_NAME);
    }

    public function getProducts() {
        $query = $this->db->query("SELECT * FROM product");

        $this->products = $query->rows;
    }

    public function addProduct($data) {
        $isset_product = $this->db_parse->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE `name` = '" . $this->db_parse->escape($data['product_description'][3]['name']) . "'");

        if (!$isset_product->num_rows) {
            $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db_parse->escape($data['model']) . "', sku = '" . $this->db_parse->escape($data['sku']) . "', upc = '" . $this->db_parse->escape($data['upc']) . "', ean = '" . $this->db_parse->escape($data['ean']) . "', jan = '" . $this->db_parse->escape($data['jan']) . "', isbn = '" . $this->db_parse->escape($data['isbn']) . "', mpn = '" . $this->db_parse->escape($data['mpn']) . "', location = '" . $this->db_parse->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db_parse->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

            $product_id = $this->db_parse->getLastId();

            if (isset($data['image'])) {
                $this->db_parse->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db_parse->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
            }

            foreach ($data['product_description'] as $language_id => $value) {
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db_parse->escape($value['name']) . "', description = '" . $this->db_parse->escape($value['description']) . "', tag = '" . $this->db_parse->escape($value['tag']) . "', meta_title = '" . $this->db_parse->escape($value['meta_title']) . "', meta_h1 = '" . $this->db_parse->escape($value['meta_h1']) . "', meta_description = '" . $this->db_parse->escape($value['meta_description']) . "', meta_keyword = '" . $this->db_parse->escape($value['meta_keyword']) . "'");
            }

            if (isset($data['product_store'])) {
                foreach ($data['product_store'] as $store_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
                }
            }

            if (isset($data['product_attribute'])) {
                foreach ($data['product_attribute'] as $product_attribute) {
                    if ($product_attribute['attribute_id']) {
                        foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                            $isset_attribute = $this->db_parse->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] ."' AND language_id = '" . (int)$language_id . "'");

                            if (!$isset_attribute->num_rows) {
                                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db_parse->escape($product_attribute_description['text']) . "'");
                            }
                        }
                    }
                }
            }

            if (isset($data['product_option'])) {
                foreach ($data['product_option'] as $product_option) {
                    if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                        if (isset($product_option['product_option_value'])) {
                            $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

                            $product_option_id = $this->db_parse->getLastId();

                            foreach ($product_option['product_option_value'] as $product_option_value) {
                                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db_parse->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db_parse->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db_parse->escape($product_option_value['weight_prefix']) . "'");
                            }
                        }
                    } else {
                        $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db_parse->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
                    }
                }
            }

            if (isset($data['product_discount'])) {
                foreach ($data['product_discount'] as $product_discount) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db_parse->escape($product_discount['date_start']) . "', date_end = '" . $this->db_parse->escape($product_discount['date_end']) . "'");
                }
            }

            if (isset($data['product_special'])) {
                foreach ($data['product_special'] as $product_special) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db_parse->escape($product_special['date_start']) . "', date_end = '" . $this->db_parse->escape($product_special['date_end']) . "'");
                }
            }

            if (isset($data['product_image'])) {
                foreach ($data['product_image'] as $product_image) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db_parse->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
                }
            }

            if (isset($data['product_download'])) {
                foreach ($data['product_download'] as $download_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
                }
            }

            if (isset($data['product_category'])) {
                foreach ($data['product_category'] as $category_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
                }
            }

            if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
                $this->db_parse->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
            } elseif (isset($data['product_category'][0])) {
                $this->db_parse->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
            }

            if (isset($data['product_filter'])) {
                foreach ($data['product_filter'] as $filter_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
                }
            }

            if (isset($data['product_related'])) {
                foreach ($data['product_related'] as $related_id) {
                    $this->db_parse->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                    $this->db_parse->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
                }
            }

            if (isset($data['product_reward'])) {
                foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
                    if ((int)$product_reward['points'] > 0) {
                        $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
                    }
                }
            }

            if (isset($data['product_layout'])) {
                foreach ($data['product_layout'] as $store_id => $layout_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
                }
            }

            if (isset($data['keyword'])) {
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db_parse->escape($data['keyword']) . "'");
            }

            if (isset($data['product_recurrings'])) {
                foreach ($data['product_recurrings'] as $recurring) {
                    $this->db_parse->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$recurring['customer_group_id'] . ", `recurring_id` = " . (int)$recurring['recurring_id']);
                }
            }
        } else {
            $product_id = $isset_product->row['product_id'];

            $this->db_parse->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$data['price'] . "', date_added = NOW() WHERE product_id = '" . (int)$product_id ."'");
        }

        return $product_id;
    }

    public function addAttribute($data) {
        $attribute_id = $this->checkAttr($data['attribute_description'][1]['name']);

        if (!$attribute_id) {
            $this->db_parse->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '7', sort_order = '" . (int)$data['sort_order'] . "'");

            $attribute_id = $this->db_parse->getLastId();

            foreach ($data['attribute_description'] as $language_id => $value) {
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db_parse->escape($value['name']) . "'");
            }
        }

        return $attribute_id;
    }

    public function addCategory($data) {
        $isset_category = $this->db_parse->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE `name` = '" . $this->db_parse->escape($data['category_description'][3]['name']) . "'");

        if (!$isset_category->num_rows) {

            $this->db_parse->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

            $category_id = $this->db_parse->getLastId();

            if (isset($data['image'])) {
                $this->db_parse->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db_parse->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
            }

            foreach ($data['category_description'] as $language_id => $value) {
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db_parse->escape($value['name']) . "', description = '" . $this->db_parse->escape($value['description']) . "', meta_title = '" . $this->db_parse->escape($value['meta_title']) . "', meta_h1 = '" . $this->db_parse->escape($value['meta_h1']) . "', meta_description = '" . $this->db_parse->escape($value['meta_description']) . "', meta_keyword = '" . $this->db_parse->escape($value['meta_keyword']) . "'");
            }

            // MySQL Hierarchical Data Closure Table Pattern
            $level = 0;

            $query = $this->db_parse->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

            foreach ($query->rows as $result) {
                $this->db_parse->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

                $level++;
            }

            $this->db_parse->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

            if (isset($data['category_filter'])) {
                foreach ($data['category_filter'] as $filter_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
                }
            }

            if (isset($data['category_store'])) {
                foreach ($data['category_store'] as $store_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
                }
            }

            // Set which layout to use with this category
            if (isset($data['category_layout'])) {
                foreach ($data['category_layout'] as $store_id => $layout_id) {
                    $this->db_parse->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
                }
            }

            if (isset($data['keyword'])) {
                $this->db_parse->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db_parse->escape($data['keyword']) . "'");
            }
        } else {
            $category_id = $isset_category->row['category_id'];
        }

        return $category_id;
    }

    public function checkAttr($name) {
        $check = 0;

        $isset_attribute = $this->db_parse->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE `name` = '" . $this->db_parse->escape($name) . "'");

        if ($isset_attribute->num_rows) {
            $check = $isset_attribute->row['attribute_id'];
        }

        return $check;
    }

    public function clearTable() {
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_description");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_image");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_attribute");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_option");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_option_value");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_discount");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_special");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_to_download");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_to_category");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_filter");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_related");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_reward");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_to_layout");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_recurring");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "product_to_store");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category_description");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category_path");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category_filter");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category_to_store");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "category_to_layout");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "attribute");
        $this->db_parse->query("TRUNCATE TABLE " . DB_PREFIX . "attribute_description");
        $this->db_parse->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query LIKE '%product_id%'");
        $this->db_parse->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query LIKE '%category_id%'");
        $this->db_parse->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query LIKE '%path%'");
    }

    public function saveImage($link, $name) {
        $ch = curl_init($link);
        $fp = fopen('/Volumes/Data/www/horeca-partner.loc/image/catalog/product/' . $name, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $name;
    }
}