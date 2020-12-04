<?php

/*

		Formul�rio de Acompanhamento de Projetos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/acompanhamento.php
		
		data de cria��o: 13/04/2007
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
		
*/	

session_start();


if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php?pagina=" . $_SERVER['PHP_SELF']);
	exit;
}



//Debug
//error_reporting(2);

include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");
require ('../includes/xajax/xajax.inc.php');

$db = new banco_dados;

function inserir($dados_form)
{

	$resposta = new xajaxResponse();

	$db = new banco_dados;

	//Checa se os campos foram selecionados / preenchidos.
	if($dados_form["id_os"] && $dados_form["id_entrada"] && $dados_form["saida"])
	{
	
		//Checa se j� existe um acompanhamento para tal OS
		$sql_verifica = "SELECT * FROM Projetos.acompanhamento ";
		$sql_verifica .= "WHERE acompanhamento.id_os = '" . $dados_form["id_os"] . "' ";
		
		$cont_verifica = $db->select($sql_verifica,'MYSQL');
		
		//Caso n�o exista
		if($db->numero_registros==0)
		{
	
			//Insere um novo acompanhamento
			$sql_inserir = "INSERT INTO Projetos.acompanhamento (id_os, id_funcionario, data) VALUES(";
			$sql_inserir .= "'" . $dados_form["id_os"] . "', ";
			$sql_inserir .= "'" . $_SESSION["id_funcionario"] . "', ";
			$sql_inserir .= "'" . date("Y-m-d") . "') ";
			
			$db->insert($sql_inserir,'MYSQL');
	
			//Pega o ID da inser��o anterior, para utilizar na inser��o das sa�das, abaixo.
			$id_acompanhamento = $db->insert_id;
	
		}	
		else
		{
			
			$reg_verifica = mysqli_fetch_array($cont_verifica);
			//Pega o ID da sele��o anterior, para utilizar na inser��o das sa�das, abaixo.			
			$id_acompanhamento = $reg_verifica["id_acompanhamento"];
		}
		
		//Remove a sa�da existente 
		$sql_remover_saida = "DELETE FROM Projetos.acompanhamento_saidas ";
		$sql_remover_saida .= "WHERE acompanhamento_saidas.id_acompanhamento = '" . $id_acompanhamento . "' ";
		$sql_remover_saida .= "AND acompanhamento_saidas.id_entrada = '" . $dados_form["id_entrada"] . "' ";
		
		$db->delete($sql_remover_saida,'MYSQL');
		
		//Insere a sa�da
		$sql_inserir_saida = "INSERT INTO Projetos.acompanhamento_saidas (id_acompanhamento, id_entrada, saida) VALUES(";
		$sql_inserir_saida .= "'" . $id_acompanhamento . "', ";
		$sql_inserir_saida .= "'" . $dados_form["id_entrada"] . "', ";
		$sql_inserir_saida .= "'" . $dados_form["saida"] . "') ";
	
		$cont_inserir_saida = $db->insert($sql_inserir_saida,'MYSQL');
	
		//Se a inser��o foi realizada com sucesso
		if($cont_inserir_saida)
		{
	
			$resposta->addAlert("Descri��o inserida com sucesso.");
			
			//Se for o �ltimo item das entradas, volta para o primeiro item, sen�o, avan�a um item.
			$jscript = "
			if(xajax.$('id_entrada').selectedIndex==xajax.$('id_entrada').length-1)
			{
				xajax.$('id_os').onchange();
			}
			else
			{
				xajax.$('btnproximo').onclick();
			}
			";
			
			$resposta->addScript($jscript);
			
			/*
			$resposta->addScript("xajax.$('btnproximo').onclick();");
			$resposta->addScript("xajax.$('id_os').onchange();");
			*/
		}
	}
	else
	{
	
		//Se os campos n�o foram selecionados / preenchidos corretamente
		$resposta->addAlert("� necess�rio selecionar uma OS, uma Entrada e preencher a Sa�da!");
		
	}
	
	return $resposta;
}


function atualizatabela($id_os)
{
	
	//Rotina para atualizar a tabela via AJAX
	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($id_os)
	{
		//Mostra a tabela
		$resposta->addAssign("tabela_tudo","style.visibility","visible");
	}
	
	//Busca os dados sobre a OS
	$sql_dados = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".Funcionarios, ".DATABASE.".contatos ";
	$sql_dados .= "WHERE OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql_dados .= "AND OS.id_cod_coord = Funcionarios.id_funcionario ";
	$sql_dados .= "AND OS.id_cod_resp = contatos.id_contato ";
	$sql_dados .= "AND OS.id_os = '" . $id_os . "' ";
	
	$cont_dados = $db->select($sql_dados,'MYSQL');
	
	$reg_dados = mysqli_fetch_array($cont_dados);
	
	//Assigna os dados nos campos
	$resposta->addAssign("tbl_cli","innerHTML",$reg_dados["empresa"]);
	$resposta->addAssign("tbl_proj","innerHTML",$reg_dados["descricao"]);
	$resposta->addAssign("tbl_iniproj","innerHTML",mysql_php($reg_dados["data_inicio"]));
	$resposta->addAssign("tbl_termproj","innerHTML",mysql_php($reg_dados["data_fim"]));
	$resposta->addAssign("tbl_coordcli","innerHTML",$reg_dados["nome_contato"]);
	$resposta->addAssign("tbl_coorddvm","innerHTML",$reg_dados["funcionario"]);	

	
	$sql_acompanhamento = "SELECT * FROM Projetos.acompanhamento, Projetos.acompanhamento_entradas, Projetos.acompanhamento_saidas ";
	$sql_acompanhamento .= "WHERE acompanhamento.id_acompanhamento = acompanhamento_saidas.id_acompanhamento ";
	$sql_acompanhamento .= "AND acompanhamento_saidas.id_entrada = acompanhamento_entradas.id_entrada ";
//	$sql_acompanhamento .= "AND acompanhamento.id_funcionario = ".DATABASE.".Funcionarios.id_funcionario ";
	$sql_acompanhamento .= "AND acompanhamento.id_os = '" . $id_os . "' ";
	$sql_acompanhamento .= "ORDER BY acompanhamento_entradas.id_tipo_entrada, acompanhamento_entradas.entrada ASC ";

	$cont_acompanhamento = $db->select($sql_acompanhamento,'MYSQL');

	while($reg_acompanhamento = mysqli_fetch_array($cont_acompanhamento))
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
	
	
	//Forma a tabela
	$conteudo .= "<div id=\"". $cont_acompanhamento["id_acompanhamento"]."\"  class=\"cell\" style=\"background-color:". $cor ."; width:100%;\" onMouseOver=\"setPointerDiv(this, 1, 'over', '". $cor ."', '#BECCD9', '#FFCC99');\" onMouseOut=\"setPointerDiv(this, 1, 'out', '". $cor . "', '#BECCD9', '#FFCC99');\">";	
	
	$conteudo .= "<div class=\"tabela_celulas\" style=\"width:50%; \">&nbsp;" . $reg_acompanhamento["entrada"] . "</div>";	
	$conteudo .= "<div class=\"tabela_celulas\" style=\"width:50%; \">&nbsp;" . $reg_acompanhamento["saida"] . "</div>";	
	
	$conteudo .= "<div id=\"separador\" style=\"clear:right; width:1%;font-family:Arial, Helvetica, sans-serif; font-size:11px; \">&nbsp;</div>";			

	$conteudo .= "</div>";
	
	}

	//$conteudo .= "</div>";

	$resposta->addAssign("tabela_acompanhamento","innerHTML", $conteudo);
	//$resposta->addScript("document.getElementById('telefones_procura').focus();");

	//$resposta->addAlert($conteudo);

	return $resposta;
	

}

function osSeleciona($combo, $valor)
{

	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
//	$array_valor = explode("#", $valor);
//	$id_os = $array_valor[0];
	
	switch($combo)
	{
	
		case "os":
		
		//Se for o combo de OS
		
		//Atribue o id_os com o valor passado como par�metro
		$id_os = $valor;
		
		//Seleciona os dados da OS selecionada pelo usu�rio
		$sql_os = "SELECT OS, ordem_servico_cliente, descricao FROM ".DATABASE.".OS ";
		$sql_os .= "WHERE OS.id_os = '" . $id_os . "' ";
		
		$cont_os = $db->select($sql_os,'MYSQL');		
		
		$reg_os = mysqli_fetch_array($cont_os);
		
		//Se o texto da descri��o for maior que 20
		if(strlen($reg_os["descricao"])>20)
		{
			//Corta a descri��o e coloca pontos no final
			$descricao = substr($reg_os["descricao"],0,10) . "...";
		}
		else
		{	
			//Sen�o, apenas corta a descri��o
			$descricao = substr($reg_os["descricao"],0,10);		
		}
		
		//Altera o conte�do do div com as informa��es da OS selecionada pelo usu�rio
		$resposta->addAssign("label_os","innerHTML",$reg_os["os"] . " - " . $reg_os["ordem_servico_cliente"] . " - " . $descricao);

		//Torna o <select> da OS invis�vel, deixando apenas o div para a visualiza��o do usu�rio
		$resposta->addAssign("id_os", "style.visibility", "hidden");
		
		//Seleciona a primeira entrada do combo de entradas
		$resposta->addScript("xajax.$('id_entrada').selectedIndex=0;");
		
		//Dispara o evento do onChange do combo de entradas
		$resposta->addScript("xajax.$('id_entrada').onchange();");

		break;
	
	
		case "entrada":
		
		//Se for o combo de entrada
		
		
		//Seleciona os dados da entrada selecionada pelo usu�rio
		$sql_entradas = "SELECT * FROM Projetos.acompanhamento_entradas ";
		$sql_entradas .= "WHERE acompanhamento_entradas.id_entrada = '" . $valor . "' ";
		
		$cont_entradas = $db->select($sql_entradas,'MYSQL');		
		
		$reg_entradas = mysqli_fetch_array($cont_entradas);
		
		//Se o texto da entrada for maior que 80		
		if(strlen($reg_entradas["entrada"])>80)
		{
		
			//Corta a entrada e coloca pontos no final		
			$entrada = substr($reg_entradas["entrada"],0,80) . "...";
					
		}
		else
		{
			//Sen�o, apenas corta a entrada
			$entrada = substr($reg_entradas["entrada"],0,80);
		
		}
		
		//Altera o conte�do do div com as informa��es da entrada selecionada pelo usu�rio		
		$resposta->addAssign("label_entrada","innerHTML",$entrada);
		
		//Torna o <select> da entrada invis�vel, deixando apenas o div para a visualiza��o do usu�rio		
		$resposta->addAssign("id_entrada","style.visibility","hidden");
		
		
		break;
	
		
	}

	return $resposta;
}

function preencheCombo($tipo)
{

	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
		
	$sql_acompanhamento = "SELECT * FROM Projetos.acompanhamento_entradas ";		
	$sql_acompanhamento .= "WHERE acompanhamento_entradas.id_tipo_entrada = '" . $tipo . "' ";

	$cont_acompanhamento = $db->select($sql_acompanhamento,'MYSQL');
	
	//Zera o combo destino
	$resposta->addScript("combo_destino = document.getElementById('id_entrada');combo_destino.length='0';");
	$resposta->addAssign("label_entrada","innerHTML","SELECIONE");
	
	
//	$resposta->addScriptCall("limpa_combo('itens')");
	
	
		while($reg_acompanhamento = mysqli_fetch_array($cont_acompanhamento))
		{
		
		
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".substr($reg_acompanhamento["entrada"],0,130)."', '".$reg_acompanhamento["id_entrada"]."');");
		
		
		}
	
	return $resposta;

}

function mudaSaida($dados_form)
{

	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
//	sleep(10);
	
	//Pega a sa�da selecionada no combo
	$sql_saida = "SELECT * FROM Projetos.acompanhamento_saidas, Projetos.acompanhamento ";
	$sql_saida .= "WHERE acompanhamento_saidas.id_acompanhamento = acompanhamento.id_acompanhamento ";
	$sql_saida .= "AND acompanhamento_saidas.id_entrada = '" . $dados_form["id_entrada"] . "' ";
	$sql_saida .= "AND acompanhamento.id_os = '" . $dados_form["id_os"] . "' ";
	
	$cont_saida = $db->select($sql_saida,'MYSQL');

	$reg_saida = mysqli_fetch_array($cont_saida);

	//Se existir um registro de sa�da
	if($db->numero_registros>0)
	{
	
		//Seta o valor para o text input e desabilita a edi��o/inser��o
		$resposta->addAssign("saida","value",$reg_saida["saida"]);
		$resposta->addAssign("saida","disabled","true");
		$resposta->addAssign("btninserir", "disabled", "true");
	}
	else
	{

		//Verifica��o de altera��es n�o salvas
		if($dados_form["saida"])
		{
			//Confirma se o usu�rio deseja descartar as altera��es
			$jscript = "
			if(confirm('Deseja inserir as altera��es?'))
			{
				xajax.$('btninserir').onclick();
			}
			
			";

			$resposta->addScript($jscript);
		
		}


		//Limpa, habilita e joga o foco para o text input
		$resposta->addAssign("saida","value","");
		$resposta->addAssign("saida","disabled","");
		$resposta->addAssign("btninserir", "disabled", "");
		$resposta->addScript("xajax.$('saida').focus();");
	
	}

	//Atualiza a tabela
	$resposta->addScript("xajax_atualizatabela('" . $dados_form["id_os"] ."')");
	
	return $resposta;

}



$xajax = new xajax();

$xajax->setCharEncoding("utf-8");
$xajax->decodeUTF8InputOn();


//$xajax->registerFunction("atualizar");
//$xajax->registerFunction("editar");
//$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("osSeleciona");
$xajax->registerFunction("preencheCombo");
$xajax->registerFunction("inserir");
$xajax->registerFunction("mudaSaida");

$xajax->processRequests();


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<?php $xajax->printJavascript('../includes/xajax'); ?>

<title>::..  (ERP1-2 0 0 7)  - Acompanhamento de Projetos..::</title>
<link href="../classes/css_geral.css" rel="stylesheet" type="text/css" />

</head>


<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<body>

<script language="javascript">

function OS_dropdown(label, combo_ref)
{

combo = document.getElementsByName(combo_ref)[0];


var iTop = 0;
var iLeft = 0;

tamanho_left = combo.offsetWidth - label.offsetWidth;

//Pega o Top/Left do label
iTop = label.offsetTop;
iLeft = label.offsetLeft;

novo_top = iTop + 16 + 'px';
novo_left = '50px';

//if(combo.offsetWidth>
novo_width = label.offsetLeft + label.offsetWidth + 50;


combo.style.top = novo_top;
combo.style.left = novo_left;
combo.style.width = novo_width;

if(combo.style.visibility=='hidden')
{
	combo.style.visibility='visible';
	combo.style.display = 'inline';
}
else
{
	combo.style.visibility='hidden';
	combo.style.display = 'none';
}

}


function disabilita_inputs()
{
	

//	saida_input = xajax.$('saida');
	btn_input = xajax.$('btninserir');
	
//	saida_input.disabled = true;
	btn_input.disabled = true;

}

function irAnterior()
{
//Fun��o para navegar para o registro anterior

	//Se o item atual do combo de entrada n�o for o primeiro
	if(xajax.$('id_entrada').selectedIndex!=0)
	{
		//Desabilita a edi��o/Inclusão
		disabilita_inputs();
		//Seleciona a entrada anterior
		xajax.$('id_entrada').selectedIndex=xajax.$('id_entrada').options.selectedIndex-1;
		//Dispara o evento onChange do combo de entrada
		xajax.$('id_entrada').onchange();
	}

}

function irProximo()
{

//Fun��o para navegar para o registro pr�ximo

	//Se o item atual do combo de entrada n�o for o �ltimo
	if(xajax.$('id_entrada').selectedIndex!=(xajax.$('id_entrada').length-1))
	{
		//Desabilita a edi��o/Inclusão
		disabilita_inputs();
		//Seleciona a pr�xima entrada
		xajax.$('id_entrada').selectedIndex=xajax.$('id_entrada').options.selectedIndex+1;
		//Dispara o evento onChange do combo de entrada
		xajax.$('id_entrada').onchange();
	}

}


</script>

<div style="width:100%;" align="center">

<form name="frm_acompanhamento" id="frm_acompanhamento" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">

	<table width="760" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><table width="760" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="119" bgcolor="#e6e6e6" class="altura_linha-10px borda_esquerda">&nbsp;</td>
			<td width="57" bgcolor="#1B4470" class="altura_linha-10px borda_esquerda">&nbsp;</td>
			<td width="608" bgcolor="#0099CC" class="altura_linha-10px">&nbsp;</td>
			<td width="16" bgcolor="#0099CC" class="altura_linha-10px">&nbsp;</td>
		  </tr>
		  <tr>
			<td colspan="2" rowspan="2"><img src="../images/logo_dvm.jpg" width="175" height="40" /></td>
			<td class="margem_direita fonte_14"> <p><img src="../images/setas.gif" width="22" height="13" />ACOMPANHAMENTO DE PROJETOS  </p></td>
			<td bgcolor="#0099CC">&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="baseline"><div align="right" class="fonte_12_az"><img src="../images/setas_menor.gif" width="18" height="10" />
                  <?= $_SESSION["login"] ?>
  &nbsp;&nbsp;<img src="../images/setas_menor.gif" width="18" height="10" /><a href="#" class="fonte_12_az" onClick="javascript:location.href='../inicio.php';">Inicio</a>&nbsp;&nbsp;<img src="../images/setas_menor.gif" width="18" height="10" /><a href="../logout.php" class="fonte_12_az">Sair</a>&nbsp;&nbsp; </div></td>
			<td bgcolor="#0099CC">&nbsp;</td>
		  </tr>
		  <tr>
			<td bgcolor="#E6E6E6" class="altura_linha-6px borda_esquerda">&nbsp;</td>
			<td bgcolor="#1B4470" class="altura_linha-6px borda_esquerda">&nbsp;</td>
			<td bgcolor="#0099CC" class="altura_linha-6px">&nbsp;</td>
			<td bgcolor="#0099CC" class="altura_linha-6px">&nbsp;</td>
		  </tr>
		</table>
		<div id="comum">
		  <table width="760" border="0" cellspacing="0" cellpadding="0">
			
			<tr>
			  <td width="106" rowspan="2" bgcolor="#999999" class="borda_alto"><input name="btninserir" type="button" class="botao_chumbo" id="btninserir" onClick="xajax_inserir(xajax.getFormValues('frm_acompanhamento'));" value="Inserir" tabindex="4" /></td>
			  <td width="10" rowspan="2"><img src="../images/pt-bt.gif" width="10" height="40" /></td>
			  <td width="58">&nbsp;</td>
			  <td width="83">&nbsp;</td>
			  <td colspan="2">&nbsp;</td>
			  <td width="78">&nbsp;</td>
			  <td width="206">&nbsp;</td>
			  <td width="119">&nbsp;</td>
			  <td width="16" bgcolor="#0099CC">&nbsp;</td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td colspan="6" rowspan="3" valign="top" class="fonte_descricao_campos"><table width="100%" border="0">
                  <tr>
                    <td width="43%" class="fonte_descricao_campos">OS</td>
                    <td colspan="2">&nbsp;</td>
                  </tr>
                  <tr>
                    <td>
					<div class="label_os_classe" id="label_os" style="position:relative; cursor:pointer; width:215px; background-image:url(../images/dropdown_bullet.gif); background-position:right; background-repeat:no-repeat; text-align:left;" onClick="OS_dropdown(this, 'id_os');">SELECIONE</div>
					<select name="id_os" id="id_os" class="caixa" size="10" style="position:absolute;visibility:hidden; display:none; z-index:1;" onChange="xajax_osSeleciona('os', this.options[this.options.selectedIndex].value);xajax_mudaSaida(xajax.getFormValues('frm_acompanhamento'));">
<!--					<option value="">SELECIONE</option> -->
					<?php
					
					$sql_os = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
					$sql_os .= "WHERE OS.id_os_status = ordem_servico_status.id_os_status ";
					$sql_os .= "ORDER BY os.os ";
					
					$cont_os = $db->select($sql_os,'MYSQL');
					
					while($reg_os = mysqli_fetch_array($cont_os))
					{
						?>
						<option value="<?= $reg_os["id_os"] ?>"><?= $reg_os["os"] . " - " . $reg_os["descricao"] ?></option>
						<?php
					}
					?>
					</select>					</td>
                    <td width="27%"><input name="tipo" type="radio" checked onClick="xajax_preencheCombo('1');xajax_mudaSaida(xajax.getFormValues('frm_acompanhamento'));xajax.$('id_os').onchange();">
                      <span class="fonte_descricao_campos">An�lise cr�tica inicial</span></td>
                    <td width="30%"><input type="radio" name="tipo" onClick="xajax_preencheCombo('2');xajax_mudaSaida(xajax.getFormValues('frm_acompanhamento'));xajax.$('id_os').onchange();"><span class="fonte_descricao_campos">An�lise cr�tica final</span></td>
                  </tr>
                </table></td>
			  <td width="16" bgcolor="#0099CC">&nbsp;</td>
			</tr>
			
			<tr>
			  <td rowspan="2" bgcolor="#999999" class="borda_alto"><input name="btnvoltar" type="button" class="botao_chumbo" id="btnvoltar" onClick="javascript:location.href='../inicio.php';" value="Voltar" /></td>
			  <td rowspan="2"><img src="../images/pt-bt.gif" width="10" height="40" /></td>
			  <td>&nbsp;</td>
			  <td width="16" bgcolor="#0099CC">&nbsp;</td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td width="16" bgcolor="#0099CC">&nbsp;</td>
			</tr>
			
			<tr>
			  <td bgcolor="#999999" class="fundo_cinza">&nbsp;</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			  <td colspan="6" valign="top" class="fonte_descricao_campos">
			  <table width="100%" border="0">
                <tr>
                  <td><span class="fonte_descricao_campos">Entradas:</span>
					  <div id="div_entradas">
					  <div class="label_os_classe" id="label_entrada" style="position:relative; cursor:pointer; width:500px; background-image:url(../images/dropdown_bullet.gif); background-position:right; background-repeat:no-repeat; text-align:left;" onClick="OS_dropdown(this, 'id_entrada');">SELECIONE</div>
					  <select name="id_entrada" id="id_entrada" class="caixa" size="10" style="position:absolute;visibility:hidden; display:none; z-index:1;" onChange="xajax_osSeleciona('entrada', this.options[this.options.selectedIndex].value);disabilita_inputs();xajax_mudaSaida(xajax.getFormValues('frm_acompanhamento'));">
<!--					  <option value="">SELECIONE</option> -->
					  <?php
					  
					  
					  $sql_entradas = "SELECT * FROM Projetos.acompanhamento_entradas ";
					  $sql_entradas .= "WHERE acompanhamento_entradas.id_tipo_entrada = '1' ";
					  
					  $cont_entradas = $db->select($sql_entradas,'MYSQL');
					  
					  while($reg_entradas = mysqli_fetch_array($cont_entradas))
					  {
						?>
						<option value="<?= $reg_entradas["id_entrada"] ?>">
						<?= substr($reg_entradas["entrada"],0,130) ?>
						</option>
						<?php
					  }
					  ?>
					  </select>
					  </div>				</td>
                  <td>&nbsp;</td>
                </tr>
              </table></td>
			  <td bgcolor="#0099CC">&nbsp;</td>
			  </tr>
			<tr>
			  <td bgcolor="#999999" class="fundo_cinza">&nbsp;</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			  <td colspan="6" valign="top" class="fonte_descricao_campos"><span class="fonte_descricao_campos">Sa�das:</span><div id="div_saida"><textarea name="saida" cols="100" rows="4" class="caixa" id="saida" tabindex="3"></textarea></div>
			  <input name="tmp_saida" type="hidden" value=""></td>
			  <td bgcolor="#0099CC">&nbsp;</td>
			  </tr>
			<tr>
			  <td bgcolor="#999999" class="fundo_cinza">&nbsp;</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			  <td colspan="6" valign="top"><input name="btnanterior" type="button" class="botao_cinza" id="btnanterior" onClick="irAnterior();" value="Anterior">
			  <input name="btnproximo" type="button" class="botao_cinza" id="btnproximo" onClick="irProximo();" value="Pr�ximo"></td>
			  <td bgcolor="#0099CC">&nbsp;</td>
			  </tr>
			<tr>
			  <td bgcolor="#999999" class="fundo_cinza">&nbsp;</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			  <td colspan="6" valign="top" class="fonte_descricao_campos">&nbsp;</td>
			  <td bgcolor="#0099CC">&nbsp;</td>
			  </tr>
			<tr>
			  <td colspan="2" bgcolor="#E6E6E6" class="altura_linha-6px borda_alto borda_esquerda">&nbsp;</td>
			  <td bgcolor="#1B4470" class="altura_linha-6px borda_alto borda_esquerda ">&nbsp;</td>
			  <td colspan="6" bgcolor="#0099CC" class="altura_linha-6px borda_alto">&nbsp;</td>
			  <td width="16" bgcolor="#0099CC" class="altura_linha-6px">&nbsp;</td>
			</tr>
		  </table>
		</div>
		  
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><!--
					<div class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:10%; background-color:#999999;">Ramais</div>
					<div class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:26%; background-color:#999999;">Funcion�rio</div>
					<div class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:25%; background-color:#999999;">E-mail</div> 
					<div class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:20%; background-color:#999999;">local Trabalho</div>
					<div class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:12%; background-color:#999999;">celular</div>
					<div align="center" class="altura_linha-26px borda_alto borda_esquerda fonte_12" style="float:left; width:5%; background-color:#999999;">E</div>
					<div class="altura_linha-26px borda_alto fonte_12" style="float:left; width:2%; background-color:#999999;">&nbsp;</div>    
					-->
					&nbsp;
				</td>
			</tr>
			<tr>
			  <td colspan="7" bgcolor="#FFFFFF" class="">
			  <div id="tabela_tudo" style="visibility:hidden;">
				  <span class="fonte_descricao_campos" style="width:60%;">Cliente:</span><div id="tbl_cli" class="fonte_11" style="width:60%; float:left;">NOME DE CLIENTE</div>
				  <span class="fonte_descricao_campos" style="width:20%;">In�cio:</span>
				  <div id="tbl_iniproj" class="fonte_11" style="float:left;width:20%;">00/00/0000</div>			  
				  <span class="fonte_descricao_campos" style="width:20%;">T�rmino:</span>
				  <div id="tbl_termproj" class="fonte_11" style="float:left;width:20%;">00/00/0000</div>			  
				  <div>&nbsp;</div>				  
				  <span class="fonte_descricao_campos" style="width:60%;">Projeto:</span><div id="tbl_proj" class="fonte_11" style="width:60%; float:left;">NOME DE PROJETO</div>
				  <span class="fonte_descricao_campos" style="width:20%;">Coordenador Cliente:</span><div id="tbl_coordcli" class="fonte_11" style="width:20%; float:left;">NOME DO COORDENADOR CLIENTE</div>			  				  
				  <span class="fonte_descricao_campos" style="width:20%;">Coordenador Devemada:</span><div id="tbl_coorddvm" class="fonte_11" style="width:20%; float:left;">NOME DO COORDENADOR DEVEMADA</div>			  				  				  

				  <div>&nbsp;</div>

				  <div class="altura_linha-26px borda_esquerda borda_alto fonte_12" id="cabecalho_entrada" style="width:50%;float:left;background-color:#999999;">ENTRADA</div><div class="altura_linha-26px borda_esquerda borda_alto fonte_12" id="cabecalho_saida" style="width:50%;float:left;background-color:#999999;">SA�DA</div>
				  <div id="tabela_acompanhamento" style="overflow:auto; height:100px;">&nbsp;</div>
			  </div>
			  </td>
			</tr>
		  </table>
		</td>
	  </tr>
	</table>
</form>

</div>

</body>
</html>
