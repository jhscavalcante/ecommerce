<?php

use \Hcode\Page; // Site
use \Hcode\Model\Product;

$app->get('/products/:desurl', function($desurl) {

    $product = new Product();

    $product->getFromURL($desurl);

	$page = new Page();
	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);
});

?>