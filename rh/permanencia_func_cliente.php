<?php
/*
		Formulário de PERMANENCIA FUNC CLIENTE	
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../rh/permanencia_func_cliente.php
	
		Versão 0 --> VERSÃO INICIAL : 29/08/2017 - Carlos
		Versão 1 --> 19/10/2017 - Adicionei os campos OS e Horas - Carlos
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 3 --> Layout responsivo - 05/02/2018 - Carlos
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(607))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $div = 'div_grid')
{
    $resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$sql_texto = str_replace(' ', '%',$filtro);
		
		//flt_id_funcionario, neste caso só é usado para a lista do histórico
		$sql_filtro = "AND (funcionario LIKE '%".$sql_texto."%' OR local.descricao LIKE '%".$sql_texto."%' OR OS.descricao LIKE '%".$sql_texto."%' OR flt_id_funcionario = '".$filtro."') ";
	}
	
	//Em caso de histórico, trazer todos os registros
	$complAtual = 'AND flt_atual = 1';
	if ($div == 'lista_historico')
	    $complAtual = '';
	
	$sql = "SELECT 
		flt_id, flt_id_funcionario, flt_id_local, flt_inicio, flt_fim, flt_atual, funcionario, local.descricao, 
		tipo_empresa, flt_numero_contrato, flt_id_os, flt_qtd_horas, OS, OS.descricao descOs
	FROM 
		".DATABASE.".funcionario_x_local_trabalho
		JOIN ".DATABASE.".funcionarios ON id_funcionario = flt_id_funcionario AND funcionarios.reg_del = 0
		JOIN ".DATABASE.".local ON id_local = flt_id_local AND local.reg_del = 0 
		LEFT JOIN ".DATABASE.".ordem_servico ON ordem_servico.id_os = flt_id_os AND ordem_servico.reg_del = 0 
	WHERE
		funcionario_x_local_trabalho.reg_del = 0 ".$sql_filtro." ".$complAtual.' 
		ORDER BY flt_id_funcionario, flt_inicio DESC ';

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $reg)
	{
	    if ($div != 'lista_historico')
	    {
	        //CLT tem 40 dias para alterar o contrato
	        //PJ tem 15 dias para alterar o contrato
	        $dias = $reg['tipo_empresa'] == 0 ? 15 : 40;
	        if ($reg['flt_fim'] >= date('Y-m-d') && $reg['flt_fim'] <= dateAdd(date('Y-m-d'),$dias,'Y-m-d', 'days'))
	            $bolinha = '<span class="icone icone-bola-amarela"></span>';
            else if ($reg['flt_fim'] < dateAdd(date('Y-m-d'),$dias,'Y-m-d', 'days'))
                $bolinha = '<span class="icone icone-bola-vermelha"></span>';
            else
                $bolinha = '<span class="icone icone-bola-verde"></span>';
	    }
	    else
	        $bolinha = '';
	    
		$xml->startElement('row');
		$xml->writeAttribute('id',$reg["flt_id"]);
		
    		$xml->startElement('cell');
    		$xml->text($bolinha);
    		$xml->endElement();
		
			$xml->startElement('cell');
			$xml->text($reg["flt_numero_contrato"]);
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text($reg["descricao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text($reg["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
			$descOs = !empty($reg['OS']) ? sprintf('%05d', $reg["os"]).' - '.$reg['descOs'] : '';
			$xml->text($descOs);
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text(mysql_php($reg["flt_inicio"]));
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text(mysql_php($reg["flt_fim"]));
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text($reg["flt_qtd_horas"]);
			$xml->endElement();
			
			if ($div == 'lista_historico')
			{
			    $xml->startElement('cell');

			    $xml->endElement();
			    
			    $xml->startElement('cell');

			    $xml->endElement();
			}
			else {
			    $xml->startElement('cell');
    			$xml->text('<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja excluir este registro?")){xajax_excluir("'.$reg["flt_id"].'");}></span>');
    			$xml->endElement();
    			
    			$xml->startElement('cell');
    			$xml->text('<span class="icone icone-lupa cursor" onclick="modal_historico('.$reg["flt_id_funcionario"].');"></span>');
    			$xml->endElement();
			}
			
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$largura = 400;
	if ($div == 'lista_historico')
	    $largura = 220;
	
	$resposta->addScript("grid('".$div."',true,'".$largura."','".$conteudo."');");
	
	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	$verificacao = false; 
	
	$db = new banco_dados();
	
	$verificacao = !empty($dados_form['funcionario']) && !empty($dados_form['local_trabalho'] && !empty($dados_form['inicio']) && !empty($dados_form['fim']) && !empty($dados_form['numero_contrato']));
	
	if($verificacao)
	{
	    $usql = "UPDATE ".DATABASE.".funcionario_x_local_trabalho SET ";
		$usql .= "flt_atual = 0, ";
		$usql .= "flt_alteracao = '".date('Y-m-d')."' ";
		$usql .= "WHERE flt_atual = 1 ";
		$usql .= "AND flt_id_funcionario = '".$dados_form['funcionario']."' ";
		$usql .= "AND flt_id_local = '".$dados_form['local_trabalho']."' ";
		$usql .= "AND reg_del = 0 ";
	    
		$db->update($usql, 'MYSQL');

	    $dados_form['qtd_horas'] = str_replace(',', '.', str_replace('.', '', $dados_form['qtd_horas']));
	    
		$isql = "INSERT INTO ".DATABASE.".funcionario_x_local_trabalho ";
		$isql .= "(flt_id_funcionario, flt_id_local, flt_inicio, flt_fim, flt_numero_contrato, flt_id_os, flt_qtd_horas) ";
		$isql .= "VALUES ('".$dados_form['funcionario']."', '".$dados_form['local_trabalho']."', '".php_mysql($dados_form['inicio'])."', '".php_mysql($dados_form['fim'])."', '".$dados_form['numero_contrato']."', '".$dados_form['id_os']."', '".$dados_form['qtd_horas']."') ";
		
		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert('Houve uma falha ao tentar inserir o registro! '.$db->erro);
		}
		else
		{
		    $resposta->addAlert('Registro inserido corretamente!');
		}
			
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta->addScript('xajax_voltar();');
	}
	else
	{
		$resposta->addAlert('Por favor, preencha todos os campos!');
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
	
	$sql = "SELECT * FROM ".DATABASE.".funcionario_x_local_trabalho ";
	$sql .= "WHERE flt_id = ".$id." ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("flt_id", "value",$id);
	
	$resposta->addAssign("funcionario", "value", $regs["flt_id_funcionario"]);
	$resposta->addAssign("local_trabalho", "value", $regs["flt_id_local"]);
	$resposta->addAssign("inicio", "value", mysql_php($regs["flt_inicio"]));
	$resposta->addAssign("fim", "value", mysql_php($regs["flt_fim"]));
	$resposta->addAssign("numero_contrato", "value", $regs["flt_numero_contrato"]);
	$resposta->addAssign("id_os", "value", $regs["flt_id_os"]);
	$resposta->addAssign("qtd_horas", "value", number_format($regs["flt_qtd_horas"], 2, ',', '.'));
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".funcionario_x_local_trabalho SET ".
	        "reg_del = 1, 
			reg_who = '".$_SESSION['id_funcionario']."', 
			data_del = '".date('Y-m-d')."'  
	        WHERE flt_id = ".$id;
	
	$db->update($usql, 'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
    	$resposta->addScript("xajax_atualizatabela('');");
    	
    	$resposta->addAlert('Registro excluido corretamente!');
	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvar");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function modal_historico(id_funcionario)
{
	var html = '<label class="labels" id="lblFuncionario"></label><br /><div id="lista_historico"></div>';

	modal(html, '300_800', 'Histórico de permanência no cliente');
	
	xajax_atualizatabela(id_funcionario, 'lista_historico');
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=4)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader(" ,Nº Contrato, Local, Funcionário, OS, Inicio, Fim, Qtd. Horas,D,H");
	mygrid.setInitWidths("30,100,150,*,*,80,80,60,30,30");
	mygrid.setColAlign("center,left,left,left,left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php
$conf = new configs();

//FUNCIONARIOS
$array_func_values = array('');
$array_func_output= array('SELECIONE');

$sql = "SELECT funcionario, id_funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao = 'ATIVO' ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql, 'MYSQL', function($reg, $i) use(&$array_func_values, &$array_func_output){
    $array_func_values[] = $reg['id_funcionario'];
    $array_func_output[] = $reg['funcionario'];
});

$smarty->assign("option_func_values",$array_func_values);
$smarty->assign("option_func_output",$array_func_output);

//LOCAL DE TRABALHO
$array_locais_values = array('');
$array_locais_output = array('SELECIONE');

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
    $array_locais_values[] = $regs["id_local"];
    $array_locais_output[] = $regs["descricao"];
}

$smarty->assign("option_locais_values",$array_locais_values);
$smarty->assign("option_locais_output",$array_locais_output);

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,14,16) ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $reg)
{
    $array_os_values[] = $reg["id_os"];
    $array_os_output[] = $reg["os"].' - '.$reg['descricao'];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('permanencia_func_cliente'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('permanencia_func_cliente.tpl');

?>