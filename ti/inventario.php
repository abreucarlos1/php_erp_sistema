<?php
/*
	Formulário de Inventário
	
	Criado por Carlos 
	
	local/Nome do arquivo:
	../ti/inventario.php

	Versão 0 --> VERSÃO ATUALIZADA : 09/06/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
	Versão 2 --> Layout responsivo - 05/02/2018 - Carlos 
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(104) && !verifica_sub_modulo(512))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$resposta -> addScriptCall("reset_campos('frm_grupos')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupos'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function realizarBaixa($idInventario, $obsDevolucao = '')
{
	$retorno = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = 
	"SELECT
		*
	FROM
		(SELECT id_funcionario codigoFuncionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0) funcionario
		LEFT JOIN ".DATABASE.".usuarios ON (usuarios.id_usuario = funcionarios.id_usuario AND usuarios.reg_del = 0)
		JOIN(
          SELECT * FROM ".DATABASE.".inventario
          JOIN(
              SELECT id_equipamento codigo_equipamento, equipamento, num_dvm patrimonio FROM ".DATABASE.".equipamentos WHERE equipamentos.reg_del = 0
          ) equipamentos
          ON codigo_equipamento = id_equipamento
          WHERE inventario.reg_del = 0 AND inventario.id_inventario = ".$idInventario."
        ) inventario
        ON id_funcionario = codigoFuncionario AND inventario.reg_del = 0 ";

	$db->select($sql, 'MYSQL',true);
	
	$reg_usuario = $db->array_select[0];
	
	if ($db->numero_registros > 0)
	{
		$usql = 
		"UPDATE ".DATABASE.".inventario
			SET situacao = 0, 
			data_devolucao = '".date('Y-m-d H:i:s')."', 
			situacao_devolucao = '".strtoupper($obsDevolucao)."'			
		WHERE id_inventario = '".$idInventario."'
		AND reg_del = 0 ";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro == '')
		{
		    if (HOST != 'localhost')
		    {
    			/*
    			 * Enviando o email
    			 */
    			$params 			= array();
    			$params['from']		= "ti@".DOMINIO;
    			$params['from_name']= "Tecnologia e Sistemas";
    			$params['subject'] 	= "Baixa do equipamento ".$reg_usuario["patrimonio"]." - ".$reg_usuario["equipamento"];
    	
    			$params['emails']['to'][] = array('email' => "ti@".DOMINIO, 'nome' => "Sistemas");
    			
    			if ($reg_usuario['situacao'] == 'ATIVO')
    				$params['emails']['to'][] = array('email' => $reg_usuario["email"], 'nome' => $reg_usuario["email"]);
    			
    			$corpo = 
    			"<html>
    				<body style='font: 11pt Arial'>
    					<p>Foi realizada baixa do equipamento <b>".$reg_usuario["patrimonio"]."</b> - <b>".$reg_usuario["equipamento"]."</b></p>
    					<p><b>Colaborador responsável</b>: ".$reg_usuario["funcionario"]."</p>
    					<p><b>data da baixa</b>: " . date("d/m/Y") . "</p>
    				</body>
				 </html>";
				
				if(ENVIA_EMAIL)
				{    			
					$mail = new email($params);
					$mail->montaCorpoEmail($corpo);
					$enviado = $mail->Send();
				}
				else 
				{
					$resposta->addScriptCall('modal', $corpo, '300_650', 'Conteúdo email', 1);
				}


		    }
			
		    $enviado = isset($enviado) ? $enviado : HOST == 'localhost';
		    
			//Por enquanto, não teremos controle dos e-mails que serão ou não enviados
			if (!$enviado)
			{
				$retorno->addAlert('E-MAIL NÃO enviado, porém, a baixa foi realizada');
			}
			else 
			{
				$retorno->addAlert('Realizada a baixa do equipamento');
			}
			
			$retorno->addScript('divPopupInst.destroi();');
			$retorno->addScript('xajax_atualizatabela();');
		}
		else
		{
			$retorno->addAlert('Houve uma falha ao tentar realizar a baixa do equipamento');
			$retorno->addScript('xajax_atualizatabela();');
		}
	}
	else
	{
		$retorno->addAlert('Houve uma falha ao tentar realizar a baixa do equipamento');
		$retorno->addScript('xajax_atualizatabela();');
	}
	
	return $retorno;
}

function atualizatabela()
{
	$resposta = new xajaxResponse();
	$conf = new configs();
	$msg = $conf->msg($resposta);
	$db = new banco_dados();
	
	$situacao = isset($_GET['atuais']) && $_GET['atuais'] == 0 ? '' : "AND situacao = 1";
	
	$sql = 
		"SELECT id_equipamento, equipamento, num_dvm, id_funcionario, data_saida, funcionario, id_inventario,
				CASE WHEN tipo = 1 THEN 'ALUGADO' ELSE 'EMPRESA' END tipo, os, area, status
		FROM
		".DATABASE.".equipamentos
		  JOIN(
		    SELECT
		      id_equipamento codigo_equipamento, id_funcionario, data_saida, funcionario, id_inventario, situacao, ordem_servico.os os, status
		    FROM
			".DATABASE.".inventario
		      JOIN(
		        SELECT
		          id_funcionario, funcionario, situacao AS status
		        FROM
		          ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0 
		      ) funcionario
		      ON id_funcionario = id_funcionario
              LEFT JOIN ".DATABASE.".ordem_servico on ordem_servico.id_os = inventario.os
		    WHERE
		      inventario.reg_del = 0
		      ".$situacao."
		  )
		  inventario
		  ON inventario.codigo_equipamento = id_equipamento
		WHERE
		  equipamentos.reg_del = 0
		ORDER BY
		  funcionario, num_dvm";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL', function($reg, $i) use(&$xml){
		$xml->startElement('row');
			
		    if ($reg['status'] != 'ATIVO')
		    {
		        $xml->writeAttribute('style', 'background-color:#FF9999;');
		    }
		
		    $xml->startElement('cell');
				$xml->text($reg["num_dvm"]);
			$xml->endElement();
		    
			$xml->startElement('cell');
				$xml->text($reg["equipamento"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg["tipo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php(substr($reg["data_saida"],0,10)));
			$xml->endElement();
			
			$xml->startElement('cell');
				$os = !empty($reg["os"]) ? sprintf('%06d', $reg["os"]) : '';
				$xml->text($os);
			$xml->endElement();
			
			$imgBaixa = "<span class=\'icone icone-aprovar cursor\' onclick=\'modal_baixa_equipamento(".$reg['id_inventario'].");\'></span>";
			$imgPrint = '<span class="icone icone-impressora cursor" onclick=window.open("./relatorios/termo_responsabilidade_equipamento.php?idInventario='.$reg['id_inventario'].'","_blank");></span>';
			$imgPasta = '<span class="icone icone-pasta cursor" onclick="xajax_showModalHistorico('.$reg['id_inventario'].');"></span>';
			
			$xml->startElement('cell');
				$xml->text($imgBaixa);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($imgPrint);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($imgPasta);
			$xml->endElement();
		$xml->endElement();
	});

	$xml->endElement();

	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('listagem',true,'450','".$conteudo."');");

	return $resposta;
}

function gravarlocal($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$idInventario 	= $dados_form['local_id_inventario'];
	$idlocal 		= $dados_form['novolocalTrabalho'];
	
	$usql = "UPDATE ".DATABASE.".inventario_locais SET ";
	$usql .= "il_atual = 0 ";
	$usql .= "WHERE il_id_inventario = ".$idInventario." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	$isql = 
	"INSERT INTO ".DATABASE.".inventario_locais (il_id_local, il_id_inventario, il_data)
		VALUES (".$idlocal.",".$idInventario.", '".date('Y-m-d')."')";
	
	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != ''){
		$resposta->addAlert('Houve uma falha ao tentar salvar o local');
	}
	else
	{
		$resposta->addAlert('Histórico gravado corretamente');
		$resposta->addScriptCall('xajax_historico('.$idInventario.');');
	}
	
	return $resposta;
}

function historico($_id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$sql = 
	"SELECT
	  id_local, il_id_inventario, id_local, il_data, descricao, il_atual
	FROM
	".DATABASE.".inventario_locais
		JOIN(
			SELECT id_local, descricao FROM ".DATABASE.".local WHERE local.reg_del = 0 
		) local
		ON id_local = il_id_local
	WHERE
		reg_del = 0
		AND il_id_inventario = ".$_id." 
	ORDER BY
		il_atual DESC, il_data DESC";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$arrAux = array(1 => '*', 0 => '');
	$db->select($sql, 'MYSQL',function($reg, $i) use(&$xml, $arrAux){
		$xml->startElement('row');
		    $xml->writeAttribute('id',$reg["id_equipamento"]);
			
			$xml->startElement('cell');
				$xml->text(mysql_php($reg['il_data']));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg['descricao']);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($arrAux[$reg['il_atual']]);
			$xml->endElement();
		$xml->endElement();
	});
	
	$xml->endElement();

	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("grid('lista_historico',true,'200','".$conteudo."');");
		
	return $resposta;
}

function showModalhistorico($_id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$html = '<form id="frmHistorico">
				<input type="hidden" id="local_id_inventario" name="local_id_inventario" value="'.$_id.'" /> 
				<label class="labels">Novo local</label>
				<select id="novolocalTrabalho" name="novolocalTrabalho" style="width:270px;" class="form-control input-sm caixa">
				<option value="">SELECIONE...</option>';
	
	$sql = "SELECT * FROM ".DATABASE.".local ";
	$sql .= "WHERE local.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$html){
		$html .= "<option value='".$reg['id_local']."'>".$reg['descricao']."</option>";
	});
	
	$html .= '</select><input type="button" onclick="xajax_gravarlocal(xajax.getFormValues(\'frmHistorico\'));" class="class_botao" id="btnGravarNovolocal" name="btnGravarNovolocal" value="GRAVAR" /></form>';
	$html .= "</form><div id='lista_historico'></div>";
	
	$resposta->addScriptCall('modal', $html, '300_550', 'Histórico do equipamento');
	$resposta->addScriptCall('xajax_historico('.$_id.');');
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	switch (trim($dados_form['pedidoVia']))
	{
		case 'lblChamado':
			$tipo = 'Chamado';
			$complemento = $dados_form['chamado'];
		break;
		
		case 'lblEmail':
			$tipo = 'E-mail';
			$complemento = $dados_form['email'];
		break;
		
		case 'lblVerbal':
			$tipo = 'Verbal';
			$complemento = $dados_form['descricaoVerbal'];//Usei o mesmo textarea para outros e verbal, visto que são opcionais
		break;
		
		case 'lblOutros':
			$tipo = 'Outros';
			$complemento = $dados_form['descricaoOutros'];
		break;
		
		default:
			$complemento = "";
		break;
	}

	$isql = 
	"INSERT INTO ".DATABASE.".inventario
		(id_equipamento, situacao, id_funcionario, data_saida, complemento, tipo, os)
	VALUES
		(".$dados_form['equipamento'].", 1, ".$dados_form['solicitante'].", '".php_mysql($dados_form['data_retirada']).date(' H:i:s')."', '".$complemento."', '".$tipo."', '".$dados_form['os']."') ";
	
	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar gravar o registro');
	}
	else
	{
		$codigoInventario = $db->insert_id;
		
		if($codigoInventario > 0)
		{
			$values = '';
			$retorno = $codigoInventario;
			
			if (count($dados_form['acessorios']) > 0)
			{
				$sep = '';
				foreach($dados_form['acessorios'] as $ac)
				{
					$values .= " ".$sep." (".$codigoInventario.", ".$ac.")";
					$sep = ',';
				}
				
				$isql = 
				"INSERT INTO ".DATABASE.".inventario_acessorios
					(id_inventario, id_acessorio)
				VALUES
				".$values;
				
				$db->insert($isql, 'MYSQL');
			
				if ($db->erro != '')
				{
					$retorno = 0;
					
					$usql = 
					"UPDATE ".DATABASE.".inventario SET 
						reg_del = 1,
						reg_who = '".$_SESSION["id_funcionario"]."',
						data_del = '".date('Y-m-d')."'					
					WHERE reg_del = 0 AND id_inventario = ".$codigoInventario;
					
					$db->update($usql, 'MYSQL');
				}
			}
			
			if (!empty($dados_form['locaisTrabalho']))
			{
				$isql = 
				"INSERT INTO ".DATABASE.".inventario_locais
					(il_id_local, il_id_inventario, il_data)
				VALUES(".$dados_form['locaisTrabalho'].", ".$codigoInventario.", '".php_mysql($dados_form['data_retirada'])."')";
				
				$db->insert($isql, 'MYSQL');
				
				if ($db->erro != '')
				{
					$resposta->addAlert('Houve uma falha ao tentar inserir o registro');
					$resposta->addScript("xajax_atualizatabela('');");
					$resposta->addScript("frm_inventario.reset();");
				}
				else
				{
					$sql = 
					"SELECT
						*
					FROM
						".DATABASE.".usuarios
						JOIN(
				          SELECT id_funcionario codigoFuncionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.reg_del = 0
				        ) funcionario
				        ON codigoFuncionario = id_funcionario
				        JOIN(
				          SELECT * FROM ".DATABASE.".inventario
				          JOIN(
				              SELECT id_equipamento codigo_equipamento, equipamento, num_dvm patrimonio FROM ".DATABASE.".equipamentos WHERE equipamentos.reg_del = 0
				          ) equipamentos
				          ON codigo_equipamento = id_equipamento
				          WHERE inventario.reg_del = 0 AND inventario.id_inventario = '".$codigoInventario."'
				        ) inventario
				        ON id_funcionario = id_funcionario AND inventario.reg_del = 0 ";
		
					$db->select($sql, 'MYSQL',true);
					
					$reg_usuario = $db->array_select[0];
					
					if ($res->erro != '')
					{
						$retorno = 0;
					}
					
					if ($db->numero_registros > 0)
					{
						/*
						 * Enviando o email
						 */
						$params 			= array();
						$params['from']		= "ti@".DOMINIO;
						$params['from_name']= "Tecnologia e Sistemas";
						$params['subject'] 	= 'Empréstimo do equipamento '.$reg_usuario["patrimonio"].' - '.$reg_usuario["equipamento"];
						
						$params['emails']['to'][] = array('email' => "ti@".DOMINIO, 'nome' => "Sistemas");
						$params['emails']['to'][] = array('email' => $reg_usuario["email"], 'nome' => $reg_usuario["email"]);		
						
						$corpo = 
						"<html>
							<body style='font: 11pt Arial'>
								<p>Foi realizado o empréstimo do equipamento <b>".$reg_usuario["patrimonio"]."</b> - <b>".$reg_usuario["equipamento"]."</b></p>
								<p><b>Colaborador responsável</b>: ".$reg_usuario["funcionario"]."</p>
								<p><b>Data do empréstimo</b>: " . date("d/m/Y") . "</p>
							</body>
						 </html>";
						
						$mail = new email($params);
						$mail->montaCorpoEmail($corpo);
						
						if (!$mail->send())
						{
							$resposta->addAlert('Houve uma falha ao tentar enviar o email!');
						}
					}
					
					$resposta->addAlert('Registro inserido corretamente!');
					$resposta->addScript("xajax_atualizatabela('');");
					$resposta->addScript("frm_inventario.reset();");
				}
			}
		}
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("realizarBaixa");
$xajax->registerFunction("historico");
$xajax->registerFunction("showModalHistorico");
$xajax->registerFunction("gravarlocal");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>
<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script type="text/javascript">
function verificaForm()
{
	var itens = 0;
	//Otimizar esta operação
	//Abaixo verifico se existem itens obrigatórios não preenchidos para desabilitar ou habilitar o botão inserir
	$('.obrigatorio').each(function(){
		if ($.trim($(this).val()) === '')
		{
			itens++;
		}
	});
	
	if (itens > 0)
		return false;
	else
		return true;
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	switch(tabela)
	{
		case 'listagem':
			mygrid.setHeader("Codigo,Equipamento, Proprietário, Funcionário, Data Retirada, OS, Baixa, Termo, Histórico");
			mygrid.setInitWidths("100,*, 100, 220, 110, 60, 60, 60, 80");
			mygrid.setColAlign("left,left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
		break;
		case 'lista_historico':
			mygrid.setHeader("data, local, Atual");
			mygrid.setInitWidths("100, *, 60");
			mygrid.setColAlign("left,left,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");
		break;
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function modal_baixa_equipamento(id)
{
	var html =
		'<label class="labels">O equipamento está em perfeito estado?</label><br />'+
		'<button id="btnSim" name="btnSim" onclick=$(".obsBaixa").hide();xajax_realizarBaixa('+id+');>Sim</button>'+
		'<button id="btnNao" name="btnNao" onclick=$(".obsBaixa").show();>Não</button><br />'+
		'<label class="labels obsBaixa" style="display:none;">Situação do equipamento</label><br />'+
		'<textarea id="obsBaixa" name="obsBaixa" class="caixa obsBaixa" style="display:none;width:100%;"></textarea><br />'+
		'<button class="class_botao obsBaixa" style="display:none;" onclick=xajax_realizarBaixa('+id+',$("#obsBaixa").val());>Salvar</button>';
	
	modal(html, '180_300', 'Realizar baixa do equipamento');
}
</script>

<?php
$conf = new configs();

//Funcionários
$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao = 'ATIVO' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$array_func_values = array('' => '');
$array_func_output = array('' => 'Selecione um funcionario');

$db->select($sql,'MYSQL', function($reg, $i) use(&$array_func_values, &$array_func_output){
	$array_func_values[] = $reg["id_funcionario"];
	$array_func_output[] = $reg["funcionario"];
});

$smarty->assign("option_func_values", $array_func_values);
$smarty->assign("option_func_output", $array_func_output);

//Equipamentos
$sql = "SELECT
		  id_equipamento, equipamento, num_dvm
		FROM
		".DATABASE.".equipamentos
		  LEFT JOIN(
		    SELECT
		      id_equipamento codigo_equipamento, id_funcionario, data_saida, id_inventario, situacao, os 
		    FROM
			".DATABASE.".inventario
		      JOIN(
		        SELECT
		          id_funcionario, funcionario, situacao AS status
		        FROM
		          ".DATABASE.".funcionarios WHERE reg_del = 0 
		      ) funcionario
		      ON id_funcionario = id_funcionario
		    WHERE
		      inventario.reg_del = 0
		      AND inventario.situacao = 1
		  )
		  inventario
		  ON inventario.codigo_equipamento = id_equipamento
		WHERE
		  equipamentos.reg_del = 0
        ORDER BY equipamentos.num_dvm";

$array_equip_values = array('' => '');
$array_equip_output = array('' => 'Selecione um Equipamento');

$db->select($sql,'MYSQL', function($reg, $i) use(&$array_equip_values, &$array_equip_output){
	$array_equip_values[] = $reg["id_equipamento"];
	$array_equip_output[] = '('.$reg["num_dvm"].') '.$reg["equipamento"];
});

$smarty->assign("option_equip_values", $array_equip_values);
$smarty->assign("option_equip_output", $array_equip_output);

$area = $_SESSION['Perfil'] == 1 ? 'TI' : 'ADM';

$sql = "SELECT * FROM ".DATABASE.".acessorios ";
$sql .= "WHERE acessorios.reg_del = 0 ";
$sql .= "AND acessorios.area = '".$area."' ";

$acessorios = $db->select($sql,'MYSQL');
$smarty->assign('acessorios', $acessorios);

$sql = "SELECT * FROM  ".DATABASE.".ordem_servico ";
$sql .= "WHERE id_os_status IN(1, 14) ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "ORDER BY ordem_servico.os ";

$array_os_values = array('' => '');
$array_os_output = array('' => 'Selecione uma OS');

$db->select($sql,'MYSQL', function($reg, $i) use(&$array_os_values, &$array_os_output){
	$array_os_values[] = $reg["id_os"];
	$array_os_output[] = sprintf('%05d', $reg["os"]);
});

$smarty->assign("option_os_values", $array_os_values);
$smarty->assign("option_os_output", $array_os_output);

$sql = "SELECT id_local, descricao FROM ".DATABASE.".local ";
$sql .= "WHERE local.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$locaisTrabalho = $db->select($sql, 'MYSQL');

$array_local_values = array('' => '');
$array_local_output = array('' => 'Selecione um local de Trabalho');

$db->select($sql,'MYSQL', function($reg, $i) use(&$array_local_values, &$array_local_output){
	$array_local_values[] = $reg["id_local"];
	$array_local_output[] = $reg["descricao"];
});

$smarty->assign("option_local_values", $array_local_values);
$smarty->assign("option_local_output", $array_local_output);

$smarty->assign('area', $area);
$smarty->assign("campo",$conf->campos('inventario'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("revisao_documento","V2");
$smarty->assign('larguraTotal', 1);
$smarty->assign("classe",CSS_FILE);
$smarty->display('inventario.tpl');
?>