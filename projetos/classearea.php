<?php
/*

		Formulário de Classe Pressão	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/classearea.php
		
		
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

	$sql = "SELECT * FROM Projetos.classe_area WHERE ";
	$sql .= "cd_classearea = '" . maiusculas($_POST["cd_classearea"]) . "' ";
	$sql .= "AND ds_classearea = '" . maiusculas($_POST["ds_classearea"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Classificação de área já cadastrada no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		$sql = "UPDATE Projetos.classe_area SET ";
		$sql .= "cd_classearea = '" . maiusculas($_POST["cd_classearea"]) . "', ";
		$sql .= "ds_classearea = '" . maiusculas($_POST["ds_classearea"]) . "' ";
		$sql .= "WHERE id_classearea = '" . $_POST["id_classearea"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Classificacao de área atualizado com sucesso.');
		</script>
		<?php
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.classe_area WHERE ";
	$sql .= "cd_classearea = '" . maiusculas($_POST["cd_classearea"]) . "' ";
	$sql .= "AND ds_classearea = '" . maiusculas($_POST["ds_classearea"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Classificação de área já cadastrada no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.classe_area ";
		$isql .= "(cd_classearea, ds_classearea) VALUES (";
		$isql .= "'" . maiusculas($_POST["cd_classearea"]) . "', ";
		$isql .= "'" . maiusculas($_POST["ds_classearea"]) . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
	
		?>
		<script>
			alert('Classificação de área inserido com sucesso.');
		</script>
		<?php
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.classe_area WHERE id_classearea = '".$_GET["id_classearea"]."' ";
	
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
<title>: : .CLASSIFICAÇÃO DE ÁREA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_classearea, ds_classearea)
{
	if(confirm('Tem certeza que deseja excluir a classe de area '+ds_classearea+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_classearea='+id_classearea+'';
	}
}

function editar(id_classearea)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_classearea='+id_classearea+'';
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
<form name="frm_equipamentos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.classe_area WHERE id_classearea= '" . $_GET["id_classearea"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$classe = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="2%" class="label1"> </td>
                      <td width="14%" class="label1">CÓD. CLASSIFICAÇÃO </td>
                      <td width="2%"> </td>
                      <td width="19%" class="label1">CLASSIFICAÇÃO ÁREA </td>
                      <td width="2%" class="label1"> </td>
                      <td width="61%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_classearea" type="text" class="txt_box" id="cd_classearea" size="40" value="<?= $classe["cd_classearea"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_classearea" type="text" class="txt_box" id="ds_classearea" size="40" value="<?= $classe["ds_classearea"] ?>">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_classearea" type="hidden" id="id_classearea" value="<?= $classe["id_classearea"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    </td>
                </tr><input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onclick="javascript:history.back();">
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
                      <td width="23%" class="label1">CÓd. CLASSIFICAÇÃO</td>
                      <td width="1%"> </td>
                      <td width="24%" class="label1">CLASSIFICAÇÃO ÁREA</td>
                      <td width="1%" class="label1"> </td>
                      <td width="50%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_classearea" type="text" class="txt_box" id="cd_classearea" value="<?= $_POST["cd_classearea"] ?>" size="40">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_classearea" type="text" class="txt_box" id="ds_classearea" value="<?= $_POST["ds_classearea"] ?>" size="40">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Subsistema" type="button" class="btn" id="Subsistema" value="SUBSISTEMA" onclick="javascript:location.href='subsistema.php';"></td>
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
						$campo = "ds_classearea";
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
				  <td width="42%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tipo','<?= $ordem ?>')">CÓD. CLASSIFICAÇÃO</a></td>
				  <td width="46%">CLASSIFICAÇÃO ÁREA </td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			
					// Mostra os funcionários
					
					$sql = "SELECT * FROM Projetos.classe_area ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($classe = mysqli_fetch_array($registro))
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
						  <td width="42%"><div align="center"><?= $classe["cd_classearea"] ?></div></td>
						  <td width="49%"><div align="center"><?= $classe["ds_classearea"] ?></div></td>
						  <td width="5%"><div align="center"> <a href="javascript:editar('<?= $classe["id_classearea"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $classe["id_classearea"] ?>','<?= $classe["ds_classearea"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
