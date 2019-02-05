<?php 

require_once("vendor/autoload.php"); // dependências do site

use \Slim\Slim;
use \Hcode\Page;

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

$app->run();

 ?>