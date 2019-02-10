<?php

use \Hcode\Page; // Site
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);
});

?>