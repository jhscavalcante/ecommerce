<?php

use \Hcode\Page; // Site
use \Hcode\Model\User;
use \Hcode\Model\Cart;


/*****************************************************/
/*********************** LOGIN  **********************/
/*****************************************************/
$app->get("/login", function(){

    $page = new Page();
    
	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>
		   (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : [
			   'name'=>'', 
			   'email'=>'', 
			   'phone'=>''
		   ]        
    ]);
    
});

$app->post("/login", function(){

	try {
		User::login($_POST['login'], $_POST['password']);
	} catch(Exception $e) {
		User::setError($e->getMessage());
    }
    
	header("Location: /checkout");
	exit;
});


/*****************************************************/
/*********************** LOGOUT  *********************/
/*****************************************************/
$app->get("/logout", function(){
	User::logout();
	Cart::removeFromSession();
	session_regenerate_id();
	
	header("Location: /login");
	exit;
});


/*****************************************************/
/********************** CADASTRO  ********************/
/*****************************************************/
$app->post("/register", function(){

	// salva as informações enviadas pelo formulário, para ocorrer o erro eles possam ser exibidos novamente	
	$_SESSION['registerValues'] = $_POST;

	// o campo name não foi definido ou foi definido mas está vazio
	if (!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {
		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST['email']) === true) {
		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;
	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	// Cria o usuário 
	$user->save();

	// E já autentica o usuário
	User::login($_POST['email'], $_POST['password']);

	$_SESSION['registerValues'] = [
		'name'=>'', 
		'email'=>'', 
		'phone'=>''
	];        

	header('Location: /checkout');
	exit;
});

/*****************************************************/
/***************** ESQUECEU A SENHA  *****************/
/*****************************************************/
$app->get("/forgot", function() {
	$page = new Page();
	$page->setTpl("forgot", [
		'error'=>
		(isset($_SESSION['UserError'])) ? $_SESSION['UserError'] : ''
	]);	

	$_SESSION['UserError'] = '';
});

$app->post("/forgot", function(){

	try {
		$user = User::getForgot($_POST["email"], false);
	} catch(Exception $e) {
		User::setError($e->getMessage());
		header("Location: /forgot");
		exit;
	}

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent", function(){
	$page = new Page();
	$page->setTpl("forgot-sent");	
});

$app->get("/forgot/reset", function(){
	//$user = User::validForgotDecrypt($_GET["code"]);

	try {
		$user = User::validForgotDecrypt($_GET["code"]);	
	} catch(Exception $e) {
		User::setErrorLinkForgotExpired($e->getMessage());
		header("Location: /forgot/expired");
		exit;
    }

	$page = new Page();
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/forgot/reset", function(){

	//try {
		$forgot = User::validForgotDecrypt($_POST["code"]);	
	//} catch(Exception $e) {
	//	User::setErrorLinkForgotExpired($e->getMessage());
	//	header("Location: /forgot/expired");
	//	exit;
    //}

	User::setForgotUsed($forgot["idrecovery"]);
	$user = new User();
	$user->get((int)$forgot["iduser"]);
	$password = $_POST["password"];
	$user->setPassword($password);

	$page = new Page();
	$page->setTpl("forgot-reset-success");
});

$app->get("/forgot/expired", function(){
	$page = new Page();
	$page->setTpl("forgot-expired", [
		'forgotLink'=>
		(isset($_SESSION['UserErrorLinkForgotExpired'])) ? $_SESSION['UserErrorLinkForgotExpired'] : ''
	]);
	
});

?>