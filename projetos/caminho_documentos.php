<?php
/*

		Formulário de ESCOLHA DE SUBSISTEMA PARA ESPEC. TEC.	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/rel_escolhaarea.php
		
		data de criação: 09/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
*/	
	
//Obtém os dados do usuário
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usuário não logado! Redireciona para a página de login
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
				alert('Caminho já cadastrado no banco de dados.');
			</script>
			<?php
		}
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO ".DATABASE.".caminho_docs ";
			$isql .= "(id_os, caminho_pasta) ";
			$isql .= "VALUES ('" . $_POST["os"] ."', ";
			$isql .= "'" . maiusculas($_POST["caminho_pasta"]) . "') ";

			$registros = mysql_query($isql,$db->conexao) or die("Não foi possível a inserção dos dados");

			?>
			<script>
				alert('Caminho inserido com sucesso.');
			</script>
			<?php

		}


}

?>


<html>
<head>
<title>: : . CAMINHO DOCUMENTOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>

function enviar(area, relatorio)
{

	if(area!='')
	{
		document.forms['areas'].action=relatorio;
		document.forms['areas'].submit();
	}

}

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
<form action="<?php $PHP_SELF ?>" method="post"  name="areas">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> <?php formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php menu() ?></td>
      </tr>
	  <tr>


      </tr>
      <tr>
        <td>
		  <table width="100%" height="100%" border="0">
            <tr>
              <td width="18%"> </td>
              <td width="10%"> </td>
              <td width="17%"> </td>
              <td width="13%"> </td>
              <td width="13%"> </td>
              <td width="7%"> </td>
              <td width="22%"> </td>
            </tr>
            <tr>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
            </tr>
            <tr>
              <td colspan="7" align="center" class="kks_nivel1">caminho para documentos  </td>
              </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
              </font></td>
              <td class="label1"> </td>
              <td class="label1"> </td>
              <td class="label1"> </td>
              <td class="label1"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><span class="label1">OS</span></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="os" class="txt_box" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
                  <?php
						  	$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status WHERE ordem_servico_status.os_status NOT LIKE 'ENCERRADA' ORDER BY OS ";
							$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                  <option value="<?= $regs["id_os"] ?>"<?php if($regs["id_os"]==$_POST["os"] || $regs["id_os"]==$_SESSION["id_os"]){echo 'selected';} ?>>
                  <?= $regs["os"]. " - " .$regs["descricao"] ?>
                  </option>
                  <?php
								}
				  ?>
                </select>
              </font></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><span class="label1">CAMINHO</span></td>
              <td class="btn"><input name="caminho_pasta" type="text" class="txt_box" id="caminho_pasta" value="<?= $_POST["caminho_pasta"] ?>" size="100"></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="5" class="btn"><input name="acao" type="hidden" class="btn" value="salvar">
                <input name="submit" type="submit" class="btn" value="OK"></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="5" class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td colspan="7" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:location.href='menu_eei.php'"></td>
              </tr>
            <tr>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
            </tr>
            <tr>
              <td> </td>
              <td> </td>
              <td> </td>
              <td> </td>
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
</form>
</center>
</body>
</html>
<?php
	$db->fecha_db();
?>

