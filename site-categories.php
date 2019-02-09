<?php

use \Hcode\Page; // Site
use \Hcode\Model\Category;

$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);
});

?>