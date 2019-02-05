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


$app->run();

 ?>