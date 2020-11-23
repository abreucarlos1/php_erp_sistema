<?php
// Inicia sessÕes, para assim poder destruÍ-las
session_start();

//Restaurando a sessÃo do administrador do sistema, quando o acesso atual for via acesso especial
if (isset($_SESSION['adminTemp']))
{
	$_SESSION["admin"] 			= $_SESSION['adminTemp']["admin"];
	$_SESSION["login"] 			= $_SESSION['adminTemp']['login'];
	$_SESSION["nivel_atuacao"] 	= $_SESSION['adminTemp']["nivel_atuacao"];					
	$_SESSION["id_usuario"] 	= $_SESSION['adminTemp']["id_usuario"];					
	$_SESSION["id_funcionario"] = $_SESSION['adminTemp']["id_funcionario"];
	$_SESSION["perfil"] 		= $_SESSION['adminTemp']["perfil"];
	$_SESSION["id_setor_aso"] 	= $_SESSION['adminTemp']["id_setor_aso"];	
	$_SESSION["nome_usuario"] 	= stripslashes($_SESSION['adminTemp']["funcionario"]);
	
	unset($_SESSION['adminTemp']);
	header("Location: inicio.php");
}
else
{
	session_destroy();
	header("Location: index.php");
}
?>
