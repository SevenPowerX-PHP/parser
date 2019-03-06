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

//Connect Opencart Parse
require_once 'opencart.php';
$opencart = new OpencartParse();

/**
 * @param $url
 * @param $selectors_category
 * @throws Exception
 */
function getCategoryAndProductLinks($url, $selectors_category)
{
    global $opencart;

    //Get page
    $html = initPhpQuery($url);

    foreach ($html->find($selectors_category) as $links) {
        $link_to_category = pq($links);

        $url_category = HTTP_SITE . $link_to_category->attr('href');

        /** @var string $url_category */
        $html = initPhpQuery($url_category);
        $selectors_product = "ul.products_list li a";
        foreach ($html->find($selectors_product) as $links_product) {
            $link_to_product = pq($links_product);

            $url_product = HTTP_SITE . $link_to_product->attr('href');
            $url_product = $opencart->db->escape($url_product);

            $opencart->db->query("INSERT ignore INTO product (url_product) VALUES ('{$url_product}')");

            // TODO:: maybe thinking about another algorithm
            getProduct($url_product);exit;
        }
    }
}
function getCategory($url_category) {
    global $opencart;

    //Get page
    $html = initPhpQuery($url_category);

    $description = $html->find('.seo_container_txt_category p')->text();
    $meta_title = $html->find('title')->text();
    $meta_h1 = $html->find('h2:first-child')->text();
    $meta_description = $html->find('meta[name="description"]')->attr('content');
    $meta_keyword = $html->find('meta[name="keywords"]')->attr('content');

    $data_cat = [
        'category_description' => [
            3 => [
                'name' => $meta_h1,
                'description' => $description,
                'meta_title' => $meta_title,
                'meta_h1' => $meta_h1,
                'meta_description' => $meta_description,
                'meta_keyword' => $meta_keyword
            ],
            2 => [
                'name' => gtranslate($meta_h1, 'pl', 'en'),
                'description' => gtranslate($description, 'pl', 'en'),
                'meta_title' => gtranslate($meta_title, 'pl', 'en'),
                'meta_h1' => gtranslate($meta_h1, 'pl', 'en'),
                'meta_description' => gtranslate($meta_description, 'pl', 'en'),
                'meta_keyword' => gtranslate($meta_keyword, 'pl', 'en')
            ],
            1 => [
                'name' => gtranslate($meta_h1, 'pl', 'ru'),
                'description' => gtranslate($description, 'pl', 'ru'),
                'meta_title' => gtranslate($meta_title, 'pl', 'ru'),
                'meta_h1' => gtranslate($meta_h1, 'pl', 'ru'),
                'meta_description' => gtranslate($meta_description, 'pl', 'ru'),
                'meta_keyword' => gtranslate($meta_keyword, 'pl', 'ru')
            ]
        ],
        'parent_id' => 0,
        'filter' => [],
        'category_store' => [
            '0' => 0
        ],
        'keyword' => basename($url_category, '.html'),
        'image' => '',
        'top' => '1',
        'column' => '1',
        'sort_order' => '0',
        'status' => '1',
        'category_layout' => [
            '0' => ''
        ]
    ];

    return $opencart->addCategory($data_cat);
}

/**
 * @param $url_product
 * @return
 * @throws Exception
 */
function getProduct($url_product)
{
    global $opencart;

    //Get page
    $html = initPhpQuery($url_product);

    // Remove Other div's
    $other_divs = '.alior_payment, script';
    $html->find($other_divs)->remove();

    // get h1
    $h1_selectors_product = ".product__data .product__data__name";
    $h1 = $html->find($h1_selectors_product, 0)->text();

    $h1_en = gtranslate($h1, 'pl', 'en');
    $h1_ru = gtranslate($h1, 'pl', 'ru');

    // Get Model
    $model_explode = explode(' kod ', $h1);
    $model = isset($model_explode[1]) ? $model_explode[1] : '0001';

    //get content
    $content_selector_product = ".product__desc__content";
    $content_pl = $html->find($content_selector_product)->html();

    $content_ru = gtranslate($content_pl, 'pl', 'ru');
    $content_en = gtranslate($content_pl, 'pl', 'en');

    // get meta
    $meta_title_pl = $html->find('title')->html();
    $meta_description_pl = $html->find('meta[name="description"]')->attr('content');
    $meta_keyword_pl = $html->find('meta[name="keywords"]')->attr('content');

    $meta_title_en = gtranslate($h1, 'pl', 'en');
    $meta_description_en = gtranslate($h1, 'pl', 'en');
    $meta_keyword_en = gtranslate($h1, 'pl', 'en');

    $meta_title_ru = gtranslate($h1, 'pl', 'ru');
    $meta_description_ru = gtranslate($h1, 'pl', 'ru');
    $meta_keyword_ru = gtranslate($h1, 'pl', 'ru');

    // Get images
    $sliders = $html->find('li.slider__list__item:not(.img_movie)');
    $images = [];
    $img_sort_order = 0;
    foreach ($sliders as $slider) {
        $img = pq($slider);
        $images[] = [
            'image' => '/catalog/product/' . $opencart->saveImage(HTTP_SITE . '/' . $img->find('a')->attr('href'), basename($img->find('a')->attr('href'))),
            'sort_order' => $img_sort_order
        ];

        $img_sort_order++;
    }

    $main_img = array_shift($images);

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
    $metr_attr = 0;
    foreach ($attributes as $attribute) {
        $elem = pq($attribute);

        $attributes_add = array();
        $attributes_add['sort_order'] = $metr_attr;
        $attributes_add['attribute_description'] = [
            1 => [
                'name' => gtranslate($elem->find('th')->text(), 'pl', 'ru')
            ],
            2 => [
                'name' => gtranslate($elem->find('th')->text(), 'pl', 'en')
            ],
            3 => [
                'name' => $elem->find('th')->text()
            ]
        ];

        $attributes_json[] = [
            'name'  => $elem->find('th')->text(),
            'value' => $elem->find('td')->text(),
            'attribute_id' => $opencart->addAttribute($attributes_add),
            'product_attribute_description' => [
                '3' => [
                    'text' => $elem->find('td')->text()
                ],
                '2' => [
                    'text' => gtranslate($elem->find('td')->text(), 'pl', 'en')
                ],
                '1' => [
                    'text' => gtranslate($elem->find('td')->text(), 'pl', 'ru')
                ]
            ]
        ];
        $metr_attr++;
    }

    // Categories
    $category_url = HTTP_SITE . $html->find('#breadcrumb')->find('.first a')->attr('href');
    $category_id = getCategory($category_url);

    $data = [
        'product_description' => [
            3 => [
                'name' => $h1,
                'description' => $content_pl,
                'meta_title' => $meta_title_pl,
                'meta_h1' => $h1,
                'meta_description' => $meta_description_pl,
                'meta_keyword' => $meta_keyword_pl,
                'tag' => ''
            ],
            2 => [
                'name' => $h1_en,
                'description' => $content_en,
                'meta_title' => $meta_title_en,
                'meta_h1' => $h1_en,
                'meta_description' => $meta_description_en,
                'meta_keyword' => $meta_keyword_en,
                'tag' => ''
            ],

            1 => [
                'name' => $h1_ru,
                'description' => $content_ru,
                'meta_title' => $meta_title_ru,
                'meta_h1' => $h1_ru,
                'meta_description' => $meta_description_ru,
                'meta_keyword' => $meta_keyword_ru,
                'tag' => ''
            ]
        ],
        'image' => $main_img['image'],
        'model' => (int)$h1,
        'sku' => '',
        'upc' => '',
        'ean' => '',
        'jan' => '',
        'isbn' => '',
        'mpn' => '',
        'location' => '',
        'price' => $price,
        'tax_class_id' => '9',
        'quantity' => '1000',
        'minimum' => '1',
        'subtract' => '0',
        'stock_status_id' => '7',
        'shipping' => '1',
        'keyword' => basename($url_product, '.html'),
        'date_available' => date('Y-m-d'),
        'length' => '',
        'width' => '',
        'height' => '',
        'length_class_id' => '1',
        'weight' => '',
        'weight_class_id' => '1',
        'status' => $status,
        'sort_order' => '1',
        'manufacturer_id' => '11',
        'main_category_id' => $category_id,
        'product_category' => [],
        'filter' => [],
        'product_store' => [
            0 => 0
        ],
        'download' => [],
        'related' => [],
        'product_attribute' => $attributes_json,
        'option' => [],
        'product_image' => $images,
        'points' => '',
        'product_reward' => [
            1 => [
                'points' => ''
            ]
        ],
        'product_layout' => [
            '0' => ''
        ]
    ];

    $opencart->addProduct($data);
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
$url = HTTP_SITE . '/site_map';
$selector_category = ".content.page li.category span a";

$opencart->clearTable();
getCategoryAndProductLinks($url, $selector_category);