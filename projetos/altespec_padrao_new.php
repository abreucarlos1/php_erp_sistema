<?
/*

		Formul�rio de Especificacao Padrao / detalhes	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/altespec_padrao.php
		
		Verifica��o de a��es:
		
		Incluir : OK
		Alterar : OK
		Deletar : OK
		Permiss�es : NOK
		Valida��es : OK
		Coment�rios : OK		
		
		data de cria��o: 10/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
*/

include ("../includes/layout.php");
include ("../includes/conectdbproj.inc");
include ("../includes/tools.inc");		
		
if($_GET["id_especificacao_padrao"])
{
	$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.processo, funcao.funcao FROM especificacao_padrao, processo, funcao, especificacao_padrao_tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.processo=processo.processo ";
	$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
	$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ORDER BY funcao, processo, ds_especificacao_tipo ";
	$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);
	$espec = mysql_fetch_array($registro);
}
else
{
	$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.processo, funcao.funcao FROM especificacao_padrao, processo, funcao, especificacao_padrao_tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
	$sql .= "AND especificacao_padrao.processo=processo.processo ";
	$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ORDER BY funcao, processo, ds_especificacao_tipo ";
	$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);
	$espec = mysql_fetch_array($registro);
						
}
	$texto = $espec["ds_funcao"] ." ". $espec["ds_processo"] . " " . $espec["ds_especificacao_tipo"];




// Inicia as sess�es
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
    // Usu�rio n�o logado! Redireciona para a p�gina de login
    header("Location: ../index.php");
    exit;
}



//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{
	// Arquivo de Inclusão de conex�o com o banco
	include("../includes/conectdbproj.inc");
	
	//Executa o comando DELETE onde o id � enviado via javascript
	mysql_query ("DELETE FROM especificacao_padrao_detalhes WHERE id_especificacao_detalhe = '".$_GET["id_especificacao_detalhe"]."' ");
	
	//Fecha a conex�o com o banco
	mysql_close($conexao);
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Especifica��o exclu�da com sucesso.');
		//location.href = '<?= $PHP_SELF ?>';
	</script>
	<?
}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	case 'salvar':
	
		include ("../includes/conectdbproj.inc");
		
		$sql = "SELECT * FROM especificacao_padrao_detalhes ";
		$sql .= "WHERE id_topico='" . $_POST["id_topico"] . "' ";
		$sql .= "AND id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND conteudo = '" . maiusculas($_POST["conteudo"]) . "' ";
		$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel executar a sele��o1." . $sql);
		$count = mysql_num_rows($registro);
		//$id = $regs["id_espec_padrao"];
		//mysql_free_result($registro);
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
				//location.href='<?= //$PHP_SELF?>?id_espec_padrao=<?= //$_POST["id_espec_padrao"] ?>';
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
					
			//Cria senten�a de Inclusão no bd
			$sql = "INSERT INTO especificacao_padrao_detalhes ";
			$sql .= "(id_especificacao_padrao, id_topico, id_variavel, conteudo) ";
			$sql .= " VALUES ('" . $_POST["id_especificacao_padrao"] . "', '". $_POST["id_topico"]. "', ";
			$sql .= " '". $_POST["id_variavel"]. "', ";
			$sql .= " '". maiusculas($_POST["conteudo"]). "') ";
			
			$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel a inser��o dos dados2." . $sql);
			?>
			<script>
				alert('Detalhe inserido com sucesso.');
				//location.href='<?= $PHP_SELF ?>';
			</script>
			<?

		}
		mysql_close($conexao);	
	break;	
	
	
	// Caso a��o seja editar...
	case 'editar':
	
		include ("../includes/conectdbproj.inc");

		$sql = "SELECT * FROM especificacao_padrao_detalhes ";
		$sql .= "WHERE id_topico='" . $_POST["id_topico"] . "' ";
		$sql .= "AND id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND conteudo = '" . maiusculas($_POST["conteudo"]) . "' ";
		$registro = mysql_query($sql, $conexao) or die("Não foi possível fazer a seleção.");
		$count = mysql_num_rows($registro);
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
				//location.href='<?= //$PHP_SELF ?>';
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			
			//Cria senten�a de Inclusão no bd
			$sql = "UPDATE especificacao_padrao_detalhes SET ";
			$sql = $sql . "id_topico = '". $_POST["id_topico"]. "', ";
			$sql = $sql . "id_variavel = '". $_POST["id_variavel"]. "', ";
			$sql = $sql . "conteudo = '". maiusculas($_POST["conteudo"]). "' ";
			$sql = $sql . "WHERE id_especificacao_detalhe = '".$_POST["id_especificacao_detalhe"]. "' ";
			$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel a Atualização dos dados.");
			
		}
		mysql_close($conexao);	
	break;

}
?>

<html>
<head>
<title>: : . ESPECIFICA��O PADR�O - DETALHES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<!-- Javascript para declara��o de vari�veis / checagem do estilo - MAC/PC -->
<script language="JavaScript" src="../includes/checkstyle.js" type="text/javascript"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_especificacao_detalhe, id_especificacao_padrao, variavel)
{
	if(confirm('Tem certeza que deseja excluir a especifica��o '+variavel+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_especificacao_detalhe='+id_especificacao_detalhe+'&id_especificacao_padrao='+id_especificacao_padrao+'';
	}
}

function editar(id_especificacao_detalhe,id_especificacao_padrao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_detalhe='+id_especificacao_detalhe+'&id_especificacao_padrao='+id_especificacao_padrao+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheCombo(combobox_destino, combobox, index)
{

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?
include("../includes/conectdbproj.inc");

$sql = "SELECT * FROM especificacao_padrao_variavel ORDER BY ordem, ds_variavel ";

$reg = mysql_query($sql,$conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados.". $sql);

	while ($cont = mysql_fetch_array($reg))
	{

	
	?>
	
	
		if(combobox.options[index].value=='<?= $cont["id_especificacao_topico"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["ds_variavel"] ?>','<?= $cont["id_variavel"] ?>');
		}


<? } ?>
		

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
<form name="detalhes" method="post" action="<?= $PHP_SELF ?>">
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
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? //formulario("CLIENTES") ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? //menu() ?></td>
      </tr>
	  <tr>
        <td>
		
			
			<?
			
			// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				include ("../includes/conectdbproj.inc");
				
				if($_GET["id_especificacao_detalhe"])
				{
					$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_GET["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico ";
				}
				else
				{
					$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_POST["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico ";				
				}
				$registro = mysql_query($sql,$conexao) or die("N�o foi poss�vel fazer a sele��o3.". $sql);
				$det = mysql_fetch_array($registro); 	
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="6%"><span class="label1">T&Oacute;PICO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="8%"><span class="label1">VARI&Aacute;VEl</span></td>
                      <td width="0%">&nbsp;</td>
                      <!-- <td width="6%"><span class="label1">Ordem</span></td> 
                      <td width="1%">&nbsp;</td> -->
                      <td width="42%"><span class="label1">CONTE&Uacute;DO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="44%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_topico" id="requerido" class="txt_box" onChange="preencheCombo(this.form.id_variavel, this, this.selectedIndex)">
					  <option value="">SELECIONE</option>
                        <?
							
							//Popula a combo-box de Descrição.
							include("../includes/conectdbproj.inc");
							$sql = "SELECT * FROM especificacao_padrao_topico ORDER BY ds_topico";
							$regdescricao = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.4" .$sql);
							while ($reg = mysql_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<? if ($det["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?
								}
							
							?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="id_variavel" class="txt_box">
                          <option value="">SELECIONE</option>
                          <?
							
							//Popula a combo-box de Descrição.
							include("../includes/conectdbproj.inc");
							$sql = "SELECT * FROM especificacao_padrao_detalhes, especificacao_padrao_variavel ";
							$sql .= "WHERE id_especificacao_topico = especificacao_padrao_detalhes.id_topico ";
							$sql .= "ORDER BY id_especificacao_topico";
							$regdescricao = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.");
							while ($reg = mysql_fetch_array($regdescricao))
								{
									?>
                          <option value="<?= $reg["id_variavel"] ?>"<? if ($det["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
                          <?= $reg["ds_variavel"] ?>
                          </option>
                          <?
								}
							?>
                        </select>
                      </font></font></td>
                      <td>&nbsp;</td>
                      <!-- <td><input name="ordem" type="text" class="txt_box" id="ordem" size="15" maxlength="3" value='<?= //$det["ordem"] ?>'></td> 
                      <td>&nbsp;</td> -->
                      <td>
					  <input name="conteudo" type="text" class="txt_box" id="requerido" size="100" maxlength="200" value='<? echo stripslashes($det["conteudo"]); ?>'></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_especificacao_detalhe" type="hidden" value="<?= $det["id_especificacao_detalhe"] ?>">
				  <!-- <input name="id_espec_padrao" type="hidden" value="<?= //$det["id_espec_padrao"] ?>"> -->

                    <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="button" class="btn" id="Alterar" value="ALTERAR" onClick="requer('detalhes')">
                    <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:self.close()"></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
			  </table>
			  </div>
			 <?
				mysql_close($conexao);
			
			 }
			 else
			 {
				include ("../includes/conectdbproj.inc");
				$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
				$sql .= "WHERE id_especificacao_detalhe='" . $_GET["id_especificacao_detalhe"] . "' ";
				$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
				$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
				$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.6" . $sql);
				$det = mysql_fetch_array($registro); 	
			 ?>	
			 <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%"><span class="label1">T&Oacute;PICO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="8%"><span class="label1">VARI&Aacute;VEl</span></td>
                      <td width="0%">&nbsp;</td>
                      <!-- <td width="7%"><span class="label1">Ordem</span></td>
                      <td width="1%">&nbsp;</td> -->
                      <td width="42%"><span class="label1">CONTE&Uacute;DO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="40%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_topico" id="requerido" class="txt_box" onChange="preencheCombo(this.form.id_variavel, this, this.selectedIndex)">
                        <option value="">SELECIONE</option>
						<?
							
							//Popula a combo-box de Descrição.
							include("../includes/conectdbproj.inc");
							$sql = "SELECT * FROM especificacao_padrao_topico ORDER BY ds_topico";
							$regdescricao = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.");
							while ($reg = mysql_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<? if ($_POST["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?
								}
							?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="requerido" class="txt_box">
						<option value="">SELECIONE</option>
						<?
							
							//Popula a combo-box de Descrição.
							include("../includes/conectdbproj.inc");
							$sql = "SELECT * FROM especificacao_padrao_variavel ";
							$sql .= "WHERE id_especificacao_topico = '" . $_POST["id_topico"] . "' ORDER BY id_especificacao_topico";
							$regdescricao = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.");
							while ($reg = mysql_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_variavel"] ?>"<? if ($_POST["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
									<?= $reg["ds_variavel"] ?>
									</option>
									<?
								}
							?>
                        </select>
                      </font></font></td>
                      <td>&nbsp;</td>
                      <!-- <td><input name="ordem" type="text" class="txt_box" id="ordem" size="15" maxlength="3"></td>
                      <td>&nbsp;</td> -->
                      <td>
					  <input name="conteudo" type="text" class="txt_box" id="requerido" value="<?= $_POST["conteudo"] ?>" size="100" maxlength="200"></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <!-- <input name="id_espec_det" type="hidden" value="<?= //$det["id_espec_det"] ?>"> 
				  
				  <input name="cod_espec_padrao" type="hidden" value="<?= //$det["id_espec_padrao"] ?>"> -->
				  <input name="id_especificacao_padrao" type="hidden" value="<? if($_GET["id_especificacao_padrao"]){echo $_GET["id_especificacao_padrao"];}else{echo $_POST["id_especificacao_padrao"];} ?> ">
                  <input name="acao" type="hidden" id="acao" value="salvar">
                  <input name="Inserir" type="button" class="btn" id="Inserir" value="INSERIR" onClick="requer('detalhes')">
                  <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:self.close();"></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
			  </table>
			  </div>
			 <?
				mysql_close($conexao);
			
			 }
			?>
			
			
		</td>
      </tr>
		<tr>
        <td>
			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="22%" class="cabecalho_tabela">T�PICO</td>
				  <!-- <td width="7%" class="cabecalho_tabela">ORDEM</td> -->
				  <td width="36%" class="cabecalho_tabela">VARIAVEL</td>
				  <td width="31%"  class="cabecalho_tabela">CONTE�DO</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
					// Arquivo de Inclusão de conex�o com o banco
					include ("../includes/conectdbproj.inc");
										
					// Mostra os clientes
					if($_GET["id_especificacao_padrao"])
					{
						$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico, conteudo";
					}
					else
					{
						$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico";					
					}
					$registro = mysql_query($sql,$conexao) or die("N�o foi poss�vel fazer a sele��o7." . $sql);
					$i = 0;
					while ($det = mysql_fetch_array($registro))
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
						  <td width="22%" class="corpo_tabela" align="left"><?= $det["ds_topico"] ?></td>
						  <!-- <td width="7%" class="corpo_tabela" align="left"><?= //$det["ordem"] ?></td> -->
						  <td width="36%" class="corpo_tabela" align="left"><?= $det["ds_variavel"] ?></td>
						  <td width="32%" class="corpo_tabela" align="left"><? if($det["conteudo"]!=""){echo stripslashes($det["conteudo"]);}else{echo '&nbsp;';} ?></td>
						  <td width="5%" class="corpo_tabela"><div align="center">
						  <a href="#" onClick="editar('<?= $det["id_especificacao_detalhe"] ?>','<?= $_GET["id_especificacao_padrao"]?$_GET["id_especificacao_padrao"]:$_POST["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>

						  </div></td>
						  <td width="5%" class="corpo_tabela"><div align="center">
						 <a href="#" onClick="excluir('<?= $det["id_especificacao_detalhe"] ?>','<?= $_GET["id_especificacao_padrao"]?$_GET["id_especificacao_padrao"]:$_POST["id_especificacao_padrao"] ?>','<?= str_replace('"',' ',$det["conteudo"]) ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a>

						  </div>
						  	
			  			  <input name="id_especificacao_padrao" type="hidden" value="<?= $det["id_especificacao_padrao"] ?>">						  </td>
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