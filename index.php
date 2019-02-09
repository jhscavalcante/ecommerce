<?php 
session_start();
require_once("vendor/autoload.php"); // dependências do site

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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


$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);
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


// TELA ESQUECEU A SENHA
$app->get('/admin/forgot', function() {
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});


// ESQUECEU A SENHA
$app->post('/admin/forgot', function() {

	$user = User::getForgot($_POST['email']);
	header("Location: /admin/forgot/sent");
	exit;
});

// TELA DE EMAIL ENVIADO
$app->get('/admin/forgot/sent', function() {
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");
});


// TELA DE RESET DE SENHA
$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post('/admin/forgot/reset', function() {
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int) $forgot["iduser"]);
	$user->setPassword($_POST["password"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");

});


/*****************************************/
/** CATEGORIAS ***************************/
// TELA DE RESET DE SENHA
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

$app->post('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory); // busca os dados atuais do banco
	$category->setData($_POST); // atualiza objeto com os dados do POST
	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->run();

 ?>