<?
/*
		Formul�rio de Locais OUTROS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/locais_outros.php
		
		data de cria��o: 05/04/2006
		
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
include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;

$sql1 = "SELECT * FROM ".DATABASE.".setores ";
$sql1 .= "WHERE setor = 'EL�TRICA' ";

$regis = $db->select($sql1,'MYSQL');

$disciplina = mysqli_fetch_array($regis);


//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND ds_complemento = '" . maiusculas($_POST["ds_complemento"]) . "' ";
	$sql .= "AND nr_elevacao = '" . $_POST["nr_elevacao"] . "' ";
	$sql .= "AND nr_eixox = '" . $_POST["nr_eixox"] . "' ";
	$sql .= "AND nr_eixoy = '" . $_POST["nr_eixoy"] . "' ";
	$sql .= "AND ds_abrigado = '" . $_POST["ds_abrigado"] . "' ";
	$sql .= "AND id_classearea = '" . $_POST["id_classearea"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local j� inserido no banco.');
		</script>
		<?
	}
	else
	{

		$sql = "UPDATE Projetos.locais SET ";
		$sql .= "id_area = '" . $_POST["id_area"] . "', ";
		$sql .= "id_equipamento = '" . $_POST["id_equipamento"] . "', ";
		$sql .= "id_disciplina = '" . $disciplina["id_setor"] . "', ";
		$sql .= "nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "', ";
		$sql .= "ds_complemento = '" . maiusculas($_POST["ds_complemento"]) . "', ";
		$sql .= "nr_elevacao = '" . $_POST["nr_elevacao"] . "', ";
		$sql .= "nr_eixox = '" . $_POST["nr_eixox"] . "', ";
		$sql .= "nr_eixoy = '" . $_POST["nr_eixoy"] . "', ";
		$sql .= "ds_abrigado = '" . $_POST["ds_abrigado"] . "', ";
		$sql .= "id_classearea = '" . $_POST["id_classearea"] . "' ";
	
		$sql .= "WHERE id_local = '" . $_POST["id_local"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');

	}

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND ds_complemento = '" . maiusculas($_POST["ds_complemento"]) . "' ";
	$sql .= "AND nr_elevacao = '" . $_POST["nr_elevacao"] . "' ";
	$sql .= "AND nr_eixox = '" . $_POST["nr_eixox"] . "' ";
	$sql .= "AND nr_eixoy = '" . $_POST["nr_eixoy"] . "' ";
	$sql .= "AND ds_abrigado = '" . $_POST["ds_abrigado"] . "' ";
	$sql .= "AND id_classearea = '" . $_POST["id_classearea"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local j� inserido no banco.');
		</script>
		<?
	}
	else
	{

		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.locais ";
		$incsql .= "(id_area, id_disciplina, id_equipamento, nr_sequencia, ds_complemento, nr_elevacao, nr_eixox, nr_eixoy, ds_abrigado, id_classearea) VALUES (";
		$incsql .= "'" . $_POST["id_area"] . "', ";
		$incsql .= "'" . $disciplina["id_setor"] . "', ";
		$incsql .= "'" . $_POST["id_equipamento"] . "', ";
		$incsql .= "'" . maiusculas($_POST["nr_sequencia"]) . "', ";
		$incsql .= "'" . maiusculas($_POST["ds_complemento"]) . "', ";
		$incsql .= "'" . $_POST["nr_elevacao"] . "', ";
		$incsql .= "'" . $_POST["nr_eixox"] . "', ";
		$incsql .= "'" . $_POST["nr_eixoy"] . "', ";
		$incsql .= "'" . $_POST["ds_abrigado"] . "', ";
		$incsql .= "'" . $_POST["id_classearea"] . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');	
	
		?>
		<script>
			alert('local inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.locais WHERE id_local = '".$_GET["id_local"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('local exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . LOCAIS EEI  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_local, nrcd_localtrecho)
{
	if(confirm('Tem certeza que deseja excluir o local '+nrcd_localtrecho+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_local='+id_local+'';
	}
}

function editar(id_local)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_local='+id_local+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Fun��o para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_local" method="post" action="<?= $PHP_SELF ?>">
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
        <td>
		
			
			<?
			
			// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.locais WHERE id_local= '" . $_GET["id_local"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$locais = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="10%" class="label1">&Aacute;REA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="12%"><span class="label1">EQUIPAMENTO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">SEQU&Ecirc;NCIA</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">COMPLEMENTO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="46%"><span class="label1">ELEVA&Ccedil;&Atilde;O</span></td>
                      <td width="2%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_area"] ?>" <? if($locais["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
							</option>
							<?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?

						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						$sql_equipamentos .= "WHERE id_disciplina='" . $disciplina["id_setor"] . "' ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <? if($locais["id_equipamento"]==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="30" maxlength="20" value="<?= $locais["nr_sequencia"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_complemento" type="text" class="txt_box" id="ds_complemento" value="<?= $locais["ds_complemento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= $locais["nr_elevacao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="10%"><span class="label1">EIXO (X)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="42%">&nbsp;</td>
                      <td width="3%">&nbsp;</td>
                      <td width="16%">&nbsp;</td>
                      <td width="3%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixox" type="text" class="txt_box" id="nr_eixox" value="<?= $locais["nr_eixox"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixoy" type="text" class="txt_box" id="nr_eixoy" value="<?= $locais["nr_eixoy"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><table width="80%" border="0">
                        <tr>
                          <td width="47%" class="label1"><input name="ds_abrigado" type="radio" value="1"  <? if($locais["ds_abrigado"]==1) { echo "checked"; } ?>>
                            SIM</td>
                          <td width="53%"><input name="ds_abrigado" type="radio" value="0" <? if($locais["ds_abrigado"]==0) { echo "checked"; } ?>>
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td><span class="label1">CLASSIFICA&Ccedil;&Atilde;O DA &Aacute;REA </span></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_classearea" class="txt_box" id="id_classearea" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.classe_area ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_classearea"] ?>" <? if($locais["id_classearea"]==$cont_equipamentos["id_classearea"]) { echo "selected"; } ?>>
                        <?= $cont["cd_classearea"] . " - " . $cont["ds_classearea"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_local" type="hidden" id="id_local" value="<?= $locais["id_local"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?
			
			 }
			else
			{
			  ?>
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%" class="label1">&Aacute;REA</td>
                      <td width="3%">&nbsp;</td>
                      <td width="16%"><span class="label1">EQUIPAMENTO</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="8%"><span class="label1">SEQU&Ecirc;NCIA</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="10%"><span class="label1">complemento</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="41%"><span class="label1">ELEVA&Ccedil;&Atilde;O</span></td>
                      <td width="41%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?

						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_area"] ?>" <? if($_POST["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						$sql_equipamentos .= "WHERE id_disciplina='" . $disciplina["id_setor"] . "' ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <? if($_POST["id_equipamento"]==$cont_equipamentos["id_equipamento"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="30" maxlength="20" value="<?= $_POST["nr_sequencia"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_complemento" type="text" class="txt_box" id="cd_complemento" value="<?= $_POST["ds_complemento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= $_POST["nr_elevacao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="10%"><span class="label1">EIXO (X) </span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="14%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="38%">&nbsp;</td>
                      <td width="19%">&nbsp;</td>
                      <td width="3%">&nbsp;</td>
                      <td width="3%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixox" type="text" class="txt_box" id="nr_eixox" value="<?= $_POST["nr_eixox"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixoy" type="text" class="txt_box" id="nr_eixoy" value="<?= $_POST["nr_eixoy"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><table width="81%" border="0">
                        <tr>
                          <td width="46%" class="label1"><input name="ds_abrigado" type="radio" value="1" <? if($_POST["ds_abrigado"]=='1'){ echo 'selected';} ?>>
                            SIM</td>
                          <td width="54%"><input name="ds_abrigado" type="radio" value="0" <? if($_POST["ds_abrigado"]=='0'){ echo 'selected';} ?>>
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td><span class="label1">CLASSIFICA&Ccedil;&Atilde;O DA &Aacute;REA</span></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_classearea" class="txt_box" id="id_classearea" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.classe_area ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_classearea"] ?>" <? if($locais["id_classearea"]==$cont["id_classearea"]) { echo "selected"; } ?>>
                        <?= $cont["cd_classearea"] . " - " . $cont["ds_classearea"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Equipamentos" type="button" class="btn" id="Equipamentos" value="COMPONENTES" onClick="javascript:location.href='componentes.php';"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
			 <?
			}
			?>
			
			
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="22%">&Aacute;REA</td>
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = "nr_sequencia";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordena��o
				  ?>
				  <td width="22%"><a href="#" class="cabecalho_tabela" onClick="ordenar('cd_local_o','<?= $ordem ?>')">EQUIPAMENTO</a></td>
				  <td width="16%">SEQU&Ecirc;NCIA</td>
				  <td width="31%">COMPLEMENTO</td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
		
					// Mostra os funcion�rios
					$sql = "SELECT * FROM Projetos.area, Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (locais.id_equipamento = equipamentos.id_equipamentos) ";
					$sql .= "WHERE locais.id_area = area.id_area ";
					$sql .= "AND area.id_os= '" . $_SESSION["id_os"]. "' ";
					$sql .= "AND equipamentos.id_disciplina = '" .$disciplina["id_setor"] . "' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($locais = mysqli_fetch_array($registro))
					{
					
						if($i%2)
						{
						// escuro
						$cor = "#F0F0F0";
						
						}
						else
						{
						//claro

						$cor = "#FFFFFF";
						}
						$i++;							

						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="22%"><div align="center">
						    <?= $locais["nr_area"] . " - " . $locais["ds_area"] ?>
					      </div></td>
						  <td width="22%"><div align="center"><?= $locais["cd_local"] . " - " . $locais["ds_equipamento"] ?></div></td>
						  <td width="17%"><div align="center"><?= $locais["nr_sequencia"] ?></div></td>
						  <td width="32%"><div align="center"><?= $locais["ds_complemento"] ?></div></td>
						  <td width="3%"><div align="center"><a href="javascript:editar('<?= $locais["id_local"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"><a href="javascript:excluir('<?= $locais["id_local"] ?>','<?= $locais["nr_sequencia"] . " " . $locais["ds_complemento"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?
					}

				?>
			  </table>
			</div>
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