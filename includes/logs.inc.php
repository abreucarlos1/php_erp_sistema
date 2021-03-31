<?php
function logs($banco, $conexao, $usuario, $acao,$db='', $id_registro=0)
{
	session_start();
/*
//	LOGA AS AÇÕES DOS USUÁRIOS

$banco -> banco de inserção dos dados
$conexao -> link da variavel $conexao
$usuario -> nome do usuario
$acao -> descrição da ação
$db -> banco alvo
$id_registro -> registro afetado

*/
	$formulario = explode("/",$_SERVER["PHP_SELF"]);
	
	$pagina = $formulario[count($formulario)-1];
	
	$isql = "INSERT INTO logs.".$banco." ";
	$isql .= " (ip, usuario, data, hora, acao, db, id_registro, formulario) ";
	$isql .= "VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', ";
	$isql .= "'" . $usuario . "', ";
	$isql .= "'" . date('Y-m-d') . "', ";
	$isql .= "'" . date('H:i:s') . "', ";
	$isql .= "'" . $acao . "', ";
	$isql .= "'" . $db . "', ";
	$isql .= "'" . $id_registro . "', ";
	$isql .= "'" . $pagina. "') ";
	
	$r = mysql_query($isql,$conexao) or die("Não foi possível a inserção dos dados ".$isql);

}

?>
