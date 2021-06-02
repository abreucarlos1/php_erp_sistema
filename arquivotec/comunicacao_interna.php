<?php
/*
		Formulário de Comunicação Interna
		
		Criado por Carlos Abreu   
		
		local/Nome do arquivo:
		../arquivotec/comunicacao_interna.php
		
		Versão 0 --> VERSÃO INICIAL - 29/04/2016 - Carlos Abreu
		Versão 1 --> Atualização - 19/09/2016 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 22/03/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(558))
{
    nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	$resposta->addAssign("data_registro","value",date("d/m/Y"));
	
	$resposta->addScript("seleciona_combo('', 'id_os');");
	
	$resposta->addScript("seleciona_combo('0', 'perm_rev');");
	
	$resposta->addScript("limpa_file('arquivo');");
	
	$resposta->addAssign("versao_documento","value","0");
	
	$resposta->addAssign("numero_documento","value","");
	
	$resposta->addAssign("origem","value","");
	
	$resposta->addAssign("titulo","value","");
	
	$resposta->addAssign("palavras_chave","value","");
	
	$resposta->addAssign("div_revisoes","innerHTML","");
	
	$resposta->addAssign("texto_ci","innerHTML","");
	
	$resposta->addAssign("acao", "value", "incluir");
	
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

function atualizatabela($dados_form, $limparServico = false)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();
	
	$filtro = "";	

	if($dados_form["busca"])
	{
		$filtro .= "AND (documentos_referencia_revisoes.arquivo LIKE '%" . $dados_form["busca"] . "%' ";
		$filtro .= "OR documentos_referencia.numero_registro LIKE '%" . $dados_form["busca"] . "%' ";
		$filtro .= "OR documentos_referencia.numero_documento LIKE '%" . $dados_form["busca"] . "%' ";
		$filtro .= "OR documentos_referencia.titulo LIKE '%" . $dados_form["busca"] . "%' ";
		$filtro .= "OR documentos_referencia.palavras_chave LIKE '%" . $dados_form["busca"] . "%' ";
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

	$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";		
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";
	$sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND tipos_referencia.id_tipo_referencia = 3 "; //COMUNICAÇÃO INTERNA	
	$sql .= $filtro;
	$sql .= "ORDER BY tipos_referencia.tipo_referencia, documentos_referencia.id_documento_referencia DESC, documentos_referencia.numero_registro DESC, setores.setor ";

	$db->select($sql, 'MYSQL', true);
	
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
		$rowHtml = ' ';
		
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
			$xml->writeElement('cell',$reg["versao_documento"]);
			
			if($reg["arquivo"]!="")
			{
				$rowHtml = '<img src="'.DIR_IMAGENS.'procurar.png" onclick=open_doc("'.urlencode($diretorio.$reg["arquivo"]).'") title="Abrir Documento">';
			}
			else
			{
				$rowHtml = ' ';	
			}
			
			$xml->writeElement('cell',$rowHtml);

			$rowHtml = ' ';
			
			//permite o editor apagar o registro
			/*
			if($_SESSION["id_funcionario"]==6 || $_SESSION["id_funcionario"]==909 || $_SESSION["id_funcionario"]==1213 || $_SESSION["id_funcionario"]==978)
			{
				$rowHtml = '<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATENÇÃO: Todos os dados referentes a esse documento de refer&ecirc;ncia serão EXCLU&Iacute;DOS definitivamente. Deseja continuar?")){xajax_excluir("'.$reg["id_documento_referencia"].'");} title="Excluir Documento">';
				
				$xml->writeElement('cell',$rowHtml);
			}
			*/
			
		$xml->endElement();
	}

	$xml->endElement();

	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_docs_referencia', false, '260', '".$conteudo."');");

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
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$id = explode('_', $id);
	
	$id = $id[1];
	
	$db = new banco_dados();

	$sql = "SELECT * FROM ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";	
	$sql .= "WHERE documentos_referencia.id_documento_referencia = '".$id."' ";
	$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
	$sql .= "AND tipos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
	$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
	}

	$reg = $db->array_select[0];
	
	$resposta->addAssign("texto_ci","innerHTML",$reg["texto_ci"]);
	
	$resposta->addAssign("origem","value",$reg["origem"]);
	
	$resposta->addAssign("perm_rev","disabled","");

	$resposta->addAssign("versao_documento","value",$reg["versao_documento"]);
	
	$resposta->addScript("seleciona_combo('" . $reg["id_os"] . "', 'id_os');");
	
	$resposta->addScript("seleciona_combo('" . $reg["servico_id"] . "', 'servico');");

	$resposta->addAssign("numero_registro","value",$reg["numero_registro"]);
	
	$resposta->addAssign("numero_documento","value",$reg["numero_documento"]);
	
	$resposta->addAssign("titulo","value",$reg["titulo"]);
	
	$resposta->addAssign("id_documento_referencia","value",$reg["id_documento_referencia"]);
	
	$resposta->addAssign("palavras_chave","value",$reg["palavras_chave"]);
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addAssign("acao", "value", "atualizar");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	

	return $resposta;
}

function excluir($id_doc_referencia)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
			
	//Seleciona os dados do arquivo a ser excluído
	$sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".documentos_referencia ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_documentos_referencia ON (documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia AND tipos_documentos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".tipos_referencia ON (tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia AND tipos_referencia.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (documentos_referencia.id_disciplina = setores.id_setor AND setores.reg_del = 0) ";	
	$sql .= "WHERE documentos_referencia.id_os = OS.id_os ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND documentos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND documentos_referencia.id_documento_referencia = '".$id_doc_referencia."' ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: ".$db->erro);
	}
	
	$reg_docs = $db->array_select[0];
	
	$os = sprintf("%05d",$reg_docs["os"]);
	
	$id_doc_ref_rev = $reg_docs["id_documento_referencia_revisoes"];
	
	//se tiver arquivo
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
		$resposta->addAlert("Não foi possível excluir o registro: ".$db->erro);
	}
	else
	{		
		if($reg_docs["arquivo"]!='')
		{
			//apaga o arquivo fisicamente
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
			$remove_arquivo = rename($diretorio.$reg_docs["arquivo"],$diretorio."_excluidos/".$reg_docs["arquivo"].".".$id_doc_ref_rev);
			
			if(!$remove_arquivo)
			{
				$resposta->addAlert("Erro ao remover o arquivo");	
			}
		}
		else
		{
				$resposta->addAlert("Documento de referência excluído com sucesso..");				
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("lib_rev");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE id_setor NOT IN ('6','2','17','28','29','3','21','24','19') ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY setor ";

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
$sql .= "WHERE tipos_referencia.reg_del = 0 ";
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
	$sql .= "AND ordem_servico.os > 3000 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY os ";
}
else
{
	$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.os > 3000 ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY os ";	
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

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('comunicacao_interna'));

$smarty->assign("classe",CSS_FILE);

$smarty->caching = true;

$smarty->display('comunicacao_interna.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function startUpload_referencias()
{
	  document.getElementById('inf_upload').innerHTML = '<img width="100px" src="../imagens/loader.gif">';	
      
	  document.getElementById('inf_upload').style.display = 'block';
	  
	  setTimeout('',3000);
	    
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
	  
	  document.getElementById('inf_upload').innerHTML = result;

	  xajax_atualizatabela(xajax.getFormValues('frm'));

	  xajax_voltar();
    
      return true;   
}

//Função javascript para criação da estrutura da grid
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	/*mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	*/
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch (tabela)
	{
		case 'div_docs_referencia':
			function doOnRowSelected(row,col)
			{
				if (col<=7)
				{					 
					xajax_editar(row);
				}				
			
				return true;
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
			mygrid.setHeader("N° Interno, N° Doc, OS, Disciplina, Tipo Documento, Título, data, Rev., A, E");
			mygrid.setInitWidths("138,138,40,90,150,200,70,40,40,40");
			mygrid.setColAlign("left,left,center,left,left,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
		break;

		case 'div_revisoes':
			mygrid.setHeader("Revisão/Versão, data, Autor, Editor, A");
			mygrid.setInitWidths("100,80,120,185,50");
			mygrid.setColAlign("left,center,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		break;
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

</script>