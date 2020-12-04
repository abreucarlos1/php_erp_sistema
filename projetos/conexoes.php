<?
/*

		Formul�rio de conex�es	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/conexoes.php
		
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

	$sql = "SELECT * FROM Projetos.conexoes WHERE ";
	$sql .= "cd_conexao = '" . maiusculas($_POST["cd_conexao"]) . "' ";
	$sql .= "AND ds_conexao = '" . maiusculas($_POST["ds_conexao"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Conex�o j� cadastrada no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE Projetos.conexoes SET ";
		$sql .= "cd_conexao = '" . maiusculas($_POST["cd_conexao"]) . "', ";
		$sql .= "ds_conexao = '" . maiusculas($_POST["ds_conexao"]) . "' ";
		$sql .= "WHERE id_conexao = '" . $_POST["id_conexao"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Conex�o atualizada com sucesso.');
		</script>
		<?
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.conexoes WHERE ";
	$sql .= "cd_conexao = '" . maiusculas($_POST["cd_conexao"]) . "' ";
	$sql .= "AND ds_conexao = '" . maiusculas($_POST["ds_conexao"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Conex�o j� cadastrada no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.conexoes ";
		$incsql .= "(cd_conexao, ds_conexao) VALUES (";
		$incsql .= "'" . maiusculas($_POST["cd_conexao"]) . "', ";
		$incsql .= "'" . maiusculas($_POST["ds_conexao"]) . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
	
		?>
		<script>
			alert('Conex�o inserida com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.conexoes WHERE id_conexao = '".$_GET["id_conexao"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Conex�o exclu�da com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . CONEX&Otilde;ES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_conexao, ds_conexao)
{
	if(confirm('Tem certeza que deseja excluir a conex�o '+ds_conexao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_conexao='+id_conexao+'';
	}
}

function editar(id_conexao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_conexao='+id_conexao+'';
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
<form name="frm_conexoes" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.conexoes WHERE id_conexao= '" . $_GET["id_conexao"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$conexoes = mysqli_fetch_array($registro);			 
			 
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
                      <td width="15%" class="label1">C&oacute;d. conexão </td>
                      <td width="1%">&nbsp;</td>
                      <td width="17%" class="label1">conexão </td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="65%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_conexao" type="text" class="txt_box" id="cd_conexao" size="37" maxlength="5" value="<?= $conexoes["cd_conexao"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_conexao" type="text" class="txt_box" id="ds_conexao" size="40" value="<?= $conexoes["ds_conexao"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_conexao" type="hidden" id="id_conexao" value="<?= $conexoes["id_conexao"] ?>">
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
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="13%" class="label1">C&oacute;d. conexão </td>
                      <td width="1%">&nbsp;</td>
                      <td width="17%" class="label1">conexão</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="67%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_conexao" type="text" class="txt_box" id="cd_conexao" value="<?= $_POST["cd_conexao"] ?>" size="37" maxlength="5">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_conexao" type="text" class="txt_box" id="ds_conexao" value="<?= $_POST["ds_conexao"] ?>" size="40">
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
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();"></td>
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
						$campo = "ds_conexao";
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
				  <td width="26%"><a href="#" class="cabecalho_tabela" onClick="ordenar('tipo','<?= $ordem ?>')">C&Oacute;D. CONEX&Atilde;O </a></td>
				  <td width="62%">CONEX&Atilde;O</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?

			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.conexoes ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($conexoes = mysqli_fetch_array($registro))
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
						  <td width="26%"><div align="center"><?= $conexoes["cd_conexao"] ?></div></td>
						  <td width="65%"><div align="center"><?= $conexoes["ds_conexao"] ?></div></td>
						  <td width="5%"><div align="center"> <a href="javascript:editar('<?= $conexoes["id_conexao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $conexoes["id_conexao"] ?>','<?= $conexoes["ds_conexao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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