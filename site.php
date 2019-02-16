<?php

use \Hcode\Page; // Site
use \Hcode\Model\Product;
use \Hcode\Model\User;
 
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



    
?>