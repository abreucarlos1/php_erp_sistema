<?php
/*
		Formulário de Classe Pressão	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/classepressao.php
		
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

	$sql = "SELECT * FROM Projetos.classe_pressao WHERE ";
	$sql .= "cd_classepressao = '" . maiusculas($_POST["cd_classepressao"]) . "' ";
	$sql .= "AND ds_classepressao = '" . maiusculas($_POST["ds_classepressao"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Classe de pressão já cadastrada no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		$sql = "UPDATE Projetos.classe_pressao SET ";
		$sql .= "cd_classepressao = '" . maiusculas($_POST["cd_classepressao"]) . "', ";
		$sql .= "ds_classepressao = '" . maiusculas($_POST["ds_classepressao"]) . "' ";
		$sql .= "WHERE id_classepressao = '" . $_POST["id_classepressao"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Classe de pressão atualizado com sucesso.');
		</script>
		<?php
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.classe_pressao WHERE ";
	$sql .= "cd_classepressao = '" . maiusculas($_POST["cd_classepressao"]) . "' ";
	$sql .= "AND ds_classepressao = '" . maiusculas($_POST["ds_classepressao"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Classe de pressão já cadastrada no banco de dados.');
		</script>
		<?php
	
	}
	else
	{
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.classe_pressao ";
		$isql .= "(cd_classepressao, ds_classepressao) VALUES (";
		$isql .= "'" . maiusculas($_POST["cd_classepressao"]) . "', ";
		$isql .= "'" . maiusculas($_POST["ds_classepressao"]) . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
	
		?>
		<script>
			alert('Classe de pressão inserido com sucesso.');
		</script>
		<?php
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.classe_pressao WHERE id_classepressao = '".$_GET["id_classepressao"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Classe de pressão excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : .CLASSE PRESSÃO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_classepressao, ds_classepressao)
{
	if(confirm('Tem certeza que deseja excluir a classe de pressao '+ds_classepressao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_classepressao='+id_classepressao+'';
	}
}

function editar(id_classepressao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_classepressao='+id_classepressao+'';
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
<form name="frm_classe" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.classe_pressao WHERE id_classepressao= '" . $_GET["id_classepressao"] . "' ";
				
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
                      <td width="14%" class="label1">CÓD. CLASSE</td>
                      <td width="2%"> </td>
                      <td width="19%" class="label1">CLASSE PRESSÃO </td>
                      <td width="2%" class="label1"> </td>
                      <td width="61%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_classepressao" type="text" class="txt_box" id="cd_classepressao" size="25" maxlength="3" value="<?= $classe["cd_classepressao"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_classepressao" type="text" class="txt_box" id="ds_classepressao" size="35" value="<?= $classe["ds_classepressao"] ?>">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_classepressao" type="hidden" id="id_classepressao" value="<?= $classe["id_classepressao"] ?>">
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
                      <td width="3%" class="label1"> </td>
                      <td width="14%" class="label1">CÓd. CLASSE </td>
                      <td width="3%"> </td>
                      <td width="19%" class="label1">CLASSE PRESSÃO </td>
                      <td width="2%" class="label1"> </td>
                      <td width="59%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_classepressao" type="text" class="txt_box" id="cd_classepressao" value="<?= $_POST["cd_classepressao"] ?>" size="25" maxlength="3">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_classepressao" type="text" class="txt_box" id="ds_classepressao" value="<?= $_POST["ds_classepressao"] ?>" size="35">
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
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Locais" type="button" class="btn" id="Locais" value="LINHAS" onclick="javascript:location.href='locais_linhas.php';"></td>
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
				  <?
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "ds_classepressao";
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
				  <td width="26%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tipo','<?= $ordem ?>')">CÓD. CLASSE </a></td>
				  <td width="62%">CLASSE PRESSÃO </td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			

					$sql = "SELECT * FROM Projetos.classe_pressao ";
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
						  <td width="26%"><div align="center"><?= $classe["cd_classepressao"] ?></div></td>
						  <td width="65%"><div align="center"><?= $classe["ds_classepressao"] ?></div></td>
						  <td width="5%"><div align="center"> <a href="javascript:editar('<?= $classe["id_classepressao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $classe["id_classepressao"] ?>','<?= $classe["ds_classepressao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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