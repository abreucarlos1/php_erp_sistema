<?php
/*

		Formulário de MENU DE PROJETOS	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/menuprojetos.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016
		
*/	
	
//Obtém os dados do usuário
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usuário não logado! Redireciona para a página de login
	header("Location: ../index.php");
	exit;
}


if ($_POST["projeto"])
{
	$_SESSION["id_os"] = $_POST["projeto"];
}

//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;


$sql = "SELECT OS, descricao FROM ".DATABASE.".OS WHERE id_os = '" .$_SESSION["id_os"]. "' ";

$reg = $db->select($sql,'MYSQL');

$descos = mysqli_fetch_array($reg);

$_SESSION["OSdesc"] = $descos["descricao"];

?>


<html>
<head>
<title>: : . MENU DE PROJETOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>

//Função para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: none;
}
a:active {
	text-decoration: none;
}
-->
</style></head>
<body  class="body">
<center>
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
	  <tr>
      </tr>
      <tr>
        <td>
		  <table width="100%" height="100%" border="0">
            <tr>
              <td width="3%"> </td>
              <td width="46%"> </td>
              <td width="42%"> </td>
              <td width="9%"> </td>
            </tr>
            <tr>
              <td colspan="4"><div align="left" class="kks_nivel1">
                <?= $descos["os"] . " - " . $descos["descricao"] ?>
              </div></td>
              </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_componentes.php">LISTA DE COMPONENTES </a><a href="menu_geral.php"></a></td>
              <td class="btn"><a href="relatorio_especificacao_tecnica.php"></a><a href="relatorio_lista_componentes_processo.php">LISTA DE COMPONENTES / PROCESSO </a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_malhas.php">LISTA DE MALHAS</a></td>
              <td class="btn"><a href="relatorio_lista_hardware.php">LISTA DE HARDWARE </a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_especificacao_hardware.php">ESPECIFICAÇÃO DE HARDWARE </a></td>
              <td class="btn"><a href="menu_eei.php"></a><a href="relatorio_lista_entradas_saidas_area.php">LISTA DE ENTRADAS E SA&Iacute;DAS </a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="rel_lista_subsistemas.php" target="_blank">LISTA DE SUBSISTEMAS</a></td>
              <td class="btn"><a href="relatorio_lista_cabos.php">LISTA DE CABOS </a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_tipo_cabos.php">LISTA CABOS / LOCAIS </a></td>
              <td class="btn"><a href="relatorio_especificacao_tecnica.php">ESPECIFICAÇÃO TÉCNICA</a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_cabos_bornes.php">LISTA CABOS / BORNES </a></td>
              <td class="btn"><a href="relatorio_lista_cabos_bornes_locais.php" target="_blank">LISTA CABOS / BORNES / LOCAIS</a> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_tipo_cabos_formacao.php" target="_blank">LISTA CABOS / LOCAIS / FORMAÇÃO</a> </td>
              <td class="btn"><a href="rel_espec_padrao_total.php">ESPECIFICAÇÃO PADRÃO DOS DISPOSITIVOS </a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td align="center" class="btn"> </td>
              <td align="center" class="btn"><a href="relatorio_lista_linhas.php">LISTA DE LINHAS </a></td>
              <td class="btn"><a href="relatorio_lista_motores.php">LISTA DE MOTORES</a></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
            </tr>
            <tr>
              <td><span class="btn">
                <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();">
              </span></td>
              <td> </td>
              <td> </td>
              <td> </td>
            </tr>
          </table>
		</td>
      </tr>
      
    </table>
	</td>
  </tr>
</table>
</center>
</body>
</html>