<?php

use \Hcode\Page; // Site
 
$app->get('/', function() {
	
	/*
	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);
	*/

	$page = new Page();
	$page->setTpl("index");
});
    
?>