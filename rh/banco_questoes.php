<?php
/*
    Formulário de Banco de questões
    
    Criado por Carlos
    
      local/Nome do arquivo:
      ../rh/banco_questoes.php
    
    Versão 0 --> VERSÃO INICIAL : 20/05/2015
    Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
    Versão 2 --> Alteração para contemplar a nova avaliação de conteúdo técnico - 07/11/2017
    Versão 3 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
    Versão 4 --> Layout responsivo - 22/11/2017 - Carlos
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(517))
{
    nao_permitido();
}

function salvar_pergunta($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    //Inserir ou atualizar dependendo do ID do grupo
    if (empty($dados_form['bqp_id']))
    {
        $sql = "SELECT * FROM ".DATABASE.".banco_questoes_perguntas
		 WHERE
		 	reg_del = 0
		 	AND bqp_bqg_id = ".$dados_form['bqp_bqg_id']."
		 	AND bqp_texto = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqp_texto'])))."'
		 	AND bqp_atual = ".$dados_form['bqp_atual']."
		 	AND bqp_bqf_id = ".$dados_form['bqp_bqf_id']."
			AND bqp_setor_aso = ".$dados_form['bqp_setor_aso'];
        
        $db->select($sql, 'MYSQL', true);
        
        if ($db->numero_registros > 0)
        {
            $resposta->addAlert('ATENÇÃO: Neste grupo já existe esta pergunta!');
            
            return $resposta;
        }
        
        $isql = "INSERT INTO ".DATABASE.".banco_questoes_perguntas
					(bqp_texto, bqp_bqg_id, bqp_atual, bqp_setor_aso, bqp_peso, bqp_bqf_id)
				VALUES (
					'".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqp_texto'])))."',
					".$dados_form['bqp_bqg_id'].",
					".$dados_form['bqp_atual'].",
					".$dados_form['bqp_setor_aso'].",
					".$dados_form['bqp_peso'].",
					".$dados_form['bqp_bqf_id']."
				)";
        $db->insert($isql, 'MYSQL');
    }
    else
    {
        $usql = "UPDATE
					".DATABASE.".banco_questoes_perguntas
				SET
					bqp_texto = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqp_texto'])))."',
					bqp_bqg_id = ".$dados_form['bqp_bqg_id'].",
					bqp_atual = ".$dados_form['bqp_atual'].",
					bqp_bqf_id = ".$dados_form['bqp_bqf_id'].",
					bqp_peso = ".$dados_form['bqp_peso'].",
					bqp_setor_aso = ".$dados_form['bqp_setor_aso']."
				WHERE
					reg_del = 0
					AND bqp_id = ".$dados_form['bqp_id']." ";
					
        $db->update($usql, 'MYSQL');
    }
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar salvar a pergunta! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Pergunta salva corretamente! '.$db->erro);
        $resposta->addScript('xajax_atualizatabela();');
    }
    
    return $resposta;
}

function atualizatabela($filtro, $dados_form = '')
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $retorno = array();
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $sql_filtro = "";
    $sql_texto = "";
    
    if($filtro!="")
    {
        $sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
        $sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
        
        $sql_filtro = " AND (bqg_titulo LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR asr_descricao LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR bqp_texto LIKE '".$sql_texto."') ";
    }
    
    $sql = "SELECT * FROM
		".DATABASE.".banco_questoes_perguntas p
        JOIN ".DATABASE.".avaliacao_setor_responsavel s ON s.reg_del = 0 AND bqp_setor_aso = s.asr_id_setor
		JOIN (SELECT * FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0) grupo on bqg_id = bqp_bqg_id
	WHERE
		p.reg_del = 0 ";
    $sql .= $sql_filtro ;
    
    $sql .= "ORDER BY bqg_id DESC, bqp_id ";
    
    $arrayAtual = array('NAO', 'SIM');
    
    $arraysetorAso = array('0' => 'SUPERVISÃO DIRETA', '1' => 'ADMINISTRATIVO', '16' => 'RH');
    
    $db->select($sql, 'MYSQL',true);
    
    foreach($db->array_select as $reg)
    {
        $xml->startElement('row');
        $xml->writeAttribute('id', $reg['bqp_id']);
        $xml->writeElement('cell', sprintf('%04d', $reg['bqp_id']));
        $xml->writeElement('cell', $reg['bqp_texto']);
        $xml->writeElement('cell', $reg['bqg_titulo']);
        $xml->writeElement('cell', $reg['bqp_peso']);
        $xml->writeElement('cell', $arrayAtual[$reg['bqp_atual']]);
        $xml->writeElement('cell', $reg['asr_descricao']);
        $xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm(\"Deseja excluir esta pergunta?\")){xajax_excluir('.$reg['bqp_id'].');}></span>');
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_perguntas',true,'415','".$conteudo."');");
    
    $resposta->addScript("limparForm();");
    
    $resposta->addAssign('btn_inserir', 'value', 'Inserir');
    
    return $resposta;
}

function atualizatabela_criterios($idPergunta)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $resposta->addScript("document.getElementById('bqc_descricao').value = '';");
    
    $resposta->addAssign("bqc_valor", "value", "");
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $sql 		=
    "SELECT * FROM
		".DATABASE.".banco_questoes_criterios
	WHERE
		reg_del = 0
		AND bqc_bqp_id = ".$idPergunta."
	ORDER BY
		bqc_ordem, bqc_valor";
    
    $db->select($sql, 'MYSQL',true);
    
    foreach($db->array_select as $reg)
    {
        $xml->startElement('row');
        $xml->writeAttribute('id', $reg['bqc_id']);
        $xml->writeElement('cell', $reg['bqc_id']);
        $xml->writeElement('cell', $reg['bqc_valor']);
        $xml->writeElement('cell', $reg['bqc_descricao']);
        $xml->writeElement('cell', $reg['bqc_ordem']);
        $xml->writeElement('cell', '<span class="icone icone-excluir" onclick=if(confirm("Deseja excluir este registro?")){xajax_excluir_criterio('.$reg['bqc_id'].','.$reg['bqc_bqp_id'].');} />');
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_itens_criterios',true,'400','".$conteudo."');");
    
    return $resposta;
}

function editar($id)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".banco_questoes_perguntas ";
	$sql .= "WHERE bqp_id = ".$id." ";
	$sql .= "AND reg_del = 0 ";
    
    $db->select($sql, 'MYSQL', true);
    
    foreach($db->array_select as $reg)
    {
        $resposta->addAssign('bqp_id', 'value', $reg['bqp_id']);
        $resposta->addScriptCall("seleciona_combo('".$reg['bqp_bqg_id']."', 'bqp_bqg_id')");
        $resposta->addScriptCall("seleciona_combo('".$reg['bqp_bqf_id']."', 'bqp_bqf_id')");
        $resposta->addAssign('bqp_texto', 'value', $reg['bqp_texto']);
        $resposta->addAssign('bqp_peso', 'value', $reg['bqp_peso']);
        $resposta->addAssign('bqp_setor_aso', 'value', $reg['bqp_setor_aso']);
    }
    
    $resposta->addAssign('btn_inserir', 'value', 'Alterar');
    
    return $resposta;
}

function editar_criterio($id)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".banco_questoes_criterios ";
	$sql .= "WHERE bqc_id = ".$id." ";
	$sql .= "AND reg_del = 0 ";
    
    $db->select($sql, 'MYSQL',true);
    
    $reg = $db->array_select[0];
    
    $resposta->addAssign('bqc_id', 'value', $reg['bqc_id']);
    $resposta->addAssign('bqc_ordem', 'value', $reg['bqc_ordem']);
    
    $resposta->addScriptCall("seleciona_combo('".$reg['bqc_valor']."', 'bqc_valor')");
    
    $resposta->addAssign('bqc_descricao', 'value', $reg['bqc_descricao']);
    
    $resposta->addAssign('btnGravarCriterio', 'value', 'Alterar');
    
    return $resposta;
}

function excluir($id)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $usql = "UPDATE ".DATABASE.".banco_questoes_perguntas SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bqp_id = ".$id." ";
	$usql .= "AND reg_del = 0 ";
    
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar excluir a pergunta! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Pergunta excluida corretamente! '.$db->erro);
        
        $resposta->addScript('xajax_atualizatabela();');
    }
    
    return $resposta;
}

function excluir_criterio($id, $pergunta)
{
    $resposta = new xajaxResponse();
    $db	= new banco_dados();
    
    $usql = "UPDATE ".DATABASE.".banco_questoes_criterios SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bqc_id = ".$id." ";
	$usql .= "AND reg_del = 0 ";
    
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar excluir o registro! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Registro excluido corretamente! '.$db->erro);
        
        $resposta->addScript('xajax_atualizatabela_criterios('.$pergunta.');');
    }
    
    return $resposta;
}

function salvar_criterio_pergunta($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    //Inserir ou atualizar dependendo do ID
    if (empty($dados_form['bqc_id']))
    {
        $isql = "INSERT INTO ".DATABASE.".banco_questoes_criterios
					(bqc_descricao, bqc_bqp_id, bqc_valor, bqc_ordem)
				VALUES (
					'".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqc_descricao'])))."',
					".$dados_form['bqp_id'].",
					".$dados_form['bqc_valor'].",
                    ".$dados_form['bqc_ordem']."
				)";
        $db->insert($isql, 'MYSQL');
    }
    else
    {
        $usql = "UPDATE
					".DATABASE.".banco_questoes_criterios
				SET
					bqc_descricao = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['bqc_descricao'])))."',
					bqc_bqp_id = ".$dados_form['bqp_id'].",
					bqc_valor = ".$dados_form['bqc_valor'].",
                    bqc_ordem = ".$dados_form['bqc_ordem']."
				WHERE
					reg_del = 0 
					AND bqc_id = ".$dados_form['bqc_id'];
        
        $db->update($usql, 'MYSQL');
    }
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar salvar o registro! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Registro corretamente! '.$db->erro);
        
        $resposta->addScript('xajax_atualizatabela_criterios('.$dados_form['bqp_id'].');');
    }
    
    return $resposta;
}

$xajax->registerFunction("salvar_criterio_pergunta");
$xajax->registerFunction("salvar_pergunta");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabela_criterios");
$xajax->registerFunction("editar");
$xajax->registerFunction("editar_criterio");
$xajax->registerFunction("excluir");
$xajax->registerFunction("excluir_criterio");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	if (tabela == 'div_perguntas')
	{
		function doOnRowSelected(row,col)
		{
			if(col<=4)
			{
				xajax_editar(row);
				xajax_atualizatabela_criterios(row);
				return true;
			}
		}
	
		mygrid.setHeader("ID, Texto, Grupo, Peso, Atual, Setor responsável, D");
		mygrid.setInitWidths("50,*,200,50, 100,200,50");
		mygrid.setColAlign("left,left,left,left,left,left,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str,str,str");
	
		mygrid.attachEvent('onRowSelect', doOnRowSelected);
	}
	else
	{
		function editarCriterio(row,col)
		{
			if(col<=2)
			{
				xajax_editar_criterio(row);
				return true;
			}
		}
		
		mygrid.setHeader("ID, Valor, Descrição, Ordem, D");
		mygrid.setInitWidths("50,100,*,80,50");
		mygrid.setColAlign("left,left,left,left,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str");

		mygrid.attachEvent('onRowSelect', editarCriterio);
	}

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function limparForm()
{
	document.getElementById('bqp_id').value = '';
	document.getElementById('bqp_texto').value = '';
	document.getElementById('bqp_peso').value = '';
	seleciona_combo('', 'bqp_bqg_id');
	seleciona_combo('', 'bqp_bqf_id');
}

function limpar_form_criterios()
{
	document.getElementById('bqc_id').value = '';
	document.getElementById('bqc_descricao').value = '';
	seleciona_combo('', 'bqc_valor');
}

</script>

<?php

$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".banco_questoes_grupos ";
$sql .= "WHERE reg_del = 0 ";

$option_grupos_values[] = '';
$option_grupos_output[] = 'Selecione...';

$db->select($sql, 'MYSQL',true);

foreach($db->array_select as $reg)
{
	$option_grupos_values[] = $reg['bqg_id'];
	$option_grupos_output[] = $reg['bqg_titulo'];
}

$smarty->assign('option_grupos_values', $option_grupos_values);
$smarty->assign('option_grupos_output', $option_grupos_output);

$sql = "SELECT * FROM ".DATABASE.".banco_questoes_fatores ";
$sql .= "WHERE reg_del = 0 ";

$option_fator_values[] = '';
$option_fator_output[] = 'Selecione...';

$db->select($sql, 'MYSQL',true);

foreach($db->array_select as $reg)
{
	$option_fator_values[] = $reg['bqf_id'];
	$option_fator_output[] = $reg['bqf_descricao'];
}

$smarty->assign('option_fator_values', $option_fator_values);
$smarty->assign('option_fator_output', $option_fator_output);

$sql = "SELECT * FROM ".DATABASE.".avaliacao_setor_responsavel ";
$sql .= "WHERE reg_del = 0 ";

$option_setor_values[] = '';
$option_setor_output[] = 'Selecione...';

$db->select($sql, 'MYSQL',true);

foreach($db->array_select as $reg)
{
    $option_setor_values[] = $reg['asr_id_setor'];
    $option_setor_output[] = $reg['asr_descricao'];
}

$smarty->assign('option_setor_values', $option_setor_values);
$smarty->assign('option_setor_output', $option_setor_output);

$smarty->assign("campo",$conf->campos('banco_questoes'));

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('banco_questoes.tpl');
?>