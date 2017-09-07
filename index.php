<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Microdata</title>
</head>
<body>
<?php
require_once ("microdata.php");

$dom = new \Microdata\DocumentParser('https://letterboxd.com/film/dunkirk-2017/');
$dom01 = new \Microdata\DocumentParser('https://letterboxd.com/film/it-comes-at-night/');

?>
</body>
</html>