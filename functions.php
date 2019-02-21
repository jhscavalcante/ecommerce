<?php 
use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlPrice)
{
    // se o [ $vlPrice ] não for maior que zero, então recebe 0
    if(!$vlPrice > 0) $vlPrice = 0;

    return number_format($vlPrice, 2, ",", ".");
}

function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

function getUserName()
{
    $user = User::getFromSession();
    //var_dump($user);
    //exit;
    return $user->getdesperson();
}


function getCartNrQtd()
{
	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();
	return $totals['nrqtd'];
}

function getCartVlSubTotal()
{
	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();
	return formatPrice($totals['vlprice']);
}

?>