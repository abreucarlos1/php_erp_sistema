<?php
/*
		Formulário de Documentos de Referência
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../arquivotec/documentos_referencia.php
		
		Versão 0 --> VERSÃO INICIAL (11/09/2008)
		Versão 1 --> ATUALIZAÇÃO (16/03/2012) : 
					  Mudanças nas formas de armazenamento das pastas;
					  Abrange CI, ATAS, Planejamento e Propostas
		Versão 2 --> Atualização  upload  - 01/09/2013 - Carlos Abreu			  
		Versão 3 --> Alteração de layout  - 24/11/2014 - Carlos Eduardo
		Versão 4 --> Inclusão de campo texto - 13/01/2015 - Carlos Abreu
		Versão 5 --> Inclusão de campo Serviço - 30/06/2015 - Carlos Eduardo
		Versão 6 --> Alteração na forma de inclusão de referencias - 03/05/2016 - Carlos Abreu
		Versão 7 --> E-mail para todos os envolvidos na OS na inclusão de referencias - 29/08/2016 - Carlos Eduardo
		Versão 8 --> Atualização layout - Carlos Abreu - 22/03/2017
		Versão 9 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//require_once(INCLUDE_DIR."include_email.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(33))
{
	nao_permitido();
}

$conf = new configs();

function lista_autorizados()
{
	//$lista_arqtec = array('6','978','909','1046','1213');

	return $lista_arqtec;
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	return $resposta;

}

function preenchetipodoc($id_disciplina,$tab = 1, $id_selecionado=0)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	if($tab == 1)
	{
		$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia ";
		$sql .= "WHERE tipos_documentos_referencia.id_disciplina = '" . $id_disciplina . "' ";
		$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
		$sql .= "ORDER BY tipos_documentos_referencia.tipo_documento ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dados.");
		}
	
		$jscript = "sel_ativ = document.getElementById('id_tipo_doc'); ";
		$jscript .= "sel_ativ.options.length = 0; ";
		$jscript .= "sel_ativ.options[0] = new Option ('SELECIONE',''); ";
		
		foreach($db->array_select as $reg)
		{
			$jscript .= "sel_ativ.options[sel_ativ.options.length] = new Option ('" . $reg["tipo_documento"] . "','" . $reg["id_tipos_documentos_referencia"] . "'); ";
	
			if($reg["id_tipos_documentos_referencia"]==$id_selecionado)
			{
				$jscript .= "sel_ativ.options[sel_ativ.options.length-1].selected=true; ";
			}
		}
	
		$resposta->addScript($jscript);
	}
	else
	{
		$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia ";
		$sql .= "WHERE tipos_documentos_referencia.id_disciplina = '" . $id_disciplina . "' ";
		$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
		$sql .= "ORDER BY tipos_documentos_referencia.tipo_documento ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dados.");
		}
	
		$jscript = "sel_ativ = document.getElementById('inc_id_tipo_doc'); ";
		$jscript .= "sel_ativ.options.length = 0; ";
		$jscript .= "sel_ativ.options[0] = new Option ('SELECIONE',''); ";
		
		foreach($db->array_select as $reg)
		{
			$jscript .= "sel_ativ.options[sel_ativ.options.length] = new Option ('" . $reg["tipo_documento"] . "','" . $reg["id_tipos_documentos_referencia"] . "'); ";
	
			if($reg["id_tipos_documentos_referencia"]==$id_selecionado)
			{
				$jscript .= "sel_ativ.options[sel_ativ.options.length-1].selected=true; ";
			}
		}
	
		$resposta->addScript($jscript);		
	}
	
	return $resposta;
}

function preenchetiporef($id_tipo, $tab = 1)
{
	$resposta = new xajaxResponse();
	
	if($tab == 1) //visualiza - altera
	{
		$resposta->addAssign("tecnica","style.display","none");
		
		switch ($id_tipo)
		{
			case '1':
				$resposta->addAssign("tecnica","style.display","block");
			break;
			
			default:
				
				$resposta->addScript("seleciona_combo('', 'id_disciplina');");
				$resposta->addScript("seleciona_combo('', 'id_formato');");
				$resposta->addScript("seleciona_combo('', 'id_tipo_doc');");
				$resposta->addAssign("num_grd","value","");
				$resposta->addAssign("origem","value","");
				$resposta->addScript("xajax.$('chk_edital').checked=false;");
				$resposta->addScript("xajax.$('chk_cert').checked=false;");		
				
		}
	}
	else
	{
		$resposta->addAssign("inc_tecnica","style.display","none");
		
		switch ($id_tipo)
		{
			case '1':
				$resposta->addAssign("inc_tecnica","style.display","block");
			break;
			
			default:
				
				$resposta->addScript("seleciona_combo('', 'inc_id_disciplina');");
				$resposta->addScript("seleciona_combo('', 'inc_id_formato');");
				$resposta->addScript("seleciona_combo('', 'inc_id_tipo_doc');");
				$resposta->addAssign("num_grd","value","");
				$resposta->addAssign("origem","value","");
				$resposta->addScript("xajax.$('inc_chk_edital').checked=false;");
				$resposta->addScript("xajax.$('inc_chk_cert').checked=false;");				
		}	
	}

	return $resposta;
}

function lib_rev($perm_rev)
{
	$resposta = new xajaxResponse();
	
	if($perm_rev)
	{
		$resposta->addAssign("versao_documento","disabled","true");
	}
	else
	{
		$resposta->addAssign("versao_documento","disabled","");
	}
	
	return $resposta;	
}

function atualizatabela($dados_form, $limparServico = false, $tab = 1)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();
	
	$filtro = "";
	
	if($tab == 1) //visualiza-alterar
	{
		if($dados_form["busca"])
		{
			$filtro .= "AND (documentos_referencia_revisoes.arquivo LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR documentos_referencia.numero_registro LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR documentos_referencia.numero_documento LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR documentos_referencia.titulo LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR documentos_referencia.palavras_chave LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR tipos_documentos_referencia.tipo_documento LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR tipos_referencia.tipo_referencia LIKE '%" . $dados_form["busca"] . "%' ";
			$filtro .= "OR documentos_referencia.origem LIKE '%" . $dados_form["busca"] . "%') ";
		}
		
		if($dados_form["id_os"])
		{
			$filtro .= "AND ordem_servico.id_os = '" . $dados_form["id_os"] . "' ";
		}
		
		if($dados_form["id_disciplina"])
		{
			$filtro .= "AND documentos_referencia.id_disciplina = '" . $dados_form["id_disciplina"] . "' ";	
		}
		
		if($dados_form["id_tipo_doc"])
		{
			$filtro .= "AND documentos_referencia.id_tipo_documento_referencia = '" . $dados_form["id_tipo_doc"] . "' ";	
		}
	
		if($dados_form["tipo_doc"])
		{
			$filtro .= "AND tipos_referencia.id_tipo_referencia = '" . $dados_form["tipo_doc"] . "' ";	
		}
	
		$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
		$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = documentos_referencia.id_disciplina AND setores.reg_del = 0)";		
		$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
		$sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND documentos_referencia.reg_del = 0 ";
		$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
		$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= $filtro;
		$sql .= "ORDER BY tipos_referencia.tipo_referencia, documentos_referencia.id_documento_referencia DESC, documentos_referencia.numero_registro DESC, setores.setor ";
	
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
		}
		
		$conteudo = "";

		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
		
		foreach($db->array_select as $reg)
		{
			$rowHtml = '&nbsp;';
			
			$os = sprintf("%05d",$reg["os"]);
			
			//Monta a pasta
			//ex: ATAS/MEC
			if($reg["grava_disciplina"]==1)
			{
				$disciplina = $reg["abreviacao"]."/";	
			}
			else
			{
				$disciplina = "";	
			}
			
			//monta diretorio base
			$diretorio = DOCUMENTOS_GED . $reg["abreviacao_GED"] . "/" . $reg["os"] . "-" .$reg["descricao"] . "/" . $reg["os"] . REFERENCIAS . $reg["pasta_base"] . "/".$disciplina;
		
			$ec = "";
		
			if($reg["edital"])
			{
				$ec = "E";
			}
			
			if($reg["certificado"])
			{
				$ec = "C";
			}
				
			//Forma a tabela
			$xml->startElement('row');
				$xml->writeAttribute('id', 'reg_'.$reg['id_documento_referencia']);
				$xml->writeElement('cell',$reg["numero_registro"]);
				$xml->writeElement('cell',$reg["numero_documento"]);
				$xml->writeElement('cell',$os);
				$xml->writeElement('cell',$reg["setor"]);			
				$xml->writeElement('cell',$reg["tipo_documento"]);		
				$xml->writeElement('cell',addslashes($reg["titulo"]));			
				$xml->writeElement('cell',mysql_php($reg["data_registro"]));
				$xml->writeElement('cell',$reg["formato"]);
				$xml->writeElement('cell',$reg["versao_documento"].'.'.$reg['revisao_documento']);
				$xml->writeElement('cell',$ec);
				
				if($reg["arquivo"]!="")
				{
					$rowHtml = '<img src="'.DIR_IMAGENS.'anexo.png" onclick=open_doc("'.urlencode($diretorio.$reg["arquivo"]).'") title="Abrir&nbsp;Documento">';
				}
				else
				{
					$rowHtml = '&nbsp;';	
				}
				
				$xml->writeElement('cell',$rowHtml);
	
				$rowHtml = '&nbsp;';
				
				//permite apagar o registro
				if(in_array($_SESSION["id_funcionario"], lista_autorizados()))
				{
					$rowHtml = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATENÇÃO:&nbsp;Todos&nbsp;os&nbsp;dados&nbsp;referentes&nbsp;a&nbsp;esse&nbsp;documento&nbsp;de&nbsp;referência&nbsp;serão&nbsp;EXCLUÍDOS&nbsp;definitivamente.&nbsp;Deseja&nbsp;continuar?")){xajax_excluir("'.$reg["id_documento_referencia"].'",1);} title="Excluir&nbsp;Documento">';
					
					$xml->writeElement('cell',$rowHtml);
				}
				
			$xml->endElement();
		}
	
		$xml->endElement();
	
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('div_docs_referencia', true, '130', '".$conteudo."');");
	
		if ($dados_form['inc_id_os'])
		{
			//Criando o campo de serviçoos
			$sql = "SELECT * FROM ".DATABASE.".servicos ";
			$sql .= "WHERE servicos.reg_del = 0 ";
			$sql .= "AND servicos.id_os = ".$dados_form['inc_id_os']." ";
			$sql .= "ORDER BY servicos.servico_descricao ";
	
			if ($limparServico)
			{
				$resposta->addScript("limpa_combo('servico')");
				
				$resposta->addScript("addOption('servico', 'SELECIONE', '')");
				
				$db->select($sql,'MYSQL',true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
				}
				
				foreach($db->array_select as $reg)
				{
					$resposta->addScript("addOption('servico', '".$reg['servico']." - ".$reg['servico_descricao']."', '".$reg['servico_id']."')");
				}
			}
		}	
	}
	else
	{
		$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_inclusao ";
		$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia_inclusao.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia_inclusao.id_disciplina = setores.id_setor AND setores.reg_del = 0)";		
		$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia_inclusao.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
		$sql .= "WHERE documentos_referencia_inclusao.id_os = ordem_servico.id_os ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= "AND documentos_referencia_inclusao.reg_del = 0 ";
		$sql .= "AND documentos_referencia_inclusao.id_os = '".$dados_form["inc_id_os"]."' ";
		$sql .= "ORDER BY tipos_referencia.tipo_referencia, documentos_referencia_inclusao.id_documento_ref_inclusao DESC, documentos_referencia_inclusao.numero_registro DESC, setores.setor ";
	
		$db->select($sql, 'MYSQL', true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
		}
		
		$num_regs = $db->numero_registros;
		
		if($num_regs>0)
		{
			$resposta->addScript("document.getElementById('btnconcluir').disabled=false");
		}
		else
		{
			$resposta->addScript("document.getElementById('btnconcluir').disabled=true");
		}
		
		$conteudo = "";
		
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
		
		foreach($db->array_select as $reg)
		{
			$rowHtml = '&nbsp;';
			
			$os = sprintf("%05d",$reg["os"]);
			
			//Monta a pasta
			//ex: ATAS/MEC
			if($reg["grava_disciplina"]==1)
			{
				$disciplina = $reg["abreviacao"]."/";	
			}
			else
			{
				$disciplina = "";	
			}
			
			//monta diretorio base
			$diretorio = DOCUMENTOS_GED . $reg["abreviacao_GED"] . "/" . $reg["os"] . "-" .$reg["descricao"] . "/" . $reg["os"] . REFERENCIAS . "temp/";
		
			$ec = "";
		
			if($reg["edital"])
			{
				$ec = "E";
			}
			
			if($reg["certificado"])
			{
				$ec = "C";
			}
				
			//Forma a tabela
			$xml->startElement('row');
				$xml->writeAttribute('id', 'reg_'.$reg['id_documento_ref_inclusao']);
				$xml->writeElement('cell',$os);
				$xml->writeElement('cell',$reg["numero_registro"]);
				$xml->writeElement('cell',$reg["numero_documento"]);				
				$xml->writeElement('cell',$reg["setor"]);			
				$xml->writeElement('cell',$reg["tipo_documento"]);		
				$xml->writeElement('cell',addslashes($reg["titulo"]));			
				$xml->writeElement('cell',mysql_php($reg["data_registro"]));
				$xml->writeElement('cell',$reg["formato"]);
				$xml->writeElement('cell',$reg["versao_documento"]);
				$xml->writeElement('cell',$ec);
				
				if($reg["strarquivo"]!="")
				{
					$rowHtml = '<img src="'.DIR_IMAGENS.'anexo.png" onclick=open_doc("'.urlencode($diretorio.$reg["strarquivo"]).'") title="Abrir&nbsp;Documento">';
				}
				else
				{
					$rowHtml = '&nbsp;';	
				}
				
				$xml->writeElement('cell',$rowHtml);
	
				$rowHtml = '&nbsp;';
				
				//permite o editor apagar o registro
				if(in_array($_SESSION["id_funcionario"], lista_autorizados()))
				{
					$rowHtml = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATENÇÃO:&nbsp;Todos&nbsp;os&nbsp;dados&nbsp;referentes&nbsp;a&nbsp;esse&nbsp;documento&nbsp;de&nbsp;referência&nbsp;serão&nbsp;EXCLUÍDOS&nbsp;definitivamente.&nbsp;Deseja&nbsp;continuar?")){xajax_excluir("'.$reg["id_documento_ref_inclusao"].'",0);} title="Excluir&nbsp;Documento">';
					
					$xml->writeElement('cell',$rowHtml);
				}
				
			$xml->endElement();
		}
	
		$xml->endElement();
	
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('inc_div_docs_referencia', true, '260', '".$conteudo."');");
	
		if ($dados_form['id_os'])
		{
			//Criando o campo de serviços
			$sql = "SELECT * FROM ".DATABASE.".servicos ";
			$sql .= "WHERE servicos.reg_del = 0 ";
			$sql .= "AND servicos.id_os = ".$dados_form['id_os']." ";
			$sql .= "ORDER BY servicos.servico_descricao ";
	
			if ($limparServico)
			{
				$resposta->addScript("limpa_combo('servico')");
				
				$resposta->addScript("addOption('servico', 'SELECIONE', '')");
				
				$db->select($sql,'MYSQL', true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
				}
				
				foreach($db->array_select as $reg)
				{
					$resposta->addScript("addOption('servico', '".$reg['servico']." - ".$reg['servico_descricao']."', '".$reg['servico_id']."')");
				}
			}
		}		
	}
	
	return $resposta;
}

function atualizatabela_revisoes($id_documento_referencia)
{
	$resposta = new xajaxResponse();

	$xml = new XMLWriter();

	$db = new banco_dados();

	$sql = "SELECT funcionario, id_funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: ". $db->erro);
	}
	
	foreach($db->array_select as $reg)
	{
		$array_funcionarios[$reg["id_funcionario"]] = $reg["funcionario"];
	}	

	$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
	$sql .= "WHERE documentos_referencia_revisoes.id_documento_referencia = '" . $id_documento_referencia . "' ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_os = ordem_servico.id_os ";
	$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND documentos_referencia_revisoes.id_documento_referencia = documentos_referencia.id_documento_referencia ";
	$sql .= "ORDER BY revisao_documento DESC, id_documentos_referencia_revisoes DESC ";
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
	}
	
	$num_regs = $db->numero_registros;
	
	$array_docs = $db->array_select;
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($array_docs as $reg)
	{
		//verifica se a versão atual
		$sql = "SELECT * FROM ".DATABASE.".documentos_referencia ";
		$sql .= "WHERE documentos_referencia.id_documento_referencia = '".$reg["id_documento_referencia"]."' ";
		$sql .= "AND documentos_referencia.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dados: ".$db->erro);
		}
		
		$regs_ver = $db->array_select[0];
		
		//verifica se existe as disciplinas nas pastas
		//ex: ATAS/MEC
		if($reg["grava_disciplina"]==1)
		{
			$disciplina = $reg["abreviacao"]."/";	
		}
		else
		{
			$disciplina = "";	
		}
		
		$array_rpl = array("/",".",":","&");
		
		$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg["abreviacao_GED"])));		
		
		$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg["descricao"])));
		
		//monta diretorio base
		$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg["os"] . "-" .$descricao_os . "/" . $reg["os"] . REFERENCIAS . $reg["pasta_base"] . "/".$disciplina;
				
		$ec = "";
	
		if($reg["edital"])
		{
			$ec = "E";
		}
		
		if($reg["certificado"])
		{
			$ec = "C";
		}
	
		$xml->startElement('row');			
			$xml->writeAttribute('id', $reg['id_documentos_referencia_revisoes']);
			$xml->writeElement('cell', $reg["versao_documento"].".".$reg["revisao_documento"]);
			$xml->writeElement('cell', mysql_php($reg["data_registro"]));
			$xml->writeElement('cell', $reg["numero_grd_cliente"]);
			$xml->writeElement('cell', $reg["nome_arquivo"]);
			$xml->writeElement('cell', $array_funcionarios[$reg["id_autor"]]);
			$xml->writeElement('cell', $array_funcionarios[$reg["id_editor"]]);
			$xml->writeElement('cell', $ec);
			
			//se os id forem diferentes, documento atual
			if($regs_ver["id_documento_referencia_revisoes"]==$reg["id_documentos_referencia_revisoes"])
			{
				if($reg["arquivo"]!="")
				{
					$html = '<img src="'.DIR_IMAGENS.'anexo.png" onclick=open_doc("'. urlencode($diretorio.$reg["arquivo"]).'") />';
				}
				else
				{
					$html = '&nbsp;';	
				}
				
			}
			else
			{
				if($reg["arquivo"]!="")
				{
					$html = '<img src="'.DIR_IMAGENS.'anexo.png" onclick=open_doc("'. urlencode($diretorio."_versoes/".$reg["arquivo"].".".$reg["id_documentos_referencia_revisoes"]).'") />';
				}
				else
				{
					$html = '&nbsp;';	
				}
			}
			
			$xml->writeElement('cell', $html);
			
			if(in_array($_SESSION["id_funcionario"], lista_autorizados()))
			{			
				$img_exc = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATENÇÃO:&nbsp;O&nbsp;documento&nbsp;de&nbsp;referência&nbsp;será&nbsp;EXCLUÍDO&nbsp;definitivamente.&nbsp;Deseja&nbsp;continuar?")){xajax_excluir("'.$reg["id_documentos_referencia_revisoes"].'",2);} title="Excluir&nbsp;Documento">';
			}
			else
			{
				$img_exc = '&nbsp;';	
			}
			
			$xml->writeElement('cell', $img_exc);			
			
		$xml->endElement();				
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('div_revisoes', true, '100', '".$conteudo."');");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$id = explode('_', $id);
	
	$id = $id[1];
	
	$db = new banco_dados();

	$sql = "SELECT * FROM ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia  ";	
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN  ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
	$sql .= "WHERE documentos_referencia.id_documento_referencia = '".$id."' ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
	}

	$reg = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $reg["id_tipo_referencia"] . "', 'tipo_doc');");
	
	$resposta->addAssign("tecnica","style.display","none");
	
	$resposta->addAssign("com_interna","style.display","none");
		
	//referencia tecnica	
	switch ($reg["id_tipo_referencia"])
	{
		case 1:
			$resposta->addAssign("tecnica","style.display","block");			
			
			$resposta->addScript("seleciona_combo('" . $reg["id_disciplina"] . "', 'id_disciplina');");
			
			$resposta->addScript("seleciona_combo('" . $reg["id_formato"] . "', 'id_formato');");
			
			$resposta->addAssign("origem","value",$reg["origem"]);
			
			if($reg["edital"]=="1")
			{
				$resposta->addScript("xajax.$('chk_edital').checked=true;");
			}
			else
			{	
				$resposta->addScript("xajax.$('chk_edital').checked=false;");
			}
			
			if($reg_docs["certificado"]=="1")
			{
				$resposta->addScript("xajax.$('chk_cert').checked=true;");	
			}
			else
			{
				$resposta->addScript("xajax.$('chk_cert').checked=false;");
			}		
		break;

	}
	
	$resposta->addAssign("perm_rev","disabled","");

	//incrementa a versao_documento corrente
	$resposta->addAssign("versao_documento","value",$reg["versao_documento"]);
	
	$resposta->addScript("xajax_preenchetipodoc('". $reg["id_disciplina"] . "','1','".$reg["id_tipo_documento_referencia"] . "'); ");
	
	$resposta->addScript("seleciona_combo('" . $reg["id_os"] . "', 'id_os');");
	
	$resposta->addScript("seleciona_combo('" . $reg["servico_id"] . "', 'servico');");

	$resposta->addAssign("numero_registro","value",$reg["numero_registro"]);
	
	$resposta->addAssign("numero_documento","value",$reg["numero_documento"]);
	
	$resposta->addAssign("num_grd","value",$reg["numero_grd_cliente"]);
	
	$resposta->addAssign("arquivo_ed","value",$reg["arquivo"]);
	
	$resposta->addAssign("titulo","value",$reg["titulo"]);
	
	$resposta->addAssign("id_documento_referencia","value",$reg["id_documento_referencia"]);
	
	$resposta->addAssign("palavras_chave","value",$reg["palavras_chave"]);
	
	$resposta->addScript("document.getElementById('btnalterar').disabled=false");
	
	$resposta->addAssign("acao", "value", "atualizar");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	
	
	$resposta->addScript("xajax_atualizatabela_revisoes('".$reg["id_documento_referencia"]."'); ");

	return $resposta;
}

function excluir($id_doc_referencia,$funcao = 0)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$arquivo = false;
	
	//documentos a incluir (tab 2)
	if($funcao==0)
	{
		//Seleciona os dados do arquivo a ser excluído
		$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia_inclusao ";
		$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia_inclusao.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia_inclusao.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";	
		$sql .= "WHERE documentos_referencia_inclusao.id_os = ordem_servico.id_os ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND tipos_referencia.reg_del = 0 ";
		$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
		$sql .= "AND documentos_referencia_inclusao.reg_del = 0 ";
		$sql .= "AND documentos_referencia_inclusao.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
		$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= "AND documentos_referencia_inclusao.id_documento_ref_inclusao = '".$id_doc_referencia."' ";
	
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
		}
		
		$reg_docs = $db->array_select[0];
		
		$os = sprintf("%05d",$reg_docs["os"]);
		
		$id_doc_ref_inc = $reg_docs["id_documento_ref_inclusao"];	
		
		if($reg_docs["strarquivo"]!='')
		{
			//monta diretorio base
			$diretorio = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . REFERENCIAS . "temp/";
		
			//se existir o arquivo
			if(is_file($diretorio.$reg_docs["strarquivo"]))
			{
				//Se ainda não existir a pasta de excluidos no diretório do arquivo, cria
				if(!is_dir($diretorio . "_excluidos"))
				{
					//Se a criação do diretório não for feita com sucesso
					if(!mkdir($diretorio . "_excluidos",0777,true))
					{
						$erro = "Erro ao criar o diretório de versões.";
					}
				
				}
				
				//move para o diretorio de excluidos
				$remove_arquivo = rename($diretorio.$reg_docs["strarquivo"],$diretorio."_excluidos/".$reg_docs["strarquivo"].".".$id_doc_ref_inc);				
				
				if(!$remove_arquivo)
				{
					$resposta->addAlert("Erro ao remover o arquivo");	
				}
			}			
		}
		
		$usql = "UPDATE ".DATABASE.".documentos_referencia_inclusao SET ";
		$usql .= "documentos_referencia_inclusao.reg_del = 1, ";
		$usql .= "documentos_referencia_inclusao.reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "documentos_referencia_inclusao.data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE documentos_referencia_inclusao.id_documento_ref_inclusao = '".$id_doc_ref_inc."' ";
		
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível excluir o registro: ".$db->erro);
		}
		else
		{
			$resposta->addAlert("Excluido com sucesso.");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),false,0);");	
		}			
	}
	else //visualiza (tab 0)
	{
		if($funcao==1)//arquivo de referencia
		{			
			//Seleciona os dados do arquivo a ser excluído
			$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
			$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
			$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";
			$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
			$sql .= "LEFT JOIN  ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
			$sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND documentos_referencia.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
			$sql .= "AND documentos_referencia.id_documento_referencia = '".$id_doc_referencia."' ";
		
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
			}
			
			$reg_docs = $db->array_select[0];
			
			$id_doc_ref_rev = $reg_docs["id_documento_referencia_revisoes"];	
			
			//se tiver arquivo, exclui fisicamente
			if($reg_docs["arquivo"]!='')
			{
				//Monta a pasta
				//ex: ATAS/MEC
				if($reg_docs["grava_disciplina"]==1)
				{
					$disciplina = $reg_docs["abreviacao"]."/";	
				}
				else
				{
					$disciplina = "";	
				}
				
				//monta diretorio base
				$diretorio = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . REFERENCIAS . $reg_docs["pasta_base"] . "/".$disciplina;
			
				if(is_file($diretorio.$reg_docs["arquivo"]))
				{
					$arquivo = true;
				}
				else
				{
					$arquivo = false;
				}	
			}	
		
			//Remove o arquivo do banco de revisoes
			$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
			$usql .= "documentos_referencia_revisoes.reg_del = 1, ";
			$usql .= "documentos_referencia_revisoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "documentos_referencia_revisoes.data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE documentos_referencia_revisoes.id_documentos_referencia_revisoes = '" . $id_doc_ref_rev . "' ";
			
			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("N&atilde;o foi poss&iacute;vel excluir o registro: ".$db->erro);
			}
	
			//seleciona o arquivo da ultima versao_documento/revisao_documento
			$sql = "SELECT * FROM ".DATABASE.".documentos_referencia_revisoes ";
			$sql .= "WHERE documentos_referencia_revisoes.id_documento_referencia = '".$id_doc_referencia."' ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "ORDER BY revisao_documento DESC, versao_documento DESC LIMIT 1 ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
			}
			
			$reg = $db->array_select[0];
			
			//existe + de 1 revisao_documento/versao_documento
			//seta a ultima revisao_documento/versao_documento 
			if($db->numero_registros >0)
			{
				$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
				$usql .= "id_documento_referencia_revisoes = '".$reg["id_documentos_referencia_revisoes"]."' ";
				$usql .= "WHERE documentos_referencia.id_documento_referencia = '".$id_doc_referencia."' ";
				$usql .= "AND documentos_referencia.reg_del = 0 ";
				
				$db->update($usql,'MYSQL');
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
				}
			
				$copia_versao = true;
			}
			else
			{
				$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
				$usql .= "documentos_referencia.reg_del = 1, ";
				$usql .= "documentos_referencia.reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "documentos_referencia.data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE documentos_referencia.id_documento_referencia = '" . $id_doc_referencia . "' ";
				
				$db->update($usql,'MYSQL');
				
				if ($db->erro != '')
				{	
					$resposta->addAlert("N&atilde;o foi poss&iacute;vel excluir o registro: ".$db->erro);
				}
				
				$copia_versao = false;
			}
			
			if($arquivo)
			{
				//Se ainda não existir a pasta de excluidos no diretório do arquivo, cria
				if(!is_dir($diretorio . "_excluidos"))
				{
					//Se a criação do diretório não for feita com sucesso
					if(!mkdir($diretorio . "_excluidos",0777,true))
					{
						$erro = "Erro ao criar o diretório de versões.";
					}
			
				}
				
				//move para o diretorio de excluidos
				$remove_arquivo = rename($diretorio.$reg_docs["arquivo"],$diretorio."_excluidos/".$reg_docs["arquivo"].".".$reg["id_documentos_referencia_revisoes"]);
				
				if(!$remove_arquivo)
				{
					$resposta->addAlert("Erro ao remover o arquivo");	
				}
				else
				{
					if($copia_versao)
					{
						$arq_old = $diretorio."_versoes/".$reg["arquivo"].".".$reg["id_documentos_referencia_revisoes"];
						$arq_new = $diretorio.$reg["arquivo"];
						
						//copia o arquivo de versoes
						$move = copy($arq_old,$arq_new);
						
						if(!$move)
						{
							$resposta->addAlert("Erro ao mover o arquivo");						
						}
						else
						{
							//apaga o arquivo de versoes
							unlink($arq_old);	
						}
					}
				}
			}		

		}
		else //arquivo de versoes
		{
			//Seleciona os dados do arquivo de revisao_documento a ser excluído
			$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
			$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
			$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";
			$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
			$sql .= "LEFT JOIN  ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
			$sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
			$sql .= "AND empresas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND documentos_referencia.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "AND documentos_referencia.id_documento_referencia = documentos_referencia_revisoes.id_documento_referencia ";
			$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
			$sql .= "AND documentos_referencia_revisoes.id_documentos_referencia_revisoes = '".$id_doc_referencia."' ";
		
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
			}
			
			$reg_docs = $db->array_select[0];
			
			//seleciona o arquivo da revisao_documento atual
			$sql = "SELECT * FROM ".DATABASE.".documentos_referencia ";
			$sql .= "WHERE documentos_referencia.id_documento_referencia = '".$reg_docs["id_documento_referencia"]."' ";
			$sql .= "AND documentos_referencia.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
			}
			
			$reg = $db->array_select[0];
			
			//se tiver arquivo, monta o caminho do arquivo
			if($reg_docs["arquivo"]!='')
			{
				//Monta a pasta
				//ex: ATAS/MEC
				if($reg_docs["grava_disciplina"]==1)
				{
					$disciplina = $reg_docs["abreviacao"]."/";	
				}
				else
				{
					$disciplina = "";	
				}
				
				//monta diretorio base
				$diretorio = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . REFERENCIAS . $reg_docs["pasta_base"] . "/".$disciplina;
				
				//verifica de a versão é a atual e monta o caminho
				if($reg["id_documento_referencia_revisoes"]==$reg_docs["id_documentos_referencia_revisoes"])
				{
					$arq_atual = true;
					
					$arq_ver = $reg_docs["arquivo"];
				}
				else
				{
					$arq_atual = false;
					
					$arq_ver = DIRETORIO_VERSOES."/".$reg_docs["arquivo"].".".$reg_docs["id_documentos_referencia_revisoes"];
				}
				
				//se existir o arquivo
				if(is_file($diretorio.$arq_ver))
				{
					$arquivo = true;
				}
				else
				{
					$arquivo = false;
				}	
			}
			
			//exclui o registro
			$usql = "UPDATE ".DATABASE.".documentos_referencia_revisoes SET ";
			$usql .= "documentos_referencia_revisoes.reg_del = 1, ";
			$usql .= "documentos_referencia_revisoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "documentos_referencia_revisoes.data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE documentos_referencia_revisoes.id_documentos_referencia_revisoes = '" . $reg_docs["id_documentos_referencia_revisoes"] . "' ";
			
			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível excluir o registro: ".$db->erro);
			}
			
			//se for o arquivo atual, então seta a ultima versao_documento/revisao_documento
			if($arq_atual)
			{
				//seleciona o arquivo da ultima versao_documento/revisao_documento
				$sql = "SELECT * FROM ".DATABASE.".documentos_referencia_revisoes ";
				$sql .= "WHERE documentos_referencia_revisoes.id_documento_referencia = '".$reg_docs["id_documento_referencia"]."' ";
				$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
				$sql .= "ORDER BY revisao_documento DESC, versao_documento DESC LIMIT 1 ";
				
				$db->select($sql,'MYSQL',true);
				
				if ($db->erro != '')
				{
					$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
				}
				
				$reg1 = $db->array_select[0];
				
				//existe + de 1 revisao_documento/versao_documento
				//seta a ultima revisao_documento/versao_documento 
				if($db->numero_registros >0)
				{
					$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
					$usql .= "id_documento_referencia_revisoes = '".$reg1["id_documentos_referencia_revisoes"]."' ";
					$usql .= "WHERE documentos_referencia.id_documento_referencia = '".$reg_docs["id_documento_referencia"]."' ";
					$usql .= "AND documentos_referencia.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{
						$resposta->addAlert("Erro ao tentar selecionar os dado: " . $db->erro);
					}
				
					$copia_versao = true;
				}
				else
				{
					$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
					$usql .= "documentos_referencia.reg_del = 1, ";
					$usql .= "documentos_referencia.reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "documentos_referencia.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE documentos_referencia.id_documento_referencia = '" . $reg_docs["id_documento_referencia"] . "' ";
					
					$db->update($usql,'MYSQL');
					
					if ($db->erro != '')
					{	
						$resposta->addAlert("Não foi possível excluir o registro: ".$db->erro);
					}
					
					$copia_versao = false;
				}
			}
			
			//se tiver arquivo
			if($arquivo)
			{
				//Se ainda não existir a pasta de excluidos no diretório do arquivo, cria
				if(!is_dir($diretorio . "_excluidos"))
				{
					//Se a criação do diretório não for feita com sucesso
					if(!mkdir($diretorio . "_excluidos",0777,true))
					{
						$erro = "Erro ao criar o diretório de versões.";
					}				
				}
								
				//move para o diretorio de excluidos
				if(is_file($diretorio.$arq_ver))
				{
					$move_arquivo = rename($diretorio.$arq_ver , $diretorio."_excluidos/".$reg_docs["arquivo"].".".$reg_docs["id_documentos_referencia_revisoes"]);	
				}
				
				if(!$move_arquivo)
				{
					$resposta->addAlert("Erro ao mover o arquivo");	
				}
				else
				{
					//se copia revisao_documento
					if($copia_versao)
					{
						$arq_old = $diretorio."_versoes/".$reg1["arquivo"].".".$reg1["id_documentos_referencia_revisoes"];
						
						$arq_new = $diretorio.$reg1["arquivo"];
						
						//copia o arquivo de versoes
						if(is_file($arq_old))
						{
							$move = rename($arq_old,$arq_new);
						}
						
						if(!$move)
						{
							$resposta->addAlert("Erro ao copiar o arquivo");						
						}
					}
				}
			}							
		}
		
		$resposta->addAlert("Documento de referência excluído com sucesso.");

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),false,1);");
		
		$resposta->addScript("xajax_atualizatabela_revisoes('" . $reg_docs["id_documento_referencia"] . "'); ");
	}

	return $resposta;
}

function concluir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$arq_mov = false;
	
	//seleciona os documentos a serem movidos
	$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_inclusao ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia_inclusao.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia_inclusao.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia_inclusao.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";	
	$sql .= "WHERE documentos_referencia_inclusao.id_os = ordem_servico.id_os ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND documentos_referencia_inclusao.reg_del = 0 ";
	$sql .= "AND documentos_referencia_inclusao.id_os = '".$dados_form["inc_id_os"]."' ";
	$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dado: ".$db->erro);
	}
	
	//Adicionado em 29/08/2016
	//Chamado 704
	$arrArquivosMovidos = array();
	
	$array_docs = $db->array_select; 
	
	foreach($array_docs as $reg_docs)
	{
		//insere o registro do documento de referencias
		$isql = "INSERT INTO ".DATABASE.".documentos_referencia (id_tipo_documento_referencia, id_os, id_disciplina, id_formato, numero_registro, sequencial, numero_documento, titulo, palavras_chave, origem, edital, certificado, servico_id) VALUES ( ";
		$isql .= "'".$reg_docs["id_tipo_documento_referencia"]."', ";
		$isql .= "'".$reg_docs["id_os"]."', ";
		$isql .= "'".$reg_docs["id_disciplina"]."', ";
		$isql .= "'".$reg_docs["id_formato"]."', ";
		$isql .= "'".$reg_docs["numero_registro"]."', ";
		$isql .= "'".$reg_docs["sequencial"]."', ";
		$isql .= "'".$reg_docs["numero_documento"]."', ";
		$isql .= "'".$reg_docs["titulo"]."', ";
		$isql .= "'".$reg_docs["palavras_chave"]."', ";
		$isql .= "'".$reg_docs["origem"]."', ";
		$isql .= "'".$reg_docs["edital"]."', ";
		$isql .= "'".$reg_docs["certificado"]."', ";
		$isql .= "'".$reg_docs["servico_id"]."') ";
		
		$db->insert($isql,'MYSQL');
  
		if($db->erro!='')
		{
			$erro = "Erro ao tentar inserir os dados.".$isql;
		} 
		else
		{
			$id_documento_referencia = $db->insert_id;
			
			//Seleciona os dados da versao_documento/revisao_documento atual
			$sql = "SELECT revisao_documento FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
			$sql .= "WHERE documentos_referencia.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.id_documento_referencia = '" . $reg_docs["id_documento_referencia"] . "' ";
			$sql .= "AND documentos_referencia.id_documento_referencia = documentos_referencia_revisoes.id_documento_referencia ";
			$sql .= "ORDER BY revisao_documento DESC LIMIT 1 ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$erro = "Erro ao tentar selecionar os dados.".$sql;
			}
			else
			{			
				$reg_ver = $db->array_select[0];
				
				//se houver registros, incrementa a versão
				if($db->numero_registros>0)
				{
					$revisao_documento = $reg_ver["revisao_documento"]+1;
				}
				else
				{
					$revisao_documento = 0;	
				}
				
				//insere a revisão/versão
				$isql = "INSERT INTO ".DATABASE.".documentos_referencia_revisoes (id_documento_referencia, nome_arquivo, arquivo, versao_documento, revisao_documento, data_registro, data_inclusao, id_autor, numero_grd_cliente) VALUES ( ";
				$isql .= "'".$id_documento_referencia."', ";
				$isql .= "'".$reg_docs["nome_arquivo"]."', ";
				$isql .= "'".$reg_docs["strarquivo"]."', ";
				$isql .= "'".$reg_docs["versao_documento"]."', ";
				$isql .= "'".$revisao_documento."', ";
				$isql .= "'".$reg_docs["data_registro"]."', ";
				$isql .= "'".date('Y-m-d')."', ";
				$isql .= "'".$reg_docs["id_autor"]."', ";
				$isql .= "'".$reg_docs["numero_grd_cliente"]."') ";
				
				$db->insert($isql,'MYSQL');
		  
				if($db->erro!='')
				{
					$erro = "Erro ao tentar inserir os dados.".$isql;
				} 
				else
				{
					$id_documento_referencia_rev = $db->insert_id;
					
					//seta o registro como deletado
					$usql = "UPDATE ".DATABASE.".documentos_referencia_inclusao SET ";
					$usql .= "documentos_referencia_inclusao.reg_del = 1, ";
					$usql .= "documentos_referencia_inclusao.reg_who = '".$_SESSION["id_funcionario"]."', ";
					$usql .= "documentos_referencia_inclusao.data_del = '".date('Y-m-d')."' ";
					$usql .= "WHERE documentos_referencia_inclusao.id_documento_ref_inclusao = '".$reg_docs["id_documento_ref_inclusao"]."' ";
					
					$db->update($usql,'MYSQL');
			  
					if($db->erro!='')
					{
						$erro = "Erro ao tentar inserir os dados.".$isql;									
					}
										
					//atualiza o id no documentos de referencia
					$usql = "UPDATE ".DATABASE.".documentos_referencia SET ";
					$usql .= "documentos_referencia.id_documento_referencia_revisoes = '".$id_documento_referencia_rev."' ";
					$usql .= "WHERE documentos_referencia.id_documento_referencia = '".$id_documento_referencia."' ";
					$usql .= "AND documentos_referencia.reg_del = 0 ";
					
					$db->update($usql,'MYSQL');
			  
					if($db->erro!='')
					{
						$erro = "Erro ao tentar inserir os dados.".$isql;
					}
					
					//se tiver arquivo, copia ao diretório correto
					if($reg_docs["strarquivo"]!='')
					{
						//verifica se grava as disciplinas nas pastas
						//ex: ATAS/MEC
						if($reg_docs["grava_disciplina"]==1)
						{
							$disciplina = $reg_docs["abreviacao"]."/";	
						}
						else
						{
							$disciplina = "";	
						}
						
						//monta diretorio base
						$diretorio_tmp = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . REFERENCIAS . "temp/";
						
						$diretorio = DOCUMENTOS_GED . $reg_docs["abreviacao_GED"] . "/" . $reg_docs["os"] . "-" .$reg_docs["descricao"] . "/" . $reg_docs["os"] . REFERENCIAS . $reg_docs["pasta_base"] . "/".$disciplina;
						
						//verifica se existe o diretorio de destino
						if(!is_dir($diretorio))
						{						
							if(!mkdir($diretorio,0777,true))
							{
								$resposta->addAlert("Erro ao tentar criar a pasta no servidor.");
							}						
						}
						
						//se o arquivo destino não existir, copia
						if(!is_file($diretorio.$reg_docs["strarquivo"]))
						{
							//se existir o arquivo origem, copia
							if(is_file($diretorio_tmp . $reg_docs["strarquivo"]))
							{
								//copia o arquivo do diretorio temporario							
								$move_arquivo = copy($diretorio_tmp . $reg_docs["strarquivo"], $diretorio . $reg_docs["strarquivo"]);
							}

							if(!$move_arquivo)
							{
								$resposta->addAlert("Erro ao mover o arquivo");
								
								$arq_mov = false;	
							}
							else
							{
								//Adicionado em 29/08/2016
								//Chamado 704
								$arrArquivosMovidos[] = array(
															'nome' => $reg_docs["strarquivo"], 
															'rev' => $reg_docs["versao_documento"], 
															'os' => $reg_docs["os"] . " - " .$reg_docs["descricao"] . "/" . $reg_docs["os"], 
															'numCliente' => $reg_docs["numero_documento"],
															'titulo' => $reg_docs["titulo"]);
								 
								$remove = unlink($diretorio_tmp . $reg_docs["strarquivo"]);
								
								if($remove)
								{
									$arq_mov = true;
								}
							}
						}						
					}	
				}			
			}			
		}	
	}
	
	if($arq_mov)
	{
		//Adicionado em 29/08/2016
		//Chamado 704 
		//Realizar o envio do e-mail para os envolvidos		
		$params 			= array();
		
		$params['from']		= "arquivotecnico@dominio.com.br";
		
		$params['from_name']= "ARQUIVO TECNICO";
		
		$params['subject'] 	= "NOVO(S) DOCUMENTO(S) DE REFERENCIA";
				
		foreach($arrArquivosMovidos as $doc)
		{
			$compl .= "Documento: ".$doc['nome']."<br />";
			$compl .= "Revisão: ".$doc['rev']."<br />";
			$compl .= "Nº. Cliente: ".$doc['numCliente']."<br />";
			$compl .= "Título: ".$doc["titulo"]."<br /><br />";		
		}
		
		$texto = "<b>Foram cadastrados os seguintes documentos de referência</b><br />";
		$texto .= "OS: ".$arrArquivosMovidos[0]['os']."<br />";
		$texto .= $compl;
		
		$sql = "SELECT funcionario, email FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
		$sql .= "WHERE os_x_funcionarios.id_os = '".$dados_form["inc_id_os"]."'  ";
		$sql .= "AND os_x_funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "AND os_x_funcionarios.id_funcionario = usuarios.id_funcionario ";
		$sql .= "ORDER BY funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		foreach($db->array_select as $reg_equipe)
		{							
			if($reg_equipe["email"]!='')
			{
				$params['emails']['to'][] = array('email' => $reg_equipe["email"], 'nome' => $reg_equipe["funcionario"]);
			}
		}
		
		$mail = new email($params,'documento_referencia');
		
		$mail->montaCorpoEmail($texto);
							
		if(!$mail->Send())
		{
			echo $mail->ErrorInfo;
		}
					
		$resposta->addAlert("Concluído com sucesso.");
	}
	else
	{
		$resposta->addAlert("Erro ao transferir os registros.");	
	}
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),false,0);");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),false,1);");	
	
	return $resposta;	
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("lib_rev");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabela_revisoes");
$xajax->registerFunction("preenchetipodoc");
$xajax->registerFunction("preenchetiporef");
$xajax->registerFunction("concluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function valida_campos()
{
	retorno = false;
	
	acao = document.getElementById('acao').value;
		
	if(acao=='incluir')
	{	
		os = document.getElementById('inc_id_os').value;
		tipo_doc = document.getElementById('inc_tipo_doc').value;
		arquivo = document.getElementById('inc_arquivo').value;
		disciplina = document.getElementById('inc_id_disciplina').value;
		id_tipo_doc = document.getElementById('inc_id_tipo_doc').value;
	}
	else
	{
		os = document.getElementById('id_os').value;
		tipo_doc = document.getElementById('tipo_doc').value;
		arquivo = document.getElementById('arquivo').value;
		arquivo_ed = document.getElementById('arquivo_ed').value;
		disciplina = document.getElementById('id_disciplina').value;
		id_tipo_doc = document.getElementById('id_tipo_doc').value;		
	}
	
	if(os!='' && tipo_doc!='' && (arquivo!='' || arquivo_ed))
	{
		if(tipo_doc!=1)
		{
			retorno = true;
		}
		else
		{
			if(disciplina!='' && id_tipo_doc!='')
			{
				retorno = true;	
			}
			else
			{
				retorno = false;
			}
		}
	}
	else
	{
		retorno = false;	
	}
	
	return retorno;
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
			
				document.getElementById('acao').value = 'incluir';
				
				xajax_atualizatabela(xajax.getFormValues('frm'),false,0);
															
			break;
			
			case "a20_":
				
				document.getElementById('acao').value = 'editar';
				
			break;
			
			case "a30_":
			
				document.getElementById('acao').value = 'editar';
	
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a20_", "Visualizar/Alterar", null, null, true);
	myTabbar.addTab("a30_", "Vers&otilde;es");
	myTabbar.addTab("a10_", "Incluir");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a30_").attachObject("a30");
	
	myTabbar.enableAutoReSize(true);

}

//Função javascript para criação da estrutura da grid
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch (tabela)
	{
		case 'inc_div_docs_referencia':

			mygrid.setHeader("OS, Nº&nbsp;Interno, Nº&nbsp;Doc., Disciplina, Tipo&nbsp;Documento, Título, Data, FMT, R, E/C, V, E");
			mygrid.setInitWidths("60,138,138,90,110,200,70,40,40,40,40,40");
			mygrid.setColAlign("left,left,center,left,left,left,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
		break;
		
		case 'div_docs_referencia':
		
			function doOnRowSelected(row,col)
			{
				if (col >= 10)
					return false;
					 
				xajax_editar(row);
			
				return true;
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
			mygrid.setHeader("Nº&nbsp;Interno, Nº&nbsp;Doc., OS, Disciplina, Tipo&nbsp;Documento, Título, Data, FMT, R/V, E/C, A, E");
			mygrid.setInitWidths("138,138,40,90,110,200,70,40,40,40,40,40");
			mygrid.setColAlign("left,left,center,left,left,left,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
		break;

		case 'div_revisoes':
			mygrid.setHeader("Revisão/Versão, data, GRD&nbsp;Cliente&nbsp;, Arquivo, Autor, Editor, E/C, A, E");
			mygrid.setInitWidths("100,80,120,180,185,185,50,50,50");
			mygrid.setColAlign("left,center,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
		break;
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function startUpload_referencias()
{	
	if(document.getElementById('acao').value=='incluir')
	{
	  document.getElementById('inc_inf_upload').innerHTML = '<img width=\"100px\" src=\"../imagens/loader.gif\" />';	
      document.getElementById('inc_inf_upload').style.display = 'block';
	}
	else
	{
	  document.getElementById('inf_upload').innerHTML = '<img width=\"100px\" src=\"../imagens/loader.gif\" />';	
      document.getElementById('inf_upload').style.display = 'block';		
	}
	  
	setTimeout('',1000);
	    
    return true;
}

function stopUpload_referencias(success,erro,id_documento_referencia)
{
      var result = '';
	  
	  if (success == 1)
	  {
		 result = '<span class="labels">Concluído! '+erro+'</span>';
	  }
	  else 
	  {
		 result = '<span class="labels">Erro! '+erro+'</span>';
	  }      

	if(document.getElementById('acao').value=='incluir')
	{		  
	  	document.getElementById('inc_inf_upload').innerHTML = result;
		
		xajax_atualizatabela(xajax.getFormValues('frm'),false,0);
	}
	else
	{
		document.getElementById('inf_upload').innerHTML = result;
		
		document.getElementById('btnalterar').disabled=true;
		
		xajax_atualizatabela(xajax.getFormValues('frm'),false,1);
		
		xajax_atualizatabela_revisoes(id_documento_referencia);	
	}
	  
    return true;   
}

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

</script>

<?php

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".tipos_documentos_referencia ";
$sql .= "WHERE setores.id_setor = tipos_documentos_referencia.id_disciplina ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
$sql .= "GROUP BY setores.id_setor ";
$sql .= "ORDER BY setores.setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção: " . $db->erro);
}

foreach($db->array_select as $cont)
{
	$array_setor_values[] = $cont["id_setor"];
	$array_setor_output[] = $cont["setor"];
}

$sql = "SELECT * FROM ".DATABASE.".tipos_referencia ";
$sql .= "WHERE tipos_referencia.id_tipo_referencia NOT IN ('3') "; //menos comunicação interna
$sql .= "AND tipos_referencia.reg_del = 0 ";
$sql .= "ORDER BY tipo_referencia ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção: " . $db->erro);
}

foreach($db->array_select as $cont)
{
	$array_tipo_values[] = $cont["id_tipo_referencia"];
	$array_tipo_output[] = $cont["tipo_referencia"];
}

$sql = "SELECT * FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";
$sql .= "ORDER BY formato ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção: " . $db->erro);
}

foreach($db->array_select as $cont)
{
	$array_formato_values[] = $cont["id_formato"];
	$array_formato_output[] = $cont["formato"];
}

//$lista_usuarios_irrestritos = array(6,49,909,910,978,871,1046,226,1142,1213);

if(!in_array($_SESSION["id_funcionario"], $lista_usuarios_irrestritos))
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".os_x_funcionarios ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = os_x_funcionarios.id_os ";
	$sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND ordem_servico_status.id_os_status IN (1,2,14,16) ";
	$sql .= "AND os.os > 3000 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
}
else
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os.os > 3000 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";	
}

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção: ". $db->erro); 
}

foreach($db->array_select as $regs)
{
	$os = sprintf("%05d",$regs["os"]);
	
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $os." - ". substr($regs["descricao"],0,50);
}

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("option_formato_values",$array_formato_values);
$smarty->assign("option_formato_output",$array_formato_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);
$smarty->assign("option_os_title",$array_os_title);

$smarty->assign("revisao_documento","V9");

$smarty->assign("campo",$conf->campos('ged_documentos_referencia'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('documentos_referencia.tpl');
?>