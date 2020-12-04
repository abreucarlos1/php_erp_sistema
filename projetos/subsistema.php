<?
/*

		Formul�rio de Subsistemas	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/subsistema.php
		
		data de cria��o: 05/04/2006
		
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

	$sql = "UPDATE Projetos.subsistema SET ";
	$sql .= "id_area = '" . $_POST["id_area"] . "', ";
	$sql .= "nr_subsistema = '" . $_POST["nr_subsistema"] . "', ";
	$sql .= "subsistema = '" . maiusculas($_POST["subsistema"]) . "' ";
	$sql .= "WHERE subsistema.id_subsistema = '" . $_POST["id_subsistema"] ."' ";
	
	$registros =$db->update($sql,'MYSQL');
	
	?>
	<script>
		alert('Subsistema atualizado com sucesso.');
	</script>
	<?
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria senten�a de Inclusão no bd
	$incsql = "INSERT INTO Projetos.subsistema ";
	$incsql .= "(id_area, nr_subsistema, subsistema) VALUES (";
	$incsql .= "'" . $_POST["id_area"] . "', ";
	$incsql .= "'" . $_POST["nr_subsistema"] . "', ";
	$incsql .= "'" . maiusculas($_POST["subsistema"]) . "') ";


	$registros = $db->insert($incsql,'MYSQL');

	?>
	<script>
		alert('Subsistema inserido com sucesso.');
	</script>
	<?

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.subsistema WHERE subsistema.id_subsistema = '".$_GET["id_subsistema"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Subsistema exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . SUBSISTEMA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_subsistema, subsistema)
{
	if(confirm('Tem certeza que deseja excluir o subsistema '+subsistema+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_subsistema='+id_subsistema+'';
	}
}

function editar(id_subsistema)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_subsistema='+id_subsistema+'';
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


function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_subsistema" method="post" action="<?= $PHP_SELF ?>" enctype="multipart/form-data">
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

				$sql = "SELECT * FROM Projetos.subsistema WHERE subsistema.id_subsistema= '" . $_GET["id_subsistema"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$subsistema = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="12%" class="label1">&Aacute;REA</td>
                      <td width="0%">&nbsp;</td>
                      <td width="12%" class="label1">N&ordm; SUBSISTEMA </td>
                      <td width="1%">&nbsp;</td>
                      <td width="75%" class="label1">SUBSISTEMA</td>
                    </tr>
                    <tr>
                      <td>
                        <select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
						  	$sqlarea = "SELECT * FROM Projetos.area ";
							$sqlarea .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ORDER BY ds_area";
							
							$regarea = $db->select($sqlarea,'MYSQL');
							
							while ($regs = mysqli_fetch_array($regarea))
								{
								  ?>
                                  <option value="<?= $regs["id_area"] ?>" <? if($subsistema["id_area"]==$regs["id_area"]) { echo "selected"; } ?>>
                                    <?= $regs["nr_area"] . " - " . $regs["ds_projeto"] . " - " . $regs["ds_area"] ?> 
                                    </option>
                                  <?
								}
							?>
                        </select>
                      </td>
                      <td>&nbsp;</td>
                      <td>
                        <input name="nr_subsistema" type="text" class="txt_box" id="nr_subsistema" size="30" value="<?= $subsistema["nr_subsistema"] ?>">
                      </td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="subsistema" type="text" class="txt_box" id="subsitema" size="30" value="<?= $subsistema["subsistema"] ?>">
                      </font></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_subsistema" type="hidden" id="id_subsistema" value="<?= $subsistema["id_subsistema"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onClick="javascript:location.href='<?= $PHP_SELF ?>'"></td>
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
                      <td width="12%" class="label1">&Aacute;REA</td>
                      <td width="0%">&nbsp;</td>
                      <td width="12%" class="label1">N&ordm; SUBSISTEMA </td>
                      <td width="1%">&nbsp;</td>
                      <td width="75%" class="label1">SUBSISTEMA</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?
							//onChange="javascript:document.forms[0].nr_subsistema.focus()"
						  	$sqlarea = "SELECT * FROM Projetos.area ";
							$sqlarea .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ORDER BY ds_area";
							
							$regarea = $db->select($sqlarea,'MYSQL');
							
							while ($regs = mysqli_fetch_array($regarea))
								{
									?>
                          <option value="<?= $regs["id_area"] ?>"<? if($_POST["id_area"]==$regs["id_area"]) { echo "selected"; } ?>>
                            <?= $regs["nr_area"] . " - " . $regs["ds_projeto"] . " - " . $regs["ds_area"] ?>
                            </option>
                          <?
								}
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_subsistema" type="text" class="txt_box" id="nr_subsistema" size="30" value="<?= $_POST["nr_subsistema"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="subsistema" type="text" class="txt_box" id="subsistema" size="30" value="<?= $_POST["subsistema"] ?>">
                      </font></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
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
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:location.href='menu_geral.php'">
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="MALHAS" onClick="javascript:location.href='malhas.php'">
					</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><span class="label1">regs:<font size="2" face="Arial, Helvetica, sans-serif">
                  <?
							$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
							$sql .= "WHERE subsistema.id_area=area.id_area ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							
							$regs = $db->select($sql,'MYSQL');
							
							$regcounter = $db->numero_registros;
							
							echo $regcounter;
						?>
                  </font></span></td>
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
						$campo = "nr_subsistema";
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
				  <td width="33%"><a href="#" class="cabecalho_tabela" onClick="ordenar('nr_area','<?= $ordem ?>')">�REA</a></td>
				  <td width="33%"><a href="#" class="cabecalho_tabela" onClick="ordenar('nr_subsistema','<?= $ordem ?>')">N� SUBSISTEMA</a></td>
				  <td width="55%"><a href="#" class="cabecalho_tabela" onClick="ordenar('subsistema','<?= $ordem ?>')">SUBSISTEMA</a></td>
				  <td width="7%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					
					$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
					$sql .= "WHERE subsistema.id_area=area.id_area ";
					$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($subsistema = mysql_fetch_array($registro))
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
						  <td width="33%"><div align="center"><?= $subsistema["nr_area"] . " - " . $subsistema["ds_area"] . " - " . $subsistema["ds_divisao"] ?></div></td>
						  <td width="33%"><div align="center"><?= $subsistema["nr_subsistema"] ?></div></td>
						  <td width="57%"><div align="center"><?= $subsistema["subsistema"] ?></div></td>
					      <td width="5%"><div align="center"> <a href="javascript:editar('<?= $subsistema["id_subsistema"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="5%"><div align="center"> <a href="javascript:excluir('<?= $subsistema["id_subsistema"] ?>','<?= $subsistema["subsistema"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
