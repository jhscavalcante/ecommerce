<?php

use \Hcode\Page; // Site
use \Hcode\Model\Cart;

$app->get('/cart', function() {

    $cart = Cart::getFromSession();

	$page = new Page();
	$page->setTpl("cart");
});

?>