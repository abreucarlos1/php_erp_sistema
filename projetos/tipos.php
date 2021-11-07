<?php
/*

		Formulário de Tipos	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/tipos.php

		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
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
$db->db = 'ti';
$db->conexao_db();

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "UPDATE Projetos.tipos SET ";
	$sql .= "tipo = '" . maiusculas($_POST["tipo"]) . "', ";
	$sql .= "ds_tipo = '" . maiusculas($_POST["ds_tipo"]) . "' ";
	$sql .= "WHERE tipos.id_tipos = '" . $_POST["id_tipos"] ."' ";
	$registros = mysql_query($sql, $db->conexao) or die("Não foi possível a Atualização dos dados.".$sql);



	?>
	<script>
		alert('tipo atualizado com sucesso.');
	</script>
	<?php
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria sentença de Inclusão no bd
	$isql = "INSERT INTO Projetos.tipos ";
	$isql .= "(tipo, ds_tipo) VALUES (";
	$isql .= "'" . maiusculas($_POST["tipo"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_tipo"]) . "') ";

	$registros = mysql_query($isql,$db->conexao) or die("Não foi possível a inserção dos dados");

	?>
	<script>
		alert('tipo inserido com sucesso.');
	</script>
	<?php

}


 
if ($_GET["acao"] == "deletar")
{
	mysql_query("DELETE FROM Projetos.tipos WHERE tipos.id_tipos = '".$_GET["id_tipos"]."' ",$db->conexao) or die ("Não foi possível excluir o registro. Motivo: " . mysql_error($db->conexao));
	?>
	<script>
		alert('tipo excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . TIPOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_tipos, tipo)
{
	if(confirm('Tem certeza que deseja excluir o tipo '+tipo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_tipos='+id_tipos+'';
	}
}

function editar(id_tipos)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_tipos='+id_tipos+'';
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


function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
}


</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="subsistema" method="post" action="<?= $PHP_SELF ?>" enctype="multipart/form-data">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> <?php //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php //menu() ?></td>
      </tr>
	  <tr>
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {

				$sql = "SELECT * FROM Projetos.tipos WHERE tipos.id_tipos= '" . $_GET["id_tipos"] . "' ";
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.".$sql);
				$tipos = mysql_fetch_array($registro); 	
			 
			 
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
                      <td width="19%" class="label1">Nº TIPO </td>
                      <td width="1%"> </td>
                      <td width="80%" class="label1">TIPO</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="tipo" type="text" class="txt_box" id="requerido" value="<?= $tipos["tipo"] ?>" size="30" maxlength="2">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tipo" type="text" class="txt_box" id="requerido" size="30" value="<?= $tipos["ds_tipo"] ?>">
                      </font></td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_tipos" type="hidden" id="id_tipos" value="<?= $tipos["id_tipos"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="button" class="btn" id="Alterar" value="Alterar" onclick="requer('subsistema')"></td>
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
                      <td width="19%" class="label1">Nº TIPO </td>
                      <td width="1%"> </td>
                      <td width="80%" class="label1">TIPO</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="tipo" type="text" class="txt_box" id="requerido" size="30" maxlength="2">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tipo" type="text" class="txt_box" id="requerido" size="30">
                      </font></td>
                    </tr>
                    <tr>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="Inserir" onclick="requer('subsistema')"></td>
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
						$campo = "nr_tipo";
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
				  <td width="33%"><a href="#" class="cabecalho_tabela" onclick="ordenar('nr_tipo','<?= $ordem ?>')">Nº TIPO</a></td>
				  <td width="55%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tipo','<?= $ordem ?>')">TIPO</a></td>
				  <td width="7%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
						
					$sql = "SELECT * FROM Projetos.tipos ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
					$regcounter = mysql_num_rows($registro);
					
					$i=0;
					
					while ($tipos = mysql_fetch_array($registro))
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
						  <td width="33%"><div align="center"><?= $tipos["tipo"] ?></div></td>
						  <td width="57%"><div align="center"><?= $tipos["ds_tipo"] ?></div></td>
					      <td width="5%"><div align="center"> <a href="javascript:editar('<?= $tipos["id_tipos"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="5%"><div align="center"> <a href="javascript:excluir('<?= $tipos["id_tipos"] ?>','<?= $tipos["ds_tipo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
<?php
	$db->fecha_db();
?>

