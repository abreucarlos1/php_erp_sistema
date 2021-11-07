<?php
/*

		Formulário de ESCOLHA DE SUBSISTEMA PARA ESPEC. TEC.	
		
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

include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;


if ($_POST["acao"]=="salvar" && $_POST["emissao"]=='1')
{
	$sql = "SELECT * FROM ".DATABASE.".revisao_cliente ";
	$sql .= "WHERE os = '". $_SESSION["os"] . "' ";
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
				alert('Não é possível emitir nesta revisão.');
			</script>
			<?php
		}
	else
		{

			$isql = "INSERT INTO ".DATABASE.".revisao_cliente ";
			$isql .= "(os, tipodoc, alteracao, id_executante, id_verificador, id_aprovador, versao_documento, data_emissao, qtd_folhas, numero_cliente, numeros_interno, documento ) ";
			$isql .= "VALUES ('" . $_SESSION["os"] ."', '". $_POST["relatorio"] . "', '". maiusculas($_POST["alteracao"]) . "',  ";
			$isql .= "'". $_POST["executante"] . "', '". $_POST["verificador"] . "', '". $_POST["aprovador"] . "', ";
			$isql .= "'". $_POST["versao_documento"] . "', '". date('Y-m-d') . "', '". $_POST["folhas"] . "', '". $_POST["numero_cliente"] . "', '". $_POST["numeros_interno"] . "', ";
			$isql .= "'". $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"].".pdf". "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			$envio_rel = true;

		}
		
	?>
	<script>
		document.forms['areas'].acao.value='';
	</script>
	
	<?php

}

?>


<html>
<head>
<title>: : . RELATÓRIOS ESPECIFICAÇÃO DE HARDWARE . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->

<script>


function enviar_area(area)
{
	
	if((area!='') && (document.forms['areas'].emissao.checked))
	{
		document.forms['areas'].target='_self';
		document.forms['areas'].action='<?= $PHP_SELF ?>';
		document.forms['areas'].acao.value='salvar';
		document.forms['areas'].relatorio.value='lista_entradas_saidas_area';
		document.forms['areas'].submit();
		
	}
	else
	{
		if(area!='')
		{
	
			if(document.forms['areas'].excel.checked)
			{
				document.forms['areas'].acao.value='';
				document.forms['areas'].action='excel_lista_entradas_saidas_area.php';
				document.forms['areas'].submit();			
			}
			else
			{
				document.forms['areas'].acao.value='';
				document.forms['areas'].action='rel_lista_entradas_saidas_area.php';
				document.forms['areas'].submit();
			}	
	

		}
	}

}

function enviar_subsistema(area)
{
	
	if((area!='') && (document.forms['areas'].emissao.checked))
	{
		document.forms['areas'].target='_self';
		document.forms['areas'].action='<?= $PHP_SELF ?>';
		document.forms['areas'].relatorio.value='especificacao_hardware_subsistema';
		document.forms['areas'].acao.value='salvar';
		document.forms['areas'].submit();
		
	}
	else
	{
		if(area!='')
		{
			document.forms['areas'].acao.value='';
			document.forms['areas'].action='rel_especificacao_hardware_subsistema.php';
			document.forms['areas'].submit();
		}
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
              <td width="13%"> </td>
              <td width="13%"> </td>
              <td width="16%"> </td>
              <td width="12%"> </td>
              <td width="14%"> </td>
              <td width="20%"> </td>
              <td width="12%"> </td>
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
              <td colspan="7" align="center" class="kks_nivel1"> LISTA DE ENTRADAS E SA&Iacute;DAS </td>
              </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
              </font><span class="label1">Nº INT</span></td>
              <td class="label1">Nº CLIENTE</td>
              <td class="label1">REVISÃO</td>
              <td class="label1">EMISSÃO PARA CLIENTE</td>
              <td class="label1">EXCEL</td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
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
              <td class="btn"><input name="excel" type="checkbox" id="excel" value="1"></td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td colspan="5" class="label1"><div align="left">ALTERAÇÕES EFETUADAS </div></td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td colspan="5" class="label1"><div align="left">
                <input name="alteracao" type="text" class="txt_box" id="alteracao" value="<?= $_POST["alteracao"] ?>" size="100">
              </div></td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="label1">EXECUTANTE</td>
              <td class="label1">VERIFICADOR</td>
              <td class="label1">APROVADOR</td>
              <td class="label1">Nº FOLHAS</td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="executante" class="txt_box" id="executante">
                  <option value="">SELECIONE</option>
                  <?php
						$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql .= "AND abreviacao NOT LIKE '' ";
						$sql .= "ORDER BY funcionarios.funcionario ";
						
						$regs = $db->select($sql,'MYSQL');
						
							while ($cont = mysqli_fetch_array($regs))
							{
							?>
                  <option value="<?= $cont["id_funcionario"] ?>">
                    <?= $cont["abreviacao"] ?>
                    </option>
                  <?php
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="verificador" class="txt_box" id="verificador">
                  <option value="">SELECIONE</option>
                  <?php
						$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql .= "AND abreviacao NOT LIKE '' ";
						$sql .= "ORDER BY funcionarios.funcionario ";
						
						$regs = $db->select($sql,'MYSQL');
						
							while ($cont = mysqli_fetch_array($regs))
							{
							?>
                  <option value="<?= $cont["id_funcionario"] ?>">
                    <?= $cont["abreviacao"] ?>
                    </option>
                  <?php
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="aprovador" class="txt_box" id="aprovador">
                  <option value="">SELECIONE</option>
                  <?php
						$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
						$sql .= "WHERE situacao NOT LIKE 'DESLIGADO' ";
						$sql .= "AND abreviacao NOT LIKE '' ";
						$sql .= "ORDER BY funcionarios.funcionario ";
						
						$regs = $db->select($sql,'MYSQL');
						
							while ($cont = mysqli_fetch_array($regs))
							{
							?>
                  <option value="<?= $cont["id_funcionario"] ?>">
                    <?= $cont["abreviacao"] ?>
                    </option>
                  <?php
							}									
									
						?>
                </select>
              </font></font></td>
              <td class="btn"><span class="label1">
                <input name="folhas" type="text" class="txt_boxcap" id="folhas" value="<?= $_POST["folhas"] ?>" size="10">
              </span></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="label1">SLOTS</td>
              <td class="label1">RACKS</td>
              <td class="label1">DEVICE</td>
              <td class="btn"><span class="label1">ÁREA</span></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="slots" class="txt_box" id="slots">
                  <option value="">TODOS</option>
                  <?php
					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.racks, Projetos.slots ";
					$sql .= "WHERE area.os = '" .$_SESSION["os"] . "' ";
					$sql .= "AND area.id_area = locais.id_area ";
					$sql .= "AND racks.id_local = locais.id_local ";
					$sql .= "AND racks.id_racks = slots.id_racks ";
					$sql .= "GROUP BY nr_slot ";
					
					$reg = $db->select($sql,'MYSQL');
					
					while ($regs = mysqli_fetch_array($reg))
						{
							?>
                  <option value="<?= $regs["nr_slot"] ?>"<?php if($regs["nr_slot"]==$_POST["slots"]){echo 'selected';} ?>>
                  <?= $regs["nr_slot"] ?>
                  </option>
                  <?php
						}
				  ?>
                </select>
              </font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="racks" class="txt_box" id="racks">
                  <option value="">TODOS</option>
                  <?php
					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.racks ";
					$sql .= "WHERE area.os = '" .$_SESSION["os"] . "' ";
					$sql .= "AND area.id_area = locais.id_area ";
					$sql .= "AND racks.id_local = locais.id_local ";
					$sql .= "GROUP BY nr_rack ";
					
					$reg = $db->select($sql,'MYSQL');
					
					while ($regs = mysqli_fetch_array($reg))
						{
							?>
                  <option value="<?= $regs["nr_rack"] ?>"<?php if($regs["nr_rack"]==$_POST["slots"]){echo 'selected';} ?>>
                  <?= $regs["nr_rack"] ?>
                  </option>
                  <?php
						}
				  ?>
                </select>
              </font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="devices" class="txt_box" id="devices">
                  <option value="">TODOS</option>
                  <?php
					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.racks, Projetos.devices ";
					$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"] . "' ";
					$sql .= "AND area.id_area = locais.id_area ";
					$sql .= "AND racks.id_local = locais.id_local ";
					$sql .= "AND racks.id_devices = devices.id_devices ";
					$sql .= "GROUP BY cd_dispositivo ";
					
					$reg = $db->select($sql,'MYSQL');
					
					while ($regs = mysqli_fetch_array($reg))
						{
							?>
                  <option value="<?= $regs["cd_dispositivo"] ?>"<?php if($regs["cd_dispoitivo"]==$_POST["devices"]){echo 'selected';} ?>>
                  <?= $regs["cd_dispositivo"] ?>
                  </option>
                  <?php
						}
				  ?>
                </select>
              </font></td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="id_area" id="id_area" class="txt_box">
                  <option value="">SELECIONE</option>
                  <?php
					$sql = "SELECT * FROM Projetos.area ";
					$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"] . "' ";
					
					$reg = $db->select($sql,'MYSQL');
					
					while ($regs = mysqli_fetch_array($reg))
						{
							?>
                  <option value="<?= $regs["id_area"] ?>"<?php if($regs["id_area"]==$_POST["id_area"]){echo 'selected';} ?>>
                  <?= $regs["nr_area"]. " - " .$regs["ds_area"]. " " .$regs["ds_divisao"] ?>
                  </option>
                  <?php
						}
				  ?>
                </select>
              </font></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="label1"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td colspan="2" class="btn"><input type="hidden" name="relatorio" id="relatorio" value="">
                <input type="hidden" name="acao" id="acao" value="">
                <input type="button" name="submit" value="OK" onclick="enviar_area(document.forms[0].id_area.value)"></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td colspan="5" class="btn"> </td>
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
<?php
	if($envio_rel)
	{
		$envio_rel = false;
	?>	
		<script>	
		enviar('<?= $_POST["id_subsistema"] ?>','<?= $_POST["relatorio"] ?>', false);
		</script>
	<?php
	}
?>
</center>
</body>
</html>