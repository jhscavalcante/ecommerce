<?php

use \Hcode\PageAdmin; // Administração
use \Hcode\Model\User;

// LISTA CLIENTES
$app->get('/admin/users', function() {

	User::verifyLogin();

	$users = User::listAll();
	
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users" => $users
	));
});

// TELA DE CREATE
$app->get('/admin/users/create', function() {

	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("users-create");
});

// EXCLUIR CLIENTE
$app->get('/admin/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	
	header("Location: /admin/users");
	exit;
});

// TELA DE UPDATE
$app->get('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();
	$user->get((int) $iduser);
	
	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user" => $user->getValues()
	));
});

// INSERE CLIENTE
$app->post("/admin/users/create", function(){

	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	$_POST['despassword'] = User::getPasswordHash($_POST['despassword']);
	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");
 	exit;
	
});


// ATUALIZA CLIENTE
$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int) $iduser);
	$user->setData($_POST); 
	$user->update();

	header("Location: /admin/users");
	exit;
});

?>