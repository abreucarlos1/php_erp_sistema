<?php
/*

		Formulário de MENU DE ACESSO AOS PROJETOS	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/escolhaos.php
		
		data de criação: 12/04/2006
		
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
	
	
//include ("../includes/layout.php");
//include ("../includes/layout_dev.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

?>


<html>
<head>
<title>: : . MENU OS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>

function enviar(projeto)
{
	if(projeto!='' || projeto!=0)
	{
		document.forms['frm_os'].projeto.value=projeto;
		document.forms['frm_os'].submit();
	}
	//else
	//{
		//document.forms['projeto'].projeto.value='ADM';
		//document.forms['projeto'].submit();
	//}	

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
<form name="frm_os" id="frm_os" method="post" action="menuprojetos.php">
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
            </tr>
            <tr>
              <td colspan="4" align="center" class="kks_nivel1">ESCOLHA A OS DE TRABALHO</td>
              </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="2" rowspan="3" class="btn">
                <select name="os" id="os" class="txt_box" onkeypress="return keySort(this);">
                  <option value="0">SELECIONE</option>
				  <?php
							
						$sql = "SELECT * FROM ".DATABASE.".OS ";
						$sql .= "WHERE OS BETWEEN 3000 AND 9999 ";
						
						$sql .= "ORDER BY OS ";
						
						$reg = $db->select($sql,'MYSQL');
						
						//$reg = mysqli_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
						
						while ($regs = mysqli_fetch_array($reg))
							{
								?>
								<option value="<?= $regs["id_os"] ?>"<?php if($regs["id_os"]==$_POST["os"] || $regs["id_os"]==$_SESSION["id_os"]){echo 'selected';} ?>>
								<?= sprintf("%05d",$regs["os"]). " - " .$regs["descricao"] ?>
								</option>
								<?php
							}
				  ?>
                </select>
				<input name="projeto" type="hidden" value="">
                <input name="seleciona" type="button" class="btn" id="seleciona" value="Selecionar" onclick="enviar(document.getElementById('os').value);"></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td colspan="4" class="btn"><a href="manual.pdf" target="_blank">MANUAL DE OPERAÇÕES</a></td>
            </tr>
            <tr>
              <td colspan="4" class="btn"><input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:location.href='../inicio.php';"></td>
              </tr>
            <tr>
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


