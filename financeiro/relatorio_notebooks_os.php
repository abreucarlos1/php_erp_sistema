<?php
/*
		Formulário de Relatorio Notebooks por os	
		
		Criado por Carlos Eduardo  
		
		local/Nome do arquivo:
		../financeiro/relatorio_notebooks_os.php
	
		Versão 0 --> VERSÃO INICIAL : 20/03/2018
		Versão 1 --> Criação da função atualizatabela - 26/03/2018 - Carlos Eduardo
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto
if(!verifica_sub_modulo(624))
{
	nao_permitido();
}

function atualizatabela($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $clausulaDataIni = !empty($dados_form['data_ini']) ? "AND c.data >= '".php_mysql($dados_form['data_ini'])."'" : '';
    $clausulaDataFim = !empty($dados_form['datafim']) ? "AND c.data <= '".php_mysql($dados_form['datafim'])."'" : '';
    $clausulaDevolucao = !empty($dados_form['datafim']) ? "OR data_devolucao >= '".php_mysql($dados_form['datafim'])."'" : '';
    $clausulaOs = !empty($dados_form['id_os']) ? "AND e.id_os = ".$dados_form['id_os'] : '';
    
    $nomeComplemento = '';
    if (!empty($clausulaDataIni))
        $nomeComplemento .= 'PERÍODO: '.$dados_form['data_ini'];
    if (!empty($clausulaDataFim))
        $nomeComplemento .= !empty($nomeComplemento) ? ' - '.$dados_form['datafim'] : 'PERÍODO: '.$dados_form['datafim'];
            
    $sql = "SELECT funcionario, equipamento, os, MAX(nApontamentos) nApontamentos, num_dvm, data_saida
    FROM (
    	SELECT
    		DISTINCT d.funcionario, b.num_dvm, equipamento, MIN(data_saida) data_saida, MAX(c.data) inicio,
            CONCAT(LPAD(e.os,5,'0'), ' - ', e.descricao) os,
    		COUNT(DISTINCT c.data) nApontamentos, a.data_devolucao
    	FROM
    		ti.inventario a
    		JOIN ti.equipamentos b ON b.reg_del = 0 AND b.id_equipamento = a.id_equipamento  AND area = 'TI'
    		LEFT JOIN ".DATABASE.".apontamento_horas c ON c.reg_del = 0 AND c.id_funcionario = a.id_funcionario AND c.data >= data_saida
    		LEFT JOIN ".DATABASE.".funcionarios d ON d.reg_del = 0 AND d.id_funcionario = a.id_funcionario
    		LEFT JOIN ".DATABASE.".ordem_servico e ON e.reg_del = 0 AND e.id_os = c.id_os
    	WHERE
    		a.reg_del = 0 ".$clausulaDataIni." ".$clausulaDataFim." ".$clausulaOs."
    	GROUP BY
    		funcionario, equipamento, e.os, e.descricao, data_devolucao
    	ORDER BY
    		funcionario, equipamento, COUNT(DISTINCT c.data) DESC
    ) consulta
    WHERE (data_devolucao IS NULL ".$clausulaDevolucao.")
    GROUP BY
    	equipamento, funcionario, data_saida";
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$xml){
        $xml->startElement('row');
        $xml->writeElement('cell', maiusculas($reg['num_dvm']));
        $xml->writeElement('cell', maiusculas($reg['equipamento']));
        $xml->writeElement('cell', mysql_php(substr($reg['data_saida'], 0, 10)));
        $xml->writeElement('cell', maiusculas($reg['funcionario']));
        $xml->writeElement('cell', $reg['os']);
        $xml->endElement();
    });
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('lista', true, '600', '".$conteudo."');");
    $resposta->addScript("hideLoader();");
    $resposta->addScript("document.getElementById('numRegistros').innerHTML = 'Registros encontrados: (".$db->numero_registros.")';");
    
    return $resposta;
}

$xajax->registerFunction('atualizatabela');

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","showLoader();xajax_atualizatabela(xajax.getFormValues('frm'));");

$conf = new configs();

$sql = "SELECT id_os, descricao, os FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status = 14 ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $reg)
{
    $array_os_values[] = $reg["id_os"];
    $array_os_output[] = sprintf('%05d', $reg["os"]).' - '.$reg["descricao"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('notebooks_os'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_notebooks_os.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Código,Equipamento,Data Empréstimo,Funcionário,OS");
	mygrid.setInitWidths("100,320,120,250,*");
	mygrid.setColAlign("left,left,left,left,left");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
    mygrid.enableMultiline(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>