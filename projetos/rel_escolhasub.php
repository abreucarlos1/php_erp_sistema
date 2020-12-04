<?
/*

		Formul�rio de ESCOLHA DE SUBSISTEMA PARA ESPEC. TEC.	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/rel_escolhaarea.php
		
		data de cria��o: 09/05/2006
		
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
	
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

?>


<html>
<head>
<title>: : . ESPECIFICA&Ccedil;&Atilde;O T&Eacute;CNICA / &Aacute;REA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>

function enviar(area, relatorio)
{

	if(area!='')
	{
		document.forms['frm_areas'].action=relatorio;
		document.forms['frm_areas'].submit();
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
<form name="frm_areas" id="frm_areas" method="post" action="" target="_blank">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"></td>
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
            </tr>
            <tr>
              <td colspan="4" align="center" class="kks_nivel1">ESCOLHA O SUBSISTEMA / RELAT&Oacute;RIO </td>
              </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td colspan="2" rowspan="3" class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
                <select name="id_subsistema" id="id_subsistema" class="txt_box">
                  <option value="">SELECIONE</option>
                  <?
						  	$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
							$sql .= "WHERE area.os = '" .$_SESSION["os"] . "' ";
							$sql .= "AND area.id_area = subsistema.id_area ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                  <option value="<?= $regs["id_subsistema"] ?>"<? if($regs["id_subsistema"]==$_POST["id_subsistema"]){echo 'selected';} ?>>
                  <?= $regs["nr_area"]. " - " .$regs["nr_subsistema"]. " " .$regs["subsistema"] ?>
                  </option>
                  <?
								}
				  ?>
                </select>
                <select name="relatorio" class="txt_box" id="relatorio" >
                  <option value="rel_espec_tec_subsistema.php">ESPECIFICA&Ccedil;&Atilde;O T&Eacute;CNICA</option>
                  <option value="rel_lista_cabos.php">LISTA DE CABOS</option>
				  <option value="rel_lista_valvulas.php">LISTA DE V�LVULAS</option>
				  <option value="rel_lista_suportes.php">LISTA DE SUPORTES</option>
                </select>
              </font><br>
			  <input type="hidden" name="acao" id="acao" value="envia">
              <input type="button" name="Submit" value="OK" onClick="enviar(document.forms[0].id_subsistema.value,document.forms[0].relatorio.value)"></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="4" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:history.back();"></td>
              </tr>
            <tr>
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