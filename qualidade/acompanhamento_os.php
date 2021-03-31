<?php
/*
		Formulário de Acompanhamento OS
		
		Criado por Carlos Abreu / Otávio Pamplona  
		
		local/Nome do arquivo:
		../os/acompanhamento_os.php
		
		Versão 0 --> VERSÃO INICIAL : 19/08/2008
		Versão 1 --> Inclusão de restricão de alteração para quem não é coordenador: Carlos Abreu 30/07/2012
		Versão 2 --> Atualização banco de dados - 21/01/2015 - Carlos Abreu		
		Versão 4 --> Inclusão itens / layout - 23/04/2015 - Carlos Abreu
		Versão 5 --> Atualização conforme chamado #846 - 30/11/2016 - Carlos Abreu
		Versão 6 --> Atualização layout - Carlos Abreu - 03/04/2017
		Versão 7 --> refatoração para deixar mais rapido - Carlos Máximo - 03/08/2017
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel/IOFactory.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(127) && !verifica_sub_modulo(213))
{
	nao_permitido();
}

function data($mostra)
{
	$resposta = new xajaxResponse();	
	
	if($mostra)
	{
		$resposta->addAssign("data_ata","value",date("d/m/Y"));
	}
	else
	{
		$resposta->addAssign("data_ata","value","");
	}	
	
	return $resposta;

}

function mostra_arq_cat($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$array_rpl = array("'","\"",")","(","\\","/",".",":","&","%");
	
	$sql = "SELECT * FROM  ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_anexos_cat ";
	$sql .= "WHERE os_x_anexos_cat.id_os = '" . $dados_form["id_os"] . "' ";
	$sql .= "AND os_x_anexos_cat.reg_del = 0 ";
	$sql .= "AND os_x_anexos_cat.id_os = ordem_servico.id_os ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";

	$db->select($sql,'MYSQL', true);

	$regs = $db->array_select[0];
	
	$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($regs["abreviacao_GED"])));		
	
	$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($regs["descricao"])));
	
	$arquivo = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $regs["os"] . "-" .$descricao_os . "/" . $regs["os"] . CAT."/".$regs["anexo"];
	
	$resposta->addAssign('div_cat','innerHTML','<a href="#" style="cursor:pointer;text-decoration:none;color:00f;" onclick=open_file("' . str_replace(" ","%20",$arquivo) . '");>'.$regs["anexo"].'</a>');
		
	return $resposta;	
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$xml = new XMLWriter();
	
	$sql_filtro = "";
	
	$sql_texto = "";	
			
	if($dados_form["status"]!=0)
	{
		$sql_filtro .= "AND ordem_servico_status.id_os_status = '".$dados_form["status"]."' ";
	}
	
	if($dados_form["os_coord"]!="")
	{
		$sql_filtro .= "AND (ordem_servico.id_cod_coord = '".$dados_form["os_coord"]."' ";
		$sql_filtro .= "OR ordem_servico.id_coord_aux = '".$dados_form["os_coord"]."') ";
	}
	
	if($dados_form["chave"]!="")
	{
		$array_valor = explode(" ",$dados_form["chave"]);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro .= " AND (ordem_servico.os LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR empresas.empresa LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR ordem_servico.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR ordem_servico.palavras_chave LIKE '".$sql_texto."') ";		
	}	
		
	$sql = "SELECT ordem_servico.os, ordem_servico.id_os, ordem_servico.descricao FROM ".DATABASE.".unidades, ".DATABASE.".empresas, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";

	//if($_SESSION["id_funcionario"]!=19 && $_SESSION["id_funcionario"]!=6 && $_SESSION["id_funcionario"]!=871 && $_SESSION["id_funcionario"]!=689 && $_SESSION["id_funcionario"]!=888 && $_SESSION["id_funcionario"]!=978 && $_SESSION["id_funcionario"]!=836 && $_SESSION["id_funcionario"]!=226 && $_SESSION["id_funcionario"]!=1142)
	//{	
		$sql .= "AND (ordem_servico.id_coord_aux = '".$_SESSION["id_funcionario"]."' OR ordem_servico.id_cod_coord = '".$_SESSION["id_funcionario"]."') ";
	//}
	
	$sql .= $sql_filtro;

	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os DESC";

	$array_os = array();
	$array_os_unico = array();
	$db->select($sql,'MYSQL', function($reg, $i) use(&$array_os, &$array_os_unico){
		if (trim($reg["os"]) != '')
		{
			$OS = sprintf("%010d", $reg["os"]);
			$array_os[$OS]['id_os'] = $reg['id_os'];
			$array_os[$OS]['descricao'] = $reg['descricao'];
			
			$array_os_unico[$OS] = $OS;
		}
	});
	
	//TABELA AF2 - TAREFAS ORCAMENTO - CUSTO PREVISTO	
	$sql = "SELECT SUM(AF2_CUSTO) AS CUSTO_PREVISTO, AF2_ORCAME FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2_ORCAME IN ('".implode("', '", $array_os_unico)."') ";
	$sql .= "AND AF2_CODIGO <> '' ";
	$sql .= "AND AF2_GRPCOM <> 'DES' "; //RETIRA AS DESPESAS
	$sql .= "AND AF2_COMPOS NOT IN ('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17') "; //RETIRA SUBCONTRATADO
	$sql .= "GROUP BY AF2_ORCAME";

	$db->select($sql,'MSSQL', function($reg, $i) use(&$array_os){
		$array_os[$reg['AF2_ORCAME']]['CUSTO_PREVISTO'] = $reg['CUSTO_PREVISTO'];
	});

	//HORAS REALIZADAS			
	$sql = "SELECT AF8_PROJET, AJK_RECURS, AJK_DATA, AJK_HQUANT FROM AE8010 WITH(NOLOCK), AJK010 WITH(NOLOCK), AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = AF9_PROJET ";
	$sql .= "AND AF8_REVISA = AF9_REVISA ";
	$sql .= "AND AF8_PROJET = AFA_PROJET ";
	$sql .= "AND AF8_REVISA = AFA_REVISA ";
	$sql .= "AND AJK_PROJET = AF8_PROJET ";
	$sql .= "AND AJK_REVISA = AF8_REVISA ";
	$sql .= "AND AJK_TAREFA = AFA_TAREFA ";
	$sql .= "AND AFA_TAREFA = AF9_TAREFA ";
	$sql .= "AND AJK_RECURS = AE8_RECURS ";
	$sql .= "AND AJK_RECURS = AFA_RECURS ";
	$sql .= "AND AF8_PROJET IN ('".implode("', '", $array_os_unico)."') ";
	
	$db->select($sql,'MSSQL', function($reg, $i) use(&$array_os){
		$recurs = explode('_', $reg['AJK_RECURS']);
		$array_os[$reg['AF8_PROJET']]['AJK_RECURS'][$recurs[1]]['AJK_DATA'] = $reg['AJK_DATA'];
		$array_os[$reg['AF8_PROJET']]['AJK_RECURS'][$recurs[1]]['AJK_HQUANT'] += $reg['AJK_HQUANT'];
	});
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($array_os as $OS => $cont_desp)
	{
		$custo_real = 0;	

		foreach($cont_desp['AJK_RECURS'] as $recurs => $regs6)
		{
			//Obtem o valor do salario na data
			$sql = "SELECT * FROM ".DATABASE.".salarios ";
			$sql .= "WHERE salarios.id_funcionario = '" . intval($recurs) . "' ";
			$sql .= "AND salarios.reg_del = 0 ";
			$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".$regs6["AJK_DATA"]."' ";
			$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
					
			$regs4 = $db->array_select[0];
			
			switch ($regs4[" tipo_contrato"])
			{
				case 'SC':
				case 'SC+CLT':
				
					$custo_real += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"],2);
					
				break;
				
				case 'CLT':
				case 'EST':
				
					$custo_real += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
					
				break;
				
				case 'SC+MENS':
				case 'SC+CLT+MENS':
					
					$custo_real += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]),2);
					
				break;
		   }		
			
		}
		
		$saldo = $cont_desp["CUSTO_PREVISTO"]-$custo_real;
		
		if($saldo<0)
		{
			$cor = 'color:#F00';	
		}
		else
		{
			$cor = 'color:#000';	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id','prop_'.$cont_desp["id_os"]);
			$xml->writeElement('cell',$OS);
			$xml->writeElement('cell',addslashes($cont_desp["descricao"]));
			$xml->writeElement('cell',number_format($cont_desp["CUSTO_PREVISTO"],2,',','.'));
			$xml->writeElement('cell',number_format($custo_real,2,',','.'));
			$xml->startElement ('cell');
				$xml->writeAttribute('style',$cor);
				$xml->text(number_format($saldo,2,',','.'));
			$xml->endElement();	
		$xml->endElement();	
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_os',true,'208','".$conteudo."');");
	
	$resposta->addScript("hideLoader();");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$params = array();
	
	$temp = explode('_',$id);
	
	$id_os = $temp[1];
	
	$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs0)
	{
		$array_func[$regs0["id_funcionario"]] = $regs0["funcionario"];
	}

	$sql = "SELECT *, empresas.id_empresa FROM ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas, ".DATABASE.".ordem_servico ";
	$sql .= "LEFT JOIN ".DATABASE.".contatos ON (ordem_servico.id_cod_resp = contatos.id_contato) ";
	$sql .= "WHERE ordem_servico.id_os = '" . $id_os . "' ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";

	$db->select($sql,'MYSQL',true);

	$reg_os = $db->array_select[0];
	
	$resposta->addAssign("id_os","value",$reg_os["id_os"]);
	
	$resposta->addAssign("id","value",$reg_os["id_os"]);
	
	$resposta->addAssign("os","innerHTML",sprintf("%010d",$reg_os["os"]));
	
	$resposta->addAssign("palavra_chave","value",$reg_os["palavras_chave"]);
	
	$resposta->addAssign("descricao","innerHTML",$reg_os["descricao"]);
	
	$resposta->addAssign("cliente","innerHTML",$reg_os["id_empresa"] . " - " .$reg_os["empresa"]);
	
	$resposta->addAssign("coord_cliente","innerHTML",$reg_os["nome_contato"]);
	
	$resposta->addAssign("email_coord_cliente","innerHTML","<a href=\"mailto:".$reg_os["email"]."\">".$reg_os["email"]."</a>");
	
	$resposta->addAssign("coord_dvm","innerHTML",$array_func[$reg_os["id_cod_coord"]]);
	
	$resposta->addAssign("coord_aux","innerHTML",$array_func[$reg_os["id_coord_aux"]]);
	
	$resposta->addAssign("status_os","innerHTML",$reg_os["os_status"]);
	
	$resposta->addScript("xajax_mostra_arq_cat(xajax.getFormValues('frm_acompanhamento'));");
	
	$resposta->addScript("xajax_analise_periodica(xajax.getFormValues('frm_acompanhamento'));");
	
	$resposta->addAssign("data_solicitacao","value",date('d/m/Y'));
	
	//$resposta->addAssign("data_validacao","value",date('d/m/Y'));
	
	$sql = "SELECT * FROM ".DATABASE.".os_x_validacao ";
	$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_validacao = $db->array_select[0];
	
	$resposta->addAssign("nome_validador","value",$reg_validacao["nome_validador"]);
	
	$resposta->addAssign("data_validacao","value",mysql_php($reg_validacao["data_validacao"]));
	
	$resposta->addScript("habilita(true);");
	
	$resposta->addScript("limpa_campos();");
	
	$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_final ";
	$sql .= "WHERE id_os = '" . $reg_os["id_os"] . "' ";

	$db->select($sql,'MYSQL',true);
	
	$regs5 = $db->array_select[0];	
	
	$resposta->addAssign("txt_asp_positivos","value",$regs5["txt_asp_positivos"]);
	$resposta->addAssign("txt_asp_negativos","value",$regs5["txt_asp_negativos"]);
	
	$resposta->addAssign("nome_analise","value",$regs5["nome_analise_final"]);
	$resposta->addAssign("data_analise","value",mysql_php($regs5["data_analise_final"]));

	return $resposta;	
}

function atualizar($dados_form, $tab='')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	switch ($tab)
	{
		case "palavra":
			
			if($dados_form["palavra_chave"]!='' && $dados_form["id_os"]!='')
			{
				$usql = "UPDATE ".DATABASE.".ordem_servico SET ";
				$usql .= "palavras_chave = '" . addslashes($dados_form["palavra_chave"]) . "' ";
				$usql .= "WHERE id_os = '" . $dados_form["id_os"] ."' ";
				
				$db->update($usql,'MYSQL');
				
				$resposta->addAlert('Palavra inserida com sucesso.');
			}			
					
		break;
		
		case "validacao":
		
			//Forma um array com a data fornecida
			$data_array = explode("/",$dados_form["data_validacao"]);
			
			$dia = $data_array[0];
			$mes = $data_array[1];
			$ano = $data_array[2];
			
			//Checa se a data fornecida é inválida
			if(!checkdate($mes,$dia,$ano))
			{
				$resposta->addAlert("A data fornecida é inválida!");
				return $resposta;
			}
			
			//Verifica se o registro já existe
			$sql = "SELECT * FROM ".DATABASE.".os_x_validacao ";
			$sql .= "WHERE id_os = '" . $dados_form["id_os"] . "' ";
			
			$db->select($sql,'MYSQL',true);

			$reg_validacao = $db->array_select[0];
			
			//Se não existir o registro, insere
			if($db->numero_registros==0)
			{
				$isql = "INSERT INTO ".DATABASE.".os_x_validacao ";
				$isql .= "(id_os, nome_validador, data_validacao) ";
				$isql .= "VALUES ('" . $dados_form["id_os"] . "', ";
				$isql .= " '" . maiusculas(addslashes($dados_form["nome_validador"])) . "', ";
				$isql .= " '" . php_mysql($dados_form["data_validacao"]) . "') ";
				
				$db->insert($isql,'MYSQL');
			}
			//Se existir, atualiza
			else
			{
				$usql = "UPDATE ".DATABASE.".os_x_validacao SET ";
				$usql .= "nome_validador = '" . maiusculas(addslashes($dados_form["nome_validador"])) . "', ";
				$usql .= "data_validacao = '" . php_mysql($dados_form["data_validacao"]) . "' ";
				$usql .= "WHERE id_os = '" . $dados_form["id_os"] . "' ";
				
				$db->update($usql,'MYSQL');
			}
			
			$resposta->addAlert('Atualizado com sucesso.');
		
		break;		
		
		case "analise_final":			
			
			$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_final ";
			$sql .= "WHERE id_os = '" . $dados_form["id_os"] . "' ";
		
			$db->select($sql,'MYSQL',true);
			
			$regs = $db->array_select[0];
		
			if($db->numero_registros==0)
			{
				$isql = "INSERT INTO ".DATABASE.".os_x_analise_critica_final ";
				$isql .= "(id_os, txt_asp_positivos, txt_asp_negativos, ";
				$isql .= "nome_analise_final, data_analise_final) ";
				$isql .= "VALUES ('". $dados_form["id_os"] ."', ";
				$isql .= " '" . maiusculas(addslashes($dados_form["txt_asp_positivos"])) . "', ";
				$isql .= " '" . maiusculas(addslashes($dados_form["txt_asp_negativos"])) . "', ";
				$isql .= " '" . maiusculas(addslashes($dados_form["nome_analise"])) . "', ";
				$isql .= " '" . php_mysql($dados_form["data_analise"]) . "') ";
				
				$db->insert($isql,'MYSQL');			
			}
			else
			{
				$usql = "UPDATE ".DATABASE.".os_x_analise_critica_final SET ";
				$usql .= "txt_asp_positivos = '" . maiusculas(addslashes($dados_form["txt_asp_positivos"])) . "', ";
				$usql .= "txt_asp_negativos = '" . maiusculas(addslashes($dados_form["txt_asp_negativos"])) . "', ";
				$usql .= "nome_analise_final = '" . maiusculas(addslashes($dados_form["nome_analise"])) . "', ";
				$usql .= "data_analise_final = '" . php_mysql($dados_form["data_analise"]) . "' ";
				$usql .= "WHERE id_os_x_analise_critica_final = '" . $regs["id_os_x_analise_critica_final"] ."' ";
				
				$db->update($usql,'MYSQL');			
			}
			
			$resposta->addAlert('Atualizado com sucesso.');
			
		break;
		
	}
	
	if($tab == 'analise_final' || $tab == 'validacao')
	{
		$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
		$sql .= "WHERE ordem_servico.id_os = '" . $dados_form["id_os"] . "' ";
	
		$db->select($sql,'MYSQL',true);
	
		$reg_os = $db->array_select[0];
		
		$params['from']	= "empresa@dominio.com.br";
		
		$params['from_name'] = "Sistema ERP";
		
		$params['subject'] 	= 'ANÁLISE CRÍTICA - '.sprintf('%05d',$reg_os["os"]).' - '.$reg_os["descricao"];
		
		$texto = '<strong>LISTA DE PENDÊNCIAS</strong><BR><BR>';
		$texto .= 'Nome validador: '.maiusculas(addslashes($dados_form["nome_validador"])).'<br>';
		$texto .= 'Data validação: '.$dados_form["data_validacao"].'<br><br><br>';
		
		$texto .= '<strong>ANÁLISE CRÍTICA FINAL</strong><BR><BR>';				
		$texto .= '1-Quais foram os aspectos positivos mais relevantes?<br>';				
		$texto .= str_replace("\r\n", "<br>", maiusculas(addslashes($dados_form["txt_asp_positivos"]))).'<br><br><br>';
		$texto .= '2-Quais foram os aspectos negativos mais relevantes?<br>';
		$texto .= str_replace("\r\n", "<br>",maiusculas(addslashes($dados_form["txt_asp_negativos"]))).'<br><br>';
		$texto .= 'Nome: '.maiusculas(addslashes($dados_form["nome_analise"])).'<br>';
		$texto .= 'Data: '.$dados_form["data_analise"].'<br>';
		
		if(ENVIA_EMAIL)
		{

			$mail = new email($params,'acompanhamento_os');
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar o e-mail.');
			}
			
			$mail->ClearAllRecipients();
		}
		else
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
		}		
	}
	
	return $resposta;
}

function analise_periodica($dados_form)
{
	$resposta = new xajaxResponse();
	
	$array_rpl = array("/",".",":","&",")","(","{","}");
	
	$xml = new XMLWriter();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_analise_critica_periodica ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON setores.id_setor = os_x_analise_critica_periodica.id_disciplina ";
	$sql .= "WHERE os_x_analise_critica_periodica.id_os = '" . $dados_form["id_os"] . "' ";
	$sql .= "AND os_x_analise_critica_periodica.id_os = ordem_servico.id_os ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
	$sql .= "ORDER BY item, data_ap ";
	
	$db->select($sql,'MYSQL',true);
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $regs)
	{		

		if($regs["id_contato"]) 
		{ 
			$checado = "checked"; 
		}
		else
		{
			$checado = "";
		}
		
		switch ($regs["status_ap"])
		{
			case '1':
				$status_str = 'PENDENTE';
			break;
			
			case '2':
				$status_str = 'RESOLVIDO';
			break;
			
			case '3':
				$status_str = 'INFORMAÇÃO';
			break;	
		}
		
		$img_anexo = "";
		
		$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($regs["abreviacao_GED"])));		
		
		$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($regs["descricao"])));
		
		$arquivo = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $regs["os"] . "-" .$descricao_os . "/" . $regs["os"] . ACOMPANHAMENTO."/".$regs["anexo"];
		
		if(is_file($arquivo))
		{
			//insere espaços HTML na string
			$img_anexo = '<img src="'.DIR_IMAGENS.'anexo.png" onclick=open_file("' . str_replace(" ","%20",$arquivo) . '"); style="cursor:pointer;">';
		}
		
		switch ($regs["pendencia_interna"])
		{
			case 1:
				$pendencia = "INTERNA";
			break;
			
			default: 
				$pendencia = "EXTERNA";	
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id','ap_'.$regs["id_os_x_analise_critica_periodica"]);
			$xml->writeElement('cell',$regs["item"]);
			$xml->writeElement('cell',mysql_php($regs["data_ap"]));
			$xml->writeElement('cell',addslashes($regs["setor"]));
			$xml->writeElement('cell',$pendencia);
			$xml->writeElement('cell',trim(str_replace("\n","<br>",addslashes($regs["identificacao_problema_ap"]))));
			$xml->writeElement('cell',trim(str_replace("\n","<br>",addslashes($regs["solucao_possivel_ap"]))));
			$xml->writeElement('cell',trim(str_replace("\n","<br>",addslashes($regs["acao_corretiva_ap"]))));
			$xml->writeElement('cell',$status_str);
			$xml->writeElement('cell',$img_anexo);
			$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Confirma a exclusão do registro selecionado?")){xajax_excluir(' . $regs["id_os_x_analise_critica_periodica"] . ',"analise_periodica");}>');
		$xml->endElement();			
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_ap',true,'200','".$conteudo."');");		

	return $resposta;
}

function editar_analise_periodica($id_ap)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$temp = explode('_',$id_ap);
	
	$id_ap = $temp[1];
	
	$resposta->addScript("control_div('tr_id_11',false);");

	$sql = "SELECT * FROM ".DATABASE.".os_x_analise_critica_periodica ";
	$sql .= "WHERE id_os_x_analise_critica_periodica = '" . $id_ap . "' ";
	$sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$reg_ap = $db->array_select[0];
	
	$resposta->addScript("document.getElementById('pend_int').disabled=false");
	
	$resposta->addScript("document.getElementById('data_solicitacao').disabled=false");
	
	$resposta->addAssign("data_ap","value",mysql_php($reg_ap["data_ap"]));
	
	$resposta->addScript("document.getElementById('chk_problemas_projeto_1').checked=true");
	
	$resposta->addScript("control_div('tr_id_11',true);");
	
	if($reg_ap["pendencia_interna"])
	{
		$resposta->addAssign("pend_int","checked",true);
	}
	else
	{
		$resposta->addAssign("pend_int","checked",false);
	}

	
	$resposta->addAssign("data_solicitacao","value",mysql_php($reg_ap["data_solicitacao"]));
	
	$resposta->addAssign("identificacao_problema_ap","value",$reg_ap["identificacao_problema_ap"]);
	
	$resposta->addAssign("solicitado_por","value",$reg_ap["solicitado_por"]);
	
	$resposta->addAssign("solucao_por","value",$reg_ap["solucao_por"]);
	
	$resposta->addAssign("solucao_possivel_ap","value",$reg_ap["solucao_possivel_ap"]);
	
	$resposta->addAssign("acao_corretiva_ap","value",$reg_ap["acao_corretiva_ap"]);
	
	$resposta->addAssign("id_os_x_analise_critica_periodica","value",$reg_ap["id_os_x_analise_critica_periodica"]);
	
	$resposta->addScript("seleciona_combo('" . $reg_ap["status_ap"] . "','status_ap');");
	
	$resposta->addScript("seleciona_combo('" . $reg_ap["id_disciplina"] . "','disciplina_analise_critica');");
	
	$resposta->addScript("document.getElementById('btn_anexar').disabled=false");

	return $resposta;
}

function excluir($id, $tab='')
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$array_rpl = array("/",".",":","&",")","(","{","}");
	
	switch ($tab)
	{
		case "analise_periodica":
		
			//obtem o item da analise		
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_analise_critica_periodica ";
			$sql .= "WHERE os_x_analise_critica_periodica.id_os_x_analise_critica_periodica = '" . $id . "' ";
			$sql .= "AND os_x_analise_critica_periodica.id_os = ordem_servico.id_os ";
			$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
			$sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);

			$reg_item = $db->array_select[0];
			
			$abreviacao_cliente = str_replace($array_rpl, " ",maiusculas(tiraacentos($reg_item["abreviacao_GED"])));		
			
			$descricao_os = str_replace($array_rpl," ",maiusculas(tiraacentos($reg_item["descricao"])));
			
			//monta o diretório
			$diretorio = DOCUMENTOS_GED . $abreviacao_cliente . "/" . $reg_item["os"] . "-" .$descricao_os . "/" . $reg_item["os"] . ACOMPANHAMENTO;
			
			//Verifica se o arquivo já existe, se existir move o antigo para o diretorio _versoes
			if(is_file($diretorio ."/". $reg_item["anexo"]))
			{
				//$erro = "O seguinte arquivo já existe no diretório e não será incluído.";
				//Se ainda não existir a pasta, cria
				if(!is_dir($diretorio.DIRETORIO_EXCLUIDOS))
				{
					mkdir($diretorio.DIRETORIO_EXCLUIDOS);
				}
				
				$move_antigo = rename($diretorio ."/". $reg_item["anexo"], $diretorio . DIRETORIO_EXCLUIDOS ."/". $reg_item["anexo"] . "." . $reg_item["id_os_x_analise_critica_periodica"]);
				
				if(!$move_antigo)
				{
					$resposta->addAlert('Erro ao excluir o arquivo');
				}
					
			}
		
			$usql = "UPDATE ".DATABASE.".os_x_analise_critica_periodica SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_os_x_analise_critica_periodica = '" . $id . "' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');		

			$resposta->addAssign("identificacao_problema_ap","value","");
			$resposta->addAssign("solucao_possivel_ap","value","");
			$resposta->addAssign("acao_corretiva_ap","value","");		
			$resposta->addAssign("id_os_x_analise_critica","value","");	
			$resposta->addScript("xajax.$('status_ap').selectedIndex=0;");

			$resposta->addAlert("Registro excluído com sucesso!");			
		
		break;	
	}

	return $resposta;
}

function imprimir($tipo, $id_os = '')
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("frm_acompanhamento","target","_self");	
	
	if($tipo=='pdf')
	{
		$resposta->addAssign("frm_acompanhamento","action","./relatorios/rel_acompanhamento_os.php");
	}
	else
	{
		if($tipo=='lista_pendencia')
		{
			$resposta->addAssign("lista_pendencia","value","1");
		}
		else
		{
			if($tipo=='lista_pendencia_geral')
			{
				$resposta->addAssign("lista_pendencia","value","2");
			}
		}		
		
		$resposta->addAssign("frm_acompanhamento","action","./relatorios/rel_acompanhamento_excel.php");
	
	}

	$resposta->addScript("frm_acompanhamento.submit();");
	
	$resposta->addAssign("lista_pendencia","value","");
	
	return $resposta;
}

$xajax->registerFunction("data");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("editar_analise_periodica");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("mostra_arq_cat");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("analise_periodica");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	myTabbar.addTab("a10_", "Dados Projeto", null, null, true);
	myTabbar.addTab("a30_", "Lista de Pendência");
	myTabbar.addTab("a50_", "Análise Crítica final");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a30_").attachObject("a30");
	myTabbar.tabs("a50_").attachObject("a50");
	
	myTabbar.enableAutoReSize(false);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);	

	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch (tabela)
	{
		case 'div_os':
		
			function doOnRowSelected(row,col)
			{
				xajax_editar(row);
			
				return true;
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected);	

			mygrid.setHeader("Projeto,Descricao,Custo planejado,Custo real,Saldo",
				null,
				["text-align:left","text-align:left","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("85,500,*,*,*");
			mygrid.setColAlign("left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
			
		break;		
		
		case 'div_ap':
		
			function doOnRowSelected2(row,col)
			{
				if(col<=7)
				{
					xajax_editar_analise_periodica(row);
				
					return true;
				}
			}
			
			mygrid.enableMultiline(true);
			mygrid.attachEvent("onRowSelect",doOnRowSelected2);	

			mygrid.setHeader("Item,Data,Disciplina,Pendência,Ident. problema,Solução possivel, Ação corretiva,Status,Anexo,D",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center"]);
			mygrid.setInitWidths("40,65,120,70,*,*,*,*,70,40");
			mygrid.setColAlign("left,left,left,left,left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
			
		break;
			
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
	
}

function control_div(id,status)
{
	if(status)
	{
		document.getElementById(id).style.display='inline';
		document.getElementById(id).style.visibility='visible';
	}
	else
	{
		document.getElementById(id).style.display='none';
		document.getElementById(id).style.visibility='hidden';	
	}
}

function habilita(status)
{
		
	var status = !status;
	
	document.getElementById('btnatualizar').disabled = status;
	
	document.getElementById('btnimprimir').disabled = status;
	
	document.getElementById('arq_cat').disabled = status;
	
	document.getElementById('btncat').disabled = status;
	
	document.getElementById('inserir_analise_final').disabled = status;
	
	document.getElementsByName('chk_problemas_projeto').item(0).disabled = status;
	
	document.getElementsByName('chk_problemas_projeto').item(1).disabled = status;
				
}

function limpa_campos()
{
	document.getElementById('chk_problemas_projeto_2').checked=true;
	
	document.getElementById('identificacao_problema_ap').value = "";
	
	document.getElementById('solicitado_por').value = "";
	
	document.getElementById('solucao_por').value = "";
	
	document.getElementById('solucao_possivel_ap').value = "";
	
	document.getElementById('acao_corretiva_ap').value = "";
	
	document.getElementById('status_ap').selectedIndex=0;
	
	document.getElementById('data_solicitacao').disabled=true;
	
	document.getElementById('pend_int').disabled=true;
	
	document.getElementById('pend_int').checked=false;
	
	document.getElementById('disciplina_analise_critica').selectedIndex=0;
	
	document.getElementById('id_os_x_analise_critica_periodica').value = "";
	
	document.getElementById('btn_anexar').disabled=true;
	
	control_div('tr_id_11',false);	
}

function finish(erro)
{
	//limpa o campo file
	var oCampo1 = document.getElementById("arq_cat");
	var oNovoCampo1 = oCampo1.cloneNode( true );
	oCampo1.parentNode.replaceChild( oNovoCampo1, oCampo1 );
	
	var oCampo2 = document.getElementById("arq_analise_periodica");
	var oNovoCampo2 = oCampo2.cloneNode( true );
	oCampo2.parentNode.replaceChild( oNovoCampo2, oCampo2 );
	
	document.getElementById('prefixo').value = "";
	
	document.getElementById('id_os_x_analise_critica_periodica').value = "";
	
	if(erro==null || erro=='')
	{
		alert('Inserido com sucesso.');	
	}
	else
	{
		alert(erro);	
	}
	
	limpa_campos();
		
	xajax_mostra_arq_cat(xajax.getFormValues('frm_acompanhamento'));
	
	xajax_analise_periodica(xajax.getFormValues('frm_acompanhamento'));
}

function open_file(documento,path)
{
	window.open("../includes/documento.php?documento="+documento+"&caminho="+path,"_blank");	
}

function anexos(prefixo)
{
	document.getElementById('frm_acompanhamento').action = 'upload.php';
	document.getElementById('frm_acompanhamento').target = 'upload_target';
	document.getElementById('prefixo').value = prefixo;
	
	document.getElementById('frm_acompanhamento').submit();	
}

</script>

<?php

$conf = new configs();

$array_coorddvm_values = NULL;
$array_coorddvm_output = NULL;

$array_status_values = NULL;
$array_status_output = NULL;

$array_disciplina_values = NULL;
$array_disciplina_output = NULL;

$array_status_values[] = "";
$array_status_output[] = "SELECIONE";

$array_status_values[] = "0";
$array_status_output[] = "TODOS";

$array_coorddvm_values[] = "";
$array_coorddvm_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario IN (SELECT id_cod_coord FROM ".DATABASE.".ordem_servico WHERE id_cod_coord <> 0 GROUP BY id_cod_coord) ";
$sql .= "OR funcionarios.id_funcionario IN (SELECT id_coord_aux FROM ".DATABASE.".ordem_servico WHERE  id_coord_aux <> 0 GROUP BY id_coord_aux) ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $regs)
{
	$array_coorddvm_values[] = $regs["id_funcionario"];
	$array_coorddvm_output[] = $regs["funcionario"];
}

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico_status.id_os_status NOT IN (4,8,9,11,12,13) ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = addslashes($regs["os_status"]);
}

$array_disciplina_values[] = "";
$array_disciplina_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "GROUP BY setores.id_setor ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_disciplina_values[] = $regs["id_setor"];
	$array_disciplina_output[] = $regs["setor"];
}

$smarty->assign("option_status_values",$array_status_values);

$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("option_coorddvm_values",$array_coorddvm_values);

$smarty->assign("option_coorddvm_output",$array_coorddvm_output);

$smarty->assign("option_disciplina_values",$array_disciplina_values);

$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("nome_validacao",$_SESSION["nome_usuario"]);

$smarty->assign("data_validacao",date('d/m/Y'));

$smarty->assign("revisao_documento","V7");

$smarty->assign("campo",$conf->campos('acompanhamento_os'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("acompanhamento_os.tpl");

?>