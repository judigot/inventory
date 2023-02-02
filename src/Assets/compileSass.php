<?php

require_once "scssphp/scss.inc.php";

use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

$cssDirectoryName = "Assets/css";
$sassDirectoryName = "Assets/sass";

$cssFiles;
$scssFiles = glob("$sassDirectoryName/*.scss");
$scss->setImportPaths($sassDirectoryName);

if (!file_exists($cssDirectoryName)) {
    mkdir($cssDirectoryName);
}

for ($i = 0; $i < count($scssFiles); $i++) {
    $currentFile = pathinfo($scssFiles[$i])["filename"];
    $sassContent = $scss->compile(file_get_contents("$sassDirectoryName/" . $currentFile . ".scss"));
    if (($currentFile[0] !== "_") && (!file_exists("$cssDirectoryName/$currentFile" . ".css")) || (file_exists("$cssDirectoryName/$currentFile" . ".css") && (strcmp(file_get_contents("$cssDirectoryName/$currentFile.css"), $sassContent) !== 0))) {
        $fp = fopen("$cssDirectoryName/$currentFile.css", "wb");
        fwrite($fp, $sassContent);
    }
}
