<?php 
session_start();
require_once("vendor/autoload.php"); // dependências do site

use \Slim\Slim;

$app = new Slim(); // cria definição de rotas

$app->config('debug', true);

require_once("functions.php");

// SITE
require_once("site.php");
require_once("site-cart.php");
require_once("site-categories.php");
require_once("site-login.php");
require_once("site-products.php");
require_once("site-profile.php");

//ADMINISTRAÇÃO
require_once("admin.php");
require_once("admin-categories.php");
require_once("admin-users.php");
require_once("admin-products.php");
require_once("admin-orders.php");

$app->run();

 ?>