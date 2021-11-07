<?php
/*

		Formulário de Componentes
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/componentes.php
	
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> ALTERAÇÃO PARA COMPONENTE
		Versão 2 --> ALTERAÇÃO PARA DISPOSITIVO
		Versão 3 --> Inclusão DA ESPECIFICAÇÃO TÉCNICA - 02/05/2006
		Versão 4 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016
		
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
include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;


$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
$sql .= "WHERE OS.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND OS.id_empresa = empresas.id_empresa ";

$reg = $db->select($sql,'MYSQL');

$cliente = mysqli_fetch_array($reg);

$client = explode(' ',$cliente["empresa"]);

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "SELECT * FROM Projetos.componentes ";
	$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] . "' ";
	$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
	$sql .= "AND id_malha = '" . $_POST["id_malha"] . "' ";
	$sql .= "AND id_local = '" . $_POST["id_local"] . "' ";
	$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
	$sql .= "AND id_disciplina = '" . $_POST["disciplina"] . "' ";
	$sql .= "AND comp_revisao = '" . $_POST["comp_revisao"] . "' ";
	$sql .= "AND comp_modif = '" . maiusculas($_POST["comp_modif"]) . "' ";
	$sql .= "AND omit_proc = '" . $_POST["omit_proc"] . "' ";
	$sql .= "AND new_comp = '" . $_POST["new_comp"] . "' ";
	
	$registros = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	// Se o número de registros for maior que zero, então existe o mesmo registro...
	if ($regs>0)
	{
		?>
		<script>
			// Mostra uma mensagem de alerta 
			alert('Componente já cadastrado na malha.');
		</script>		
		<?php
	}
	else
	{
		$sql = "UPDATE Projetos.componentes SET ";
		$sql .= "id_dispositivo = '" . $_POST["id_dispositivo"] . "', ";
		$sql .= "id_funcao = '" . $_POST["id_funcao"] . "', ";
		$sql .= "id_malha = '" . $_POST["id_malha"] . "', ";
		$sql .= "id_local = '" . $_POST["id_local"] . "', ";
		$sql .= "id_tipo = '" . $_POST["id_tipo"] . "', ";
		$sql .= "id_disciplina = '" . $_POST["disciplina"] . "', ";
		$sql .= "comp_revisao = '" . $_POST["comp_revisao"] . "', ";
		$sql .= "versao_documento = '" . $_POST["versao_documento"] . "', ";
		$sql .= "comp_modif = '" . $_POST["comp_modif"] . "', ";
		$sql .= "cd_tag_eq = '" . maiusculas($_POST["cd_tag_eq"]) . "', ";
		$sql .= "new_comp = '" . $_POST["new_comp"] . "', ";
		$sql .= "omit_proc = '" . $_POST["omit_proc"] . "' ";
		$sql .= "WHERE id_componente = '" . $_POST["id_componente"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
		
		$sql = "SELECT * FROM Projetos.especificacao_tecnica ";
		$sql .= "WHERE especificacao_tecnica.id_componente = '" . $_POST["id_componente"] ."' ";
		
		$regs = $db->select($sql,'MYSQL');
		
		$espec = mysqli_fetch_array($regs);

		$dsql = "DELETE FROM Projetos.especificacao_tecnica WHERE id_especificacao_tecnica = '".$espec["id_especificacao_tecnica"]."' ";
		
		$db->delete($dsql,'MYSQL');
		
		
		$dsql = "DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$espec["id_especificacao_tecnica"]."' ";
		
		$db->delete($dsql,'MYSQL');		
		
		//fim de curso
		if($_POST["id_dispositivo"]==39 || $_POST["id_dispositivo"]==44 || $_POST["id_dispositivo"]==40 || $_POST["id_dispositivo"]==41 || $_POST["id_dispositivo"]==43)
		{
			$disposi = 10;
		}
		
		//conversor
		if($_POST["id_dispositivo"]==165 || $_POST["id_dispositivo"]==166)
		{
			$disposi = 6;
		}
		
		//termostato
		if($_POST["id_dispositivo"]==167)
		{
			$disposi = 18;
		}
		
		//transmissor
		if($_POST["id_dispositivo"]==135 || $_POST["id_dispositivo"]==136 || $_POST["id_dispositivo"]==174)
		{
			$disposi = 6;
		}		
		
		//solenoide
		if($_POST["id_dispositivo"]==38)
		{
			$disposi = 36;
		}
		
		//sensor
		if($_POST["id_dispositivo"]==175)
		{
			$disposi = 32;
		}
		
		//válvula
		if($_POST["id_dispositivo"]==73 || $_POST["id_dispositivo"]==74 || $_POST["id_dispositivo"]==75 || $_POST["id_dispositivo"]==76 || $_POST["id_dispositivo"]==170 || $_POST["id_dispositivo"]==192)
		{
			$disposi = 37;
		}
		
		//manometro
		if($_POST["id_dispositivo"]==42)
		{
			$disposi = 12;
		}

		//ALARME
		if($_POST["id_dispositivo"]==54 || $_POST["id_dispositivo"]==49)
		{
			$disposi = 21;
		}
		
		//chave de nivel
		if($_POST["id_dispositivo"]==51 || $_POST["id_dispositivo"]==53)
		{
			$disposi = 4;
		}
		
		//estação manual
		if($_POST["id_dispositivo"]==60)
		{
			$disposi = 9;
		}
		
		//fluxostato
		if($_POST["id_dispositivo"]==161)
		{
			$disposi = 27;
		}
		
		//pressostato
		if($_POST["id_dispositivo"]==92 || $_POST["id_dispositivo"]==93 || $_POST["id_dispositivo"]==94 || $_POST["id_dispositivo"]==181 || $_POST["id_dispositivo"]==182 || $_POST["id_dispositivo"]==50 || $_POST["id_dispositivo"]==52 || $_POST["id_dispositivo"]==164 )
		{
			$disposi = 15;
		}
		
		//termometro
		if($_POST["id_dispositivo"]==55)
		{
			$disposi = 17;
		}
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao ";
		$sql .= "WHERE (id_dispositivo = '" . $_POST["id_dispositivo"] . "' OR  id_dispositivo = '" . $disposi . "') ";
		$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
		$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
		
		$registros = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;
		
		if($count>0)
		{
			$regs = mysqli_fetch_array($registros);
			
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.especificacao_tecnica ";
			$isql .= "(id_especificacao_padrao, id_componente) ";
			$isql .= "VALUES (";
			$isql .= "'" . $regs["id_especificacao_padrao"] . "', ";
			$isql .= "'" . $_POST["id_componente"] . "') ";
			
			$registros = $db->insert($isql,'MYSQL');
			
			$esp = $db->insert_id;
			
			$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE id_especificacao_padrao = '" . $regs["id_especificacao_padrao"] . "' ";
			
			$regis = $db->select($sql,'MYSQL');
			
			while($reg = mysqli_fetch_array($regis))
			{
				//Cria sentença de Inclusão no bd
				$isql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
				$isql .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
				$isql .= "VALUES (";
				$isql .= "'" . $esp . "', ";
				$isql .= "'" . $reg["id_especificacao_detalhe"] . "', ";
				$isql .= "'" . $reg["conteudo"] . "') ";
				
				$regist = $db->insert($isql,'MYSQL');			
			}
			
		}
		?>
		<script>
			alert('Componente atualizado com sucesso.');
		</script>
		<?php
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
	$sql .= "AND id_disciplina = '" . $_POST["disciplina"] . "' ";
	$sql .= "AND comp_revisao = '" . $_POST["comp_revisao"] . "' ";
	$sql .= "AND comp_modif = '" . $_POST["comp_modif"] . "' ";
	$sql .= "AND omit_proc = '" . $_POST["omit_proc"] . "' ";
	$sql .= "AND new_comp = '" . $_POST["new_comp"] . "' ";
	
	$registros = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	// Se o número de registros for maior que zero, então existe o mesmo registro...
	if ($regs>0)
	{
		?>
		<script>
			// Mostra uma mensagem de alerta 
			alert('Componente já cadastrado na malha.');
		</script>		
		<?php
	}
	else
	{
	
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.componentes ";
		$isql .= "(id_funcao, id_malha, id_local, id_tipo, id_dispositivo, id_disciplina, comp_modif, comp_revisao, omit_proc, new_comp, cd_tag_eq) VALUES (";
		$isql .= "'" . $_POST["id_funcao"] . "', ";
		$isql .= "'" . $_POST["id_malha"] . "', ";
		$isql .= "'" . $_POST["id_local"] . "', ";
		$isql .= "'" . $_POST["id_tipo"] . "', ";
		$isql .= "'" . $_POST["id_dispositivo"] . "', ";
		$isql .= "'" . $_POST["disciplina"] . "', ";
		$isql .= "'" . maiusculas($_POST["comp_modif"]) . "', ";
		$isql .= "'" . $_POST["comp_revisao"] . "', ";
		$isql .= "'" . $_POST["omit_proc"] . "', ";
		$isql .= "'" . $_POST["new_comp"] . "', ";
		$isql .= "'" . maiusculas($_POST["cd_tag_eq"]) . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
		
		$comp = $db->insert_id;
		
		//fim de curso
		if($_POST["id_dispositivo"]==39 || $_POST["id_dispositivo"]==44 || $_POST["id_dispositivo"]==40 || $_POST["id_dispositivo"]==41 || $_POST["id_dispositivo"]==43)
		{
			$disposi = 10;
		}
		
		//solenoide
		if($_POST["id_dispositivo"]==38)
		{
			$disposi = 36;
		}
		
		//manometro
		if($_POST["id_dispositivo"]==42)
		{
			$disposi = 12;
		}

		//ALARME
		if($_POST["id_dispositivo"]==54 || $_POST["id_dispositivo"]==49)
		{
			$disposi = 21;
		}
		
		//chave de nivel
		if($_POST["id_dispositivo"]==51 || $_POST["id_dispositivo"]==53)
		{
			$disposi = 4;
		}
		
		//estação manual
		if($_POST["id_dispositivo"]==60)
		{
			$disposi = 9;
		}
		
		//pressostato
		if($_POST["id_dispositivo"]==52 || $_POST["id_dispositivo"]==50)
		{
			$disposi = 15;
		}
		
		//termometro
		if($_POST["id_dispositivo"]==52 || $_POST["id_dispositivo"]==50)
		{
			$disposi = 17;
		}
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao ";
		$sql .= "WHERE (id_dispositivo = '" . $_POST["id_dispositivo"] . "' OR  id_dispositivo = '" . $disposi . "') ";
		$sql .= "AND id_funcao = '" . $_POST["id_funcao"] . "' ";
		$sql .= "AND id_tipo = '" . $_POST["id_tipo"] . "' ";
		
		$registros = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;
		
		if($count>0)
		{
			$regs = mysqli_fetch_array($registros);
			
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.especificacao_tecnica ";
			$isql .= "(id_especificacao_padrao, id_componente) ";
			$isql .= "VALUES (";
			$isql .= "'" . $regs["id_especificacao_padrao"] . "', ";
			$isql .= "'" . $comp . "') ";
			
			$registros = $db->insert($isql,'MYSQL');
			
			$esp = $db->insert_id;
			
			$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE id_especificacao_padrao = '" . $regs["id_especificacao_padrao"] . "' ";
			
			$regis = $db->select($sql,'MYSQL');
			
			while($reg = mysqli_fetch_array($regis))
			{
				//Cria sentença de Inclusão no bd
				$isql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
				$isql .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
				$isql .= "VALUES (";
				$isql .= "'" . $esp . "', ";
				$isql .= "'" . $reg["id_especificacao_detalhe"] . "', ";
				$isql .= "'" . $reg["conteudo"] . "') ";
				
				$regist = $db->insert($isql,'MYSQL');			
			
			}		
		}		
		
		?>
		<script>
			alert('Componente inserido com sucesso.');
		</script>
		<?php
	}
	
$_POST["id_processo"] = $_POST["id_processo"];

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.componentes WHERE id_componente = '".$_GET["id_componente"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Componente excluído com sucesso.');
	</script>
	<?php
}

?>

<html>
<head>
<title>: : . COMPONENTES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>


function excluir(id_componente, componente, id_processo)
{
	if(confirm('Tem certeza que deseja excluir o componente '+componente+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_componente='+id_componente+'&id_processo='+id_processo+'';
	}
}

function editar(id_componente, id_disciplina, id_processo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_componente='+id_componente+'&id_disciplina='+id_disciplina+'&id_processo='+id_processo+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

function altera()
{
	document.getElementById('acao').value='';
	document.getElementById('frm_componentes').submit();
}

function replicar()
{
	if(confirm('Tem certeza que deseja duplicar a malha'))
	{
		if(document.getElementById('id_malha').value == '')
		{
			alert('Para duplicar uma malha, deve-se escolher uma.');
		}
		else
		{
			document.getElementById('acao').value = 'replicar';
			document.getElementById('frm_componentes').action = 'replicarmalhas.php';
			document.getElementById('frm_componentes').submit();
		}
	}
}

//Função para redimensionar a janela.
function maximiza() 
{

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


//Função para preenchimento dos comboboxes dinâmicos.
function preenchecomboeei(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?php

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";

$reg = $db->select($sql,'MYSQL');


	while ($cont = mysqli_fetch_array($reg))
	{
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= addslashes($cont["nr_area"]). " - ".  addslashes($cont["cd_local"]). " ". addslashes($cont["nr_sequencia"]). " - ". addslashes($cont["ds_equipamento"]) ?>','<?= addslashes($cont["id_local"]) ?>');
		<?php 
	} 
	?>
}


//Função para preenchimento dos comboboxes dinâmicos.
function preenchecombomecanica(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?php

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'MECÂNICA' ";
$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";

$reg = $db->select($sql,'MYSQL');


	while ($cont = mysqli_fetch_array($reg))
	{
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"]. "  ". $cont["ds_descricao"] ?>','<?= $cont["id_local"] ?>');
		<?php 
	} 
	?>
}

//Função para preenchimento dos comboboxes dinâmicos.
function preenchecombolinha(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
<?php

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
$sql .= "AND ".DATABASE.".setores.setor = 'TUBULAÇÃO' ";
$sql .= "ORDER BY nr_area, cd_fluido, nr_sequencia, cd_material, nr_diametro ";

$reg = $db->select($sql,'MYSQL');


	while ($cont = mysqli_fetch_array($reg))
	{
	
		if($client[0]=='SUZANO')
		{
			$linha = $cont["cd_fluido"]." - ".$cont["nr_diametro"]." - ".$cont["cd_material"]." - ". $cont["ds_complemento"]." - ".$cont["nr_sequencia"];
		}
		else
		{
			$linha = $cont["cd_fluido"]. " - " . $cont["nr_sequencia"]. " - " . $cont["cd_material"]. " - " . $cont["nr_diametro"];
		}	
	
		?>
			combobox_destino.options[combobox_destino.length] = new Option('<?= addslashes($linha) ?>','<?= $cont["id_local"] ?>');
		<?php 
	} 
	?>
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">

<center>
<form name="frm_componentes" id="frm_componentes" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
	  <tr>
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.componentes WHERE id_componente= '" . $_GET["id_componente"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$componentes = mysqli_fetch_array($registro); 	
			 
			 
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
                      <td width="10%" class="label1">MALHA</td>
                      <td width="1%"> </td>
                      <td width="10%" class="label1">DISPOSITIVO</td>
                      <td width="1%" class="label1"> </td>
                      <!-- <td width="12%" class="label1">TIPO</td> 
                      <td width="1%" class="label1"> </td> -->
                      <td width="9%" class="label1">FUNÇÃO</td>
                      <td width="1%" class="label1"> </td>
                      <td width="10%" class="label1">TIPO</td>
                      <td width="1%" class="label1"> </td>
                      <td width="57%" class="label1">MOD.</td>
                      </tr>
                    <tr>
                      <td><select name="id_malha" class="txt_box" id="id_malha" onkeypress="return keySort(this);">
					  <option value="">SELECIONE</option>
					  <?php
					  
					  	//onChange="preencheComboFuncao(this.form.funcao, this, this.selectedIndex)"
						$sql_malha = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo ";
						$sql_malha .= "WHERE area.id_area = subsistema.id_area ";
						$sql_malha .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
						$sql_malha .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql_malha .= "AND malhas.id_processo = processo.id_processo ";
						$sql_malha .= "ORDER BY processo, nr_malha ";
						
						$reg_malha = $db->select($sql_malha,'MYSQL');
						
						while($cont_malha = mysqli_fetch_array($reg_malha))
						{
							if($cont_malha["processo"]!='D')
							{
								$nrmalha = sprintf("%03d",$cont_malha["nr_malha"]);
							}
							else
							{
								$nrmalha = $cont_malha["nr_malha"];
							}
							
							if($cont_malha["nr_malha_seq"]!='')
							{
								$nrseq = '.'.$cont_malha["nr_malha_seq"];
							}
							else
							{
								$nrseq = ' ';
							}
							
							?>
							<option value="<?= $cont_malha["id_malha"] ?>" <?php if($cont_malha["id_malha"]==$componentes["id_malha"]) { echo "selected"; } ?>><?= $cont_malha["processo"] . " - " . $nrmalha.$nrseq . " - " . $cont_malha["ds_servico"] ?></option>
							<?php
							
						}
						
					?>
						
					  
                      </select></td>
                      <td> </td>
                      <td><select name="id_dispositivo" class="txt_box" id="id_dispositivo" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_componente = "SELECT * FROM Projetos.dispositivos ORDER BY dispositivo, ds_dispositivo ";
						
						$reg_componente = $db->select($sql_componente,'MYSQL');
						
						while($cont_componente = mysqli_fetch_array($reg_componente))
						{
						?>
                        <option value="<?= $cont_componente["id_dispositivo"] ?>" <?php if($cont_componente["id_dispositivo"]==$componentes["id_dispositivo"]) { echo "selected"; } ?>>
                        <?php 
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
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_funcao" class="txt_box" id="id_funcao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_funcao = "SELECT * FROM Projetos.funcao ORDER BY funcao, ds_funcao ";
						
						$reg_funcao = $db->select($sql_funcao,'MYSQL');
						
						while($cont_funcao = mysqli_fetch_array($reg_funcao))
						{
						?>
                        <option value="<?= $cont_funcao["id_funcao"] ?>" <?php if($cont_funcao["id_funcao"]==$componentes["id_funcao"]) { echo "selected"; } ?>>
                        <?php 
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
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_tipo" id="id_tipo" class="txt_box" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_tipo = "SELECT * FROM Projetos.tipo ";
						$sql_tipo .= "ORDER BY ds_tipo ";
						
						$reg_tipo = $db->select($sql_tipo,'MYSQL');
						
						while($tipo = mysqli_fetch_array($reg_tipo))
						{
						?>
                        <option value="<?= $tipo["id_tipo"] ?>" <?php if($tipo["id_tipo"]==$componentes["id_tipo"]) { echo "selected"; } ?>>
                        <?= $tipo["ds_tipo"];

						?>
                        </option>
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><input name="comp_modif" type="text" class="txt_box" id="comp_modif" value="<?= $componentes["comp_modif"] ?>" size="10" ></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="18%">TIPO LOCAL</td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">LOCAL</span></td>
                      <td width="1%"> </td>
                      <td width="70%">DISCIPLINA</td>
                      </tr>
                    <tr>
                      <td><table width="100%" border="0">
                        <tr class="label1">
                          <td width="7%">EEI</td>
                          <td width="3%"> </td>
                          <td width="21%">MECÂNICA</td>
                          <td width="3%"> </td>
                          <td width="66%">LINHA</td>
                        </tr>
                        <tr>
                          <td><div align="center">
						  <?php
							
							$sql1 = "SELECT * FROM ".DATABASE.".setores ";
							$sql1 .= "WHERE id_setor = '" . $_GET["id_disciplina"] . "' ";
							
							$regis = $db->select($sql1,'MYSQL');
							
							$disciplina = mysqli_fetch_array($regis);
							
						  ?>
                              <input name="tipo" type="radio" value="ELÉTRICA" <?php if($disciplina["setor"]=='ELÉTRICA'){ echo 'checked';} ?> onclick="preenchecomboeei(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                              <input name="tipo" type="radio" value="MECÂNICA" <?php if($disciplina["setor"]=='MECÂNICA'){ echo 'checked';} ?> onclick="preenchecombomecanica(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                              <input name="tipo" type="radio" value="TUBULAÇÃO" <?php if($disciplina["setor"]=='TUBULAÇÃO'){ echo 'checked';} ?> onclick="preenchecombolinha(this.form.id_local, this)">
                          </div></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td><select name="id_local" class="txt_box" id="id_local" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						//
					  	if($disciplina["setor"]=='ELÉTRICA')
						{
							$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
							$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
							$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
							$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
							$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
							$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
							$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
							
							$regis = $db->select($sql,'MYSQL');
							
							while($cont = mysqli_fetch_array($regis))
							{
							?>
							<option value="<?= $cont["id_local"] ?>" <?php if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"] ?>
							</option>
							<?php
								
							}
						}
						else
						{
							if($disciplina["setor"]=='MECÂNICA')
							{
								$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
								$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
								$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
								$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
								$sql .= "AND ".DATABASE.".setores.setor = 'MECÂNICA' ";
								$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";							
								
								$regis = $db->select($sql,'MYSQL');
								
								while($cont = mysqli_fetch_array($regis))
								{
								?>
								<option value="<?= $cont["id_local"] ?>" <?php if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
								<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"]. "  ". $cont["ds_descricao"] ?>
								</option>
								<?php
									
								}
							
							}
							else
							{
								$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
								$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
								$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
								$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
								$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
								$sql .= "AND ".DATABASE.".setores.setor = 'TUBULAÇÃO' ";
								$sql .= "ORDER BY nr_area, cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

								$regis = $db->select($sql,'MYSQL');
								
								while($cont = mysqli_fetch_array($regis))
								{
									
									if($client[0]=='SUZANO')
									{
										$linha = $cont["cd_fluido"]." - ".$cont["nr_diametro"]." - ".$cont["cd_material"]." - ". $cont["ds_complemento"]." - ".$cont["nr_sequencia"];
									}
									else
									{
										$linha = $cont["cd_fluido"]. " - " . $cont["nr_sequencia"]. " - " . $cont["cd_material"]. " - " . $cont["nr_diametro"];
									}								

								?>
								<option value="<?= $cont["id_local"] ?>" <?php if($cont["id_local"]==$componentes["id_local"]) { echo "selected"; } ?>>
								<?= $linha //$cont["nr_area"]. " - ".  $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"] ?>
								</option>
								<?php
									
								}

							
							
							}
						}
						

						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="disciplina" class="txt_box"  id="disciplina" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
							$sql = "SELECT * FROM ".DATABASE.".setores WHERE setor NOT LIKE 'ARQUIVO TÉCNICO' AND setor NOT LIKE 'ADMINISTRATIVO' ";
							$sql .= " AND setor NOT LIKE 'TECNOLOGIA DE INFORMAÇÃO' AND setor NOT LIKE 'COMERCIAL' AND setor NOT LIKE 'COORDENAÇÃO' ";
							$sql .= " AND setor NOT LIKE 'CIVIL' AND setor NOT LIKE 'GERAL' AND setor NOT LIKE 'ENGENHARIA BÁSICA/PROCESSO' ";
							$sql .= " AND setor NOT LIKE 'FINANCEIRO' AND setor NOT LIKE 'GESTÃO DE QUALIDADE' AND setor NOT LIKE 'PLANEJAMENTO' ORDER BY setor ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os países
							while ($cont = mysqli_fetch_array($registro))
								{
									?>
                        <option value="<?= $cont["id_setor"] ?>"<?php if($cont["id_setor"]==$componentes["id_disciplina"]){ echo 'selected'; } ?>>
                        <?= $cont["setor"] ?>
                        </option>
                        <?php
								}
						
						?>
                      </select></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="19%"><span class="label1">TAG EQUIVALENTE</span></td>
                      <td width="3%"> </td>
                      <td width="10%">REVISÃO</td>
                      <td width="3%"> </td>
                      <td width="21%">OMITIR PROCESSO</td>
                      <td width="3%"> </td>
                      <td width="22%">NOVO COMPONENTE</td>
                      <td width="19%"> </td>
                    </tr>
                    <tr>
                      <td><input name="cd_tag_eq" type="text" class="txt_box" id="cd_tag_eq" value="<?= $componentes["cd_tag_eq"] ?>" size="35"></td>
                      <td> </td>
                      <td><input name="versao_documento" id="versao_documento" type="hidden" value="<?= versao_documento($componentes["comp_revisao"]) ?>">
                        <input name="comp_revisao" type="text" class="txt_boxcap" id="comp_revisao" value="<?= $componentes["comp_revisao"] ?>" size="15"></td>
                      <td> </td>
                      <td><div align="center">
                        <input name="omit_proc" type="checkbox" id="omit_proc" value="1" <?php if($componentes["omit_proc"]){ echo 'checked';} ?>>
                      </div></td>
                      <td> </td>
                      <td><div align="center">
                        <input name="new_comp" type="checkbox" id="new_comp" value="1" <?php if($componentes["new_comp"]){ echo 'checked';} ?>>
                      </div></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_componente" type="hidden" id="id_componente" value="<?= $componentes["id_componente"] ?>">
				  <input name="id_processo" type="hidden" id="id_processo" value="<?= $_GET["id_processo"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Voltar" type="button" class="btn" id="Voltar" value="VOLTAR" onclick="history.back();">
                    <!-- <input name="Especificação técnica" type="button" class="btn" id="Especificação técnica" value="Especificação técnica" onclick="javascript:location.href='especificacao_tecnica.php';"> -->				</td>
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
                      <td width="9%" class="label1">MALHA</td>
                      <td width="1%"> </td>
                      <td width="10%" class="label1">DISPOSITIVO</td>
                      <td width="1%" class="label1"> </td>
                      <!-- <td width="12%" class="label1">TIPO</td> 
                      <td width="1%" class="label1"> </td> -->
                      <td width="9%" class="label1">FUNÇÃO</td>
                      <td width="1%" class="label1"> </td>
                      <td width="9%" class="label1">TIPO</td>
                      <td width="1%" class="label1"> </td>
                      <td width="48%" class="label1">MOD.</td>
                      <td width="2%" class="label1"> </td>
                      <td width="9%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_malha" class="txt_box" id="id_malha" onkeypress="return keySort(this);" >
                        <option value="">SELECIONE</option>
                        <?php
					  	//onChange="preencheComboFuncao(this.form.funcao, this, this.selectedIndex)"
						$sql_malha = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo ";
						$sql_malha .= "WHERE area.id_area = subsistema.id_area ";
						$sql_malha .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
						$sql_malha .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql_malha .= "AND malhas.id_processo = processo.id_processo ";
						$sql_malha .= "ORDER BY processo, nr_malha ";
						
						$reg_malha = $db->select($sql_malha,'MYSQL');
						
						while($cont_malha = mysqli_fetch_array($reg_malha))
						{
							if($cont_malha["processo"]!='D')
							{
								$nrmalha = sprintf("%03d",$cont_malha["nr_malha"]);
							}
							else
							{
								$nrmalha = $cont_malha["nr_malha"];
							}
							
							if($cont_malha["nr_malha_seq"]!='')
							{
								$nrseq = '.'.$cont_malha["nr_malha_seq"];
							}
							else
							{
								$nrseq = ' ';
							}
							
							?>
                        <option value="<?= $cont_malha["id_malha"] ?>" <?php if($cont_malha["id_malha"]==$_POST["id_malha"]) { echo "selected"; } ?>>
                          <?= $cont_malha["processo"] . " - " . $nrmalha.$nrseq . " - " . $cont_malha["ds_servico"] ?>
                          </option>
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_dispositivo" class="txt_box" id="id_dispositivo" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_componente = "SELECT * FROM Projetos.dispositivos ";
						$sql_componente .="ORDER BY dispositivo, ds_dispositivo ";
						
						$reg_componente = $db->select($sql_componente,'MYSQL');
						
						while($cont_componente = mysqli_fetch_array($reg_componente))
						{
						?>
                        <option value="<?= $cont_componente["id_dispositivo"] ?>" <?php if($cont_componente["id_dispositivo"]==$_POST["id_dispositivo"]) { echo "selected"; } ?>>
                        <?php
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
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_funcao" class="txt_box" id="id_funcao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_funcao = "SELECT * FROM Projetos.funcao ORDER BY funcao, ds_funcao ";
						
						$reg_funcao = $db->select($sql_funcao,'MYSQL');
						
						while($cont_funcao = mysqli_fetch_array($reg_funcao))
						{
						?>
                        <option value="<?= $cont_funcao["id_funcao"] ?>" <?php if($cont_funcao["id_funcao"]==$_POST["id_funcao"]) { echo "selected"; } ?>>
                        <?php
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
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_tipo" id="id_tipo" class="txt_box" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
					  
						$sql_tipo = "SELECT * FROM Projetos.tipo ";
						$sql_tipo .= "ORDER BY ds_tipo ";
						
						$reg_tipo = $db->select($sql_tipo,'MYSQL');
						
						while($tipo = mysqli_fetch_array($reg_tipo))
						{
						?>
                        <option value="<?= $tipo["id_tipo"] ?>" <?php if($tipo["id_tipo"]==$_POST["id_tipo"]) { echo "selected"; } ?>>
                        <?= $tipo["ds_tipo"] ?>
                        </option>
                        <?php
							
						}
						
					?>
                      </select></td>
                      <td> </td>
                      <td><input name="comp_modif" type="text" class="txt_box" id="comp_modif" value="<?= $_POST["comp_modif"] ?>" size="10"></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="18%">TIPO LOCAL</td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">LOCAL</span></td>
                      <td width="1%"> </td>
                      <td width="29%">DISCIPLINA</td>
                      <td width="41%"> </td>
                      </tr>
                    <tr>
                      <td><table width="52%" border="0">
                        <tr class="label1">
                          <td width="8%">EEI</td>
                          <td width="3%"> </td>
                          <td width="25%">MECÂNICA</td>
                          <td width="3%"> </td>
                          <td width="61%">LINHA</td>
                        </tr>
                        <tr>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="ELÉTRICA" onclick="preenchecomboeei(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="MECÂNICA" onclick="preenchecombomecanica(this.form.id_local, this)">
                          </div></td>
                          <td><div align="center"></div></td>
                          <td><div align="center">
                            <input name="tipo" type="radio" value="TUBULAÇÃO" onclick="preenchecombolinha(this.form.id_local, this)">
                          </div></td>
                        </tr>
                      </table></td>
                      <td> </td>
                      <td><select name="id_local" class="txt_box" id="id_local" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
					  	/*
						$sql_local = "SELECT * FROM locais_outros, equipamentos_outros ";
						$sql_local .= "WHERE locais_outros.id_equipamento_o = equipamentos.id_equipamento_o ORDER BY nr_local_o ";
						$reg_local = mysql_query($sql_local,$conexao);
						
						while($cont_local = mysql_fetch_array($reg_local))
						{
						?>
                          <option value="<?= $cont_local["id_localoutro"] ?>" <?php if($cont_local["id_localoutro"]==$_POST["id_localoutro"]) { echo "selected"; } ?>>
                          <?= $cont_local["nr_local_o"] . " - " . $cont_local["cd_local_o"] . " - " . $cont_local["ds_equipamento_o"] ?>
                          </option>
                          <?
							
						}
						*/
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="disciplina" class="txt_box"  id="disciplina" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						  	
							$sql = "SELECT * FROM ".DATABASE.".setores WHERE setor NOT LIKE 'ARQUIVO TÉCNICO' AND setor NOT LIKE 'ADMINISTRATIVO' ";
							$sql .= " AND setor NOT LIKE 'TECNOLOGIA DE INFORMAÇÃO' AND setor NOT LIKE 'COMERCIAL' AND setor NOT LIKE 'COORDENAÇÃO' ";
							$sql .= " AND setor NOT LIKE 'CIVIL' AND setor NOT LIKE 'GERAL' AND setor NOT LIKE 'ENGENHARIA BÁSICA/PROCESSO' ";
							$sql .= " AND setor NOT LIKE 'FINANCEIRO' AND setor NOT LIKE 'GESTÃO DE QUALIDADE' AND setor NOT LIKE 'PLANEJAMENTO' ORDER BY setor ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os países
							while ($cont = mysqli_fetch_array($registro))
								{
									?>
                        <option value="<?= $cont["id_setor"] ?>"<?php if($cont["id_setor"]==$_POST["disciplina"]){ echo 'selected'; } ?>>
                        <?= $cont["setor"] ?>
                        </option>
                        <?php
								}
						
						?>
                      </select></td>
                      <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="16%"><span class="label1">TAG EQUIVALENTE</span></td>
                      <td width="1%"> </td>
                      <td width="7%">REVISÃO</td>
                      <td width="1%"> </td>
                      <td width="17%">OMITIR PROCESSO</td>
                      <td width="1%"> </td>
                      <td width="20%">NOVO COMPONENTE</td>
                      <td width="37%">EXIBIR POR: </td>
                    </tr>
                    <tr>
                      <td><input name="cd_tag_eq" type="text" class="txt_box" id="cd_tag_eq" value="<?= $_POST["cd_tag_eq"] ?>" size="35"></td>
                      <td> </td>
                      <td><input name="comp_revisao" type="text" class="txt_boxcap" id="comp_revisao" value="0" size="15"></td>
                      <td> </td>
                      <td><div align="center">
                        <input name="omit_proc" type="checkbox" id="omit_proc" value="1" <?php if($_POST["omit_proc"]){echo 'checked';} ?>>
                      </div></td>
                      <td> </td>
                      <td><div align="center">
                        <input name="new_comp" type="checkbox" id="new_comp" value="1" <?php if($_POST["omit_proc"]){echo 'checked';} ?>>
                      </div></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_processo" id="id_processo" class="txt_box" onkeypress="return keySort(this);" onChange="altera()">
                          <option value="">TODOS</option>
                          <?php
									if($_GET["id_processo"])
									{
										$idp = $_GET["id_processo"];
									}
									else
									{
										$idp = $_POST["id_processo"];
									}
									//Popula a combo-box de Função.
									$sql = "SELECT * FROM Projetos.processo ";
									//$sql .= "GROUP BY ds_processo ";
									$sql .= "ORDER BY ds_processo ";
									
									$regdescricao = $db->select($sql,'MYSQL');
									
									while ($reg = mysqli_fetch_array($regdescricao))
										{
											?>
                          <option value="<?= $reg["id_processo"] ?>"<?php if ($idp == $reg["id_processo"]){ echo 'selected';}?>>
                          <?= $reg["processo"] . " - " . $reg["ds_processo"] . " - " . $reg["norma"] ?>
                          </option>
                          <?php
										}
								
								
							?>
                        </select>
                      </font></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Voltar" type="button" class="btn" id="Voltar" value="VOLTAR" onclick="javascript:history.back();">
                    <!-- <input name="Especificação técnica" type="button" class="btn" id="Malhas" value="Especificação técnica" onclick="javascript:location.href='especificacao tecnica.php';"> -->
				    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="DUPLICAR MALHA" onclick="replicar()">
				    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="ESPECIFICAÇÃO TÉCNICA" onclick="javascript:location.href='especificacao_tecnica.php';"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td><span class="label1">regs:<font size="2" face="Arial, Helvetica, sans-serif">
                  <?php
							$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.locais, ".DATABASE.".setores ";
							$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND locais.id_area = area.id_area ";
							$sql .= "AND locais.id_disciplina = setores.id_setor ";
							$sql .= "AND area.id_area = subsistema.id_area ";
							$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
							$sql .= "AND malhas.id_malha = componentes.id_malha ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
							$sql .= "AND componentes.id_local = locais.id_local ";
							
							$regs = $db->select($sql,'MYSQL');
							
							$regcounter = $db->numero_registros;
							
							echo $regcounter;
						?>
                  </font></span></td>
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
				  
				  	if($_POST["id_processo"] || $_GET["id_processo"])
					{
						$exibir = "AND (processo.id_processo = '".$_POST["id_processo"]."' OR processo.id_processo = '".$_GET["id_processo"]."') ";
					}
					else
					{
						$exibir ="";
					}				  
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "processo, nr_malha, nr_malha_seq, ds_dispositivo, ds_funcao, comp_modif ";
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
				  <td width="19%"><a href="#" class="cabecalho_tabela" onclick="ordenar(' processo, nr_malha, ds_dispositivo, ds_funcao, comp_modif ','<?= $ordem ?>')">TAG</a></td>
				  <td width="19%"><a href="#" class="cabecalho_tabela" onclick="ordenar('dispositivo, sequencia','<?= $ordem ?>')">DISPOSITIVO</a></td>
				  <td width="13%"><a href="#" class="cabecalho_tabela" onclick="ordenar('sequencia, dispositivo','<?= $ordem ?>')">FUNÇÃO</a></td>
				  <td width="17%"><a href="#" class="cabecalho_tabela" onclick="ordenar('nr_local, sequencia','<?= $ordem ?>')">LOCAL</a></td>
				  <td width="16%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_tag_eq, sequencia','<?= $ordem ?>')">TAG EQUIV.</a></td>
				  <td width="4%">REV.</td>
				  <td width="5%"  class="cabecalho_tabela">NOVO</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					// Arquivo de Inclusão de conexão com o banco
			
					// Mostra os dispositivos
					$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.locais, ".DATABASE.".setores ";
					$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
					$sql .= "AND locais.id_area = area.id_area ";
					$sql .= "AND locais.id_disciplina = setores.id_setor ";
					$sql .= "AND area.id_area = subsistema.id_area ";
					$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
					$sql .= "AND malhas.id_malha = componentes.id_malha ";
					$sql .= "AND malhas.id_processo = processo.id_processo ";
					$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
					$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
					$sql .= "AND componentes.id_local = locais.id_local ";
					$sql .= $exibir;
					$sql .= "ORDER BY " . $campo ." ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i = 0;
					
					while($componentes = mysqli_fetch_array($registro))
					{
					  	if($componentes["setor"]=='ELÉTRICA')
						{
							$sql = "SELECT * FROM Projetos.locais ";
							$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
							$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
							$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
							
							$regis = $db->select($sql,'MYSQL');
							
							$cont = mysqli_fetch_array($regis);
							
							$tag = $componentes["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];

						}
						else
						{
							if($componentes["setor"]=='MECÂNICA')
							{
								$sql = "SELECT * FROM Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
								$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
								$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
								
								$regis = $db->select($sql,'MYSQL');
								
								$cont = mysqli_fetch_array($regis);
								
								$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
								
							}
							else
							{
								$sql = "SELECT * FROM Projetos.locais ";
								$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
								$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
								$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
								$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

								$regis = $db->select($sql,'MYSQL');
								
								$cont = mysqli_fetch_array($regis);
								
								if($client[0]=='SUZANO')
								{
									$tag = $cont["cd_fluido"]." - ".$cont["nr_diametro"]." - ".$cont["cd_material"]." - ". $cont["ds_complemento"]." - ".$cont["nr_sequencia"];
								}
								else
								{
									$tag = $cont["cd_fluido"]. " - " . $cont["nr_sequencia"]. " - " . $cont["cd_material"]. " - " . $cont["nr_diametro"];
								}	

								//$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
						
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
						
						if($componentes["processo"]!='D')
						{

							$nrmalha = sprintf("%03d", $componentes["nr_malha"]);
						}
						else
						{
							$nrmalha = $componentes["nr_malha"];
						}
						
						if($componentes["nr_malha_seq"]!='')
						{
							$nrseq = '.'.$componentes["nr_malha_seq"];
						}
						else
						{
							$nrseq = ' ';
						}
													

						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="19%"><div align="center">
						  	<?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $nrmalha.$nrseq.$modificador ?>
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
					        <?php if($componentes["new_comp"]){?>
					        <img src="../images/buttons/aprovado.gif" width="16" height="16" border="0">
			                <?php
							 } ?>			              
		                   </div></td>
					      <td width="3%"><div align="center"> <a href="javascript:editar('<?= $componentes["id_componente"] ?>','<?= $componentes["id_disciplina"] ?>','<?= $componentes["id_processo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="2%"><div align="center"> <a href="javascript:excluir('<?= $componentes["id_componente"] ?>','<?= $componentes["ds_dispositivo"] ?>','<?= $componentes["id_processo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
					}
					// Libera a memória
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
