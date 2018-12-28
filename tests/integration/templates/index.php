<?php
/**
 * @var \Ufo\Modules\View $this
 */

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?=$section->title?></title>
</head>
<body>
<h1><?=$section->title?></h1>
<?=($content??'')?>
<?=$this->renderWidgets('left col top')?>
</body>
</html>
