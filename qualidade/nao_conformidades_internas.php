<?php 
/*
	  Formulario de nao Conformidades
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../qualidade/nao_conformidades_internas.php
	  
	  data de criacao: 24/03/2014
	  
	  Versao 0 --> VERSAO INICIAL
	  Versao 1 --> Alteracao no sistema de inserir, envio de e-mail, impressao (#597) - 30/06/2014 - Carlos Abreu
	  Versao 2 --> Alteracao do formulario - #2243 - Carlos Abreu - 24/07/2015
	  Versao 3 --> Alteracao do formulario - permissoes - Carlos Abreu - 11/09/2015
	  Versao 4 --> Alteracao do formulario - permissoes/grid anexos - Carlos Abreu - 25/09/2015
	  Versao 5 --> Revisao das permissoes conforme solicitacao Hugo Castilho - 29/09/2015 - Carlos Abreu
	  Versao 6 --> Alteracoes pedidas por Clayton - 27/09/2016 - Carlos Máximo
	  Versao 7 --> Alteracoes pedidas por Clayton - 12/01/2017 - Carlos Máximo
	  Versao 7 --> atualizacao layout - Carlos Abreu - 03/04/2017
	  Versão 8 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$_SESSION["id_sub_modulo"] = 583;

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(583))
{
	nao_permitido();
}

//nivel de supervisao, coordenacao
function permit_colab_sup()
{
	$db = new banco_dados;
	
	$array_func = NULL;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.nivel_atuacao IN ('S','D','G','C') ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach ($db->array_select as $regs)
	{
		$array_func[$regs["id_funcionario"]] = $regs["id_funcionario"];
	}
	
	return $array_func;	
}

//colaboradores sgi
function permit_colab_sgi()
{
	$array_sgi = NULL;

	//$array_sgi = array(6=>6,978=>978,871=>871,576=>576,1142=>1142);
		
	return $array_sgi;	
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$array_func = permit_colab_sup();
	
	$array_sgi = permit_colab_sgi();
	
	$resposta->addAssign("codigo","value", "NC-".date('YmdHi'));

	$resposta->addAssign("originador", "value" ,$_SESSION["nome_usuario"]);
	
	$resposta->addAssign("id_originador", "value", $_SESSION["id_funcionario"]);

	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$cont = $db->array_select[0];
	
	$resposta->addAssign("id","value", "0");
	
	$resposta->addScript("seleciona_combo('0','escolhaos');");
	
	$resposta->addScript("seleciona_combo('0','cliente');");
	
	$resposta->addScript("seleciona_combo('0','disciplina');");
	
	$resposta->addAssign("setor","value", $cont["setor"]);
	
	$resposta->addAssign("id_setor","value", $cont["id_setor"]);
	
	$resposta->addAssign("data","value", date('d/m/Y'));
	
	$resposta->addAssign("outros","value", "");
	
	$resposta->addAssign("desc_nc", "value", "");
	
	$resposta->addAssign("desc_acao_imediata", "value", "");
	
	$resposta->addAssign("desc_perdas", "value", "");	
	
	$resposta->addAssign("desc_evidencia", "value", "");
	
	$resposta->addAssign("desc_obs", "value", "");
	
	$resposta->addAssign("desc_encerramento", "value", "");

	$resposta->addScript('document.getElementById("outros").style.backgroundColor="white"');
	
	$resposta->addScript("document.getElementById('outros').readOnly=false");

	$resposta->addScript("document.getElementById('procedente_0').checked='';");
	
	$resposta->addScript("document.getElementById('procedente_1').checked='';");	

	$resposta->addScript("document.getElementById('status_0').checked='';");
	
	$resposta->addScript("document.getElementById('status_1').checked='';");
	
	$resposta->addScript("document.getElementById('status_2').checked='';");	
	
	$resposta->addScript("xajax_arq_anexos('0');");	
	
	$resposta->addScript("xajax_acoes_complem('0');");
	
	$sql = "SELECT COUNT(*) AS referencias FROM ".DATABASE.".tipos_documentos_planos_acao "; 
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs0 = $db->array_select[0];
	
	for($ch = 0;$ch<$regs0["referencias"];$ch++)
	{
		$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=""');
	
		$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].checked=""');
	}

	//permite a edicao dos campos aos funcionarios da SGI ou Coordenadores
	if(in_array($_SESSION["id_funcionario"],$array_sgi) || in_array($_SESSION["id_funcionario"],$array_func))
	{		
		
		$resposta->addScript('document.getElementById("outros").disabled=false');		
		
		$resposta->addScript("document.getElementById('desc_evidencia').disabled=false;");
				
		//somente ao SGI
		if(in_array($_SESSION["id_funcionario"],$array_sgi))
		{
			$resposta->addScript('document.getElementById("escolhaos").disabled=false');
			
			$resposta->addScript('document.getElementById("disciplina").disabled=false');
			
			$resposta->addScript('document.getElementById("cliente").disabled=false');
			
			$resposta->addScript('document.getElementById("desc_nc").disabled=false');
			
			$resposta->addScript('document.getElementById("desc_acao_imediata").disabled=false');
			
			$resposta->addScript('document.getElementById("desc_perdas").disabled=false');
			
			$resposta->addScript("document.getElementById('desc_obs').disabled=false");
			
			$resposta->addScript("document.getElementById('desc_encerramento').disabled=false");
			
			$resposta->addScript("document.getElementById('procedente_0').disabled=false;");
			
			$resposta->addScript("document.getElementById('procedente_1').disabled=false;");
			
			$resposta->addScript("document.getElementById('input_1').disabled=false");
			
		}
	}
	else
	{
		
		$resposta->addScript("document.getElementById('img_1').style.visibility='hidden'");
		
		$resposta->addScript("document.getElementById('input_1').disabled=true");
	
		$resposta->addScript("document.getElementById('desc_evidencia').disabled=true");
	
		$resposta->addScript("document.getElementById('desc_evidencia').disabled=true");
		
		$resposta->addScript("document.getElementById('desc_obs').disabled=true");
		
		$resposta->addScript("document.getElementById('desc_encerramento').disabled=true;");
		
		$resposta->addScript('document.getElementById("outros").disabled=true');

		$resposta->addScript("document.getElementById('desc_obs').disabled=true");
		
		$resposta->addScript("document.getElementById('desc_encerramento').disabled=true;");	

		$resposta->addScript('document.getElementById("escolhaos").disabled=false');
		
		$resposta->addScript('document.getElementById("disciplina").disabled=false');
		
		$resposta->addScript('document.getElementById("cliente").disabled=false');
		
		$resposta->addScript('document.getElementById("desc_nc").disabled=false');
		
		$resposta->addScript('document.getElementById("desc_acao_imediata").disabled=false');
		
		$resposta->addScript('document.getElementById("desc_perdas").disabled=false');

	}
	
	$resposta->addScript("document.getElementById('disciplina').disabled=false;document.getElementById('cliente').selectedIndex=0;document.getElementById('cliente').disabled=true';");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm',true),0); ");
	
	$resposta->addEvent("btnenviar","onclick","xajax_insere(xajax.getFormValues('frm',true),1); ");
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$array_sgi = permit_colab_sgi();
	
	$array_sup = permit_colab_sup();
		
	//editar
	if($dados_form["id"]!=0)
	{
		$resposta->addScript("xajax_editar(".$dados_form["id"].");");
	}
	else
	{
		//habilita os campos
		if(in_array($_SESSION["id_funcionario"],$array_sgi) || in_array($_SESSION["id_funcionario"],$array_sup))
		{
			$resposta->addScript('document.getElementById("outros").disabled=false');		
			
			$resposta->addScript("document.getElementById('desc_evidencia').disabled=false;");
			
			$resposta->addScript("document.getElementById('img_1').style.visibility='visible'");
			
			$resposta->addScript("document.getElementById('input_1').disabled=false");
			
			if(in_array($_SESSION["id_funcionario"],$array_sgi))
			{
				$resposta->addScript("document.getElementById('desc_obs').disabled=false");
				
				$resposta->addScript("document.getElementById('desc_encerramento').disabled=false");
				
				$resposta->addScript("document.getElementById('procedente_0').disabled=false;");
				
				$resposta->addScript("document.getElementById('procedente_1').disabled=false;");
			}
		}
		
		$resposta->addScript("xajax_acoes_complem(0)");
		
		$resposta->addScript("xajax_arq_anexos(0)");		
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);
	
	$array_func = permit_colab_sup();
	
	$array_sgi = permit_colab_sgi();
	
	$db = new banco_dados;
	
	$sql = "SELECT COUNT(*) AS referencias FROM ".DATABASE.".tipos_documentos_planos_acao ";

	$db->select($sql,'MYSQL',true);
	
	$regs0 = $db->array_select[0];

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".nao_conformidades ";
	$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (nao_conformidades.id_os = ordem_servico.id_os) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_planos_acao ON (nao_conformidades.id_tipo_documento = tipos_documentos_planos_acao.id_tipo_documento) ";
	$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades.id_funcionario_criador = funcionarios.id_funcionario ";
	$sql .= "AND nao_conformidades.id_setor = setores.id_setor ";
	$sql .= "AND nao_conformidades.id_nao_conformidade = '".$id."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];

	$resposta->addAssign("codigo", "value", $regs["cod_nao_conformidade"]);
	
	$resposta->addAssign("id", "value", $id);

	$resposta->addAssign("originador", "value", $regs["funcionario"]);
	
	$resposta->addAssign("id_originador", "value", $regs["id_funcionario_criador"]);
	$resposta->addAssign("id_funcionario", "value", $regs["id_funcionario_responsavel"]);
	
	$resposta->addAssign("setor", "value", $regs["setor"]);
	
	$resposta->addAssign("id_setor", "value", $regs["id_setor"]);
	
	$resposta->addAssign("data_nc", "value", mysql_php($regs["data_criacao"]));
	$resposta->addAssign("data_resp", "value", mysql_php($regs["data"]));
	
	$resposta->addScript("seleciona_combo(".$regs["id_os"].",'escolhaos');");
	
	//se nao for aplicado a OS
	if($regs["id_os"]!=0)
	{
		$resposta->addScript("xajax_clientes(".$regs["id_os"].")");
	}
	else
	{
		$resposta->addScript("seleciona_combo(".$regs["id_cliente"].",'cliente');");
	}
	
	$resposta->addScript("seleciona_combo(".$regs["id_disciplina"].",'disciplina');");	
	
	if($regs["id_tipo_origem"]==1)
	{
		$resposta->addScript('document.getElementById("outros_cliente").style.display="block"');
		$resposta->addAssign("outros_cliente", "value", $regs["desc_outros_cliente"]);
	}
	else
	{
		$resposta->addScript('document.getElementById("outros_cliente").style.display="none"');
		$resposta->addAssign("outros_cliente", "value", "");
	}
	
	if($regs["id_tipo_origem"]==2)
	{
		$resposta->addScript('document.getElementById("outros_acidente").style.display="block"');
		$resposta->addAssign("outros_acidente", "value", $regs["desc_outros_acidente"]);
	}
	else
	{
		$resposta->addScript('document.getElementById("outros_acidente").style.display="none"');
		$resposta->addAssign("outros_acidente", "value", "");
	}

	if($regs["id_tipo_origem"]==3)
	{
		$resposta->addScript('document.getElementById("outros").style.display="block"');
		$resposta->addAssign("outros", "value", $regs["desc_outros"]);
	}
	else
	{
		$resposta->addScript('document.getElementById("outros").style.display="none"');
		$resposta->addAssign("outros", "value", "");
	}
	
	if($regs["id_tipo_origem"]==4)
	{
		$resposta->addScript('document.getElementById("outros_incidente").style.display="block"');
		$resposta->addAssign("outros_incidente", "value", $regs["desc_outros_incidente"]);
	}
	else
	{
		$resposta->addScript('document.getElementById("outros_incidente").style.display="none"');
		$resposta->addAssign("outros_incidente", "value", "");
	}
		
	if($regs["id_tipo_origem"]==7)
	{
		$resposta->addScript('document.getElementById("outros_fornec").style.display="block"');
		$resposta->addAssign("outros_fornec", "value", $regs["desc_outros_fornec"]);
	}
	else
	{
		$resposta->addScript('document.getElementById("outros_fornec").style.display="none"');
		$resposta->addAssign("outros_fornec", "value", "");
	}
		
	$index = $regs["id_tipo_documento"] != 10 ? $regs["id_tipo_documento"]-1 : 2;
	
	$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$index.'].checked=true');
	
	$indexTpOrigem = $regs["id_tipo_origem"]-1;
	
	$resposta->addScript('document.getElementsByName("rd_tp_origem")['.$indexTpOrigem.'].checked=true');
	
	switch($regs["id_tipo_origem"])
	{
		case 3:
			$resposta->addScript('document.getElementById("outros_origem").style.display="block"');
			$resposta->addAssign("outros_origem", "value", $regs["desc_outros"]);
		break;
		case 1:
			$resposta->addScript('document.getElementById("outros_origem").style.display="block"');
			$resposta->addAssign("outros_origem", "value", $regs["desc_outros"]);
		break;
		case 7:
			$tp_orig .= "<td align=\"left\" ".$width." ><input type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros_fornec').style.display='block';document.getElementById('outros_fornec').style.backgroundColor='white';document.getElementById('outros_fornec').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros_fornec\" type=\"text\" class=\"caixa\" id=\"outros_fornec\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		default:
			$tp_orig .= "<td align=\"left\" ".$width."><input type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('nao_conf').disabled=true;\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label></td>";
		break;
	}
	
	$resposta->addScript('document.getElementsByName("status_nc")['.$regs["status"].'].checked=true');
	
	if (!empty($regs["verificacao_eficacia_status"]))
	{
		$resposta->addScript('document.getElementById("rd_eficacia'.$regs["verificacao_eficacia_status"].'").checked=true');
	}

	if ($regs['procedente'] == 1)
	{
		$resposta->addScript("document.getElementById('tableAnaliseCausa').style.display='block'");
	}
	
	$resposta->addScript('document.getElementsByName("procedente")['.($regs["procedente"]-1).'].checked=true');
	
	$resposta->addAssign("desc_nc", "value", $regs["desc_nao_conformidade"]);
	
	$resposta->addAssign("desc_acao_imediata", "value", $regs["desc_acao_imediata"]);
	
	$resposta->addAssign("desc_perdas", "value", $regs["desc_perdas"]);	
	
	$resposta->addAssign("desc_evidencia", "value", $regs["desc_evidencia"]);
	
	$resposta->addAssign("desc_obs", "value", $regs["desc_obs"]);
	
	$resposta->addAssign("desc_encerramento", "value", $regs["desc_encerramento"]);
	
	$resposta->addAssign("desc_analise_causa", "value", $regs["desc_analise_causa"]);
	
	//habilita os campos conforme:
	//usuario comum, somente pode editar se nao enviou (envio_email=0)
	//coordenadores, podem editar somente as acoes complementares e se nao enviou (envio_email=2)
	//sgi, podem editar todos os campos
	
	//tipos documentos
	for($ch = 0;$ch<$regs0["referencias"];$ch++)
	{
		//se usuario do sgi, habilita campo
		if(in_array($_SESSION["id_funcionario"],$array_sgi))
		{
			//$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=false');
			$resposta->addScriptCall("enabledByClass","campoOriginador");
		}
		else
		{
			//se nao enviado e-mail e for o criador da NC, habilita edicao
			if($regs["id_funcionario_criador"]==$_SESSION["id_funcionario"] && (in_array($regs["envio_email"],array('0','2'))))
			{
				//usuario comum, habilita campos
				if(!in_array($_SESSION["id_funcionario"],$array_func) && in_array($regs["envio_email"],array('0')))
				{
					$resposta->addScriptCall("disabledByClass","campoSGI");
					//Se nao envio email e o usuario e o criador da nc habilita campos
					if($regs["id_funcionario_criador"]==$_SESSION["id_funcionario"] && in_array($regs["envio_email"],array('0')))
					{
						$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=false');
					}
					else
					{
						$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=true');	
					}
				}
				else
				{
					//coordenacao, habilita campos
					if((in_array($_SESSION["id_funcionario"],$array_func) || $regs["id_funcionario_criador"]==$_SESSION["id_funcionario"]) && in_array($regs["envio_email"],array('2')))
					{
						$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=false');
					}
					else
					{
						$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=true');	
					}
				}
			}
			else
			{
				$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=true');
			}			
		}
	}	
	
	//permite a edicao dos campos aos funcionarios da SGI ou Coordenadores
	if(in_array($_SESSION["id_funcionario"],$array_sgi))
	{	
		$resposta->addScriptCall("enabledByClass","campoOriginador");		
	}
	else
	{
		//se nao enviado e-mail e for o criador da NC, habilita edicao
		if((($regs["id_funcionario_criador"]==$_SESSION["id_funcionario"] || in_array($_SESSION["id_funcionario"],$array_func)) && (in_array($regs["envio_email"],array('0','2')))) || in_array($_SESSION["id_funcionario"],$array_sgi))
		{			
			$resposta->addScriptCall("enabledByClass","campoOriginador");
		}
		else
		{
			$resposta->addScriptCall("disabledByClass","campoOriginador");
		}
	}
	
	$resposta->addScript("xajax_acoes_complem(".$id.");");
	$resposta->addScript("xajax_arq_anexos(".$id.");");	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm',true),0);");
	$resposta->addEvent("btnenviar", "onclick", "xajax_atualizar(xajax.getFormValues('frm',true),1);");

	//Habilitando os campos do responsável pela verificacao de eficácia
	if ($_SESSION['id_funcionario'] == $regs['id_funcionario_responsavel'])
	{
		$resposta->addScript("document.getElementById('btnenviar').className='class_botao';");
		$resposta->addScript("document.getElementById('desc_encerramento').className='campoResponsavel';");
		$resposta->addScript("document.getElementById('data_resp').className='campoResponsavel caixa';");
		$resposta->addScriptCall("enabledByClass","campoResponsavel");
	}			

	$resposta->addScript('verificar_status_acoes();');
	
	return $resposta;
}

function acoes_complem($id=0)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();

	$msg = $conf->msg($resposta);
	
	$resposta->addAssign("div_acao_complementar","innerHTML","");
	
	$array_func = permit_colab_sup();
	
	$array_sgi = permit_colab_sgi();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".nao_conformidades ";
	$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades.id_nao_conformidade = '".$id."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs_nc = $db->array_select[0];
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios  ";
	$sql .= "WHERE planos_acoes_complementos.id_nao_conformidade = '".$id."' ";
	$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
	$sql .= "AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario ";
	$sql .= "ORDER BY prazo, item_acao";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_pac = $db->array_select;
	
	//se for do SGI ou coordenacao, habilita inserir PAC
	if(in_array($_SESSION["id_funcionario"],$array_sgi))
	{
		$disable = '';
		
		$resposta->addScriptCall("enabledByClass","campoSGI");
		$resposta->addScript("document.getElementById('add_ac').style.visibility='visible'");
	}
	else
	{			
		$disable = 'disabled="disabled"';
		$resposta->addScriptCall("disabledByClass","campoSGI");
		$resposta->addScript("document.getElementById('add_ac').style.visibility='hidden'");		
	}
	
	//edicao
	if($id!=0 && $db->numero_registros>0)
	{
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
		$sql .= "ORDER BY funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{				 
			foreach ($db->array_select as $regs)
			{
				$func[$regs["id_funcionario"]] = $regs["funcionario"];
			}	
			
			$i = 1;
			
			//habilita campos ao sgi
			if(in_array($_SESSION["id_funcionario"],$array_sgi))
			{				
				$resposta->addScript("document.getElementById('add_ac').style.visibility = 'visible'");
			}
			else
			{
				//se coordenacao, habilita
				if(in_array($_SESSION["id_funcionario"],$array_func))
				{
					$resposta->addScript("document.getElementById('add_ac').style.visibility = 'visible'");
				}
				else
				{
					$resposta->addScript("document.getElementById('add_ac').style.visibility = 'hidden'");
				}
			}
						
			foreach($array_pac as $regs1)
			{
				$select_st = "";
				
				$st = '<select id="status" name="status[]" '.$disable.' class="caixa" style="width:140px" onchange="verificar_status_acoes();">';
		
				$st .= '<option value="">SELECIONE</option>';
		
				for($j=0;$j<=2;$j++)
				{
					if($regs1["status_plano_acao"]==$j)
					{	
						$select_st = 'selected';
					}
					else
					{
						$select_st = '';
					}
					
					switch ($j)
					{
						case 0:
							$texto = 'PENDENTE';
						break;
						
						case 1:
							$texto = 'EM ANDAMENTO';
						break;
						
						case 2:
							$texto = 'ENCERRADO';
						break;		
					}
					
					$st .= '<option value="'.$j.'" '.$select_st.'>'.$texto.'</option>';			
				}		
				
				$st .= '</select>';
				
				$opt = '<select id="responsavel" name="responsavel[]"  '.$disable.' class="caixa" style="width:200px">';		
				
				$opt .= '<option value="0">SELECIONE</option>';
				
				$select_st = '';
				
				foreach($func as $cod=>$fun)
				{
					if($regs1["id_funcionario_responsavel"]==$cod)
					{	
						$select_st = 'selected';
					}
					else
					{
						$select_st = '';
					}
					
					$opt .= '<option value="'.$cod.'" '.$select_st.'>'.$fun.'</option>';
				}
				
				$opt .= '</select>';	
				
				$xml->startElement('row');
					$xml->writeAttribute('id','tr'.$i);
					$xml->writeAttribute('style', 'height:40px !important;');
					$xml->writeElement ('cell','<input onkeyup="alterar_status_acoes();" name="numero[]" type="text" style="border:none;background-color:transparent" id="numero" size="5" value="'.$regs1["item_acao"].'" readonly="readonly">');
					$xml->writeElement ('cell','<textarea name="acao[]" '.$disable.' id="acao" cols="60" rows="2">'.$regs1["plano_acao"].'</textarea>');
					$xml->writeElement ('cell',$opt);
					$xml->writeElement ('cell','<input name="data[]" type="text" '.$disable.' class="caixa" id="data" size="13" onkeypress="transformaData(this, event);" value="'.mysql_php($regs1["prazo"]).'" onblur=return checaTamanhoData(this,10);>');
					$xml->writeElement ('cell',$st);
				$xml->endElement();	
				
				$i++;		
			}
			
			$resposta->addAssign("itens","value", $i);
		}
		
	}
	else
	{
		$opt = '<select id="responsavel" name="responsavel[]" '.$disable.'  class="caixa">';
		
		$opt .= '<option value="0">SELECIONE</option>';
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.situacao NOT IN('CANCELADO','DESLIGADO') ";
		$sql .= "ORDER BY funcionario ";

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		} 
		
		foreach ($db->array_select as $regs)
		{
			$opt .= '<option value='.$regs["id_funcionario"].'>'.$regs["funcionario"].'</option>';
		}
		
		$opt .= '</select>';
		
		$st = '<select id="status" name="status[]" '.$disable.' class="caixa" onchange="verificar_status_acoes();">';
		$st .= '<option value="">SELECIONE</option>';
		$st .= '<option value="0">PENDENTE</option>';
		$st .= '<option value="1">EM ANDAMENTO</option>';
		$st .= '<option value="2">ENCERRADO</option>';
		$st .= '</select>';
	
		$xml->startElement('row');
			$xml->writeAttribute('id','tr1');
			$xml->writeElement ('cell','<input name="numero[]" type="text" style="border:none;background-color:transparent" id="numero" size="5" value="1" readonly="readonly">');
			$xml->writeElement ('cell','<input onkeyup="alterar_status_acoes();" name="acao[]" type="text" '.$disable.' class="caixa" id="acao" size="41" value="">');
			$xml->writeElement ('cell',$opt);
			$xml->writeElement ('cell','<input name="data[]" type="text" '.$disable.' class="caixa" id="data" size="13" onkeypress=transformaData(this, event); value="'.date('d/m/Y').'" onblur=return checaTamanhoData(this,10);>');
			$xml->writeElement ('cell',$st);
		$xml->endElement();	
	
		$resposta->addAssign("itens","value", "1");
		
	}
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_acao_complementar',true,'300','".$conteudo."');");
	$resposta->addScript("verificar_status_acoes();");
	
	return $resposta;	
}

function arq_anexos($id=0)
{
	$resposta = new xajaxResponse();
	
	$array_func = permit_colab_sup();
	
	$array_sgi = permit_colab_sgi();
	
	$xml = new XMLWriter();
	
	$conf = new configs();

	$msg = $conf->msg($resposta);
	
	$resposta->addAssign("div_arquivos","innerHTML","");

	$db = new banco_dados;
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos, ".DATABASE.".nao_conformidades ";
	$sql .= "WHERE nao_conformidades.id_nao_conformidade = '".$id."' ";
	$sql .= "AND nao_conformidades_anexos.reg_del = 0 ";
	$sql .= "AND nao_conformidades.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades.id_nao_conformidade = nao_conformidades_anexos.id_nao_conformidade ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		
		foreach($db->array_select as $regs1)
		{
			$arq = pathinfo($regs1["anexo"]);
			
			//permite a exclusao dos anexos ao SGI
			if(in_array($_SESSION["id_funcionario"],$array_sgi))
			{
				$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir o arquivo?")){xajax_excluir_arquivo("'.$regs1["id_nao_conformidade_anexo"].'","'.$regs["nome_arquivo"] . '");};>';
			}
			else
			{
				//se coordenador e nao enviada
				if(in_array($_SESSION["id_funcionario"],$array_func) && $regs1["envio_email"]==2)
				{
					$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja excluir o arquivo?")){xajax_excluir_arquivo("'.$regs1["id_nao_conformidade_anexo"].'","'.$regs["nome_arquivo"] . '");};>';
				}
				else
				{
					$img = ' ';
				}
			}
			
			$xml->startElement('row');
				$xml->writeAttribute('id','tr_'.$regs1["anexo"]);
				$xml->writeElement ('cell',retornaImagem($arq["extension"]));				
				$xml->writeElement ('cell',$regs1["nome_arquivo"]);
				$xml->writeElement ('cell',$img);
			$xml->endElement();	

		}
	}
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_arquivos',true,'300','".$conteudo."');");
	
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
	
	$desc_evidencia = '';
	
	$desc_obs = '';
	
	$desc_encerramento = '';
	
	$array_func = permit_colab_sup();
	
	$array_sgi =  permit_colab_sgi();		
		
	$validacao = $dados_form["codigo"]!='' &&
				 ($dados_form["disciplina"]!='0' || $dados_form["cliente"]!='0') &&
				 $dados_form["id_originador"]!='' &&
				 $dados_form["data_nc"]!= '' &&
				 $dados_form["id_setor"]!= '' &&
				 $dados_form["desc_nc"]!= '' &&
				 $dados_form["desc_acao_imediata"]!= '' &&
				 $dados_form["desc_perdas"]!= '';	
	
	if($validacao)
	{	
		$outrosCliente = '';
		$outrosAcidente = '';
		$outros = '';
		$outrosIncidente = '';
		$outrosFornec = '';
		switch($dados_form['rd_tp_origem'])
		{
			case 1:
				$outrosCliente = maiusculas($dados_form["outros_cliente"]);
			break;
			case 2:
				$outrosAcidente = maiusculas($dados_form["outros_acidente"]);
			break;
			case 3:
				$outros = maiusculas($dados_form["outros"]);	
			break;
			case 4:
				$outrosIncidente = maiusculas($dados_form["outros_incidente"]);
			break;
			case 7:
				$outrosFornec = maiusculas($dados_form["outros_fornec"]);
			break;
		}
		
		$procedente = $dados_form["procedente"];

		$desc_encerramento = maiusculas($dados_form["desc_encerramento"]);
	
		$statusNc = $dados_form["status_nc"];
		$desc_evidencia = maiusculas($dados_form["desc_evidencia"]);
		
		$desc_obs = maiusculas($dados_form["desc_obs"]);
		
		$isql = "INSERT INTO ".DATABASE.".nao_conformidades ";
		$isql .= "(cod_nao_conformidade, id_os, data_criacao, id_funcionario_criador, id_funcionario_responsavel, 
					id_setor, id_disciplina, id_tipo_documento, id_tipo_origem, desc_outros, desc_outros_cliente, desc_outros_fornec, desc_outros_acidente,desc_outros_incidente, id_cliente, procedente, desc_nao_conformidade, 
					desc_acao_imediata, desc_obs, desc_evidencia, status, desc_encerramento, desc_perdas, desc_analise_causa, verificacao_eficacia_status, data) "; //, desc_eficacia, status, procedente, plano_acao, id_plano_acao)
		$isql .= "VALUES ('" . $dados_form["codigo"] . "', ";
		$isql .= "'" . $dados_form["escolhaos"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_nc"]) . "', ";
		$isql .= "'" . $dados_form["id_originador"] . "', ";
		$isql .= "'" . $dados_form["id_funcionario"] . "', ";
		$isql .= "'" . $dados_form["id_setor"] . "', ";
		$isql .= "'" . $dados_form["disciplina"] . "', ";
		$isql .= "'" . $dados_form["rd_doc_ref"] . "', ";
		$isql .= "'" . $dados_form["rd_tp_origem"] . "', ";
		$isql .= "'" . $outros . "', ";
		$isql .= "'" . $outrosCliente . "', ";
		$isql .= "'" . $outrosFornec . "', ";
		$isql .= "'" . $outrosAcidente . "', ";
		$isql .= "'" . $outrosIncidente . "', ";
		$isql .= "'" . $dados_form["cliente"] . "', ";
		$isql .= "'" . $procedente . "', ";
		$isql .= "'" . maiusculas($dados_form["desc_nc"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["desc_acao_imediata"]) . "', ";
		$isql .= "'" . $desc_obs . "', ";
		$isql .= "'" . $desc_evidencia . "', ";
		$isql .= "'" . $statusNc . "', ";
		$isql .= "'" . $desc_encerramento . "', ";
		$isql .= "'" . maiusculas($dados_form["desc_perdas"]) . "', ";
		$isql .= "'" . maiusculas($dados_form["desc_analise_causa"]) . "', ";
		$isql .= "'" . $dados_form["rd_eficacia"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_resp"]) . "') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro."-".$isql);				
		}
		else
		{
			$id_nc = $db->insert_id;
			
			//só insere os permitidos (SUPERVISÃO)
			if(in_array($_SESSION["id_funcionario"],$array_func) || in_array($_SESSION["id_funcionario"],$array_sgi))
			{
				foreach($dados_form["numero"] as $valor)
				{
					if($dados_form["acao"][$valor-1]!='' && $dados_form["responsavel"][$valor-1]!='0' && $dados_form["data"][$valor-1]!='' && $dados_form["status"][$valor-1]!='')
					{
						$isql = "INSERT INTO ".DATABASE.".planos_acoes_complementos ";
						$isql .= "(id_nao_conformidade, item_acao, plano_acao, id_funcionario_responsavel, prazo, status_plano_acao) ";
						$isql .= "VALUES ('" .$id_nc . "', ";
						$isql .= "'" . $dados_form["numero"][$valor-1] . "', ";
						$isql .= "'" . maiusculas(utf8_decode($dados_form["acao"][$valor-1])) . "', ";
						$isql .= "'" . $dados_form["responsavel"][$valor-1] . "', ";
						$isql .= "'" . php_mysql($dados_form["data"][$valor-1]) . "', ";
						$isql .= "'" . $dados_form["status"][$valor-1] . "') ";						

						$db->insert($isql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro."-".$isql);
						}
					}			
				}
				
				//salva anexos
				$resposta->addScript("anexos(".$id_nc.");");
			}
			
			if($status==1)
			{
				$resposta->addScript("xajax_email(".$id_nc.");");
			}
			
			$resposta->addAlert('Registro salvo corretamente!');
		}
		
		$resposta->addScript("window.location='./formulario_reporte.php'");		
	}
	else
	{
		$resposta->addAlert('Todos campos contendo "*" devem estar preenchidos');
	}
	
	return $resposta;
}

//status 0 - salva / 1 - salva e envia
function atualizar($dados_form, $status = 0)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);	
			
	$db = new banco_dados;
	
	$validacao = false;
	
	$array_func = permit_colab_sup();
	
	$array_sgi = permit_colab_sgi();	

	$validacao = $dados_form["codigo"]!='' &&
			 ($dados_form["disciplina"]!='0' || $dados_form["cliente"]!='0') &&
			 $dados_form["id_originador"]!='' &&
			 $dados_form["data_nc"]!= '' &&
			 $dados_form["id_setor"]!= '' &&
			 $dados_form["desc_nc"]!= '' &&
			 $dados_form["desc_acao_imediata"]!= '' &&
			 $dados_form["desc_perdas"]!= '';
	
	if($validacao)
	{
		//Pos validacao por causa do IE8
		if (!in_array($regs["envio_email"],array('0','2')) && !in_array($_SESSION["id_funcionario"],$array_sgi))
		{
			$resposta->addAlert('Voce nao possui permissao para realizar esta operacao');
			return $resposta;
		}
		
		$usql = "UPDATE ".DATABASE.".nao_conformidades SET ";
		
		//permite a salvar os campos aos funcionarios da SGI
		if(in_array($_SESSION["id_funcionario"],$array_sgi))
		{

			$usql .= "desc_encerramento = '" . maiusculas($dados_form["desc_encerramento"]) . "', ";
			$usql .= "status = '" . $dados_form["status_nc"] . "', ";
			$usql .= "procedente = '" . $dados_form["procedente"] . "', ";
		}
		
		if(in_array($_SESSION["id_funcionario"],$array_func) || in_array($_SESSION["id_funcionario"],$array_sgi))
		{
			$usql .= "desc_obs = '".maiusculas($dados_form["desc_obs"])."',";
			$usql .= "desc_evidencia = '".maiusculas($dados_form["desc_evidencia"])."',";
		}
		
		$outrosCliente = '';
		$outrosAcidente = '';
		$outros = '';
		$outrosIncidente = '';
		$outrosFornec = '';
		switch($dados_form['rd_tp_origem'])
		{
			case 1:
				$outrosCliente = maiusculas($dados_form["outros_cliente"]);
			break;
			case 2:
				$outrosAcidente = maiusculas($dados_form["outros_acidente"]);
			break;
			case 3:
				$outros = maiusculas($dados_form["outros"]);	
			break;
			case 4:
				$outrosIncidente = maiusculas($dados_form["outros_incidente"]);
			break;
			case 7:
				$outrosFornec = maiusculas($dados_form["outros_fornec"]);
			break;
		}
		
		$usql .= "desc_analise_causa = '" . maiusculas($dados_form["desc_analise_causa"]) . "', ";
		$usql .= "desc_nao_conformidade = '" . maiusculas($dados_form["desc_nc"]) . "', ";
		$usql .= "desc_acao_imediata = '" . maiusculas($dados_form["desc_acao_imediata"]) . "', ";
		$usql .= "desc_perdas = '" . maiusculas($dados_form["desc_perdas"]) . "', ";
		$usql .= "id_tipo_documento = '".$dados_form["rd_doc_ref"]."', ";
		$usql .= "id_tipo_origem = '".$dados_form["rd_tp_origem"]."', ";
		$usql .= "id_funcionario_responsavel = '" . $dados_form["id_funcionario"] . "', ";
		$usql .= "desc_outros = '".$outros."', ";
		$usql .= "id_os = '" . $dados_form["escolhaos"] . "', ";
		$usql .= "id_setor = '" . $dados_form["id_setor"] . "', ";
		$usql .= "id_disciplina = '" . $dados_form["disciplina"] . "', ";
		$usql .= "id_cliente = '" . $dados_form["cliente"] . "', ";
		$usql .= "desc_outros_cliente = '".$outrosCliente."', ";
		$usql .= "desc_outros_fornec = '".$outrosFornec."', ";
		$usql .= "desc_outros_acidente = '".$outrosAcidente."', ";
		$usql .= "desc_outros_incidente = '".$outrosIncidente."', ";
		$usql .= "desc_encerramento = '".$dados_form['desc_encerramento']."', ";
		$usql .= "verificacao_eficacia_status = '".$dados_form['rd_eficacia']."', ";
		$usql .= "data = '".php_mysql($dados_form["data_resp"])."' ";
		
		$usql .= "WHERE id_nao_conformidade = '".$dados_form["id"]."' ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			return $resposta;
		}
		else
		{
			//so insere os permitidos (SUPERVISAO)
			if(in_array($_SESSION["id_funcionario"],$array_func) || in_array($_SESSION["id_funcionario"],$array_sgi))
			{
				//Exclui todos os itens ja cadastrados para posterior insercao com alteracoes
				$usql = "UPDATE ".DATABASE.".planos_acoes_complementos SET plano_acao_complemento_delete = 1 ";
				$usql .= "WHERE id_nao_conformidade = '".$dados_form["id"]."' ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				else						
				{
					$nRegs = 0;
					$isql = "INSERT INTO ".DATABASE.".planos_acoes_complementos ";
					$isql .= "(id_nao_conformidade, item_acao, plano_acao, id_funcionario_responsavel, prazo, status_plano_acao) VALUES ";
					foreach($dados_form["acao"] as $k => $valor)
					{
						if(!empty($dados_form["acao"][$k]) && !empty($dados_form["responsavel"][$k]) && !empty($dados_form["data"][$k]) && $dados_form["status"][$k] != '')
						{
							$isql .= $sep."('" .$dados_form["id"] . "', ";
							$isql .= "'" . $dados_form["numero"][$k] . "', ";
							$isql .= "'" . maiusculas(utf8_decode($dados_form["acao"][$k])) . "', ";
							$isql .= "'" . $dados_form["responsavel"][$k] . "', ";
							$isql .= "'" . php_mysql($dados_form["data"][$k]) . "', ";
							$isql .= "'" . $dados_form["status"][$k] . "') ";						

							$sep = ',';
							$nRegs++;
						}
					}
					
					if ($nRegs > 0)
					{
						$db->insert($isql,'MYSQL');
	
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro."-".$isql);	
						}
					}
				}
					
				$resposta->addScript("anexos(".$dados_form["id"].");");

				$resposta->addAlert('Registro salvo corretamente!');
			}
			
			if($status==1)
			{
				$resposta->addScript("xajax_email(".$dados_form["id"].");");
			}
			
			$resposta->addScript("window.location='./formulario_reporte.php'");
		}		
	}
	else
	{
		$resposta->addAlert($msg[4]);
	}	

	return $resposta;
}
 //verificado
function email($id)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_func = permit_colab_sup();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$params 			= array();
	$params['from']		= "qualidade@".DOMINIO;
	$params['from_name']= "SGI";
	$params['subject'] 	= "NAO CONFORMIDADES INTERNAS";	
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".nao_conformidades ";
	$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (nao_conformidades.id_os = ordem_servico.id_os) ";
	$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades.id_funcionario_criador = funcionarios.id_funcionario ";
	$sql .= "AND nao_conformidades.id_setor = setores.id_setor ";
	$sql .= "AND nao_conformidades.id_nao_conformidade = '".$id."' ";	

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	switch ($regs["status"])
	{
		case 0:
			$status = "PENDENTE";
		break;
		
		case 1:
			$status = "EM ANALISE";
		break;
		
		case 2:
			$status = "ENCERRADO";
		break;
	}
	
	$procedente = $regs["procedente"]?"SIM":"NÃO";
	
	if($regs["os"]!=0)
	{
		$os = sprintf("%010d",$regs["os"]);
	}
	else
	{
		$os = "NÃO APLICÁVEL";	
	}
	
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE id_setor = '".$regs["id_disciplina"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$cont0 = $db->array_select[0];
	
	$sql = "SELECT *, unidades.descricao AS unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
	$sql .= "WHERE empresas.id_empresa = '".$regs["id_cliente"]."' ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$cont1 = $db->array_select[0];		
	
	$body = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"1\">
		  <tr>
			<td colspan=\"8\" align=\"left\"><strong>Documento:</strong> NÃO CONFORMIDADES INTERNAS</td>
		  </tr>
		  <tr>
			<td colspan=\"8\"><strong>Formulário: </strong>".$regs["cod_nao_conformidade"]." </td>
		  </tr>
		  <tr>
			<td colspan=\"3\"><strong>Revisão Nº:</strong> 0</td>
			<td colspan=\"5\"><strong>Data da Emissão:</strong> ".date('d/m/Y')." </td>
		  </tr>
		  <tr>
			<td colspan=\"3\"><strong>Originador:</strong> ".$regs["funcionario"]."</td>
			<td colspan=\"3\"><strong>Setor:</strong> ".$regs["setor"]."</td>
			<td colspan=\"2\"><strong>Data criação:</strong> ".mysql_php($regs["data_criacao"])."</td>
		  </tr>
		  <tr>
			<td colspan=\"4\"><strong>OS:</strong> ". $os."</td>
			<td colspan=\"4\"><strong>Status:</strong> ". $status ."</td>
		  </tr>			  
		  <tr>
			<td colspan=\"4\"><strong>Disciplina:</strong> ". $cont0["setor"]."</td>
			<td colspan=\"4\"><strong>Cliente:</strong> ". $cont1["empresa"]. " - ".$cont1["unidade"] ."</td>
		  </tr>
		  <tr>
			<td colspan=\"4\"><strong>Procedente:</strong> ". $procedente."</td>
			<td colspan=\"4\"><strong><!-- Plano de Ação Corretiva:</strong> ". $cont3["cod_plano_acao"]. " --></td>
		  </tr>
		  <tr>
			<td colspan=\"8\"><strong>Descrição da não conformidade:</strong></td>
		  </tr>
		  <tr>
			<td colspan=\"8\">". nl2br($regs["desc_nao_conformidade"]). " </td>
		  </tr>
		  <tr>
			<td colspan=\"8\"><strong>Ação Imediata:</strong></td>
		  </tr>
		  <tr>
			<td colspan=\"8\">". nl2br($regs["desc_acao_imediata"]). " </td>
		  </tr>
		  <tr>
			<td colspan=\"8\"><strong>Perdas:</strong></td>
		  </tr>
		  <tr>
			<td colspan=\"8\">". nl2br($regs["desc_perdas"]) ." </td>
		  </tr>
		  
		  <!--
		  
		  <tr>
			<td colspan=\"8\"><strong>Eficácia de Ação Corretiva / Preventiva:</strong></td>
		  </tr>
		  
		  <tr>
			<td colspan=\"8\">". nl2br($regs["desc_eficacia"]) ." </td>
		  </tr>
		  
		  -->
		  
		  <tr>		  
				<td colspan=\"8\"><strong>Ações Complementares:</strong></td>
			  </tr>			  
			  <tr>
				<td><strong>Item</strong></td>
				<td><strong>Ações</strong></td>
				<td colspan=\"4\"><strong>Responsável</strong></td>
				<td><strong>Prazo</strong></td>
				<td><strong>status</strong></td>
			  </tr>";			  
			  
			$sql = "SELECT funcionario, funcionarios.id_funcionario, usuarios.email, status_plano_acao, item_acao, prazo, plano_acao ";
			$sql .= "FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios, ".DATABASE.".usuarios  ";
			$sql .= "WHERE planos_acoes_complementos.id_nao_conformidade = '".$id."' ";
			$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
			$sql .= "AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario ";
			$sql .= "AND funcionarios.id_usuario = usuarios.id_usuario ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			if($db->numero_registros>0)
			{
				$array_pac = $db->array_select;
				
				foreach($array_pac as $regs1)
				{
					switch ($regs1["status_plano_acao"])
					{
						case 0:
							$status_acao = "PENDENTE";
						break;
						
						case 1:
							$status_acao = "EM ANDAMENTO";
						break;
						
						case 2:
							$status_acao = "ENCERRADO";
						break;
					}
					if($regs1["email"]!="")
					{
						$array_email[$regsa["funcionario"]] = $regs1["email"];
						
						//No chamado 1835, pois antes não eram eviados os emails para os colaboradores relacionados no plano de ação
						$params['emails']['to'][] = array('email' => $regs1['email'], 'nome' => $regs1['funcionario']);
					}
					
					$texto = explode("|",utf8_decode($regs1["plano_acao"]));
				
					$body .= "<tr>
								<td>".$regs1["item_acao"]." </td>
															
								<td >".implode("<br>\n",$texto)."</td>
								<td colspan=\"4\">".$regs1["funcionario"]." </td>
								<td >".mysql_php($regs1["prazo"])." </td>
								<td >".$status_acao." </td>
							  </tr>";
				
				}			  
			}
		  	
			$body .= "<tr>
				<td colspan=\"8\"><strong>Observações:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">".nl2br($regs["desc_obs"])." </td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Verificação da eficácia da(s) ação(ões):</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">".nl2br($regs["desc_encerramento"])." </td>
			  </tr>
		  
		  </table>";

	if(ENVIA_EMAIL)
	{

		$mail = new email($params, 'nao_conformidades');
		
		$mail->montaCorpoEmail($body);
		
		if(!$mail->Send())
		{
			$resposta->addAlert($mail->ErrorInfo);
		}
		else
		{
			$resposta->addAlert("E-mail enviado aos envolvidos.");
		}

		$mail->ClearAddresses();
	}
	else
	{
		$resposta->addScriptCall('modal', $body, '300_650', 'Conteúdo email', 1);
	}

	
	//coordenacao ou SGI, status do e-mail
	if(in_array($_SESSION["id_funcionario"],$array_func) || in_array($_SESSION["id_funcionario"],$array_sgi))
	{
		$envio_email = 3;
	}
	else
	{
		$envio_email = 1;	
	}
	
	$usql = "UPDATE ".DATABASE.".nao_conformidades SET ";
	$usql .= "envio_email = '".$envio_email."' ";
	$usql .= "WHERE id_nao_conformidade = '".$regs["id_nao_conformidade"]."' ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	return $resposta;
}

//verificado
function clientes($id_os)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($id_os!=0)
	{
		$sql = "SELECT *, unidades.descricao AS unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND ordem_servico.id_os = '".$id_os."' ";
		$sql .= "GROUP BY empresas.id_empresa ";
		$sql .= "ORDER BY empresa, unidades.descricao ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$cont = $db->array_select[0];
	
		$cliente = $cont["id_empresa"];
	}
	else
	{
		$cliente = 0;	
	}
	
	$resposta->addScript("seleciona_combo(".$cliente.",'cliente');");
	
	return $resposta;
}

//verificado
function excluir_arquivo($id_anexo)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$erro = false;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_RNC/";
	
	$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos ";
	$sql .= "WHERE nao_conformidades_anexos.id_nao_conformidade_anexo = '".$id_anexo."' ";

	$db->select($sql,'MYSQL',true);
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		$erro = true;
	}
	else
	{				
		$regs = $db->array_select[0];
		
		$del = unlink($diretorio.$regs["anexo"]);
		
		if(!$del)
		{
			$erro = true;	
		}					
		
		$usql = "UPDATE ".DATABASE.".nao_conformidades_anexos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "WHERE id_nao_conformidade_anexo = '".$regs["id_nao_conformidade_anexo"]."' ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}		
	}
	
	if(!$erro)
	{
		$resposta->addAlert("Anexo excluído do sistema.");
		
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
$xajax->registerFunction("email");
$xajax->registerFunction("acoes_complem");
$xajax->registerFunction("clientes");
$xajax->registerFunction("arq_anexos");
$xajax->registerFunction("excluir_arquivo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

$conf = new configs();

$db = new banco_dados;

$array_os_values[] = "0";
$array_os_output[] = "NAO APLICAVEL";

$array_cliente_values[] = "0";
$array_cliente_output[] =  "SELECIONE";

$array_disciplina_values[] = "0";
$array_disciplina_output[] =  "SELECIONE";

$array_pac_values[] = "0";
$array_pac_output[] = "SELECIONE";

$array_func_values[] = "";
$array_func_output[] = "SELECIONE";

$smarty->assign("revisao_documento","V8");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('nao_conformidades_internas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","NÃO CONFORMIDADES INTERNAS");

$smarty->assign("codigo","NC-".date('YmdHi'));

$smarty->assign("originador",$_SESSION["nome_usuario"]);

$smarty->assign("id_originador",$_SESSION["id_funcionario"]);

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
$sql .= "GROUP BY ordem_servico.os ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont2)
{
	$array_os_values[] = $cont2["id_os"];
	$array_os_output[] =  sprintf("%010d",$cont2["os"]);
}

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$cont = $db->array_select[0];

$smarty->assign("setor",$cont["setor"]);

$smarty->assign("id_setor",$cont["id_setor"]);

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont0)
{
	$array_disciplina_values[] = $cont0["id_setor"];
	$array_disciplina_output[] =  $cont0["setor"];
}

$sql = "SELECT *, unidades.descricao AS unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidades, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_empresa = empresas.id_empresa ";
$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
$sql .= "GROUP BY empresas.id_empresa ";
$sql .= "ORDER BY empresa, unidades.descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont1)
{
	$array_cliente_values[] = $cont1["id_empresa"];
	$array_cliente_output[] =  $cont1["empresa"]. " - ".$cont1["unidade"];
}

$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_planos_acao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$doc_ref = "<table width=\"100%\" border=\"0\">";

$i = 1;

foreach ($db->array_select as $cont1)
{
	$width = '';
		
	if($i%2)
	{
		$doc_ref .= "<tr align=\"center\"><td width=\"18%\"> </td>";
		$width = 'width=370px;';
	}

	if($cont1["id_tipo_documento"]==5)
	{
		$doc_ref .= "<td align=\"left\" ".$width." ><input type=\"radio\" name=\"rd_doc_ref\" id=\"rd_doc_ref\" value=\"".$cont1["id_tipo_documento"]."\" onclick=\"trocarSufixo('".$cont1['sufixo_codigo']."');document.getElementById('outros').style.backgroundColor='white';document.getElementById('outros').readOnly=false;document.getElementById('outros').focus();\" /><label class=\"labels\">".$cont1["tipo_documento"]."</label>";
		$doc_ref .= "  <input name=\"outros\" type=\"text\" class=\"caixa\" id=\"outros\" size=\"50\" readonly=\"readonly\" value=\"\" style=\"background-color:grey;\" /></td>";
	}
	else
	{
		$doc_ref .= "<td align=\"left\" ".$width."><input type=\"radio\" name=\"rd_doc_ref\"  id=\"rd_doc_ref\" value=\"".$cont1["id_tipo_documento"]."\" onclick=\"trocarSufixo('".$cont1['sufixo_codigo']."');\" /><label class=\"labels\">".$cont1["tipo_documento"]."</label></td>";
	}		

	if(!$i%2)
	{
		$doc_ref .= "</tr>";	
	}
	
	$i++;
}

$doc_ref .= "</table>";

//Adicionei em 09/08/2016
//Carlos Eduardo
$sql = "SELECT * FROM ".DATABASE.".tipo_origem ";
$sql .= "ORDER BY id_tipo_origem ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$tp_orig = "<table id=\"table_tp_origem\" width=\"100%\" border=\"0\">";

$i = 1;

$array_tipo = $db->array_select;

foreach ($array_tipo as $cont1)
{
	$width = '';
		
	if($i%2)
	{
		$tp_orig .= "<tr align=\"center\"><td width=\"18%\"> </td>";
		$width = 'width=370px;';
	}

	switch($cont1["id_tipo_origem"])
	{
		case 2:
			$tp_orig .= "<td align=\"left\" ".$width." ><input onblur=\"limpaTextNaoSelecionado();\" class=\"campoOriginador\" type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros_acidente').style.display='block';document.getElementById('outros_acidente').style.backgroundColor='white';document.getElementById('outros_acidente').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros_acidente\" type=\"text\" class=\"caixa campoOriginador\" id=\"outros_acidente\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		case 3:
			$tp_orig .= "<td align=\"left\" ".$width." ><input onblur=\"limpaTextNaoSelecionado();\" class=\"campoOriginador\" type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros').style.display='block';document.getElementById('outros').style.backgroundColor='white';document.getElementById('outros').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros\" type=\"text\" class=\"caixa campoOriginador\" id=\"outros\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		case 4:
			$tp_orig .= "<td align=\"left\" ".$width." ><input onblur=\"limpaTextNaoSelecionado();\" class=\"campoOriginador\" type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros_incidente').style.display='block';document.getElementById('outros_incidente').style.backgroundColor='white';document.getElementById('outros_incidente').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros_incidente\" type=\"text\" class=\"caixa campoOriginador\" id=\"outros_incidente\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		case 1:
			$tp_orig .= "<td align=\"left\" ".$width." ><input onblur=\"limpaTextNaoSelecionado();\" class=\"campoOriginador\" type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros_cliente').style.display='block';document.getElementById('outros_cliente').style.backgroundColor='white';document.getElementById('outros_cliente').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros_cliente\" type=\"text\" class=\"caixa campoOriginador\" id=\"outros_cliente\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		case 7:
			$tp_orig .= "<td align=\"left\" ".$width." ><input onblur=\"limpaTextNaoSelecionado();\" class=\"campoOriginador\" type=\"radio\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"document.getElementById('outros_fornec').style.display='block';document.getElementById('outros_fornec').style.backgroundColor='white';document.getElementById('outros_fornec').focus();\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= "  <input name=\"outros_fornec\" type=\"text\" class=\"caixa campoOriginador\" id=\"outros_fornec\" size=\"50\" value=\"\" style=\"background-color:grey;display:none;\" /></td>";
		break;
		case 6:
			
			$sql = "SELECT id_setor, setor FROM ".DATABASE.".setores";

			$db->select($sql,'MYSQL', true);
			
			$select = "<select id='sel_setores' name='sel_setores' class='caixa' style='display:none';><option value=''>Selecione</option>";
			
			foreach($db->array_select as $k => $v)
			{
				$select .= '<option value="'.$v['id_setor'].'">'.$v['setor'].'</option>';
			}
			
			$select .= "</select>";

			$tp_orig .= "<td align=\"left\" ".$width." ><input type=\"radio\" class=\"campoOriginador\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"limpaTextNaoSelecionado();document.getElementById('sel_setores').style.display='block';\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label>";
			$tp_orig .= $select."</td>";
		break;
		default:
			$tp_orig .= "<td align=\"left\" ".$width."><input type=\"radio\" class=\"campoOriginador\" name=\"rd_tp_origem\" id=\"rd_tp_origem\" value=\"".$cont1["id_tipo_origem"]."\" onclick=\"limpaTextNaoSelecionado();document.getElementById('nao_conf').disabled=true;\" /><label class=\"labels\">".$cont1["tipo_origem"]."</label></td>";
		break;
	}

	if(!$i%2)
	{
		$tp_orig .= "</tr>";	
	}
	
	$i++;
}

$tp_orig .= "</table>";

$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao = 'ATIVO' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont2)
{
	$array_func_values[] = $cont2["id_funcionario"];
	$array_func_output[] =  $cont2['funcionario'];
}

$smarty->assign("doc_ref",$doc_ref);
$smarty->assign("tp_orig",$tp_orig);

$smarty->assign("id",$_GET["id"]);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_disciplina_values",$array_disciplina_values);
$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);

$smarty->assign("option_func_values",$array_func_values);
$smarty->assign("option_func_output",$array_func_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('nao_conformidades_internas.tpl');


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
//Função que troca o sufixo do código NC, OC, AP, etc. de acordo com os dados cadastrados no BD
function trocarSufixo(sufixo)
{
	var codigo = document.getElementById('codigo').value;
	codigo = codigo.split('-');
	codigo = sufixo+'-'+codigo[1];
	document.getElementById('codigo').value = codigo;		
}

//função que altera o valor de todas as selects dos planos de ação quando alterar o status
function alterar_status_acoes(status)
{
	document.getElementById('status_1').checked = true;	
	return true;
}

function verificar_status_acoes()
{
	var sels = document.getElementsByName('status[]');
	var qtdEncerrados = 0;
	
	for (i=0; i<sels.length; i++)
	{
	   if (sels[i].value == 2)
	   {
			qtdEncerrados++;
	   }
	}

	if (qtdEncerrados == sels.length && sels.length > 0)
	{
		document.getElementById('tr_verificacao_eficacia').style.display = 'block';
	}
	else
	{
		
		document.getElementById('tr_verificacao_eficacia').style.display = 'none';
	}

	return true;
}

//funcao que adiciona campos no div
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

/**
 * Alterei esta funcao criando mygrid1 pois a funcao add_camp estava se perdendo ao adicionar uma nova linha.
 * 08/09/2016 - Eduardo
 */
function grid(tabela, autoh, height, xml)
{
	switch (tabela)
	{
		case 'div_acao_complementar':

			mygrid1 = new dhtmlXGridObject(tabela);
			
			mygrid1.enableAutoHeight(autoh,height);

			mygrid1.enableRowsHover(true,'cor_mouseover');

			this.add_camp = function()
			{
				hoje = new Date();
				dia = hoje.getDate();
				mes = hoje.getMonth();
				ano = hoje.getFullYear();
				
				if (dia < 10)
					dia = "0" + dia
				if (ano < 2000)
					ano = "19" + ano
				mes = mes+1;
				if (mes < 10)
					mes = "0" + mes
				
				var num_elements = document.getElementById("itens").value;
				
				num_elements++;
				
				//copia a linha do grid
				mygrid1.addRow("tr"+num_elements,"");
				
				mygrid1.copyRowContent("tr1","tr"+num_elements);
				
				//seta os valores default
				var numero = document.getElementsByName('numero[]');
				var acao = document.getElementsByName('acao[]');
				var data = document.getElementsByName('data[]');
				var responsavel = document.getElementsByName('responsavel[]');
				var status = document.getElementsByName('status[]');
				
				for(i=0; i< numero.length;i++)
				{
					numero[i].value = i+1;		
				}
				
				acao[numero.length-1].value = '';
				acao[numero.length-1].readOnly = '';
				
				data[numero.length-1].value = dia+'/'+mes+'/'+ano;
				data[numero.length-1].readOnly = '';
				
				responsavel[numero.length-1].selectedIndex = 0;
				responsavel[numero.length-1].disabled = '';
				
				status[numero.length-1].selectedIndex = 0;
				status[numero.length-1].disabled = '';
				
				document.getElementById("itens").value = num_elements;

				alterar_status_acoes();
			}
			
			mygrid1.setHeader("Nº,Ações,Responsável,Prazo,Status",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center"]);
			mygrid1.setInitWidths("30,*,250,80,150");
			mygrid1.setColAlign("center,left,left,left,left");
			mygrid1.setColTypes("ro,ro,ro,ro,ro");
			mygrid1.setColSorting("str,str,str,str,str");
			
			mygrid1.setSkin("dhx_skyblue");
			mygrid1.enableMultiselect(true);
			mygrid1.enableCollSpan(true);	
			mygrid1.init();
			mygrid1.loadXMLString(xml);
		break;
		
		case 'div_arquivos':
			mygrid = new dhtmlXGridObject(tabela);
			
			mygrid.enableAutoHeight(autoh,height);
			mygrid.enableRowsHover(true,'cor_mouseover');

			function doOnRowSelected1(row,col)
			{
				if(col<=1)
				{
					arq = row.split("_");
					
					open_file(arq[1],'ANEXOS_RNC');
		
					return true;
				}
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);	
		
			mygrid.setHeader(" ,Nome Arquivo,D",
				null,
				["text-align:center","text-align:left","text-align:left"]);
			mygrid.setInitWidths("30,*,80");
			mygrid.setColAlign("center,left,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");

			mygrid.setSkin("dhx_skyblue");
			mygrid.enableMultiselect(true);
			mygrid.enableCollSpan(true);	
			mygrid.init();
			mygrid.loadXMLString(xml);
		break;
	}
}

function anexos(id_nc)
{
	document.getElementById('id').value = id_nc;

	document.getElementById('frm').submit();		
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

/**
 * função que limpa e oculta os descritivos que não estão sendo usados na DIV tipo de origem
 */
function limpaTextNaoSelecionado()
{
	var c = document.getElementById("table_tp_origem").getElementsByTagName('input');
	for(i=0;i<=c.length;i++)
	{
	    if (c[i].type == 'radio' && c[i].checked)
	    {
	      if (c[i].value == "1")
	      {
	        document.getElementById('outros').value = '';
	        document.getElementById('outros_fornec').value = '';
	        document.getElementById('outros_acidente').value = '';
	        document.getElementById('outros_incidente').value = '';
	        document.getElementById('outros').style.display = 'none';
	        document.getElementById('outros_fornec').style.display = 'none';
	        document.getElementById('outros_acidente').style.display = 'none';
	        document.getElementById('outros_incidente').style.display = 'none';
	      }
	      else if (c[i].value == "2")
	      {
	    	document.getElementById('outros').value = '';
	        document.getElementById('outros_cliente').value = '';
	        document.getElementById('outros_fornec').value = '';
	        document.getElementById('outros_incidente').value = '';
	        document.getElementById('outros').style.display = 'none';
	        document.getElementById('outros_cliente').style.display = 'none';
	        document.getElementById('outros_fornec').style.display = 'none';
	        document.getElementById('outros_incidente').style.display = 'none';
	      }
	      else if (c[i].value == "3")
	      {
	        document.getElementById('outros_cliente').value = '';
	        document.getElementById('outros_fornec').value = '';
	        document.getElementById('outros_acidente').value = '';
	        document.getElementById('outros_incidente').value = '';
	        document.getElementById('outros_cliente').style.display = 'none';
	        document.getElementById('outros_fornec').style.display = 'none';
	        document.getElementById('outros_acidente').style.display = 'none';
	        document.getElementById('outros_incidente').style.display = 'none';
	      }
	      else if (c[i].value == "4")
	      {
	    	document.getElementById('outros').value = '';
	        document.getElementById('outros_cliente').value = '';
	        document.getElementById('outros_fornec').value = '';
	        document.getElementById('outros_acidente').value = '';
	        document.getElementById('outros').style.display = 'none';
	        document.getElementById('outros_cliente').style.display = 'none';
	        document.getElementById('outros_fornec').style.display = 'none';
	        document.getElementById('outros_acidente').style.display = 'none';
	      }
	      else if (c[i].value == "7")
	      {
	        document.getElementById('outros').value = '';
	        document.getElementById('outros_cliente').value = '';
	        document.getElementById('outros_acidente').value = '';
	        document.getElementById('outros_incidente').value = '';
	        document.getElementById('outros').style.display = 'none';
	        document.getElementById('outros_cliente').style.display = 'none';
	        document.getElementById('outros_acidente').style.display = 'none';
	        document.getElementById('outros_incidente').style.display = 'none';
	      }
	      else
	      {
	        document.getElementById('outros').value = '';
	        document.getElementById('outros_cliente').value = '';
	        document.getElementById('outros_fornec').value = '';
	        document.getElementById('outros_acidente').value = '';
	        document.getElementById('outros_incidente').value = '';
	        document.getElementById('outros').style.display = 'none';
	        document.getElementById('outros_fornec').style.display = 'none';
	        document.getElementById('outros_cliente').style.display = 'none';
	        document.getElementById('outros_acidente').style.display = 'none';
	      }

	      return true;  
	    }
	}
}
</script>