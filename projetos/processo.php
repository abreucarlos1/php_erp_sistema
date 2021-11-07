<?php
/*

		Formulário de PROCESSO	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/processo.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> MODIFICAÇÃO DE NOMES / SUPRESSÃO DE CAMPOS - 20/04/2006
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
	$sql = "SELECT * FROM Projetos.processo ";
	$sql .= "WHERE processo = '". $_POST["processo"] . "' ";
	$sql .= "AND ds_processo = '". maiusculas($_POST["ds_processo"]) . "' ";
	$sql .= "AND norma = '". maiusculas($_POST["norma"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Processo já cadastrado no banco de dados.');
			</script>
			<?php
		}
	else
		{

		
			$sql = "UPDATE Projetos.processo SET ";
			$sql .= "processo = '" . $_POST["processo"] . "', ";
			$sql .= "ds_processo = '" . maiusculas($_POST["ds_processo"]) . "', ";
			$sql .= "norma = '" . maiusculas($_POST["norma"]) . "' ";
			
			$sql .= "WHERE id_processo = '" . $_POST["id_processo"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
		
			?>
			<script>
				alert('Processo atualizado com sucesso.');
			</script>
			<?php
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.processo ";
	$sql .= "WHERE processo = '". $_POST["processo"] . "' ";
	$sql .= "AND ds_processo = '". maiusculas($_POST["ds_processo"]) . "' ";
	$sql .= "AND norma = '". maiusculas($_POST["norma"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Processo já cadastrado no banco de dados.');
			</script>
			<?php
		}
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.processo ";
			$isql .= "(processo, ds_processo, norma) ";
			$isql .= "VALUES ('" . $_POST["processo"] ."', ";
			$isql .= "'" . maiusculas($_POST["ds_processo"]) . "', '" . maiusculas($_POST["norma"]) . "') ";

			$registros = $db->insert($isql,'MYSQL');

			?>
			<script>
				alert('Processo inserido com sucesso.');
			</script>
			<?php

		}

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.processo WHERE id_processo = '".$_GET["id_processo"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Processo excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . PROCESSO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_processo, ds_processo)
{
	if(confirm('Tem certeza que deseja excluir o processo '+ds_processo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_processo='+id_processo+'';
	}
}

function editar(id_processo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_processo='+id_processo+'';
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
<form name="frm_processos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.processo WHERE id_processo= '" . $_GET["id_processo"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$processo = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="8%"><span class="label1">CÓD. proCESSO</span></td>
                      <td width="1%"> </td>
                      <td width="21%" class="label1">PROCESSO</td>
                      <td width="1%"> </td>
                      <td width="69%" class="label1">norma</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="processo" type="text" class="txt_boxcap" id="processo" value="<?= $processo["processo"] ?>" size="30" maxlength="2">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_processo" type="text" class="txt_box" id="ds_processo" value="<?= $processo["ds_processo"] ?>" size="50">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="norma" type="text" class="txt_box" id="norma" value="<?= $processo["norma"] ?>" size="50">
                      </font></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_processo" type="hidden" id="id_processo" value="<?= $processo["id_processo"] ?>">
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
                      <td width="8%"><span class="label1">CÓD. PROCESSO</span></td>
                      <td width="0%"> </td>
                      <td width="21%" class="label1">PROCESSO </td>
                      <td width="1%" class="label1"> </td>
                      <td width="70%" class="label1">NORMA</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="processo" type="text" class="txt_boxcap" id="processo" value="<?= $_POST["processo"] ?>" size="30" maxlength="2">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_processo" type="text" class="txt_box" id="ds_processo" value="<?= $_POST["ds_processo"] ?>" size="50">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="norma" type="text" class="txt_box" id="norma" value="<?= $_POST["norma"] ?>" size="50" maxlength="255">
                      </font></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="COMPONENTES" onclick="javascript:location.href='componentes.php'"></td>
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
						$campo = " processo ";
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
				  <td width="18%"><a href="#" class="cabecalho_tabela" onclick="ordenar('processo','<?= $ordem ?>')">CÓD. PROCESSO</a></td>
				  <td width="16%"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_processo','<?= $ordem ?>')">PROCESSO</a></td>
				  <td width="56%"><a href="#" class="cabecalho_tabela" onclick="ordenar('norma','<?= $ordem ?>')">NORMA</a></td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
				
					$sql = "SELECT * FROM Projetos.processo ";
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i = 0;
					
					while ($processo = mysqli_fetch_array($registro))
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
						  <td width="18%" height="18"><div align="center"><?= $processo["processo"] ?></div></td>
						  <td width="16%">
						    <div align="left">
						      <?= $processo["ds_processo"] ?>
				            </div></td>
						  <td width="58%"><div align="left">
                            <?= $processo["norma"] ?>
                          </div></td>
						  <td width="4%"><div align="center">
						 <a href="#" onclick="editar('<?= $processo["id_processo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="4%"><div align="center"> <a href="#" onclick="excluir('<?= $processo["id_processo"] ?>','<?= $processo["ds_processo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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