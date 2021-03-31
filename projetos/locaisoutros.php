<?php
/*

		Formulário de Locais OUTROS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/locais_outros.php
		
		data de criação: 05/04/2006
		
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


//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "UPDATE Projetos.locaisoutros SET ";
	$sql .= "id_area = '" . $_POST["id_area"] . "', ";
	$sql .= "id_equipamento_o = '" . $_POST["id_equipamento_o"] . "', ";
	$sql .= "nr_local_o = '" . maiusculas($_POST["nr_local_o"]) . "', ";
	$sql .= "complemento_o = '" . maiusculas($_POST["complemento_o"]) . "', ";
	$sql .= "nr_elevacao_o = '" . $_POST["nr_elevacao_o"] . "', ";
	$sql .= "nr_eixo_o = '" . $_POST["nr_eixo_o"] . "', ";
	$sql .= "nr_coluna_o = '" . $_POST["nr_coluna_o"] . "', ";
	$sql .= "id_abrigada_o = '" . $_POST["id_abrigada_o"] . "', ";
	$sql .= "id_area_clas_o = '" . $_POST["id_area_clas_o"] . "' ";

	$sql .= "WHERE id_localoutro = '" . $_POST["id_localoutro"] ."' ";
	$registros = mysql_query($sql, $db->conexao) or die("Não foi possível a Atualização dos dados.");

	?>
	<script>
		alert('local atualizado com sucesso.');
	</script>
	<?php
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria sentença de Inclusão no bd
	$isql = "INSERT INTO Projetos.locaisoutros ";
	$isql .= "(id_area, id_equipamento_o, nr_local_o, complemento_o, nr_elevacao_o, nr_eixo_o, nr_coluna_o, id_abrigada_o, id_area_clas_o) VALUES (";
	$isql .= "'" . $_POST["id_area"] . "', ";
	$isql .= "'" . $_POST["id_equipamento_o"] . "', ";
	$isql .= "'" . maiusculas($_POST["nr_local_o"]) . "', ";
	$isql .= "'" . maiusculas($_POST["complemento_o"]) . "', ";
	$isql .= "'" . $_POST["nr_elevacao_o"] . "', ";
	$isql .= "'" . $_POST["nr_eixo_o"] . "', ";
	$isql .= "'" . $_POST["nr_coluna_o"] . "', ";
	$isql .= "'" . $_POST["id_abrigada_o"] . "', ";
	$isql .= "'" . $_POST["id_area_clas_o"] . "') ";

	$registros = mysql_query($isql,$db->conexao) or die("Não foi possível a inserção dos dados" . $isql);


	?>
	<script>
		alert('local inserido com sucesso.');
	</script>
	<?php

}
 
if ($_GET["acao"] == "deletar")
{
	mysql_query("DELETE FROM Projetos.locaisoutros WHERE id_localoutro = '".$_GET["id_localoutro"]."' ",$conexao) or die ("Não foi possível excluir o registro. Motivo: " . mysql_error($conexao));
	?>
	<script>
		alert('local excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . LOCAIS OUTROS  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_localoutro, nrcd_localtrecho)
{
	if(confirm('Tem certeza que deseja excluir o local '+nrcd_localtrecho+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_local='+id_localoutro+'';
	}
}

function editar(id_localoutro)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_local='+id_localoutro+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Função para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="local" method="post" action="<?= $PHP_SELF ?>">
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
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.locaisoutro WHERE id_localoutro= '" . $_GET["id_localoutro"] . "' ";
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.".$sql);
				$locais = mysql_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="10%" class="label1">ÁREA</td>
                      <td width="1%"> </td>
                      <td width="12%"><span class="label1">EQUIPAMENTOS</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">Nº LOCAL</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">complemento</span></td>
                      <td width="1%"> </td>
                      <td width="46%"><span class="label1">ELEVAÇÃO</span></td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.os = '" . $_SESSION["os"] . "' ";
						
						$reg = mysql_query($sql_equipamentos,$db->conexao);
						
						while($cont = mysql_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_area"] ?>" <?php if($locais["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamento_o" class="txt_box" id="id_equipamentos" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						
						$reg_equipamentos = mysql_query($sql_equipamentos,$db->conexao);
						
						while($cont_equipamentos = mysql_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($locais["id_equipamentos"]==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_local" type="text" class="txt_box" id="nr_local" size="30" maxlength="20" value="<?= $locais["nr_local"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_complenento" type="text" class="txt_box" id="requerido" value="<?= $locais["o_complemento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= formatavalor($locais["nr_elevacao"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="7%"><span class="label1">EIXO (X)</span></td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%"> </td>
                      <td width="18%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%"> </td>
                      <td width="21%"><span class="label1">ÁREA CLASSIFICADA</span></td>
                      <td width="1%"> </td>
                      <td width="39%"> </td>
                      <td width="1%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixo" type="text" class="txt_box" id="nr_eixo" value="<?= $locais["nr_eixo"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_coluna" type="text" class="txt_box" id="nr_coluna" value="<?= $locais["nr_eixo"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><table width="100%" border="0">
                        <tr>
                          <td width="48%" class="label1"><input name="id_abrigada" type="radio" value="1"  <?php if($locais["id_abrigada"]==1) { echo "checked"; } ?>>
                            SIM</td>
                          <td width="52%"><input name="id_abrigada" type="radio" value="0" <?php if($locais["id_abrigada"]==0) { echo "checked"; } ?>>
                              <span class="label1">NÃO</span></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td><table width="90%" border="0">
                        <tr>
                          <td width="46%" class="label1"><input name="id_area_clas" type="radio" value="1" <?php if($locais["id_area_clas"]==1) { echo "checked"; } ?>>
                            SIM</td>
                          <td width="54%"><input name="id_area_clas" type="radio" value="0" <?php if($locais["id_area_clas"]==0) { echo "checked"; } ?>>
                              <span class="label1">NÃO</span></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_local" type="hidden" id="id_local" value="<?= $locais["id_local"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="button" class="btn" id="Alterar" value="Alterar" onclick="requer('local')">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:location.href='<?= $PHP_SELF ?>';"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?php
			
			 }
			else
			{
			  ?>
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%" class="label1">ÁREA</td>
                      <td width="3%"> </td>
                      <td width="16%"><span class="label1">EQUIPAMENTOS</span></td>
                      <td width="3%"> </td>
                      <td width="8%"><span class="label1">Nº LOCAL</span></td>
                      <td width="3%"> </td>
                      <td width="10%"><span class="label1">complemento</span></td>
                      <td width="3%"> </td>
                      <td width="41%"><span class="label1">ELEVAÇÃO</span></td>
                      <td width="41%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.os = '" . $_SESSION["os"] . "' ";
						
						$reg = mysql_query($sql,$db->conexao);
						
						while($cont = mysql_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_area"] ?>" <?php if($_POST["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamentos" class="txt_box" id="id_equipamentos" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?php
						
						if($_POST["id_equipamentos"])
						{
							$cod_id_equipamentos = $_POST["id_equipamentos"];
						}
						else 
						{
							$cod_id_equipamentos = $locais["id_equipamentos"];
						}
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						
						$reg_equipamentos = mysql_query($sql_equipamentos,$db->conexao);
						
						while($cont_equipamentos = mysql_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($cod_id_equipamentos==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_local" type="text" class="txt_box" id="nr_local" size="30" maxlength="20" value="<?= $_POST["nr_local"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_complemento" type="text" class="txt_box" id="cd_complemento" value="<?= $_POST["o_complemento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= $_POST["nr_elevacao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="7%"><span class="label1">EIXO (X) </span></td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%"> </td>
                      <td width="19%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%"> </td>
                      <td width="21%"><span class="label1">ÁREA CLASSIFICADA</span></td>
                      <td width="38%"> </td>
                      <td width="1%"> </td>
                      <td width="1%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixo" type="text" class="txt_box" id="nr_eixo" value="<?= $_POST["nr_eixo"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_coluna" type="text" class="txt_box" id="nr_coluna" value="<?= $_POST["nr_eixo"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><table width="99%" border="0">
                        <tr>
                          <td width="46%" class="label1"><input name="id_abrigada" type="radio" value="1">
                            SIM</td>
                          <td width="54%"><input name="id_abrigada" type="radio" value="0">
                              <span class="label1">NÃO</span></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td><table width="100%" border="0">
                        <tr>
                          <td width="47%" class="label1"><input name="id_area_clas" type="radio" value="1">
                            SIM</td>
                          <td width="53%"><input name="id_area_clas" type="radio" value="0">
                              <span class="label1">NÃO</span></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="Inserir" onclick="requer('local')">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:location.href='menuprojetos.php';">
                    <input name="Equipamentos" type="button" class="btn" id="Equipamentos" value="COMPONENTES" onclick="javascript:location.href='componentes.php';"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
			 <?php
			}
			?>
			
			
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="29%">ÁREA</td>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "nr_local";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordenação
				  ?>
				  <td width="29%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_local','<?= $ordem ?>')">EQUIPAMENTO</a></td>
				  <td width="21%">NUMLOCAL</td>
				  <td width="36%">COMPLEMENTO</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="5%"  class="cabecalho_tabela">D</td>
				  <td width="4%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php

			
					// Mostra os funcionários
					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.equipamentos ";
					$sql .= "WHERE locais.id_equipamentos = equipamentos.id_equipamentos ";
					$sql .= "AND locais.id_area = area.id_area ";
					$sql .= "AND area.os= '" . $_SESSION["os"]. "' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
					$regcounter = mysql_num_rows($registro);
					
					$i=0;
					
					while ($locais = mysql_fetch_array($registro))
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
						  <td width="29%"><div align="center">
						    <?= $locais["nr_area"] . " - " . $locais["ds_area"] ?>
					      </div></td>
						  <td width="29%"><div align="center"><?= $locais["cd_local"] . " - " . $locais["ds_equipamento"] ?></div></td>
						  <td width="22%"><div align="center"><?= $locais["nr_local"] ?></div></td>
						  <td width="35%"><div align="center"><?= $locais["o_complemento"] ?></div></td>
						  <td width="7%"><div align="center"><a href="javascript:editar('<?= $locais["id_local"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="7%"><div align="center"><a href="javascript:excluir('<?= $locais["id_local"] ?>','<?= $locais["nr_local"] . " " . $locais["cd_trecho"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
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
<?php
	$db->fecha_db();
?>

