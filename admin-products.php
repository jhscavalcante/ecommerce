<?php 

use \Hcode\PageAdmin; // Administração
use \Hcode\Model\User;
use \Hcode\Model\Product;

// LISTA
$app->get('/admin/products', function() {

	User::verifyLogin();

	$products = Product::listAll();
	
	$page = new PageAdmin();
	$page->setTpl("products", array(
		"products" => $products
	));
});

// TELA INSERT
$app->get('/admin/products/create', function() {

	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("products-create");
});

// INSERT
$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$product = new Product();
	$product->setData($_POST);
	$product->save();

	header("Location: /admin/products");
 	exit;	
});

// TELA UPDATE
$app->get('/admin/products/:idproduct', function($idproduct) {

	User::verifyLogin();

	$product = new Product();
	$product->get((int)$idproduct);

	$page = new PageAdmin();
	$page->setTpl("products-update", [
		"product"=>$product->getValues()
	]);
});

// UPDATE
$app->post('/admin/products/:idproduct', function($idproduct) {

	User::verifyLogin();

	$product = new Product();
	$product->get((int)$idproduct); // busca os dados atuais do banco
	$product->setData($_POST); // atualiza objeto com os dados do POST
    $product->save();

    //var_dump($_FILES);
    //exit;
    
    if($_FILES["file"]["name"] !== ""){
        $product->setPhoto($_FILES["file"]);
    }

	header("Location: /admin/products");
	exit;
});


// DELETE
$app->get('/admin/products/:idproduct/delete', function($idproduct) {

    User::verifyLogin();
    
	$product = new Product();
	$product->get((int)$idproduct);

    $product->delete();

    header("Location: /admin/products");
	exit;
});

?>