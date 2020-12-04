<?
/*

		Formul�rio de MENU DE PROJETOS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/menuprojetos.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
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
	$_SESSION["os"] = $_POST["projeto"];
}

//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$sql = "SELECT OS, descricao FROM ".DATABASE.".OS WHERE OS = '" .$_SESSION["os"]. "' ";
$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
$descos = mysql_fetch_array($reg);

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
        <td bgcolor="#BECCD9" align="left"><? //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"><? //titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
 	  </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? //menu() ?></td>
      </tr>
	  <tr>


      </tr>
      <tr>
        <td>
		  <table width="100%" height="100%" border="0">
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="4"><div align="left" class="kks_nivel1">
                <?= $descos["os"] . " - " . $descos["descricao"] ?>
              </div></td>
              </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><a href="menu_geral.php">GERAL</a></td>
              <td class="btn"><a href="menu_processo.php">PROCESSO</a></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn"><a href="menu_mectub.php">MEC/TUB</a></td>
              <td class="btn"><a href="menu_eei.php">EEI</a></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td align="center" class="btn">&nbsp;</td>
              <td align="center" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
				  <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn"><a href="listacomp.php" target="_blank"></a></td>
              <td class="btn"><a href="rel_espec_tec.php" target="_blank"></a></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn"><a href="rel_escolhaarea.php"></a></td>
              <td class="btn"><a href="rel_escolhasub.php"></a></td>
              <td class="btn"><a href="rel_escolhamalhas.php"></a></td>
              <td class="btn"><a href="rel_escolhacomponentes.php"></a></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td><span class="btn">
                <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='escolhaos.php';">
              </span></td>
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
<?
	$db->fecha_db();
?>

