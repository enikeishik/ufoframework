<?php
/**
 * @var \Ufo\Modules\View $this
 * @var array $items
 */

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?=$section->title?></title>
</head>
<body>
<h1><?=$section->title?></h1>
<?=$info?>
<?=$this->renderWidgets('left col top')?>
</body>
</html>
