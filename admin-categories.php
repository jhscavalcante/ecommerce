<?php

use \Hcode\PageAdmin; // Administração
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

/*****************************************/
/** CATEGORIAS ***************************/
// TELA DE CATEGORIAS
$app->get('/admin/categories', function() {

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();
	$page->setTpl("categories", array(
		"categories"=>$categories
	));
});


// CATEGORIES - TELA INSERT
$app->get('/admin/categories/create', function() {

	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("categories-create");
});

// CATEGORIES INSERT
$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
 	exit;	
});

// CATEGORIES DELETE
$app->get('/admin/categories/:idcategory/delete', function($idcategory) {

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();
	
	header("Location: /admin/categories");
	exit;
});

// CATEGORIES - TELA UPDATE
$app->get('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);
});

// CATEGORIES - UPDATE
$app->post('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory); // busca os dados atuais do banco
	$category->setData($_POST); // atualiza objeto com os dados do POST
	$category->save();

	header("Location: /admin/categories");
	exit;
});


// TELA CATEGORIAS X PRODUTOS
$app->get('/admin/categories/:idcategory/products', function($idcategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);
});

// ADD CATEGORIAS X PRODUTOS
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct) {
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

// REMOVE CATEGORIAS X PRODUTOS
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct) {
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

?>