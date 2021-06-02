<?php
/*
	  Formulário de Informações de Funcionários	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../rh/funcionarios_dados.php
	  
	  Versão 0 --> VERSÃO INICIAL - 28/01/2008
	  Versão 1 --> Atualização de lay-out : 13/08/2008
	  Versão 2 --> Atualização de classe de banco de dados - 23/01/2015 - Carlos Abreu
	  Versão 3 --> Atualização layout, filtro para coordenadores/supervisores - 03/03/2015 - Carlos Abreu
	  Versão 4 --> Atualização layout - Carlos Abreu - 07/04/2017
	  Versão 5 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(88) && !verifica_sub_modulo(510)  && !verifica_sub_modulo(508))
{
	nao_permitido();
}

$conf = new configs();

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$xml = new xmlWriter();

	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($dados_form["busca"]!="")
	{				
		$array_valor = explode(" ",$dados_form);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = " AND funcionarios.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR funcionarios.funcionario_cidade LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR funcionarios.funcionario_estado LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_cargos ";
	$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','SUSPENSO','CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND funcionarios.nivel_atuacao IN ('S','G','C') ";
	$sql .= "AND rh_cargos.grupo NOT LIKE '%RECURSOS HUMANOS%' ";
	$sql .= "AND funcionarios.id_cargo = rh_cargos.id_cargo_grupo ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $cont_func)
	{
		$array_perm[$cont_func["id_funcionario"]] = $cont_func["id_funcionario"];
	}	
	
	$sql = "SELECT *,funcionarios.id_funcionario AS id_funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = funcionarios.id_setor AND setores.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_funcao = funcionarios.id_funcao AND rh_funcoes.reg_del = 0) ";
	$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','SUSPENSO','CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY funcionarios.funcionario ";	

	$db->select($sql,'MYSQL',true);
	
	$xml->openMemory();
	
	$xml->startElement('rows');
	
	$id = 0;
	
	$array_func = $db->array_select;
	
	foreach($array_func as $cont_desp)
	{
		$xml->startElement('row');				
			$xml->writeAttribute('id', '1_'.$id);
			$xml->writeElement('cell', '<strong>Funcionário: </strong>'.$cont_desp["funcionario"]);
			$xml->writeElement('cell', '<strong>Setor: </strong>'.$cont_desp["setor"]);
			$xml->writeElement('cell', '<strong>Função: </strong>'.$cont_desp["descricao"]);
			$xml->writeElement('cell', '');
		$xml->endElement();
		
		$xml->startElement('row');				
			$xml->writeAttribute('id', '2_'.$id);
			$xml->writeElement('cell', '<strong>Data nascimento: </strong>'.mysql_php($cont_desp["data_nascimento"]));
			$xml->writeElement('cell', '<strong>RG: </strong>'.$cont_desp["identidade_num"]." - ".$cont_desp["identidade_emissor"]);
			$xml->writeElement('cell', '<strong>CPF: </strong>'.$cont_desp["cpf"]);
			$xml->writeElement('cell', '');
		$xml->endElement();
		
		if(!in_array($_SESSION["id_funcionario"],$array_perm))
		{
			$xml->startElement('row');				
				$xml->writeAttribute('id', '3_'.$id);
				$xml->writeElement('cell', '<strong>Endereço: </strong>'.$cont_desp["funcionario_endereco"]);
				$xml->writeElement('cell', '<strong>Bairro: </strong>'.$cont_desp["funcionario_bairro"]);
				$xml->writeElement('cell', '<strong>Cidade: </strong>'.$cont_desp["funcionario_cidade"]);
				$xml->writeElement('cell', '<strong>Estado: </strong>'.$cont_desp["funcionario_estado"]);
			$xml->endElement();
			
			$xml->startElement('row');				
				$xml->writeAttribute('id', '4_'.$id);
				$xml->writeElement('cell', '<strong>Empresa: </strong>'.$cont_desp["empresa_func"]);
				$xml->writeElement('cell', '<strong>CNPJ: </strong>'.$cont_desp["empresa_cnpj"]);
				$xml->writeElement('cell', '<strong>IE: </strong>'.$cont_desp["empresa_ie"]);
				$xml->writeElement('cell', '<strong>IM: </strong>'.$cont_desp["empresa_im"]);
			$xml->endElement();
			
			$xml->startElement('row');				
				$xml->writeAttribute('id', '5_'.$id);
				$xml->writeElement('cell', '<strong>Tipo Exame</strong>');
				$xml->writeElement('cell', '<strong>Realizado</strong>');
				$xml->writeElement('cell', '<strong>Vencimento</strong>');
				$xml->writeElement('cell', '');
			$xml->endElement();
			
			$sql = "SELECT * FROM ".DATABASE.".rh_aso ";
			$sql .= "WHERE rh_aso.id_funcionario = '".$cont_desp["id_funcionario"]."' ";
			$sql .= "AND rh_aso.reg_del = 0 ";
			$sql .= "ORDER BY rh_aso.data_vencimento ";
			
			$db->select($sql,'MYSQL',true);		
			
			foreach($db->array_select as $cont)
			{		
				$tipo_exame = "";
				
				switch($cont["tipo_exame"])
				{
					case '1':
						$tipo_exame = 'ADMISSIONAL';
					break;
					case '2':
						$tipo_exame = 'PERIÓDICO';
					break;
					case '3':
						$tipo_exame = 'PERIÓDICO/AUDIOMÉTRICO';
					break;
					case '4':
						$tipo_exame = 'MUDANÇA DE FUNÇÃO';
					break;
					case '5':
						$tipo_exame = 'DEMISSIONAL';
					break;
					case '6':
						$tipo_exame = 'RETORNO AO TRABALHO';
					break;
				
				}
	
				$xml->startElement('row');				
					$xml->writeAttribute('id', '6_'.$cont["id_rh_aso"]);
					$xml->writeElement('cell', $tipo_exame);
					$xml->writeElement('cell', mysql_php($cont["data_exame"]));
					$xml->writeElement('cell', mysql_php($cont["data_vencimento"]));
					$xml->writeElement('cell', '');
				$xml->endElement();
			
			}
		}
		
		$xml->startElement('row');				
			$xml->writeAttribute('id', '7_'.$id);
			$xml->writeElement('cell', '');
			$xml->writeElement('cell', '');
			$xml->writeElement('cell', '');
			$xml->writeElement('cell', '');
		$xml->endElement();
		
		$id++;	
	}

	$xml->endElement();

	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("grid('funcionarios', true, '450', '".$conteudo."');");
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setHeader("Funcionário, , , ");
	mygrid.setInitWidths("*,*,*,*");
	mygrid.setColAlign("left,left,left,left");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");	
	mygrid.setSkin("dhx_skyblue");
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$smarty->assign("nome_formulario","INFORMAÇÕES FUNCIONÁRIOS");

$smarty->assign("revisao_documento","V5");

$smarty->assign('campo', $conf->campos('funcionarios_dados'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('funcionarios_dados.tpl');

?>