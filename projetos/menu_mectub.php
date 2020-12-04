<?
/*

		Formul�rio de MENU DE PROJETOS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/menuprojetos.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
*/	
	
//Obt�m os dados do usu�rio
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
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

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>

//Fun��o para redimensionar a janela.
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
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;</td>
      </tr>
	  <tr>


      </tr>
      <tr>
        <td>
		  <table width="100%" height="100%" border="0">
            <tr>
              <td width="7%">&nbsp;</td>
              <td width="27%">&nbsp;</td>
              <td width="27%">&nbsp;</td>
              <td width="32%">&nbsp;</td>
              <td width="7%">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="5"><div align="left" class="kks_nivel1">
                <?= $descos["os"] . " - " . $descos["descricao"] ?>
              </div></td>
              </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"equipamentos_mec.php\" class=\"btnlink\">EQUIPAMENTOS MEC.</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"locais_mec.php\" class=\"btnlink\">LOCAIS MEC.</a>";

				
			?></td>
              <td class="btn"><?

							echo "<a href=\"equipamentos_tub.php\" class=\"btnlink\">EQUIPAMENTOS TUB.</a>";

				?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><?

							echo "<a href=\"materiais.php\" class=\"btnlink\">MATERIAIS</a>";

				?></td>
              <td class="btn"><?

							echo "<a href=\"valvulas.php\" class=\"btnlink\">V&Aacute;LVULAS</a>";

				?></td>
              <td class="btn"><? 

					echo "<a href=\"lista_valvulas.php\" class=\"btnlink\">LISTA DE V&Aacute;LVULAS</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"tipos_suportes.php\" class=\"btnlink\">TIPOS DE SUPORTES</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"suportes_acessorios.php\" class=\"btnlink\">ACESS&Oacute;RIOS DE SUPORTES</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"lista_suportes.php\" class=\"btnlink\">LISTA DE SUPORTES</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><?

							echo "<a href=\"acionamentos.php\" class=\"btnlink\">ACIONAMENTOS</a>";

				?></td>
              <td class="btn"><?

							echo "<a href=\"conexoes.php\" class=\"btnlink\">CONEX&Otilde;ES</a>";

				?></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"especificacao_padrao_topico.php\" class=\"btnlink\">T&Oacute;PICO</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"especificacao_padrao_variavel.php\" class=\"btnlink\">VARI&Aacute;VEL</a>";

				
			?></td>
              <td class="btn"><a href="rel_escolhasub_mectub.php"></a></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td><span class="btn">
                <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();">
              </span></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
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
