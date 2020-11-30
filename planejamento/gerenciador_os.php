<?php
/*
		Formul�rio de GERENCIADOR OS
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:		
		../planejamento/gerenciador_os.php
		
		Vers�o 0 --> VERS�O INICIAL - 05/01/2018 - Carlos Abreu
		Vers�o 1 --> Inclus�o de data GRD, valor Medido, valor venda - 11/01/2018 - Carlos Abreu
		Vers�o 2 --> Altera��o de fases chamado #2533 - 24/01/2018 - Carlos Abreu
*/	

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(614))
{
	nao_permitido();
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$xml = new XMLWriter();
	
	//seleciona os percentuais medidos
	$sql = "SELECT (SUM(bms_medicao.valor_medido)/bms_pedido.valor_pedido)*100 AS PERCENTUAL, bms_pedido.id_os AS OS FROM ".DATABASE.".bms_pedido, ".DATABASE.".bms_medicao, ".DATABASE.".bms_item "; 
	$sql .= "WHERE bms_pedido.reg_del = 0 "; 
	$sql .= "AND bms_medicao.reg_del = 0 "; 
	$sql .= "AND bms_item.reg_del = 0 "; 
	$sql .= "AND bms_pedido.id_bms_pedido = bms_medicao.id_bms_pedido "; 
	$sql .= "AND bms_medicao.id_bms_item = bms_item.id_bms_item ";
	//$sql .= "AND (bms_pedido.data_pedido >= 2017-07-01 OR bms_pedido.id_os IN (SELECT os FROM ".DATABASE.".bms_excecoes WHERE reg_del = 0)) ";
	$sql .= "GROUP BY bms_pedido.id_os ";
	$sql .= "ORDER BY bms_pedido.id_os ";
	
	$db->select($sql, 'MYSQL', true);
	
	$array_perc = $db->array_select;
	
	foreach($array_perc as $regs)
	{
		$array_percentual[sprintf("%010d",$regs["os"])] = $regs["PERCENTUAL"]; 
	}
	
	//seleciona as GRDs (ultima emiss�o)
	$sql = "SELECT MAX(grd.data_emissao) AS EMISSAO, os.os FROM ".DATABASE.".grd, ".DATABASE.".OS ";
	$sql .= "WHERE grd.reg_del = 0 ";
	$sql .= "AND OS.reg_del = 0 ";
	$sql .= "AND grd.id_os = OS.id_os ";
	$sql .= "GROUP BY OS.id_os ";
	$sql .= "ORDER BY os.os ";
	
	$db->select($sql, 'MYSQL', true);
	
	$array_grd = $db->array_select;

	foreach($array_grd as $regs)
	{
		$array_emissao[sprintf("%010d",$regs["os"])] = $regs["EMISSAO"]; 
	}
	
	//TABELA AF2 - TAREFAS ORCAMENTO - HORAS PREVISTAS
	$sql = "SELECT SUM(AF3_QUANT) AS HORAS_PREV, AF2_ORCAME FROM AF2010 WITH(NOLOCK), AF3010 WITH(NOLOCK) "; 
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' "; 
	$sql .= "AND AF3010.D_E_L_E_T_ = '' "; 
	$sql .= "AND AF3_TAREFA = AF2_TAREFA "; 
	$sql .= "AND AF3_ORCAME = AF2_ORCAME "; 
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF2_GRPCOM <> 'DES' ";
	$sql .= "AND AF2_COMPOS NOT IN ('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17') ";   
	$sql .= "GROUP BY AF2_ORCAME ";
	$sql .= "ORDER BY AF2_ORCAME ";	

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$array_prev = $db->array_select;
	
	foreach($array_prev as $regs)
	{
		$array_horas_prev[trim($regs["AF2_ORCAME"])] = $regs["HORAS_PREV"]; 
	}
	
	//seleciona os planejadores
	$sql = "SELECT AF8_PROJET, AE8_RECURS, AE8_DESCRI FROM AF8010, AF9010, AFA010, AE8010 ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9_PROJET = AF8_PROJET ";
	$sql .= "AND AF9_REVISA = AF8_REVISA ";
	$sql .= "AND AE8_EQUIP = '0000000005' "; //PLANEJAMENTO
	$sql .= "AND AF8_PROJET > '0000004000' ";
	$sql .= "AND AE8_RECURS NOT LIKE 'ORC_%' ";
	
	if(intval($dados_form["fase"])>0)
	{
		//$sql .= "AND AF8_FASE = '".$dados_form["fase"]."' ";
		switch (intval($dados_form["fase"]))
		{
			case 3:
			case 7:
				$array_fase = array('03','07');
			break;
			
			case 5:
			case 11:
				$array_fase = array('05','11');
			break;
			
			default:
				$array_fase = array($dados_form["fase"]);
		}
		
		$sql .= "AND AF8_FASE IN ('".implode("','",$array_fase)."') ";		
	}
	
	$sql .= "AND AF8_FASE NOT IN ('01','06','08','10','17','18','13','4','09') ";
	$sql .= "AND AF9_PROJET = AFA_PROJET ";
	$sql .= "AND AF9_REVISA = AFA_REVISA ";
	$sql .= "AND AF9_TAREFA = AFA_TAREFA ";
	$sql .= "AND AFA_RECURS = AE8_RECURS ";

	$db->select($sql, 'MSSQL', true);
	
	$array_planejadores = $db->array_select;
	
	foreach($array_planejadores as $regs)
	{
		$array_pln[trim($regs["AF8_PROJET"])][trim($regs["AE8_RECURS"])] = trim($regs["AE8_DESCRI"]); 
	}
	
	//Seleciona as OSs
	$sql = "SELECT * FROM AF8010, AF1010, SA1010, AEA010, PA7010 ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND PA7010.D_E_L_E_T_ = '' ";
	$sql .= "AND AEA010.D_E_L_E_T_ = '' ";
	
	if(intval($dados_form["fase"])>0)
	{
		//$sql .= "AND AF8_FASE = '".$dados_form["fase"]."' ";
		$sql .= "AND AF8_FASE IN ('".implode("','",$array_fase)."') ";
	}
	
	$sql .= "AND AF8_FASE NOT IN ('01','06','08','10','17','18','13','4','09') ";
	$sql .= "AND AF8_PROJET > '0000004000' ";
	$sql .= "AND AF8_FASE = AEA_COD ";
	$sql .= "AND AF8_ORCAME = AF1_ORCAME ";
	$sql .= "AND AF1_CLIENT = A1_COD ";
	$sql .= "AND AF1_LOJA = A1_LOJA ";
	$sql .= "AND AF1_COORD1 = PA7_ID ";
	$sql .= "ORDER BY AF8_PROJET ";
	
	$db->select($sql, 'MSSQL', true);
	
	$array_projetos = $db->array_select;
	
	$xml->openMemory();
	  $xml->setIndent(false);
	  $xml->startElement('rows');
	
	foreach($array_projetos as $regs)
	{
		
		$array_nome_plan = NULL;
		
		foreach($array_pln[trim($regs["AF1_ORCAME"])] as $descricao)
		{
			$array_nome_plan[] = $descricao;
		}
		
		$nomes = implode(',',$array_nome_plan);
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',trim($regs["AF1_ORCAME"]));
			
			$xml->startElement('cell');
				$xml->text(trim($regs["AF1_ORCAME"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(trim($regs["AF1_DESCRI"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(trim($regs["A1_NOME"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(trim($regs["AEA_DESCRI"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(trim($regs["PA7_NOME"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($nomes);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php(protheus_mysql(trim($regs["AF1_DTAPRO"]))));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(number_format($array_horas_prev[trim($regs["AF1_ORCAME"])],2,',','.'));
			$xml->endElement();	

			$xml->startElement('cell');
				$xml->text(mysql_php($array_emissao[trim($regs["AF1_ORCAME"])]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(number_format($array_percentual[trim($regs["AF1_ORCAME"])],2,',','.'));
			$xml->endElement();				
			
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_os',true,'500','".$conteudo."');");

	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Projeto,Descri&ccedil;&atilde;o,Cliente,Fase,Coordenador,Planejador,data&nbsp;aprov.,Horas&nbsp;Vend.,Ultima&nbsp;emiss.,%&nbsp;Medido",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left"]);
	mygrid.setInitWidths("80,200,250,180,200,200,100,100,100,100");
	mygrid.setColAlign("left,left,left,left,left,left,left,left,left,left");
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

$db = new banco_dados;

$array_fases_values = NULL;
$array_fases_output = NULL;

$array_fases_values[] = "";
$array_fases_output[] = "SELECIONE A FASE";

$array_fases_values[] = "0";
$array_fases_output[] = "TODAS";

$sql = "SELECT * FROM AEA010 ";
$sql .= "WHERE AEA010.D_E_L_E_T_ = '' ";
$sql .= "AND AEA_COD NOT IN ('01','06','08','10','17','18','13','4','09') "; //hh por adm
$sql .= "ORDER BY AEA_DESCRI ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs)
{
	switch (trim($regs["AEA_COD"]))
	{
		case '03':
		case '07':
			$array_fases_values['03'] = '03';
			$array_fases_output['03'] = 'PROJETO EM EXECU��O';
		break;
		
		case '05':
		case '11':
			$array_fases_values['05'] = '05';
			$array_fases_output['05'] = 'PROJETO PARALIZADO';
		break;
		
		default:
			$array_fases_values[trim($regs["AEA_COD"])] = $regs["AEA_COD"];
			$array_fases_output[trim($regs["AEA_COD"])] = trim($regs["AEA_DESCRI"]);					
	}
}

$smarty->assign("option_fases_values",$array_fases_values);
$smarty->assign("option_fases_output",$array_fases_output);

$campo[1] = "GERENCIADOR OS";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V2");

$smarty->assign("classe",CSS_FILE);

$smarty->display('gerenciador_os.tpl');	

?>




