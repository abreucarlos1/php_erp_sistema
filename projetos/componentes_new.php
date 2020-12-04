<?
/*

		Formul�rio de Componentes
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/componentes.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> ALTERA��O PARA COMPONENTE
		Versão 2 --> ALTERA��O PARA DISPOSITIVO
		Versão 3 --> Inclusão DA ESPECIFICA��O T�CNICA
		
		Ultima Atualização: 02/05/2006
		
		
		
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

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "SELECT * FROM Projetos.componentes ";
	$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] . "' ";
	$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
	$sql .= "AND id_malha = '" . $_POST["id_malha"] . "' ";
	$sql .= "AND id_local = '" . $_POST["id_local"] . "' ";
	$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
	$sql .= "AND comp_revisao = '" . $_POST["comp_revisao"] . "' ";
	$sql .= "AND comp_modif = '" . maiusculas($_POST["comp_modif"]) . "' ";
	$sql .= "AND omit_proc = '" . $_POST["omit_proc"] . "' ";
	$sql .= "AND new_comp = '" . $_POST["new_comp"] . "' ";
	$registros = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
	$regs = mysql_num_rows($registros);
	
	// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
	if ($regs>0)
	{
		?>
		<script>
			// Mostra uma mensagem de alerta 
			alert('Componente j� cadastrado na malha.');
		</script>		
		<?
	}
	else
	{
		$sql = "UPDATE Projetos.componentes SET ";
		$sql .= "id_dispositivo = '" . $_POST["id_dispositivo"] . "', ";
		$sql .= "id_funcao = '" . $_POST["id_funcao"] . "', ";
		$sql .= "id_malha = '" . $_POST["id_malha"] . "', ";
		$sql .= "id_local = '" . $_POST["id_local"] . "', ";
		$sql .= "id_tipo = '" . $_POST["id_tipo"] . "', ";
		$sql .= "comp_revisao = '" . $_POST["comp_revisao"] . "', ";
		$sql .= "comp_modif = '" . $_POST["comp_modif"] . "', ";
		$sql .= "cd_tag_eq = '" . maiusculas($_POST["cd_tag_eq"]) . "', ";
		$sql .= "new_comp = '" . $_POST["new_comp"] . "', ";
		$sql .= "omit_proc = '" . $_POST["omit_proc"] . "' ";
		$sql .= "WHERE id_componente = '" . $_POST["id_componente"] ."' ";
		$registros = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a Atualização dos dados.");
		
		$sql = "SELECT * FROM Projetos.especificacao_tecnica ";
		$sql .= "WHERE especificacao_tecnica.id_componente = '" . $_POST["id_componente"] ."' ";
		$regs = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a Atualização dos dados.");
		$espec = mysql_fetch_array($regs);

		mysql_query("DELETE FROM Projetos.especificacao_tecnica WHERE id_especificacao_tecnica = '".$espec["id_especificacao_tecnica"]."' ",$db->conexao) or die ("N�o foi poss�vel excluir o componente. Motivo: " . mysql_error($conexao));
		mysql_query("DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$espec["id_especificacao_tecnica"]."' ",$db->conexao) or die ("N�o foi poss�vel excluir o componente. Motivo: " . mysql_error($conexao));
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao ";
		$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] . "' ";
		$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
		$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
		$registros = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
		$count = mysql_num_rows($registros);
		if($count>0)
		{
			$regs = mysql_fetch_array($registros);
			
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.especificacao_tecnica ";
			$incsql .= "(id_especificacao_padrao, id_componente) ";
			$incsql .= "VALUES (";
			$incsql .= "'" . $regs["id_especificacao_padrao"] . "', ";
			$incsql .= "'" . $_POST["id_componente"] . "') ";
			$registros = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);
			
			$esp = mysql_insert_id($db->conexao);
			
			$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE id_especificacao_padrao = '" . $regs["id_especificacao_padrao"] . "' ";
			$regis = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
			
			while($reg = mysql_fetch_array($regis))
			{
				//Cria senten�a de Inclusão no bd
				$incsql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
				$incsql .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
				$incsql .= "VALUES (";
				$incsql .= "'" . $esp . "', ";
				$incsql .= "'" . $reg["id_especificacao_detalhe"] . "', ";
				$incsql .= "'" . $reg["conteudo"] . "') ";
				$regist = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);			
			
			}
			
		}
		?>
		<script>
			alert('Componente atualizado com sucesso.');
		</script>
		<?
	}
	
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	$sql = "SELECT * FROM Projetos.componentes ";
	$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] . "' ";
	$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
	$sql .= "AND id_malha = '" . $_POST["id_malha"] . "' ";
	$sql .= "AND id_local = '" . $_POST["id_local"] . "' ";
	$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
	$sql .= "AND comp_revisao = '" . $_POST["comp_revisao"] . "' ";
	$sql .= "AND comp_modif = '" . $_POST["comp_modif"] . "' ";
	$sql .= "AND omit_proc = '" . $_POST["omit_proc"] . "' ";
	$sql .= "AND new_comp = '" . $_POST["new_comp"] . "' ";
	$registros = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
	$regs = mysql_num_rows($registros);
	
	// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
	if ($regs>0)
	{
		?>
		<script>
			// Mostra uma mensagem de alerta 
			alert('Componente j� cadastrado na malha.');
		</script>		
		<?
	}
	else
	{
	
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.componentes ";
		$incsql .= "(id_funcao, id_malha, id_local, id_tipo, id_dispositivo, comp_modif, comp_revisao, omit_proc, new_comp, cd_tag_eq) VALUES (";
		$incsql .= "'" . $_POST["id_funcao"] . "', ";
		$incsql .= "'" . $_POST["id_malha"] . "', ";
		$incsql .= "'" . $_POST["id_local"] . "', ";
		$incsql .= "'" . $_POST["id_tipo"] . "', ";
		$incsql .= "'" . $_POST["id_dispositivo"] . "', ";
		$incsql .= "'" . maiusculas($_POST["comp_modif"]) . "', ";
		$incsql .= "'" . $_POST["comp_revisao"] . "', ";
		$incsql .= "'" . $_POST["omit_proc"] . "', ";
		$incsql .= "'" . $_POST["new_comp"] . "', ";
		$incsql .= "'" . maiusculas($_POST["cd_tag_eq"]) . "') ";
	
		$registros = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);
		
		$comp = mysql_insert_id($db->conexao);
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao ";
		$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] . "' ";
		$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
		$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
		$registros = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
		$count = mysql_num_rows($registros);
		if($count>0)
		{
			$regs = mysql_fetch_array($registros);
			
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.especificacao_tecnica ";
			$incsql .= "(id_especificacao_padrao, id_componente) ";
			$incsql .= "VALUES (";
			$incsql .= "'" . $regs["id_especificacao_padrao"] . "', ";
			$incsql .= "'" . $comp . "') ";
			$registros = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);
			
			$esp = mysql_insert_id($db->conexao);
			
			$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE id_especificacao_padrao = '" . $regs["id_especificacao_padrao"] . "' ";
			$regis = mysql_query($sql, $db->conexao) or die("N�o foi poss�vel a sele��o dos dados.");
			
			while($reg = mysql_fetch_array($regis))
			{
				//Cria senten�a de Inclusão no bd
				$incsql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
				$incsql .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
				$incsql .= "VALUES (";
				$incsql .= "'" . $esp . "', ";
				$incsql .= "'" . $reg["id_especificacao_detalhe"] . "', ";
				$incsql .= "'" . $reg["conteudo"] . "') ";
				$regist = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);			
			
			}
			
			
		
		}		
		
		?>
		<script>
			alert('Componente inserido com sucesso.');
		</script>
		<?
	}

}


 
if ($_GET["acao"] == "deletar")
{
	mysql_query("DELETE FROM Projetos.componentes WHERE id_componente = '".$_GET["id_componente"]."' ",$db->conexao) or die ("N�o foi poss�vel excluir o componente. Motivo: " . mysql_error($conexao));
	?>
	<script>
		alert('Componente exclu�do com sucesso.');
	</script>
	<?
}

?>

<html>
<head>
<title>: : . COMPONENTES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>


function excluir(id_componente, componente)
{
	if(confirm('Tem certeza que deseja excluir o componente '+componente+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_componente='+id_componente+'';
	}
}

function editar(id_componente, id_disciplina)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_componente='+id_componente+'&id_disciplina='+id_disciplina+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

function replicar()
{
	if(confirm('Tem certeza que deseja duplicar a malha'))
	{
		if(document.forms.componentes.id_malha.value == '')
		{
			alert('Para duplicar uma malha, deve-se escolher uma.');
		}
		else
		{
			document.forms.componentes.acao.value = 'replicar';
			document.forms.componentes.action = 'replicarmalhas.php';
			document.forms.componentes.submit();
		}
	}
}

//Fun��o para redimensionar a janela.
function maximiza() 
{

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


//Fun��o para preenchimento dos comboboxes din�micos.
function preenchecomboeei(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'EL�TRICA' ";
$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados." . $sql);


	while ($cont = mysql_fetch_array($reg))
	{
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"] ?>','<?= $cont["id_local"] ?>');
		<? 
	} 
	?>
}


//Fun��o para preenchimento dos comboboxes din�micos.
function preenchecombomecanica(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'MEC�NICA' ";
$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados." . $sql);


	while ($cont = mysql_fetch_array($reg))
	{
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"]. "  ". $cont["ds_descricao"] ?>','<?= $cont["id_local"] ?>');
		<? 
	} 
	?>
}

//Fun��o para preenchimento dos comboboxes din�micos.
function preenchecombolinha(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'TUBULA��O' ";
$sql .= "ORDER BY nr_area, cd_fluido, nr_sequencia, cd_material, nr_diametro ";
$reg = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel estabelecer a conex�o com o banco de dados." . $sql);


	while ($cont = mysql_fetch_array($reg))
	{
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["nr_area"]. " - ".  $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"] ?>','<?= $cont["id_local"] ?>');
		<? 
	} 
	?>
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">

<center>
<form name="componentes" method="post" action="<?= $PHP_SELF ?>" enctype="multipart/form-data">
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
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;<? //formulario() ?></td>
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
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.componentes WHERE id_componente= '" . $_GET["id_componente"] . "' ";
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.".$sql);
				$componentes = mysql_fetch_array($registro); 	
			 
			 
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
                      <td width="10%" class="label1">MALHA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%" class="label1">dispositivo</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <!-- <td width="12%" class="label1">TIPO</td> 
                      <td width="1%" class="label1">&nbsp;</td> -->
                      <td width="9%" class="label1">FUN&Ccedil;&Atilde;O</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="10%" class="label1">tipo</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="57%" class="label1">MOD.</td>
                      </tr>
                    <tr>
                      <td><select name="id_malha" class="txt_box" id="requerido" >
					  <option value="">SELECIONE</option>
					  <?
					  
					  	//onChange="preencheComboFuncao(this.form.funcao, this, this.selectedIndex)"
						$sql_malha = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo ";
						$sql_malha .= "WHERE area.id_area = subsistema.id_area ";
						$sql_malha .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
						$sql_malha .= "AND area.os = '" . $_SESSION["os"] . "' ";
						$sql_malha .= "AND malhas.id_processo = processo.id_processo ";
						$sql_malha .= "ORDER BY processo, nr_malha ";
						$reg_malha = mysql_query($sql_malha,$db->conexao);
						
						while($cont_malha = mysql_fetch_array($reg_malha))
						{
							?>
							<option value="<?= $cont_malha["id_malha"] ?>" <? if($cont_malha["id_malha"]==$componentes["id_malha"]) { echo "selected"; } ?>><?= $cont_malha["processo"] . " - " . $cont_malha["nr_malha"] . " - " . $cont_malha["ds_servico"] ?></option>
							<?
							
						}
						
					?>
						
					  
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_dispositivo" class="txt_box" id="requerido">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_componente = "SELECT * FROM Projetos.dispositivos ORDER BY sequencia, dispositivo ";
						$reg_componente = mysql_query($sql_componente,$db->conexao);
						
						while($cont_componente = mysql_fetch_array($reg_componente))
						{
						?>
                        <option value="<?= $cont_componente["id_dispositivo"] ?>" <? if($cont_componente["id_dispositivo"]==$componentes["id_dispositivo"]) { echo "selected"; } ?>>
                        <? 
							if($cont_componente["dispositivo"]!="")
							{
								echo $cont_componente["dispositivo"] . " - " . $cont_componente["ds_dispositivo"];
							}
							else
							{
								echo $cont_componente["ds_dispositivo"];
							} 
						?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_funcao" class="txt_box" id="requerido">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_funcao = "SELECT * FROM Projetos.funcao ORDER BY funcao, ds_funcao ";
						$reg_funcao = mysql_query($sql_funcao,$db->conexao) or die($sql_funcao);
						
						while($cont_funcao = mysql_fetch_array($reg_funcao))
						{
						?>
                        <option value="<?= $cont_funcao["id_funcao"] ?>" <? if($cont_funcao["id_funcao"]==$componentes["id_funcao"]) { echo "selected"; } ?>>
                        <? 
							if($cont_funcao["funcao"]!="")
							{
							 	echo $cont_funcao["funcao"] . " - " . $cont_funcao["ds_funcao"];
							}
							else
							{
								echo $cont_funcao["ds_funcao"];
							} 
						?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_tipo" class="txt_box" id="">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_tipo = "SELECT * FROM Projetos.tipo ";
						$sql_tipo .= "ORDER BY ds_tipo ";
						$reg_tipo = mysql_query($sql_tipo,$db->conexao) or die($sql_tipo);
						
						while($tipo = mysql_fetch_array($reg_tipo))
						{
						?>
                        <option value="<?= $tipo["id_tipo"] ?>" <? if($tipo["id_tipo"]==$componentes["id_tipo"]) { echo "selected"; } ?>>
                        <?= $tipo["ds_tipo"];

						?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><input name="comp_modif" type="text" class="txt_box" id="comp_modif" value="<?= $componentes["comp_modif"] ?>" size="10"></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="18%">TIPO LOCAL</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%"><span class="label1">LOCAL</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="15%"><span class="label1">TAG EQUIVALENTE</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="7%">revis&Atilde;O</td>
                      <td width="1%">&nbsp;</td>
                      <td width="9%">OMITIR PROCESSO </td>
                      <td width="1%">&nbsp;</td>
                      <td width="11%">novo componente </td>
                      <td width="0%">&nbsp;</td>
                      <td width="25%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><table width="100%" border="0">
                        <tr class="label1">
                          <td>EEI</td>
                          <td>&nbsp;</td>
                          <td>MEC&Acirc;NICA</td>
                          <td>&nbsp;</td>
                          <td>LINHA</td>
                        </tr>
                        <tr>
                          <td><div align="center">
						  <?
						
							$sql1 = "SELECT * FROM ".DATABASE.".setores ";
							$sql1 .= "WHERE id_setor = '" . $_GET["id_disciplina"] . "' ";
							$regis = mysql_query($sql1,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
							$disciplina = mysql_fetch_array($regis);
							
						  ?>
                              <input name="tipo" type="radio" value="EL�TRICA" <? if($disciplina["setor"]=='EL�TRICA'){ echo 'checked';} ?> onClick="preenchecomboeei(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                              <input name="tipo" type="radio" value="MEC�NICA" <? if($disciplina["setor"]=='MEC�NICA'){ echo 'checked';} ?> onClick="preenchecombomecanica(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                              <input name="tipo" type="radio" value="TUBULA��O" <? if($disciplina["setor"]=='TUBULA��O'){ echo 'checked';} ?> onClick="preenchecombolinha(this.form.id_local, this)">
                          </div></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td><select name="id_local" class="txt_box" id="requerido">
                        <option value="">SELECIONE</option>
                        <?
						
						//
					  	if($disciplina["setor"]=='EL�TRICA')
						{
							$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
							$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
							$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
							$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
							$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
							$sql .= "AND ".DATABASE.".setores.setor = 'EL�TRICA' ";
							$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
							
							$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
							
							while($cont = mysql_fetch_array($regis))
							{
							?>
							<option value="<?= $cont["id_local"] ?>" <? if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"] ?>
							</option>
							<?
								
							}
						}
						else
						{
							if($disciplina["setor"]=='MEC�NICA')
							{
								$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
								$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
								$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
								$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
								$sql .= "AND ".DATABASE.".setores.setor = 'MEC�NICA' ";
								$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";							
								
								$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
								
								while($cont = mysql_fetch_array($regis))
								{
								?>
								<option value="<?= $cont["id_local"] ?>" <? if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
								<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"]. "  ". $cont["ds_descricao"] ?>
								</option>
								<?
									
								}
							
							}
							else
							{
								$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
								$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
								$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
								$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
								$sql .= "AND Projetos.area.os = '" .$_SESSION["os"] . "' ";
								$sql .= "AND ".DATABASE.".setores.setor = 'TUBULA��O' ";
								$sql .= "ORDER BY nr_area, cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

								$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
								
								while($cont = mysql_fetch_array($regis))
								{
								?>
								<option value="<?= $cont["id_local"] ?>" <? if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
								<?= $cont["nr_area"]. " - ".  $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"] ?>
								</option>
								<?
									
								}

							
							
							}
						}
						

						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><input name="cd_tag_eq" type="text" class="txt_box" id="cd_tag_eq" value="<?= $componentes["cd_tag_eq"] ?>" size="35"></td>
                      <td>&nbsp;</td>
                      <td><input name="comp_revisao" type="text" class="txt_boxcap" id="comp_revisao" value="<?= versao_documento($componentes["comp_revisao"]) ?>" size="15"></td>
                      <td>&nbsp;</td>
                      <td><div align="center">
                        <input name="omit_proc" type="checkbox" id="omit_" value="1" <? if($componentes["omit_proc"]){ echo 'checked';} ?>>
                      </div></td>
                      <td>&nbsp;</td>
                      <td><div align="center">
                        <input name="new_comp" type="checkbox" id="omit_" value="1" <? if($componentes["new_comp"]){ echo 'checked';} ?>>
                      </div></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_componente" type="hidden" id="id_componente" value="<?= $componentes["id_componente"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="button" class="btn" id="Alterar" value="Alterar" onClick="requer('componentes')">
                    <input name="Voltar" type="button" class="btn" id="Voltar" value="Voltar" onClick="location.href='<?= $PHP_SELF ?>';">
                    <!-- <input name="Especifica��o t�cnica" type="button" class="btn" id="Especifica��o t�cnica" value="Especifica��o t�cnica" onClick="javascript:location.href='especificacao_tecnica.php';"> -->				</td>
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
                      <td width="9%" class="label1">MALHA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%" class="label1">dispositivo</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <!-- <td width="12%" class="label1">TIPO</td> 
                      <td width="1%" class="label1">&nbsp;</td> -->
                      <td width="9%" class="label1">FUN&Ccedil;&Atilde;O</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">tipo</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="48%" class="label1">MOD.</td>
                      <td width="2%" class="label1">&nbsp;</td>
                      <td width="9%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_malha" class="txt_box" id="requerido" >
                        <option value="">SELECIONE</option>
                        <?
					  	//onChange="preencheComboFuncao(this.form.funcao, this, this.selectedIndex)"
						$sql_malha = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo ";
						$sql_malha .= "WHERE area.id_area = subsistema.id_area ";
						$sql_malha .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
						$sql_malha .= "AND area.os = '" . $_SESSION["os"] . "' ";
						$sql_malha .= "AND malhas.id_processo = processo.id_processo ";
						$sql_malha .= "ORDER BY processo, nr_malha ";
						$reg_malha = mysql_query($sql_malha,$db->conexao) or die($sql_malha);
						
						while($cont_malha = mysql_fetch_array($reg_malha))
						{
							?>
                        <option value="<?= $cont_malha["id_malha"] ?>" <? if($cont_malha["id_malha"]==$_POST["id_malha"]) { echo "selected"; } ?>>
                          <?= $cont_malha["processo"] . " - " . $cont_malha["nr_malha"] . " - " . $cont_malha["ds_servico"] ?>
                          </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_dispositivo" class="txt_box" id="requerido">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_componente = "SELECT * FROM Projetos.dispositivos ORDER BY sequencia, dispositivo ";
						$reg_componente = mysql_query($sql_componente,$db->conexao);
						
						while($cont_componente = mysql_fetch_array($reg_componente))
						{
						?>
                        <option value="<?= $cont_componente["id_dispositivo"] ?>" <? if($cont_componente["id_dispositivo"]==$_POST["id_dispositivo"]) { echo "selected"; } ?>>
                        <?
							if($cont_componente["dispositivo"]!="")
							{
								echo $cont_componente["dispositivo"] . " - " . $cont_componente["ds_dispositivo"];
							}
							else
							{
								echo $cont_componente["ds_dispositivo"];
							} 
						?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_funcao" class="txt_box" id="requerido">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_funcao = "SELECT * FROM Projetos.funcao ORDER BY funcao, ds_funcao ";
						$reg_funcao = mysql_query($sql_funcao,$db->conexao) or die($sql_funcao);
						
						while($cont_funcao = mysql_fetch_array($reg_funcao))
						{
						?>
                        <option value="<?= $cont_funcao["id_funcao"] ?>" <? if($cont_funcao["id_funcao"]==$_POST["id_funcao"]) { echo "selected"; } ?>>
                        <?
							if($cont_funcao["funcao"]!="")
							{
							 	echo $cont_funcao["funcao"] . " - " . $cont_funcao["ds_funcao"];
							}
							else
							{
								echo $cont_funcao["ds_funcao"];
							} 
						?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><select name="id_tipo" class="txt_box" id="">
                        <option value="">SELECIONE</option>
                        <?
					  
						$sql_tipo = "SELECT * FROM Projetos.tipo ";
						$sql_tipo .= "ORDER BY ds_tipo ";
						$reg_tipo = mysql_query($sql_tipo,$db->conexao);
						
						while($tipo = mysql_fetch_array($reg_tipo))
						{
						?>
                        <option value="<?= $tipo["id_tipo"] ?>" <? if($tipo["id_tipo"]==$_POST["id_tipo"]) { echo "selected"; } ?>>
                        <?= $tipo["ds_tipo"] ?>
                        </option>
                        <?
							
						}
						
					?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><input name="comp_modif" type="text" class="txt_box" id="comp_modif" value="<?= $_POST["comp_modif"] ?>" size="10"></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="18%">TIPO LOCAL</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%"><span class="label1">LOCAL</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="15%"><span class="label1">TAG EQUIVALENTE</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="7%">revis&Atilde;O</td>
                      <td width="1%">&nbsp;</td>
                      <td width="9%">OMITIR PROCESSO </td>
                      <td width="1%">&nbsp;</td>
                      <td width="11%">NOVO COMPONENTE </td>
                      <td width="0%">&nbsp;</td>
                      <td width="25%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><table width="100%" border="0">
                        <tr class="label1">
                          <td>EEI</td>
                          <td>&nbsp;</td>
                          <td>MEC&Acirc;NICA</td>
                          <td>&nbsp;</td>
                          <td>LINHA</td>
                        </tr>
                        <tr>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="EL�TRICA" onClick="preenchecomboeei(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="MEC�NICA" onClick="preenchecombomecanica(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="TUBULA��O" onClick="preenchecombolinha(this.form.id_local, this)">
                          </div></td>
                        </tr>
                      </table></td>
                      <td>&nbsp;</td>
                      <td><select name="id_local" class="txt_box" id="requerido">
                          <option value="">SELECIONE</option>
                          <?
					  	/*
						$sql_local = "SELECT * FROM locais_outros, equipamentos_outros ";
						$sql_local .= "WHERE locais_outros.id_equipamento_o = equipamentos.id_equipamento_o ORDER BY nr_local_o ";
						$reg_local = mysql_query($sql_local,$conexao);
						
						while($cont_local = mysql_fetch_array($reg_local))
						{
						?>
                          <option value="<?= $cont_local["id_localoutro"] ?>" <? if($cont_local["id_localoutro"]==$_POST["id_localoutro"]) { echo "selected"; } ?>>
                          <?= $cont_local["nr_local_o"] . " - " . $cont_local["cd_local_o"] . " - " . $cont_local["ds_equipamento_o"] ?>
                          </option>
                          <?
							
						}
						*/
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><input name="cd_tag_eq" type="text" class="txt_box" id="cd_tag_eq" value="<?= $_POST["cd_tag_eq"] ?>" size="35"></td>
                      <td>&nbsp;</td>
                      <td><input name="comp_revisao" type="text" class="txt_boxcap" id="comp_revisao" value="0" size="15"></td>
                      <td>&nbsp;</td>
                      <td><div align="center">
                        <input name="omit_proc" type="checkbox" id="omit_" value="1" <? if($_POST["omit_proc"]){echo 'checked';} ?>>
                      </div></td>
                      <td>&nbsp;</td>
                      <td><div align="center">
                        <input name="new_comp" type="checkbox" id="omit_" value="1" <? if($_POST["omit_proc"]){echo 'checked';} ?>>
                      </div></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="Inserir" onClick="requer('componentes')">
                    <input name="Voltar" type="button" class="btn" id="Voltar" value="Voltar" onClick="javascript:location.href='menu_eei.php';">
                    <!-- <input name="Especifica��o t�cnica" type="button" class="btn" id="Malhas" value="Especifica��o t�cnica" onClick="javascript:location.href='especificacao tecnica.php';"> -->
				    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="DUPLICAR MALHA" onClick="replicar()">
				    <input name="Inserir3" type="button" class="btn" id="Inserir2" value="ESPECIFICA��O T�CNICA" onClick="javascript:location.href='especificacao_tecnica.php';"></td>
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
						$campo = "processo, nr_malha, ds_dispositivo, ds_funcao, comp_modif ";
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
				  <td width="19%"><a href="#" class="cabecalho_tabela" onClick="ordenar(' processo, nr_malha, ds_dispositivo, ds_funcao, comp_modif ','<?= $ordem ?>')">TAG</a></td>
				  <td width="19%"><a href="#" class="cabecalho_tabela" onClick="ordenar('dispositivo, sequencia','<?= $ordem ?>')">DISPOSITIVO</a></td>
				  <td width="13%"><a href="#" class="cabecalho_tabela" onClick="ordenar('sequencia, dispositivo','<?= $ordem ?>')">FUN&Ccedil;&Atilde;O</a></td>
				  <td width="17%"><a href="#" class="cabecalho_tabela" onClick="ordenar('nr_local, sequencia','<?= $ordem ?>')">LOCAL</a></td>
				  <td width="16%"><a href="#" class="cabecalho_tabela" onClick="ordenar('cd_tag_eq, sequencia','<?= $ordem ?>')">TAG EQUIV.</a></td>
				  <td width="4%">REV.</td>
				  <td width="5%"  class="cabecalho_tabela">NOVO</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
			
					// Mostra os dispositivos
					$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.locais, ".DATABASE.".setores ";
					$sql .= "WHERE area.os = '" . $_SESSION["os"] . "' ";
					$sql .= "AND locais.id_area = area.id_area ";
					$sql .= "AND locais.id_disciplina = setores.id_setor ";
					$sql .= "AND area.id_area = subsistema.id_area ";
					$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
					$sql .= "AND malhas.id_malha = componentes.id_malha ";
					$sql .= "AND malhas.id_processo = processo.id_processo ";
					$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
					$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
					$sql .= "AND componentes.id_local = locais.id_local ";
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);

					while($componentes = mysql_fetch_array($registro))
					{
					  	if($componentes["setor"]=='EL�TRICA')
						{
							$sql = "SELECT * FROM Projetos.locais ";
							$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
							$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
							$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
							
							$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.1" . $sql);
							
							$cont = mysql_fetch_array($regis);
							
							$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];

						}
						else
						{
							if($componentes["setor"]=='MEC�NICA')
							{
								$sql = "SELECT * FROM Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
								$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
								$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
								
								$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.2" . $sql);
								
								$cont = mysql_fetch_array($regis);
								
								$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
								
							}
							else
							{
								$sql = "SELECT * FROM Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
								$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
								$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
								$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

								$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.3" . $sql);
								
								$cont = mysql_fetch_array($regis);

								$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
						
							}
						}
					
					//}
					
					/*
					$sql = "SELECT *, locais.id_disciplina AS disciplina FROM area, subsistema, malhas, dispositivos, componentes, processo, funcao, locais ";
					$sql .= "LEFT JOIN equipamentos ON (equipamentos.id_equipamentos = locais.id_equipamento ) ";
					$sql .= "WHERE area.os = '" . $_SESSION["os"] . "' ";
					$sql .= "AND area.id_area = subsistema.id_area ";
					$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
					$sql .= "AND malhas.id_malha = componentes.id_malha ";
					$sql .= "AND malhas.id_processo = processo.id_processo ";
					$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
					$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
					$sql .= "AND componentes.id_local = locais.id_local ";
					
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção." . $sql);
					*/
					$i=0;
					
					//while ($componentes = mysql_fetch_array($registro))
					//{
					
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
													

						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="19%"><div align="center">
						  	<?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $componentes["nr_malha"] . $modificador ?>
						  </div>						  </td>
						  <td width="19%"><div align="center"><?= $componentes["ds_dispositivo"] ?></div></td>
						  <td width="13%"><div align="center">
						  	<?= $componentes["ds_funcao"] ?>
						  </div></td>
					      <td width="17%"><div align="center">
						  
						  <?= $tag ?></div></td>
					      
						  <td width="17%"><div align="center">
                            <?= $componentes["cd_tag_eq"] ?>
                          </div></td>
					      <td width="5%"><div align="center">
                            <?= $componentes["comp_revisao"] ?>
                          </div></td>
					      <td width="5%">
					        <div align="center">
					        <? if($componentes["new_comp"]){?>
					        <img src="../images/buttons/aprovado.gif" width="16" height="16" border="0">
			                <? } ?>			              
		                  &nbsp;</div></td>
					      <td width="3%"><div align="center"> <a href="javascript:editar('<?= $componentes["id_componente"] ?>','<?= $componentes["id_disciplina"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="2%"><div align="center"> <a href="javascript:excluir('<?= $componentes["id_componente"] ?>','<?= $componentes["ds_dispositivo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
<?
	$db->fecha_db();
?>