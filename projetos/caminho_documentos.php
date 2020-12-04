<?
/*

		Formul�rio de ESCOLHA DE SUBSISTEMA PARA ESPEC. TEC.	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/rel_escolhaarea.php
		
		data de cria��o: 09/05/2006
		
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
	
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM ".DATABASE.".caminho_docs ";
	$sql .= "WHERE id_os = '". $_POST["os"] . "' ";
	$sql .= "AND caminho_pasta = '". maiusculas($_POST["caminho_pasta"]) . "' ";
	$verify = mysql_query($sql, $db->conexao) or die("Não foi possível fazer a seleção.");
	$regs = mysql_num_rows($verify);
	if ($regs>0)
		{
			?>
			<script>
				alert('Caminho j� cadastrado no banco de dados.');
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO ".DATABASE.".caminho_docs ";
			$incsql .= "(id_os, caminho_pasta) ";
			$incsql .= "VALUES ('" . $_POST["os"] ."', ";
			$incsql .= "'" . maiusculas($_POST["caminho_pasta"]) . "') ";

			$registros = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados");

			?>
			<script>
				alert('Caminho inserido com sucesso.');
			</script>
			<?

		}


}

?>


<html>
<head>
<title>: : . CAMINHO DOCUMENTOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>

function enviar(area, relatorio)
{

	if(area!='')
	{
		document.forms['areas'].action=relatorio;
		document.forms['areas'].submit();
	}

}

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
<form action="<? $PHP_SELF ?>" method="post"  name="areas">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? menu() ?></td>
      </tr>
	  <tr>


      </tr>
      <tr>
        <td>
		  <table width="100%" height="100%" border="0">
            <tr>
              <td width="18%">&nbsp;</td>
              <td width="10%">&nbsp;</td>
              <td width="17%">&nbsp;</td>
              <td width="13%">&nbsp;</td>
              <td width="13%">&nbsp;</td>
              <td width="7%">&nbsp;</td>
              <td width="22%">&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="7" align="center" class="kks_nivel1">caminho para documentos  </td>
              </tr>
            <tr class="btn">
              <td class="btn">&nbsp;</td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
              </font></td>
              <td class="label1">&nbsp;</td>
              <td class="label1">&nbsp;</td>
              <td class="label1">&nbsp;</td>
              <td class="label1">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><span class="label1">OS</span></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="os" class="txt_box" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
                  <?
						  	$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status WHERE ordem_servico_status.os_status NOT LIKE 'ENCERRADA' ORDER BY OS ";
							$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                  <option value="<?= $regs["id_os"] ?>"<? if($regs["id_os"]==$_POST["os"] || $regs["id_os"]==$_SESSION["id_os"]){echo 'selected';} ?>>
                  <?= $regs["os"]. " - " .$regs["descricao"] ?>
                  </option>
                  <?
								}
				  ?>
                </select>
              </font></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><span class="label1">CAMINHO</span></td>
              <td class="btn"><input name="caminho_pasta" type="text" class="txt_box" id="caminho_pasta" value="<?= $_POST["caminho_pasta"] ?>" size="100"></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="btn"><input name="acao" type="hidden" class="btn" value="salvar">
                <input name="Submit" type="submit" class="btn" value="OK"></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="7" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:location.href='menu_eei.php'"></td>
              </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
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
</form>
</center>
</body>
</html>
<?
	$db->fecha_db();
?>

