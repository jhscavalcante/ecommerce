<?php

use \Hcode\Page; // Site
use \Hcode\Model\Cart;
use \Hcode\Model\Product;
use \Hcode\Model\User;
use \Hcode\Model\Address;

$app->get('/cart', function() {

	// recuperar o carrinho (existente ou novo)
	$cart = Cart::getFromSession();
	
	//var_dump($cart->getProducts());
	//exit;

	$page = new Page();
	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

$app->get('/cart/:idproduct/add', function($idproduct) {
	
	$product = new Product();
	$product->get((int)$idproduct);

	// recuperar o carrinho (existente ou novo)
	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});

$app->get('/cart/:idproduct/minus', function($idproduct) {
	
	$product = new Product();
	$product->get((int)$idproduct);

	// recuperar o carrinho (existente ou novo)
	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get('/cart/:idproduct/remove', function($idproduct) {
	
	$product = new Product();
	$product->get((int)$idproduct);

	// recuperar o carrinho (existente ou novo)
	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true); //TRUE => remove todos

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;
});

$app->get("/checkout", function(){

	User::verifyLogin(false);

	$address = new Address();
	$cart = Cart::getFromSession();

	if (!isset($_GET['zipcode'])) {
		$_GET['zipcode'] = $cart->getdeszipcode();
	}

	if (isset($_GET['zipcode'])) {
		$address->loadFromCEP($_GET['zipcode']);
		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();
		$cart->getCalculateTotal();
	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();
	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);
});



?>