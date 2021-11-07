<?php
/*

		Formulário de Especificação Técnica
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/especificacao tecnica.php
		
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

if ($_POST["acao"]=="salvar" && $_POST["emissao"]=='1')
{
	$sql = "SELECT * FROM ".DATABASE.".revisao_cliente ";
	$sql .= "WHERE id_os = '". $_SESSION["id_os"] . "' ";
	$sql .= "AND tipodoc = '". $_POST["relatorio"] . "' ";
	$sql .= "AND versao_documento = '". $_POST["versao_documento"] . "' ";
	$sql .= "AND numero_cliente = '". $_POST["numero_cliente"] . "' ";
	$sql .= "AND numeros_interno = '". $_POST["numeros_interno"] . "' ";
	$sql .= "AND documento = '" . $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"].".pdf". "' ";
	$verify = mysql_query($sql, $db->conexao) or die("Não foi possível fazer a seleção.".$sql);
	$regs = mysql_num_rows($verify);
	if ($regs>0)
		{
			?>
			<script>
				alert('Não é possível emitir nesta revisão.');
			</script>
			<?php
		}
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO ".DATABASE.".revisao_cliente ";
			$isql .= "(id_os, tipodoc, alteracao, id_executante, id_verificador, id_aprovador, versao_documento, data_emissao, qtd_folhas, numero_cliente, numeros_interno, documento ) ";
			$isql .= "VALUES ('" . $_SESSION["id_os"] ."', '". $_POST["relatorio"] . "', '". maiusculas($_POST["alteracao"]) . "',  ";
			$isql .= "'". $_POST["executante"] . "', '". $_POST["verificador"] . "', '". $_POST["aprovador"] . "', ";
			$isql .= "'". $_POST["versao_documento"] . "', '". date('Y-m-d') . "', '". $_POST["folhas"] . "', '". $_POST["numero_cliente"] . "', '". $_POST["numeros_interno"] . "', ";
			$isql .= "'". $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"].".pdf". "') ";

			$registros = mysql_query($isql,$db->conexao) or die("Não foi possível a inserção dos dados".$isql);
		
			$envio_rel = true;

		}
		
	?>
	<script>
		document.forms['espec_tec'].acao.value='';
	</script>
	
	<?php

}	
?>

<html>
<head>
<title>: : . ESPECIFICAÇÃO TÉCNICA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"> </script> 


<!-- Javascript para envio dos dados através do método GET -->
<script>

function maximiza() 
{
	//Função para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}


function enviar_subsistema(area)
{
	
	if((area!='') && (document.forms['espec_tec'].emissao.checked))
	{
		document.forms['espec_tec'].target='_self';
		document.forms['espec_tec'].action='<?= $PHP_SELF ?>';
		document.forms['espec_tec'].relatorio.value='especificacao_tecnica_subsistema';
		document.forms['espec_tec'].acao.value='salvar';
		document.forms['espec_tec'].submit();
		
	}
	else
	{
		if(area!='')
		{
			document.forms['espec_tec'].acao.value='';
			document.forms['espec_tec'].relatorio.value='especificacao_tecnica_subsistema';
			document.forms['espec_tec'].action='rel_espec_tec_disp_subsistema.php';
			document.forms['espec_tec'].submit();
		}
	}

}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body onLoad="maximiza()" onResize="maximiza()" class="body">

<center>
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9" class="menu_superior"> <?php //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php //menu() ?></td>
      </tr>
<tr>

<td>
<form name="espec_tec" method="post" action="" target="_blank">
<?php

// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
// para eventual Atualização


  ?>
<!-- MODIFICAÇÃO AQUI -->

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" cellpadding="0" cellspacing="0" border=0 class="cabecalho_tabela">
    <tr>
      <td width="26%" class="cabecalho_tabela"><div align="center">TAG</div></td>
      <td width="30%" class="cabecalho_tabela">SERVIÇO</td>
      <td width="35%" class="cabecalho_tabela"><div align="center">COMPONENTE</div></td>
      <!-- <td width="8%" class="cabecalho_tabela">V</td> -->
	  <td width="7%" class="cabecalho_tabela">IMPRIMIR</td>
      <td width="2%" class="cabecalho_tabela"> </td>
    </tr>
</table>

</div>

<div id="tbbody" style="position:relative; width:100%; height:350px; z-index:2; overflow-y:scroll; overflow-x:hidden;">  
<table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border=0>
	<?php

		if($_POST["id_subsistema"]!='')
		{
			$filtro = "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
		}
		else
		{
			$filtro = "";
		}
		
		if($_POST["ds_dispositivo"])
		{
			$filtro .= "AND dispositivos.ds_dispositivo = '".$_POST["ds_dispositivo"]."' ";
		}
				
		
		$sql = "SELECT * FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.componentes, Projetos.especificacao_tecnica, Projetos.tipo, Projetos.processo, Projetos.funcao, Projetos.locais ";
		$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
		$sql .= "AND componentes.id_malha = malhas.id_malha ";
		$sql .= "AND componentes.id_local = locais.id_local ";
		$sql .= "AND malhas.id_processo = processo.id_processo ";
		$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
		$sql .= "AND subsistema.id_area = area.id_area ";
		//$sql .= "AND area.id_area = '".$_POST["id_area"]."' ";
		$sql .= "AND area.id_os = '" .$_SESSION["id_os"]. "' ";
		$sql .= $filtro;
		$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
		$sql .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
		$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
		$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
		//$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
		$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		
		$sql .= "ORDER BY nr_area, processo, dispositivo, nr_malha, nr_sequencia ";
		
		$registro = mysql_query($sql,$db->conexao) or die($sql);
		
		$count = mysql_num_rows($registro);
		
		$i = 0;
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

			  <td width="25%" class="corpo_tabela"><div align="center"><?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $componentes["nr_malha"] . $modificador ?></div>
			    <div align="center"></div></td>			
			  <td width="29%" class="corpo_tabela"><div align="center"><?= $componentes["ds_servico"] ?>
			    </div></td>
			  <td width="34%" class="corpo_tabela"><div align="center"><?= $componentes["ds_dispositivo"] ."  " . $componentes["ds_funcao"] . " " . $componentes["ds_tipo"]    ?></div><div align="center"></div></td>
			  <td width="6%" class="corpo_tabela"><input type="checkbox" name="<?= $componentes["id_componente"] ?>" value="1"></td>
			</tr>
			<?php
		}
		
		
	?>
  </table>  
</div>
<div id="div" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
  <table width="100%" class="corpo_tabela">
    <tr>
      <td class="label1"> </td>
      <td colspan="2" class="label1">REGS: <?= $count ?></td>
      </tr>
    <tr>
      <td width="4" class="label1"> </td>
      <td width="66" class="label1">
	  <input name="acao" type="hidden" class="btn" value="salvar">
	  <input name="emissao" type="hidden" class="btn" value="<?= $_POST["emissao"] ?>">
	  <input name="relatorio" type="hidden" class="btn" value="<?= $_POST["relatorio"] ?>">
	  <input name="id_subsistema" type="hidden" class="btn" value="<?= $_POST["id_subsistema"] ?>">
	  <input name="disciplina" type="hidden" class="btn" value="<?= $_POST["disciplina"] ?>">
	  <input name="numeros_interno" type="hidden" class="btn" value="<?= $_POST["numeros_interno"] ?>">
	  <input name="numero_cliente" type="hidden" class="btn" value="<?= $_POST["numero_cliente"] ?>">
	  <input name="versao_documento" type="hidden" class="btn" value="<?= $_POST["versao_documento"] ?>">
	  <input name="alteracao" type="hidden" class="btn" value="<?= $_POST["alteracao"] ?>">
	  <input name="executante" type="hidden" class="btn" value="<?= $_POST["executante"] ?>">
	  <input name="verificador" type="hidden" class="btn" value="<?= $_POST["verificador"] ?>">
	  <input name="aprovador" type="hidden" class="btn" value="<?= $_POST["aprovador"] ?>">
	  <input name="button" type="button" class="btn" value="IMPRIMIR" onclick="enviar_subsistema(document.forms[0].id_subsistema.value)"></td>
      <td width="907" class="label1"><input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='relatorio_especificacao_tecnica.php';">
        <input name="marcar" type="button" class="btn" id="botao" onclick="checkbox('espec_tec','check')" value="Marcar Todos">
        <input name="desmarcar" type="button" class="btn" id="desmarcar"onclick="checkbox('espec_tec','uncheck')" value="Desmarcar Todos"></td>
    </tr>
  </table>
</div>
</form>

</td>
</tr>
</table>
</td>
</tr>
</table>
<?php
	if($envio_rel)
	{
		$envio_rel = false;
		
		if($_POST["relatorio"]=='especificacao_tecnica_subsistema')
		{
			?>	
				<script>	
				enviar_subsistema('<?= $_POST["id_subsistema"] ?>');
				</script>
			<?php			
		}

	}
?>
</center>
</body>
</html>
<?php
	$db->fecha_db();
?>

