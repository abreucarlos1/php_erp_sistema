<?php
/*
		Formulário de Acompanhamento Orçamento
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:		
		../contratos_controle/controle_negociando_protheus.php
		
		Versão 0 --> VERSÃO INICIAL - 25/07/2013
		Versão 1 --> Mudança dos bancos de projetos para orcamento - 12/11/2013 - Carlos Abreu
		Versão 2 --> Atualização classe banco - 20/01/2015 - Carlos Abreu
		Versão 3 --> Atualização - 09/04/2015 - Eduardo
		Versão 4 --> Atualização layout - Carlos Abreu - 23/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(625))
{
	nao_permitido();
}

$conf = new configs();

function atualizatabela($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".bms_previsao_vendas WHERE reg_del = 0 AND ano = ".$dados_form['selAno']." AND (confirmado = 1 OR id_confirmar IS NOT NULL)  ORDER BY tp_orcamento";
    
    $xml = new XMLWriter();
    
    $xml->setIndent(false);
    $xml->openMemory();
    $xml->startElement('rows');
    
    $alteracoesAconfirmar = false;

    $comboTipo = array('1' => 'MO/ADM', '2' => 'PROJETO');
    $db->select($sql,'MYSQL',function($reg, $i) use(&$xml, $comboTipo, &$alteracoesAconfirmar){
        $xml->startElement('row');
        
        if (!empty($reg["id_confirmar"]))
        {
            $xml->writeAttribute('style', 'background-color:#ccffcc;');
        }
        
        $xml->writeAttribute('id', empty($reg["id_confirmar"]) ? $reg["id_bms_previsao_vendas"] : $reg["id_confirmar"]);
        $xml->writeElement('cell', number_format($reg['val_janeiro'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_fevereiro'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_marco'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_abril'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_maio'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_junho'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_julho'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_agosto'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_setembro'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_outubro'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_novembro'],2,',','.'));
        $xml->writeElement('cell', number_format($reg['val_dezembro'],2,',','.'));
        
        if ($reg['tp_orcamento'] == 1)
        {
            $a = 'selected="selected"';
            $b = '';
        }
        else
        {
            $a = '';
            $b = 'selected="selected"';
        }
        
        $xml->writeElement('cell', $comboTipo[$reg['tp_orcamento']]);
        
        $xml->endElement();
        
        if ($reg['id_confirmar'] && $alteracoesAconfirmar == false)
            $alteracoesAconfirmar = true;
    });
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('divLista', true, '490', '".$conteudo."');");
    
    if ($alteracoesAconfirmar)
        $resposta->addScript('document.getElementById("trConfirmar").style.display = "block";');
    else
        $resposta->addScript('document.getElementById("trConfirmar").style.display = "none";');
    
    //Caso o ano selecionado ainda não exista no banco, criar dois registros um para cada tipo de operação
    if ($db->numero_registros == 0)
    {
        $isql = "INSERT INTO ".DATABASE.".bms_previsao_vendas ";
        $isql .= "(val_janeiro, val_fevereiro, val_marco, val_abril, val_maio, val_junho, val_julho, val_agosto, val_setembro, val_outubro, val_novembro, val_dezembro, ano, tp_orcamento, confirmado) values ";
        $isql .= "(null,null,null,null,null,null,null,null,null,null,null,null,".$dados_form['selAno'].", 1,1),";
        $isql .= "(null,null,null,null,null,null,null,null,null,null,null,null,".$dados_form['selAno'].", 2,1)";
        
        $db->insert($isql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar incluir o registro do ano selecionado.');
        }
        else
        {
            $resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
        }
    }
    
    return $resposta;
}

function editar($idBmsPrevisaoVendas, $valor, $colunaAlterada)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Busca os dados do registro a ser alterado
    $sql = "SELECT * FROM ".DATABASE.".bms_previsao_vendas WHERE id_confirmar = ".$idBmsPrevisaoVendas;
    $db->select($sql, 'MYSQL', true);
    
    $colunas = array('val_janeiro', 'val_fevereiro', 'val_marco', 'val_abril', 'val_maio', 'val_junho', 'val_julho', 'val_agosto',
                     'val_setembro', 'val_outubro', 'val_novembro', 'val_dezembro', 'ano', 'tp_orcamento');
    
    if ($db->numero_registros > 0)
    {
        $usql = "UPDATE ".DATABASE.".bms_previsao_vendas SET ".$colunas[$colunaAlterada]." = '".$valor."' ";
        $usql .= "WHERE id_bms_previsao_vendas = ".$db->array_select[0]['id_bms_previsao_vendas'];
        
        $db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
            $retorno = false;
        else
            $retorno = true;
    }
    else
    {
        $isql = "INSERT INTO ".DATABASE.".bms_previsao_vendas ";
        $isql .= "(val_janeiro, val_fevereiro, val_marco, val_abril, val_maio, val_junho, val_julho, val_agosto, val_setembro, val_outubro, val_novembro, val_dezembro, ano, tp_orcamento, id_confirmar, confirmado)";
        $isql .= "SELECT val_janeiro, val_fevereiro, val_marco, val_abril, val_maio, val_junho, val_julho, val_agosto, val_setembro, val_outubro, val_novembro, val_dezembro, ano, tp_orcamento, id_bms_previsao_vendas, 0 ";
        $isql .= "FROM ".DATABASE.".bms_previsao_vendas WHERE id_bms_previsao_vendas = ".$idBmsPrevisaoVendas." AND reg_del = 0";
        
        $db->insert($isql, 'MYSQL');
        
        if ($db->erro != '')
            $retorno = false;
        else
        {
            $usql = "UPDATE ".DATABASE.".bms_previsao_vendas SET ".$colunas[$colunaAlterada]." = '".$valor."' ";
            $usql .= "WHERE id_bms_previsao_vendas = ".$db->insert_id;
            
            $db->update($usql, 'MYSQL');
            
            //Alterando o não confirmado para manter apenas um registro no momento
            $usql = 'UPDATE ".DATABASE.".bms_previsao_vendas SET confirmado = 0 WHERE id_bms_previsao_vendas = '.$idBmsPrevisaoVendas;
            $db->update($usql, 'MYSQL');
            
            $retorno = true;
        }
    }
    
    if (!$retorno)
    {
        $resposta->addAlert('Houve uma falha ao tentar alterar o registro.');
    }
    else
    {
        $resposta->addAlert('Alteração realizada corretamente!');
        
    }
    
    return $resposta;
}

function confirmar_alteracoes($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $usql = "UPDATE ".DATABASE.".bms_previsao_vendas SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE ano = '".$dados_form['selAno']."' AND reg_del = 0 AND confirmado = 0 AND id_confirmar IS NULL";
    $db->update($usql, 'MYSQL');
    
    $usql = 'UPDATE ".DATABASE.".bms_previsao_vendas SET confirmado = 1, id_confirmar = null WHERE ano = '.$dados_form['selAno'].' AND reg_del = 0 AND confirmado = 0';
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar alterar o registro.');
    }
    else
    {
        $resposta->addAlert('Alteração realizada corretamente!');
        
        $resposta->addScript('xajax_atualizatabela(xajax.getFormValues("frm"));');
    }
    
    return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("confirmar_alteracoes");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");
?>
<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>
<script src="<?php echo ROOT_WEB.'/includes/' ?>dhtmlx_403/codebase/dhtmlx.js"></script>
<script language="javascript">
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Janeiro, Fevereiro, Março, Abril, Maio, Junho, Julho, Agosto, Setembro, Outubro, Novembro, Dezembro, tipo");
	mygrid.setInitWidths("90,90,90,90,90,90,90,90,90,90,90,90,120");
	mygrid.setColAlign("left,left,left,left,left,left,left,left,left,left,left,left,left");
	mygrid.setColTypes("ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ro");
	
	mygrid.setColSorting("str,str,str,str,str,,str,str,str,str,str,str,str");
	mygrid.enableEditEvents(true,true,true,true,true,true,true,true,true,true,true,true);

	//Editor usando a propria grid
	mygrid.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){
		if (stage == 2 && nValue != oValue)
		{
			xajax_editar(rId, nValue, cInd);
		}

		return true;
	});

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	
	mygrid.loadXMLString(xml);
}
</script>

<?php
$sql = "SELECT DISTINCT YEAR(data_pedido) ANO FROM ".DATABASE.".bms_pedido WHERE reg_del = 0 AND YEAR(data_pedido) > 2016 ORDER BY YEAR(data_pedido) DESC";
$db->select($sql,'MYSQL', true);
						 
foreach($db->array_select as $regs)
{
	$array_ano_values[] = $regs["ANO"];
	$array_ano_output[] =  $regs["ANO"];
}

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign('campo', $conf->campos('planilha_orcamentacao'));

$smarty->assign('revisao_documento', 'V0');

$smarty->assign('larguraTotal', '1');

$smarty->assign("classe",CSS_FILE);

$smarty->display('controle_orcamentacao.tpl');
?>