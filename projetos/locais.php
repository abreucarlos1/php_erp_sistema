<?
/*

		Formul�rio de Locais	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/locais.php
		
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
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "UPDATE Projetos.locais SET ";
	$sql .= "id_area = '" . $_POST["id_area"] . "', ";
	$sql .= "id_equipamentos = '" . $_POST["id_equipamentos"] . "', ";
	$sql .= "nr_local = '" . maiusculas($_POST["nr_local"]) . "', ";
	$sql .= "cd_trecho = '" . maiusculas($_POST["cd_trecho"]) . "', ";
	$sql .= "nr_elevacao = '" . $_POST["nr_elevacao"] . "', ";
	$sql .= "nr_eixo = '" . $_POST["nr_eixo"] . "', ";
	$sql .= "nr_coluna = '" . $_POST["nr_coluna"] . "', ";
	$sql .= "id_abrigada = '" . $_POST["id_abrigada"] . "', ";
	$sql .= "id_area_clas = '" . $_POST["id_area_clas"] . "' ";

	$sql .= "WHERE locais.id_local = '" . $_POST["id_local"] ."' ";
	
	$registros = $db->update($sql,'MYSQL');


	?>
	<script>
		alert('local atualizado com sucesso.');
	</script>
	<?
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria senten�a de Inclusão no bd
	$incsql = "INSERT INTO Projetos.locais ";
	$incsql .= "(id_area, id_equipamentos, nr_local, cd_trecho, nr_elevacao, nr_eixo, nr_coluna, id_abrigada, id_area_clas) VALUES (";
	$incsql .= "'" . $_POST["id_area"] . "', ";
	$incsql .= "'" . $_POST["id_equipamentos"] . "', ";
	$incsql .= "'" . maiusculas($_POST["nr_local"]) . "', ";
	$incsql .= "'" . maiusculas($_POST["cd_trecho"]) . "', ";
	$incsql .= "'" . $_POST["nr_elevacao"] . "', ";
	$incsql .= "'" . $_POST["nr_eixo"] . "', ";
	$incsql .= "'" . $_POST["nr_coluna"] . "', ";
	$incsql .= "'" . $_POST["id_abrigada"] . "', ";
	$incsql .= "'" . $_POST["id_area_clas"] . "') ";

	$registros = $db->insert($incsql,'MYSQL');

	?>
	<script>
		alert('local inserido com sucesso.');
	</script>
	<?

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.locais WHERE locais.id_local = '".$_GET["id_local"]."' ";
	
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
<title>: : . LOCAIS . : :</title>
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


function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_local" method="post" action="<?= $PHP_SELF ?>" enctype="multipart/form-data">
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
				$sql = "SELECT * FROM Projetos.locais WHERE locais.id_local= '" . $_GET["id_local"] . "' ";
				
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
                      <td width="12%"><span class="label1">EQUIPAMENTOS</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">N&ordm; LOCAL</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">TRECHO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="46%"><span class="label1">ELEVA&Ccedil;&Atilde;O</span></td>
                      <td width="2%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onChange="javascript:document.forms[0].nr_local.focus()">
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
                      <td><select name="id_equipamentos" class="txt_box" id="id_equipamentos" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <? if($locais["id_equipamentos"]==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_local" type="text" class="txt_box" id="nr_local" size="30" maxlength="20" value="<?= $locais["nr_local"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_trecho" type="text" class="txt_box" id="cd_trecho" value="<?= $locais["cd_trecho"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= formatavalor($locais["nr_elevacao"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%"><span class="label1">EIXO (X)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="16%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="19%"><span class="label1">&Aacute;REA CLASSIFICADA</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="33%">&nbsp;</td>
                      <td width="2%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixo" type="text" class="txt_box" id="nr_eixo" value="<?= $locais["nr_eixo"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_coluna" type="text" class="txt_box" id="nr_coluna" value="<?= $locais["nr_coluna"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><table width="100%" border="0">
                        <tr>
                          <td width="49%" class="label1"><input name="id_abrigada" type="radio" value="1"  <? if($locais["id_abrigada"]==1) { echo "checked"; } ?>>
                            SIM</td>
                          <td width="51%"><input name="id_abrigada" type="radio" value="0" <? if($locais["id_abrigada"]==0) { echo "checked"; } ?>>
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td><table width="100%" border="0">
                        <tr>
                          <td width="43%" class="label1"><input name="id_area_clas" type="radio" value="1" <? if($locais["id_area_clas"]==1) { echo "checked"; } ?>>
                            SIM</td>
                          <td width="57%"><input name="id_area_clas" type="radio" value="0" <? if($locais["id_area_clas"]==0) { echo "checked"; } ?>>
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
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
                      <td width="16%"><span class="label1">EQUIPAMENTOS</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="8%"><span class="label1">N&ordm; LOCAL</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="10%"><span class="label1">TRECHO</span></td>
                      <td width="3%">&nbsp;</td>
                      <td width="41%"><span class="label1">ELEVA&Ccedil;&Atilde;O</span></td>
                      <td width="41%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area">
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
                      <td><select name="id_equipamentos" class="txt_box" id="id_equipamentos" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?
						
						if($_POST["id_equipamentos"])
						{
							$cod_id_equipamentos = $_POST["id_equipamentos"];
						}
						else 
						{
							$cod_id_equipamentos = $locais["id_equipamentos"];
						}
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <? if($cod_id_equipamentos==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_local" type="text" class="txt_box" id="nr_local" size="30" maxlength="20" value="<?= $_POST["nr_local"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_trecho" type="text" class="txt_box" id="cd_trecho" value="<?= $_POST["cd_trecho"] ?>" size="30" maxlength="20">
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
                      <td width="13%"><span class="label1">EIXO (X) </span></td>
                      <td width="2%">&nbsp;</td>
                      <td width="13%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="15%"><span class="label1">ABRIGADO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="18%"><span class="label1">&Aacute;REA CLASSIFICADA</span></td>
                      <td width="35%">&nbsp;</td>
                      <td width="1%">&nbsp;</td>
                      <td width="1%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixo" type="text" class="txt_box" id="nr_eixo" value="<?= $_POST["nr_eixo"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_coluna" type="text" class="txt_box" id="nr_coluna" value="<?= $_POST["nr_coluna"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><table width="99%" border="0">
                        <tr>
                          <td width="46%" class="label1"><input name="id_abrigada" type="radio" value="1">
                            SIM</td>
                          <td width="54%"><input name="id_abrigada" type="radio" value="0">
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td><table width="100%" border="0">
                        <tr>
                          <td width="47%" class="label1"><input name="id_area_clas" type="radio" value="1">
                            SIM</td>
                          <td width="53%"><input name="id_area_clas" type="radio" value="0">
                              <span class="label1">N&Atilde;O</span></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
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
				  <td width="29%">&Aacute;REA</td>
				  <?
					// Controle de ordena��o
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
					//Controle de ordena��o
				  ?>
				  <td width="29%"><a href="#" class="cabecalho_tabela" onClick="ordenar('cd_local','<?= $ordem ?>')">EQUIPAMENTO</a></td>
				  <td width="21%">NUMLOCAL</td>
				  <td width="36%">TRECHO</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="5%"  class="cabecalho_tabela">D</td>
				  <td width="4%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?

					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.equipamentos ";
					$sql .= "WHERE locais.id_equipamento = equipamentos.id_equipamentos ";
					$sql .= "AND locais.id_area = area.id_area ";
					$sql .= "AND area.id_os= '" . $_SESSION["id_os"]. "' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
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
						  <td width="29%"><div align="center">
						    <?= $locais["nr_area"] . " - " . $locais["ds_area"] ?>
					      </div></td>
						  <td width="29%"><div align="center"><?= $locais["cd_local"] . " - " . $locais["ds_equipamento"] ?></div></td>
						  <td width="22%"><div align="center"><?= $locais["nr_local"] ?></div></td>
						  <td width="35%"><div align="center"><?= $locais["cd_trecho"] ?></div></td>
						  <td width="7%"><div align="center"><a href="javascript:editar('<?= $locais["id_local"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="7%"><div align="center"><a href="javascript:excluir('<?= $locais["id_local"] ?>','<?= $locais["nr_local"] . " " . $locais["cd_trecho"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div></td>
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