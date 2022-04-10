<?php

$dbInfoFileName = "dbinfo.json";
$jsonPath = (file_exists($dbInfoFileName)) ? $dbInfoFileName : "Classes/$dbInfoFileName";
$dbinfo = json_decode(file_get_contents($jsonPath), true);

$DatabaseName = $dbinfo["database"][0];
$Host = $dbinfo["host"];
$Username = $dbinfo["username"];
$Password = $dbinfo["password"];

$app_user = $dbinfo["table"][0];
$app_customer = $dbinfo["table"][1];
$app_order = $dbinfo["table"][2];
$app_order_product = $dbinfo["table"][3];
$app_product = $dbinfo["table"][4];
$app_product_category = $dbinfo["table"][5];

$appName = $dbinfo["appName"];
$appSettings = $dbinfo["appSettings"];