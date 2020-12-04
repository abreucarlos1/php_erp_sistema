<?
/*

		Formul�rio de FINALIDADES DE CABOS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cabos_finalidades.php
		
		data de cria��o: 23/05/2006
		
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

	$sql = "SELECT * FROM Projetos.cabos_finalidades WHERE ";
	$sql .= "ds_finalidade = '" . $_POST["ds_finalidade"] . "' ";
	$sql .= "cd_finalidade = '" . $_POST["cd_finalidade"]. "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Finalidade de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE Projetos.cabos_finalidades SET ";
		$sql .= "ds_finalidade = '" . $_POST["ds_finalidade"] . "', ";
		$sql .= "cd_finalidade = '" . $_POST["cd_finalidade"] . "' ";
		$sql .= "WHERE id_cabo_finalidade = '" . $_POST["id_cabo_finalidade"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('Finalidade de cabo atualizado com sucesso.');
		</script>
		<?
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.cabos_finalidades WHERE ";
	$sql .= "ds_finalidade = '" . $_POST["ds_finalidade"] . "' ";
	$sql .= "cd_finalidade = '" . $_POST["cd_finalidade"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Finalidade de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.cabos_finalidades ";
		$incsql .= "(ds_finalidade, cd_finalidade) VALUES (";
		$incsql .= "'" . $_POST["ds_finalidade"] . "', ";
		$incsql .= "'" . $_POST["cd_finalidade"] . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
	
		?>
		<script>
			alert('Finalidade de cabo inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.cabos_finalidades WHERE id_cabo_finalidade = '".$_GET["id_cabo_finalidade"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Finalidade de cabo exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . FINALIDADE DE CABOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_cabo_finalidade, ds_finalidade)
{
	if(confirm('Tem certeza que deseja excluir a finalidade de cabo '+ds_finalidade+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_cabo_finalidade='+id_cabo_finalidade+'';
	}
}

function editar(id_cabo_finalidade)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_cabo_finalidade='+id_cabo_finalidade+'';
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
<form name="frm_cabos_finalidades" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.cabos_finalidades WHERE id_cabo_finalidade = '" . $_GET["id_cabo_finalidade"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$cabos_finalidades = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="26%" class="label1">C&Oacute;DIGO FINALIDADE </td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="23%" class="label1">FINALIDADE</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="50%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_finalidade" type="text" class="txt_boxcap" id="cd_finalidade" size="40" value="<?= $cabos_finalidades["cd_finalidade"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_finalidade" type="text" class="txt_boxcap" id="ds_finalidade" size="40" value="<?= $cabos_finalidades["ds_finalidade"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_cabo_finalidade" type="hidden" id="id_cabo_finalidade" value="<?= $cabos_finalidades["id_cabo_finalidade"] ?>">
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
                      <td width="23%" class="label1">c&Oacute;DIGO FINALIDADE </td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="45%" class="label1">FINALIDADE</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="31%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_finalidade" type="text" class="txt_boxcap" id="cd_finalidade" value="<?= $_POST["cd_finalidade"] ?>" size="40">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_finalidade" type="text" class="txt_boxcap" id="ds_finalidade" value="<?= $_POST["ds_finalidade"] ?>" size="40">
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
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Locais" type="button" class="btn" id="Locais" value="TIPOS DE CABOS" onClick="javascript:location.href='cabos_tipos.php';"></td>
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
				  <td width="29%">C&Oacute;DIGO</td>
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = "ordem_finalidade";
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
				  <td width="51%">FINALIDADE</td>
				  <td width="7%"  class="cabecalho_tabela">E</td>
				  <td width="7%"  class="cabecalho_tabela">D</td>
				  <td width="6%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					// Arquivo de Inclusão de conex�o com o banco
			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.cabos_finalidades ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($cabos_finalidades = mysqli_fetch_array($registro))
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
						  <td width="29%"><div align="center">
						    <?= $cabos_finalidades["cd_finalidade"] ?>
					      </div></td>
						  <td width="59%"><div align="center"><?= $cabos_finalidades["ds_finalidade"] ?></div></td>
						  <td width="6%"><div align="center"> <a href="javascript:editar('<?= $cabos_tipos["id_cabo_finalidade"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="6%"><div align="center"> <a href="javascript:excluir('<?= $cabos_finalidades["id_cabo_finalidade"] ?>','<?= $cabos_tipos["ds_finalidade"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?
					}
					// Libera a mem�ria
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