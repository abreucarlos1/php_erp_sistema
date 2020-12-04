<?
/*

		Formul�rio de �REA	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/area.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> VERIFICA��O OS - 12/04/2006
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
	$sql = "SELECT * FROM Projetos.area ";
	$sql .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
	$sql .= "AND nr_area = '".$_POST["nr_area"] . "' ";
	$sql .= "AND ds_area = '".maiusculas($_POST["ds_area"]) . "' ";
	$sql .= "AND ds_projeto = '".maiusculas($_POST["ds_projeto"]) . "' ";
	$sql .= "AND id_cliente = '".$_POST["id_cliente"] . "' ";
	$sql .= "AND ds_divisao = '".maiusculas($_POST["ds_divisao"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('�rea j� cadastrada no banco de dados.');
			</script>
			<?
		}
	else
		{

		
			$sql = "UPDATE Projetos.area SET ";
			$sql .= "id_os = '" . $_SESSION["id_os"] . "', ";
			$sql .= "nr_area = '" . $_POST["nr_area"] . "', ";
			$sql .= "ds_area = '" . maiusculas($_POST["ds_area"]) . "', ";
			$sql .= "ds_projeto = '" . maiusculas($_POST["ds_projeto"]) . "', ";
			$sql .= "id_cliente = '" . $_POST["id_cliente"] . "', ";
			$sql .= "ds_divisao = '" . maiusculas($_POST["ds_divisao"]) . "' ";
			$sql .= "WHERE id_area = '" . $_POST["id_area"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');	
		
			?>
			<script>
				alert('�rea atualizada com sucesso.');
			</script>
			<?
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.area ";
	$sql .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
	$sql .= "AND nr_area = '".$_POST["nr_area"] . "' ";
	$sql .= "AND ds_area = '".maiusculas($_POST["ds_area"]) . "' ";
	$sql .= "AND ds_projeto = '".maiusculas($_POST["ds_projeto"]) . "' ";
	$sql .= "AND id_cliente = '".$_POST["id_cliente"] . "' ";
	$sql .= "AND ds_divisao = '".maiusculas($_POST["ds_divisao"]) . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('�rea j� cadastrada no banco de dados.');
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.area ";
			$incsql .= "(id_os, nr_area, ds_area, ";
			$incsql .= "ds_projeto, ds_divisao, id_cliente) ";
			$incsql .= "VALUES ('" . $_SESSION["id_os"] . "', '" . $_POST["nr_area"] ."', ";
			$incsql .= "'" . maiusculas($_POST["ds_area"]) . "', '" . maiusculas($_POST["ds_projeto"]) . "', ";
			$incsql .= "'" . maiusculas($_POST["ds_divisao"]) . "', '" . $_POST["id_cliente"] . "' ) ";

			$registros = $db->insert($incsql,'MYSQL');

			?>
			<script>
				alert('�rea inserida com sucesso.');
			</script>
			<?
		}


}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	
	//$sql = "DELETE area, subsistema, malhas, componentes, especificacao_tecnica, especificacao_tecnica_detalhes FROM area, subsistema, malhas, componentes, especificacao_tecnica, especificacao_tecnica_detalhes ";
	
	$sql = "DELETE area, subsistema, malhas, componentes, especificacao_tecnica, especificacao_tecnica_detalhes FROM Projetos.area ";
	$sql .= "LEFT JOIN Projetos.subsistema ON(area.id_area = subsistema.id_area) ";
	$sql .= "LEFT JOIN Projetos.malhas ON(subsistema.id_subsistema = malhas.id_subsistema) ";
	$sql .= "LEFT JOIN Projetos.componentes ON (malhas.id_malha = componentes.id_malha) ";
	$sql .= "LEFT JOIN Projetos.especificacao_tecnica ON (especificacao_tecnica.id_componente = componentes.id_componente) ";
	$sql .= "LEFT JOIN Projetos.especificacao_tecnica_detalhes ON (especificacao_tecnica_detalhes.id_especificacao_tecnica = especificacao_tecnica.id_especificacao_tecnica) ";

	$sql .= "WHERE area.id_area = '".$_GET["id_area"]."' ";
	
	$db->delete($sql,'MYSQL');
	
	?>
	<script>
		alert('�rea exclu�da com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . &Aacute;REA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_area, ds_area)
{
	if(confirm('Tem certeza que deseja excluir a �rea '+ds_area+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_area='+id_area+'';
	}
}

function editar(id_area)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_area='+id_area+'';
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
<form name="frm_areas" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.area WHERE id_area= '" . $_GET["id_area"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$areas = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="12%"><span class="label1">CLIENTE</span></td>
                      <td width="2%">&nbsp;</td>
                      <td width="86%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td height="44"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_cliente" class="txt_box" id="id_cliente" onkeypress="return keySort(this);">
                          <option value="">NENHUMA</option>
                          <?
						  	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
							$sql .= "WHERE empresas.id_unidade=unidades.id_unidade ORDER BY empresa";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["id_empresa_erp"] ?>"<? if($regs["id_empresa_erp"]==$areas["id_cliente"]){ echo 'selected';} ?>><?= $regs["empresa"]. " - " .$regs["descricao"] . " - " . $regs["unidade"] ?></option>
                          <?
								}
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="27%"><span class="label1">projeto</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="1%"><span class="label1">divis&Atilde;O</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="8%" class="label1">NR. &Aacute;REA </td>
                      <td width="1%">&nbsp;</td>
                      <td width="27%" class="label1">&Aacute;REA</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="32%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_projeto" type="text" class="txt_box" id="ds_projeto" value="<?= $areas["ds_projeto"] ?>" size="70">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_divisao" type="text" class="txt_box" id="ds_divisao" value="<?= $areas["ds_divisao"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_area" type="text" class="txt_box" id="nr_area" value="<?= $areas["nr_area"] ?>" size="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_area" type="text" class="txt_box" id="ds_area" value="<?= $areas["ds_area"] ?>" size="70">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_area" type="hidden" id="id_area" value="<?= $areas["id_area"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir" type="button" class="btn" id="Inserir22" value="VOLTAR" onClick="javascript:history.back();"></td>
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
                  <td align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="12%"><span class="label1">CLIENTE</span></td>
                      <td width="2%">&nbsp;</td>
                      <td width="86%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td height="44"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_cliente" class="txt_box" id="id_cliente" onkeypress="return keySort(this);">
                          <option value="">NENHUMA</option>
                          <?
						  	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";							
							$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ORDER BY empresa";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["id_empresa_erp"] ?>"<? if($regs["id_empresa_erp"]==$_POST["id_cliente"]){ echo 'selected';}?>>
                            <?= $regs["empresa"]. " - " .$regs["descricao"] . " - " . $regs["unidade"] ?>
                            </option>
                          <?
								}
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="27%"><span class="label1">projeto</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="1%"><span class="label1">divis&Atilde;O</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="8%" class="label1">NR. &Aacute;REA </td>
                      <td width="1%">&nbsp;</td>
                      <td width="27%" class="label1">&Aacute;REA</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="32%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_projeto" type="text" class="txt_box" id="ds_projeto" size="70" value="<?= $_SESSION["OSdesc"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_divisao" type="text" class="txt_box" id="ds_divisao" size="50" value="<?= $_POST["ds_divisao"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_area" type="text" class="txt_box" id="nr_area" size="20" value="<?= $_POST["nr_area"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_area" type="text" class="txt_box" id="ds_area" size="70" value="<?= $_POST["ds_area"] ?>">
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
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="SUBSISTEMA" onClick="javascript:location.href='subsistema.php'"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><span class="label1">regs:<font size="2" face="Arial, Helvetica, sans-serif">
                  <?
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
						
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
				  <td width="6%"><a href="#" class="cabecalho_tabela" onClick="ordenar('os','<?= $ordem ?>')">OS</a></td>
				  <td width="22%"><a href="#" class="cabecalho_tabela" onClick="ordenar('ds_projeto','<?= $ordem ?>')">PROJETO</a></td>
				  <td width="27%"><a href="#" class="cabecalho_tabela" onClick="ordenar('nr_area','<?= $ordem ?>')">DIVIS&Atilde;O</a></td>
				  <td width="10%"><a href="#" class="cabecalho_tabela" onClick="ordenar('ds_area','<?= $ordem ?>')">NR. &Aacute;REA</a></td>
				  <td width="27%"><a href="#" class="cabecalho_tabela" onClick="ordenar('ds_divisao','<?= $ordem ?>')">&Aacute;REA </a></td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.area, ".DATABASE.".OS  ";
					$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
					$sql .= "AND area.id_os = OS.id_os ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i = 0;
					
					while ($areas = mysql_fetch_array($registro))
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
						  <td width="6%"><div align="center"><?= $areas["os"] ?></div></td>
						  <td width="22%"><div align="center"><?= $areas["ds_projeto"] ?></div></td>
						  <td width="27%"><div align="center">
						    <?= $areas["ds_divisao"] ?>
</div></td>
						  <td width="10%"><div align="center"><?= $areas["nr_area"] ?></div></td>
						  
						  <td width="28%"><div align="center">
						    <div align="center">
                              <?= $areas["ds_area"] ?>
                            </div>
					      </div></td>
						  <td width="3%"><div align="center">
						 <a href="#" onClick="editar('<?= $areas["id_area"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="4%"><div align="center"> <a href="#" onClick="excluir('<?= $areas["id_area"] ?>','<?= $areas["ds_area"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
