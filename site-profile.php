<?php

use \Hcode\Page; // Site
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Order;

$app->get("/profile", function(){

	// false = não é admin
	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

$app->post("/profile", function(){

	// false = não é admin
	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu e-mail.");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();

	// se mudou o login, vai verificar se o novo e-mail já existe no banco
	if ($_POST['desemail'] !== $user->getdesemail()) {
		if (User::checkLoginExist($_POST['desemail']) === true) {
			User::setError("Este endereço de e-mail já está cadastrado.");
			header('Location: /profile');
			exit;
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados alterados com sucesso!");
	header('Location: /profile');
	exit;
});


$app->get("/profile/orders", function(){

    User::verifyLogin(false);
    
    $user = User::getFromSession();
    
	$page = new Page();
	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
	]);
});

$app->get("/profile/orders/:idorder", function($idorder){

    User::verifyLogin(false);
    
	$order = new Order();
    $order->get((int)$idorder);
    
	$cart = new Cart();
	$cart->get((int)$order->getidcart());
    $cart->getCalculateTotal();
    
	$page = new Page();
	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);	
});
    
?>