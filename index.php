<?php 
session_start();
require_once("vendor/autoload.php"); // dependências do site

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim(); // cria definição de rotas

$app->config('debug', true);

$app->get('/', function() {
	
	/*
	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);
	*/

	$page = new Page();
	$page->setTpl("index");
});

/**************************************************************************************
 *  ADMINISTRAÇÃO
 **************************************************************************************/
$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->get('/admin/login', function() {
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");
});

$app->post('/admin/login', function() {
	
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function() {
	
	User::logout();

	header("Location: /admin/login");
	exit;
});


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


	/*
	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

		"cost"=>12

	]);
	*/

	//var_dump($_POST);
	//exit;

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


$app->run();

 ?>