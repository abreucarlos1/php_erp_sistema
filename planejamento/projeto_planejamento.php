<?php
/*
	Formul�rio de Planejamento Projeto	
	
	Criado por Carlos Abreu / Carlos M�xim ia
	
	local/Nome do arquivo:
	../planejamento/projeto_planejamento.php
	
	Vers�o 0 --> VERS�O INICIAL - Carlos Abreu - 01/03/2017
	Vers�o 1 --> atualiza��o layout - Carlos Abreu - 03/04/2017	
	Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function status_proposta($id_proposta)
{
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$id_proposta."' ";

	$db->select($sql,'MYSQL',true);
	
	$cont = $db->array_select[0];
	
	$status = $cont["id_status_projeto"];
	
	return $status;
}

function dados_proposta($numero_proposta)
{
	$db = new banco_dados;

	$array_dados = NULL;
	
	$sql = "SELECT REPLACE(AF1_DESCRI, '�', '-') AF1_DESCRI_TRATADO, * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_CLIENT = A1_COD ";
	$sql .= "AND AF1_LOJA = A1_LOJA ";
	$sql .= "AND AF1_ORCAME = '".$numero_proposta."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	else
	{
		$regs = $db->array_select[0];
		
		$array_dados["orcamento"] = $regs["AF1_ORCAME"];
		$array_dados["descricao"] = trim($regs["AF1_DESCRI_TRATADO"]);
		$array_dados["cliente"] = trim($regs["A1_NOME"]);
		$array_dados["apelido"] = trim($regs["A1_APELIDO"]);
	}
	
	return $array_dados;
			
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("div_control_escopo_geral", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_escopo_geral", "style.display", "none");
	
	$resposta->addAssign("div_control_escopo_detalhado", "style.visibility", "hidden");

	$resposta->addAssign("div_control_resumo", "style.visibility", "hidden");
		
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND fase_orcamento IN ('09','04') ";
	$sql .= "AND id_status_proposta IN ('5') ";
	
	if($dados_form["status"]!=0)
	{
		$sql .= "AND propostas.id_status_projeto = '".$dados_form["status"]."' ";
	}

	$sql .= "ORDER BY propostas.numero_proposta DESC ";

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont)
	{
		$importar = '&nbsp;';
		
		switch ($cont["id_status_projeto"])
		{
			case 1:	//N�O IMPORTADO
					
				$status = '<img src="'.DIR_IMAGENS.'led_vm.png">';
				
				$titulo = utf8_encode('OR�AMENTO N�O IMPORTADO');
				
				$importar = '<img src="'.DIR_IMAGENS.'arrow_down.png" onclick = if(confirm("Deseja&nbsp;importar&nbsp;o&nbsp;or�amento&nbsp;t�cnico&nbsp;para&nbsp;o&nbsp;Aloca&ccedil;&atilde;o?")){xajax_importar("'.$cont["id_proposta"].'");}>';
				
							
			break;
			
			case 2: //IMPORTADO
			
				$status = '<img src="'.DIR_IMAGENS.'led_am.png">';
				
				$titulo = utf8_encode('OR�AMENTO IMPORTADO');
				
				$importar = '<img src="'.DIR_IMAGENS.'arrow_up.png" onclick = if(confirm("Deseja&nbsp;exportar&nbsp;o&nbsp;or�amento&nbsp;t�cnico&nbsp;para&nbsp;o&nbsp;Protheus?")){xajax_exportar("'.$cont["id_proposta"].'");}>'; 
	
			break;
			
			case 3: //EXPORTADO PROTHEUS
			
				$status = '<img src="'.DIR_IMAGENS.'led_vd.png">';
				
				$titulo = utf8_encode('OR�AMENTO EXPORTADO');
				
				$importar = '&nbsp;'; 
	
			break;		
		}
		
		if($cont["fase_orcamento"]=='04' && $cont["id_status_projeto"]==1)
		{
			$status = '<img src="'.DIR_IMAGENS.'led_az.png">';
			$importar = '&nbsp;';
			$titulo = utf8_encode('OR�AMENTO EXPORTADO CONCLU&Iacute;DO');	
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','prop_'.$cont["id_proposta"]);
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($status);
			$xml->endElement();
			$xml->writeElement ('cell',$cont["numero_proposta"]);
			$xml->writeElement ('cell',str_replace("'", '', $cont["descricao_proposta"]));
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($importar);
			$xml->endElement();

		$xml->endElement();	
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_dados_cliente',true,'420','".$conteudo."');");
	
	$resposta->addAssign("btn_escopo","value","Inserir");
	
	$resposta->addAssign("h_escopogeral","value","");
	
	$resposta->addAssign("escopogeral","value","");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$temp = explode('_',$id);
	
	$id = $temp[1];
	
	switch ($temp[0])
	{
		case 'prop':	
			
			//carrega proposta
			$sql = "SELECT * FROM ".DATABASE.".propostas ";
			$sql .= "WHERE propostas.reg_del = 0 ";
			$sql .= "AND propostas.id_proposta = '".$id."' ";
		
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			$resposta->addAssign("nr_proposta", "innerHTML",$cont["numero_proposta"]);
			
			$resposta->addAssign("id_proposta", "value",$cont["id_proposta"]);
			
			$resposta->addAssign("descricao_proposta", "innerHTML",$cont["descricao_proposta"]);
			
			$resposta->addAssign("div_escopo_detalhado","innerHTML","");
			
			$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
			
			//se status projeto for maior que 1, habilita o relatorio
			if($cont["id_status_projeto"]>=2)
			{
				$resposta->addAssign("btnimprimir","disabled","");
			}
			else
			{
				$resposta->addAssign("btnimprimir","disabled","disabled");
			}
			
			//se status projeto for exportado, desabilita os bot�es
			if($cont["id_status_projeto"]==3)
			{
				$resposta->addAssign("btn_escopo","disabled","disabled");
			}
			else
			{
				$resposta->addAssign("btn_escopo","disabled","");
			}	
			
			//escopo geral
			$resposta->addAssign("div_control_escopo_geral", "style.visibility", "");
			
			$resposta->addAssign("div_control_escopo_geral", "style.display", "");
			
			//escopo detalhado
			$resposta->addAssign("div_control_escopo_detalhado", "style.visibility", "");	
			
			$resposta->addAssign("div_control_resumo", "style.visibility", "");
			
			$resposta->addScript("xajax_preenche_disciplina(xajax.getFormValues('frm'));");
			
		break;
		
		case 'escopgeral':

			//seleciona os escopos gerais
			$sql = "SELECT * FROM ".DATABASE.".propostas, planejamento.escopo_geral ";
			$sql .= "WHERE escopo_geral.reg_del = 0 ";
			$sql .= "AND propostas.reg_del = 0 ";
			$sql .= "AND escopo_geral.id_escopo_geral = '".$id."' ";
			$sql .= "AND escopo_geral.id_proposta = propostas.id_proposta ";
			
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			//se status projeto for exportado, desabilita os bot�es
			if($cont["id_status_projeto"]==3)
			{
				$resposta->addAssign("btn_escopo","disabled","disabled");
			}
			else
			{
				$resposta->addAssign("btn_escopo","disabled","");
				
				$resposta->addAssign("escopogeral", "value",$cont["escopo_geral"]);
				
				$resposta->addAssign("h_escopogeral", "value",$cont["id_escopo_geral"]);
				
				$resposta->addAssign("btn_escopo","value","Atualizar");	
				
			}	
			
		break;

	}
	
	return $resposta;	
}

function inc_escopogeral($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","�","`");
	
	//n�o existe o escopo, insere
	if($dados_form["h_escopogeral"]=='' || $dados_form["h_escopogeral"]==0)
	{
		$isql = "INSERT INTO planejamento.escopo_geral (id_proposta, escopo_geral ) VALUES (";
		$isql .= "'" . $dados_form["id_proposta"] . "', ";
		$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["escopogeral"])))). "') ";
		
		$db->insert($isql,'MYSQL');	
	}
	else
	{
		$usql = "UPDATE planejamento.escopo_geral SET ";
		$usql .= "escopo_geral = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["escopogeral"])))) . "' ";
		$usql .= "WHERE id_escopo_geral = '".$dados_form["h_escopogeral"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');			
	}
	
	//seta o campo hidden
	$resposta->addAssign("h_escopogeral","value","");
	
	$resposta->addAssign("escopogeral","value","");
	  
	$resposta->addAssign("btn_escopo","value","Inserir");
	
	$resposta->addScript("xajax_preencheEscopoGeral(xajax.getFormValues('frm'));");	
	
	$resposta->addAlert("Atualizado com sucesso.");
	
	return $resposta;
}

function del_escopogeral($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{	
		//pega o numero da proposta
		$sql = "SELECT * FROM ".DATABASE.".propostas, planejamento.escopo_geral ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND propostas.reg_del = 0 ";
		$sql .= "AND escopo_geral.id_escopo_geral = '".$id."' ";
		$sql .= "AND escopo_geral.id_proposta = propostas.id_proposta ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];
		
		//se status projeto for exportado, desabilita os bot�es
		if($cont["id_status_projeto"]!=3)
		{					
			if(!empty($id))
			{				
				$usql = "UPDATE planejamento.escopo_geral SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "data_del = '".date("Y-m-d")."' ";
				$usql .= "WHERE escopo_geral.id_escopo_geral = '".$id."' ";
				$usql .= "AND escopo_geral.reg_del = 0 ";
			
				$db->update($usql,'MYSQL');
				
				$usql = "UPDATE planejamento.escopo_detalhado SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "data_del = '".date("Y-m-d")."' ";
				$usql .= "WHERE escopo_detalhado.id_escopo_geral = '".$id."' ";
				$usql .= "AND escopo_detalhado.reg_del = 0 ";
			
				$db->update($usql,'MYSQL');
				
				$usql = "UPDATE planejamento.recursos SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "data_del = '".date("Y-m-d")."' ";
				$usql .= "WHERE recursos.id_escopo_geral = '".$id."' ";
				$usql .= "AND recursos.reg_del = 0 ";
			
				$db->update($usql,'MYSQL');		
							
			}
			
			$resposta->addAssign("h_escopogeral", "value","");
			
			$resposta->addAssign("escopogeral", "value","");
			
			$resposta->addAssign("btn_escopo","value","Inserir");
			
			$resposta->addScript("xajax_preencheEscopoGeral(xajax.getFormValues('frm'));");		
		}

	}
	
	return $resposta;
}

function preencheEscopoGeral($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();	

	$db = new banco_dados;
	
	//se proposta estiver exportado, desabilita a exclus�o
	if(status_proposta($dados_form["id_proposta"])==3)
	{
		$visivel = "visibility:hidden;";	
	}
	else
	{
		$visivel = "visibility:visible;";
	}	

	//seleciona os escopos gerais
	$sql = "SELECT * FROM planejamento.escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "ORDER BY escopo_geral ";
	
	$db->select($sql,'MYSQL',true);
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont1)
	{			
		$sql = "SELECT * FROM planejamento.escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$cont1["id_escopo_geral"]."' ";
		
		$db->select($sql,'MYSQL',true);
	
		if($db->numero_registros==0)
		{
			$txt = 'if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;escopo&nbsp;geral?")){xajax_del_escopogeral('.$cont1["id_escopo_geral"].');};';
		}
		else
		{
			$txt = 'if(confirm("Existem&nbsp;tarefas&nbsp;associadas&nbsp;a&nbsp;este&nbsp;escopo,&nbsp;tem&nbsp;certeza&nbsp;que&nbsp;ir�&nbsp;excluir?")){xajax_del_escopogeral('.$cont1["id_escopo_geral"].');};';
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','escopgeral_'.$cont1["id_escopo_geral"]);
			$xml->writeElement('cell',$cont1["escopo_geral"]);
			$xml->writeElement ('cell', '<img style="cursor:pointer;'.$visivel.'" src="'.DIR_IMAGENS.'apagar.png" onclick = '.$txt.'>');
		$xml->endElement();	
	}
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_escopo_geral',true,'410','".$conteudo."');");
	
	return $resposta;		
}

function seleciona_escopo_geral($dados_form)
{
	$resposta = new xajaxResponse();	

	$db = new banco_dados;
	
	$disable = '';
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '". $dados_form["id_proposta"]."' ";

	$db->select($sql,'MYSQL',true);
	
	$regs0 = $db->array_select[0];
	
	$sql = "SELECT * FROM planejamento.escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '". $dados_form["id_proposta"]."' ";
	$sql .= "ORDER BY escopo_geral ";

	$db->select($sql,'MYSQL',true);
	
	$combo = '<select id="sel_escopo_geral" name="sel_escopo_geral" class="caixa" '.$disable.' onchange=xajax_mostra_tarefas(xajax.getFormValues("frm",true));>';
	
	$combo .= '<option value="0">SELECIONE</option>';
	
	$array_escopo = $db->array_select;
	
	foreach ($array_escopo as $regs)
	{		
		$style = "";
		
		if($dados_form["sel_escopo_geral"]==$regs["id_escopo_geral"])
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';	
		}
		
		$sql = "SELECT * FROM planejamento.escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '". $regs["id_escopo_geral"]."' ";

		$db->select($sql,'MYSQL',true);

		foreach($db->array_select as $regs1)
		{
			$array[$regs["id_escopo_geral"]][] = $regs1["status_escopo"];	  
		}
		
		$combo .= '<option value="'.$regs["id_escopo_geral"].'" '.$selected.'>'.$regs["escopo_geral"].'</option>';
	}
	
	$combo .= '</select>';
	
	$resposta->addAssign("escop", "innerHTML", $combo);
	
	return $resposta;
}

function mostra_tarefas($dados_form)
{
	$resposta = new xajaxResponse();
		
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$block = false;
	
	$select = "";
	
	$status = status_proposta($dados_form["id_proposta"]);
	
	if($status>=3)
	{
		$resposta->addAssign("btn_escopodet","disabled","disabled");
		
		$disabled_prop = true;
	}
	else
	{
		$resposta->addAssign("btn_escopodet","disabled","");
		
		$disabled_prop = false;
	}
	
	$sql = "SELECT * FROM ".DATABASE.".formatos ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_formatos[$regs["id_formato"]] = $regs["formato"];	
	}	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE atividades.cod = '" . $dados_form["disciplina"] . "' ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.obsoleto = 0 ";	
	$sql .= "GROUP BY atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
  
	$db->select($sql,'MYSQL',true);
	
	$array_atividades = $db->array_select;
	
	foreach($array_atividades as $regs)
	{	
		$disabled = "disabled";			
		
		$id_escopo_detalhado = 0;
		
		//verifica se existe registro no escopo detalhado
		$sql = "SELECT * FROM planejamento.escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$dados_form["sel_escopo_geral"]."' ";
		$sql .= "AND escopo_detalhado.id_tarefa = '".$regs["id_atividade"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		//se existir
		if($db->numero_registros>0)
		{
			$indice = 0;
			
			foreach($db->array_select as $regs_esc)
			{				
				//se projeto importado, habilita os inputs
				if($status<3)
				{
					$disabled_check = '';
					
					$disabled = '';
				}
				else
				{
					$disabled_check = 'disabled';
				}
				
				//combo de formatos
				$combof = '<select lang="cb_fmt_'.$regs["id_atividade"].'" class="cb_fmt_'. $regs["id_atividade"] . '" id="cb_fmt[' . $regs["id_atividade"] . '][]" name="cb_fmt[' . $regs["id_atividade"] . '][]" '.$disabled.' onkeypress = return keySort(this);>';
				
				$combof .= '<option value="0">SELECIONE</option>';
				
				foreach ($array_formatos as $id_formato=>$formato)
				{
					$select_fmt = '';
					
					if($regs_esc["id_formato"]==$id_formato)
					{
						$select_fmt = 'selected';
						
						$checked = 'checked';
						
						$delreg = 0;
					}
					else
					{
						$select_fmt = '';	
					}
					
					$combof .= '<option value="'.$id_formato.'" '.$select_fmt.'>'.$formato.'</option>';
				}	
				
				$combof .= '</select>';				
							
				$checked = 'checked';				
				
				$desc_ativ = $regs_esc["descricao_escopo"];
				
				$id_escopo_detalhado = $regs_esc["id_escopo_detalhado"];
				
				$xml->startElement('row');
							
					$xml->writeAttribute('id',$regs["id_atividade"].'_'.$indice);
					
					$xml->startElement ('cell');
						$xml->writeAttribute('title',utf8_encode('DUPLICAR&nbsp;TAREFA'));
						$xml->writeAttribute('style','background-color:#FFFFFF');
						$xml->text('<img src="'.DIR_IMAGENS.'add.png" '.$disabled_check.' onclick = if(confirm("Deseja&nbsp;duplicar&nbsp;a&nbsp;tarefa?")){adiciona_linha(mygrid.getRowIndex("'.$regs["id_atividade"].'_'.$indice.'"))} >');
					$xml->endElement();
					
					$xml->writeElement ('cell','<input type="checkbox" lang="chk_escopodet_'.$regs["id_atividade"].'" class="chk_escopodet_'. $regs["id_atividade"] . '" id="chk_escopodet_'. $regs["id_atividade"] . '['.$indice.']" name="chk_escopodet['. $regs["id_atividade"] . ']['.$indice.']" value="1" '.$select.' '.$checked.' '.$disabled_check.' onclick = lib_campos(this);>');
					
					$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_'.$regs["id_atividade"].'" id="chk_codigo[' . $regs["id_atividade"] . '][]" name="chk_codigo[' . $regs["id_atividade"] . '][]" value="'.substr($regs["codigo"],0,3).'">');
					$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_'.$id_escopo_detalhado.'" id="chk_del[' . $id_escopo_detalhado . '][]" name="chk_del[' . $id_escopo_detalhado . '][]" value="">');
					$xml->writeElement ('cell','<input lang="txt_descativ_'.$regs["id_atividade"].'"  class="txt_descativ_'. $regs["id_atividade"] . '" id="txt_descativ[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_descativ[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="70" '.$disabled.'  value="'.$desc_ativ.'" />');
					
					$xml->writeElement ('cell',$combof.'<input lang="hd_fmt_'.$regs["id_atividade"].'" class="hd_fmt_'. $regs["id_atividade"] . '" id="hd_fmt[' . $regs["id_atividade"] . '][]" name="hd_fmt[' . $regs["id_atividade"] . '][]" type="hidden" value="'.$regs["id_formato"].'" />');
					
					$xml->writeElement ('cell','<input lang="txt_qtd_'.$regs["id_atividade"].'" class="txt_qtd_'. $regs["id_atividade"] . '" id="txt_qtd[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_qtd[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="15" '.$disabled.'  value="'.$regs_esc["quantidade"].'" onkeypress = num_only(); /><input lang="hd_qtd_'.$regs["id_atividade"].'" class="hd_qtd_'. $regs["id_atividade"] . '" id="hd_qtd[' . $regs["id_atividade"] . ']['.$indice.']" name="hd_qtd[' . $regs["id_atividade"] . ']['.$indice.']" type="hidden" value="'.$regs_esc["quantidade_orcada"].'"/>');
					
					$xml->writeElement ('cell','<input lang="txt_horas_'.$regs["id_atividade"].'" class="txt_horas_'. $regs["id_atividade"] . '" id="txt_horas[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_horas[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="15" '.$disabled.'  value="'.$regs_esc["horas"].'" onkeypress = num_only(); /><input lang="hd_horas_'.$regs["id_atividade"].'" class="hd_horas_'. $regs["id_atividade"] . '" id="hd_horas[' . $regs["id_atividade"] . ']['.$indice.']" name="hd_horas[' . $regs["id_atividade"] . ']['.$indice.']" type="hidden"  value="'.$regs_esc["horas_orcada"].'"/>');
				
				$xml->endElement();	
				
				$indice++;
			}
		}
		else
		{
			//se projeto importado, habilita o checkbox
			if($status<3)
			{
				$disabled_check = '';
			}
			else
			{
				$disabled_check = 'disabled';
			}
			
			//combo de formatos
			$combof = '<select lang="cb_fmt_'.$regs["id_atividade"].'" class="cb_fmt_'. $regs["id_atividade"] . '" id="cb_fmt[' . $regs["id_atividade"] . '][0]" name="cb_fmt[' . $regs["id_atividade"] . '][0]" '.$disabled.' onkeypress = return keySort(this);>';
			
			$combof .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_formatos as $id_formato=>$formato)
			{
				if($regs["id_formato"]==$id_formato)
				{
					$select_fmt = 'selected';
				}
				else
				{
					$select_fmt = '';	
				}
				
				$combof .= '<option value="'.$id_formato.'" '.$select_fmt.'>'.$formato.'</option>';
			}	
			
			$combof .= '</select>';	
			
			$checked = '';
			
			$desc_ativ = $regs["descricao_escopo"];	

			$xml->startElement('row');
						
				$xml->writeAttribute('id',$regs["id_atividade"].'_0');
				
				$xml->startElement ('cell');
					$xml->writeAttribute('title',utf8_encode($regs["descricao"]));
					$xml->writeAttribute('style','background-color:#FFFFFF');
					$xml->text('<img src="'.DIR_IMAGENS.'add.png" '.$disabled_ckeck.' onclick = if(confirm("Deseja&nbsp;duplicar&nbsp;a&nbsp;tarefa?")){adiciona_linha(mygrid.getRowIndex("'.$regs["id_atividade"].'_0"))} >');
				$xml->endElement();				
				
				$xml->writeElement ('cell','<input type="checkbox" lang="chk_escopodet_'.$regs["id_atividade"].'" class="chk_escopodet_'. $regs["id_atividade"] . '" id="chk_escopodet['. $regs["id_atividade"] . '][0]" name="chk_escopodet['. $regs["id_atividade"] . '][0]" value="1" '.$select.' '.$checked.' '.$disabled_check.' onclick = lib_campos(this);>');
				
				$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_'.$regs["id_atividade"].'" id="chk_codigo[' . $regs["id_atividade"] . '][0]" name="chk_codigo[' . $regs["id_atividade"] . '][0]" value="'.substr($regs["codigo"],0,3).'">');
				$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_'.$id_escopo_detalhado.'" id="chk_del[' . $id_escopo_detalhado . '][0]" name="chk_del[' . $id_escopo_detalhado . '][0]" value="">');
				$xml->writeElement ('cell','<input lang="txt_descativ_'.$regs["id_atividade"].'" class="txt_descativ_'. $regs["id_atividade"] . '" id="txt_descativ[' . $regs["id_atividade"] . '][0]" name="txt_descativ[' . $regs["id_atividade"] . '][0]" type="text" size="70" '.$disabled.'  value="'.$desc_ativ.'" />');
								
				$xml->writeElement ('cell',$combof.'<input lang="hd_fmt_'.$regs["id_atividade"].'" class="hd_fmt_'. $regs["id_atividade"] . '" id="hd_fmt[' . $regs["id_atividade"] . '][0]" name="hd_fmt[' . $regs["id_atividade"] . '][0]" type="hidden" value="'.$regs["id_formato"].'" />');
				
				$xml->writeElement ('cell','<input lang="txt_qtd_'.$regs["id_atividade"].'" class="txt_qtd_'. $regs["id_atividade"] . '" id="txt_qtd[' . $regs["id_atividade"] . '][0]" name="txt_qtd[' . $regs["id_atividade"] . '][0]" type="text" size="15" '.$disabled.'  value="'.$regs["quantidade"].'" onkeypress = num_only(); /><input lang="hd_qtd_'.$regs["id_atividade"].'" class="hd_qtd_'. $regs["id_atividade"] . '" id="hd_qtd[' . $regs["id_atividade"] . '][0]" name="hd_qtd[' . $regs["id_atividade"] . '][0]" type="hidden" value="'.$regs["quantidade_orcada"].'" />');
				
				$xml->writeElement ('cell','<input lang="txt_horas_'.$regs["id_atividade"].'" class="txt_horas_'. $regs["id_atividade"] . '" id="txt_horas[' . $regs["id_atividade"] . '][0]" name="txt_horas[' . $regs["id_atividade"] . '][0]" type="text" size="15" '.$disabled.'  value="'.$regs["horas"].'" onkeypress = num_only(); /><input lang="hd_horas_'.$regs["id_atividade"].'" class="hd_horas_'. $regs["id_atividade"] . '" id="hd_horas[' . $regs["id_atividade"] . '][0]" name="hd_horas[' . $regs["id_atividade"] . '][0]" type="hidden"  value="'.$regs["horas_orcada"].'" />');
				
			$xml->endElement();
		}
	}	
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_escopo_detalhado',true,'410','".$conteudo."');");
	
	return $resposta;
}

function inc_escopodetalhado($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","�","`");
	
	$msg = $conf->msg($resposta);

	$erro = NULL;
	
	$camp_vazio = false;
	
	$no_check = true;
	
	$id_escopo_geral = $dados_form["sel_escopo_geral"];

	//return $resposta;	
	if(!empty($id_escopo_geral))
	{	
		//inclui os itens dos checkboxes	
		foreach($dados_form["chk_escopodet"] as $id=>$array_valor)
		{				
			foreach($array_valor as $index=>$val)
			{
				$no_check = false;
				
				if(!empty($dados_form["txt_qtd"][$id][$index]) && !empty($dados_form["txt_horas"][$id][$index]))
				{
					$sql = "SELECT * FROM planejamento.escopo_detalhado ";
					$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
					$sql .= "AND escopo_detalhado.id_tarefa = '".$id."' ";
					$sql .= "AND escopo_detalhado.item = '".$index."' ";
					$sql .= "AND escopo_detalhado.id_escopo_geral = '".$id_escopo_geral."' ";

					$db->select($sql,'MYSQL',true);								
					
					//existe registro, atualiza
					if($db->numero_registros>0)
					{
						$regs = $db->array_select[0];

						$usql = "UPDATE planejamento.escopo_detalhado SET ";
						$usql .= "descricao_escopo = '" . maiusculas(addslashes(trim(utf8_decode(str_replace($chars,"",$dados_form["txt_descativ"][$id][$index]))))) . "', ";
						$usql .= "quantidade = '" . number_format(str_replace(",",".",$dados_form["txt_qtd"][$id][$index]),2,'.','') . "', ";
						$usql .= "horas = '" . number_format(str_replace(",",".",$dados_form["txt_horas"][$id][$index]),2,'.','') . "', ";
						$usql .= "id_formato = '" . $dados_form["cb_fmt"][$id][$index] . "' ";
						$usql .= "WHERE id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
						$usql .= "AND reg_del = 0 ";
					
						$db->update($usql,'MYSQL');
					
					}
					else
					{
						$sql = "SELECT * FROM ".DATABASE.".atividades ";
						$sql .= "WHERE atividades.id_atividade = '".$id."' ";
						$sql .= "AND atividades.reg_del = 0 ";
						
						$db->select($sql,'MYSQL',true);
						
						$tarefa_orc = $db->array_select[0];						
						
						$isql = "INSERT INTO planejamento.escopo_detalhado (id_escopo_geral, id_tarefa, item, descricao_escopo, quantidade_orcada, horas_orcada, id_formato_orcado, quantidade, horas, id_formato) VALUES (";
						$isql .= "'" . $id_escopo_geral . "', ";
						$isql .= "'" . $id . "', ";
						$isql .= "'" . $index . "', ";
						$isql .= "'" . maiusculas(addslashes(trim(utf8_decode(str_replace($chars,"",$dados_form["txt_descativ"][$id][$index]))))). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["hd_qtd"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["hd_horas"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . $tarefa_orc["id_formato"] . "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["txt_qtd"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["txt_horas"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . $dados_form["cb_fmt"][$id][$index]. "') ";

						$db->insert($isql,'MYSQL');
						
						$id_escopo_det = $db->insert_id;
						
						//seleciona as atividades e os recursos associados
						$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
						$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
						$sql .= "AND atividades_orcamento.reg_del = 0 ";
						$sql .= "AND rh_cargos.reg_del = 0 ";
						$sql .= "AND atividades_orcamento.id_atividade = '" . $id . "' ";
					
						$db->select($sql,'MYSQL',true);
						
						$item = 0;
						
						foreach($db->array_select as $reg_por)
						{
							$qtd = number_format(str_replace(",",".",$dados_form["txt_qtd"][$id][$index]),2,'.','');
							
							$horas = number_format(str_replace(",",".",$dados_form["txt_horas"][$id][$index]),2,'.','');
							
							$calc = $horas*($reg_por["porcentagem"]/100);
							
							//insere os recursos
							$isql = "INSERT INTO planejamento.recursos (id_escopo_detalhado, id_recurso_orcamento, id_escopo_geral, id_tarefa, item_escopo, item, horas_orcamento) VALUES ( ";
							$isql .= "'".$id_escopo_det."', ";
							$isql .= "'".$reg_por["id_cargo_grupo"]."', ";
							$isql .= "'".$id_escopo_geral."', ";
							$isql .= "'".$id."', ";
							$isql .= "'".$index."', ";
							$isql .= "'".$item."', ";
							$isql .= "'".$calc."') ";
							
							$db->insert($isql,'MYSQL');
							
							$item++;								
						}			
							
					}
				}
				else
				{
					$camp_vazio = true;	
				}
			}	
		}
		
		//exclui os itens desselecionados
		foreach($dados_form["chk_del"] as $id=>$array_valor)
		{		
			foreach($array_valor as $index=>$val)
			{
				if($val==1)
				{
					$array_del[] = $id;
				}
			}
		}
		
		$del_string = implode(',',$array_del);
		
		if(count($array_del)>0)
		{
			//exclui o escopo detalhado
			$usql = "UPDATE planejamento.escopo_detalhado SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_escopo_detalhado IN (".$del_string.") ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			//exclui os recursos
			$usql = "UPDATE planejamento.recursos SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_escopo_detalhado IN (".$del_string.") ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');				
			
			$camp_vazio = true;	
		}

		if($camp_vazio || $no_check)
		{
			$resposta->addAlert("Campos n�o preenchidos/selecionados n�o ser�o inclu�dos.");
		}
		else
		{
			$resposta->addAlert("Itens inclu�dos.");	
		}
		
	}
	
	return $resposta;
}

function preenche_resumo($dados_form)
{
	$resposta = new xajaxResponse();
			
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".formatos ";
	$sql .= "WHERE formatos.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_formatos[$regs["id_formato"]] = $regs["formato"];	
	}	
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, planejamento.escopo_geral, planejamento.escopo_detalhado ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_detalhado.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
	$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN') ";	
	$sql .= "ORDER BY escopo_geral.escopo_geral, setores.setor, atividades.descricao ";

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;
	
	$escopo_geral = "";
	
	$disciplina = "";
	
	$tot_setor = NULL;
	
	$total_geral = NULL;
	
	$array_resumo = $db->array_select;

	foreach($array_resumo as $regs)
	{
		if($escopo_geral!=$regs["id_escopo_geral"])
		{		
			$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_escopo_geral"].'_'.$regs["id_escopo_detalhado"]);
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.'background-color:#00BBFF');
					$xml->writeAttribute('colspan','8');
					$xml->text($regs["escopo_geral"]);
				$xml->endElement();
			$xml->endElement();			
		}
		
		if($disciplina!=$regs["id_setor"] || $escopo_geral!=$regs["id_escopo_geral"])
		{
			$xml->startElement('row');
				$xml->writeAttribute('id',$regs["id_setor"].'_'.$regs["id_escopo_detalhado"]);
				$xml->writeElement ('cell','&nbsp;');
			
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;');
					$xml->writeAttribute('colspan','7');
					$xml->text($regs["setor"]);					
				$xml->endElement();
			
			$xml->endElement();											
		}
		
		//seleciona a tabela de recursos cadastrados
		$sql = "SELECT * FROM planejamento.recursos ";
		$sql .= "WHERE recursos.reg_del = 0 ";
		$sql .= "AND recursos.id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
		$sql .= "AND recursos.id_recurso <> 0 "; //recurso alocado
		
		$db->select($sql,'MYSQL',true);	
		
		//CASO tenha recursos alocados (FUN_), MUDA COR DA LINHA
		if($db->numero_registros>0)
		{
			$color = 'background-color:#66FF66';		
		}
		else
		{
			$color = '';
		}

		$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_escopo_detalhado"]);
			$xml->writeAttribute('style',$color);
			$xml->writeElement ('cell','&nbsp;');
			$xml->writeElement ('cell','&nbsp;');
			$xml->writeElement ('cell',$regs["codigo"]);
			$xml->writeElement ('cell',$regs["descricao"]." ".$regs["descricao_escopo"]);
			
			$xml->writeElement ('cell',$array_formatos[$regs["id_formato"]]);
			
			$xml->writeElement ('cell',number_format($regs["quantidade"],2,",","."));
			
			$xml->writeElement ('cell',number_format($regs["horas"],2,",","."));
			
			$xml->startElement ('cell');
				$xml->writeAttribute('title',utf8_encode('RECURSOS'));
				$xml->writeAttribute('style',$color);
				$xml->text('<img lang="'.$regs["id_atividade"].'_'.$regs["item"].'" class="img_rec_'.$regs["id_atividade"].'" id="img_rec[' . $regs["id_atividade"] . ']['.$regs["item"].']" src="'.DIR_IMAGENS.'bt_canais_2.png" onclick=adiciona_recursos(this.lang,'.$regs["id_escopo_geral"].','.$regs["id_escopo_detalhado"].') >');
			$xml->endElement();
			
		$xml->endElement();	
		
		$disciplina = $regs["id_setor"];
		
		$escopo_geral = $regs["id_escopo_geral"];		
	}	
		
	$xml->endElement();
			
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_resumo',true,'500','".$conteudoResumo."');");
	
	//seleciona a linha clicada ao retornar
	$resposta->addScript("rowid();");
	
	return $resposta;
}

function preenche_disciplina($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_setores = NULL;

	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE abreviacao NOT IN ('ADM','DES','COM','FIN','SGQ','CMS','CON','GOB','MON','SUP','MAT','OUT','GER','TIN') ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "ORDER BY setor";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{		
		$array_setores[$regs["id_setor"]] = $regs["setor"];
	}

	$combo = '<select id="disciplina" name="disciplina" class="caixa" '.$disable.' onkeypress = return keySort(this); onchange = xajax_mostra_tarefas(xajax.getFormValues("frm",true));>';
	
	$combo .= '<option value="0">SELECIONE</option>';
	
	foreach ($array_setores as $codsetor=>$setor)
	{
		$combo .= '<option value="'.$codsetor.'">'.$setor.'</option>';
	}	
	
	$combo .= '</select>';
	
	$resposta->addAssign("div_disciplina", "innerHTML",$combo);
	
	return $resposta;	
}

function exportar($id_proposta)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","�","`","'");

	$edt = 1;
	
	$disciplina = "";
	
	$escopo_geral = "";
	
	$sql = "SELECT numero_proposta FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$numero_proposta = $regs["numero_proposta"];
	
	//verifica o apontamento
	$sql = "SELECT R_E_C_N_O_ FROM AJK010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_PROJET = '".$numero_proposta."' ";
	
	$db->select($sql,'MSSQL',true);
	
	if($db->numero_registros_ms>0)
	{
		$resposta->addAlert("Este projeto j� tem apontamentos e n�o ser� modificado.");
	}
	else
	{
		//limpa o PROJETO
		$usql = "UPDATE AF8010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AF8_PROJET = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		//limpa o historico de revisoes
		$usql = "UPDATE AFE010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AFE_PROJET = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
			
		//limpa as EDTS
		$usql = "UPDATE AFC010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AFC_PROJET = '".$numero_proposta."' ";
		//$usql .= "AND AFC_NIVEL > '001' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		//limpa as tarefas
		$usql = "UPDATE AF9010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AF9_PROJET = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		//limpa os recursos
		$usql = "UPDATE AFA010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AFA_PROJET = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		//limpa as despesas
		$usql = "UPDATE AFB010 SET ";
		$usql .= "D_E_L_E_T_ = '*', ";
		$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AFB_PROJET = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		//SELECIONA O ORCAMENTO PARA INCLUSAO NO PROJETO
		$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
		$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF1_ORCAME = '".$numero_proposta."' ";
		
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$reg_orcamento = $db->array_select[0];
		
		//INSERE O PROJETO
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF8010 WITH(NOLOCK) ";			
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$regs1 = $db->array_select[0];
		
		$recno_af8 = $regs1["R_E_C_N_O_"] + 1;			
		
		$isql = "INSERT INTO AF8010 (AF8_PROJET, AF8_ORCAME, AF8_RAIZ, AF8_DATA, AF8_DESCRI, AF8_CLIENT, AF8_LOJA, AF8_REVISA, ";
		$isql .= "AF8_TPPRJ, AF8_CALEND, AF8_FASE, AF8_PRJREV, AF8_CTRUSR, AF8_TPCUS, AF8_DELIM, AF8_MASCAR, AF8_NMAX, AF8_NMAXF3, ";
		$isql .= "AF8_RECALC, AF8_TRUNCA, AF8_AUTCUS, AF8_ENCPRJ, AF8_REALOC, AF8_PRIREA, AF8_CUSOP, AF8_CUSOPE, AF8_REAFIX, ";
		$isql .= "AF8_ITEM, AF8_USAAJT, AF8_CONVIT, AF8_REFCLI, AF8_DESCRE, AF8_RESPTE, AF8_VISAO, AF8_TITULO, AF8_PAR003, AF8_AJCUST, ";
		$isql .= "AF8_RESPSU, AF8_PAR002, AF8_PAR004, AF8_COORD1, AF8_COORD2, AF8_PAR005, ";
		$isql .= "AF8_PAR006, AF8_DTSOLI, AF8_DTENTR, AF8_EXECU1, AF8_EXECU2, AF8_EXECU3, AF8_EXECU4, AF8_OBSERV, AF8_PEDIDO, AF8_CONTRA, ";
		$isql .= "AF8_TPCLIE, AF8_VENDED, AF8_ACAO, AF8_DTAPRO, AF8_SEGMEN, AF8_TCORD1, AF8_TCORD2, R_E_C_N_O_) VALUES ( ";
		$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
		$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
		$isql .= "'".$reg_orcamento["AF1_RAIZ"]."', ";
		$isql .= "'".date('Ymd')."', ";
		$isql .= "'".trim(addslashes(str_replace($chars,"",$reg_orcamento["AF1_DESCRI"])))."', ";
		$isql .= "'".trim(addslashes(str_replace($chars,"",$reg_orcamento["AF1_CLIENT"])))."', ";
		$isql .= "'".$reg_orcamento["AF1_LOJA"]."', ";
		$isql .= "'0001', ";
		$isql .= "'".$reg_orcamento["AF1_TPORC"]."', ";
		$isql .= "'001', ";
		$isql .= "'01', ";
		$isql .= "'1', ";
		$isql .= "'".$reg_orcamento["AF1_CTRUSR"]."', ";
		$isql .= "'1', ";
		$isql .= "'".$reg_orcamento["AF1_DELIM"]."', ";
		$isql .= "'".$reg_orcamento["AF1_MASCAR"]."', ";
		$isql .= "'".$reg_orcamento["AF1_NMAX"]."', ";
		$isql .= "'".$reg_orcamento["AF1_NMAXF3"]."', ";
		$isql .= "'".$reg_orcamento["AF1_RECALC"]."', ";
		$isql .= "'".$reg_orcamento["AF1_TRUNCA"]."', ";
		$isql .= "'".$reg_orcamento["AF1_AUTCUS"]."', ";
		$isql .= "'2', ";
		$isql .= "'2', ";
		$isql .= "'1', ";
		$isql .= "'1', ";
		$isql .= "'1', ";
		$isql .= "'1', ";
		$isql .= "'".$reg_orcamento["AF1_ITEM"]."', ";
		$isql .= "'2', ";
		$isql .= "'".$reg_orcamento["AF1_CONVIT"]."', ";
		$isql .= "'".$reg_orcamento["AF1_REFCLI"]."', ";
		$isql .= "'".$reg_orcamento["AF1_DESCRE"]."', ";
		$isql .= "'".$reg_orcamento["AF1_RESPTE"]."', ";
		$isql .= "'".$reg_orcamento["AF1_VISAO"]."', ";
		$isql .= "'".$reg_orcamento["AF1_TITULO"]."', ";
		$isql .= "'2', ";
		$isql .= "'0', ";
		$isql .= "'".$reg_orcamento["AF1_RESPSU"]."', ";
		$isql .= "'3', ";
		$isql .= "'2', ";
		$isql .= "'".$reg_orcamento["AF1_COORD1"]."', ";
		$isql .= "'".$reg_orcamento["AF1_COORD2"]."', ";
		$isql .= "'1', ";
		$isql .= "'2', ";
		$isql .= "'".$reg_orcamento["AF1_DTSOLI"]."', ";
		$isql .= "'".$reg_orcamento["AF1_DTENTR"]."', ";
		$isql .= "'".$reg_orcamento["AF1_EXECU1"]."', ";
		$isql .= "'".$reg_orcamento["AF1_EXECU2"]."', ";
		$isql .= "'".$reg_orcamento["AF1_EXECU3"]."', ";
		$isql .= "'".$reg_orcamento["AF1_EXECU4"]."', ";
		$isql .= "'".$reg_orcamento["AF1_OBSERV"]."', ";
		$isql .= "'".$reg_orcamento["AF1_PEDIDO"]."', ";
		$isql .= "'".$reg_orcamento["AF1_CONTRA"]."', ";
		$isql .= "'".$reg_orcamento["AF1_TPCLIE"]."', ";
		$isql .= "'".$reg_orcamento["AF1_VENDED"]."', ";
		$isql .= "'".$reg_orcamento["AF1_ACAO"]."', ";
		$isql .= "'".$reg_orcamento["AF1_DTAPRO"]."', ";
		$isql .= "'".$reg_orcamento["AF1_SEGMEN"]."', ";
		$isql .= "'".$reg_orcamento["AF1_TCORD1"]."', ";
		$isql .= "'".$reg_orcamento["AF1_TCORD2"]."', ";
		$isql .= "'".$recno_af8."') ";
		
		$db->insert($isql,'MSSQL');
		
		//INSERE A REVISAO (CONTROLE REVIS�O)
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFE010 WITH(NOLOCK) ";			
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$regs2 = $db->array_select[0];
		
		$recno_afe = $regs2["R_E_C_N_O_"] + 1;
		
		$isql = "INSERT INTO AFE010 (AFE_PROJET, AFE_REVISA, AFE_DATAI, AFE_HORAI, AFE_USERI, AFE_MEMO, AFE_DATAF, AFE_HORAF, AFE_USERF, AFE_TIPO, R_E_C_N_O_) VALUES ( ";
		$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
		$isql .= "'0001', ";
		$isql .= "'".date('Ymd')."', ";
		$isql .= "'".date('H:i')."', ";
		$isql .= "'000000', ";
		$isql .= "'Inclusao do Projeto - Sistema', ";
		$isql .= "'".date('Ymd')."', ";
		$isql .= "'".date('H:i')."', ";
		$isql .= "'000000', ";
		$isql .= "'1', ";
		$isql .= "'".$recno_afe."') ";
		
		$db->insert($isql,'MSSQL');
		
		//INSERE OS ESCOPOS GERAIS --> EDTS - NIVEL 1 - PROJETO
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFC010 WITH(NOLOCK) ";			
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$regs3 = $db->array_select[0];
		
		$recno_afc = $regs3["R_E_C_N_O_"] + 1;			
		
		$isql = "INSERT INTO AFC010 (AFC_PROJET, AFC_REVISA, AFC_EDT, AFC_NIVEL, AFC_DESCRI, AFC_UM, AFC_QUANT, AFC_FATURA, ";
		$isql .= "AFC_CALEND, AFC_START, AFC_FINISH, AFC_HORAI, AFC_HORAF, AFC_RESTRI, R_E_C_N_O_) VALUES ( ";
		$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
		$isql .= "'0001', ";
		$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
		$isql .= "'001', ";
		$isql .= "'".trim(addslashes(str_replace($chars,"",$reg_orcamento["AF1_DESCRI"])))."', ";
		$isql .= "'UN', ";
		$isql .= "'1.00', ";
		$isql .= "'1', ";
		$isql .= "'001', ";
		$isql .= "'".date('Ymd')."', ";
		$isql .= "'".date('Ymd')."', ";
		$isql .= "'08:00', ";
		$isql .= "'17:00', ";
		$isql .= "'3', ";
		$isql .= "'".$recno_afc."') ";
		
		$db->insert($isql,'MSSQL');			
		
		//seleciona os escopos gerais da proposta
		$sql = "SELECT * FROM planejamento.escopo_geral, planejamento.escopo_detalhado ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
		$sql .= "AND escopo_geral.id_proposta = '".$id_proposta."' ";
		$sql .= "GROUP BY escopo_geral.id_escopo_geral ";
		$sql .= "ORDER BY escopo_geral.escopo_geral ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$array_escopo = $db->array_select;
		
		foreach($array_escopo as $regs)
		{		
			//INSERE OS ESCOPOS GERAIS --> EDTS > NIVEL 2
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFC010 WITH(NOLOCK) ";			
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs4 = $db->array_select[0];
			
			$recno_afc = $regs4["R_E_C_N_O_"] + 1;			
			
			$isql = "INSERT INTO AFC010 (AFC_PROJET, AFC_REVISA, AFC_NIVEL, AFC_DESCRI, AFC_UM, AFC_QUANT, AFC_CALEND, ";
			$isql .= "AFC_EDT, AFC_EDTPAI, AFC_START, AFC_FINISH, AFC_HORAI, AFC_HORAF, AFC_FATURA, AFC_RESTRI, R_E_C_N_O_) VALUES ( ";
			$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
			$isql .= "'0001', ";
			$isql .= "'002', ";
			$isql .= "'".maiusculas(tiraacentos($regs["escopo_geral"]))."', ";
			$isql .= "'UN', ";
			$isql .= "'1.00', ";
			$isql .= "'001', ";
			$isql .= "'".sprintf("%02d",$edt)."', ";
			$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
			$isql .= "'".date('Ymd')."', ";
			$isql .= "'".date('Ymd')."', ";
			$isql .= "'08:00', ";
			$isql .= "'17:00', ";
			$isql .= "'1', ";
			$isql .= "'3', ";
			$isql .= "'".$recno_afc."') ";
			
			$db->insert($isql,'MSSQL');			
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$edt_dis = 1;
			
			//seleciona as disciplinas
			$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, planejamento.escopo_detalhado ";
			$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND escopo_detalhado.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
			$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
			$sql .= "AND atividades.cod = setores.id_setor ";
			$sql .= "AND atividades.obsoleto = 0 ";	
			$sql .= "GROUP BY setores.setor ";
			$sql .= "ORDER BY setores.setor, atividades.descricao "; 
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_detalhado = $db->array_select;
			
			foreach($array_detalhado as $regs5)
			{
				//if($regs5["setor"]!='DESPESAS')
				//{
					$setor = $regs5["setor"];
				//}
				//else
				//{
					//$setor = 'MOBILIZA��O';
				//}
				
				//INSERE AS DISCIPLINAS --> EDTS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFC010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs_r = $db->array_select[0];
				
				$recno_afc = $regs_r["R_E_C_N_O_"] + 1;			

				$isql = "INSERT INTO AFC010 (AFC_PROJET, AFC_REVISA, AFC_NIVEL, AFC_DESCRI, AFC_UM, AFC_QUANT, AFC_CALEND, ";
				$isql .= "AFC_EDT, AFC_EDTPAI, AFC_START, AFC_FINISH, AFC_HORAI, AFC_HORAF, AFC_FATURA, AFC_RESTRI, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
				$isql .= "'0001', ";
				$isql .= "'003', ";
				$isql .= "'".maiusculas(tiraacentos($setor))."', ";
				$isql .= "'UN', ";
				$isql .= "'1.00', ";
				$isql .= "'001', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
				$isql .= "'".sprintf("%02d",$edt)."', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'08:00', ";
				$isql .= "'17:00', ";
				$isql .= "'1', ";
				$isql .= "'3', ";
				$isql .= "'".$recno_afc."') ";
				
				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$edt_tar = 1;
				
				//seleciona as tarefas
				$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".formatos, planejamento.escopo_detalhado ";
				$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
				$sql .= "AND setores.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND formatos.reg_del = 0 ";
				$sql .= "AND escopo_detalhado.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
				$sql .= "AND setores.id_setor = '".$regs5["id_setor"]."' ";
				$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
				$sql .= "AND atividades.cod = setores.id_setor ";
				$sql .= "AND atividades.id_formato = formatos.id_formato ";
				$sql .= "AND atividades.obsoleto = 0 ";	
				$sql .= "ORDER BY setores.setor, atividades.descricao, escopo_detalhado.id_escopo_detalhado ";				
				
				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$array_tarefas = $db->array_select;
				
				foreach($array_tarefas as $regs6)
				{
					$titulo1 = "";
										
					//se mobiliza��o
					//if(substr($regs6["codigo"],0,3)=='DES')
					//{
						//$horas = $regs6["horasestimadas"]*$regs6["quantidade"]; //////VERIFICAR AQUI - 23/02/2017
					//}
					//else
					//{
						$horas = $regs6["horas"];
					//}
					
					//obtem o ultimo codigo exclusivo
					$sql = "SELECT TOP 1 AF9_CODIGO FROM AF9010 WITH(NOLOCK) ";
					$sql .= "WHERE D_E_L_E_T_ = '' ";
					$sql .= "AND AF9_PROJET = '".$reg_orcamento["AF1_ORCAME"]."' ";			
					$sql .= "ORDER BY AF9_CODIGO DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs9 = $db->array_select[0];
					
					$codigo = intval($regs9["AF9_CODIGO"]) + 1;					
					
					//INSERE AS TAREFAS
					$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF9010 WITH(NOLOCK) ";			
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs7 = $db->array_select[0];
					
					$recno_af9 = $regs7["R_E_C_N_O_"] + 1;			
					
					$isql = "INSERT INTO AF9010 (AF9_PROJET, AF9_REVISA, AF9_TAREFA, AF9_NIVEL, AF9_DESCRI, AF9_UM, AF9_QUANT, ";
					$isql .= "AF9_GRPCOM, AF9_COMPOS, AF9_HDURAC, AF9_CALEND, AF9_START, AF9_FINISH, AF9_HORAI, AF9_HORAF, AF9_HUTEIS, AF9_HESF, AF9_TPMEDI, ";
					$isql .= "AF9_PRIORI, AF9_FATURA, AF9_UTIBDI, AF9_EDTPAI, AF9_CNVPRV, AF9_EMAIL, AF9_RESTRI, AF9_TPHORA, ";
					$isql .= "AF9_RASTRO, AF9_TPTRF, AF9_AGCRTL, AF9_CODIGO, R_E_C_N_O_) VALUES ( ";
					$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
					$isql .= "'0001', ";
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";
					$isql .= "'004', ";
					$isql .= "'".$regs6["descricao"]." ".maiusculas(tiraacentos($regs6["descricao_escopo"]))."', ";
					$isql .= "'".$regs6["codigo_formato"]."', ";
					$isql .= "'".$regs6["quantidade"]."', ";
					$isql .= "'".$regs6["abreviacao"]."', ";
					$isql .= "'".$regs6["codigo"]."', ";
					$isql .= "'".$horas."', ";
					$isql .= "'001', ";
					$isql .= "'".date('Ymd')."', ";
					$isql .= "'".date('Ymd')."', ";
					$isql .= "'08:00', ";
					$isql .= "'17:00', ";
					$isql .= "'".$regs6["horas"]."', ";
					$isql .= "'".$regs6["horas"]."', ";
					$isql .= "'4', ";
					$isql .= "'500', ";
					$isql .= "'1', ";
					$isql .= "'1', ";					
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
					$isql .= "'1', ";
					$isql .= "'2', ";
					$isql .= "'7', ";
					$isql .= "'3', ";
					$isql .= "'1', ";
					$isql .= "'2', ";
					$isql .= "'2', ";
					$isql .= "'".sprintf("%06d",$codigo)."', ";
					$isql .= "'".$recno_af9."') ";
	
					$db->insert($isql,'MSSQL');			
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}

					$sql = "SELECT * FROM planejamento.recursos ";
					$sql .= "WHERE recursos.reg_del = 0 ";
					$sql .= "AND recursos.id_escopo_detalhado = '".$regs6["id_escopo_detalhado"]."' ";
					
					$db->select($sql,'MYSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
					
					$array_recursos = $db->array_select;
					
					$item_recurso = 1;						
					
					foreach($array_recursos as $regs_rec)
					{
						$produto = "";
						
						$recurso = "";
						
						$custo = 0;
						
						//se recurso funcionario = 0 e recurso_orcamento <> 0
						//obtem o valor standard
						if($regs_rec["id_recurso"]==0 && $regs_rec["id_recurso_orcamento"]!=0)
						{
							//SELECIONA O RECURSO ORCAMENTO
							$sql = "SELECT * FROM AE8010 WITH(NOLOCK) ";
							$sql .= "WHERE AE8010.D_E_L_E_T_ = '' ";
							$sql .= "AND AE8_RECURS = 'ORC_".sprintf("%011d",$regs_rec["id_recurso_orcamento"])."' ";
			
							$db->select($sql,'MSSQL', true);
						
							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
								
								return $resposta;
							}
							
							$array_orc = $db->array_select[0];
							
							$recurso = $array_orc["AE8_RECURS"];
							
							$produto = $array_orc["AE8_PRODUT"];
							
							$custo = $regs_rec["AE8_VALOR"];	
						}
						else
						{
							//se recurso funcionario
							if($regs_rec["id_recurso"]!=0)
							{
								//SELECIONA O RECURSO FUNCIONARIO
								$sql = "SELECT * FROM AE8010 WITH(NOLOCK) ";
								$sql .= "WHERE AE8010.D_E_L_E_T_ = '' ";
								$sql .= "AND AE8_RECURS = 'FUN_".sprintf("%011d",$regs_rec["id_recurso"])."' ";
				
								$db->select($sql,'MSSQL', true);
							
								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
									
									return $resposta;
								}
								
								$array_fun = $db->array_select[0];
								
								$recurso = $array_fun["AE8_RECURS"];
								
								$produto = $array_fun["AE8_PRODUT"];
								
								//Obtem o valor do salario na data
								$sql = "SELECT * FROM ".DATABASE.".salarios ";
								$sql .= "WHERE salarios.id_funcionario = '" . $regs_rec["id_recurso"] . "' ";
								$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".date('Ymd')."' ";
								$sql .= "AND salarios.reg_del = 0 ";
								$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
						
								$db->select($sql,'MYSQL',true);
						
								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
										
								$regs_sal = $db->array_select[0];
						  
								switch ($regs_sal[" tipo_contrato"])
								{
									case 'SC':
									case 'SC+CLT':

										//$custo = round($regs_sal["salario_hora"]*$regs_rec["horas"]*(0.975),2);
										$custo = round($regs_sal["salario_hora"],2);
										
									break;
									
									case 'CLT':
									case 'EST':

										//$custo = round((($regs_sal["salario_clt"]/176)*1.84*$regs_rec["horas"]),2);
										$custo = round((($regs_sal["salario_clt"]/176)*1.84),2);
										
									break;
									
									case 'SC+MENS':
									case 'SC+CLT+MENS':

										//$custo = round((($regs_sal["salario_mensalista"]/176)*$regs_rec["horas"]*(0.975)),2);
										$custo = round((($regs_sal["salario_mensalista"]/176)),2);
										
									break;
							   }	
							}
						}
						
						//SE RECURSO N�O TIVER ASSOCIADO, PEGA AS HORAS DO ORCADO
						if($regs_rec["id_recurso"]!=0)
						{
							$horas_rec = $regs_rec["horas"];
						}
						else
						{
							$horas_rec = $regs_rec["horas_orcamento"];
						}									
						
						//INSERE OS RECURSOS --> TAREFAS
						$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFA010 WITH(NOLOCK) ";			
						$sql .= "ORDER BY R_E_C_N_O_ DESC ";
					
						$db->select($sql,'MSSQL', true);
					
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
							
							return $resposta;
						}	
						
						$regs_tar = $db->array_select[0];
						
						$recno_afa = $regs_tar["R_E_C_N_O_"] + 1;
						
						$isql = "INSERT INTO AFA010 (AFA_PROJET, AFA_REVISA, AFA_TAREFA, AFA_ITEM, AFA_RECURS, AFA_PRODUT, AFA_QUANT, ";
						$isql .= "AFA_MOEDA, AFA_ACUMUL, AFA_CUSTD, AFA_ALOC, AFA_DATPRF, AFA_START, AFA_FINISH, AFA_HORAI, AFA_HORAF, AFA_COMPOS, ";
						$isql .= "AFA_FIX, AFA_RECALC, AFA_PLNPOR, AFA_GERPLA, AFA_RESP, R_E_C_N_O_) VALUES ( ";
						$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
						$isql .= "'0001', ";
						$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";						
						$isql .= "'".sprintf("%02d",$item_recurso)."', ";
						$isql .= "'".$recurso."', ";
						$isql .= "'".$produto."', ";
						$isql .= "'".$horas_rec."', ";
						$isql .= "'1', ";
						$isql .= "'3', ";
						$isql .= "'".$custo."', ";
						$isql .= "'100', ";
						$isql .= "'".date('Ymd')."', ";
						$isql .= "'".date('Ymd')."', ";
						$isql .= "'".date('Ymd')."', ";
						$isql .= "'08:00', ";
						$isql .= "'17:00', ";												
						$isql .= "'".$regs6["codigo"]."', ";						
						$isql .= "'2', ";
						$isql .= "'1', ";
						$isql .= "'1', ";
						$isql .= "'2', ";
						$isql .= "'2', ";
						$isql .= "'".$recno_afa."') ";
		
						$db->insert($isql,'MSSQL');			
						
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
							
							return $resposta;
						}
						
						$item_recurso++;
													
					}
					
					$edt_tar++;					
				}
				
				$edt_dis++;			
			}		
			
			$edt++;
			
			//seleciona a mobilizacao (DESPESAS)
			$sql = "SELECT * FROM ".DATABASE.".mobilizacao, ".DATABASE.".atividades, ".DATABASE.".formatos ";
			$sql .= "WHERE mobilizacao.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND formatos.reg_del = 0 ";
			$sql .= "AND mobilizacao.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
			$sql .= "AND mobilizacao.id_tarefa = atividades.id_atividade ";
			$sql .= "AND atividades.id_formato = formatos.id_formato ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_mobilizacao = $db->array_select;
			
			//se hover mobilizacao insere os registros
			if($db->numero_registros>0)
			{
				//INSERE AS DESPESAS --> EDTS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFC010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs_r = $db->array_select[0];
				
				$recno_afc = $regs_r["R_E_C_N_O_"] + 1;			

				$isql = "INSERT INTO AFC010 (AFC_PROJET, AFC_REVISA, AFC_NIVEL, AFC_DESCRI, AFC_UM, AFC_QUANT, AFC_CALEND, ";
				$isql .= "AFC_EDT, AFC_EDTPAI, AFC_START, AFC_FINISH, AFC_HORAI, AFC_HORAF, AFC_FATURA, AFC_RESTRI, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
				$isql .= "'0001', ";
				$isql .= "'003', ";
				$isql .= "'MOBILIZACAO', ";
				$isql .= "'UN', ";
				$isql .= "'1.00', ";
				$isql .= "'001', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
				$isql .= "'".sprintf("%02d",$edt)."', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'08:00', ";
				$isql .= "'17:00', ";
				$isql .= "'1', ";
				$isql .= "'3', ";
				$isql .= "'".$recno_afc."') ";
				
				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$edt_tar = 1;
							
				foreach($array_mobilizacao as $reg_mobilizacao)
				{
					//obtem o ultimo codigo exclusivo
					$sql = "SELECT TOP 1 AF9_CODIGO FROM AF9010 WITH(NOLOCK) ";
					$sql .= "WHERE D_E_L_E_T_ = '' ";
					$sql .= "AND AF9_PROJET = '".$reg_orcamento["AF1_ORCAME"]."' ";			
					$sql .= "ORDER BY AF9_CODIGO DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs9 = $db->array_select[0];
					
					$codigo = intval($regs9["AF9_CODIGO"]) + 1;					
					
					//INSERE AS TAREFAS (DESPESAS)
					$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF9010 WITH(NOLOCK) ";			
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs7 = $db->array_select[0];
					
					$recno_af9 = $regs7["R_E_C_N_O_"] + 1;			
					
					$isql = "INSERT INTO AF9010 (AF9_PROJET, AF9_REVISA, AF9_TAREFA, AF9_NIVEL, AF9_DESCRI, AF9_UM, AF9_QUANT, ";
					$isql .= "AF9_GRPCOM, AF9_COMPOS, AF9_HDURAC, AF9_CALEND, AF9_START, AF9_FINISH, AF9_HORAI, AF9_HORAF, AF9_HUTEIS, AF9_HESF, AF9_TPMEDI, ";
					$isql .= "AF9_PRIORI, AF9_FATURA, AF9_UTIBDI, AF9_EDTPAI, AF9_CNVPRV, AF9_EMAIL, AF9_RESTRI, AF9_TPHORA, ";
					$isql .= "AF9_RASTRO, AF9_TPTRF, AF9_AGCRTL, AF9_CODIGO, R_E_C_N_O_) VALUES ( ";
					$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
					$isql .= "'0001', ";
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";
					$isql .= "'004', ";
					$isql .= "'".$reg_mobilizacao["descricao"]." ".maiusculas(tiraacentos($reg_mobilizacao["descricao_mobilizacao"]))."', ";
					$isql .= "'".$reg_mobilizacao["codigo_formato"]."', ";
					$isql .= "'".$reg_mobilizacao["qtd_necessario"]."', ";
					$isql .= "'".$reg_mobilizacao["abreviacao"]."', ";
					$isql .= "'".$reg_mobilizacao["codigo"]."', ";
					$isql .= "'0', ";
					$isql .= "'001', ";
					$isql .= "'".date('Ymd')."', ";
					$isql .= "'".date('Ymd')."', ";
					$isql .= "'08:00', ";
					$isql .= "'17:00', ";
					$isql .= "'0', ";
					$isql .= "'0', ";
					$isql .= "'4', ";
					$isql .= "'500', ";
					$isql .= "'1', ";
					$isql .= "'1', ";					
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
					$isql .= "'1', ";
					$isql .= "'2', ";
					$isql .= "'7', ";
					$isql .= "'3', ";
					$isql .= "'1', ";
					$isql .= "'2', ";
					$isql .= "'2', ";
					$isql .= "'".sprintf("%06d",$codigo)."', ";
					$isql .= "'".$recno_af9."') ";
	
					$db->insert($isql,'MSSQL');			
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
					
					$edt_tar++;				
				}
				
				$edt_dis++;	
			}
			
			$edt++;											
		}
		
		//insere subcontratos
		//seleciona os subcontratados
		$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
		$sql .= "WHERE subcontratados.reg_del = 0 ";
		$sql .= "AND subcontratados.id_proposta = '".$id_proposta."' ";
		$sql .= "ORDER BY subcontratado ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$array_subcontrato = $db->array_select;
		
		//se existir subcontratados
		if($db->numero_registros>0)
		{			
			//INSERE A EDT --> GERAL
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFC010 WITH(NOLOCK) ";			
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs2 = $db->array_select[0];
			
			$recno_afc = $regs2["R_E_C_N_O_"] + 1;			

			$isql = "INSERT INTO AFC010 (AFC_PROJET, AFC_REVISA, AFC_EDT, AFC_EDTPAI, AFC_NIVEL, AFC_DESCRI, AFC_UM, AFC_QUANT, AFC_FATURA, ";
			$isql .= "AFC_CALEND, AFC_START, AFC_FINISH, AFC_HORAI, AFC_HORAF, AFC_RESTRI, R_E_C_N_O_) VALUES ( ";
			$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
			$isql .= "'0001', ";
			$isql .= "'".sprintf("%02d",$edt)."', ";
			$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
			$isql .= "'002', ";
			$isql .= "'GERAL', ";
			$isql .= "'UN', ";
			$isql .= "'1.00', ";
			$isql .= "'1', ";
			$isql .= "'001', ";
			$isql .= "'".date('Ymd')."', ";
			$isql .= "'".date('Ymd')."', ";
			$isql .= "'08:00', ";
			$isql .= "'17:00', ";
			$isql .= "'3', ";
			$isql .= "'".$recno_afc."') ";
			
			$db->insert($isql,'MSSQL');	
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$edt_tar = 1;
			
			foreach($array_subcontrato as $regs10)
			{	
				//insere o subcontratados como tarefa		
				$sql = "SELECT TOP 1 AF9_CODIGO FROM AF9010 WITH(NOLOCK) ";
				$sql .= "WHERE D_E_L_E_T_ = '' ";
				$sql .= "AND AF9_PROJET = '".$reg_orcamento["AF1_ORCAME"]."' ";			
				$sql .= "ORDER BY AF9_CODIGO DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs9 = $db->array_select[0];
				
				$codigo = intval($regs9["AF9_CODIGO"]) + 1;					
				
				//INSERE A TAREFA SUBCONTRATOS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF9010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs7 = $db->array_select[0];
				
				$recno_af9 = $regs7["R_E_C_N_O_"] + 1;			
				
				$isql = "INSERT INTO AF9010 (AF9_PROJET, AF9_REVISA, AF9_TAREFA, AF9_NIVEL, AF9_DESCRI, AF9_UM, AF9_QUANT, ";
				$isql .= "AF9_GRPCOM, AF9_COMPOS, AF9_HDURAC, AF9_CALEND, AF9_START, AF9_FINISH, AF9_HORAI, AF9_HORAF, AF9_HUTEIS, AF9_HESF, AF9_TPMEDI, ";
				$isql .= "AF9_PRIORI, AF9_FATURA, AF9_UTIBDI, AF9_EDTPAI, AF9_CNVPRV, AF9_EMAIL, AF9_RESTRI, AF9_TPHORA, ";
				$isql .= "AF9_RASTRO, AF9_TPTRF, AF9_AGCRTL, AF9_CODIGO, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
				$isql .= "'0001', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."', ";
				$isql .= "'004', ";
				$isql .= "'".tiraacentos($regs10["subcontratado"])."-".tiraacentos($regs10["descritivo"])."', ";
				$isql .= "'VB', ";
				$isql .= "'1', ";//ATEN��O A ESTE ITEM
				$isql .= "'SUP', ";
				$isql .= "'SUP12', ";
				$isql .= "'0', ";
				$isql .= "'001', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'".date('Ymd')."', ";
				$isql .= "'08:00', ";
				$isql .= "'17:00', ";
				$isql .= "'0', ";
				$isql .= "'0', ";
				$isql .= "'4', ";
				$isql .= "'500', ";
				$isql .= "'1', ";
				$isql .= "'1', ";					
				$isql .= "'".sprintf("%02d",$edt)."', ";
				$isql .= "'1', ";
				$isql .= "'2', ";
				$isql .= "'7', ";
				$isql .= "'3', ";
				$isql .= "'1', ";
				$isql .= "'2', ";
				$isql .= "'2', ";
				$isql .= "'".sprintf("%06d",$codigo)."', ";
				$isql .= "'".$recno_af9."') ";
	
				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}		
	
				//INSERE AS DESPESAS --> TAREFAS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AFB010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs_des = $db->array_select[0];
				
				$recno_afb = $regs_des["R_E_C_N_O_"] + 1;
				
				$sql = "SELECT TOP 1 AFB_ITEM FROM AFB010 WITH(NOLOCK) ";
				$sql .= "WHERE D_E_L_E_T_ = '' ";
				$sql .= "AND AFB_PROJET = '".$reg_orcamento["AF1_ORCAME"]."' ";
				$sql .= "AND AFB_TAREFA = '".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."' ";			
				$sql .= "ORDER BY AFB_ITEM DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs11 = $db->array_select[0];
				
				$item = intval($regs11["AFB_ITEM"]) + 1;	
				
				$isql = "INSERT INTO AFB010 (AFB_PROJET, AFB_REVISA, AFB_ITEM, AFB_TIPOD, AFB_DESCRI, AFB_MOEDA, AFB_VALOR, AFB_TAREFA, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$reg_orcamento["AF1_ORCAME"]."', ";
				$isql .= "'0001', ";
				$isql .= "'".sprintf("%02d",$item)."', ";
				$isql .= "'0005', ";
				$isql .= "'".tiraacentos($regs10["subcontratado"])."-".tiraacentos($regs10["descritivo"])."', ";
				$isql .= "'1', ";
				$isql .= "'".$regs10["valor_subcontrato"]."', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."', ";
				$isql .= "'".$recno_afb."') ";
	
				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$edt_tar++;				
			}
		}		
		
		//ATUALIZA STATUS DO ORCAMENTO
		$usql = "UPDATE ".DATABASE.".propostas SET ";
		$usql .= "id_status_projeto = 3 ";
		$usql .= "WHERE id_proposta = '".$id_proposta."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		$usql = "UPDATE AF1010 SET ";
		$usql .= "AF1_FASE = '04' "; //EM PROJETO
		$usql .= "WHERE D_E_L_E_T_ = '' ";
		$usql .= "AND AF1_ORCAME = '".$numero_proposta."' ";
		
		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert('ERRO');
		}
		
		$resposta->addAlert("Or�amento exportado com sucesso.");
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		
	}
	
	return $resposta;
}

function importar($id_proposta)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	//percorre os escopos gerais
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_escopo_geral = $db->array_select;
	
	foreach($array_escopo_geral as $reg_escopo)
	{
		//insere o escopo geral		
		$isql = "INSERT INTO planejamento.escopo_geral (id_proposta,escopo_geral) VALUES ( ";
		$isql .= "'".$reg_escopo["id_proposta"]."',";
		$isql .= "'".$reg_escopo["escopo_geral"]."') ";
		
		$db->insert($isql,'MYSQL');
		
		$id_escopo_geral = $db->insert_id;
		
		//seleciona os escopos detalhados
		$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$reg_escopo["id_escopo_geral"]."' ";
		$sql .= "AND atividades.id_atividade = escopo_detalhado.id_tarefa ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_escopo_detalhado = $db->array_select;
		
		foreach($array_escopo_detalhado as $reg_escopo_det)
		{
			//insere o escopo detalhado
			$isql = "INSERT INTO planejamento.escopo_detalhado (id_escopo_geral, id_tarefa, item, descricao_escopo, valor, quantidade_orcada, horas_orcada, id_formato_orcado, quantidade, horas, id_formato) VALUES ( ";
			$isql .= "'".$id_escopo_geral."', ";
			$isql .= "'".$reg_escopo_det["id_tarefa"]."', ";
			$isql .= "'".$reg_escopo_det["item"]."', ";
			$isql .= "'".$reg_escopo_det["descricao_escopo"]."', ";
			$isql .= "'".$reg_escopo_det["valor"]."', ";
			$isql .= "'".$reg_escopo_det["qtd_necessario"]."', ";
			$isql .= "'".$reg_escopo_det["totaliza_categoria"]."', ";
			$isql .= "'".$reg_escopo_det["id_formato"]."', ";
			$isql .= "'".$reg_escopo_det["qtd_necessario"]."', ";
			$isql .= "'".$reg_escopo_det["totaliza_categoria"]."', ";
			$isql .= "'".$reg_escopo_det["id_formato"]."') ";
			
			$db->insert($isql,'MYSQL');
			
			$id_escopo_detalhado = $db->insert_id;
			
			//seleciona as atividades e os recursos associados
			$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
			$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
			$sql .= "AND atividades_orcamento.reg_del = 0 ";
			$sql .= "AND rh_cargos.reg_del = 0 ";
			$sql .= "AND atividades_orcamento.id_atividade = '" . $reg_escopo_det["id_tarefa"] . "' ";
		
			$db->select($sql,'MYSQL',true);
			
			$array_porcent = NULL;
			
			$item = 0;
			
			foreach($db->array_select as $reg_por)
			{
				$calc = $reg_escopo_det["totaliza_categoria"]*($reg_por["porcentagem"]/100);
				
				//insere os recursos
				$isql = "INSERT INTO planejamento.recursos (id_escopo_detalhado, id_recurso_orcamento, id_escopo_geral, id_tarefa, item_escopo, item, horas_orcamento)VALUES ( ";
				$isql .= "'".$id_escopo_detalhado."', ";
				$isql .= "'".$reg_por["id_cargo_grupo"]."', ";
				$isql .= "'".$id_escopo_geral."', ";
				$isql .= "'".$reg_escopo_det["id_tarefa"]."', ";
				$isql .= "'".$reg_escopo_det["item"]."', ";
				$isql .= "'".$item."', ";
				$isql .= "'".$calc."') ";
				
				$db->insert($isql,'MYSQL');
				
				$item++;								
			}		
		}		
	}
	
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "id_status_projeto = 2 ";
	$usql .= "WHERE id_proposta = '".$id_proposta."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addAlert("Importado com sucesso.");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;	
}

function mostra_recursos($id_tarefa, $id_escopo_geral, $id_escopo_detalhado)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$checked = '';
	
	$select = '';
	
	//seleciona os recursos atividades
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
	$sql .= "WHERE rh_cargos.reg_del = 0 ";
	$sql .= "ORDER BY grupo";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recs_orc[$regs["id_cargo_grupo"]] = $regs["grupo"];	
	}
	
	//seleciona os recursos funcionarios
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionario ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recs_func[$regs["id_funcionario"]] = $regs["funcionario"];	
	}
	
	//seleciona a tabela de recursos
	$sql = "SELECT * FROM planejamento.recursos ";
	$sql .= "WHERE recursos.reg_del = 0 ";
	$sql .= "AND recursos.id_escopo_geral = '".$id_escopo_geral."' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recursos['horas'][$regs["id_tbl_recursos"]] = $regs["horas"];
		$array_recursos['horas_orcamento'][$regs["id_tbl_recursos"]] = $regs["horas_orcamento"];
	}
	
	//seleciona para obter o id_escopo_detalhado e qual a descri��o			
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
	$sql .= "LEFT JOIN planejamento.escopo_detalhado ON (escopo_detalhado.id_tarefa = atividades.id_atividade  ";
	$sql .= "AND escopo_detalhado.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "AND escopo_detalhado.id_escopo_detalhado = '".$id_escopo_detalhado."' ";
	$sql .= "AND escopo_detalhado.reg_del = 0) ";
	$sql .= "WHERE atividades.id_atividade = '".$id_tarefa."' ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "LIMIT 1";			
	
	$db->select($sql,'MYSQL',true);

	$escopo_det = $db->array_select[0];
	
	$resposta->addAssign('tarefa','innerHTML','<strong>ATIVIDADE:</strong>&nbsp;'.$escopo_det["descricao"]." ".$escopo_det["descricao_escopo"]);
	
	$resposta->addAssign("horas","innerHTML",'<strong>HORAS:</strong>&nbsp;'.$escopo_det["horas"]);
	
	//seleciona para obter o id_proposta		
	$sql = "SELECT id_proposta FROM planejamento.escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "LIMIT 1";			
	
	$db->select($sql,'MYSQL',true);

	$escopo_geral = $db->array_select[0];
	
	if(status_proposta($escopo_geral["id_proposta"])==3)
	{
		$disabled = 'disabled';
		
		$resposta->addAssign("btn_recursos","disabled","disabled");
	}
	else
	{
		$disabled = '';
		
		$resposta->addAssign("btn_recursos","disabled","");
	}

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');				

	//seleciona a tabela de recursos cadastrados
	$sql = "SELECT * FROM planejamento.recursos ";
	$sql .= "WHERE recursos.reg_del = 0 ";
	$sql .= "AND recursos.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "AND recursos.id_escopo_detalhado = '".$id_escopo_detalhado."' ";
	$sql .= "AND recursos.id_tarefa = '".$escopo_det["id_atividade"]."' ";
	$sql .= "AND recursos.item_escopo = '".$escopo_det["item"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	//j� existe cadastro em recursos (tarefas importadas)
	if($db->numero_registros>0)
	{
		$array_rec = $db->array_select;
		
		foreach($array_rec as $reg_rec)
		{		
						
			$checked = '';
			
			$delreg = 1;
			
			//combo de categorias
			$comboa = '<select lang="rec_orc_'.$escopo_det["id_atividade"].'" class="rec_orc_'. $escopo_det["id_atividade"] . '" id="rec_orc['.$escopo_det["id_atividade"].']['.$reg_rec["item"].']" name="rec_orc['.$escopo_det["id_atividade"].']['.$reg_rec["item"].']" '.$disabled.' onkeypress = return keySort(this);>';
			
			$comboa .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_recs_orc as $id_categoria=>$categoria)
			{
				$select_orc = '';
				
				if($reg_rec["id_recurso_orcamento"]==$id_categoria)
				{
					$select_orc = 'selected';
					
					$checked = 'checked';
					
					$delreg = 0;
				}
				
				$comboa .= '<option value="'.$id_categoria.'" '.$select_orc.'>'.$categoria.'</option>';
			}	
			
			$comboa .= '</select>';
			
			//combo de funcionarios
			$combof = '<select lang="funcionario_'.$escopo_det["id_atividade"].'" class="funcionario_'. $escopo_det["id_atividade"] . '" id="funcionario['.$escopo_det["id_atividade"].']['.$reg_rec["item"].']" name="funcionario['.$escopo_det["id_atividade"].']['.$reg_rec["item"].']" '.$disabled.' onkeypress = return keySort(this);>';
			
			$combof .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_recs_func as $id_funcionario=>$funcionario)
			{
				$select_func = '';
				
				if($reg_rec["id_recurso"]==$id_funcionario)
				{
					$select_func = 'selected';
					
					$checked = 'checked';
					
					$delreg = 0;
				}
				
				$combof .= '<option value="'.$id_funcionario.'" '.$select_func.'>'.$funcionario.'</option>';
			}	
			
			$combof .= '</select>';
			
			$xml->startElement('row');
						
				$xml->writeAttribute('id',$escopo_det["id_atividade"].'_'.$reg_rec["item"]);
				
				$xml->startElement ('cell');
					$xml->writeAttribute('title',utf8_encode($array_recs_orc[$reg_rec["id_recurso_orcamento"]]));
					$xml->writeAttribute('style','background-color:#FFFFFF');
					$xml->text('<img src="'.DIR_IMAGENS.'add.png" '.$disabled.' onclick = if(confirm("Deseja&nbsp;duplicar&nbsp;o&nbsp;recurso?")){adiciona_linha_recurso(mygrid4.getRowIndex("'.$escopo_det["id_atividade"].'_'.$reg_rec["item"].'"))} >');
				$xml->endElement();
				
				$xml->writeElement ('cell','<input type="checkbox" lang="chk_recurso_'.$escopo_det["id_atividade"].'" class="chk_recurso_'. $escopo_det["id_atividade"] . '" id="chk_recurso['. $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" name="chk_recurso['. $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" value="1" '.$checked.' '.$disabled.' onclick = lib_campos_rec(this);> >');
				
				$check = $comboa;
				$check .= '<input type="hidden" lang="chk_drec_'.$escopo_det["id_atividade"].'" id="chk_drec[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" name="chk_drec[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" value="'.$delreg.'">';
				$check .= '<input type="hidden" lang="id_rec_'.$escopo_det["id_atividade"].'" id="id_rec[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" name="id_rec[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" value="'.$reg_rec["id_tbl_recursos"].'">';
				
				$xml->writeElement ('cell',$check);						

				$xml->writeElement ('cell',$combof);					

				$quantidade = 0;
					
				if($array_recursos['horas'][$reg_rec["id_tbl_recursos"]]!=0)
				{
					$horas = $array_recursos['horas'][$reg_rec["id_tbl_recursos"]];
				}
				else
				{
					$horas = $array_recursos['horas_orcamento'][$reg_rec["id_tbl_recursos"]];
				}
				
				$campo_qtd = '<input lang="rec_horas_'.$escopo_det["id_atividade"].'" class="rec_horas_'. $escopo_det["id_atividade"] . '" id="rec_horas[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" name="rec_horas[' . $escopo_det["id_atividade"] . ']['.$reg_rec["item"].']" type="text" size="30" '.$disabled.'  value="'.$horas.'" onfocus=this.value=""; onkeypress = num_only(); />';
				
				$xml->writeElement ('cell',$campo_qtd);				
				
			$xml->endElement();
		}
		
	}
	else
	{					
		$indice = 0;
		
		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
		$sql .= "WHERE atividades_orcamento.id_atividade = '" . $escopo_det["id_atividade"] . "' ";
		$sql .= "AND atividades_orcamento.reg_del = 0 ";					
		
		$db->select($sql,'MYSQL',true);
		
		$array_orc = $db->array_select;
		
		foreach($array_orc as $reg_rec)
		{			
			//combo de categorias
			$comboa = '<select lang="rec_orc_'.$escopo_det["id_atividade"].'" class="rec_orc_'. $escopo_det["id_atividade"] . '" id="rec_orc['.$escopo_det["id_atividade"].']['.$indice.']" name="rec_orc['.$escopo_det["id_atividade"].']['.$indice.']"  onkeypress = return keySort(this);>';
			
			$comboa .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_recs_orc as $id_categoria=>$categoria)
			{
				$comboa .= '<option value="'.$id_categoria.'">'.$categoria.'</option>';
			}	
			
			$comboa .= '</select>';	
			
			//combo de funcionarios
			$combof = '<select lang="funcionario_'.$escopo_det["id_atividade"].'" class="funcionario_'. $escopo_det["id_atividade"] . '" id="funcionario['.$escopo_det["id_atividade"].']['.$indice.']" name="funcionario['.$escopo_det["id_atividade"].']['.$indice.']" onkeypress = return keySort(this);>';
			
			$combof .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_recs_func as $id_funcionario=>$funcionario)
			{
				$combof .= '<option value="'.$id_funcionario.'">'.$funcionario.'</option>';
			}	
			
			$combof .= '</select>';		
			
			$xml->startElement('row');
						
				$xml->writeAttribute('id',$escopo_det["id_atividade"].'_'.$indice);
				
				$xml->startElement ('cell');
					$xml->writeAttribute('title',utf8_encode($array_recs_orc[$reg_rec["id_cargo"]]));
					$xml->writeAttribute('style','background-color:#FFFFFF');
					$xml->text('<img src="'.DIR_IMAGENS.'add.png" onclick = if(confirm("Deseja&nbsp;duplicar&nbsp;o&nbsp;recurso?")){adiciona_linha_recurso(mygrid4.getRowIndex("'.$escopo_det["id_atividade"].'_'.$indice.'"))} >');
				$xml->endElement();	
				
				$xml->writeElement ('cell','<input type="checkbox" lang="chk_recurso_'.$escopo_det["id_atividade"].'" class="chk_recurso_'. $escopo_det["id_atividade"] . '" id="chk_recurso['. $escopo_det["id_atividade"] . ']['.$indice.']" name="chk_recurso['. $escopo_det["id_atividade"] . ']['.$indice.']" value="1" onclick = lib_campos_rec(this);> >');

				$check = $comboa;
				$check .= '<input type="hidden" lang="chk_drec_'.$escopo_det["id_atividade"].'" id="chk_drec[' . $escopo_det["id_atividade"] . ']['.$indice.']" name="chk_drec[' . $escopo_det["id_atividade"] . ']['.$indice.']" value="0">';
				
				$xml->writeElement ('cell',$check);					

				$xml->writeElement ('cell',$combof);					

				$horas = 0;

				$horas = $escopo_det["horas"]*($reg_rec["porcentagem"]/100);

				$campo_qtd = '<input lang="rec_horas_'.$escopo_det["id_atividade"].'" class="rec_horas_'. $escopo_det["id_atividade"] . '" id="rec_horas[' . $escopo_det["id_atividade"] . ']['.$indice.']" name="rec_horas[' . $escopo_det["id_atividade"] . ']['.$indice.']" type="text" size="10" '.$disabled.'  value="'.$horas.'" onfocus=this.value=""; onkeypress = num_only(); />';
				
				$xml->writeElement ('cell',$campo_qtd);
				
			$xml->endElement();
			
			$indice++;
		}
												
	}

	$xml->endElement();
							
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_recursos',true,'300','".$conteudo."');");		
	
	return $resposta;	
}

function inc_recursos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","�","`");
	
	$msg = $conf->msg($resposta);

	$erro = NULL;
	
	$camp_vazio = false;
	
	$id_escopo_geral = $dados_form["id_escopo_geral"];
	
	$id_escopo_detalhado = $dados_form["id_escopo_detalhado"];
	
	$id_modal = $dados_form["id_modal"];
	
	//seleciona o escopo detalhado para calculo do quantitativo orcamento
	$sql = "SELECT * FROM planejamento.escopo_detalhado ";
	$sql .= "WHERE escopo_detalhado.id_escopo_detalhado = '".$id_escopo_detalhado."' ";
	$sql .= "AND escopo_detalhado.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$escopo_det = $db->array_select[0];

	//inclui os itens dos checkboxes	
	foreach($dados_form["chk_recurso"] as $id=>$array_valor)
	{				
		foreach($array_valor as $index=>$val)
		{				
			if(!empty($dados_form["funcionario"][$id][$index]) && !empty($dados_form["rec_horas"][$id][$index]))
			{
				//verifica se existe o registro										
				$sql = "SELECT * FROM planejamento.recursos ";
				$sql .= "WHERE recursos.reg_del = 0 ";
				$sql .= "AND recursos.id_tbl_recursos = '".$dados_form["id_rec"][$id][$index]."' ";

				$db->select($sql,'MYSQL',true);								
				
				//existe registro, atualiza
				if($db->numero_registros>0)
				{
					$regs = $db->array_select[0];
					
					$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
					$sql .= "WHERE atividades_orcamento.id_atividade = '" . $escopo_det["id_tarefa"] . "' ";
					$sql .= "AND atividades_orcamento.reg_del = 0 ";
					$sql .= "AND atividades_orcamento.id_cargo = '".$dados_form["rec_orc"][$id][$index]."' ";					
					
					$db->select($sql,'MYSQL',true);
					
					$array_orc = $db->array_select[0];
					
					$horas = 0;
					
					$horas = $escopo_det["horas"]*($array_orc["porcentagem"]/100);
					
					$usql = "UPDATE planejamento.recursos SET ";
					$usql .= "id_recurso = '" . $dados_form["funcionario"][$id][$index] . "', ";
					$usql .= "horas = '" . number_format(str_replace(",",".",$dados_form["rec_horas"][$id][$index]),2,'.','') . "', ";
					$usql .= "id_recurso_orcamento = '" . $dados_form["rec_orc"][$id][$index] . "', ";
					$usql .= "horas_orcamento = '".$horas."' ";
					$usql .= "WHERE recursos.id_tbl_recursos = '".$regs["id_tbl_recursos"]."' ";
					$usql .= "AND reg_del = 0 ";
				
					$db->update($usql,'MYSQL');
				}
				else
				{
					$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
					$sql .= "WHERE atividades_orcamento.id_atividade = '" . $escopo_det["id_tarefa"] . "' ";
					$sql .= "AND atividades_orcamento.reg_del = 0 ";
					$sql .= "AND atividades_orcamento.id_cargo = '".$dados_form["rec_orc"][$id][$index]."' ";					
					
					$db->select($sql,'MYSQL',true);
					
					$array_orc = $db->array_select[0];
					
					$horas = 0;
					
					$horas = $escopo_det["horas"]*($array_orc["porcentagem"]/100);
					
					$isql = "INSERT INTO planejamento.recursos (id_escopo_geral, id_escopo_detalhado, id_tarefa, item, id_recurso_orcamento, horas_orcamento, id_recurso, horas) VALUES (";
					$isql .= "'" . $id_escopo_geral . "', ";
					$isql .= "'" . $id_escopo_detalhado . "', ";
					$isql .= "'" . $id . "', ";
					$isql .= "'" . $index . "', ";
					$isql .= "'" . $dados_form["rec_orc"][$id][$index]. "', ";
					$isql .= "'" . $horas. "', ";
					$isql .= "'" . $dados_form["funcionario"][$id][$index]. "', ";
					$isql .= "'" . number_format(str_replace(",",".",$dados_form["rec_horas"][$id][$index]),2,'.',''). "') ";

					$db->insert($isql,'MYSQL');	
				}
			}
			else
			{
				$camp_vazio = true;	
			}
		}
	}
	
	if($camp_vazio)
	{
		$resposta->addAlert("Recurso com quantidade vazia n�o ser� inclu�do.");
	}
	
	//exclui os itens desselecionados
	foreach($dados_form["chk_drec"] as $id=>$array_valor)
	{		
		foreach($array_valor as $index=>$val)
		{
			if($val==1)
			{
				$array_del[] = $dados_form["id_rec"][$id][$index];
			
			}
		}
	}		
	
	$del_string = implode(',',$array_del);
	
	if(count($array_del)>0)
	{
		$usql = "UPDATE planejamento.recursos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_tbl_recursos IN (".$del_string.") ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');			
		
		$resposta->addAlert("Existem itens n�o preenchidos, e n�o ser�o cadastrados.");	
	}
	
	$resposta->addScript("divPopupInst.destroi(".$id_modal.")");
	
	$resposta->addScript("xajax_preenche_resumo(xajax.getFormValues('frm'))");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("inc_escopogeral");
$xajax->registerFunction("del_escopogeral");
$xajax->registerFunction("preencheEscopoGeral");
$xajax->registerFunction("mostra_tarefas");
$xajax->registerFunction("seleciona_escopo_geral");
$xajax->registerFunction("inc_escopodetalhado");
$xajax->registerFunction("preenche_resumo");
$xajax->registerFunction("preenche_disciplina");
$xajax->registerFunction("exportar");
$xajax->registerFunction("importar");
$xajax->registerFunction("mostra_recursos");
$xajax->registerFunction("inc_recursos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script language="javascript" type="text/javascript">

function calcula_esp(pai)
{
	return true;		
}

function lib_campos(pai)
{	
	 var parent = pai.parentNode.parentNode;
	 
	 if(pai.checked)
	 {
		//alert(parent.childNodes.item(3).childNodes.item(1).id);
		//chk_del
		parent.childNodes.item(3).childNodes.item(1).value = 0;		
		//txt_descrativ
		parent.childNodes.item(4).childNodes.item(0).disabled = false;
		//formato
		parent.childNodes.item(5).childNodes.item(0).disabled = false;		
		//txt_qtd
		parent.childNodes.item(6).childNodes.item(0).disabled = false;
		
		parent.childNodes.item(7).childNodes.item(0).disabled = false;			
	 
	 }
	 else
	 {
		 //chk_del
		 parent.childNodes.item(3).childNodes.item(1).value = 1;
		 //txt_descriativ
		 parent.childNodes.item(4).childNodes.item(0).disabled = true;
		 //formato
		 parent.childNodes.item(5).childNodes.item(0).disabled = true;	
		 //txt_qtd
		 parent.childNodes.item(6).childNodes.item(0).disabled = true;
		 
		 parent.childNodes.item(7).childNodes.item(0).disabled = true;
	 }
		
	return true;		
}

function lib_campos_rec(pai)
{	
	 var parent = pai.parentNode.parentNode;
	 
	 if(pai.checked)
	 {
		//chk_drec
		parent.childNodes.item(2).childNodes.item(1).value = 0;		
		//rec_orc
		parent.childNodes.item(2).childNodes.item(0).disabled = false;		
		//funcionario
		parent.childNodes.item(3).childNodes.item(0).disabled = false;
		//quantidade
		parent.childNodes.item(4).childNodes.item(0).disabled = false;		
 
	 }
	 else
	 {
		 //chk_del
		 parent.childNodes.item(2).childNodes.item(1).value = 1;
		//rec_orc
		parent.childNodes.item(2).childNodes.item(0).disabled = true;	
		//funcionario
		parent.childNodes.item(3).childNodes.item(0).disabled = true;
		//quantidade
		parent.childNodes.item(4).childNodes.item(0).disabled = true;		
	 }
		
	return true;		
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	function sel_tab(idNew,idOld)
	{
		//ativa quando seleciona a tab		
		switch(idNew)
		{
			case "a10_":
			
				document.getElementById('status').disabled = false;
				
				xajax_atualizatabela(xajax.getFormValues('frm'));
															
			break;
			
			case "a20_":
				
				document.getElementById('status').disabled = true;	
				
				xajax_preencheEscopoGeral(xajax.getFormValues('frm'));
				
			break;
			
			case "a30_":
				
				document.getElementById('status').disabled = true;	
				
				xajax_seleciona_escopo_geral(xajax.getFormValues('frm'));
			break;
			
			case "a40_":
				
				document.getElementById('status').disabled = true;
					
				xajax_preenche_resumo(xajax.getFormValues('frm'));
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a10_", "Proposta", null, null, true);
	myTabbar.addTab("a20_", "Escopo&nbsp;Geral");
	myTabbar.addTab("a30_", "Escopo&nbsp;Detalhado");
	myTabbar.addTab("a40_", "Resumo");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a30_").attachObject("a30");
	myTabbar.tabs("a40_").attachObject("a40");
	
	myTabbar.enableAutoReSize(true);
}

function adiciona_linha(row_index)
{
	id = mygrid.getRowId(row_index);
	
	nid = id.split('_');

	nid[1]++;
	
	mygrid.addRow(nid[0]+'_'+nid[1],'',row_index+1);
	
	mygrid.copyRowContent(id,nid[0]+'_'+nid[1]);
	
	var elements = $('.chk_escopodet_'+nid[0]);	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'chk_escopodet['+nid[0]+']['+i+']';
		elements[i].name = 'chk_escopodet['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.chk_codigo_'+nid[0]);	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'chk_codigo['+nid[0]+']['+i+']';
		elements[i].name = 'chk_codigo['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.hd_fmt_'+nid[0]);	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'hd_fmt['+nid[0]+']['+i+']';
		elements[i].name = 'hd_fmt['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.hd_qtd_'+nid[0]);	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'hd_qtd['+nid[0]+']['+i+']';
		elements[i].name = 'hd_qtd['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.hd_horas_'+nid[0]);	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'hd_horas['+nid[0]+']['+i+']';
		elements[i].name = 'hd_horas['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}

	//var elements = document.getElementsByClassName('txt_descativ_'+nid[0]);
	var elements = $('.txt_descativ_'+nid[0]);
	var idNovo = elements.length;

	//var j = 0;
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_descativ['+nid[0]+']['+i+']';
		elements[i].name = 'txt_descativ['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.cb_fmt_'+nid[0]);
	var idNovo = elements.length;

	//var j = 0;
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'cb_fmt['+nid[0]+']['+i+']';
		elements[i].name = 'cb_fmt['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	//var elements = document.getElementsByClassName('txt_qtd_'+nid[0]);
	var elements = $('.txt_qtd_'+nid[0]);
	var idNovo = elements.length;

	//var j = 0;
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_qtd['+nid[0]+']['+i+']';
		elements[i].name = 'txt_qtd['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}

	//var elements = document.getElementsByClassName('txt_qtd_'+nid[0]);
	var elements = $('.txt_horas_'+nid[0]);
	var idNovo = elements.length;

	//var j = 0;
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'txt_horas['+nid[0]+']['+i+']';
		elements[i].name = 'txt_horas['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
	
	var elements = $('.img_rec_'+nid[0]);
	var idNovo = elements.length;

	//var j = 0;
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'img_rec['+nid[0]+']['+i+']';
		elements[i].name = 'img_rec['+nid[0]+']['+i+']';
		elements[i].lang = nid[0]+'_'+i;
	}
		
	return true;
}

function adiciona_linha_recurso(row_index)
{
	id = mygrid4.getRowId(row_index);
	
	nid = id.split('_');

	nid[1]++;
	
	mygrid4.addRow(nid[0]+'_'+nid[1],'',row_index+1);
	
	mygrid4.copyRowContent(id,nid[0]+'_'+nid[1]);
	
	//checkbox
	var elements = $('.chk_recurso_'+nid[0]);
	
	var idNovo = elements.length;
		
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'chk_recurso['+nid[0]+']['+i+']';
		elements[i].name = 'chk_recurso['+nid[0]+']['+i+']';
	}
	
	//combo categorias	
	var elements = $('.rec_orc_'+nid[0]);
	
	var idNovo = elements.length;
	
	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'rec_orc['+nid[0]+']['+i+']';
		elements[i].name = 'rec_orc['+nid[0]+']['+i+']';
	}
	
	//funcionarios
	var elements = $('.funcionario_'+nid[0]);
	
	var idNovo = elements.length;

	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'funcionario['+nid[0]+']['+i+']';
		elements[i].name = 'funcionario['+nid[0]+']['+i+']';
	}

	var elements = $('.chk_drec_'+nid[0]);

	var idNovo = elements.length;

	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'chk_drec['+nid[0]+']['+i+']';
		elements[i].name = 'chk_drec['+nid[0]+']['+i+']';
	}
	
	//quantidade recursos
	var elements = $('.rec_horas_'+nid[0]);

	var idNovo = elements.length;

	for(i = 0; i < idNovo; i ++)
	{
		//TR pai de todos na linha
		tr = elements[i].parentNode.parentNode;
		
		elements[i].id = 'rec_horas['+nid[0]+']['+i+']';
		elements[i].name = 'rec_horas['+nid[0]+']['+i+']';
	}
	
	return true;
}

function adiciona_recursos(id_tarefa, id_escopo_geral, id_escopo_detalhado)
{
	var id = id_tarefa.split("_");
	
	conteudo = '<form name="frm_recursos" id="frm_recursos" action="projeto_planejamento.php" method="post">';
	conteudo += '<table>';
	conteudo += '<tr>';
	conteudo += '<td>';
	conteudo += '<label class="labels"><div id="tarefa">&nbsp;</div><div id="horas">&nbsp;</div>&nbsp;</label>';
	conteudo += '</td>';
	conteudo += '</tr>';
	conteudo += '</table>';
	conteudo += '<div id="div_recursos">&nbsp;</div><br />';
	conteudo += '<input name="btn_recursos" type="button" id="btn_recursos" class="class_botao" value="Incluir" onclick=xajax_inc_recursos(xajax.getFormValues("frm_recursos",true));>';
	conteudo += '<input name="id_escopo_geral" id="id_escopo_geral" type="hidden" value="'+id_escopo_geral+'">';
	conteudo += '<input name="id_escopo_detalhado" id="id_escopo_detalhado" type="hidden" value="'+id_escopo_detalhado+'">';
	conteudo += '<input name="id_modal" id="id_modal" type="hidden" value="'+id[1]+'">';
	conteudo += '</form>';
	
	modal(conteudo, 'g', 'RECURSOS',id[1]);
	
	xajax_mostra_recursos(id[0], id_escopo_geral, id_escopo_detalhado);
	
	return true;	
}

function grid(tabela, autoh, height, xml)
{
	if (tabela != 'div_resumo')
	{
		switch (tabela)
		{
			case 'div_dados_cliente':
			
				mygrid1 = new dhtmlXGridObject(tabela);
				mygrid1.enableAutoHeight(autoh,height);
				mygrid1.enableRowsHover(true,'cor_mouseover');
			
				function doOnRowSelected1(row,col)
				{
					if(col<=3)
					{						
						xajax_editar(row);
			
						return true;
					}
				}
				
				mygrid1.attachEvent("onRowSelect",doOnRowSelected1);	
				mygrid1.setHeader("&nbsp;,Proposta, Descri��o,I/E",
					null,
					["text-align:center","text-align:center","text-align:center","text-align:center"]);
				mygrid1.setInitWidths("22,80,*,35");
				mygrid1.setColAlign("center,left,left,center");
				mygrid1.setColTypes("ro,ro,ro,ro");
				mygrid1.setColSorting("str,str,str,str");
				
				mygrid1.setSkin("dhx_skyblue");
				mygrid1.enableMultiselect(true);
				mygrid1.enableCollSpan(true);	
				mygrid1.init();
				mygrid1.loadXMLString(xml);
				
			break;
			
			case 'div_escopo_geral':
			
				mygrid2 = new dhtmlXGridObject(tabela);
				mygrid2.enableAutoHeight(autoh,height);
				mygrid2.enableRowsHover(true,'cor_mouseover');
			
				function doOnRowSelected2(row,col)
				{
					if(col<=0)
					{
						xajax_editar(row);
					
						return true;
					}
				}
				
				mygrid2.attachEvent("onRowSelect",doOnRowSelected2);	
					
				mygrid2.setHeader("Escopo&nbsp;Geral,E",
					null,
					["text-align:left","text-align:center"]);
				mygrid2.setInitWidths("*,25");
				mygrid2.setColAlign("left,center");
				mygrid2.setColTypes("ro,ro");
				mygrid2.setColSorting("str,str");
				
				mygrid2.setSkin("dhx_skyblue");
				mygrid2.enableMultiselect(true);
				mygrid2.enableCollSpan(true);	
				mygrid2.init();
				mygrid2.loadXMLString(xml);
				
			break;
			
			case 'div_escopo_detalhado':

				mygrid = new dhtmlXGridObject(tabela);

				mygrid.enableAutoHeight(autoh,height);
				mygrid.enableRowsHover(true,'cor_mouseover');
				
				mygrid.setHeader("&nbsp;,&nbsp;,C&oacute;digo,Tarefa,Descri&ccedil;&atilde;o,Formato,Qtd,Horas",
					null,
					["text-align:center","text-align:center","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left"]);
				mygrid.setInitWidths("25,25,55,*,*,60,100,100");
				mygrid.setColAlign("center,center,left,left,left,left,left,left");
				mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid.setColSorting("str,str,str,str,str,str,str,str");
				
				mygrid.setSkin("dhx_skyblue");
				mygrid.enableMultiselect(true);
				mygrid.enableCollSpan(true);	
				mygrid.init();
				mygrid.loadXMLString(xml);
				
			break;
			
			case 'div_recursos':
			
				mygrid4 = new dhtmlXGridObject(tabela);

				mygrid4.enableAutoHeight(autoh,height);
				mygrid4.enableRowsHover(true,'cor_mouseover');
				
				mygrid4.setHeader("&nbsp;,&nbsp;,Recurso&nbsp;orc,Recurso,Horas",
					null,
					["text-align:center","text-align:left","text-align:left","text-align:left","text-align:left"]);
				mygrid4.setInitWidths("25,25,300,400,70");
				mygrid4.setColAlign("center,center,left,left,left");
				mygrid4.setColTypes("ro,ro,ro,ro,ro");
				mygrid4.setColSorting("str,str,str,str,str");
				
				mygrid4.setSkin("dhx_skyblue");
				mygrid4.enableMultiselect(true);
				mygrid4.enableCollSpan(true);	
				mygrid4.init();
				mygrid4.loadXMLString(xml);
				
			break;	
						
		}
			

	}
	else
	{
		mygrid_resumo = new dhtmlXGridObject(tabela);

		mygrid_resumo.enableAutoHeight(autoh,height);
		mygrid_resumo.enableRowsHover(true,'cor_mouseover');
		
		//seta o id da linha para retornar a posi��o original
		mygrid_resumo.attachEvent("onRowSelect", function(id,ind){
    		document.getElementById("row_id").value = id;
		});		
		
		mygrid_resumo.setHeader("Escopo&nbsp;Geral,Disciplina,Tarefa,Descri��o,Fmt,Qtd,Horas,R");
		mygrid_resumo.setInitWidths("85,80,50,*,*,60,60,25");
		mygrid_resumo.setColAlign("left,left,left,left,left,left,left,center");
		mygrid_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
		mygrid_resumo.setColSorting("str,str,str,str,str,str,str,str");

		mygrid_resumo.setSkin("dhx_skyblue");
	    mygrid_resumo.enableMultiselect(true);
	    mygrid_resumo.enableCollSpan(true);
		
		//mygrid_resumo.selectRow(0,true,true,true);
			
		mygrid_resumo.init();
		mygrid_resumo.loadXMLString(xml);
	}	
}

function rowid()
{
	var id = document.getElementById("row_id").value;
	
	if(id!='')
	{
		mygrid_resumo.selectRowById(id);
	}
	
	document.getElementById("row_id").value = "";
	
	return true;	
}

function imprimir()
{
	document.getElementById('frm').action="relatorios/rel_horas_prevista_projetada.php";

	document.getElementById('frm').submit();	
}

</script>

<?php
$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_disciplina_values[] = $regs["id_setor"];
	$array_disciplina_output[] = $regs["setor"];
}

$array_status = array(0=>'TODAS',3=>'EXPORTADO',2=>'IMPORTADO',1=>'N�O IMPORTADO');

foreach ($array_status as $chave=>$valor)
{
	$array_status_values[] = $chave;
	$array_status_output[] = $valor;
}

$smarty->assign("option_disciplina_values",$array_disciplina_values);
$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('projeto_planejamento'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('projeto_planejamento.tpl');

?>

