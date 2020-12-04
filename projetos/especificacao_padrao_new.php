<?
/*

		Formul�rio de Especificacao Padr�o	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_padrao.php
		
		data de cria��o: 06/04/2006
		
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

//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{
	
	//Executa o comando DELETE onde o id � enviado via javascript
	mysql_query ("DELETE FROM Projetos.especificacao_padrao WHERE id_especificacao_padrao = '".$_GET["id_especificacao_padrao"]."' ",$db->conexao);
	mysql_query ("DELETE FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao = '".$_GET["id_especificacao_padrao"]."' ",$db->conexao);
	
	//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/m/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICA��O PADR�O",$_GET["id_espec_padrao"],'registro exclu�do');
	

	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Especifica��o exclu�da com sucesso.');
		location.href = '<?= $PHP_SELF ?>';
	</script>
	<?
}

/*
if ($_GET["acao"]=='add_det')
{
	include ("../includes/conectdbproj.inc");

	$sql = "SELECT * FROM especificacao_padrao ORDER BY id_especificacao_padrao DESC LIMIT 1 ";
	$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel executar a sele��o3.");
	$regs = mysql_fetch_array($registro);
	$id_especificacao_padrao = $regs["id_especificacao_padrao"];
		
	$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.ds_processo, funcao.ds_funcao FROM especificacao_padrao, processo, funcao, especificacao_padrao_tipo ";
	$sql .= "WHERE especificacao_padrao.processo=processo.processo ";
	$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
	$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ";
	$sql .= "AND id_especificacao_padrao='$id_especificacao_padrao' ";
		
	//$sql = "SELECT * FROM Espec_padrao, Espec_padrao_descricao, Espec_padrao_tipo, Espec ORDER BY id_espec_padrao DESC LIMIT 1 ";
	$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel executar a sele��o3.");
	$regs = mysql_fetch_array($registro);
	$form_espec_padrao = $regs["processo"] . '  ' . $regs["funcao"] . '  ' . $regs["ds_especificacao_tipo"];
}
else
{
	$form_espec_padrao = "ESPECIFICA��O PADR�O";
}
*/
	
// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{

	// Caso a��o seja editar...
	case 'editar':
	
		//Checagem do logotipo, se vazio preenche com o logotipo atual.
		if ($_FILES["tipico"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
			$tipico_temp = $_FILES["tipico"]["tmp_name"]; 
			$tipico_name = $_FILES["tipico"]["name"];
			$tipico_size = $_FILES["tipco"]["size"];
			$tipico_type = $_FILES["tipico"]["type"];
			
			copy($tipico_temp, "tipicos/$tipico_name") or die("<script language='Javascript'>{ alert('Erro ao fazer upload da imagem.'); history.back() } </script>");
			
			$tipico = "tipicos/".$_FILES["tipico"]["name"];
		}
		else 
		{
			$tipico = $_POST["tipicoatual"];
		}
		
		if($tipico=='')
		{
			$tipico = "../images/ndisp.jpg";
		}

		// Verifica se o Cliente j� existe no banco
		//$sql = "SELECT nome_cliente, cod_cliente FROM Clientes WHERE nome_cliente = '". $_POST["nome_cliente"]. "' AND cod_cliente = '". $_POST["cod_cliente"]. "' ";
		//$registro = mysql_query($sql, $conexao) or die("Não foi possível fazer a seleção.");
		//$regs = mysql_num_rows($registro);
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
		//if ($regs>0)
			//{
				?>
				<script>
					// Mostra uma mensagem de alerta 
					//alert('Cliente j� cadastrado no banco de dados.');
					// Re-envia a pagina para resetar as variaveis
					//location.href='<?= //$PHP_SELF ?>';
				</script>		
				<?
			//}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		//else
		//{
			// Atualiza os campos com as variaveis 'postadas' pelo formulario
			$sql = "UPDATE Projetos.especificacao_padrao SET ";
			$sql = $sql . "processo = '". $_POST["processo"]. "', ";
			$sql = $sql . "funcao = '". $_POST["funcao"]. "', ";
			$sql = $sql . "id_tipo = '". $_POST["id_tipo"]. "', ";
			$sql = $sql . "tipico = '". $tipico ."' ";
			//$sql = $sql . "desempate = '". $_POST["desempate"]. "' ";
			$sql = $sql . "WHERE id_especificacao_padrao = '".$_POST["id_especificacao_padrao"]. "' ";
			$registro = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a Atualização dos dados.");

			//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/n/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICA��O PADR�O",$_POST["id_espec_padrao"],'registro alterado');			
		
		//}

			?>
			<script>
				location.href='<?= $PHP_SELF ?>';
			</script>
			<?
	break;
	
	// Caso a��o seja adicionar espec. padr�o
	case 'add_espec':
	

				//Checagem do logotipo, se vazio preenche com o logotipo atual.
		if ($_FILES["tipico"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
			$tipico_temp = $_FILES["tipico"]["tmp_name"]; 
			$tipico_name = $_FILES["tipico"]["name"];
			$tipico_size = $_FILES["tipco"]["size"];
			$tipico_type = $_FILES["tipico"]["type"];
			
			copy($tipico_temp, "tipicos/$tipico_name") or die("<script language='Javascript'>{ alert('Erro ao fazer upload da imagem.'); history.back() } </script>");
			
			$tipico = "tipicos/".$_FILES["tipico"]["name"];
		}
		else 
		{
			$tipico = $_POST["tipicoatual"];
		}
		
		if($tipico=='')
		{
			$tipico = "../images/ndisp.jpg";
		}
		
		// Verifica se a especifica��o padrao j� existe no banco
		$sql = "SELECT * FROM Projetos.especificacao_padrao WHERE processo = '". $_POST["processo"]. "' ";
		$sql .= "AND funcao = '". $_POST["funcao"]. "' ";
		$sql .= "AND id_tipo = '". $_POST["id_tipo"]. "' ";
		$sql .= "AND tipico = '$tipico' ";
		$registro = mysql_query($sql, $db->conexao) or die("Não foi possível fazer a seleção.");
		$regs = mysql_num_rows($registro);
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
		if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Especifica��o j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
				//location.href='<?= //$PHP_SELF ?>';
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.especificacao_padrao ";
			$incsql = $incsql . "(processo, funcao, tipico, id_tipo) ";
			$incsql = $incsql . "VALUES ('". $_POST["processo"]. "', ";
			$incsql = $incsql . "'". $_POST["funcao"] . "', ";
			//$incsql = $incsql . "'". $_POST["desempate"]. "', ";
			$incsql = $incsql . "'$tipico', ";
			$incsql = $incsql . "'". $_POST["id_tipo"]. "') ";

			//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/n/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICA��O PADR�O",mysql_insert_id($conexao),'registro inclu�do');			
			
			//Carrega os registros
			$registro = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados" . $incsql);
			?>
			<script>
				//location.href='<?= //$PHP_SELF ?>?acao=add_det';
				//location.href='<?= //$PHP_SELF ?>';
			</script>
			<?

			
		}
	break;
/*	
	case 'add_det':
		
		include ("../includes/conectdbproj.inc");
	
		$sql = "SELECT * FROM especificacao_padrao ORDER BY id_especificacao_padrao DESC LIMIT 1 ";
		$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel executar a sele��o55.");
		$regs = mysql_fetch_array($registro);
		$id = $regs["id_especificacao_padrao"];
		
		//COMENT�RIO - Ot�vio - Talvez se copiar tudo isso aqui l� para baixo deve resolver o problema com o $id:
		
		$sql = "SELECT * FROM especificacao_padrao_detalhes WHERE id_topico='" . $_POST["id_topico"] . "' AND ";
		$sql .= " id_variavel='" . $_POST["id_variavel"] . "' AND id_especificacao_padrao='$id' ";
		$registro = mysql_query($sql, $conexao) or die("Não foi possível fazer a seleção.");
		$count = mysql_num_rows($registro);
		//$id = $regs["id_espec_padrao"];

		//COMENT�RIO - Ot�vio - Mas ele executar� 2 vezes o mesmo SQL...
		
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
				//location.href='<?= $PHP_SELF ?>';
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO especificacao_padrao_detalhes ";
			$incsql .= "(id_especificacao_padrao, id_topico, id_variavel, conteudo) ";

			//COMENT�RIO - Ot�vio - Aqui o $id n�o est� vindo. Est� inserindo sempre como 0.
			$incsql = $incsql . "VALUES ('$id', ";
			$incsql = $incsql . "'". $_POST["id_topico"]. "', ";
			$incsql = $incsql . "'". $_POST["id_variavel"]. "', ";
			$incsql = $incsql . "'". maiusculas($_POST["conteudo"]). "') ";

			//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/n/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICA��O PADR�O DETALHES",mysql_insert_id($conexao),'registro inclu�do');

			//Carrega os registros
			$registro = mysql_query($incsql,$conexao) or die("Não foi possível a inserção dos dados");
			?>
			<script>
				//location.href='<?= //$PHP_SELF ?>';
			</script>
			<?
		}
		mysql_close($conexao);	
	break;
*/
}

?>

<html>
<head>
<title>: : . ESPECIFICA��O PADR�O . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>


function excluir(id_especificacao_padrao)
{
	if(confirm('Tem certeza que deseja excluir a especifica��o?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_especificacao_padrao='+id_especificacao_padrao+'';
	}
}

function editarespec(id_especificacao_padrao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_padrao='+id_especificacao_padrao+'';
}

function editar(id_especificacao_padrao, wid, heig)
{
	window.open("altespec_padrao.php?id_especificacao_padrao="+id_especificacao_padrao, "Editar","left=0,top=0,width="+screen.width+",height="+screen.height+",toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no");
}

function replicar(id_especificacao_padrao, wid, heig)
{
	window.open("replicarespecpadrao.php?id_especificacao_padrao="+id_especificacao_padrao, "Editar","left=0,top=0,width="+screen.width+",height="+screen.height+",toolbar=yes,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes");
}

function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
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

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheComboProcesso(combobox_destino, combobox, index)
{

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?

$sql = "SELECT * FROM Projetos.processo ";
$sql .= " ORDER BY processo ";

$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados.". $sql);

	while ($cont = mysql_fetch_array($reg))
	{

	
	?>
	
	
		if(combobox.options[index].value=='<?= $cont["funcao"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["ds_processo"] ?>','<?= $cont["processo"] ?>');
		}


<? } ?>
		

}

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheComboTipo(combobox_destino, combobox, index)
{

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?

$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
$sql .= " ORDER BY ds_especificacao_tipo ";

$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados.". $sql);

	while ($cont = mysql_fetch_array($reg))
	{

	
	?>
	
	
		if(combobox.options[index].value=='<?= $cont["processo"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["ds_especificacao_tipo"] ?>','<?= $cont["id_tipo"] ?>');
		}


<? } ?>
		

}

</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="especificacao_padrao" action="<? $PHP_SELF ?>" method="post" enctype="multipart/form-data">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"><? //titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
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
			
			 if ($_GET["acao"]=='add_det')
			 {/*
			  ?>
			    <div id="add_detalhes" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
				  <table width="100%" class="corpo_tabela">
					<tr>
					<td>&nbsp;</td>
					<td width="23">&nbsp;</td>
					</tr>
					<tr>
					  <td>&nbsp;</td>
					  <td><table width="100%" border="0">
                        <tr>
                          <td width="16%"><span class="label1">CONTE�DO</span></td>
                          <td width="1%">&nbsp;</td>
                          <td width="6%"><span class="label1">T�PICO</span></td>
                          <td width="1%">&nbsp;</td>
                          <td width="8%"><span class="label1">VARI�VEL</span></td>
                          <td width="68%">&nbsp;</td>
                        </tr>
                        <tr>
                          <td><input name="conteudo" type="text" class="txt_box" id="conteudo2" size="30" maxlength="100">
                          <input name="id" type="hidden" value="<?= $id ?>"></td>
                          <td>&nbsp;</td>
                          <td><select name="id_topico" id="select3" class="txt_box">
                            <?
							
							//Popula a combo-box de Descri��o.
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
                          <td><select name="id_variavel" id="select" class="txt_box">
                            <?
							//Popula a combo-box de Fun��o.
							include("../includes/conectdb.inc");
							$sql = "SELECT * FROM especificacao_padrao_variavel ORDER BY ds_variavel ";
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
                          </select></td>
                          <td>&nbsp;</td>
                        </tr>
                      </table></td>
				    </tr>
				  <tr>
				    <td width="1%" class="label1">&nbsp;</td>
					<td class="label1">
					<input type="hidden" name="acao" value="add_det">
                      <input name="Submit" type="submit" class="btn" value="ADICIONAR">
                    <!-- <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:location.href='<?= //$PHP_SELF ?>';"></td> -->					</tr>
					<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					</tr>
				</table>	
			  </div>
				<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
				<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
					<tr>
					  <td width="16%" class="cabecalho_tabela">T�PICO</td>
					  <td width="43%" class="cabecalho_tabela">VARIAVEL</td>
					  <td width="39%"  class="cabecalho_tabela">CONTE�DO</td>
					  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
					</tr>
				</table>
				</div>
				<div id="tbbody" style="position:relative; width:100%; height:300px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
				  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
					<?
						// Arquivo de Inclusão de conex�o com o banco
						include ("../includes/conectdbproj.inc");
						
						
						if ($_POST["id_especificacao_padrao"])
						{
							$id = $_POST["id_especificacao_padrao"];
						}
						
						
						// Mostra os registros
						$sql = "SELECT * FROM especificacao_padrao_topico, especificacao_padrao_variavel, especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='$id' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ORDER BY topico, variavel, conteudo ";
						$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);
						
						$i=0;
						
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
							  <td width="16%" align="center"><?= $det["topico"] ?></td>
							  <td width="43%" align="center"><?= $det["variavel"] ?></td>
							  <td width="41%" align="center"><?= $det["conteudo"] ?></td>
						    </tr>
							<?
						}
						// Libera a mem�ria
						mysql_close($conexao);
					?>
				  </table>
				</div>				  
				  
				  
			  <?
			  */}
			else
			{
			  if ($_GET["acao"]=='editar')
			  {
				$sql = "SELECT * FROM Projetos.especificacao_padrao WHERE id_especificacao_padrao = '" . $_GET["id_especificacao_padrao"] . "' ";
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
				$especpadrao = mysql_fetch_array($registro); 
				?>
				<div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
							  <table width="100%" class="corpo_tabela">
								<tr>
								<td>&nbsp;</td>
								<td colspan="7">&nbsp;</td>
								</tr>
								
								<tr>
								  <td width="1%" class="label1">&nbsp;</td>
								  <td width="6%" class="label1">FUN��O</td>
								  <td width="1%" class="label1">&nbsp;</td>
								  <td width="9%" class="label1">PROCESSO</td>
								  <td width="1%" class="label1">&nbsp;</td>
								  <td width="5%" class="label1">TIPO </td>
								  <td width="1%" class="label1">&nbsp;</td>
								  <td width="76%" class="label1">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<select name="funcao" id="requerido" class="txt_box" onChange="preencheComboProcesso(this.form.processo, this, this.selectedIndex)">
										<option value="">SELECIONE</option>
										<?

										$sql = "SELECT * FROM Projetos.funcao ORDER BY ds_funcao ";
										$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
										while ($reg = mysql_fetch_array($regdescricao))
											{
												?>
												<option value="<?= $reg["funcao"] ?>" <? if ($especpadrao["funcao"]==$reg["funcao"]){ echo 'selected';}?>><?= $reg["ds_funcao"] ?></option>
												<?
											}
										?>
									  </select>					</td>
									<td>&nbsp;</td>
									<td>
										<select name="processo" id="requerido" class="txt_box" onChange="preencheComboTipo(this.form.id_tipo, this, this.selectedIndex)">
										<option value="">SELECIONE</option>
												<?

												$sql = "SELECT * FROM Projetos.processo, Projetos.funcao, Projetos.especificacao_padrao ";
												$sql .= "WHERE id_especificacao_padrao = '" .$_GET["id_especificacao_padrao"]. "' ";
												$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
												$sql .= "AND processo.funcao=funcao.funcao ";
												$sql .= "ORDER BY ds_processo ";
												$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção." . $sql);
												while ($reg = mysql_fetch_array($regdescricao))
													{
														?>
														<option value="<?= $reg["processo"] ?>"<? if($especpadrao["processo"]==$reg["processo"]){ echo 'selected';}?>><?= $reg["ds_processo"] ?></option>
														<?
													}
											?>
									  </select>					
								  </td>					
									<td>&nbsp;</td>
									<td>
										<select name="id_tipo" id="requerido" class="txt_box">
										<option value="">SELECIONE</option>
										<?
										

										$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.especificacao_padrao_tipo ";
										$sql .= "WHERE id_especificacao_padrao = '" . $_GET["id_especificacao_padrao"] . "' ";
										$sql .= "AND especificacao_padrao_tipo.processo = especificacao_padrao.processo ";
										$sql .= "ORDER BY ds_especificacao_tipo ";
										$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção." . $sql);
										while ($reg = mysql_fetch_array($regdescricao))
											{
												?>
												<option value="<?= $reg["id_tipo"] ?>"<? if ($especpadrao["id_tipo"]==$reg["id_tipo"]){ echo 'selected';} ?>><?= $reg["ds_especificacao_tipo"] ?></option>
												<?
											}
										
										?>
										</select>
										</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
								  <td>&nbsp;</td>
								  <td colspan="7"><table width="100%" border="0">
                                    <tr>
                                      <td><span class="label1">t&Iacute;pico:
										<?= $especpadrao["tipico"] ?>
                                      </span></td>
                                    </tr>
                                    <tr>
                                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                                        <input name="tipico"  type="file" id="logotipo3" size="70" class="txt_box" value="<?= $especpadrao["tipico"] ?>">
                                      </font></td>
                                    </tr>
                                  </table></td>
							    </tr>
							<tr>
							  <td>&nbsp;</td>
							  <td colspan="7">
								<input type="hidden" name="acao" value="editar">
								<input type="hidden" name="id_especificacao_padrao" value="<?= $_GET["id_especificacao_padrao"] ?>">
								<input type="hidden" name="tipicoatual" value="<?= $especpadrao["tipico"] ?>">
								<input name="Submit" type="button" class="btn" value="ALTERAR" onClick="requer('especificacao_padrao')">
								<span class="label1">
								<input name="button2" type="button" class="btn" value="VOLTAR" onClick="javascript:location.href='<?= $PHP_SELF ?>';">
								</span> </td>
							  </tr>
						<tr><td>&nbsp;</td>
						<td colspan="7">&nbsp;</td>
						</tr>									
						</table>
			  </div>
				<?			  
			  }
			  else
			  {
			  ?>
				<div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" class="corpo_tabela">
				<tr>
				<td>&nbsp;</td>
				<td colspan="7">&nbsp;</td>
				</tr>
				
				<tr>
				  <td width="1%" class="label1">&nbsp;</td>
				  <td width="6%" class="label1">FUN��O</td>
				  <td width="1%" class="label1">&nbsp;</td>
				  <td width="9%" class="label1">PROCESSO</td>
				  <td width="1%" class="label1">&nbsp;</td>
				  <td width="5%" class="label1">TIPO </td>
				  <td width="1%" class="label1">&nbsp;</td>
				  <td width="76%" class="label1">&nbsp;</td>
				</tr>
				<tr>
  					<td>&nbsp;</td>
  					<td>
						<select name="funcao" id="requerido" class="txt_box" onChange="preencheComboProcesso(this.form.processo, this, this.selectedIndex)">
						<option value="">SELECIONE</option>
						<?

						$sql = "SELECT * FROM Projetos.funcao ORDER BY ds_funcao ";
						$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
						while ($reg = mysql_fetch_array($regdescricao))
							{

								?>
								<option value="<?= $reg["funcao"] ?>" <? if ($_POST["funcao"]==$reg["funcao"]){ echo 'selected';}?>><?= $reg["ds_funcao"] ?></option>
								<?
							}
						?>
					  </select>					</td>
					<td>&nbsp;</td>
  					<td>
						<select name="processo" id="requerido" class="txt_box" onChange="preencheComboTipo(this.form.id_tipo, this, this.selectedIndex)">
						<option value="">SELECIONE</option>
								<?

									$sql = "SELECT * FROM Projetos.processo ";
									$sql .= "WHERE funcao='" . $_POST["funcao"] . "' ORDER BY ds_processo ";
									$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
									while ($reg = mysql_fetch_array($regdescricao))
										{
											?>
											<option value="<?= $reg["processo"] ?>"<? if ($_POST["processo"]==$reg["processo"]){ echo 'selected';}?>><?= $reg["ds_processo"] ?></option>
											<?
										}
								
								
							?>
					  </select>
					  </td>					
					<td>&nbsp;</td>
					<td>
						<select name="id_tipo" id="requerido" class="txt_box" >
						<option value="">SELECIONE</option>
					  	<?
						$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
						$sql .= "WHERE processo = '" . $_POST["processo"] . "' ORDER BY ds_especificacao_tipo ";
						$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
						while ($reg = mysql_fetch_array($regdescricao))
							{
								?>
								<option value="<?= $reg["id_tipo"] ?>"<? if ($_POST["id_tipo"]==$reg["id_tipo"]){ echo 'selected';}?>><?= $reg["ds_especificacao_tipo"] ?></option>
								<?
							}
						
						?>
                		</select>					</td>
			        <td>&nbsp;</td>
				    <td>&nbsp;</td>
				</tr>
				<tr>
				  <td>&nbsp;</td>
				  <td colspan="7"><table width="100%" border="0">
                    <tr>
                      <td><span class="label1">t&Iacute;pico:
                        <?= $especpadrao["tipico"] ?>
                      </span></td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="tipico"  type="file" id="tipico" size="70" class="txt_box" value="<?= $especpadrao["tipico"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
				  </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td colspan="7">
			  	<input type="hidden" name="acao" value="add_espec">
			  	<input name="Submit" type="button" class="btn" value="ADICIONAR" onClick="requer('especificacao_padrao')">
			  	<span class="label1">
			  	<input name="button2" type="button" class="btn" value="VOLTAR" onClick="javascript:location.href='<?= $PHP_SELF ?>';">
			  	</span> </td>
			  </tr>
		<tr>
		<td>&nbsp;</td>
		<td colspan="7">&nbsp;</td>
		</tr>									
		</table>
	  </div>
	  
			  <?
			  }
			}
?>
				<div id="tbheader1" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
				<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
					<tr>
					  <td width="20%" class="cabecalho_tabela">FUN��O</td>
					  <td width="30%" class="cabecalho_tabela">PROCESSO </td>
					  <td width="32%"  class="cabecalho_tabela">TIPO </td>
					  <td width="4%"  class="cabecalho_tabela">A</td>
					  <td width="4%"  class="cabecalho_tabela">R</td>
					  <td width="4%"  class="cabecalho_tabela">E</td>
					  <td width="4%"  class="cabecalho_tabela">D</td>
					  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
					</tr>
				</table>
				</div>
				<div id="tbbody" style="position:relative; width:100%; height:250px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
				  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
					<?

						$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.ds_processo, funcao.ds_funcao FROM Projetos.especificacao_padrao, Projetos.processo, Projetos.funcao, Projetos.especificacao_padrao_tipo ";
						$sql .= "WHERE especificacao_padrao.funcao=funcao.funcao ";
						$sql .= "AND especificacao_padrao.processo=processo.processo ";
						$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ";
						$sql .= "GROUP BY ds_funcao, ds_processo, ds_especificacao_tipo ";
						$sql .= "ORDER BY ds_funcao, ds_processo, ds_especificacao_tipo ";
						$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
						$i = 0;
						
						while ($espec = mysql_fetch_array($registro))
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
							  <td width="20%" class="corpo_tabela"><div align="left"><?= $espec["ds_funcao"] ?></div></td>
							  <td width="30%" class="corpo_tabela"><div align="left"><?= $espec["ds_processo"] ?></div></td>
							  <td width="33%" class="corpo_tabela" align="left"><?= $espec["ds_especificacao_tipo"] ?></td>
							  
							  <td width="4%" class="corpo_tabela" align="left"><div align="center">

                                <a href="#" onClick="editarespec('<?= $espec["id_especificacao_padrao"] ?>')"><img src="/voith/images/buttons/bt_visualizar.gif" width="22" height="22" border="0"></a>

                              </div></td>
							  <td width="4%" class="corpo_tabela" align="center"><div align="center">

                                <a href="#" onClick="replicar('<?= $espec["id_especificacao_padrao"] ?>',620,250)"><img src="../images/buttons_action/replicar.gif" width="15" height="13" border="0"></a>

                              </div></td>
							  <td width="5%" class="corpo_tabela"><div align="center">
							<a href="#" onClick="editar('<?= $espec["id_especificacao_padrao"] ?>',620,250)"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>

							  
							  </div></td>
							  <td width="4%" class="corpo_tabela"><div align="center">

									 <a href="#" onClick="excluir('<?= $espec["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a>

							  </div></td>
							</tr>
							<?
						}
			

					?>
				  </table>
				</div>	
   </table>
</table>	  

	</form>
</center>
</body>
</html>
<?
	$db->fecha_db();
?>

