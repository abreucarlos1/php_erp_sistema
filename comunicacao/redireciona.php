<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Imagem</title>

<link href="../classes/css_geral.css" rel="stylesheet" type="text/css" />
</head>

<?php

$imagem = $_GET["imagem"];

?>

<style type="text/css">
<!--
body {
	margin:0px;
	padding:0px;
	background-image: url(<?= $imagem ?>);
}
-->
</style>

<body>

<div id="botao_div" style="position:absolute; top:550px; left:10px;">
<input type="button" value="Voltar" class="fonte_botao" onclick="history.back();" />
</div>

</body>
</html>
