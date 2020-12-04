<?
/*

		Formul�rio de FUN��O	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/funcao.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> MODIFICA��O DE NOMES / SUPRESS�O DE CAMPOS - 25/04/2006
		Versão 2 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016

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
	$sql = "SELECT * FROM Projetos.funcao ";
	$sql .= "WHERE funcao = '". $_POST["funcao"] . "' ";
	$sql .= "AND ds_funcao = '". maiusculas($_POST["ds_funcao"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Fun��o j� cadastrada no banco de dados.');
			</script>
			<?
		}
	else
		{

		
			$sql = "UPDATE Projetos.funcao SET ";
			$sql .= "funcao = '" . $_POST["funcao"] . "', ";
			$sql .= "ds_funcao = '" . maiusculas($_POST["ds_funcao"]) . "') ";
			
			$sql .= "WHERE id_funcao = '" . $_POST["id_funcao"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
		
			?>
			<script>
				alert('Fun��o atualizada com sucesso.');
			</script>
			<?
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.funcao ";
	$sql .= "WHERE funcao = '". $_POST["funcao"] . "' ";
	$sql .= "AND ds_funcao = '". maiusculas($_POST["ds_funcao"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Fun��o j� cadastrada no banco de dados.');
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.funcao ";
			$incsql .= "(funcao, ds_funcao) ";
			$incsql .= "VALUES ('" . $_POST["funcao"] ."', ";
			$incsql .= "'" . maiusculas($_POST["ds_funcao"]) . "') ";

			$registros = $db->insert($incsql,'MYSQL');

			?>
			<script>
				alert('Fun��o inserida com sucesso.');
			</script>
			<?

		}

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.funcao WHERE id_funcao = '".$_GET["id_funcao"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Fun��o exclu�da com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . FUN��O . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_funcao, ds_funcao)
{
	if(confirm('Tem certeza que deseja excluir a fun��o '+ds_funcao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_funcao='+id_funcao+'';
	}
}

function editar(id_funcao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_funcao='+id_funcao+'';
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
<form name="frm_processos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.funcao WHERE id_funcao= '" . $_GET["id_funcao"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$funcao = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="8%"><span class="label1">C&Oacute;D. FUN��O</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="21%" class="label1">FUN��O</td>
                      <td width="1%">&nbsp;</td>
                      <td width="69%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="funcao" type="text" class="txt_box" id="funcao" value="<?= $funcao["funcao"] ?>" size="30" maxlength="2">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_funcao" type="text" class="txt_box" id="ds_funcao" value="<?= $funcao["ds_funcao"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_funcao" type="hidden" id="id_funcao" value="<?= $funcao["id_funcao"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onClick="javascript:history.back();"></td>
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
                  <td align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="8%"><span class="label1">C&Oacute;D. FUN��O</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="21%" class="label1">FUN��O </td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="70%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="funcao" type="text" class="txt_box" id="funcao" value="<?= $_POST["funcao"] ?>" size="30" maxlength="2">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_funcao" type="text" class="txt_box" id="ds_funcao" value="<?= $_POST["ds_funcao"] ?>" size="50">
                      </font></td>
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
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="COMPONENTES" onClick="javascript:location.href='componentes.php'"></td>
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
						$campo = "funcao";
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
				  <td width="29%"><a href="#" class="cabecalho_tabela" onClick="ordenar('funcao','<?= $ordem ?>')">C&Oacute;D. FUN��O</a></td>
				  <td width="61%"><a href="#" class="cabecalho_tabela" onClick="ordenar('ds_funcao','<?= $ordem ?>')">FUN��O</a></td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					
					$sql = "SELECT * FROM Projetos.funcao ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($funcao = mysqli_fetch_array($registro))
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
						  <td width="29%" height="18"><div align="center"><?= $funcao["funcao"] ?></div></td>
						  <td width="62%">
						    
					        <div align="center">
					          <?= $funcao["ds_funcao"] ?>
				              </div></td>
						  <td width="5%"><div align="center">
						 <a href="#" onClick="editar('<?= $funcao["id_funcao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="4%"><div align="center"> <a href="#" onClick="excluir('<?= $funcao["id_funcao"] ?>','<?= $funcao["ds_funcao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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