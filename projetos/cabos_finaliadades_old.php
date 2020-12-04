<?
/*

		Formul�rio de TIPOS DE CABOS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cabos_tipos.php
		
		Verifica��o de a��es:
		
		Incluir : NOK
		Alterar : NOK
		Deletar : NOK
		Permiss�es : NOK
		Valida��es : NOK
		Coment�rios : NOK		
		
		data de cria��o: 19/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
*/
	
//Obt�m os dados do usu�rio
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php");
	exit;
}
		
include ("../includes/layout.php");
include ("../includes/conectdbproj.inc");
include ("../includes/tools.inc");


//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "SELECT * FROM cabos_tipos WHERE ";
	$sql .= "ds_cabo_tipo = '" . $_POST["ds_cabo_tipo"] . "' ";
	$sql .= "AND ds_formacao = '" . $_POST["ds_formacao"] . "' ";
	$regis = mysql_query($sql, $conexao) or die("N�o foi poss�vel a sele��o dos dados.");
	
	if(mysql_num_rows($regis)>0)
	{
		?>
		<script>
			alert('tipo de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE cabos_tipos SET ";
		$sql .= "ds_cabo_tipo = '" . $_POST["ds_cabo_tipo"] . "', ";
		$sql .= "ds_formacao = '" . $_POST["ds_formacao"] . "' ";
		$sql .= "WHERE id_cabo_tipo = '" . $_POST["id_cabo_tipo"] ."' ";
		$registros = mysql_query($sql, $conexao) or die("N�o foi poss�vel a Atualização dos dados.");
		?>
		<script>
			alert('tipo de cabo atualizado com sucesso.');
		</script>
		<?
	}		

mysql_close($conexao);	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM cabos_tipos WHERE ";
	$sql .= "ds_cabo_tipo = '" . $_POST["ds_cabo_tipo"] . "' ";
	$sql .= "AND ds_formacao = '" . $_POST["ds_formacao"] . "' ";
	$regis = mysql_query($sql, $conexao) or die("N�o foi poss�vel a sele��o dos dados.");
	
	if(mysql_num_rows($regis)>0)
	{
		?>
		<script>
			alert('tipo de cabo j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO cabos_tipos ";
		$incsql .= "(ds_cabo_tipo, ds_formacao) VALUES (";
		$incsql .= "'" . $_POST["ds_cabo_tipo"] . "', ";
		$incsql .= "'" . $_POST["ds_formacao"] . "') ";
	
		$registros = mysql_query($incsql,$conexao) or die("Não foi possível a inserção dos dados" . $incsql);
	
		?>
		<script>
			alert('tipo de cabo inserido com sucesso.');
		</script>
		<?
	}
mysql_close($conexao);
}


 
if ($_GET["acao"] == "deletar")
{
	mysql_query("DELETE FROM cabos_tipos WHERE id_cabo_tipo = '".$_GET["id_cabo_tipo"]."' ",$conexao) or die ("N�o foi poss�vel excluir o registro. Motivo: " . mysql_error($conexao));
	?>
	<script>
		alert('tipo de cabo exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . TIPOS DE CABOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<!-- Javascript para declara��o de vari�veis / checagem do estilo - MAC/PC -->
<script language="JavaScript" src="../includes/checkstyle.js" type="text/javascript"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_cabo_tipo, ds_formacao)
{
	if(confirm('Tem certeza que deseja excluir o tipo de cabo '+ds_formacao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_cabo_tipo='+id_cabo_tipo+'';
	}
}

function editar(id_cabo_tipo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_cabo_tipo='+id_cabo_tipo+'';
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
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="cabos_tipos" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"><? titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
 	  </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? menu() ?></td>
      </tr>
	  <tr>
        <td>
		
			
			<?
			
			// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				include ("../includes/conectdbproj.inc");
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM cabos_tipos WHERE id_cabo_tipo = '" . $_GET["id_cabo_tipo"] . "' ";
				$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.".$sql);
				$cabos_tipos = mysql_fetch_array($registro); 	
			 
			 
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
                      <td width="27%" class="label1">FINALIDADE</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="61%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_finalidade" type="text" class="txt_boxcap" id="requerido" size="40" value="<?= $cabos_tipos["ds_formacao"] ?>">
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
                    <input name="Alterar" type="button" class="btn" id="Alterar" value="Alterar" onClick="requer('cabos_tipos')">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='<?= $PHP_SELF ?>';"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?
				mysql_close($conexao);
			
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
                      <td width="38%" class="label1">FINALIDADE</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="50%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_finalidade" type="text" class="txt_boxcap" id="requerido" value="<?= $_POST["ds_formacao"] ?>" size="40">
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
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="Inserir" onClick="requer('cabos_tipos')">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='menuprojetos.php';">
                    <input name="Locais" type="button" class="btn" id="Locais" value="LOCAIS" onClick="javascript:location.href='locais.php';"></td>
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
						$campo = "ds_cabo_tipo";
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
				  <td width="26%"><a href="#" class="cabecalho_tabela" onClick="ordenar('tipo','<?= $ordem ?>')">TIPO</a></td>
				  <td width="62%">FORMA&Ccedil;&Atilde;O</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					// Arquivo de Inclusão de conex�o com o banco
					include ("../includes/conectdbproj.inc");
			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM cabos_tipos ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);
					$i=0;
					
					while ($cabos_tipos = mysql_fetch_array($registro))
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
						  <td width="26%"><div align="center"><?= $cabos_tipos["ds_cabo_tipo"] ?></div></td>
						  <td width="65%"><div align="center"><?= $cabos_tipos["ds_formacao"] ?></div></td>
						  <td width="5%"><div align="center"> <a href="javascript:editar('<?= $cabos_tipos["id_cabo_tipo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $cabos_tipos["id_cabo_tipo"] ?>','<?= $cabos_tipos["ds_formacao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?
					}
					// Libera a mem�ria
					mysql_close($conexao);
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


