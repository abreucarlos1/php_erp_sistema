<?
/*

		Formul�rio de ESPECIFICA��O PADR�O TIPO	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_padrao_tipo.php
	
		data de cria��o: 06/04/2006
		
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
	$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
	$sql .= "WHERE ds_especificacao_tipo = '".maiusculas($_POST["ds_especificacao_tipo"])."' ";
	$sql .= "AND processo = '" . $_POST["processo"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('tipo j� cadastrado no banco de dados.');
				location.href='<?= $PHP_SELF ?>';
			</script>
			<?
		}
	else
		{

		
			$sql = "UPDATE Projetos.especificacao_padrao_tipo SET ";
			$sql .= "ds_especificacao_tipo = '" . maiusculas($_POST["ds_especificacao_tipo"]) . "', ";
			$sql .= "processo = '" . $_POST["processo"] . "' ";
			$sql .= "WHERE id_tipo = '" . $_POST["id_tipo"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
	
			?>
			<script>
				alert('tipo atualizado com sucesso.');
			</script>
			<?
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
	$sql .= "WHERE ds_especificacao_tipo = '".maiusculas($_POST["ds_especificacao_tipo"])."' ";
	$sql .= "AND processo = '" . $_POST["processo"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('tipo j� cadastrado no banco de dados.');
				location.href='<?= $PHP_SELF ?>';
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.especificacao_padrao_tipo ";
			$incsql .= "(ds_especificacao_tipo, processo) ";
			$incsql .= "VALUES ('" . maiusculas($_POST["ds_especificacao_tipo"]) . "', '" . $_POST["processo"] . "') ";

			$registros = $db->insert($incsql,'MYSQL');

		}

	?>
	<script>
		alert('tipo inserido com sucesso.');
	</script>
	<?

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.especificacao_padrao_tipo WHERE id_especificacao_tipo = '".$_GET["id_especificacao_tipo"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('tipo exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . TIPO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_especificacao_tipo, ds_especificacao_tipo)
{
	if(confirm('Tem certeza que deseja excluir o tipo '+ds_especificacao_tipo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_especificacao_tipo='+id_especificacao_tipo+'';
	}
}

function editar(id_especificacao_tipo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_tipo='+id_especificacao_tipo+'';
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
<form name="frm_tipos" id="frm_tipos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo WHERE id_tipo= '" . $_GET["id_especificacao_tipo"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$tipo = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="27%" class="label1">tipo</td>
                      <td width="1%">&nbsp;</td>
                      <td width="72%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_especificacao_tipo" type="text" class="txt_box" id="ds_especificacao_tipo" value="<?= $tipo["ds_especificacao_tipo"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_tipo" type="hidden" id="id_tipo" value="<?= $tipo["id_tipo"] ?>">
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
                      <td width="27%" class="label1">tipo</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="72%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_especificacao_tipo" type="text" class="txt_box" id="ds_especificacao_tipo" value="<?= $_POST["ds_especificacao_tipo"] ?>" size="50">
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
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:history.back();"></td>
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
						$campo = "ds_especificacao_tipo";
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
				  <td width="91%"><a href="#" class="cabecalho_tabela" onClick="ordenar(' ds_especificacao_tipo','<?= $ordem ?>')">TIPO</a></td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
			
					// Mostra os funcion�rios
					
					/*
					$sql = "SELECT * FROM especificacao_padrao_tipo, processo ";
					$sql .= "WHERE especificacao_padrao_tipo.processo=processo.processo ";
					$sql .= "GROUP BY especificacao_padrao_tipo.ds_especificacao_tipo, processo.processo ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					*/

					$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($tipo = mysqli_fetch_array($registro))
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
						  <td width="93%" height="18"><div align="center"><?= $tipo["ds_especificacao_tipo"] ?></div></td>
						  <td width="3%"><div align="center">
						 <a href="#" onClick="editar('<?= $tipo["id_tipo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="4%"><div align="center"> <a href="#" onClick="excluir('<?= $tipo["id_tipo"] ?>','<?= $tipo["ds_especificacao_tipo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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