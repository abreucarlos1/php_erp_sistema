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

							echo "<a href=\"malhas.php\" class=\"btnlink\">MALHAS</a>";

				?></td>
              <td class="btn"><?

							echo "<a href=\"componentes.php\" class=\"btnlink\">COMPONENTES</a>";

				?></td>
              <td class="btn"><? 

					echo "<a href=\"locais_eei.php\" class=\"btnlink\">LOCAIS EEI</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"equipamentos_eei.php\" class=\"btnlink\">EQUIPAMENTOS EEI</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"cabos_finalidades.php\" class=\"btnlink\">FINALIDADES DE CABOS</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"cabos_tipos.php\" class=\"btnlink\">TIPOS DE CABOS</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"cabos.php\" class=\"btnlink\">CABOS</a>";

				
			?></td>
              <td class="btn"><?

					echo "<a href=\"funcao.php\" class=\"btnlink\">FUN&Ccedil;&Atilde;O</a>";

				
			?></td>
              <td class="btn"><?

					echo "<a href=\"dispositivos.php\" class=\"btnlink\">DISPOSITIVOS</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"tipo.php\" class=\"btnlink\">TIPO</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"especificacao_padrao_topico.php\" class=\"btnlink\">T&Oacute;PICO</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"especificacao_padrao_variavel.php\" class=\"btnlink\">VARI&Aacute;VEL</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"especificacao_padrao.php\" class=\"btnlink\">ESPEC. PADR&Atilde;O</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"especificacao_tecnica.php\" class=\"btnlink\">ESPEC. T&Eacute;CNICA</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"slots.php\" class=\"btnlink\">SLOTS</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><? 

					echo "<a href=\"racks.php\" class=\"btnlink\">RACKS</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"cartoes.php\" class=\"btnlink\">CART&Otilde;ES</a>";

				
			?></td>
              <td class="btn"><? 

					echo "<a href=\"devices.php\" class=\"btnlink\">DEVICES</a>";

				
			?></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn">&nbsp;
			  <?

					echo "<a href=\"processo.php\" class=\"btnlink\">PROCESSO</a>";

				
			?></td>
              <td class="btn"><?
			   
					echo "<a href=\"isolacao_cabo.php\" class=\"btnlink\">ISOLA��O CABOS</a>";

				
			?>			</td>
              <td class="btn">&nbsp;</td>
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
