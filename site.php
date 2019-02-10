<?php

use \Hcode\Page; // Site
use \Hcode\Model\Product;
 
$app->get('/', function() {
	
	/*
	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);
	*/

	$products = Product::listAll();

	$page = new Page();
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);
});
    
?>