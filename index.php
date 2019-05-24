<?php


//引入autoload
require_once __DIR__ . '/vendor/autoload.php';
use domainCheck\src\DomainForCheckService;

$service = new DomainForCheckService();
$service->apiUrl = '域名检测api地址';
$service->webhook = '钉钉机器人api地址';
