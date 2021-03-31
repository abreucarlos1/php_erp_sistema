<?php
/*
		Formulário de MATERIAIS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/materiais.php
		
		data de criação: 16/05/2006
		
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

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "SELECT * FROM Projetos.materiais WHERE ";
	$sql .= "cd_material = '" . $_POST["cd_material"] . "' ";
	$sql .= "AND ds_material = '" . maiusculas($_POST["ds_material"]) . "' ";
	$sql .= "AND mat_cliente = '" . maiusculas($_POST["mat_cliente"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Material já cadastrado no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		$sql = "UPDATE Projetos.materiais SET ";
		$sql .= "cd_material = '" . $_POST["cd_material"] . "', ";
		$sql .= "mat_cliente = '" . maiusculas($_POST["mat_cliente"]) . "', ";
		$sql .= "ds_material = '" . maiusculas($_POST["ds_material"]) . "' ";
		$sql .= "WHERE id_material = '" . $_POST["id_material"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Material atualizado com sucesso.');
		</script>
		<?php
	}		

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.materiais WHERE ";
	$sql .= "cd_material = '" . $_POST["cd_material"] . "' ";
	$sql .= "AND ds_material = '" . maiusculas($_POST["ds_material"]) . "' ";
	$sql .= "AND mat_cliente = '" . maiusculas($_POST["mat_cliente"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Material já cadastrado no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.materiais ";
		$isql .= "(cd_material, mat_cliente, ds_material) VALUES (";
		$isql .= "'" . $_POST["cd_material"] . "', ";
		$isql .= "'" . maiusculas($_POST["mat_cliente"]) . "', ";
		$isql .= "'" . maiusculas($_POST["ds_material"]) . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
	
		?>
		<script>
			alert('Material inserido com sucesso.');
		</script>
		<?php
	}
}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.materiais WHERE id_material = '".$_GET["id_material"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	
	?>
	<script>
		alert('Material de área excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . MATERIAL . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_material, ds_material)
{
	if(confirm('Tem certeza que deseja excluir o material '+ds_material+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_material='+id_material+'';
	}
}

function editar(id_material)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_material='+id_material+'';
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
<form name="frm_materiais" method="post" action="<?= $PHP_SELF ?>">
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
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				$sql = "SELECT * FROM Projetos.materiais WHERE id_material= '" . $_GET["id_material"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$material = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td> </td>
                  <td align="left"> </td>
                </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="1%" class="label1"> </td>
                      <td width="13%" class="label1">CÓD. MATERIAL </td>
                      <td width="1%"> </td>
                      <td width="17%" class="label1">MATERIAL </td>
                      <td width="1%" class="label1"> </td>
                      <td width="67%" class="label1">CLIENTE/NORMA</td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_material" type="text" class="txt_boxcap" id="cd_material" size="30" maxlength="5" value="<?= $material["cd_material"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_material" type="text" class="txt_box" id="ds_material" size="40" value="<?= $material["ds_material"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="mat_cliente" type="text" class="txt_box" id="mat_cliente" size="40" value="<?= $material["mat_cliente"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_material" type="hidden" id="id_material" value="<?= $material["id_material"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
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
                  <td> </td>
                  <td align="left"> </td>
                </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="1%" class="label1"> </td>
                      <td width="13%" class="label1">cÓd. MATERIAL </td>
                      <td width="1%"> </td>
                      <td width="17%" class="label1">MATERIAL</td>
                      <td width="1%" class="label1"> </td>
                      <td width="67%" class="label1">CLIENTE/NORMA</td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_material" type="text" class="txt_boxcap" id="cd_material" value="<?= $_POST["cd_material"] ?>" size="30" maxlength="5">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_material" type="text" class="txt_box" id="ds_material" value="<?= $_POST["ds_material"] ?>" size="40">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="mat_cliente" type="text" class="txt_box" id="mat_cliente" value="<?= $_POST["mat_cliente"] ?>" size="40">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
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
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "mat_cliente, cd_material";
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
				  <td width="16%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tipo','<?= $ordem ?>')">CÓD. MATERIAL </a></td>
				  <td width="38%">MATERIAL</td>
				  <td width="30%">CLIENTE/NORMA</td>
				  <td width="8%"  class="cabecalho_tabela">E</td>
				  <td width="5%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					
					$sql = "SELECT * FROM Projetos.materiais ";
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($material = mysqli_fetch_array($registro))
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
						  <td width="15%"><div align="center"><?= $material["cd_material"] ?></div></td>
						  <td width="39%"><div align="center"><?= $material["ds_material"] ?></div></td>
						  <td width="31%"><div align="center">
						    <?= $material["mat_cliente"] ?>
					      </div></td>
						  <td width="8%"><div align="center"> <a href="javascript:editar('<?= $material["id_material"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="7%"><div align="center"> <a href="javascript:excluir('<?= $material["id_material"] ?>','<?= $material["ds_material"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
					}
					// Libera a memória

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