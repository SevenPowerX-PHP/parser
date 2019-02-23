<?php
	/**
	 * Created by PhpStorm.
	 * User: Lavryniuk Andrii
	 * Date: 23.02.2019
	 * Time: 13:23
	 */

	require_once 'lib/phpQuery-onefile.php';


	$str = '<div id="elem">Текст</div><div>Еще тег</div>';
	$pq = phpQuery::newDocument($str);