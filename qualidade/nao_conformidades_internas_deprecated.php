<?php 
/*
	  Formul�rio de N�o Conformidades
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../qualidade/nao_conformidades_internas.php
	  
	  data de cria��o: 24/03/2014
	  
	  Versão 0 --> VERSÃO INICIAL
	  Versão 1 --> Altera�ao no sistema de inserir, envio de e-mail, impress�o (#597) - 30/06/2014 - Carlos Abreu
	  Deprecated --> N�o apagar do ambiente oficial, Clayton e Hugo Castilho ainda usam
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."phpmailer/class.phpmailer.php");

$_SESSION["id_sub_modulo"] = 325;

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(325))
{
	die("ACESSO PROIBIDO!");
}

function ver_status($id_plano)
{
	//verifica os status das acoes complementares
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos ";
	$sql .= "WHERE id_plano_acao = '".$id_plano."' ";
	$sql .= "AND status_plano_acao < 2 ";
	
	$cont = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	if($db->numero_registros>0)
	{
		return false;	
	}
	else
	{
		return true;
	}	

}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("codigo","value", "NC-".date('YmdHi'));

	$resposta->addAssign("originador", "value" ,$_SESSION["nome_usuario"]);
	
	$resposta->addAssign("id_originador", "value", $_SESSION["id_funcionario"]);

	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	
	//FAZ O SELECT
	$res = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$cont = mysqli_fetch_assoc($res);
	
	$resposta->addAssign("setor","value", $cont["setor"]);
	
	$resposta->addAssign("id_setor","value", $cont["id_setor"]);
	
	$resposta->addAssign("data","value", date('d/m/Y'));
	
	$resposta->addAssign("div_anexos","innerHTML","<input type=\"file\" name=\"input_1\" id=\"input_1\">");
	
	$resposta->addScript("document.getElementById('div_anex').style.visibility = 'hidden';");
	
	$resposta->addAssign("div_arquivos","innerHTML","");
	
	$resposta->addScript("document.getElementById('div_arq').style.visibility = 'hidden';");

	$resposta->addScript("document.getElementById('status_0').disabled=true;");
	
	$resposta->addScript("document.getElementById('status_1').disabled=true;");

	$resposta->addScript("document.getElementById('status_2').disabled=true;");

	$resposta->addScript("document.getElementById('procedente_0').disabled=true;");
	
	$resposta->addScript("document.getElementById('procedente_1').disabled=true;");

	$resposta->addScript("document.getElementById('pac_0').disabled=true;");
	
	$resposta->addScript("document.getElementById('pac_1').disabled=true;");
	
	$resposta->addScript("document.getElementById('id_plano').disabled=true;");
		
	$resposta->addScript("document.getElementById('desc_eficacia').disabled=true;");
	
	$resposta->addScript("document.getElementById('desc_evidencia').disabled=true;");
	
	$resposta->addScript("document.getElementById('disciplina').disabled=false;document.getElementById('cliente').selectedIndex=0;document.getElementById('cliente').disabled=true';");
	
	//$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm',true),0); ");
	
	$resposta->addEvent("btnenviar","onclick","xajax_insere(xajax.getFormValues('frm',true),1); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('nao_conformidades_internas',$resposta);
	
	switch($dados_form["filtro"])
	{
		//geral
		case 0:
			$filtro = "";
		break;
		
		//pendentes
		case 1:
			//$filtro1 = " >= '".date('Y-m-d')."' ";
			$filtro = "AND nao_conformidades_deprecated.status = 0 ";
		break;
		
		//em an�lise
		case 2:
			$filtro1 = "AND nao_conformidades_deprecated.data_criacao >= '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
			$filtro = "AND nao_conformidades_deprecated.status = 1 ";
		break;
		
		//atrasados
		case 3:
			$filtro1 = "AND nao_conformidades_deprecated.data_criacao < '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
			$filtro = "AND nao_conformidades_deprecated.status = 1 ";
		break;
		
		//encerrados
		case 4:
			$filtro = "AND nao_conformidades_deprecated.status = 2 ";
		break;		
	}
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".nao_conformidades_deprecated ";
	$sql .= "LEFT JOIN ".DATABASE.".OS ON (nao_conformidades_deprecated.id_os = OS.id_os) ";
	$sql .= "WHERE nao_conformidades_deprecated.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades_deprecated.id_funcionario_criador = funcionarios.id_funcionario ";
	
	$sql .= $filtro;
	$sql .= $filtro1;
	$sql .= "ORDER BY nao_conformidades_deprecated.data_criacao ";

	//FAZ O SELECT
	$cont = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$conteudo = "";
	
	$header = "<div class='auto_lista' style='overflow:auto;height:400px;'><table id=\"tbl1\" class=\"auto_lista\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<th width=\"100\" type=\"ro\">".$campos[3]."</th>";
	$header .= "<th width=\"70\" type=\"ro\">".$campos[6]."</th>";
	$header .= "<th width=\"85\" type=\"ro\">".$campos[2]."</th>";
	$header .= "<th width=\"285\" type=\"ro\">".$campos[4]."</th>";
	$header .= "<th width=\"100\" type=\"ro\">".$campos[9]."</th>";
	$header .= "<th width=\"100\" type=\"ro\">".$campos[10]."</th>";
	$header .= "<th width=\"50\" align=\"center\" type=\"ro\">".$campos[14]."</th>";
	$header .= "<th width=\"70\" type=\"ro\">".$campos[15]."</th>";
	$header .= "<th width=\"100\" type=\"ro\">".$campos[17]."</th>";
	$header .= "<th width=\"30\" type=\"ro\">I</th>";
	$header .= "<th width=\"30\" type=\"ro\">D</th>";
	$header .= "</tr>";
	
	$footer = "</table></div>";
	
	$chars = array("'","\"",")","(","\\","/");
	
	while($regs = mysqli_fetch_assoc($cont))
	{	
		//pega a data mais distante
		$sql = "SELECT * FROM ".DATABASE.".planos_acoes  ";
		$sql .= "WHERE planos_acoes.id_plano_acao = '".$regs["id_plano_acao"]."' ";
		$sql .= "AND planos_acoes.plano_acao_delete = 0 ";
		
		//FAZ O SELECT
		$cont2 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs2 = mysqli_fetch_assoc($cont2);
		
		$title = "";
		
		if($regs["status"]==0)//pendente
		{
			//led vm
			$img = "<img style=\"cursor:pointer;\" src=\"../imagens/led_vm.png\">";
			$title = "PENDENTE";
		}
		else
		{
			if($regs["status"]==2)//encerrada
			{
				//led vm
				$img = "<img style=\"cursor:pointer;\" src=\"../imagens/led_az.png\">";
				$title = "ENCERRADA";
			}
			else
			{	
				//em an�lise				
				if($regs["status"]==1 && $regs["data_criacao"]>=php_mysql(calcula_data(date('d/m/Y'),'sub','day',15)))
				{
					//led vd
					$img = "<img style=\"cursor:pointer;\" src=\"../imagens/led_vd.png\">";
					$title = "EM AN�LISE";
				}
				else
				{
					//atrasadas
					//led am
					$img = "<img style=\"cursor:pointer;\" title=\"ATRASADA\" src=\"../imagens/led_am.png\">";
					$title = "ATRASADA";
				}
				
			}
		}		
		
		$sql = "SELECT * FROM ".DATABASE.".setores  ";
		$sql .= "WHERE setores.id_setor = '".$regs["id_disciplina"]."' ";
		
		//FAZ O SELECT
		$cont1 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = mysqli_fetch_assoc($cont1);
		
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade  ";
		$sql .= "WHERE empresas.id_empresa_erp = '".$regs["id_cliente"]."' ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		
		//FAZ O SELECT
		$cont3 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs3 = mysqli_fetch_assoc($cont3);
		
		switch ($regs["procedente"])
		{
			case 1: 
				$procedente = "SIM";
			break; 
			
			case 2: 
				$procedente = "N�O";
			break;
			
			default: $procedente = "";	
		}
		
		//permite excluir
		if(($regs["envio_email"]==0) || ($_SESSION["id_funcionario"]==871) || ($_SESSION["id_funcionario"]==576) || ($_SESSION["id_funcionario"]==1142))
		{
			$img_del = "<img style=\"cursor:pointer;\" src=\"../imagens/apagar.png\" onClick=\"javascript:if(apagar('".$regs["cod_nao_conformidade"] . "')){xajax_excluir('".$regs["id_nao_conformidade"]."','".$regs["cod_nao_conformidade"] . "');}\">";
		}
		else
		{
			$img_del = "&nbsp;";
		}
		
		
		if($regs["os"]!=0)
		{
			$os = sprintf("%010d",$regs["os"]);
		}
		else
		{
			$os = "N�O APLIC�VEL";	
		}

		$conteudo .= "<tr >";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$regs["cod_nao_conformidade"]."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".mysql_php($regs["data_criacao"])."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$os."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$regs["funcionario"]."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$regs1["setor"]."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$regs3["empresa"]." - ".$regs3["descricao"]."</label></td>";
		$conteudo .= "<td align=\"center\" title=\"".$title."\" >".$img."</td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$procedente."</label></td>";
		$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_nao_conformidade"]."')\">".$regs2["cod_plano_acao"]."</label></td>";
		//$conteudo .= "<td align=\"center\" >".$img_email."</td>";
		$conteudo .= "<td title=\"Imprimir\" ><img style=\"cursor:pointer;\" src=\"../imagens/impressora.png\" onclick=\"javascript:imprimir_rnc('".$regs["id_nao_conformidade"]."');\"></td>";
		$conteudo .= "<td title=\"Apagar\" >".$img_del."</td>";
		$conteudo .= "</tr>";
	}

	$resposta->addAssign("dv_rotinas","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('tbl1','250');");

	return $resposta;
}

//status 0 - salva / 1 - salva e envia
function insere($dados_form, $status = 0) 
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$validacao = false;	
	
	$validacao = $dados_form["codigo"]!='' &&
				 ($dados_form["disciplina"]!='0' || $dados_form["cliente"]!='0') &&
				 $dados_form["id_originador"]!='' &&
				 $dados_form["data"]!= '' &&
				 $dados_form["id_setor"]!= '' &&
				 $dados_form["desc_nc"]!= '' &&
				 $dados_form["desc_acao_imediata"]!= '' &&
				 $dados_form["desc_perdas"]!= '';	
	
	
	if($validacao)
	{	
		if(true || $conf->checa_permissao(8,$resposta))
		{	
			
			$isql = "INSERT INTO ".DATABASE.".nao_conformidades_deprecated ";
			$isql .= "(cod_nao_conformidade, id_os, data_criacao, id_funcionario_criador, id_setor, id_disciplina, id_cliente, desc_nao_conformidade, desc_acao_imediata, desc_perdas) "; //, desc_eficacia, status, procedente, plano_acao, id_plano_acao)
			$isql .= "VALUES ('" . $dados_form["codigo"] . "', ";
			$isql .= "'" . $dados_form["escolhaos"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
			$isql .= "'" . $dados_form["id_originador"] . "', ";
			$isql .= "'" . $dados_form["id_setor"] . "', ";
			$isql .= "'" . $dados_form["disciplina"] . "', ";
			$isql .= "'" . $dados_form["cliente"] . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_nc"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_acao_imediata"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_perdas"]) . "') ";

			//FAZ O INSERT
			$db->insert($isql,'MYSQL');
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro."-".$isql);				
			}
			else
			{
				$id_nc = $db->insert_id;
				
				if($status==1)
				{
					$resposta->addScript("xajax_email(".$id_nc.");");
				}
				
				//salva anexos
				//$resposta->addScript("anexos(".$id_nc.");");				
				$resposta->addAlert($msg[1]);				
			}
										
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
			
			$resposta->addScript("xajax_voltar();");							
		}
	}
	else
	{
		$resposta->addAlert('Os campos devem estar preenchidos');
	}
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".nao_conformidades_deprecated ";
	$sql .= "LEFT JOIN ".DATABASE.".OS ON (nao_conformidades_deprecated.id_os = OS.id_os) ";
	$sql .= "WHERE nao_conformidades_deprecated.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades_deprecated.id_funcionario_criador = funcionarios.id_funcionario ";
	$sql .= "AND nao_conformidades_deprecated.id_setor = setores.id_setor ";
	$sql .= "AND nao_conformidades_deprecated.id_nao_conformidade = '".$id."' ";
	
	//FAZ O SELECT
	$registro = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = mysqli_fetch_assoc($registro);
	
	//permite a edi��o dos campos aos funcionarios da SGI
	if($_SESSION["id_funcionario"]==6 || $_SESSION["id_funcionario"]==871 || $_SESSION["id_funcionario"]==978 || $_SESSION["id_funcionario"]==576 || $_SESSION["id_funcionario"]==1142)
	{
		$resposta->addScript("document.getElementById('desc_eficacia').disabled=false;");
		
		$resposta->addScript("document.getElementById('desc_evidencia').disabled=false;");
	
		$resposta->addScript("document.getElementById('div_anex').style.visibility = 'visible'");
	
		$resposta->addScript("document.getElementById('status_0').disabled=false;");
	
		$resposta->addScript("document.getElementById('status_0').disabled=false;");
		
		$resposta->addScript("document.getElementById('status_1').disabled=false;");

		$resposta->addScript("document.getElementById('status_2').disabled=false;");

		$resposta->addScript("document.getElementById('procedente_0').disabled=false;");
		
		$resposta->addScript("document.getElementById('procedente_1').disabled=false;");
	
		$resposta->addScript("document.getElementById('pac_0').disabled=false;");
		
		$resposta->addScript("document.getElementById('pac_1').disabled=false;");
	}
	else
	{
		//se tiver sido enviado e-mail, desabilita os bot�es aos usuarios
		if($regs["envio_email"])
		{
			$resposta->addScript("document.getElementById('btninserir').disabled=true;");
			
			$resposta->addScript("document.getElementById('btnenviar').disabled=true;");			
		}
		else
		{
			$resposta->addScript("document.getElementById('btninserir').disabled=false;");
			
			$resposta->addScript("document.getElementById('btnenviar').disabled=false;");	
		}
	}
	
	$resposta->addScript("document.getElementById('div_arq').style.visibility = 'hidden'");
	
	$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos_deprecated ";
	$sql .= "WHERE id_nao_conformidade = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	//FAZ O SELECT
	$registro1 = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		if($db->numero_registros>0)
		{
			$resposta->addScript("document.getElementById('div_arq').style.visibility = 'visible'");
		}
		
		$arquivo = "";
		
		while($regs1 = mysqli_fetch_assoc($registro1))
		{
			$arq = pathinfo($regs1["anexo"]);
			
			$link = "<a href=\"#\" style=\"text-decoration:none\" onclick=\"javascript:open_file('".$regs1["anexo"]."','ANEXOS_RNC');\">".$regs1["nome_arquivo"]."</a>";
			
			//permite a exclus�o dos anexos
			if($_SESSION["id_funcionario"]==6 || $_SESSION["id_funcionario"]==978 || $_SESSION["id_funcionario"]==871 || $_SESSION["id_funcionario"]==576 || $_SESSION["id_funcionario"]==1142)
			{
				$del = "<img style=\"cursor:pointer;\" src=\"../images/buttons_action/apagar.gif\" onClick=\"javascript:if(apagar('".$regs1["nome_arquivo"] . "')){xajax_excluir_arquivo('".$regs1["id_nao_conformidade_anexo"]."','".$regs["nome_arquivo"] . "');}\">";
			}
			else
			{
				$del = "&nbsp;";

			}			
			
			$arquivo .= "<div style=\"width:20px; float:left;\">".retornaImagem($arq["extension"])."</div><div style=\"width:150px; float:left; \">".$link."</div><div style=\"width:20px; float:left; \">".$del."</div><div style=\"clear:both;\"></div>";
		}
	}
	
	$resposta->addAssign("codigo", "value", $regs["cod_nao_conformidade"]);
	
	$resposta->addAssign("id", "value", $id);
	
	$resposta->addAssign("div_arquivos", "innerHTML", $arquivo);
	
	$resposta->addAssign("originador", "value", $regs["funcionario"]);
	
	$resposta->addAssign("id_originador", "value", $regs["id_funcionario_criador"]);
	
	$resposta->addAssign("setor", "value", $regs["setor"]);
	
	$resposta->addAssign("id_setor", "value", $regs["id_setor"]);
	
	$resposta->addAssign("data", "value", mysql_php($regs["data_criacao"]));
	
	$resposta->addScript("seleciona_combo(".$regs["id_os"].",'escolhaos');");
	
	$resposta->addAssign("desc_nc", "innerHTML", $regs["desc_nao_conformidade"]);
	
	$resposta->addScript("seleciona_combo(".$regs["id_disciplina"].",'disciplina');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_cliente"].",'cliente');");
	
	$resposta->addAssign("desc_acao_imediata", "innerHTML", $regs["desc_acao_imediata"]);
	
	$resposta->addAssign("desc_perdas", "innerHTML", $regs["desc_perdas"]);
	
	$resposta->addAssign("desc_eficacia", "innerHTML", $regs["desc_eficacia"]);
	
	$resposta->addAssign("desc_evidencia", "innerHTML", $regs["desc_evidencia"]);
	
	$resposta->addScript('document.getElementsByName("status")['.$regs["status"].'].checked=true');

	$resposta->addScript('document.getElementsByName("procedente")['.($regs["procedente"]-1).'].checked=true');

	$resposta->addScript('document.getElementsByName("pac")['.($regs["plano_acao"]).'].checked=true');

	if($regs["plano_acao"]==0)
	{
		$resposta->addScript("document.getElementById('id_plano').selectedIndex=0;document.getElementById('id_plano').disabled=true;");
	}
	else
	{
		$resposta->addScript("document.getElementById('id_plano').disabled=false;");	
	
		$resposta->addScript("seleciona_combo(".$regs["id_plano_acao"].",'id_plano');");
	}
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'),0);");

	$resposta->addEvent("btnenviar", "onclick", "xajax_atualizar(xajax.getFormValues('frm'),1);");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

//status 0 - salva / 1 - salva e envia
function atualizar($dados_form, $status = 0)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);	
			
	$db = new banco_dados;
	
	if($conf->checa_permissao(4,$resposta))
	{
		$validacao = $dados_form["desc_eficacia"]!='' && 
					$dados_form["status"]!='' &&
					$dados_form["procedente"]!='' &&
					$dados_form["pac"]!='';

		
		if($validacao)
		{

			$usql = "UPDATE ".DATABASE.".nao_conformidades_deprecated SET ";
			
			//permite a salvar os campos aos funcionarios da SGI
			if($_SESSION["id_funcionario"]==6 || $_SESSION["id_funcionario"]==978 || $_SESSION["id_funcionario"]==871 || $_SESSION["id_funcionario"]==576 || $_SESSION["id_funcionario"]==1142)
			{

				$usql .= "desc_evidencia = '" . maiusculas($dados_form["desc_evidencia"]) . "', ";
				$usql .= "data_criacao = '". php_mysql($dados_form["data"]) ."', ";
				$usql .= "desc_eficacia = '" . maiusculas($dados_form["desc_eficacia"]) . "', ";
				$usql .= "status = '" . $dados_form["status"] . "', ";
				$usql .= "procedente = '" . $dados_form["procedente"] . "', ";
				$usql .= "plano_acao = '" . $dados_form["pac"] . "', ";
				$usql .= "id_plano_acao = '" . $dados_form["id_plano"] . "', ";
			}
			
			$usql .= "desc_nao_conformidade = '" . maiusculas($dados_form["desc_nc"]) . "', ";
			$usql .= "desc_acao_imediata = '" . maiusculas($dados_form["desc_acao_imediata"]) . "', ";
			$usql .= "desc_perdas = '" . maiusculas($dados_form["desc_perdas"]) . "', ";
			$usql .= "id_os = '" . $dados_form["escolhaos"] . "', ";
			$usql .= "id_setor = '" . $dados_form["id_setor"] . "', ";
			$usql .= "id_disciplina = '" . $dados_form["disciplina"] . "', ";
			$usql .= "id_cliente = '" . $dados_form["cliente"] . "' ";
		
			$usql .= "WHERE id_nao_conformidade = '".$dados_form["id"]."' ";
			
			//FAZ O UPDATE
			$db->update($usql,'MYSQL');
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				if($status==1)
				{
					$resposta->addScript("xajax_email(".$dados_form["id"].");");
				}
				
				//salva anexos
				$resposta->addScript("anexos(".$dados_form["id"].");");
			}
			
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
	}

	return $resposta;
}

function email($id)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	//if($conf->checa_permissao(8,$resposta))
	//{
		$mail = new PHPMailer();	
		
		$mail->From     = "hugo.castilho@dominio.com.br";
		$mail->FromName = "SGI";
		$mail->Host     = "smtp.devemada";
		$mail->Mailer   = "smtp";
		$mail->ContentType = "text/html";
		$mail->Subject = "N�O CONFORMIDADES INTERNAS";		
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".nao_conformidades_deprecated ";
		$sql .= "LEFT JOIN ".DATABASE.".OS ON (nao_conformidades_deprecated.id_os = OS.id_os) ";
		$sql .= "WHERE nao_conformidades_deprecated.nao_conformidade_delete = 0 ";
		$sql .= "AND nao_conformidades_deprecated.id_funcionario_criador = funcionarios.id_funcionario ";
		$sql .= "AND nao_conformidades_deprecated.id_setor = setores.id_setor ";
		$sql .= "AND nao_conformidades_deprecated.id_nao_conformidade = '".$id."' ";	
		
		//FAZ O SELECT
		$registro = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
	
		$regs = mysqli_fetch_assoc($registro);
		
		switch ($regs["status"])
		{
			case 0:
				$status = "PENDENTE";
			break;
			
			case 1:
				$status = "EM AN�LISE";
			break;
			
			case 2:
				$status = "ENCERRADO";
			break;
		}
		
		$procedente = $regs["procedente"]?"SIM":"N�O";
		
		if($regs["os"]!=0)
		{
			$os = sprintf("%010d",$regs["os"]);
		}
		else
		{
			$os = "N�O APLIC�VEL";	
		}
		
		$sql = "SELECT * FROM ".DATABASE.".planos_acoes ";
		$sql .= "WHERE plano_acao_delete = 0 ";
		$sql .= "AND planos_acoes.id_plano_acao = '".$regs["id_plano_acao"]."' ";
		
		//FAZ O SELECT
		$res3 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$cont3 = mysqli_fetch_assoc($res3);		
		
		$sql = "SELECT * FROM ".DATABASE.".setores ";
		$sql .= "WHERE id_setor = '".$regs["id_disciplina"]."' ";
		
		//FAZ O SELECT
		$res0 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$cont0 = mysqli_fetch_assoc($res0);
		
		$sql = "SELECT *, unidades.descricao AS unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
		$sql .= "WHERE empresas.id_empresa_erp = '".$regs["id_cliente"]."' ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		
		//FAZ O SELECT
		$res1 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$cont1 = mysqli_fetch_assoc($res1);		
		
		$body = "<html>
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>teste</title>
			<style type=\"text/css\">
			<!--
			.style4 {color: #0000FF; font-family: Arial, Helvetica, sans-serif; font-size: 10px; }
			.style7 {color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 9px; }
			-->
			</style>
			</head>
			<body>
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"1\">
			  <tr>
				<td colspan=\"8\" align=\"left\"><strong>Documento:</strong>&nbsp;N�O&nbsp;CONFORMIDADES&nbsp;INTERNAS</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Formulario:&nbsp;</strong>".$regs["cod_nao_conformidade"]."</td>
			  </tr>
			  <tr>
				<td colspan=\"3\"><strong>Revis�o&nbsp;N�:</strong>&nbsp;0</td>
				<td colspan=\"5\"><strong>data&nbsp;da&nbsp;Emiss�o:</strong>&nbsp;".date('d/m/Y')."</td>
			  </tr>
			  <tr>
				<td colspan=\"3\"><strong>Originador:</strong>&nbsp;".$regs["funcionario"]."</td>
				<td colspan=\"3\"><strong>setor:</strong>&nbsp;".$regs["setor"]."</td>
				<td colspan=\"2\"><strong>data&nbsp;cria��o:</strong>&nbsp;".mysql_php($regs["data_criacao"])."</td>
			  </tr>
			  <tr>
				<td colspan=\"4\"><strong>OS:</strong>&nbsp;". $os."</td>
				<td colspan=\"4\"><strong>status:</strong>&nbsp;". $status ."</td>
			  </tr>			  
			  <tr>
				<td colspan=\"4\"><strong>Disciplina:</strong>&nbsp;". $cont0["setor"]."</td>
				<td colspan=\"4\"><strong>Cliente:</strong>&nbsp;". $cont1["empresa"]. " - ".$cont1["unidade"] ."</td>
			  </tr>
			  <tr>
				<td colspan=\"4\"><strong>Procedente:</strong>&nbsp;". $procedente."</td>
				<td colspan=\"4\"><strong>Plano&nbsp;de&nbsp;A��o&nbsp;Corretiva:</strong>&nbsp;". $cont3["cod_plano_acao"]. "</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Descri��o&nbsp;da&nbsp;n�o&nbsp;conformidade:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_nao_conformidade"]). "</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>A��o&nbsp;Imediata:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_acao_imediata"]). "</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Perdas:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_perdas"]) ."</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Efic�cia&nbsp;de&nbsp;A��o&nbsp;Corretiva&nbsp;/&nbsp;Preventiva:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_eficacia"]) ."</td>
			  </tr>
			  
			  </table></body></html>";	


		$mail->Body = $body;
		
		//AMBIENTE OFICIAL
		if(AMBIENTE==2)
		{
			$mail->AddAddress('carlos.abreu@dominio.com.br', 'Carlos Abreu');
			$mail->AddAddress('hugo.castilho@dominio.com.br', 'Hugo Castilho');
			$mail->AddAddress('norberto.muchuelo@dominio.com.br', 'Norberto Muchuelo');
		}
		else
		{
			$mail->AddAddress('sistemas@dominio.com.br', 'Sistemas Devemada');	
		}
		
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
		else
		{
			
			$usql = "UPDATE ".DATABASE.".nao_conformidades_deprecated SET ";
			$usql .= "envio_email = 1 ";
			$usql .= "WHERE id_nao_conformidade = '".$regs["id_nao_conformidade"]."' ";
			
			//FAZ O UPDATE
			$db->update($usql,'MYSQL');
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{			
				$resposta->addAlert("E-mail enviado aos envolvidos.");
			}
		}
		
		$mail->ClearAddresses();	
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_RNC/";
	
	$erro = false;
	
	if($conf->checa_permissao(2,$resposta))
	{		
		$usql = "UPDATE ".DATABASE.".nao_conformidades_deprecated SET ";
		$usql .= "nao_conformidades_deprecated.nao_conformidade_delete = 1, ";
		$usql .= "nao_conformidade_delete_who = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "WHERE nao_conformidades_deprecated.id_nao_conformidade = '".$id."' ";
		
		$db->update($usql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}
		else
		{
			$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos_deprecated ";
			$sql .= "WHERE nao_conformidades_anexos_deprecated.id_nao_conformidade = '".$id."' ";

			$cont = $db->select($sql,'MYSQL');
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				$erro = true;
			}
			else
			{				
				while($regs = mysqli_fetch_assoc($cont))
				{
					$del = unlink($diretorio.$regs["anexo"]);
					
					if(!$del)
					{
						$erro = true;	
					}					
					
					$usql = "UPDATE ".DATABASE.".nao_conformidades_anexos_deprecated SET ";
					$usql .= "reg_del = 1, ";
					$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "WHERE id_nao_conformidade_anexo = '".$regs["id_nao_conformidade_anexo"]."' ";
					
					$db->update($usql,'MYSQL');
					
					//se der mensagem de erro, mostra
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						$erro = true;
					}
				}
			}
		}
		
		if($erro)
		{
			$resposta->addAlert('Erro ao excluir o registro');
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");	

	}	

	return $resposta;
}

function excluir_arquivo($id_anexo)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$erro = false;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_RNC/";
	
	$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos_deprecated ";
	$sql .= "WHERE nao_conformidades_anexos_deprecated.id_nao_conformidade_anexo = '".$id_anexo."' ";

	$cont = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		$erro = true;
	}
	else
	{				
		$regs = mysqli_fetch_assoc($cont);
		
		$del = unlink($diretorio.$regs["anexo"]);
		
		if(!$del)
		{
			$erro = true;	
		}					
		
		$usql = "UPDATE ".DATABASE.".nao_conformidades_anexos_deprecated SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "WHERE id_nao_conformidade_anexo = '".$regs["id_nao_conformidade_anexo"]."' ";
		
		$db->update($usql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}		
	}
	
	if(!$erro)
	{
		$resposta->addAlert("Anexo exclu�do do sistema.");
		
		$resposta->addScript("xajax_editar(".$regs["id_nao_conformidade"].");");
	}
	else
	{
		$resposta->addAlert("Erro ao excluir arquivo.");	
	}
	
	return $resposta;	
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("email");
$xajax->registerFunction("excluir_arquivo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Grid -->
<script type="text/javascript" src="../includes/dhtmlx_3_6/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx_3_6/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx_3_6/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx_3_6/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script language="javascript">
/*
<!--
//captura a tecla enter
document.onkeypress = keyhandler;

function keyhandler(e) 
{
	if (document.layers)
		Key = e.which;
	else
		Key = window.event.keyCode;

	if (Key != 0)
		if (Key == 13)
			//alert('Enter key press');
			xajax_insere(xajax.getFormValues('frm'));
}

//-->
*/

//fun��o que adiciona campos no div
function add_controles(div_container)
{	
	var id_item, texto, controle;
	
	id_item = document.getElementById('qtd').value;

	id_item++;
	
	input_file = document.createElement('input');
	input_file.type = 'file';
	input_file.id = 'input_' + id_item;
	input_file.name = 'input_' + id_item;
	
	document.getElementById(div_container).appendChild(input_file);
	
	document.getElementById('qtd').value = id_item;
}

function anexos(id_nc)
{
	document.getElementById('id').value = id_nc;

	document.getElementById('frm').submit();		
}

function finish(erro)
{
	if(erro==null || erro=='')
	{
		alert('Registro inserido com sucesso.');	
	}
	else
	{
		alert(erro);	
	}
	
	xajax_atualizatabela(xajax.getFormValues('frm'));
	
	xajax_voltar();	
}

function grid(table,tamanho)
{	
	var mygrid = new dhtmlXGridFromTable(table);
	
	mygrid.imgURL = "../includes/dhtmlx_3_6/dhtmlxGrid/codebase/imgs/";
	
	mygrid.enableAutoHeight(true,tamanho);
	//mygrid.enableAutoWidth(true);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.setSkin("dhx_skyblue");
	
}

function imprimir()
{
	document.getElementById('frm').action = './relatorios/rel_nc_excel.php';
	document.getElementById('frm').target = '_blank';
	document.getElementById('frm').submit();	
}

function imprimir_rnc(id_rnc)
{
	window.open('relatorios/rel_rnc.php?id_rnc='+id_rnc, '_blank');
}

function open_file(documento,path)
{
	window.open("../includes/documento.php?documento="+documento+"&caminho="+path,"_blank");	
}

</script>

<?php

$conf = new configs();

$db = new banco_dados;

$array_os_values[] = "0";
$array_os_output[] = "N�O APLIC�VEL";

$array_cliente_values[] = "0";
$array_cliente_output[] =  "SELECIONE";

$array_disciplina_values[] = "0";
$array_disciplina_output[] =  "SELECIONE";

$array_pac_values[] = "0";
$array_pac_output[] = "SELECIONE";	

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('nao_conformidades_internas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","N�O CONFORMIDADES INTERNAS");

$smarty->assign("codigo","NC-".date('YmdHi'));

$smarty->assign("originador",$_SESSION["nome_usuario"]);

$smarty->assign("id_originador",$_SESSION["id_funcionario"]);

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE OS.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND os.os > 1700 ";
//$sql .= "AND ordem_servico_status.os_status IN ('EM ANDAMENTO','AS BUILT','OS POR ADM','APROVADA') ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
$sql .= "GROUP BY os.os ";
$sql .= "ORDER BY os.os ";

//FAZ O SELECT
$res2 = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

while ($cont2 = mysqli_fetch_assoc($res2))
{
	$array_os_values[] = $cont2["id_os"];
	$array_os_output[] =  sprintf("%010d",$cont2["os"]);
}

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

//FAZ O SELECT
$res = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

$cont = mysqli_fetch_assoc($res);

$smarty->assign("setor",$cont["setor"]);

$smarty->assign("id_setor",$cont["id_setor"]);

$sql = "SELECT * FROM ".DATABASE.".setores ";
//$sql .= "WHERE nao_conformidade_delete = 0 ";
$sql .= "ORDER BY setor ";

//FAZ O SELECT
$res0 = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

while ($cont0 = mysqli_fetch_assoc($res0))
{
	$array_disciplina_values[] = $cont0["id_setor"];
	$array_disciplina_output[] =  $cont0["setor"];
}


$sql = "SELECT *, unidades.descricao AS unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE OS.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND OS.id_os_status = ordem_servico_status.id_os_status ";
//$sql .= "AND ordem_servico_status.os_status IN ('EM ANDAMENTO','AS BUILT','OS POR ADM','APROVADA') ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
$sql .= "GROUP BY empresas.id_empresa_erp ";
$sql .= "ORDER BY empresa, unidades.descricao ";

//FAZ O SELECT
$res1 = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

while ($cont1 = mysqli_fetch_assoc($res1))
{
	$array_cliente_values[] = $cont1["id_empresa_erp"];
	$array_cliente_output[] =  $cont1["empresa"]. " - ".$cont1["unidade"];
}

$sql = "SELECT * FROM ".DATABASE.".planos_acoes ";
$sql .= "WHERE plano_acao_delete = 0 ";

//FAZ O SELECT
$res3 = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

while ($cont3 = mysqli_fetch_assoc($res3))
{
	$array_pac_values[] = $cont3["id_plano_acao"];
	$array_pac_output[] = $cont3["cod_plano_acao"];
}

//$smarty->assign("origem",$origem);

$smarty->assign("option_os_values",$array_os_values);

$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_disciplina_values",$array_disciplina_values);

$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("option_cliente_values",$array_cliente_values);

$smarty->assign("option_cliente_output",$array_cliente_output);

$smarty->assign("option_pac_values",$array_pac_values);

$smarty->assign("option_pac_output",$array_pac_output);

$smarty->assign("classe","../classes/".$conf->classe('administrativo').".css");

$smarty->display('nao_conformidades_internas_deprecated.tpl');

?>
