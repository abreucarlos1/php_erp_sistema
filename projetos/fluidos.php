<?php
/*

		Formulário de fluidos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/fluidos.php
		
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

	$sql = "SELECT * FROM Projetos.fluidos WHERE ";
	$sql .= "cd_fluido = '" . $_POST["cd_fluido"] . "' ";
	$sql .= "AND ds_fluido = '" . maiusculas($_POST["ds_fluido"]) . "' ";
	$sql .= "AND cliente = '" . maiusculas($_POST["cliente"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('fluido já cadastrado no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		$sql = "UPDATE Projetos.fluidos SET ";
		$sql .= "cd_fluido = '" . $_POST["cd_fluido"] . "', ";
		$sql .= "cliente = '" . maiusculas($_POST["cliente"]) . "', ";
		$sql .= "ds_fluido = '" . maiusculas($_POST["ds_fluido"]) . "' ";
		$sql .= "WHERE id_fluido = '" . $_POST["id_fluido"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('fluido atualizado com sucesso.');
		</script>
		<?php
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.fluidos WHERE ";
	$sql .= "cd_fluido = '" . $_POST["cd_fluido"] . "' ";
	$sql .= "AND ds_fluido = '" . maiusculas($_POST["ds_fluido"]) . "' ";
	$sql .= "AND cliente = '" . maiusculas($_POST["cliente"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('fluido já cadastrado no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.fluidos ";
		$isql .= "(cd_fluido, cliente, ds_fluido) VALUES (";
		$isql .= "'" . $_POST["cd_fluido"] . "', ";
		$isql .= "'" . maiusculas($_POST["cliente"]) . "', ";
		$isql .= "'" . maiusculas($_POST["ds_fluido"]) . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
	
		?>
		<script>
			alert('fluido inserido com sucesso.');
		</script>
		<?php
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.fluidos WHERE id_fluido = '".$_GET["id_fluido"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Classificação de área excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . FLU&Iacute;DO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_fluido, ds_fluido)
{
	if(confirm('Tem certeza que deseja excluir o fluido '+ds_fluido+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_fluido='+id_fluido+'';
	}
}

function editar(id_fluido)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_fluido='+id_fluido+'';
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
<form name="frm_fluidos" method="post" action="<?= $PHP_SELF ?>">
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
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.fluidos WHERE id_fluido= '" . $_GET["id_fluido"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$fluido = mysql_fetch_array($registro); 	
			 
			 
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
                      <td width="13%" class="label1">CÓD. FLU&Iacute;DO </td>
                      <td width="1%"> </td>
                      <td width="17%" class="label1">flu&Iacute;do </td>
                      <td width="1%" class="label1"> </td>
                      <td width="67%" class="label1">CLIENTE/NORMA</td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_fluido" type="text" class="txt_boxcap" id="cd_fluido" size="30" maxlength="10" value="<?= $fluido["cd_fluido"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fluido" type="text" class="txt_box" id="ds_fluido" size="40" value="<?= $fluido["ds_fluido"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cliente" type="text" class="txt_box" id="cliente" size="40" value="<?= $fluido["cliente"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_fluido" type="hidden" id="id_fluido" value="<?= $fluido["id_fluido"] ?>">
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
                      <td width="13%" class="label1">cÓd. FLU&Iacute;DO </td>
                      <td width="1%"> </td>
                      <td width="17%" class="label1">FLU&Iacute;DO</td>
                      <td width="1%" class="label1"> </td>
                      <td width="67%" class="label1">CLIENTE/NORMA</td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_fluido" type="text" class="txt_boxcap" id="cd_fluido" value="<?= $_POST["cd_fluido"] ?>" size="30" maxlength="10">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fluido" type="text" class="txt_box" id="ds_fluido" value="<?= $_POST["ds_fluido"] ?>" size="40">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cliente" type="text" class="txt_box" id="cliente" size="40" value="<?= $_POST["cliente"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Locais" type="button" class="btn" id="Locais" value="CLASSE PRESSÃO" onclick="javascript:location.href='classepressao.php';"></td>
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
						$campo = " cliente, cd_fluido ";
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
				  <td width="16%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_fluido','<?= $ordem ?>')">CÓD. FLU&Iacute;DO </a></td>
				  <td width="38%"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_fluido','<?= $ordem ?>')">FLU&Iacute;DO </a></td>
				  <td width="33%">CLIENTE/NORMA</td>
				  <td width="6%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			
					// Mostra os funcionários
					
					$sql = "SELECT * FROM Projetos.fluidos ";
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($fluido = mysqli_fetch_array($registro))
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
						  <td width="15%"><div align="center"><?= $fluido["cd_fluido"] ?></div></td>
						  <td width="39%"><div align="center"><?= $fluido["ds_fluido"] ?></div></td>
						  <td width="34%"><div align="center">
						    <?= $fluido["cliente"] ?>
					      </div></td>
						  <td width="6%"><div align="center"> <a href="javascript:editar('<?= $fluido["id_fluido"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="6%"><div align="center"> <a href="javascript:excluir('<?= $fluido["id_fluido"] ?>','<?= $fluido["ds_fluido"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
