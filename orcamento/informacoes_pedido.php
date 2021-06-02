<?php
/**
 *
 *		Formulario de Input de informacoes gerais do pedido
 *
 *		Criado por Carlos Eduardo  
 *
 *		local/Nome do arquivo:
 *		../orcamento/informacoes_pedido.php
 *
 *		data de criacao: 28/02/2018
 *
 *		Versao 0 --> VERSAO INICIAL - Carlos Eduardo
 */

/**
 * ATENCAO PROGRAMADOR
 * AO MUDAR QUALQUER CONTEUDO DESTE ARQUIVO, VERIFICAR SE O MESMO DEVE SER FEITO NO ARQUIVO contratos_controle/bms.php
 * POIS MUITAS FUNCOES DEVE ESTAR EXATAMENTE IGUAIS (APENAS XAJAX)
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

if(!verifica_sub_modulo(622))
{
    nao_permitido();
}

function atualizatabela($filtro = '', $status = '')
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql_filtro = "";
    
    $sql_texto = "";
    
    if($filtro!="")
    {
        $sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
        $sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
        
        $sql_filtro = " AND (AF1_DESCRI LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR AF1_ORCAME LIKE '".$sql_texto."' ) ";
    }

    /*

    $sql = "SELECT AF1_ORCAME, AF1_DESCRI, AF1_FASE, AEA_DESCRI
        	FROM AF1010 WITH(NOLOCK)
        	LEFT JOIN (
        		SELECT 
        			AF8_PROJET, AF8_FASE, AEA_DESCRI
        		FROM 
        			AF8010 WITH(NOLOCK) 
        			JOIN AEA010 ON AEA010.D_E_L_E_T_ = '' AND AEA_COD = AF8_FASE
        		WHERE 
        			AF8010.D_E_L_E_T_ = '') AF8010 ON AF8_PROJET = AF1_ORCAME
            WHERE D_E_L_E_T_ = '' AND AF1_DATA >= '20170101' AND AF1_FASE IN ('02','04','09','12') AND AF1_DTAPRO <> ''
            AND (AF8_PROJET IS NULL OR AF8_FASE IN('03','09','12')) ".$sql_filtro."
            ORDER BY AF1_DESCRI";
    
    $db->select($sql,'MSSQL',true);
    
    $dados = $db->array_select;
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
        
    //Todos os pedidos em aberto
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    $arrAuxInseridos = array();
    $sql = "SELECT os FROM ".DATABASE.".bms_pedidos_informacoes WHERE reg_del = 0";
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$arrAuxInseridos){
        $arrAuxInseridos[sprintf('%010d', $reg['os'])] = true;
    });
    
    foreach($dados as $reg)
    {
        
        $xml->startElement('row');
        $xml->writeAttribute('id', $reg["AF1_ORCAME"]);
        $xml->writeElement('cell', $reg["AF1_ORCAME"]);
        $xml->writeElement('cell', str_replace(array("'"), '', $reg["AF1_DESCRI"]));
        $xml->writeElement('cell', $reg['AEA_DESCRI']);
        
        if (key_exists($reg['AF1_ORCAME'], $arrAuxInseridos))
        {
            $xml->writeElement('cell', '<span class="icone icone-balao cursor" onclick=xajax_modal_informacoes_pedido('.$reg['AF1_ORCAME'].') />');
        }
        else
        {
            $xml->writeElement('cell', '<span class="icone icone-balao-opaco cursor" onclick=xajax_modal_informacoes_pedido('.$reg['AF1_ORCAME'].') />');
        }
        
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(true);
        
    $resposta->addScript("grid('div_grid', true, '415', '".$conteudo."');");
    $resposta->addScript("hideLoader();");

    */
    
    return $resposta;
}

function modal_informacoes_pedido($os)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $osInt = intval($os);
    
    $id_bms_pedidos_informacoes = $condPgto = $formaPgto = $recebimento = $medicao = $periodoMed = $respMed = $respNF = $obs = '';
    
    $sql = "SELECT * FROM ".DATABASE.".bms_pedidos_informacoes WHERE reg_del = 0 AND os = ".$osInt;
    $db->select($sql, 'MYSQL', true);
    
    if ($db->numero_registros > 0)
    {
        $id_bms_pedidos_informacoes = $db->array_select[0]['id_bms_pedidos_informacoes'];
        $condPgto = $db->array_select[0]['cond_pgto'];
        $formaPgto = $db->array_select[0]['forma_pgto'];
        $recebimento = $db->array_select[0]['recebimento'];
        $medicao = $db->array_select[0]['data_medicao'];
        $periodoMed = $db->array_select[0]['periodo_medicao'];
        $respMed = $db->array_select[0]['responsavel_medicao'];
        $respNF = $db->array_select[0]['responsavel_nf'];
        $obs = $db->array_select[0]['obs'];
    }
    else
    {
        //VERIFICANDO SE A TEM OS RAIZ E SE ESTA RAIZ TEM DADOS DE CONTRATO PARA BUSCAR
        $sql = "SELECT AF1_RAIZ, AF1_ORCAME FROM AF1010 WHERE D_E_L_E_T_ = '' AND AF1_ORCAME = '".sprintf('%010d', $os)."'";
        $db->select($sql, 'MSSQL', true);
        $osRaiz = trim($db->array_select[0]['AF1_RAIZ']);
        
        //Buscando dados do contrato da os ou da raiz
        $complOsRaiz = intval($osRaiz) > 0 ? ", (SELECT os FROM ".DATABASE.".bms_pedido WHERE reg_del = 0 AND os = '".$osRaiz."')" : '';
        $sql = "SELECT * FROM ".DATABASE.".bms_pedidos_informacoes WHERE reg_del = 0 AND os IN(".$osInt.$complOsRaiz.")";
        $db->select($sql, 'MYSQL', true);
        
        if ($db->numero_registros > 0)
        {
            $condPgto = $db->array_select[0]['cond_pgto'];
            $formaPgto = $db->array_select[0]['forma_pgto'];
            $recebimento = $db->array_select[0]['recebimento'];
            $medicao = $db->array_select[0]['data_medicao'];
            $periodoMed = $db->array_select[0]['periodo_medicao'];
            $respMed = $db->array_select[0]['responsavel_medicao'];
            $respNF = $db->array_select[0]['responsavel_nf'];
            $obs = $db->array_select[0]['obs'];
        }
    }
            
    $html = '<form id="frm_informacoes" method="post">'.
        '<input name="os" type="hidden" id="os" value="'.$os.'">'.
        '<input name="id_bms_pedidos_informacoes" type="hidden" id="id_bms_pedidos_informacoes" value="'.$id_bms_pedidos_informacoes.'">'.
        '<label class="labels" style="float:left;width:140px;">Condições de PGTO</label>'.
        '<input name="cond_pgto" class="caixa" type="text" placeholder="Condicoes de pgto 10DDL | 60DDL ..." id="cond_pgto" size="90" value="'.$condPgto.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Responsável Medição</label>'.
        '<input name="responsavel_medicao" class="caixa" style="text-transform:initial;" type="text" placeholder="Nome e/ou E-mail responsavel pela aprovacao do boletim de medicao" id="responsavel_medicao" size="90" value="'.$respMed.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Responsável NF</label>'.
        '<input name="responsavel_nf" class="caixa" style="text-transform:initial;" type="text" placeholder="Nome e/ou E-mail responsavel pelo recebimento da nota fiscal" id="responsavel_nf" size="90" value="'.$respNF.'" /><br />'.
        
        '<label class="labels" style="float:left;width:140px;">Forma de PGTO</label>'.
        '<input name="forma_pgto" class="caixa" type="text" placeholder="Digite uma forma de PGTO - 3X Mes - 10/20/30 ..." id="forma_pgto" size="90" value="'.$formaPgto.'" /><br />'.
        
        '<label for="recebimento" style="float:left;width:140px;" class="labels">data limite envio NF</label>'.
        '<input name="recebimento" type="text" class="caixa" placeholder="At&ecirc; 25/MÊs | At&ecirc; 30/MÊS ..." id="recebimento"  value="'.$recebimento.'" /><br />'.
        
        '<label for="data_medicao" style="float:left;width:140px;" class="labels">data de Medição</label>'.
        '<input name="data_medicao" placeholder="30/MÊs | 10/MÊS ..." type="text" class="caixa" id="data_medicao"  value="'.$medicao.'" /><br />'.
        
        '<label for="periodo_medicao" style="float:left;width:140px;" class="labels">Período de Medição</label>'.
        '<input name="periodo_medicao" type="text" class="caixa" placeholder="26 - 25 | 11 - 10 ..." id="periodo_medicao"  value="'.$periodoMed.'" /><br />'.
        
        '<label for="obs" style="float:left;width:140px;" class="labels">Outras informações</label>'.
        '<textarea id="obs" name="obs" class="caixa" cols="88" rows="7" placeholder="Outras informacoes importantes">'.$obs.'</textarea><br />'.
        
        '<input type="button" class="class_botao" onclick="xajax_salvar_informacoes_pedido(xajax.getFormValues(\'frm_informacoes\'));" value="SALVAR">'.
        '<input type="button" class="class_botao" onclick="xajax_modal_anexar_pedido('.$os.');" value="ANEXAR" />'.
        '</form>';
    
    $resposta->addScriptCall("modal",$html, '450_850', 'Informações sobre o pedido','1');
    
    return $resposta;
}

function modal_anexar_pedido($os)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    $conf = new configs();
    
    $sql = "SELECT arquivo_pedido, arquivo_proposta, arquivo_contrato, os FROM ".DATABASE.".bms_pedido ";
    $sql .= "WHERE bms_pedido.reg_del = 0 ";
    $sql .= "AND bms_pedido.id_os = ".$os;
    
    $db->select($sql, 'MYSQL', true);
    
    //Por enquanto, esta liberado
    $liberaExclusao = $conf->checa_permissao(2) ? 'display:block;' : 'display:block;';
    $temContrato = false;
    
    if ($db->numero_registros > 0)
    {
        $imgExcluir = '<button class="class_botao" %s style="margin-left:10px;'.$liberaExclusao.'"> <span class="icone icone-excluir icone-botao"></span>EXCLUIR</button>';
        
        if (!empty($db->array_select[0]['arquivo_pedido']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_pedido\');}"';
            $htmlPedido = "<fieldset style='margin:0;'><legend class='labels'>Pedido</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_pedido"]."');>VISUALIZAR PEDIDO</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
        }
        else
        {
            $htmlPedido = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Pedido</legend><form id="frm_pedido" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">N&deg; pedido</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="pedido" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_pedido").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
        
        if (!empty($db->array_select[0]['arquivo_proposta']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_proposta\');}"';
            $htmlProposta = "<fieldset style='margin:0;'><legend class='labels'>Proposta</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_proposta"]."');>VISUALIZAR PROPOSTA</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
        }
        else
        {
            $htmlProposta = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Proposta</legend><form id="frm_proposta" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">N&deg; proposta</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="proposta" />'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_proposta").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
        
        if (!empty($db->array_select[0]['arquivo_contrato']))
        {
            $acao = 'onclick="if(confirm(\'Deseja realmente excluir este arquivo?\')){xajax_excluir_arquivo('.$os.',\'arquivo_contrato\');}"';
            $htmlContrato = "<fieldset style='margin:0;'><legend class='labels'>Contrato</legend>".
                "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_contrato"]."');>VISUALIZAR CONTRATO</button>".
                sprintf($imgExcluir, $acao).
                "</fieldset>";
            
            $temContrato = true;
        }
        else
        {
            $htmlContrato = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Contrato</legend><form id="frm_contrato" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
                '<label class="labels" style="float:left;width:80px;">N&deg; contrato</label>'.
                '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
                '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="contrato" />'.
                '<input class="caixa" name="myfile" type="file" size="30" />'.
                '<button class="class_botao" onclick=document.getElementById("frm_contrato").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
                '<input name="os" type="hidden" id="os" value="'.$os.'">'.
                '</form></fieldset>';
        }
    }
    else
    {
        $htmlPedido = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Pedido</legend><form id="frm_pedido" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">N&deg; pedido</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="pedido" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_pedido").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
        
        $htmlContrato = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Contrato</legend><form id="frm_contrato" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">N&deg; contrato</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="contrato" />'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_contrato").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
        
        $htmlProposta = '<fieldset style="margin:0;padding-bottom:0;"><legend class="labels">Proposta</legend><form id="frm_proposta" enctype="multipart/form-data" action="../contratos_controle/upload_bms_pedido.php" target="upload_target" method="post">'.
            '<label class="labels" style="float:left;width:80px;">N&deg; proposta</label>'.
            '<input name="nome_arquivo" class="caixa" type="text" id="nome_arquivo">'.
            '<input name="tipo_arquivo" type="hidden" id="tipo_arquivo" value="proposta" />'.
            '<input class="caixa" name="myfile" type="file" size="30" />'.
            '<button class="class_botao" onclick=document.getElementById("frm_proposta").submit();><span class="icone icone-clips icone-botao"></span> ANEXAR</button>'.
            '<input name="os" type="hidden" id="os" value="'.$os.'">'.
            '</form></fieldset>';
    }
    
    //SE NAO TEM CONTRATO, VERIFICAR SE TEM RAIZ E VERIFICAR SE A OS RAIZ TEM CONTRATO
    if (!$temContrato)
    {
        //VERIFICANDO SE A TEM OS RAIZ E SE ESTA RAIZ TEM DADOS DE CONTRATO PARA BUSCAR
        $sql = "SELECT AF1_RAIZ, AF1_ORCAME FROM AF1010 WHERE D_E_L_E_T_ = '' AND AF1_ORCAME = '".sprintf('%010d', $db->array_select[0]['os'])."'";
        $db->select($sql, 'MSSQL', true);
        $osRaiz = trim($db->array_select[0]['AF1_RAIZ']);
    
        if (!empty($osRaiz))
        {
            $sql = "SELECT arquivo_pedido, arquivo_proposta, arquivo_contrato, os FROM ".DATABASE.".bms_pedido ";
            $sql .= "WHERE bms_pedido.reg_del = 0 AND arquivo_contrato IS NOT NULL ";
            $sql .= "AND bms_pedido.id_os = ".$osRaiz;
            
            $db->select($sql, 'MYSQL', true);
            
            if ($db->numero_registros > 0)
            {
                $htmlContrato = "<fieldset style='margin:0;'><legend class='labels'>Contrato</legend>".
                    "<button class='class_botao' style='width:250px;float:left;' onclick=window.open('../includes/documento.php?documento=".DOCUMENTOS_FINANCEIRO.'/pedidos/'.$db->array_select[0]["arquivo_contrato"]."');>VISUALIZAR CONTRATO RAIZ</button>".
                    "</fieldset>";
            }
        }
    }
    
    $html = '<iframe id="upload_target" name="upload_target" src="#" style="width:100%;height:100px;border:1px solid #000;display:none;"></iframe>';
    
    $html .= $htmlPedido.$htmlProposta.$htmlContrato;
    
    $resposta->addScriptCall("modal",$html, '250_800', 'Anexar arquivos ao pedido');
    
    return $resposta;
}

function salvar_informacoes_pedido($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    //Atualiza
    if (!empty($dados_form['id_bms_pedidos_informacoes']))
    {
        $virgula = '';
        $campos = '';
        $valores = '';
        $usql = 'UPDATE ".DATABASE.".bms_pedidos_informacoes SET ';
        foreach($dados_form as $campo => $valor)
        {
            if ($campo == 'id_bms_pedidos_informacoes')
            {
                $where = ' WHERE '.$campo.' = '.$valor;
                continue;
            }
            
            $usql .= $virgula.$campo." = '".maiusculas($valor)."'";
            $virgula = ', ';
        }
        $usql .= $where;
        $db->update($usql, 'MYSQL');
    }//Insere
    else
    {
        $virgula = '';
        $campos = '';
        $valores = '';
        $isql = '';
        foreach($dados_form as $campo => $valor)
        {
            if ($campo == 'id_bms_pedidos_informacoes')
                continue;
                
                $campos .= $virgula.$campo;
                $valores .= $virgula."'".maiusculas($valor)."'";
                $virgula = ', ';
        }
        $isql = "INSERT INTO ".DATABASE.".bms_pedidos_informacoes (".$campos.") VALUES (".$valores.")";
        $db->insert($isql, 'MYSQL');
        
        $resposta->addAssign('id_bms_pedidos_informacoes', 'value', $db->insert_id);
    }
    
    if($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar salvar o registro. '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Registro salvo corretamente.');
    }
    
    return $resposta;
}

function excluir_arquivo($os, $campo)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT ".$campo." as arquivo FROM ".DATABASE.".bms_pedido ";
    $sql .= "WHERE bms_pedido.reg_del = 0 ";
    $sql .= "AND bms_pedido.id_os = ".$os;
    
    $db->select($sql, 'MYSQL', true);
    
    if (HOST != 'localhost')
        $pasta = DOCUMENTOS_FINANCEIRO.'/pedidos/';
    else
        $pasta = ROOT_DIR.'/contratos_controle/pedidos/';
    
    if (is_file($pasta.$db->array_select[0]['arquivo']))
    {
        if (unlink($pasta.$db->array_select[0]['arquivo']))
        {
            $usql = "UPDATE ".DATABASE.".bms_pedido SET ".$campo." = '' ";
            $usql .= "WHERE bms_pedido.reg_del = 0 ";
            $usql .= "AND bms_pedido.id_os = ".$os;
            
            $db->update($usql, 'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert('Arquivo Excluido parcialmente! Houve uma falha no registro do banco de dados!');
            }
            else
            {
                $resposta->addAlert('Arquivo Excluído corretamente!');
                $resposta->addScript('divPopupInst.destroi();');
                $resposta->addScriptCall('xajax_modal_anexar_pedido', $os);
                //$resposta->addScript('xajax_atualizatabela();');
            }
        }
        else
        {
            $resposta->addAlert('Houve uma falha ao tentar excluir o arquivo!');
        }
    }
    else
    {
        $usql = "UPDATE ".DATABASE.".bms_pedido SET ".$campo." = '' ";
        $usql .= "WHERE bms_pedido.reg_del = 0 ";
        $usql .= "AND bms_pedido.id_os = ".$os;
        
        $db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha no registro do banco de dados!');
        }
        else
        {
            $resposta->addAlert('Arquivo Excluido corretamente!');
            $resposta->addScript('divPopupInst.destroi();');
            $resposta->addScriptCall('xajax_modal_anexar_pedido', $os);
            //$resposta->addScript('xajax_atualizatabela();');
        }
    }
    
    return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("modal_informacoes_pedido");
$xajax->registerFunction("modal_anexar_pedido");
$xajax->registerFunction("salvar_informacoes_pedido");
$xajax->registerFunction("excluir_arquivo");

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
	
	mygrid.setHeader("Pedido, descricao, situacao, I");
	mygrid.setInitWidths("100,*,200,30");
	mygrid.setColAlign("left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");
	mygrid.enableMultiline(true);

	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

var iniciaBusca2=
{
	buffer: false,
	tempo: 1000, 

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		showLoader();
		xajax_atualizatabela(valor);	
	}
}
</script>

<?php
$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE fase_protheus <> '00' ";
$sql .= "AND reg_del = 0 ";
$sql .= "AND id_os_status IN(1,2,7,14,15,16) ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
    exit("Não foi possível realizar a seleção.".$sql);
}

$array_status_os_values[] = '';
$array_status_os_output[] = 'SELECIONE';

foreach($db->array_select as $regs)
{
    $array_status_os_values[] = $regs["id_os_status"];
    $array_status_os_output[] = $regs["os_status"];
}

$smarty->assign("option_status_os_values",$array_status_os_values);
$smarty->assign("option_status_os_output",$array_status_os_output);

$smarty->assign("classe",CSS_FILE);
$smarty->assign("revisao_documento","V0");
$smarty->assign("larguraTotal",true);
$smarty->assign("campo",$conf->campos('informacoespedido'));
$smarty->assign("botao",$conf->botoes());

$smarty->display('informacoes_pedido.tpl');
?>