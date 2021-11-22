<?php 

require_once("vendor/autoload.php");

use Slim\Slim;
use Hcode\Page;

$app = new Slim();

$app->config('debug', true);
//essa função tras o por padrao com o metodo get, transformando essa pagina na pagina raiz
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->run();

 ?>