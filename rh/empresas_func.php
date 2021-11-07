<?php
/*
		Formulário de empresas de Funcionários	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../rh/empresas_func.php
		
		Versão 0 --> VERSÃO INICIAL : 20/03/2007
		Versão 1 --> Atualização classe banco de dados - 23/01/2015 - Carlos Abreu
		Versão 2 --> Atualização de layout
		Versão 3 --> Atualização imagens - 12/07/2016 - Carlos Abreu
		Versão 4 --> Atualização layout - Carlos Abreu - 05/04/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(89) && !verifica_sub_modulo(207))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_empresas').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_empresas')); ");
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $filtro_situacao="")
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM CC3010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND CC3_MSBLQL = 'N' ";
	
	$db->select($sql,'MSSQL', true);
	
	foreach($db->array_select as $regs)
	{
		$array_cnae[trim($regs["CC3_COD"])] = trim($regs["CC3_COD"]);
	}

	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = "AND (empresa_funcionarios.empresa_func LIKE '".$sql_texto."' ";
		$sql_filtro .= "OR funcionarios.funcionario LIKE '".$sql_texto."') ";
	}

	if($filtro_situacao!="")
	{
		$sql_filtro_situacao .= "AND empresa_funcionarios.empresa_situacao = '" . $filtro_situacao . "' ";
	}

	$sql = "SELECT empresa_funcionarios.*, funcionarios.funcionario AS funcionario, rh_funcoes.descricao AS descricao  FROM ".DATABASE.".empresa_funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".Bancos ON (empresa_funcionarios.empresa_banco = Bancos.id_banco AND Bancos.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (empresa_funcionarios.empresa_socio = funcionarios.id_funcionario AND funcionarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON ( funcionarios.id_funcao = rh_funcoes.id_funcao AND rh_funcoes.reg_del = 0) "; 
	$sql .= "WHERE empresa_funcionarios.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= $sql_filtro_situacao;
	$sql .= " ORDER BY empresa_func ";
	
	$db->select($sql,'MYSQL',true);

	$reg = $db->array_select;

	$xml = new xmlWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($reg as $cont_desp)
	{
		if($cont_desp["empresa_situacao"]=='1')
		{
			$situacao = 'ATIVA';
		}
		else
		{
			$situacao = 'INATIVA';
		}

		$xml->startElement('row');
		
		$xml->writeAttribute('id', $cont_desp["id_empfunc"]);
		
		$xml->writeElement('cell', $cont_desp["empresa_func"]);
		$xml->writeElement('cell', $array_cnae[$cont_desp["empresa_cnae"]]);
		$xml->writeElement('cell', $cont_desp["funcionario"]);
		$xml->writeElement('cell', $cont_desp["descricao"]);
		$xml->writeElement('cell', $situacao);
		$xml->writeElement('cell', $cont_desp["instituicao"]);
		$xml->writeElement('cell', $cont_desp["empresa_agencia"]);
		$xml->writeElement('cell', $cont_desp["empresa_cc"]);
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_empfunc = '".$cont_desp["id_empfunc"]."' ";
		$sql .= "OR funcionarios.id_funcionario = '".$cont_desp["empresa_socio"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			$xml->writeElement('cell', ' ');
		}
		else
		{
			$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma a exclusão da empresa selecionada?")){xajax_excluir("'.$cont_desp["id_empfunc"].'");}>');
		}
		
		$conteudo = "<img src=\'".DIR_IMAGENS."web.png\' style=\'cursor:pointer;\' onclick=if(confirm(\'Enviar Relatório ao Financeiro?\')){xajax_enviarRelatorio(\'".$cont_desp["id_empfunc"]."\');} />";
		
		$xml->writeElement('cell', $conteudo);
		
		$xml->endElement();	
	}
	
	$xml->endElement();

	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("grid('empresas', true, '260', '".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' || $dados_form["imposto"]!='' || $dados_form["responsavel"]!='')
	{		
		$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios ";
		$sql .= "WHERE empresa_func = '".maiusculas($dados_form["empresa"])."' ";
		$sql .= "AND empresa_socio = '".$dados_form["responsavel"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			$resposta->addAlert("Empresa já cadastrada");
		}
		else
		{
	
			$isql = "INSERT INTO ".DATABASE.".empresa_funcionarios ";
			$isql .= "(empresa_func, empresa_socio, empresa_endereco, empresa_bairro, empresa_cidade, empresa_estado, empresa_cep, empresa_telefone, empresa_cnpj, empresa_cnae, empresa_ie, empresa_im, empresa_banco, empresa_agencia, empresa_cc, empresa_imposto, empresa_situacao) ";
			$isql .= "VALUES ('" . maiusculas($dados_form["empresa"]) . "', ";
			$isql .= "'" . $dados_form["responsavel"] . "', ";
			$isql .= "'" . maiusculas($dados_form["endereco"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["bairro"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["cidade"]) . "', ";
			$isql .= "'" . $dados_form["estado"] . "', ";
			$isql .= "'" . $dados_form["cep"] . "', ";
			$isql .= "'" . $dados_form["telefone"] . "', ";
			$isql .= "'" . $dados_form["cnpj"] . "', ";
			$isql .= "'" . $dados_form["cnae"] . "', ";
			$isql .= "'" . $dados_form["ince"] . "', ";
			$isql .= "'" . $dados_form["im"] . "', ";
			$isql .= "'" . $dados_form["banco"] . "', ";
			$isql .= "'" . $dados_form["agencia"] . "', ";
			$isql .= "'" . $dados_form["cc"] . "', ";
			$isql .= "'" . $dados_form["imposto"] . "', ";
			$isql .= "'" . $dados_form["situacao"] . "') ";

			$db->insert($isql,'MYSQL');

			$resposta->addScript("xajax_voltar('');");

			$resposta->addScript("xajax_atualizatabela('');");
		
			$resposta->addAlert("Empresa cadastrada com sucesso.");
		}

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT e.*, f.id_funcionario, numero_contrato FROM ".DATABASE.".empresa_funcionarios e ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios f ON id_funcionario = empresa_socio AND situacao = 'ATIVO' ";
	$sql .= "LEFT JOIN ".DATABASE.".pj_contratos p ON p.reg_del = 0 AND p.id_funcionario = f.id_funcionario ";
	$sql .= "WHERE e.id_empfunc = '".$id."' ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];

	$nContrato = substr_replace($regs['numero_contrato'], '', -4, 4);
	$anoContrato = substr($regs['numero_contrato'], -4);
	
	$resposta->addAssign("contratoColaboradorNumero","value",$nContrato);
	$resposta->addAssign("contratoColaboradorAno","value",$anoContrato);
	
	$resposta->addAssign("id_empresa", "value",$regs["id_empfunc"]);	
	$resposta->addAssign("empresa", "value",$regs["empresa_func"]);	
	$resposta->addAssign("endereco", "value",$regs["empresa_endereco"]);
	$resposta->addAssign("bairro", "value",$regs["empresa_bairro"]);
	$resposta->addAssign("cidade", "value",$regs["empresa_cidade"]);
    $resposta->addAssign("cep", "value",$regs["empresa_cep"]);
	$resposta->addAssign("cnpj", "value",$regs["empresa_cnpj"]);
	$resposta->addAssign("ince", "value",$regs["empresa_ie"]);
	$resposta->addAssign("im", "value",$regs["empresa_im"]);
	$resposta->addAssign("agencia", "value",$regs["empresa_agencia"]);
	$resposta->addAssign("cc", "value",$regs["empresa_cc"]);
	$resposta->addAssign("telefone", "value",$regs["empresa_telefone"]);
	
	if (!empty($regs['id_funcionario']))
		$resposta->addScript("seleciona_combo('" . $regs["empresa_socio"] . "', 'responsavel'); ");
	else
		$resposta->addScript("seleciona_combo('', 'responsavel'); ");
	
	$resposta->addScript("seleciona_combo('" . $regs["empresa_cnae"] . "', 'cnae'); ");
	$resposta->addScript("seleciona_combo('" . $regs["empresa_imposto"] . "', 'imposto'); ");
	$resposta->addScript("seleciona_combo('" . $regs["empresa_estado"] . "', 'estado'); ");
	$resposta->addScript("seleciona_combo('" . $regs["empresa_situacao"] . "', 'situacao'); ");
	$resposta->addScript("seleciona_combo('" . $regs["empresa_banco"] . "', 'banco'); ");
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta ->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_empresas'));");
	$resposta ->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' || $dados_form["imposto"]!='')
	{		
		$usql = "UPDATE ".DATABASE.".empresa_funcionarios SET ";
		$usql .= "empresa_func = '" . maiusculas($dados_form["empresa"]) . "', ";
		$usql .= "empresa_socio = '" . $dados_form["responsavel"] . "', ";
		$usql .= "empresa_endereco = '" . maiusculas($dados_form["endereco"]) . "', ";
		$usql .= "empresa_bairro = '" . maiusculas($dados_form["bairro"]) . "', ";
		$usql .= "empresa_cidade = '" . maiusculas($dados_form["cidade"]) . "', ";
		$usql .= "empresa_estado = '" . $dados_form["estado"] . "', ";
		$usql .= "empresa_cep = '" . $dados_form["cep"] . "', ";
		$usql .= "empresa_telefone = '" . $dados_form["telefone"] . "', ";
		$usql .= "empresa_cnpj = '" . $dados_form["cnpj"] . "', ";
		$usql .= "empresa_cnae = '" . $dados_form["cnae"] . "', ";
		$usql .= "empresa_ie = '" . $dados_form["ince"] . "', ";
		$usql .= "empresa_im = '" . $dados_form["im"] . "', ";
		$usql .= "empresa_banco = '" . $dados_form["banco"] . "', ";
		$usql .= "empresa_agencia = '" . $dados_form["agencia"] . "', ";
		$usql .= "empresa_cc = '" . $dados_form["cc"] . "', ";
		$usql .= "empresa_imposto = '" . $dados_form["imposto"] . "', ";
		$usql .= "empresa_situacao = '" . $dados_form["situacao"] . "' ";
		$usql .= "WHERE id_empfunc = '".$dados_form["id_empresa"]."' ";

		$db->update($usql,'MYSQL');

		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela('');");
	
		$resposta->addAlert("Empresa atualizada com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$dsql = "DELETE FROM ".DATABASE.".empresa_funcionarios ";
	$dsql .= "WHERE empresa_funcionarios.id_empfunc = '".$id."' ";
	
	$db->delete($dsql,'MYSQL');

	$resposta->addScript("xajax_atualizatabela('');");
	
	$resposta ->addAlert($what . " excluido com sucesso.");
	
	return $resposta;
}

function enviarRelatorio($idEmpresa)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT CTT_CUSTO, CTT_DESC01 FROM CTT010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = '' "; //CENTRO DE CUSTO
	$sql .= "AND CTT_BLOQ = '2' "; //SOMENTE OS CC NAO BLOQUEADOS
	
	$arrCC = array();
	
	$db->select($sql,'MSSQL', true);
	
	foreach($db->array_select as $reg)
	{
		$arrCC[trim($reg['CTT_CUSTO'])] = trim($reg['CTT_CUSTO']).' - '.trim($reg['CTT_DESC01']);
	}
	
	$sql = "SELECT *, empresa_funcionarios.id_empfunc AS id_empfunc, funcionarios.funcionario AS funcionario, rh_funcoes.descricao AS descricao,
	salario_mensalista, salario_hora,  tipo_contrato, setor, empresa_cnae FROM ".DATABASE.".empresa_funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".bancos ON (empresa_funcionarios.empresa_banco = bancos.id_banco) ";
	$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (empresa_funcionarios.empresa_socio = funcionarios.id_funcionario) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON ( funcionarios.id_funcao = rh_funcoes.id_funcao ) ";
	$sql .= "JOIN ".DATABASE.".setores ON setores.id_setor = funcionarios.id_setor "; 
	
	$sql .= 
		"JOIN (
			SELECT id_salario codSalario, salario_mensalista, salario_hora,  tipo_contrato
			FROM 
				".DATABASE.".salarios
			WHERE
				salarios.reg_del = 0
		) salarios
		ON codSalario = id_salario ";
	
	$sql .= "WHERE empresa_funcionarios.id_empfunc = ".$idEmpresa." ";
	
	$objPHPExcel = PHPExcel_IOFactory::load("./modelos_excel/empresas.xls");
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$tipo_contrato = array(
						"CLT" => "CLT",
						"EST" => "ESTAGIO",
						"SC" => "SOCIEDADE CIVIL (HORISTA)",
						"SC+CLT" => "SOCIEDADE CIVIL + CLT",
						"SC+MENS" => "SOCIEDADE CIVIL (MENSALISTA)",
						"SC+CLT+MENS" => "SOCIEDADE CIVIL + CLT (MENSALISTA)",
						"SOCIO" => "SOCIO");

	$db->select($sql, 'MYSQL', true);
	
	$array_emp = $db->array_select;
	
	foreach($array_emp as $reg)
	{
		//Dados da empresa
		$objPHPExcel->getActiveSheet()->setCellValue('E5', $reg['empresa_func']);
		$objPHPExcel->getActiveSheet()->setCellValue('C7', $reg['empresa_endereco']);
		$objPHPExcel->getActiveSheet()->setCellValue('O7', AntiInjection::formatarGenerico($reg['empresa_cep'], '#####-###'));
		$objPHPExcel->getActiveSheet()->setCellValue('L9', $reg['empresa_bairro']);
		$objPHPExcel->getActiveSheet()->setCellValue('C11', $reg['empresa_cidade']);
		$objPHPExcel->getActiveSheet()->setCellValue('C12', $reg['empresa_estado']);
		$objPHPExcel->getActiveSheet()->setCellValue('L9', $reg['empresa_bairro']);
		$objPHPExcel->getActiveSheet()->setCellValue('O11', $reg['empresa_im']);
		$objPHPExcel->getActiveSheet()->setCellValue('B13', $reg['empresa_cnpj']);
		$objPHPExcel->getActiveSheet()->setCellValue('O13', $reg['empresa_ie']);
		
		//Dados Pessoais
		$objPHPExcel->getActiveSheet()->setCellValue('D19', $reg['funcionario']);
		$objPHPExcel->getActiveSheet()->setCellValue('B21', $reg['email_particular']);
		$objPHPExcel->getActiveSheet()->setCellValue('C23', $reg['telefone'].','.$reg['celular']);
		
		//Dados Bancários em optante simples nacional
		$objPHPExcel->getActiveSheet()->setCellValue('C27', $reg['dv'].' '.$reg['instituicao']);
		$objPHPExcel->getActiveSheet()->setCellValue('C28', $reg['empresa_agencia']);
		$objPHPExcel->getActiveSheet()->setCellValue('E29', $reg['empresa_cc']);
		
		$arrTipoEmpresa = array(1 => 'SIM', 2 => 'NÃO');
		$objPHPExcel->getActiveSheet()->setCellValue('P28', $arrTipoEmpresa[$reg['tipo_empresa']]);
		
		//Informações sobre o trabalho a ser desenvolvido
		$objPHPExcel->getActiveSheet()->setCellValue('E33', mysql_php($reg['data_inicio']));
		$objPHPExcel->getActiveSheet()->setCellValue('E35', $reg['descricao']);
		$objPHPExcel->getActiveSheet()->setCellValue('P35', $arrCC[trim($reg['id_centro_custo'])]);
		$objPHPExcel->getActiveSheet()->setCellValue('O33', $reg['setor']);
		
		$sql = "SELECT * FROM CC3010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND CC3_COD = '".$reg['empresa_cnae']."'";
		
		$db->select($sql,'MSSQL', true);
		
		foreach($db->array_select as $reg1)
		{
			$desc = trim($reg1["CC3_COD"])." - ".trim($reg1["CC3_DESC"]);
			$objPHPExcel->getActiveSheet()->setCellValue('B15', utf8_encode($desc));
		}
				
		if (intval($reg['salario_mensalista']) > 0)
		{
			$salario = $reg['salario_mensalista'];
		}
		else
		{
			$salario = $reg['salario_hora'];
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('C37', str_replace('.', ',',str_replace(',', '', $salario)));
		$objPHPExcel->getActiveSheet()->setCellValue('O37', $tipo_contrato[$reg[' tipo_contrato']]);
	}
		
	$filename = '../templates_c/empresas_func.xlsx';
	
	$objWriter->save($filename);

	$params = array();
	$params['from'] = "ti@".DOMINIO;
	$params['from_name'] = "RECURSOS HUMANOS";
	$params['subject'] = "CADASTRO DE EMPRESA DE FUNCIONARIO";

	$corpo = '<b>Segue em anexo novo cadastro de empresa de funcionário</b>';
	
	if(ENVIA_EMAIL)
	{
	
		//Agora passando o segundo parametro buscaremos os e-mails direto no banco de dados
		$mail = new email($params, 'cadastro_empresa_funcionario');
		$mail->montaCorpoEmail($corpo);
		$mail->AddAttachment($filename, 'cadastro_empresa_funcionario.xlsx');

		if(!$mail->Send())
		{
			$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}
		else
		{
			$resposta->addAlert('E-mail enviado corretamente!');
		}
	}
	else 
	{
		$resposta->addScriptCall('modal', $corpo, '300_650', 'Conteúdo email', 1);
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("enviarRelatorio");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('','1');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Empresa, CNAE, Responsável, Função, Situação, Instituição, Agência, C.C, D,  ");
	mygrid.setInitWidths("*,65,*,*,80,100,80,80,80,50,50");
	mygrid.setColAlign("left,center,left,left,center,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");

	function editar(id, col)
	{
		if (col < 9)
		{
			xajax_editar(id);
		}
	}
	
	mygrid.attachEvent('onRowSelect', editar);
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php
$array_responsavel_values = NULL;
$array_responsavel_output = NULL;

$array_bancos_values = NULL;
$array_bancos_output = NULL;

$array_responsavel_values[] = "0";
$array_responsavel_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_socio)
{
	$array_responsavel_values[] = $reg_socio["id_funcionario"];
	$array_responsavel_output[] = $reg_socio["funcionario"];
}

$array_bancos_values[] = "0";
$array_bancos_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".Bancos ";
$sql .= "ORDER BY instituicao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $contbancos)
{
	$array_bancos_values[] = $contbancos["id_banco"];
	$array_bancos_output[] = $contbancos["dv"] . " - " . $contbancos["instituicao"];
}

//CNAE
$sql = "SELECT * FROM CC3010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND CC3_MSBLQL = 'N' ";

$db->select($sql,'MSSQL', true);

foreach($db->array_select as $regs)
{
	$array_cnae_values[] = trim($regs["CC3_COD"]);
	$array_cnae_output[] = trim($regs["CC3_COD"])." - ".trim($regs["CC3_DESC"]);	
}

$protheus = new ProtheusDao();

$array_uf_values = array();

$dados = $protheus->getTabelaX5($db, true, '12');
foreach($dados as $k => $reg)
{
    $array_uf_values[] = trim($reg['X5_CHAVE']);
}

$smarty->assign("option_responsavel_values",$array_responsavel_values);
$smarty->assign("option_responsavel_output",$array_responsavel_output);

$smarty->assign("option_uf_values",$array_uf_values);

$smarty->assign("option_bancos_values",$array_bancos_values);
$smarty->assign("option_bancos_output",$array_bancos_output);

$smarty->assign("option_cnae_values",$array_cnae_values);
$smarty->assign("option_cnae_output",$array_cnae_output);

$smarty->assign("nome_formulario","EMPRESAS DE FUNCIONÁRIOS");

$smarty->assign("revisao_documento","V5");

$smarty->assign('campo', $conf->campos('empresas_funcionarios'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('empresas_func.tpl');
?>