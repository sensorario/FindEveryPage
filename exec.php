<?php
require __DIR__ . '/vendor/autoload.php';
use Kryuko\FindEveryPage\FindEveryPageClass;

$settings = array(
    'baseUrl' => 'https://wpportfolio.net/',
    'enableLog' => true,
    'sleepTime' => 1
);
$visitEveryPage = new FindEveryPageClass;
$visitEveryPage->setBaseData($settings);
$urlsArray = $visitEveryPage->automate();