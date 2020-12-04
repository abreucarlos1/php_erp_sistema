<?php
/*
		Formulário de avaliação de eficacia de treinamentos
		
		Criado por Carlos Eduardo Maximo  
		
	  local/Nome do arquivo:
	  ../rh/avaliacao_eficacia.php
				
		Versão 0 --> VERSÃO INICIAL : 30/10/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 2 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(611))
{
	nao_permitido();
}

function atualizatabela()
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".rh_treinamentos_cabecalho
        JOIN(
        SELECT * FROM ".DATABASE.".rh_treinamentos_itens
            JOIN(
                SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADODVM','CANCELADO') AND funcionarios.reg_del = 0
            ) funcionarios
            ON id_funcionario = rti_id_funcionario
        WHERE
            reg_del = 0
        ) itens
        ON rti_rtc_id = rtc_id
        JOIN(
        SELECT * FROM ".DATABASE.".rh_treinamentos
        ) treinamento
        ON id_rh_treinamento = rtc_id_treinamento
        WHERE treinamento.reg_del = 0
        AND rtc_responsavel_eficacia = ".$_SESSION['id_funcionario']."
        ORDER BY
            rtc_data_vencimento, funcionario";

    $xml = new XMLWriter();
    $xml->setIndent(false);
    $xml->openMemory();
    $xml->startElement('rows');
    
    $arrDescrTipo = array(1 => 'FORMAÇÃO', 2 => 'RECICLAGEM', 3 => 'INTERNO', 4 => 'EXTERNO', 5 => 'ON THE JOB', 6 => 'OBRIGATÓRIO');
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$xml, &$arrDescrTipo){
        if ($reg['rti_eficacia'] == '1')
            $simNao = '<span class="icone icone-aprovar"></span>';
        else if ($reg['rti_eficacia'] == '0')
            $simNao = '<span class="icone icone-atencao cursor" onclick="xajax_showModalDescricaoEficacia('.$reg['rti_id'].');"></span>';
        else
            $simNao = "<select onchange=if(this.value!=\'\'){showModalEficacia(".$reg['rti_id'].",this.value);} class=\'caixa\' name=\'eficacia[".$reg['rti_id']."]\' id=\'eficacia[".$reg['rti_id']."]\'><option value=\'\'></option><option value=\'1\'>S</option><option value=\'0\'>N</option></select>";
        
        $xml->startElement('row');
            $xml->writeAttribute('id', $reg['rti_id']);
            $xml->writeElement('cell', $reg['rtc_id']);
            $xml->writeElement('cell', $reg['funcionario']);
            $xml->writeElement('cell', $reg['treinamento']);
            $xml->writeElement('cell', $arrDescrTipo[$reg["rtc_id_tipo"]]);
            $xml->writeElement('cell', mysql_php($reg["rtc_data_treinamento"]));
            $xml->writeElement('cell', $simNao);
        $xml->endElement();
    });
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    if ($db->numero_registros > 0)
        $resposta->addScript("grid('divLista', true, '400', '".$conteudo."');");
    else
        $resposta->addAssign('divLista', 'innerHTML', '<label class="labels">NÃO EXISTEM TREINAMENTOS PARA SEREM AVALIADOS NO MOMENTO</label>');
    
    return $resposta;
}

function salvarEficacia($idItem, $eficaz, $dados_form = array())
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    //Excluindo todos os planos do item selecionado
    $usql = "UPDATE ".DATABASE.".rh_treinamentos_plano_acao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rta_rti_id = ".$idItem." ";
	$usql .= "AND reg_del = 0 ";
    
	$db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar realizar esta alteração. '.$db->erro);
    }
    else
    {
        $usql = "UPDATE ".DATABASE.".rh_treinamentos_itens SET ";
		$usql .= "rti_eficacia = '".$eficaz."' ";
		$usql .= "WHERE rti_id = '".$idItem."' ";
		$usql .= "AND reg_del = 0 ";
        
		$db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar realizar esta alteração. '.$db->erro);
        }
        else
        {
            $virgula = '';
            $isql = "INSERT ".DATABASE.".rh_treinamentos_plano_acao (rta_rti_id, rta_motivo, rta_proposta, rta_prazo) VALUES ";
            
			foreach($dados_form['observacao'] as $k => $motivo)
            {
                if (!empty($motivo))
                {
                    $isql .= $virgula."(".$idItem.", '".tiraacentos($motivo)."', '".tiraacentos($dados_form['proposta_correcao'][$k])."', '".php_mysql($dados_form['prazo'][$k])."')";
                    $virgula = ',';
                }
            }
            
            if (!empty($virgula))
            {
                $db->insert($isql, 'MYSQL');
                if ($db->erro != '')
                {
                    $resposta->addAlert('Houve uma falha ao tentar realizar esta alteração. '.$db->erro);
                }
                else
                {
                    $resposta->addAlert('Atualização realizada corretamente!');
                    $resposta->addScript('xajax_atualizatabela();');
                    $resposta->addScript('divPopupInst.destroi();');
                }
            }        
        }
    }
    
    return $resposta;
}

function showModalDescricaoEficacia($idItem)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT
        rta_objetivo, rta_motivo, rta_proposta, rta_prazo
        FROM
            ".DATABASE.".rh_treinamentos_itens
            JOIN ".DATABASE.".rh_treinamentos_plano_acao ON rta_rti_id = rti_id AND rh_treinamentos_plano_acao.reg_del = 0
        WHERE
            reg_del = 0 
            AND rti_id = '".$idItem."' ";
    
    $db->select($sql, 'MYSQL', true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar realizar esta operação. '.$db->erro);
    }
    else
    {
        $html = 
        '<table class="auto_lista" width="100%">
            <tr><th>Motivo</th><th>Proposta de correção</th><th width="5%">Prazo</th></tr>';
            
        foreach($db->array_select as $reg)
        {
            $html .= '<tr><td>'.$reg['rta_motivo'].'</td>
            <td>'.$reg['rta_proposta'].'</td>
            <td>'.mysql_php($reg['rta_prazo']).'</td></tr>';
        }
        
        $html .= '</table>';
        
        $resposta->addScriptCall("modal", $html, "250_1024", "Observações da falta de eficácia");
    }
    
    return $resposta;
}


$xajax->registerFunction('atualizatabela');
$xajax->registerFunction('salvarEficacia');
$xajax->registerFunction("showModalDescricaoEficacia");
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

	mygrid.setHeader("Nº, Funcionário, Treinamento, Tipo, Data, Eficácia");
	mygrid.setInitWidths("40,190,*,80,90,90");
	mygrid.setColAlign("left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function showModalEficacia(idItem, simNao)
{
	if (simNao == 1)
	{
		xajax_salvarEficacia(idItem,1);
		return false;
	}

	var html = '<form id="frmRenovacao"  target="_blank" method="post">'+
	'<input type="hidden" id="idItem" name="idItem" value='+idItem+' />'+
	'<table class="auto_lista" width="100%">'+
	'<tr><th>Motivo</th><th>Proposta de correção</th><th width="5%">Prazo</th></tr>'+
	'<tr><td><textarea class="caixa" id="observacao1" name="observacao[]" style="width:100%;margin:0;" rows="1" placeholder="Motivo 1"></textarea></td>'+
	'<td><textarea class="caixa" id="proposta_correcao1" name="proposta_correcao[]" style="width:100%;margin:0;" rows="1" placeholder="Proposta de correção 1"></textarea></td>'+
	'<td><input type="text" size="10" name="prazo[]" id="prazo1" class="caixa" onKeyPress="transformaData(this, event);" value="" placeholder="Prazo 1" onBlur="return checaTamanhoData(this,10);" /></td></tr>'+
	
	'<tr><td><textarea class="caixa" id="observacao2" name="observacao[]" style="width:100%;margin:0;" rows="1" placeholder="Motivo 2"></textarea></td>'+
	'<td><textarea class="caixa" id="proposta_correcao2" name="proposta_correcao[]" style="width:100%;margin:0;" rows="1" placeholder="Proposta de correção 2"></textarea></td>'+
	'<td><input type="text" size="10" name="prazo[]" id="prazo2" class="caixa" onKeyPress="transformaData(this, event);" value="" placeholder="Prazo 2" onBlur="return checaTamanhoData(this,10);" /></td></tr>'+

	'<tr><td><textarea class="caixa" id="observacao3" name="observacao[]" style="width:100%;margin:0;" rows="1" placeholder="Motivo 3"></textarea></td>'+
	'<td><textarea class="caixa" id="proposta_correcao3" name="proposta_correcao[]" style="width:100%;margin:0;" rows="1" placeholder="Proposta de correção 3"></textarea></td>'+
	'<td><input type="text" size="10" name="prazo[]" id="prazo3" class="caixa" onKeyPress="transformaData(this, event);" value="" placeholder="Prazo 2" onBlur="return checaTamanhoData(this,10);" /></td></tr>'+
	
	'</table>'+
	'<br /><input type="button" id="btnSalvarRenovacao" name="btnSalvarRenovacao" onclick="xajax_salvarEficacia('+idItem+',0,xajax.getFormValues(\'frmRenovacao\'));" value="Enviar" class="class_botao" />';

	modal(html, '250_1024', 'Plano de ação');
}
</script>
<?php

    $conf = new configs();
    $smarty->assign('campo', $conf->campos('avaliacao_eficacia'));
    $smarty->assign('botoes', $conf->botoes());

    $smarty->assign('revisao_documento', 'V2');

    $smarty->assign('larguraTotal', 1);

    $smarty->assign("classe",CSS_FILE);

    $smarty->display('avaliacao_eficacia.tpl');
    
?>