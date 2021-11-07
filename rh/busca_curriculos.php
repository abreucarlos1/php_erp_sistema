<?php
/*
		Formulário de Busca de Currículos	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/busca_curriculos.php
		
		Versão 0 --> VERSÃO INICIAL - 20/03/2007
		Versão 1 --> Atualização de Lay-out | Smarty : 17/07/2008
		Versao 2 --> Alteração do banco de dados - Agencia OCSSO - Curriculo --> bd_site - Carlos Abreu
		Versão 3 --> Atualização de Layout: 08/12/2014
		Versão 4 --> Correção erros - 10/05/2016 - Carlos Abreu
		Versão 5 --> email de Atualização de currículos
		Versão 6 --> Permitir alterar a modalidade/função - 30/01/2017 - Carlos Abreu
		Versão 7 --> atualizacao layout - Carlos Abreu - 04/04/2017
		Versão 8 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 9 --> Layout responsivo - Carlos - 05/02/2018
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(83) && !verifica_sub_modulo(254))
{
	nao_permitido();
}

$conf = new configs();

function atualizatabela($dados_form, $page = 0)
{
	$offset = !isset($dados_form['txtQtdItensPaginacao']) ? 50 : $dados_form['txtQtdItensPaginacao'];
	$limit = $page * $offset;
	
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$filtro = "";
	
	$filtroDados = '';
	
	if($dados_form["nome"]!='')
	{
		$filtro .= " AND DADOS.DAD_NOME LIKE '%" . $dados_form["nome"] . "%' ";
		$filtroDados .= "AND DADOS.DAD_NOME LIKE '%" . $dados_form["nome"] . "%' ";
	}
	
	$filtroCidade = '';
	if ($dados_form["cidade"]!='')
	{
		$filtro .= " AND DADOS.DAD_CID = '" . $dados_form["cidade"] . "' ";
		$filtroCidade .= "WHERE id_cidade = '" . $dados_form["cidade"] . "' ";
	}
	
	$filtroEstados = '';
	if ($dados_form["estado"]!='')
	{
		$filtro .= " AND DADOS.DAD_EST = '" . $dados_form["estado"] . "' ";
		$filtroEstados .= "WHERE id_estado = '" . $dados_form["estado"] . "' ";
	}
	
	if ($dados_form["atualizado"]!='')
	{
		$filtro .= " AND DADOS.ATUALIZADO = '" . $dados_form["atualizado"] . "' ";
		$filtroDados .= !empty($filtroDados) ? " AND DADOS.ATUALIZADO = '" . $dados_form["atualizado"] . "' " : " WHERE DADOS.ATUALIZADO = '" . $dados_form["atualizado"] . "' ";
	}
	
	$filtroObjetivos = '';
	if ($dados_form["modalidade"]!='')
	{
		$filtro .= " AND OBJETIVO.id_area = '" . $dados_form["modalidade"] . "' ";
		$filtroObjetivos .= " AND OBJETIVO.id_area = '" . $dados_form["modalidade"] . "' ";
	}
	
	if ($dados_form["funcao"]!='')
	{
		$filtro .= " AND rh_funcoes.id_funcao = '" . $dados_form["funcao"] . "' ";
		$filtroObjetivos .= !empty($filtroObjetivos) ? " AND OBJETIVO.id_cargo = '" . $dados_form["funcao"] . "' " : " AND OBJETIVO.id_cargo = '" . $dados_form["funcao"] . "' ";
	}
	
	if($dados_form["data"]!='')
	{
		$filtro .= " AND DADOS.data_atualizacao >= '" . php_mysql($dados_form["data"]) . "' ";
		$filtroDados .= !empty($filtroDados) ? " AND DADOS.data_atualizacao >= '" . php_mysql($dados_form["data"]) . "' " : " AND DADOS.data_atualizacao >= '" . php_mysql($dados_form["data"]) . "' ";
	}
	
	if ($dados_form["conhecimentos"]!='')
	{
		if($dados_form["conhecimentos"]=='AUTOCAD')
		{
			$filtro .= " AND FORMACAO.FOR_AUTOCAD IN ('Básico','Intermediário','Avançado') ";
		}

		if($dados_form["conhecimentos"]=='MICROSTATION')
		{
			$filtro .= " AND FORMACAO.FOR_MICRO IN ('Básico','Intermediário','Avançado') ";
		}
		
		if($dados_form["conhecimentos"]=='PDS')
		{
			$filtro .= " AND FORMACAO.FOR_PDS IN ('Básico','Intermediário','Avançado') ";
		}
		
		if($dados_form["conhecimentos"]=='PDMS')
		{
			$filtro .= " AND FORMACAO.FOR_PDMS IN ('Básico','Intermediário','Avançado') ";
		}
		
		if($dados_form["conhecimentos"]=='NR10')
		{
			$filtro .= " AND FORMACAO.FOR_NR10 IN ('Sim','Não') ";
		}
	}
	
	if ($dados_form["trabalho"]!='')
	{
		$filtro .= " AND id_status = '" . $dados_form["trabalho"] . "' ";
	}
	
	$sql = "SELECT * FROM bd_site.status ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção. ERRO: " . $db->erro);	
	}
	
	foreach($db->array_select as $regs)
	{
		$array_status[$regs["id_status"]] = $regs["status"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".cidades, ".DATABASE.".estados, bd_site.DADOS ";
	$sql .= "LEFT JOIN bd_site.CONTA ON (DADOS.UID = CONTA.UID AND CONTA.reg_del = 0 ) ";
	$sql .= "LEFT JOIN bd_site.OBJETIVO ON (DADOS.UID = OBJETIVO.UID AND OBJETIVO.reg_del = 0 ) ";
	$sql .= "LEFT JOIN bd_site.FORMACAO ON (DADOS.UID = FORMACAO.UID AND FORMACAO.reg_del = 0 ) ";
	$sql .= "LEFT JOIN ".DATABASE.".setores ON (OBJETIVO.id_area = setores.id_setor AND setores.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_cargos ON (OBJETIVO.id_cargo = rh_cargos.id_cargo_grupo AND rh_cargos.reg_del = 0) ";
	$sql .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_cargo_grupo = rh_cargos.id_cargo_grupo AND rh_funcoes.reg_del = 0) "; 
	$sql .= "WHERE DADOS.reg_del = 0 ";
	$sql .= "AND cidades.reg_del = 0 ";
	$sql .= "AND estados.reg_del = 0 ";
	$sql .= "AND DADOS.DAD_CID = cidades.id_cidade ";
	$sql .= "AND DADOS.DAD_EST = estados.id_estado ";
	
	if($filtro!='')
	{
		$sql .= $filtro;
	}
	
	$sql .= " ORDER BY DAD_NOME, uf, cidade, setor, grupo ";

	//Executa apenas para pegar o número total de registros
	$db->select($sql, 'MYSQL');
	
	$num_regs = $db->numero_registros;
	
	$sql .= "LIMIT ".$limit.",". $offset." ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção. ERRO: " . $db->erro);	
	}
	 
	$num = $db->numero_registros;
	
	$htmlQtdItens = '<label class="labels">Mostrar </label><input type="text" class="caixa" size="2" value="50" id="txtQtdItensPaginacao" name="txtQtdItensPaginacao" />';
	
	$resposta->addAssign("registros","innerHTML", $htmlQtdItens." Foram encontrados ".$num_regs." registros");
	
	$conteudo = "";
	
	$edicao = true;
	
	$xml = new XMLWriter();
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $cont_desp)
	{
		switch ($cont_desp["id_status"])
		{
			case 0:
			case 1: //sem status
			case 2:
				$imgnr =  'icone icone-cadeado-fechado cursor';
				//$operacao = 2;
				$edicao = true;
				$color = 'background-color:#FFFFFF';
			break;
			
			case 3: //trabalhou na INT - recomendado
				$imgnr = 'icone icone-cadeado-joinha cursor';
				//$operacao = 2;
				$edicao = true;
				$color = 'background-color:#66FFFF';
			break;
			
			case 4: //não recomendado
				$imgnr = 'icone icone-cadeado-aberto cursor';
				//$operacao = 1;
				$edicao = false;
				$color = 'background-color:#FFFF66';
			break;
			
			case 5: //INDICADO
				$imgnr = 'icone icone-cadeado-joinha cursor';
				//$operacao = 2;
				$edicao = true;
				$color = 'background-color:#66FF99';
			break;
				
		}
	
		$xml->startElement('row');
		$xml->writeAttribute('id', $cont_desp['UID']);
		$xml->writeAttribute('style',$color);		
				
		$data_cadastro = explode("-",$cont_desp["data_atualizacao"]);
		$data = calcula_data(date("d/m/Y"), "sub", "month", 6);
		$data_calculada = explode("/",$data);
		
		$data2 = calcula_data(date("d/m/Y"), "sub", "month", 24);
		$data_calculada2 = explode("/",$data2);
		
		if(($data_cadastro[0].$data_cadastro[1].$data_cadastro[2])>=($data_calculada[2].$data_calculada[1].$data_calculada[0]))
		{
			$xml->writeElement('cell', "<font color=\'#00FF00\'><b>".mysql_php($cont_desp["data_atualizacao"])."</b></font>");
		}
		else if(($data_cadastro[0].$data_cadastro[1].$data_cadastro[2])<=($data_calculada2[2].$data_calculada2[1].$data_calculada2[0]))
		{
			$xml->writeElement('cell', "<font color=\'#FF0000\'><b>".mysql_php($cont_desp["data_atualizacao"])."</b></font>");
		}
		else
		{
			$xml->writeElement('cell', mysql_php($cont_desp["data_atualizacao"]));
		}
		
		$html = '<a href="visualizar_curriculo.php?uid='.$cont_desp['UID'].'" target="blank">'.$cont_desp["DAD_NOME"].'</a>';
		
		$xml->writeElement('cell', $html);
	
		$xml->writeElement('cell', $cont_desp["cidade"]);
		$xml->writeElement('cell', $cont_desp["uf"]);
		$xml->writeElement('cell', $cont_desp["setor"]);
		$xml->writeElement('cell', $cont_desp["grupo"]);
		
		if($dados_form["conhecimentos"]=='')
		{
			$conh = '';
			if($cont_desp["FOR_AUTOCAD"]!='')
			{
				$conh .='Autocad, '; 
			}
			if($cont_desp["FOR_MICRO"]!='')
			{
				$conh .='Microstation, '; 
			}
			if($cont_desp["FOR_PDS"]!='')
			{
				$conh .='PDS, '; 
			}
			if($cont_desp["FOR_PDMS"]!='')
			{
				$conh .='PDMS, '; 
			}
			if($cont_desp["FOR_NR10"]!='')
			{
				$conh .='NR 10 '; 
			}
			
			$xml->writeElement('cell', $conh);
		}
		else
		{		
			$ch = '';
			switch($dados_form["conhecimentos"])
			{
				case "AUTOCAD":
					$ch = "Autocad ".$cont_desp["FOR_AUTOCAD"];
				break;
				
				case "MICROSTATION":
					$ch = "Microstation ".$cont_desp["FOR_MICRO"];
				break;

				case "PDS":
					$ch = "PDS ".$cont_desp["FOR_PDS"];
				break;
				
				case "PDMS":
					$ch = "PDMS ".$cont_desp["FOR_PDMS"];
				break;
				
				case "NR10":
					$ch = "NR 10 ".$cont_desp["FOR_NR10"];
				break;
			}
			
			$xml->writeElement('cell', $ch);
		}
		
		$xml->writeElement('cell', $array_status[$cont_desp["id_status"]]);
		
		if(is_file($cont_desp["LinkDoc"]))
		{
			$link = '<span class="icone icone-procurar cursor" onclick=window.open("download_curriculo.php?uid='.$cont_desp['UID'].'");></span>';
		}
		else
		{
			$link = " ";
		}
		
		$xml->writeElement('cell', $link);
		
		if ($edicao)
		{
			$apg = '<span class="icone icone-excluir cursor" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont_desp['UID'].'");}></span>';
		}
		else
		{
			$apg = ' ';
		}
		
		$xml->writeElement('cell', $apg);
		
		$str_checked = "";
		
		if($cont_desp["flag_funcionario"]== 1)
		{
			$str_checked = "checked";
		}
		
		if ($edicao)
		{
			$atu = '<input name="chk'.$cont_desp["UID"].'" type="checkbox" onclick=if(this.checked){xajax_atualizar("1",'.$cont_desp["UID"].');}else{xajax_atualizar("0",'.$cont_desp["UID"].');}; value="1"'.$str_checked.' />';
		}
		else
		{
			$atu = ' ';
		}
		
		$xml->writeElement('cell', $atu);
		
		$nr = '<span class="'.$imgnr.'" onclick=onclick=escolherMarcacao('.$cont_desp['UID'].')></span>';
		
		$xml->writeElement('cell', $nr);
		
		if($edicao)
		{
			if ($cont_desp['envio_email_atualizacao'] == 0)
			{
				$email = '<span class="icone icone-envelope cursor" onclick=if(confirm("Enviar e-mail para Atualização?")){onclick=xajax_enviarEmailAtualizacao('.$cont_desp['UID'].')};></span>';
			}
			else
			{
				$email = '<span class="icone icone-envelope-opaco cursor"></span>';
			}
		}
		else
		{
			$email = ' ';	
		}
		
		$xml->writeElement('cell', $email);
		
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("document.getElementById('btnatualizar').disabled=true");
	
	$resposta->addAssign("id_curriculo", "value", "");	
	
	$resposta->addScript("grid('curriculos', true, '440', '".$conteudo."');");
	
	$resposta->addScript("htmlPaginacao('gridPaginacao', ".$page.", ".$limit.", ".$offset.", ".$num_regs.", 'frm_curriculos', false);");
	
	//$resposta->addScript("hideLoader();");
	
	return $resposta;
}

/**
 * 0 -func não está trabalhando na INT atualmente  / 1 - está trabalho atualmente INT
 */
function atualizar($conteudo,$id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$usql = "UPDATE bd_site.CONTA SET ";
	$usql .= "flag_funcionario = '".$conteudo."' ";
	$usql .= "WHERE UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
    $db->update($usql,'MYSQL');
    
    if ($db->erro != '')
    {
    	$resposta->addAlert("Não foi possível a atualizar o campo "-" " .$sql);
    }
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT LinkDoc FROM bd_site.DADOS ";
	$sql .= "WHERE DADOS.UID = '".$id."' ";
	$sql .= "AND DADOS.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}
	
	$regs = $db->array_select[0];

	$usql = "UPDATE bd_site.DADOS SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE DADOS.UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$usql);
	}
	
	$usql = "UPDATE bd_site.OBJETIVO SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE OBJETIVO.UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$usql);
	}
	
	$usql = "UPDATE bd_site.FORMACAO SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE FORMACAO.UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$usql);
	}
	
	$usql = "UPDATE bd_site.CONTA SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE CONTA.UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$usql);
	}
	
	$usql = "UPDATE bd_site.TELEFONE SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE TELEFONE.UID = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$usql);
	}
	
	if(file_exists($regs["LinkDoc"]))
	{
		unlink($regs["LinkDoc"]);
	}

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));");
	
	$resposta->addAlert("Arquivo excluido com sucesso.");
	
	return $resposta;
}

//marca o funcionario como não recomendavel/recomendavel
//function marcar($id,$tipo)
function marcar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	if($dados_form["rdo_marcacao"]=='')
	{
		$status = 0;
	}
	else
	{
		$status = $dados_form["rdo_marcacao"];	
	}	

	$usql = "UPDATE bd_site.DADOS SET ";
	
	if($dados_form["rdo_marcacao"]==5) //indicado
	{
		$usql .= "indicado = '".maiusculas(tiraacentos($dados_form["indicado"]))."', ";
	}
	
	$usql .= "id_status = '".$status."' ";
	
	$usql .= "WHERE UID = '".$dados_form["uid"]."' ";
	$usql . "AND reg_del = 0 ";
	
    $db->update($usql,'MYSQL');
    
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar realizar esta operação!');
	}
	else
	{
		$resposta->addAlert('Alteração realizada corretamente!');
		$resposta->addScript("divPopupInst.destroi();");
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));");
	}
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM bd_site.DADOS ";
	$sql .= "LEFT JOIN bd_site.OBJETIVO ON (OBJETIVO.UID = DADOS.UID AND OBJETIVO.reg_del= 0) ";
	$sql .= "WHERE DADOS.reg_del = 0 ";
	$sql .= "AND DADOS.UID = '".$id."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}
	
	$regs = $db->array_select[0];
	
	$resposta->addAssign("nome", "value",$regs["DAD_NOME"]);
	
	$resposta->addAssign("id_curriculo", "value", $id);
	
	$resposta->addScript("seleciona_combo('','estado');");
	
	$resposta->addScript("seleciona_combo('','cidade');");
	
	$resposta->addScript("seleciona_combo('','modalidade');");
	
	$resposta->addScript("seleciona_combo('','funcao');");
	
	$resposta->addScript("seleciona_combo(".$regs["DAD_EST"].",'estado');");
	
	$resposta->addScript("seleciona_combo(".$regs["DAD_CID"].",'cidade');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_area"].",'modalidade');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_cargo"].",'funcao');");
	
	$resposta->addScript("seleciona_combo(".$regs["id_status"].",'trabalho');");
	
	$resposta->addScript("document.getElementById('btnatualizar').disabled=false");
	
	return $resposta;	
}

function atualizar_registro($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["funcao"]!='' || $dados_form["modalidade"]!='')
	{
		$usql = "UPDATE bd_site.OBJETIVO SET ";
		$usql .= "OBJETIVO.id_cargo = '" . $dados_form["funcao"] . "', ";
		$usql .= "OBJETIVO.id_area = '" . $dados_form["modalidade"] . "' ";
		$usql .= "WHERE OBJETIVO.UID = '".$dados_form["id_curriculo"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($usql . $db->erro);
		}
			
		$resposta -> addScript("xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));");
	
		$resposta -> addAlert("Currículo atualizado com sucesso.");			

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	
	}

	return $resposta;
}

/**
 * Rotina feita a pedido do Wesley e Katsumi para tentar atualizar o banco de curriculos
 * data: 13/01/2017
 * @param $id
 */
function enviarEmailAtualizacao($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = 
"SELECT DAD_NOME, EMAIL FROM bd_site.DADOS
JOIN(
  SELECT EMAIL, UID AS idDados FROM bd_site.CONTA WHERE CONTA.reg_del = 0
) conta
ON idDados = UID 
WHERE 
	DADOS.UID = '".$id."' 
	AND DADOS.reg_del = 0 ";
	
	$db->select($sql,'MYSQL', true);
	
	$nome = maiusculas(tiraacentos($db->array_select[0]['DAD_NOME']));
	
	$email = minusculas(tiraacentos($db->array_select[0]['EMAIL']));
	
	if (empty($email))
	{
		$resposta->addAlert('Este candidato não tem e-mail cadastrado!');
	}
	else
	{
		$params = array();					
		$params['from']	= "empresa@".DOMINIO;
		$params['from_name'] = "";
		$params['subject'] 	= "Atualização de Currículo";
		
		$params['emails']['to'][] = array('email' => $email, 'nome' => $nome);
		
		$mail = new email($params);
		
		$corpo = "<font face=arial>Prezado candidato,<br />";
		$corpo .= "Seu currículo no site da <b></b> está desatualizado.<br>";
		$corpo .= "Voce poderá atualizá-lo através do link http://www.empresa.com.br/site/gestao/trabalhe_conosco.<br><br>";
		$corpo .= "Atenciosamente,<br>";
		$corpo .= "Recursos Humanos - <br><br>";
		$corpo .= "Rua Olegário Paiva, 36 - Centro - Mogi das Cruzes / SP</font>";

		$mail->montaCorpoEmail($corpo);
		
		if (!$mail->send())
		{
			$resposta->addAlert('Houve uma falha ao tentar enviar o e-mail!');
		}
		else
		{
			$resposta->addAlert('E-mail enviado corretamente!');
			
			$usql = "UPDATE bd_site.DADOS SET envio_email_atualizacao = 1 ";
			$usql .= "WHERE UID = ".$id." ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql, 'MYSQL');
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));");
		}
	}
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizar_registro");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("marcar");
$xajax->registerFunction("enviarEmailAtualizacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>

function escolherMarcacao(id)
{
	var html = '<form name="frm_marcar" id="frm_marcar">';
		html += '<input type="hidden" id="uid" name="uid" value="'+id+'" >';
		html += '<input type="radio" name="rdo_marcacao" id="rdo_marcacao" value="1" /><label class="labels">Trabalhou na  .</label><br />';
		html += '<input type="radio" name="rdo_marcacao" id="rdo_marcacao" value="3" /><label class="labels">Recomendado</label><br />';
		html += '<input type="radio" name="rdo_marcacao" id="rdo_marcacao" value="4" /><label class="labels">Não Recomendado</label><br />';
		html += '<input type="radio" name="rdo_marcacao" id="rdo_marcacao" value="5" /><label class="labels">Indicado</label>  <input type="text" name="indicado" id="indicado" class="caixa" size="50"><br /><br />';
		html += '<input type="button" class="class_botao" id="btnEnviarMarcacao" value="Enviar" onclick=xajax_marcar(xajax.getFormValues("frm_marcar")); /></form>';
		
	modal(html, 'p', 'Escolha o tipo de marcação');
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind>=2 && ind<=7)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);

	mygrid.setHeader("Cad./Atualiz., Nome, cidade, UF, Modalidade, Função, Conhecimentos, Trab.INT, A, D, F, R/NR, EM/At");
	mygrid.setInitWidths("90,*,120,30,*,*,100,80,40,40,40,40,60");
	mygrid.setColAlign("left,left,left,left,left,left,left,center,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	
	mygrid.loadXMLString(xml);
}
</script>

<?php

$array_cidades_values = NULL;
$array_cidades_output = NULL;

$array_modalidade_values = NULL;
$array_modalidade_output = NULL;

$array_funcao_values = NULL;
$array_funcao_output = NULL;

$array_cidades_values[] = "";
$array_cidades_output[] = "TODAS";

$array_estados_values[] = "";
$array_estados_output[] = "TODOS";

$sql = "SELECT * FROM ".DATABASE.".estados, ".DATABASE.".cidades, bd_site.DADOS ";
$sql .= "WHERE DADOS.DAD_CID = cidades.id_cidade ";
$sql .= "AND estados.reg_del = 0 ";
$sql .= "AND cidades.reg_del = 0 ";
$sql .= "AND DADOS.reg_del = 0 ";
$sql .= "AND cidades.id_estado = estados.id_estado ";
$sql .= "GROUP BY id_cidade ";
$sql .= "ORDER BY cidade ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach ($db->array_select as $reg)
{
	$array_cidades_values[] = $reg["id_cidade"];
	$array_cidades_output[] = $reg["cidade"]." - ".$reg["uf"];
}

$sql = "SELECT * FROM ".DATABASE.".estados, bd_site.DADOS ";
$sql .= "WHERE DADOS.DAD_EST = estados.id_estado ";
$sql .= "AND estados.reg_del = 0 ";
$sql .= "AND DADOS.reg_del = 0 ";
$sql .= "GROUP BY id_estado "; 
$sql .= "ORDER BY uf ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $reg)
{
	$array_estados_values[] = $reg["id_estado"];
	$array_estados_output[] = $reg["uf"];
}

$array_modalidade_values[] = "";
$array_modalidade_output[] = "TODAS";

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.");
}

foreach ($db->array_select as $reg)
{
	$array_modalidade_values[] = $reg["id_setor"];
	$array_modalidade_output[] = $reg["setor"];
}

$array_funcao_values[] = "";
$array_funcao_output[] = "TODAS";

$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
$sql .= "WHERE rh_funcoes.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.");
}

foreach ($db->array_select as $reg)
{
	$array_funcao_values[] = $reg["id_funcao"];
	$array_funcao_output[] = $reg["descricao"];
}

$smarty->assign("option_cidades_values",$array_cidades_values);
$smarty->assign("option_cidades_output",$array_cidades_output);

$smarty->assign("option_estados_values",$array_estados_values);
$smarty->assign("option_estados_output",$array_estados_output);

$smarty->assign("option_modalidade_values",$array_modalidade_values);
$smarty->assign("option_modalidade_output",$array_modalidade_output);

$smarty->assign("option_funcao_values",$array_funcao_values);
$smarty->assign("option_funcao_output",$array_funcao_output);

$smarty->assign("revisao_documento","V9");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('busca_curriculo'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('busca_curriculos.tpl');
?>