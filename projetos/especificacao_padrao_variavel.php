<?php
/*

		Formulário de Variavel	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_padrao_variavel.php
		
		data de criação: 11/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Mudança de estrutura / sem filtro por tópico
					 / acrescentado disciplina - 18/04/2006
		Versão 2 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016	

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
	$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
	$sql .= "WHERE disciplina = '".$_POST["disciplina"]."' ";
	//$sql .= "AND id_topico = '" . $_POST["id_topico"] . "' ";
	$sql .= "AND ds_variavel = '". maiusculas($_POST["ds_variavel"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Variável já cadastrado no banco de dados.');
			</script>
			<?php
		}
	else
		{

		
			$sql = "UPDATE Projetos.especificacao_padrao_variavel SET ";
			$sql .= "disciplina = '" . $_POST["disciplina"] . "', ";
			//$sql .= "id_topico = '" . $_POST["id_topico"] . "', ";
			$sql .= "ds_variavel = '" . maiusculas($_POST["ds_variavel"]) . "' ";
			$sql .= "WHERE id_variavel = '" . $_POST["id_variavel"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
		
			?>
			<script>
				alert('Variável atualizada com sucesso.');
			</script>
			<?php
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
	$sql .= "WHERE disciplina = '".$_POST["disciplina"]."' ";
	//$sql .= "AND id_topico = '".$_POST["id_topico"]."' ";
	$sql .= "AND ds_variavel = '". maiusculas($_POST["ds_variavel"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Variável já cadastrado no banco de dados.');
			</script>
			<?php
		}
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.especificacao_padrao_variavel ";
			$isql .= "(disciplina, ds_variavel) ";
			$isql .= "VALUES ('" . $_POST["disciplina"] . "', '" . maiusculas($_POST["ds_variavel"]) ."' ) ";

			$registros = $db->insert($isql,'MYSQL');

			?>
			<script>
				alert('Variável inserida com sucesso.');
			</script>
			<?php

		}
}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.especificacao_padrao_variavel WHERE id_variavel = '".$_GET["id_variavel"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Variável excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : .  VARIÁVEL . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_variavel, ds_variavel)
{
	if(confirm('Tem certeza que deseja excluir o variável '+ds_variavel+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_variavel='+id_variavel+'';
	}
}

function editar(id_variavel)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_variavel='+id_variavel+'';
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
<form name="frm_variaveis" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
				$sql .= "WHERE id_variavel= '" . $_GET["id_variavel"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$variavel = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%" class="label1">DISCIPLINA</td>
                      <td width="1%" class="label1"> </td>
                      <td width="54%" class="label1">VARIÁVEL</td>
                      <td width="1%"> </td>
                      <td width="28%" class="label1"> </td>
                      </tr>
                    <tr>
                      <td><select name="disciplina" class="txt_box" id="disciplina" onkeypress="return keySort(this);">
                        <option value="INS" <?php if($topico["disciplina"]=='INS'){ echo 'selected'; } ?>>EEI</option>
                        <option value="MEC" <?php if($topico["disciplina"]=='MEC'){ echo 'selected'; } ?>>MEC/TUB</option>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_variavel" type="text" class="txt_box" id="ds_variavel" value="<?= $variavel["ds_variavel"] ?>" size="50">
                      </font></td>
                      <td> </td>
                      <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_variavel" type="hidden" id="id_variavel" value="<?= $variavel["id_variavel"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onclick="javascript:history.back();"></td>
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
                  <td align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%" class="label1">DISCIPLINA</td>
                      <td width="1%" class="label1"> </td>
                      <td width="51%" class="label1">VARIÁVEL </td>
                      <td width="3%" class="label1"> </td>
                      <td width="29%" class="label1"> </td>
                      </tr>
                    <tr>
                      <td><select name="disciplina" class="txt_box" id="disciplina" onkeypress="return keySort(this);">
                        <option value="INS" <?php if($_POST["disciplina"]=='INS'){ echo 'selected'; } ?>>EEI</option>
                        <option value="MEC" <?php if($_POST["disciplina"]=='MEC'){ echo 'selected'; } ?>>MEC/TUB</option>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_variavel" type="text" class="txt_box" id="ds_variavel" value="<?= $_POST["ds_variavel"] ?>" size="50">
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
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="ESPECIFICAÇÃO PADRÃO" onclick="javascript:location.href='especificacao_padrao.php'"></td>
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
						$campo = "ds_variavel";
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
				  <td width="10%"><a href="#" class="cabecalho_tabela" onclick="ordenar('disciplina','<?= $ordem ?>')">DISCIPLINA</a></td>
				  <td width="46%"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_variavel','<?= $ordem ?>')">VARIÁVEL</a></td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					
					$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
					//$sql .= "WHERE especificacao_padrao_variavel.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($variavel = mysqli_fetch_array($registro))
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
						  <td width="10%" height="18"><div align="center">
						    <?php
								if($variavel["disciplina"]=="INS")
								{ echo 'EEI';}
							   	else
							   	{ echo 'MEC/TUB';}
							
							?>
					      </div></td>
						  <td width="48%"><div align="center">
						    <?= $variavel["ds_variavel"] ?>
					      </div></td>
						  <td width="4%"><div align="center">
						 <a href="#" onclick="editar('<?= $variavel["id_variavel"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="5%"><div align="center"> <a href="#" onclick="excluir('<?= $variavel["id_variavel"] ?>','<?= $variavel["ds_variavel"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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