<?php
/*
		Formulário de arquivo morto - agmc	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../arquivotec/arquivo_morto.php
		
		Versão 0 --> VERSÃO INICIAL - 19/02/2018 - Chamado #2623
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(618))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela_versoes()
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql = "SELECT
				a.revisao_documento, a.data, b.funcionario, a.status
			FROM
				".DATABASE.".arquivo_morto_versoes a
                JOIN ".DATABASE.".funcionarios b ON b.id_funcionario = a.id_funcionario AND b.reg_del = 0
            WHERE
                a.reg_del = 0
            ORDER BY
                a.revisao_documento DESC";

	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $reg)
	{
	    $icone = $reg['status'] == 0 ? "<span class=\'icone icone-cadeado-aberto\'></span>" : '';
	    
		$xml->startElement('row');
		$xml->writeElement('cell', $icone.' '.$reg['revisao_documento']);
		$xml->writeElement('cell', $reg['funcionario']);
		$xml->writeElement('cell', mysql_php($reg['data']));
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_lista_versoes', true, '330', '".$conteudo."');");
	
	return $resposta;
}

function atualizatabela_descartes()
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    $sql = "SELECT
				a.revisao_documento, a.data, b.funcionario, a.status, a.nome_arquivo, a.ano_referencia
			FROM
				".DATABASE.".arquivo_morto_descartes a
                JOIN ".DATABASE.".funcionarios b ON b.id_funcionario = a.id_funcionario AND b.reg_del = 0
            WHERE
                a.reg_del = 0
            ORDER BY
                a.revisao_documento DESC";
    
    $db->select($sql,'MYSQL',true);
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $chars = array("'","\"",")","(","\\","/");
    
    foreach($db->array_select as $reg)
    {
        if (!empty($reg['nome_arquivo']))
            $icone = "<span class=\'icone icone-arquivo-xls cursor\' onclick=window.open(\'../includes/documento.php?documento=".DOCUMENTOS_GED."ARQUIVO_MORTO/_versoes/".$reg['nome_arquivo']."\');></span>";
        else
            $icone = '';
        
        $xml->startElement('row');
        $xml->writeElement('cell', $icone.' '.$reg['revisao_documento']);
        $xml->writeElement('cell', $reg['funcionario']);
        $xml->writeElement('cell', mysql_php($reg['data']));
        $xml->writeElement('cell', $reg['ano_referencia']);
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_lista_descartes', true, '330', '".$conteudo."');");
    
    return $resposta;
}

function atualizatabela_os($idFuncionario, $coordenador = 0)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $complCoord = $coordenador > 0 ? 'AND b.id IS NOT NULL AND b.status = 0' : 'AND (b.id IS NULL OR b.status = 0)';
    
    $sql = "SELECT
                a.id_os, b.id, a.os, a.descricao, b.status
            FROM 
                ".DATABASE.".ordem_servico a
                LEFT JOIN ".DATABASE.".arquivo_morto_aprovadas b ON b.reg_del = 0 AND b.id_os = a.id_os 
            WHERE 
                a.reg_del = 0
            AND a.OS > 3000
            AND a.id_cod_coord = ".$idFuncionario."
            ".$complCoord."
            ORDER BY
                b.status DESC, a.os";
    
    $db->select($sql, 'MYSQL', true);
    
    if ($db->numero_registros > 0)
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement('rows');
        
        $chars = array("'","\"",")","(","\\","/");
        
        foreach($db->array_select as $k => $reg)
        {
            $texto = 0;
            $style = '';
            $compl = '';
            
            if ($reg['status'] == 0 && $reg['id'] > 0)
            {
                $style = 'background-color:#FF0000;';
                //$texto = 1;
                $compl = '#';
            }
            
            $xml->startElement('row');
            $xml->writeAttribute('id', $reg['id_os'].$compl);
            
            $xml->startElement('cell');
            $xml->writeAttribute('style',$style);
            $xml->text($texto);
            $xml->endElement();
            
            $xml->writeElement('cell', sprintf('%06d', $reg['OS']).' - '.$reg['descricao']);
            $xml->endElement();
        }
        
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        
        $resposta->addScript("grid('div_lista_os', true, '450', '".$conteudo."');");
    }
    else
    {
        $resposta->addAssign("div_lista_os", 'innerHTML', '<label class="labels">Não há OS s para realizar esta operação</label>');
    }
    
    $resposta->addScript('hideLoader();');
    
    return $resposta;
}

function liberarBloquear($botao, $dados_form='', $reinserir = 1)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
    $revisao_documento = 0;
    $status = 0;
    $idFuncionario = '';
    
    //Pega a ultima revisao_documento do sistema
    $sql = "SELECT revisao_documento+1 revisao_documento, status, id_funcionario FROM ".DATABASE.".arquivo_morto_versoes WHERE reg_del = 0 ORDER BY id DESC LIMIT 0, 1";
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$revisao_documento,&$status,&$idFuncionario){
        $revisao_documento = $reg['revisao_documento'];
        $status = $reg['status'];
        $idFuncionario = $reg['id_funcionario'];
    });
    
    if ($db->numero_registros == 0)
    {
        $status = 1;
    }
    
	//Desbloquear
	if ($botao == 1)
	{
	    if ($status == 1 && $reinserir)
	    {
    	    $isql = "INSERT INTO
                        ".DATABASE.".arquivo_morto_versoes (id_funcionario, revisao_documento, data, status)
                    VALUES
                        ('".$_SESSION['id_funcionario']."', '".$revisao_documento."', '".date('Y-m-d')."', 0)";
            
            $db->insert($isql, 'MYSQL');
            
            $status = 0;
            $idFuncionario = $_SESSION['id_funcionario'];
	    }

        $btnLiberar = $status == 1 ? true : false;
        $btnBloquear = ($status == 0 && $idFuncionario == $_SESSION['id_funcionario']) ? true : false;
        
        $resposta->addAssign('btnLiberar', 'disabled', !$btnLiberar);
        $resposta->addAssign('btnBloquear', 'disabled', !$btnBloquear);
        $resposta->addScript('document.getElementById("iconeCadeadoAberto").style.display="block";');
        $resposta->addScript('document.getElementById("iconeCadeadoFechado").style.display="none";');
        $resposta->addAssign('btnArquivoMorto', 'disabled', !$btnBloquear);
        
        if ($btnBloquear)
           $resposta->addScript('document.getElementById("fileArquivoMorto").style.display="block";');
        
        $resposta->addScript('xajax_atualizatabela_versoes();');
	}
	else //Bloquear
	{
	    if (empty($dados_form['fileArquivoMorto']))
	    {
            $resposta->addAlert('É necessário selecionar um arquivo antes de realizar o bloqueio.');   
	    }
	    else
	    {
	        $resposta->addScript('document.getElementById("frm").submit();');
	        
    	    $resposta->addAssign('btnLiberar', 'disabled', false);
    	    $resposta->addAssign('btnBloquear', 'disabled', true);
        	$resposta->addScript('document.getElementById("iconeCadeadoAberto").style.display="none";');
        	$resposta->addScript('document.getElementById("iconeCadeadoFechado").style.display="block";');
        	$resposta->addAssign('btnArquivoMorto', 'disabled', true);
    	    $resposta->addScript('document.getElementById("fileArquivoMorto").style.display="none";');
	    }
	}
	
	return $resposta;
}

function liberarBloquearDescarte($botao, $dados_form='', $reinserir = 1)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $revisao_documento = 0;
    $status = 0;
    $idFuncionario = '';
    //Pega a ultima revisao_documento do sistema
    $sql = "SELECT revisao_documento+1 revisao_documento, status, id_funcionario FROM ".DATABASE.".arquivo_morto_descartes WHERE reg_del = 0 ORDER BY id DESC LIMIT 0, 1";
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$revisao_documento,&$status,&$idFuncionario){
        $revisao_documento = $reg['revisao_documento'];
        $status = $reg['status'];
        $idFuncionario = $reg['id_funcionario'];
    });
        
    if ($db->numero_registros == 0)
    {
        $status = 1;
    }
    
    //Desbloquear
    if ($botao == 1)
    {
        if ($status == 1 && $reinserir)
        {
            $isql = "INSERT INTO
                    ".DATABASE.".arquivo_morto_descartes (id_funcionario, revisao_documento, data, status)
                VALUES
                    ('".$_SESSION['id_funcionario']."', '".$revisao_documento."', '".date('Y-m-d')."', 0)";
            $db->insert($isql, 'MYSQL');
            
            $status = 0;
            $idFuncionario = $_SESSION['id_funcionario'];
        }
        
        $btnLiberar = $status == 1 ? true : false;
        $btnBloquear = ($status == 0 && $idFuncionario == $_SESSION['id_funcionario']) ? true : false;
        
        $resposta->addAssign('btnLiberarDescarte', 'disabled', !$btnLiberar);
        $resposta->addAssign('btnBloquearDescarte', 'disabled', !$btnBloquear);
        $resposta->addScript('document.getElementById("iconeCadeadoAbertoDescarte").style.display="block";');
        $resposta->addScript('document.getElementById("iconeCadeadoFechadoDescarte").style.display="none";');
        $resposta->addAssign('btnArquivoDescarte', 'disabled', !$btnBloquear);
        
        if ($btnBloquear)
            $resposta->addScript('document.getElementById("fileArquivoDescarte").style.display="block";');
            
        $resposta->addScript('xajax_atualizatabela_descartes();');
    }
    else //Bloquear
    {
        if (empty($dados_form['fileArquivoDescarte']))
        {
            $resposta->addAlert('É necessário selecionar um arquivo antes de realizar o bloqueio.');
        }
        else
        {
            $resposta->addScript('document.getElementById("frm").submit();');
            
            $resposta->addAssign('btnLiberarDescarte', 'disabled', false);
            $resposta->addAssign('btnBloquearDescarte', 'disabled', true);
            $resposta->addScript('document.getElementById("iconeCadeadoAbertoDescarte").style.display="none";');
            $resposta->addScript('document.getElementById("iconeCadeadoFechadoDescarte").style.display="block";');
            $resposta->addAssign('btnArquivoDescarte', 'disabled', true);
            $resposta->addScript('document.getElementById("fileArquivoDescarte").style.display="none";');
        }
    }
    
    return $resposta;
}

function insere($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $arrOs = explode(',', $dados_form['selecionados']);
    
    $idFuncionario = $dados_form['funcionario'];
    
    $isql = "INSERT INTO ".DATABASE.".arquivo_morto_aprovadas (id_funcionario, id_os, data, status) VALUES ";
    
    foreach($arrOs as $os)
    {
        $sql = "SELECT * FROM ".DATABASE.".arquivo_morto_aprovadas WHERE reg_del = 0 AND id_os = ".$os;
        $db->select($sql, 'MYSQL');
        
        if ($db->numero_registros>0)
            continue;
        
        $isql .= $virgula."('".$idFuncionario."', '".$os."', '".date('Y-m-d')."', 0)";
        $virgula = ',';
    }
    
    if (!empty($virgula))
    {
        $db->insert($isql, 'MYSQL');    
        
        if (empty($db->erro))
        {
            $usql = "UPDATE ".DATABASE.".arquivo_morto_versoes 
                        JOIN (SELECT MAX(id) id2 FROM ".DATABASE.".arquivo_morto_versoes WHERE reg_del = 0) versoes2 on versoes2.id2 = id
                    SET status = 1";
            $db->update($usql, 'MYSQL');
            
            $sql = "SELECT 
                        funcionario, email 
                    FROM
                        ".DATABASE.".funcionarios f
                        JOIN ".DATABASE.".usuarios u ON u.reg_del = 0 AND u.id_funcionario = f.id_funcionario 
                    WHERE 
                        f.reg_del = 0 AND f.id_funcionario = ".$idFuncionario;
            
            $db->select($sql, 'MYSQL', true);
            $dadosFunc = $db->array_select[0];
            
            $sql = "SELECT
                a.id_os, b.id, a.os, a.descricao
            FROM 
                ".DATABASE.".ordem_servico a
                JOIN ".DATABASE.".arquivo_morto_aprovadas b ON b.reg_del = 0 AND b.id_os = a.id_os AND b.data = '".date('Y-m-d')."' 
            WHERE 
                a.id_os IN(".$dados_form['selecionados'].")
            AND a.reg_del = 0
            AND b.status = 0";
    
            $texto = "<b>Prezado ".$dadosFunc['funcionario']."</b>,<br /> As OS's abaixo aguardam aprovação para serem enviadas a AGMC<br />".
                     "Favor, acessar o módulo (Arquivo Técnico/Arquivo Morto) para realizar esta operação.<br /><br />".
                     '<b>OS</b>';
            
            $db->select($sql, 'MYSQL', function($reg, $i) use(&$texto){
                $texto .= '<br />'.sprintf('%05d', $reg['os']).' - '.$reg['descricao'];
            });
            
            if(ENVIA_EMAIL)
			{
                $params 			= array();
                $params['from']	    = "arquivotecnico@".DOMINIO;
                $params['from_name']= "OS S PARA APROVAÇÃO ARQUIVO MORTO";
                $params['subject'] 	= "OS S PARA APROVAÇÃO ARQUIVO MORTO";
                
                $params['emails']['to'][] = array('email' => $dadosFunc['email'], 'nome' => $dadosFunc['funcionario']);
                
                $mail = new email($params, 'aprovacao_arquivo_morto');
                
                $mail->montaCorpoEmail($texto);
                

                if ($mail->send())
                {
                    $resposta->addAlert('email enviado corretamente');
                    $resposta->addScript('showLoader();xajax_atualizatabela_os('.$idFuncionario.');');
                }
            }
            else 
            {
                $resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
            }

        }
    }
    else
    {
        $resposta->addAlert('Os itens já foram enviados, devendo agora ser aprovados');
    }
    
    return $resposta;
}

function aprovar($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $deveAprovar = strpos($dados_form['selecionados'], '#');
    
    if (intval($deveAprovar) == 0)
    {
        $resposta->addAlert('ATENÇÃO: Somente os campos em registros em verde podem ser aprovados');
        return $resposta;
    }
    
    $dados_form['selecionados'] = str_replace('#', '', $dados_form['selecionados']);
    $idFuncionario = !empty($dados_form['funcionario']) ? $dados_form['funcionario'] : $_SESSION['id_funcionario'];

    $usql = "UPDATE ".DATABASE.".arquivo_morto_aprovadas SET status = 1 WHERE id_os IN(".$dados_form['selecionados'].") AND id_funcionario = ".$idFuncionario;
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar atualizar os registros '.$db->erro);    
    }
    else
    {
        $sql = "SELECT
                        funcionario, email
                    FROM
                        ".DATABASE.".funcionarios f
                        JOIN ".DATABASE.".usuarios u ON u.reg_del = 0 AND u.id_funcionario = f.id_funcionario
                    WHERE
                        f.reg_del = 0 AND f.id_funcionario = ".$idFuncionario;
        
        $db->select($sql, 'MYSQL', true);
        $dadosFunc = $db->array_select[0];
        
        $sql = "SELECT
                a.id_os, b.id, a.os, a.descricao
            FROM
                ".DATABASE.".ordem_servico a
                JOIN ".DATABASE.".arquivo_morto_aprovadas b ON b.reg_del = 0 AND b.id_os = a.id_os
            WHERE
                a.id_os IN(".$dados_form['selecionados'].")
            AND a.reg_del = 0
            AND b.status = 1";
        
        $texto = "<b>OS S aprovadas por ".$dadosFunc['funcionario']."</b>,<br /> As OS's abaixo aguardam aprovação para serem enviadas a AGMC<br />".
            "Favor, acessar o módulo (Arquivo Técnico/Arquivo Morto) para realizar esta operação.<br /><br />".
            '<b>OS</b>';
        
        $db->select($sql, 'MYSQL', function($reg, $i) use(&$texto){
            $texto .= '<br />'.sprintf('%05d', $reg['os']).' - '.$reg['descricao'];
        });
        
        if(ENVIA_EMAIL)
        {        
            $params 			= array();
            $params['from']	    = "arquivotecnico@".DOMINIO;
            $params['from_name']= "OS S APROVADAS ARQUIVO MORTO";
            $params['subject'] 	= "OS S APROVADAS ARQUIVO MORTO";
            
            $params['emails']['to'][] = array('email' => $dadosFunc['email'], 'nome' => $dadosFunc['funcionario']);
            
            $mail = new email($params, 'aprovacao_arquivo_morto');
            
            $mail->montaCorpoEmail($texto);
            

            if (!$mail->send())
            {
                $resposta->addAlert($msg[21].$mail->ErrorInfo);
            }
        }
        else 
        {
            $resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
        }

        $coord = !in_array($_SESSION['id_setor_aso'], array(2,17)) ? 1 : 0;
        
        $resposta->addAlert('Alterações realizadas corretamente');
        
        $resposta->addScript('showLoader();xajax_atualizatabela_os('.$idFuncionario.','.$coord.');');
        
        $resposta->addScript("document.getElementById('btnenviar').disabled=true;");
        
    }
    
    return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("liberarBloquear");
$xajax->registerFunction("liberarBloquearDescarte");
$xajax->registerFunction("atualizatabela_versoes");
$xajax->registerFunction("atualizatabela_descartes");
$xajax->registerFunction("atualizatabela_os");
$xajax->registerFunction("insere");
$xajax->registerFunction("aprovar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$scripts = !in_array($_SESSION['id_setor_aso'], array(2,17)) ? 'showLoader();xajax_atualizatabela_os('.$_SESSION['id_funcionario'].',1);' : '';

if (in_array($_SESSION['id_setor_aso'], array(2,17)))
    $scripts .= "xajax_liberarBloquear(1,'',0);xajax_liberarBloquearDescarte(1,'',0);xajax_atualizatabela_descartes();";
    
$smarty->assign("body_onload","tab(".$_SESSION['id_setor_aso'].");xajax_atualizatabela_versoes();".$scripts);


//Se for Arquivo Técnico ou TI, montar acessos
$acessoTotal = in_array($_SESSION['id_setor_aso'], array(2,17)) ? true : false;

if ($acessoTotal)
{
    $array_func_values[] = "0";
    $array_func_output[] = "SELECIONE";
    	  
    $sql = "SELECT 
                id_funcionario, funcionario FROM ".DATABASE.".funcionarios
            WHERE 
                situacao = 'ATIVO'
                AND funcionarios.reg_del = 0
                AND id_cargo IN(36,37,202) /*coordenadores: comissionamento, projeto e mão de obra respectivamente*/
            ORDER BY
                funcionario ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
    	exit("Não foi possível realizar a seleção.".$sql);
    }
    
    foreach($db->array_select as $regs)
    {
    	$array_func_values[] = $regs["id_funcionario"];
    	$array_func_output[] = $regs["funcionario"];	
    }
    
    $smarty->assign("option_func_values",$array_func_values);
    $smarty->assign("option_func_output",$array_func_output);
}

$smarty->assign('pasta_ged', DOCUMENTOS_GED.'ARQUIVO_MORTO/');

$anoReferencia = array();
$ano = 2013;
while($ano <= date('Y'))
{
    $anoReferencia[] = $ano++;
}
$smarty->assign('anoReferencia', $anoReferencia);

$smarty->assign('campo', $conf->campos('arquivo_morto'));
$smarty->assign('acessoTotal', $acessoTotal);

$smarty->assign('revisao_documento', 'V0');

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('arquivo_morto.tpl');


?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script type="application/javascript">
// function liberarBotoes()
// {
//     document.getElementById('btnLiberar').disabled = true;
//     document.getElementById('btnBloquear').disabled = false;
//     document.getElementById("iconeCadeadoAberto").style.display="block";
//     document.getElementById("iconeCadeadoFechado").style.display="none";
//     document.getElementById('btnArquivoMorto').disabled = false;
//     document.getElementById("fileArquivoMorto").style.display="block";

//     xajax_atualizatabela_versoes();
// }

function tab(setorAso)
{
	myTabbar = new dhtmlXTabBar("my_tabbar");

	myTabbar.addTab("a10_", "Coordenadores");
	myTabbar.addTab("a20_", "Arquivo Morto");
	myTabbar.addTab("a30_", "Descarte");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a30_").attachObject("a30");
	
	myTabbar.tabs("a10_").setActive();

	if (setorAso != 17 && setorAso != 2)
	{
		myTabbar.tabs("a20_").hide();
		myTabbar.tabs("a30_").hide();
	}
	
	myTabbar.enableAutoReSize(true);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setImagePath("<?php echo INCLUDE_JS; ?>dhtmlx_403/codebase/imgs/");

	var colunaExtra = new Array('','','','','');
			
	if (tabela == 'div_lista_descartes')
	{
		colunaExtra[0] = ',Ano';
		colunaExtra[1] = ',50';
		colunaExtra[2] = ',center';
		colunaExtra[3] = ',ro';
		colunaExtra[4] = ',str';
	}
	
	switch(tabela)
	{
		case 'div_lista_versoes':
		case 'div_lista_descartes':
			mygrid.setHeader("Versão, Responsável, Data Registro"+colunaExtra[0]);
			mygrid.setInitWidths("70,*,70"+colunaExtra[1]);
			mygrid.setColAlign("right,left,left"+colunaExtra[2]);
			mygrid.setColTypes("ro,ro,ro"+colunaExtra[3]);
			mygrid.setColSorting("str,str,str"+colunaExtra[4]);
		break;
		
		case 'div_lista_os':
			var chkAll = '<input type="checkbox" id="chkTodos" style="margin:0;" onclick="mygrid.checkAll(this.checked);desbloquearBotaoEnviar();" />';
			
			mygrid.setHeader(chkAll+", OS");
			mygrid.setInitWidths("50,*");
			mygrid.setColAlign("left,left");
			mygrid.setColTypes("ch,ro");
			mygrid.setColSorting("str,str");

			mygrid.attachEvent("onCheck", function(rId,cInd,state){
				desbloquearBotaoEnviar();
			});
		break;
	}

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function desbloquearBotaoEnviar()
{
	if (mygrid.getCheckedRows(0) != "")
    {
		document.getElementById('btnenviar').disabled=false;
		document.getElementById('btnaprovar').disabled=false;
    }
	else
	{
		document.getElementById('btnenviar').disabled=true;
		document.getElementById('btnaprovar').disabled=true;
	}
	
	document.getElementById('selecionados').value = mygrid.getCheckedRows(0);
}
</script>
