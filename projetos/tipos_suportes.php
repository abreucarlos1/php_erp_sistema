<?
/*

		Formul�rio de TIPOS DE SUPORTES	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/tipos_suportes.php
		
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

	$sql = "SELECT * FROM Projetos.tipos_suportes WHERE ";
	$sql .= "cd_tipo_suporte = '" . maiusculas($_POST["cd_tipo_suporte"]) . "' ";
	$sql .= "AND ds_tipo_suporte = '" . maiusculas($_POST["ds_tipo_suporte"]) . "' ";
	$sql .= "AND cm_tipo_suporte = '" . maiusculas($_POST["cm_tipo_suporte"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('tipo de suporte j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		$sql = "UPDATE Projetos.tipos_suportes SET ";
		$sql .= "cd_tipo_suporte = '" . maiusculas($_POST["cd_tipo_suporte"]) . "', ";
		$sql .= "ds_tipo_suporte = '" . maiusculas($_POST["ds_tipo_suporte"]) . "', ";
		$sql .= "cm_tipo_suporte = '" . maiusculas($_POST["cm_tipo_suporte"]) . "' ";
		$sql .= "WHERE id_tipo_suporte = '" . $_POST["id_tipo_suporte"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		?>
		<script>
			alert('tipo de suporte atualizado com sucesso.');
		</script>
		<?
	}		


}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.tipos_suportes WHERE ";
	$sql .= "cd_tipo_suporte = '" . maiusculas($_POST["cd_tipo_suporte"]) . "' ";
	$sql .= "AND ds_tipo_suporte = '" . maiusculas($_POST["ds_tipo_suporte"]) . "' ";
	$sql .= "AND cm_tipo_suporte = '" . maiusculas($_POST["cm_tipo_suporte"]) . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('tipo de suporte j� cadastrado no banco de dados.');
		</script>
		<?
	
	}
	else
	{
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.tipos_suportes ";
		$incsql .= "(cd_tipo_suporte, ds_tipo_suporte, cm_tipo_suporte ) VALUES (";
		$incsql .= "'" . maiusculas($_POST["cd_tipo_suporte"]) . "', ";
		$incsql .= "'" . maiusculas($_POST["ds_tipo_suporte"]) . "', ";
		$incsql .= "'" . maiusculas($_POST["cm_tipo_suporte"]) . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
	
		?>
		<script>
			alert('tipo de suporte inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.tipos_suportes WHERE id_tipo_suporte = '".$_GET["id_tipo_suporte"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('tipo de suporte exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . TIPOS SUPORTES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_tipo_suporte, ds_tipo_suporte)
{
	if(confirm('Tem certeza que deseja excluir o tipo de suporte '+ds_tipo_suporte+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_tipo_suporte='+id_tipo_suporte+'';
	}
}

function editar(id_tipo_suporte)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_tipo_suporte='+id_tipo_suporte+'';
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
<form name="frm_tipos_suportes" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.tipos_suportes WHERE id_tipo_suporte= '" . $_GET["id_tipo_suporte"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$tipos_suportes = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="15%" class="label1">COD. SUPORTE  </td>
                      <td width="1%">&nbsp;</td>
                      <td width="17%" class="label1">SUPORTE </td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="65%" class="label1">COMPLEMENTO</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_tipo_suporte" type="text" class="txt_box" id="cd_tipo_suporte" size="37" maxlength="20" value="<?= str_replace('"',"&quot;",$tipos_suportes["cd_tipo_suporte"]) ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tipo_suporte" type="text" class="txt_box" id="ds_tipo_suporte" size="40" value="<?= str_replace('"',"&quot;",$tipos_suportes["ds_tipo_suporte"]) ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cm_tipo_suporte" type="text" class="txt_box" id="cm_tipo_suporte" size="40" value="<?= str_replace('"',"&quot;",$tipos_suportes["cm_tipo_suporte"]) ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_tipo_suporte" type="hidden" id="id_tipo_suporte" value="<?= $tipos_suportes["id_tipo_suporte"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
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
                      <td width="13%" class="label1">c&Oacute;d. SUPORTE </td>
                      <td width="1%">&nbsp;</td>
                      <td width="17%" class="label1">SUPORTE</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="67%" class="label1">COMPLEMENTO</td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_tipo_suporte" type="text" class="txt_box" id="cd_tipo_suporte" value="<?= str_replace('\"',"&quot;",$_POST["cd_tipo_suporte"]) ?>" size="37" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tipo_suporte" type="text" class="txt_box" id="ds_tipo_suporte" value="<?= str_replace('\"',"&quot;",$_POST["ds_tipo_suporte"]) ?>" size="40">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cm_tipo_suporte" type="text" class="txt_box" id="cm_tipo_suporte" size="40" value="<?= str_replace('\"',"&quot;",$_POST["cm_tipo_suporte"]) ?>">
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:location.href='menu_mectub.php';"></td>
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
						$campo = "cd_tipo_suporte";
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
				  <td width="14%"><a href="#" class="cabecalho_tabela" onClick="ordenar('tipo','<?= $ordem ?>')">C&Oacute;D.  SUPORTE </a></td>
				  <td width="33%">SUPORTE</td>
				  <td width="45%">COMPLEMENTO</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.tipos_suportes ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($tipos_suportes = mysqli_fetch_array($registro))
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
						  <td width="14%"><div align="center"><?= $tipos_suportes["cd_tipo_suporte"] ?></div></td>
						  <td width="33%"><div align="center"><?= $tipos_suportes["ds_tipo_suporte"] ?></div></td>
						  <td width="46%"><div align="center">
						    <?= $tipos_suportes["cm_tipo_suporte"] ?>
					      </div></td>
						  <td width="3%"><div align="center"> <a href="javascript:editar('<?= $tipos_suportes["id_tipo_suporte"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $tipos_suportes["id_tipo_suporte"] ?>','<?= $tipos_suportes["ds_tipo_suporte"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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