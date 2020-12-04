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

if ($_POST["acao"]=="salvar" && $_POST["emissao"]=='1')
{
	$sql = "SELECT * FROM ".DATABASE.".revisao_cliente ";
	$sql .= "WHERE id_os = '". $_SESSION["id_os"] . "' ";
	$sql .= "AND tipodoc = '". $_POST["relatorio"] . "' ";
	$sql .= "AND versao_documento = '". $_POST["versao_documento"] . "' ";
	$sql .= "AND numero_cliente = '". $_POST["numero_cliente"] . "' ";
	$sql .= "AND numeros_interno = '". $_POST["numeros_interno"] . "' ";
	$sql .= "AND documento = '" . $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"].".pdf". "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('N�o � poss�vel emitir nesta revis�o.');
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO ".DATABASE.".revisao_cliente ";
			$incsql .= "(id_os, tipodoc, alteracao, id_executante, id_verificador, id_aprovador, versao_documento, data_emissao, qtd_folhas, numero_cliente, numeros_interno, documento ) ";
			$incsql .= "VALUES ('" . $_SESSION["id_os"] ."', '". $_POST["relatorio"] . "', '". maiusculas($_POST["alteracao"]) . "',  ";
			$incsql .= "'". $_POST["executante"] . "', '". $_POST["verificador"] . "', '". $_POST["aprovador"] . "', ";
			$incsql .= "'". $_POST["versao_documento"] . "', '". date('Y-m-d') . "', '". $_POST["folhas"] . "', '". $_POST["numero_cliente"] . "', '". $_POST["numeros_interno"] . "', ";
			$incsql .= "'". $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"].".pdf". "') ";

			$registros = $db->insert($incsql,'MYSQL');
			
			$envio_rel = true;

		}
		
	?>
	<script>
		document.forms['areas'].acao.value='';
	</script>
	
	<?

}

?>


<html>
<head>
<title>: : . RELAT&Oacute;RIOS / &Aacute;REA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->

<script>


function enviar_area(area)
{
	
	if((area!='') && (document.forms['areas'].emissao.checked))
	{
		document.forms['areas'].target='_self';
		document.forms['areas'].action='<?= $PHP_SELF ?>';
		document.forms['areas'].acao.value='salvar';
		document.forms['areas'].submit();
		
	}
	else
	{
		if(area!='')
		{
			document.forms['areas'].acao.value='';
			document.forms['areas'].action='rel_espec_tec_area.php';
			document.forms['areas'].submit();
		}
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
<form name="frm_areas" id="frm_areas" method="post" action="<?= $PHP_SELF ?>" target="_blank">
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
              <td width="14%">&nbsp;</td>
              <td width="14%">&nbsp;</td>
              <td width="17%">&nbsp;</td>
              <td width="13%">&nbsp;</td>
              <td width="20%">&nbsp;</td>
              <td width="7%">&nbsp;</td>
              <td width="15%">&nbsp;</td>
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
              <td colspan="7" align="center" class="kks_nivel1"> RELAT&Oacute;RIO DE ESPECIFICA&Ccedil;&Atilde;O T&Eacute;CNICA </td>
              </tr>
            <tr class="btn">
              <td class="btn">&nbsp;</td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
              </font><span class="label1">N&ordm; DVM</span></td>
              <td class="label1">N&ordm; CLIENTE</td>
              <td class="label1">revis&Atilde;O</td>
              <td class="label1">EMISS&Atilde;O PARA CLIENTE</td>
              <td class="label1">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><div align="center"><span class="label1">
                <input name="numeros_interno" type="text" class="txt_boxcap" id="numeros_interno" value="<?= $_POST["numeros_interno"] ?>" size="30">
              </span></div></td>
              <td class="btn"><div align="center"><span class="label1">
                <input name="numero_cliente" type="text" class="txt_boxcap" id="numero_cliente" value="<?= $_POST["numero_cliente"] ?>" size="30">
              </span></div></td>
              <td class="btn"><span class="label1">
                <input name="versao_documento" type="text" class="txt_boxcap" id="versao_documento" value="<?= $_POST["versao_documento"] ?>" size="10">
              </span></td>
              <td class="btn"><input name="emissao" type="checkbox" id="emissao" value="1"></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr class="btn">
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="label1"><div align="left">ALTERA&Ccedil;&Otilde;ES EFETUADAS </div></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr class="btn">
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="label1"><div align="left">
                <input name="alteracao" type="text" class="txt_box" id="alteracao" value="<?= $_POST["alteracao"] ?>" size="100">
              </div></td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr class="btn">
              <td class="btn">&nbsp;</td>
              <td class="label1">EXECUTANTE</td>
              <td class="label1">VERIFICADOR</td>
              <td class="label1">APROVADOR</td>
              <td class="label1">N&ordm; FOLHAS</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="executante" class="txt_box" id="executante">
                  <option value="">SELECIONE</option>
                  <?
						$sql_funcionario = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql_funcionario .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql_funcionario .= "ORDER BY funcionarios.funcionario ";
						
						$reg_funcionario = $db->select($sql_funcionario,'MYSQL');
						
							while ($cont_funcionario = mysqli_fetch_array($reg_funcionario))
							{
							?>
                  <option value="<?= $cont_funcionario["id_funcionario"] ?>">
                    <?= $cont_funcionario["abreviacao"] ?>
                    </option>
                  <?
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="verificador" class="txt_box" id="verificador">
                  <option value="">SELECIONE</option>
                  <?
						$sql_funcionario = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql_funcionario .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql_funcionario .= "ORDER BY funcionarios.funcionario ";
						
						$reg_funcionario = $db->select($sql_funcionario,'MYSQL');
						
							while ($cont_funcionario = mysqli_fetch_array($reg_funcionario))
							{
							?>
                  <option value="<?= $cont_funcionario["id_funcionario"] ?>">
                    <?= $cont_funcionario["abreviacao"] ?>
                    </option>
                  <?
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="aprovador" class="txt_box" id="aprovador">
                  <option value="">SELECIONE</option>
                  <?
						$sql_funcionario = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql_funcionario .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql_funcionario .= "ORDER BY funcionarios.funcionario ";
						
						$reg_funcionario = $db->select($sql_funcionario,'MYSQL');
						
							while ($cont_funcionario = mysqli_fetch_array($reg_funcionario))
							{
							?>
                  <option value="<?= $cont_funcionario["id_funcionario"] ?>">
                    <?= $cont_funcionario["abreviacao"] ?>
                    </option>
                  <?
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><span class="label1">
                <input name="folhas" type="text" class="txt_boxcap" id="folhas" value="<?= $_POST["folhas"] ?>" size="10">
              </span></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><span class="label1">&Aacute;REA</span></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="id_area" id="id_area" class="txt_box">
                  <option value="">SELECIONE</option>
                  <?
					$sql = "SELECT * FROM Projetos.area ";
					$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"] . "' ";
					
					$reg = $db->select($sql,'MYSQL');
					
					while ($regs = mysqli_fetch_array($reg))
						{
							?>
                  <option value="<?= $regs["id_area"] ?>"<? if($regs["id_area"]==$_POST["id_area"]){echo 'selected';} ?>>
                  <?= $regs["nr_area"]. " - " .$regs["ds_area"]. " " .$regs["ds_divisao"] ?>
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
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td class="btn"><input type="hidden" name="acao" id="acao" value="">
			  	<input type="hidden" name="disciplina" id="disciplina" value="INSTRUMENTA��O">
                <input type="button" name="Submit" value="OK"></td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td class="btn">&nbsp;</td>
              <td colspan="5" class="btn">&nbsp;</td>
              <td class="btn">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="7" class="btn"><input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:history.back();"></td>
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
<?
	if($envio_rel)
	{
		$envio_rel = false;
	?>	
		<script>	
		enviar('<?= $_POST["id_subsistema"] ?>','<?= $_POST["relatorio"] ?>', false);
		</script>
	<?
	}
?>
</center>
</body>
</html>