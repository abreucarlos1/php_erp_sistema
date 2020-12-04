<?
/*

		Formul�rio de TIPOS DE SUPORTES	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/suportes_acessorios.php
		
		data de cria��o: 05/06/2006
		
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

	$sql = "SELECT * FROM Projetos.suportes_acessorios WHERE ";
	$sql .= "id_tipo_suporte = '" . $_POST["id_tipo_suporte"] . "' ";
	$sql .= "AND id_acessorio = '" . $_POST["id_acessorio"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Acess�rio j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE Projetos.suportes_acessorios SET ";
		$sql .= "id_tipo_suporte = '" . $_POST["id_tipo_suporte"] . "', ";
		$sql .= "id_acessorio = '" . $_POST["id_acessorio"] . "' ";
		$sql .= "WHERE id_suporte_acessorio = '" . $_POST["id_suporte_acessorio"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Acess�rio atualizado com sucesso.');
		</script>
		<?
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.suportes_acessorios WHERE ";
	$sql .= "id_tipo_suporte = '" . $_POST["id_tipo_suporte"] . "' ";
	$sql .= "AND id_acessorio = '" . $_POST["id_acessorio"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Acess�rio j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.suportes_acessorios ";
		$incsql .= "(id_tipo_suporte, id_acessorio) VALUES (";
		$incsql .= "'" . $_POST["id_tipo_suporte"] . "', ";
		$incsql .= "'" . $_POST["id_acessorio"] . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
	
		?>
		<script>
			alert('Acess�rio inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.suportes_acessorios WHERE id_suporte_acessorio = '".$_GET["id_suporte_acessorio"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Acess�rio exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . SUPORTES - ACESS�RIOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_suporte_acessorio, ds_tipo_suporte)
{
	if(confirm('Tem certeza que deseja excluir o acess�rio '+ds_tipo_suporte+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_suporte_acessorio='+id_suporte_acessorio+'';
	}
}

function editar(id_suporte_acessorio)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_suporte_acessorio='+id_suporte_acessorio+'';
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
<form name="frm_suportes_acessorios" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.suportes_acessorios WHERE id_suporte_acessorio= '" . $_GET["id_suporte_acessorio"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$suportes_acessorios = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="13%" class="label1">SUPORTE  </td>
                      <td width="1%">&nbsp;</td>
                      <td width="19%" class="label1">ACESS&Oacute;RIO </td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="65%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><select name="id_tipo_suporte" class="txt_box" id="id_tipo_suporte" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_tipo_suporte"] ?>" <? if($suportes_acessorios["id_tipo_suporte"]==$cont["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont["cd_tipo_suporte"] . " - " . $cont["ds_tipo_suporte"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_acessorio" class="txt_box" id="id_acessorio" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_tipo_suporte"] ?>" <? if($suportes_acessorios["id_acessorio"]==$cont["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont["cd_tipo_suporte"] . " - " . $cont["ds_tipo_suporte"] ?>
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
				  <input name="id_suporte_acessorio" type="hidden" id="id_suporte_acessorio" value="<?= $suportes_acessorios["id_suporte_acessorio"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='<?= $PHP_SELF ?>';"></td>
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
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="13%" class="label1"> SUPORTE </td>
                      <td width="1%">&nbsp;</td>
                      <td width="17%" class="label1">ACESS&Oacute;RIO</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="67%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><select name="id_tipo_suporte" class="txt_box" id="id_tipo_suporte" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_tipo_suporte"] ?>" <? if($_POST["id_tipo_suporte"]==$cont["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont["cd_tipo_suporte"] . " - " . $cont["ds_tipo_suporte"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_acessorio" class="txt_box" id="id_acessorio" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_tipo_suporte"] ?>" <? if($_POST["id_acessorio"]==$cont["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont["cd_tipo_suporte"] . " - " . $cont["ds_tipo_suporte"] ?>
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
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='menu_mectub.php';"></td>
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
						$campo = "cd_tipo_suporte";
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
				  <td width="33%">SUPORTE</td>
				  <td width="45%">ACESS&Oacute;RIOS</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					
					$sql = "SELECT * FROM Projetos.suportes_acessorios, Projetos.tipos_suportes ";
					$sql .= "WHERE suportes_acessorios.id_tipo_suporte = tipos_suportes.id_tipo_suporte ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($suportes_acessorios = mysqli_fetch_array($registro))
					{
						
						$sql1 = "SELECT * FROM Projetos.tipos_suportes ";
						$sql1 .= "WHERE tipos_suportes.id_tipo_suporte = '". $suportes_acessorios["id_acessorio"] ."' ";
						
						$registro1 = $db->select($sql1,'MYSQL');
						
						$acessorios = mysqli_fetch_array($registro1);
						
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
						  <td width="33%"><div align="center"><?= $suportes_acessorios["cd_tipo_suporte"] ." - ". $suportes_acessorios["ds_tipo_suporte"] ?></div></td>
						  <td width="46%"><div align="center"><?= $acessorios["cd_tipo_suporte"]." - ". $acessorios["ds_tipo_suporte"] ?>
					      </div></td>
						  <td width="3%"><div align="center"> <a href="javascript:editar('<?= $suportes_acessorios["id_suporte_acessorio"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $suportes_acessorios["id_suporte_acessorio"] ?>','<?= $acessorios["ds_tipo_suporte"]." - ". $acessorios["ds_tipo_suporte"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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