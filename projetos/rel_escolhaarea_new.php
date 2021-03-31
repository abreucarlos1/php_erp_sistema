<?php
/*

		Formulário de ESCOLHA DE ÁREAS PARA ESPEC. TEC.	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
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

//Função para preenchimento dos comboboxes dinâmicos.
function preencheCombo(combobox_destino, combobox, index)
{

alert(combobox[index].text);

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
	combobox_destino.options[i] = null;
	}
	
	
<?php

$sql_subsis = "SELECT * FROM ".DATABASE.".numero_cliente, ".DATABASE.".numeros_interno, ".DATABASE.".Documentos ";
$sql_subsis .= "WHERE numero_cliente.id_documento = Documentos.id_documento "; 
$sql_subsis .= "AND numero_cliente.id_numcliente = numeros_interno.id_numcliente ";
$sql_subsis .= "AND numeros_interno.os = '".$_SESSION["os"]."' ";

$reg_subsis = mysql_query($sql_subsis,$db->conexao) or die("Não foi possível estabelecer a conexão com o banco de dados.");

	while ($cont_subsis = mysql_fetch_array($reg_subsis))
	{

	
	?>
		alert(combobox[index].text);
		alert('<?= $cont_subsis["documento"] ?>');
	
		if(combobox[index].text=='<?= $cont_subsis["documento"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont_subsis["numero_cliente"] . " - INT - 0" . $cont_subsis["os"].$cont_subsis["sequencia"]." - ".$cont_subsis["documento"]." - ".$cont_subsis["disciplina"] ?>','<?= $cont_subsis["id_numcliente"] ?>');
		}


<?php

  } ?>	
}



function enviar(area, relatorio)
{

	if(area!='' && relatorio!='')
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
<form name="areas" method="post" action="" target="_blank">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"><?php //titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
 	  </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> <?php //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php //menu() ?></td>
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
              <td colspan="4" align="center" class="kks_nivel1">ESCOLHA A ÁREA / RELATÓRIO </td>
              </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="2" class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
				<select name="area" class="txt_box" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
				  <?php
						  	$sql = "SELECT * FROM Projetos.area ";
							$sql .= "WHERE os = '" .$_SESSION["os"] . "' ";
							$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                  					<option value="<?= $regs["id_area"] ?>"<?php if($regs["id_area"]==$_POST["area"]){echo 'selected';} ?>>
                    				<?= $regs["nr_area"]. " " .$regs["ds_area"]. " " .$regs["ds_divisao"] ?>
                    				</option>
                  					<?php
								}
				  ?>
                </select>
                <select name="relatorio" class="txt_box" id="relatorio" onChange="preencheCombo(this.form.numero_cliente, this, this.selectedIndex);" onkeypress="return keySort(this);">
                  <option>SELECIONE</option>
                  <option value="rel_espec_tec_area.php">ESPECIFICAÇÃO TÉCNICA</option>
                  <option value="rel_espec_hardware.php">ESPECIFICAÇÃO DE HARDWARE</option>
				  <option value="listacomp.php">LISTA DE COMPONENTES</option>
                  <option value="listamalhas.php">LISTA DE MALHAS</option>
				</select>
              </font>
                <input type="hidden" name="acao" value="envia">
                <input type="button" name="submit" value="VISUALIZAR" onclick="enviar(document.forms[0].area.value,document.forms[0].relatorio.value)">
                <br></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="2" class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="numero_cliente" class="txt_box" id="numero_cliente" onkeypress="return keySort(this);">
				<option value="">SELECIONE</option>
                </select>
              </font></td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="2" class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="2" class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td colspan="4" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:location.href='menuprojetos.php'"></td>
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
<?php
	$db->fecha_db();
?>

