<?php
function logs($banco, $conexao, $usuario, $acao,$db='', $id_registro=0)
{
	session_start();
/*
//	LOGA AS AÇOES DOS USUÁRIOS

$banco -> banco de inserção dos dados
$conexao -> link da variavel $conexao
$usuario -> nome do usuario
$acao -> descrição da ação
$db -> banco alvo
$id_registro -> registro afetado

*/
	$formulario = explode("/",$_SERVER["PHP_SELF"]);
	
	$pagina = $formulario[count($formulario)-1];
	
	$incsql = "INSERT INTO logs.".$banco." ";
	$incsql .= " (ip, usuario, data, hora, acao, db, id_registro, formulario) ";
	$incsql .= "VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', ";
	$incsql .= "'" . $usuario . "', ";
	$incsql .= "'" . date('Y-m-d') . "', ";
	$incsql .= "'" . date('H:i:s') . "', ";
	$incsql .= "'" . $acao . "', ";
	$incsql .= "'" . $db . "', ";
	$incsql .= "'" . $id_registro . "', ";
	$incsql .= "'" . $pagina. "') ";
	
	$r = mysql_query($incsql,$conexao) or die("Não foi possível a inserção dos dados".$incsql);

}

?>
