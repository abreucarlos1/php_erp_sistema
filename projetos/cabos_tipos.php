<?
/*

		Formul�rio de TIPOS DE CABOS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cabos_tipos.php
		
		data de cria��o: 19/05/2006
		
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

	$sql = "SELECT * FROM Projetos.cabos_tipos WHERE ";
	$sql .= "id_cabo_finalidade = '" . $_POST["id_cabo_finalidade"] . "' ";
	$sql .= "AND ds_formacao = '" . $_POST["ds_formacao"] . "' ";
	$sql .= "AND cod_tipo = '" . $_POST["cod_tipo"] . "' ";
	$sql .= "AND qtd_veias = '" . $_POST["qtd_veias"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('tipo de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE Projetos.cabos_tipos SET ";
		$sql .= "id_cabo_finalidade = '" . $_POST["id_cabo_finalidade"] . "', ";
		$sql .= "ds_formacao = '" . $_POST["ds_formacao"] . "', ";
		$sql .= "cod_tipo = '" . $_POST["cod_tipo"] . "', ";
		$sql .= "qtd_veias = '" . $_POST["qtd_veias"] . "' ";
		$sql .= "WHERE id_cabo_tipo = '" . $_POST["id_cabo_tipo"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('tipo de cabo atualizado com sucesso.');
		</script>
		<?
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.cabos_tipos WHERE ";
	$sql .= "id_cabo_finalidade = '" . $_POST["id_cabo_finalidade"] . "' ";
	$sql .= "AND ds_formacao = '" . $_POST["ds_formacao"] . "' ";
	$sql .= "AND cod_tipo = '" . $_POST["cod_tipo"] . "' ";
	$sql .= "AND qtd_veias = '" . $_POST["qtd_veias"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('tipo de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.cabos_tipos ";
		$incsql .= "(id_cabo_finalidade, ds_formacao, cod_tipo, qtd_veias) VALUES (";
		$incsql .= "'" . $_POST["id_cabo_finalidade"] . "', ";
		$incsql .= "'" . $_POST["ds_formacao"] . "', ";
		$incsql .= "'" . $_POST["cod_tipo"] . "', ";
		$incsql .= "'" . $_POST["qtd_veias"] . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
	
		?>
		<script>
			alert('tipo de cabo inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.cabos_tipos WHERE id_cabo_tipo = '".$_GET["id_cabo_tipo"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	$dsql = "DELETE FROM Projetos.cabos_veias WHERE id_cabo_tipo = '".$_GET["id_cabo_tipo"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('tipo de cabo exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . TIPOS DE CABOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_cabo_tipo, ds_formacao)
{
	if(confirm('Tem certeza que deseja excluir o tipo de cabo '+ds_formacao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_cabo_tipo='+id_cabo_tipo+'';
	}
}

function editar(id_cabo_tipo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_cabo_tipo='+id_cabo_tipo+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

function veias(id_cabo_tipo)
{
	location.href = 'cabos_veias.php?id_cabo_tipo='+id_cabo_tipo+'';
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
<form name="frm_cabos_tipos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.cabos_tipos WHERE id_cabo_tipo = '" . $_GET["id_cabo_tipo"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$cabos_tipos = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td>&nbsp;</td>
                  <td align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">FINALIDADE</td>
                      <td width="0%">&nbsp;</td>
                      <td width="17%" class="label1">FORMA&Ccedil;&Atilde;O</td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">tipo veias </td>
                      <td width="2%" class="label1">&nbsp;</td>
                      <td width="63%" class="label1">QTD VEIAS </td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><select name="id_cabo_finalidade" class="txt_box" id="id_cabo_finalidade" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
				
						$sql = "SELECT * FROM Projetos.cabos_finalidades ";
						$sql .= "ORDER BY ordem_finalidade ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_cabo_finalidade"] ?>" <? if($cabos_tipos["id_cabo_finalidade"]==$cont["id_cabo_finalidade"]) { echo "selected"; } ?>>
                        <?= $cont["cd_finalidade"]." ".$cont["ds_finalidade"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_formacao" type="text" class="txt_boxcap" id="ds_formacao" size="40" value="<?= $cabos_tipos["ds_formacao"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><select name="cod_tipo" class="txt_box" id="cod_tipo" onkeypress="return keySort(this);">
                        <option value="1" <? if($cabos_tipos["cod_tipo"]==1){ echo 'selected';} ?>>NUMERADA</option>
                        <option value="2" <? if($cabos_tipos["cod_tipo"]==2){ echo 'selected';} ?>>COLORIDA</option>
                                                                  </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="qtd_veias" type="text" class="txt_boxcap" id="qtd_veias" size="15" value="<?= $cabos_tipos["qtd_veias"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_cabo_tipo" id="id_cabo_tipo" type="hidden" value="<?= $cabos_tipos["id_cabo_tipo"] ?>">
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
                  <td>&nbsp;</td>
                  <td align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">FINALIDADE</td>
                      <td width="0%">&nbsp;</td>
                      <td width="17%" class="label1">FORMA&Ccedil;&Atilde;O</td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">tipo veias </td>
                      <td width="2%" class="label1">&nbsp;</td>
                      <td width="63%" class="label1">QTD VEIAS </td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><select name="id_cabo_finalidade" class="txt_box" id="id_cabo_finalidade" onkeypress="return keySort(this);">
                        	<option value="">SELECIONE</option>
						<?
						
					
						$sql = "SELECT * FROM Projetos.cabos_finalidades ";
						$sql .= "ORDER BY ordem_finalidade ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_cabo_finalidade"] ?>" <? if($_POST["id_cabo_finalidade"]==$cont["id_cabo_finalidade"]) { echo "selected"; } ?>>
							<?= $cont["cd_finalidade"]." ".$cont["ds_finalidade"] ?>
							</option>
							<?
							
						}
						
						?>
                      </select>                      </td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_formacao" type="text" class="txt_boxcap" id="ds_formacao" value="<?= $_POST["ds_formacao"] ?>" size="40">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><select name="cod_tipo" class="txt_box" id="cod_tipo" onkeypress="return keySort(this);">
                        <option value="1" <? if($_POST["cod_tipo"]==1){ echo 'selected';} ?>>NUMERADO</option>
                        <option value="2" <? if($_POST["cod_tipo"]==2 || $_POST["cod_tipo"]==''){ echo 'selected';} ?>>COLORIDO</option>
                        </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="qtd_veias" type="text" class="txt_boxcap" id="qtd_veias" size="15" value="<?= $_POST["qtd_veias"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Locais" type="button" class="btn" id="Locais" value="CABOS" onClick="javascript:location.href='cabos.php';"></td>
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
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = "ordem_finalidade, ds_cabo_tipo";
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
				  <td width="31%"><a href="#" class="cabecalho_tabela" onClick="ordenar('tipo','<?= $ordem ?>')">FINALIDADE</a></td>
				  <td width="29%">FORMA&Ccedil;&Atilde;O</td>
				  <td width="18%">TIPO VEIAS </td>
				  <td width="10%">QTD VEIAS </td>
				  <td width="4%">V</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
		
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.cabos_tipos, Projetos.cabos_finalidades ";
					$sql .= "WHERE cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i = 0;
					
					while ($cabos_tipos = mysqli_fetch_array($registro))
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
						  <td width="31%"><div align="center"><?= $cabos_tipos["cd_finalidade"]." ".$cabos_tipos["ds_finalidade"] ?></div></td>
						  <td width="29%"><div align="center"><?= $cabos_tipos["ds_formacao"] ?></div></td>
						  <td width="19%"><div align="center">
						    <? if($cabos_tipos["cod_tipo"]==1){echo 'NUMERADO';}; if($cabos_tipos["cod_tipo"]==2){echo 'COLORIDO';}; ?>
					      </div></td>
						  <td width="10%"><div align="center">
						    <?= $cabos_tipos["qtd_veias"] ?>
					      </div></td>
						  <td width="4%"><div align="center"> <a href="javascript:veias('<?= $cabos_tipos["id_cabo_tipo"] ?>')"><img src="../images/buttons_action/veias.gif" width="16" height="16" border="0"></a> </div></td>
						  <td width="3%"><div align="center"> <a href="javascript:editar('<?= $cabos_tipos["id_cabo_tipo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $cabos_tipos["id_cabo_tipo"] ?>','<?= $cabos_tipos["ds_formacao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?
					}
					// Libera a mem�ria
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