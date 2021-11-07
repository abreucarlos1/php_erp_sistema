<?php
/*

		Formulário de ESCOLHA DE ÁREAS PARA ESPEC. TEC.	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/rel_escolhaarea.php
		
		data de criação: 09/05/2006
		
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
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

?>


<html>
<head>
<title>: : . ESPECIFICAÇÃO TÉCNICA / ÁREA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<!-- Javascript para declaração de variáveis / checagem do estilo - MAC/PC -->

<!-- Javascript para envio dos dados através do método GET -->
<script>

function enviar(area, relatorio)
{

	if(area!='')
	{
		document.forms['frm_areas'].action=relatorio;
		document.forms['frm_areas'].submit();
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
<form name="frm_areas" method="post" action="" target="_blank">
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
              <td width="13%"> </td>
              <td width="17%"> </td>
              <td width="20%"> </td>
              <td width="13%"> </td>
              <td width="13%"> </td>
              <td width="9%"> </td>
              <td width="15%"> </td>
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
              <td colspan="7" align="center" class="kks_nivel1">ESCOLHA A ÁREA / RELATÓRIO </td>
              </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="label1">ÁREA</td>
              <td class="label1">DOCUMENTO</td>
              <td class="label1">N&deg; INT </td>
              <td class="label1">N&deg; CLIENTE </td>
              <td class="label1">REVISÃO</td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
				<select name="area" id="area" class="txt_box" >
                  <option value="">SELECIONE</option>
				  <?php
						  	$sql = "SELECT * FROM Projetos.area ";
							$sql .= "WHERE os = '" .$_SESSION["os"] . "' ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                  					<option value="<?= $regs["id_area"] ?>"<?php if($regs["id_area"]==$_POST["area"]){echo 'selected';} ?>>
                    				<?= $regs["nr_area"]. " " .$regs["ds_area"]. " " .$regs["ds_divisao"] ?>
                    				</option>
                  					<?php
								}
				  ?>
                </select>
              </font><br></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="relatorio" class="txt_box" id="relatorio" >
                  <option value="rel_espec_tec_area.php">ESPECIFICAÇÃO TÉCNICA</option>
                  <option value="rel_lista_componentes_area.php">LISTA DE COMPONENTES</option>
                  <option value="rel_espec_hardware.php">ESPECIFICAÇÃO DE HARDWARE</option>
                  <option value="listamalhas.php">LISTA DE MALHAS</option>
                                </select>
              </font></td>
              <td class="btn"><span class="label1">
                <input name="numeros_interno" type="text" class="txt_boxcap" id="numeros_interno" value="<?= $_POST["numeros_interno"] ?>" size="30">
              </span></td>
              <td class="btn"><span class="label1">
                <input name="numero_cliente" type="text" class="txt_boxcap" id="numero_cliente" value="<?= $_POST["numero_cliente"] ?>" size="30">
              </span></td>
              <td class="btn"><span class="label1">
                <input name="versao_documento" type="text" class="txt_boxcap" id="versao_documento" value="<?= $_POST["versao_documento"] ?>" size="10">
              </span></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="5" class="btn"><input type="hidden" name="acao" id="acao" value="envia">
                <input type="button" name="submit" value="OK" onclick="enviar(document.forms[0].area.value,document.forms[0].relatorio.value)"></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="5" class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td colspan="7" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:history.back();"></td>
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