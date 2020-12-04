<?
/*

		Formul�rio de Replica��o de Componentes
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/replicarmalhas.php
		
		data de cria��o: 28/04/2006
		
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
		
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");


$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	$sql = "SELECT * FROM Projetos.malhas ";
	$sql .= "WHERE id_subsistema = '".$_POST["id_subsistema"]."' ";
	$sql .= "AND id_processo = '".$_POST["id_processo"] . "' ";
	$sql .= "AND nr_malha = '".maiusculas($_POST["nr_malha"]) . "' ";
	$sql .= "AND tp_malha = '".$_POST["tp_malha"]. "' ";
	$sql .= "AND ds_servico = '".maiusculas($_POST["ds_servico"]). "' ";
	$verify = mysql_query($sql, $db->conexao) or die("Não foi possível fazer a seleção.");
	$regs = mysql_num_rows($verify);
	if ($regs>0)
		{
			?>
			<script>
				alert('Malha j� cadastrada no banco de dados.');
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.malhas ";
			$incsql .= "(id_subsistema, id_processo, nr_malha, tp_malha, ds_servico) ";
			$incsql .= "VALUES ('" . $_POST["id_subsistema"] . "', '" . $_POST["id_processo"] ."', ";
			$incsql .= "'" . maiusculas($_POST["nr_malha"]) . "', '" . $_POST["tp_malha"] . "', ";
			$incsql .= "'" . maiusculas($_POST["ds_servico"]) . "') ";

			$registros = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados");
			
			$malha = mysql_insert_id($db->conexao);
			
			$sql = "SELECT * FROM Projetos.componentes ";
			$sql .= "WHERE id_malha = '" . $_POST["id_malha"] . "' ";
			$registros = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados");
			while($regs = mysql_fetch_array($registros))
			{
				if($_POST[$regs["id_componente"]]=="1")
				{
					//Cria senten�a de Inclusão no bd
					$incsql = "INSERT INTO Projetos.componentes ";
					$incsql .= "(id_funcao, id_malha, id_local, id_dispositivo, omit_proc, cd_tag_eq) VALUES (";
					$incsql .= "'" . $regs["id_funcao"] . "', ";
					$incsql .= "'" . $malha . "', ";
					$incsql .= "'" . $regs["id_local"] . "', ";
					$incsql .= "'" . $regs["id_dispositivo"] . "', ";
					$incsql .= "'" . $_POST["omit_proc"] . "', ";
					$incsql .= "'" . $regs["cd_tag_eq"] . "') ";
					$regis = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);
					
					$comp = mysql_insert_id($db->conexao);
					
					$sql1 = "SELECT * FROM Projetos.especificacao_padrao ";
					$sql1 .= "WHERE id_dispositivo = '" . $regs["id_dispositivo"] . "' ";
					$sql1 .= "AND id_funcao = '" . $regs["id_funcao"] . "' ";
					$sql1 .= "AND id_tipo = '" . $regs["id_tipo"] . "' ";
					$registros1 = mysql_query($sql1, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
					$count = mysql_num_rows($registros1);
					if($count>0)
					{
						$regs1 = mysql_fetch_array($registros1);
						
						//Cria senten�a de Inclusão no bd
						$incsql1 = "INSERT INTO Projetos.especificacao_tecnica ";
						$incsql1 .= "(id_especificacao_padrao, id_componente) ";
						$incsql1 .= "VALUES (";
						$incsql1 .= "'" . $regs1["id_especificacao_padrao"] . "', ";
						$incsql1 .= "'" . $comp . "') ";
						$registros2 = mysql_query($incsql1,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);
						
						$esp = mysql_insert_id($db->conexao);
						
						$sql2 = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
						$sql2 .= "WHERE id_especificacao_padrao = '" . $regs1["id_especificacao_padrao"] . "' ";
						$regis = mysql_query($sql2, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
						
						while($reg = mysql_fetch_array($regis))
						{
							//Cria senten�a de Inclusão no bd
							$incsql2 = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
							$incsql2 .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
							$incsql2 .= "VALUES (";
							$incsql2 .= "'" . $esp . "', ";
							$incsql2 .= "'" . $reg["id_especificacao_detalhe"] . "', ";
							$incsql2 .= "'" . $reg["conteudo"] . "') ";
							$regist = mysql_query($incsql2,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql2);			
						
						}
						
						
					
					}
			
				}
			}
			
			?>
			<script>
				alert('Malha duplicada com sucesso.');
				location.href='componentes.php';
			</script>
			<?
			

		}
}

?>

<html>
<head>
<title>: : . REPLICAR MALHA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheComboProcesso(combobox_destino, combobox, index)
{
/*
var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
*/	
	
<?
/*
$sql = "SELECT * FROM processo ";
$sql .= " ORDER BY processo ";

$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados.". $sql);

	while ($cont = mysql_fetch_array($reg))
	{
*/
	
	?>
	
	/*
		if(combobox.options[index].value=='<?= $cont["funcao"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["funcao"] ?>','<?= $cont["processo"] ?>');
		}
*/

<? //} ?>
		

}

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheComboFuncao(combobox_destino, combobox, index)
{
/*
var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
*/	
<?

/*
$sql = "SELECT * FROM malhas, processo, funcao ";
$sql .= "WHERE processo.processo = malhas.processo ";
$sql .= "AND processo.funcao = funcao.funcao ";
$sql .= " ORDER BY malhas.processo ";

$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados.". $sql);

	while ($cont = mysql_fetch_array($reg))
	{
	*/
	?>
	/*
		if(combobox.options[index].value=='<?= $cont["id_malha"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["funcao"] . " - " . $cont["ds_funcao"] ?>','<?= $cont["funcao"] ?>');
		}

	*/
<? //} ?>
		

}




function excluir(id_componente, componente)
{
	if(confirm('Tem certeza que deseja excluir o componente '+componente+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_componente='+id_componente+'';
	}
}

function editar(id_componente)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_componente='+id_componente+'';
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
<form name="componentes" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? //menu() ?></td>
      </tr>
	  <tr>
        <td>
			<div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td>&nbsp;</td>
                  <td align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="10%"><span class="label1">subsistema</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="9%" class="label1">PROCESSO</td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="8%" class="label1">NR. malha </td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">TIPO MALHA </td>
                      <td width="0%" class="label1">&nbsp;</td>
                      <td width="17%" class="label1">omitir processo </td>
                      <td width="20%" class="label1">&nbsp;</td>
                      <td width="27%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td height="44"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_subsistema" class="txt_box" id="requerido" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?
				
							$sql1 = "SELECT * FROM Projetos.subsistema, Projetos.malhas ";
							$sql1 .= "WHERE subsistema.id_subsistema = malhas.id_subsistema ";
							$sql1 .= "AND malhas.id_malha = '".$_POST["id_malha"]."' ";
							$reg1 = mysql_query($sql1,$db->conexao) or die("Não foi possível realizar a seleção.");
							$regs1 = mysql_fetch_array($reg1);
							
							
						  	$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
							$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"]. "' ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "ORDER BY nr_subsistema, subsistema";
							$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["id_subsistema"] ?>"<? if(($regs["id_subsistema"]==$_POST["id_subsistema"])||($regs["id_subsistema"]==$regs1["id_subsistema"])){ echo 'selected';}?>>
                            <?= $regs["nr_area"] . " - " .$regs["nr_subsistema"] . " - " . $regs["subsistema"] ?>
                            </option>
                          <?
								}
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_processo" id="id_processo" class="txt_box" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?
									
									
									$sql1 = "SELECT * FROM Projetos.processo, Projetos.malhas ";
									$sql1 .= "WHERE processo.id_processo = malhas.id_processo ";
									$sql1 .= "AND malhas.id_malha = '".$_POST["id_malha"]."' ";
									$reg1 = mysql_query($sql1,$db->conexao) or die("Não foi possível realizar a seleção.");
									$regs1 = mysql_fetch_array($reg1);
									
									$sql = "SELECT * FROM Projetos.processo ";
									$sql .= "ORDER BY processo, ds_processo ";
									$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
									while ($reg = mysql_fetch_array($regdescricao))
										{
											?>
                          <option value="<?= $reg["id_processo"] ?>"<? if (($_POST["id_processo"]==$reg["id_processo"])||($regs1["id_processo"]==$reg["id_processo"])){ echo 'selected';}?>>
                          <?= $reg["processo"] . " - " . $reg["ds_processo"] . " - " . $reg["norma"] ?>
                          </option>
                          <?
										}
								
								
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_malha" type="text" class="txt_box" id="nr_malha" size="20" value="<?= $_POST["nr_malha"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="tp_malha" class="txt_box" id="tp_malha" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?
							
							$sql1 = "SELECT * FROM Projetos.tipos, Projetos.malhas ";
							$sql1 .= "WHERE tipos.tipo = malhas.tp_malha ";
							$sql1 .= "AND malhas.id_malha = '".$_POST["id_malha"]."' ";
							$reg1 = mysql_query($sql1,$db->conexao) or die("Não foi possível realizar a seleção.");
							$regs1 = mysql_fetch_array($reg1);
							
						  	$sql = "SELECT * FROM Projetos.tipos ";
							$sql .= "ORDER BY ds_tipo ";
							$reg = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
							while ($regs = mysql_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["tipo"] ?>"<? if(($regs["tipo"]==$_POST["tp_malha"])||($regs["tipo"]==$regs1["tp_malha"])){ echo 'selected'; }?>>
                          <?= $regs["ds_tipo"] ?>
                          </option>
                          <?
								}
							?>
                        </select>
                      </font></td>
                      <td>&nbsp;</td>
                      <td><div align="center">
                        <input name="omit_proc" type="checkbox" id="omit_" value="1">
                      </div></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td><span class="label1">SERVI&Ccedil;O</span></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <?
							$sql1 = "SELECT * FROM Projetos.malhas ";
							$sql1 .= "WHERE malhas.id_malha = '".$_POST["id_malha"]."' ";
							$reg1 = mysql_query($sql1,$db->conexao) or die("Não foi possível realizar a seleção.");
							$regs1 = mysql_fetch_array($reg1);
                        ?>
                        <input name="ds_servico" type="text" class="txt_box" id="ds_servico" size="100" value="<? if($_POST["ds_servico"]!=''){ echo $_POST["ds_servico"];}else{ echo $regs1["ds_servico"];}  ?>">
                      </font></td>
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
					 <input name="id_malha" type="hidden" id="id_malha" value="<?= $_POST["id_malha"] ?>">
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="DUPLICAR" onClick="requer('componentes')">
                    <input name="Voltar" type="button" class="btn" id="Voltar" value="Voltar" onClick="javascript:location.href='componentes.php';">
                    <!-- <input name="Especifica��o t�cnica" type="button" class="btn" id="Malhas" value="Especifica��o t�cnica" onClick="javascript:location.href='especificacao tecnica.php';"> -->				</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="14%">TAG</td>
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = " ";
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
				  <td width="16%"><a href="#" class="cabecalho_tabela" onClick="ordenar('dispositivo','<?= $ordem ?>')">DISPOSITIVO</a></td>
				  <td width="15%"><a href="#" class="cabecalho_tabela" onClick="ordenar('funcao','<?= $ordem ?>')">FUN&Ccedil;&Atilde;O</a></td>
				  <td width="24%"><a href="#" class="cabecalho_tabela" onClick="ordenar('nr_local','<?= $ordem ?>')">LOCAL</a></td>
				  <td width="24%"><a href="#" class="cabecalho_tabela" onClick="ordenar('cd_tag_eq','<?= $ordem ?>')">TAG EQUIV.</a></td>
				  <td width="4%"  class="cabecalho_tabela">DUP</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					
					$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.dispositivos, Projetos.componentes, Projetos.processo, Projetos.funcao, Projetos.locais, Projetos.equipamentos  ";
					$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
					$sql .= "AND malhas.id_malha = '" . $_POST["id_malha"] . "' ";
					$sql .= "AND area.id_area = subsistema.id_area ";
					$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
					$sql .= "AND malhas.id_malha = componentes.id_malha ";
					$sql .= "AND malhas.id_processo = processo.id_processo ";
					$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
					$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
					$sql .= "AND componentes.id_local = locais.id_local ";
					$sql .= "AND locais.id_equipamento = equipamentos.id_equipamentos ";
					
					//$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
					$regcounter = mysql_num_rows($registro);
					
					$i=0;
					
					while ($componentes = mysql_fetch_array($registro))
					{
						if($componentes["omit_proc"])
						{
							$processo = '';
						}
						else
						{
							$processo = $componentes["processo"];
						}
						
						if($componentes["funcao"]!="")
						{
							$modificador =" - ". $componentes["funcao"];
						}
						else
						{
							if($componentes["comp_modif"])
							{
								$modificador = ".".$componentes["comp_modif"];
							}
							else
							{
								$modificador = " ";
							}
						}
						
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
						  <td width="14%"><div align="center">
                            <?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $componentes["nr_malha"] . $modificador ?>
                          </div></td>
						  <td width="16%"><div align="center"><?= $componentes["ds_dispositivo"] ?></div></td>
						  <td width="16%"><div align="center"><?= $componentes["funcao"] . " - " . $componentes["ds_funcao"] ?></div></td>
					      <td width="30%"><div align="center"><?= $componentes["nr_local"] . " - " . $componentes["cd_local"] . " - " . $componentes["ds_equipamento"] ?></div></td>
					      <td width="16%"><div align="center">
                            <?= $componentes["cd_tag_eq"] ?>
                          </div></td>
					      <td width="8%"><div align="center"> <a href="javascript:excluir('<?= $componentes["id_componente"] ?>','<?= $componentes["ds_dispositivo"] ?>')"></a> 
					        <input name="<?= $componentes["id_componente"] ?>" type="checkbox" id="dispositivo" value="1" <? if($_POST["acao"]!='salvar'){echo 'checked';}else{ if($_POST[$componentes["id_componente"]]=='1'){echo 'checked';}} ?>>
					      </div></td>
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
<?
	$db->fecha_db();
?>

