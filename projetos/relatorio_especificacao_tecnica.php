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
				alert('Não é possível emitir nesta revisão.');
			</script>
			<?php
		}
	else
		{

			$isql = "INSERT INTO ".DATABASE.".revisao_cliente ";
			$isql .= "(id_os, tipodoc, alteracao, id_executante, id_verificador, id_aprovador, versao_documento, data_emissao, qtd_folhas, numero_cliente, numeros_interno, documento ) ";
			$isql .= "VALUES ('" . $_SESSION["id_os"] ."', '". $_POST["relatorio"] . "', '". maiusculas($_POST["alteracao"]) . "',  ";
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
<title>: : . RELATÓRIOS ESPECIFICAÇÃO TÉCNICA . : :</title>
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
		document.forms['areas'].relatorio.value='especificacao_tecnica_area';
		document.forms['areas'].submit();
		
	}
	else
	{
		if(area!='')
		{
			document.forms['areas'].acao.value='';
			document.forms['areas'].relatorio.value='especificacao_tecnica_area';
			document.forms['areas'].action='rel_espec_tec_area.php';
			document.forms['areas'].submit();
		}
	}

}

function enviar_subsistema(area)
{
	
	if((area!='') && (document.forms['areas'].emissao.checked))
	{
		document.forms['areas'].target='_self';
		document.forms['areas'].action='<?= $PHP_SELF ?>';
		document.forms['areas'].relatorio.value='especificacao_tecnica_subsistema';
		document.forms['areas'].acao.value='salvar';
		document.forms['areas'].submit();
		
	}
	else
	{
		if(area!='')
		{
			document.forms['areas'].acao.value='';
			document.forms['areas'].relatorio.value='especificacao_tecnica_subsistema';
			document.forms['areas'].action='rel_espec_tec_subsistema.php';
			document.forms['areas'].submit();
		}
	}

}

function abrir_espec_area(area)
{
	
	if(area!='')
	{	
		document.forms['areas'].relatorio.value='especificacao_tecnica_area';
		document.forms['areas'].target='_self';
		document.forms['areas'].acao.value='';
		document.forms['areas'].action='rel_especificacao_tecnica_disp_area.php';
		document.forms['areas'].submit();
	}
}

function abrir_espec_subsistema(area)
{
	
	if(area!='')
	{	
		document.forms['areas'].relatorio.value='especificacao_tecnica_subsistema';
		document.forms['areas'].target='_self';
		document.forms['areas'].acao.value='';
		document.forms['areas'].action='rel_especificacao_tecnica_disp_subsistema.php';
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
              <td width="14%"> </td>
              <td width="14%"> </td>
              <td width="17%"> </td>
              <td width="13%"> </td>
              <td width="20%"> </td>
              <td width="7%"> </td>
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
              <td colspan="7" align="center" class="kks_nivel1"> RELATÓRIO DE ESPECIFICAÇÃO TÉCNICA </td>
              </tr>
            <tr class="btn">
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <!-- onChange="enviar(this[selectedIndex].value)" -->
              </font><span class="label1">Nº INT</span></td>
              <td class="label1">Nº CLIENTE</td>
              <td class="label1">REVISÃO</td>
              <td class="label1">EMISSÃO PARA CLIENTE</td>
              <td class="label1"> </td>
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
              <td class="btn"> </td>
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
              <td class="label1">DISCIPLINA</td>
              <td class="btn"><span class="label1">ÁREA</span></td>
              <td class="label1">SUBSISTEMA</td>
              <td class="label1"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><select name="disciplina" class="txt_box"  id="disciplina" onkeypress="return keySort(this);">
                <option value="">GERAL</option>
                <?php
						  	
							$sql = "SELECT * FROM ".DATABASE.".setores WHERE setor NOT LIKE 'ARQUIVO TÉCNICO' AND setor NOT LIKE 'ADMINISTRATIVO' ";
							$sql .= " AND setor NOT LIKE 'TECNOLOGIA DE INFORMAÇÃO' AND setor NOT LIKE 'COMERCIAL' AND setor NOT LIKE 'COORDENAÇÃO' ";
							$sql .= " AND setor NOT LIKE 'CIVIL' AND setor NOT LIKE 'GERAL' AND setor NOT LIKE 'ENGENHARIA BÁSICA/PROCESSO' ";
							$sql .= " AND setor NOT LIKE 'FINANCEIRO' AND setor NOT LIKE 'GESTÃO DE QUALIDADE' AND setor NOT LIKE 'PLANEJAMENTO' ORDER BY setor ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os países
							while ($cont = mysqli_fetch_array($registro))
								{
									?>
                <option value="<?= $cont["id_setor"] ?>"<?php if($cont["id_setor"]==$_POST["disciplina"]){ echo 'selected'; } ?>>
                <?= $cont["setor"] ?>
                </option>
                <?php
								}
						
						?>
              </select></td>
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
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="id_subsistema" id="id_subsistema" class="txt_box">
                  <option value="">SELECIONE</option>
                  <?php
						  	$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
							$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"] . "' ";
							$sql .= "AND area.id_area = subsistema.id_area ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                  <option value="<?= $regs["id_subsistema"] ?>"<?php if($regs["id_subsistema"]==$_POST["id_subsistema"]){echo 'selected';} ?>>
                  <?= $regs["nr_area"]. " - " .$regs["nr_subsistema"]. " " .$regs["subsistema"] ?>
                  </option>
                  <?php
								}
				  ?>
                </select>
              </font></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"><span class="label1">dispositivos</span></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"><font size="2" face="Arial, Helvetica, sans-serif">
                <select name="ds_dispositivo" class="txt_box" id="ds_dispositivo">
                  <option value="">TODOS</option>
                  <?php
						  
						  	$sql_dispositivos = "SELECT * FROM Projetos.dispositivos ";
//	COMENTADO				$sql_dispositivos .= "GROUP BY dispositivo, ds_dispositivo ";	
							$sql_dispositivos .= "GROUP BY ds_dispositivo ";	

							$reg_dispositivos = $db->select($sql_dispositivos,'MYSQL');
							
							while ($regs_dispositivos = mysqli_fetch_array($reg_dispositivos))
								{
									?>
                  <option value="<?= $regs_dispositivos["ds_dispositivo"] ?>"<?php if($regs_dispositivos["ds_dispositivo"]==$_POST["ds_dispositivo"]){echo 'selected';} ?>>
                  <?php
/* COMENTADO				 
				  if($regs_dispositivos["dispositivo"])
				  {
				  	$dispositivo = $regs_dispositivos["dispositivo"]. " - ";
				  }
				  else
				  {
				  	$dispositivo = "";
				  }
COMENTADO */				
				  ?>
				  <?= $regs_dispositivos["ds_dispositivo"] ?>
                  </option>
                  <?php
								}
				  ?>
                </select>
              </font></td>
              <td class="btn"> </td>
              <td class="btn"> </td>
              <td class="btn"> </td>
            </tr>
            <tr>
              <td class="btn"> </td>
              <td class="btn"><input type="hidden" name="relatorio" id="relatorio" value=""></td>
              <td class="btn"><input type="hidden" name="acao" id="acao" value="">
                <input name="submit" type="button" class="btn" onclick="enviar_area(document.forms[0].id_area.value)" value="OK">
                <input name="submit" type="button" class="btn" onclick="abrir_espec_area(document.forms[0].id_area.value)" value="ESCOLHER COMPONENTES"></td>
              <td class="btn"><input name="submit" type="button" class="btn" onclick="enviar_subsistema(document.forms[0].id_subsistema.value)" value="OK">
                <br>
                <input name="submit" type="button" class="btn" onclick="abrir_espec_subsistema(document.forms[0].id_subsistema.value)" value="ESCOLHER COMPONENTES"></td>
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
		
		if($_POST["relatorio"]=='especificacao_tecnica_area')
		{
			?>	
				<script>	
				enviar_area('<?= $_POST["id_area"] ?>');
				</script>
			<?php			
		}
		if($_POST["relatorio"]=='especificacao_tecnica_subsistema')
		{
			?>	
				<script>	
				enviar_subsistema('<?= $_POST["id_subsistema"] ?>');
				</script>
			<?php			
		}

	}
?>
</center>
</body>
</html>