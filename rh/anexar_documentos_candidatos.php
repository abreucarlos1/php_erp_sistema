<?php
/*
	Formulário de Anexar documentos de candidatos aprovados	
	
	Criado por Carlos Eduardo Máximo
	
		local/Nome do arquivo:
		../rh/anexar_documentos_candidatos.php
		
	Versão 0 --> VERSÃO INICIAL : 18/04/2016
	Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

function atualizatabela($idContrato)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $sql = "SELECT mod_contrato FROM ".DATABASE.".candidatos ";
	$sql .= "WHERE candidatos.reg_del = 0 "; 
	$sql .= "AND id = ".$idContrato;
   
    $db->select($sql, 'MYSQL', true);
    
    switch ($db->array_select[0]['mod_contrato'])
    {
        case 'EST':
            $tipoFunc = 'ES';
        break;
        case 'CLT':
            $tipoFunc = 'PF';        
        break;
        
        default:
            $tipoFunc = 'PJ';
        break;
    }
    
    $sql = "SELECT
            	ctd_descricao, caq_arquivo, null as ano, null as nome, null as caq_id, ctd_id
            FROM
				".DATABASE.".candidatos_tipos_documentos a
                LEFT JOIN ".DATABASE.".candidatos_arquivos b ON b.caq_ctd_id = a.ctd_id AND b.reg_del = 0 AND caq_candidato_id = ".$idContrato."
            WHERE
            	a.ctd_tipo_funcionario = '".$tipoFunc."'
                AND a.reg_del = 0
                AND b.caq_ctd_id IS NULL
            
            UNION ALL
            
            SELECT
            	CASE WHEN ctd_descricao IS NULL THEN caq_arquivo ELSE ctd_descricao END ctd_descricao, caq_arquivo, ano, nome, caq_id, ctd_id
            FROM
				".DATABASE.".candidatos_arquivos
                LEFT JOIN ".DATABASE.".candidatos_tipos_documentos ON ctd_id = caq_ctd_id AND candidatos_tipos_documentos.reg_del = 0
                JOIN(
            		SELECT nome, year(data_inicio) ano, id FROM ".DATABASE.".candidatos WHERE candidatos.reg_del = 0
            	) can
            	ON id = caq_candidato_id
            WHERE
            	caq_candidato_id = ".$idContrato."
				AND candidatos_arquivos.reg_del = 0                
            ORDER BY
            	ctd_descricao";
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$xml, &$idContrato){
        $xml->startElement('row');
        $xml->writeAttribute('id', $reg["ctd_id"]);
        if (!empty($reg['ano']))
        {
            $pastaCandidato = $reg['ano'].'/'.strtoupper(tiraacentos($reg['nome']));
            $conteudo = "<span class=\'cursor\' onclick=\'window.parent.open(\"../includes/documento.php?documento=".$pastaCandidato.'/'.$reg['caq_arquivo']."&caminho=DOCUMENTOS_CANDIDATOS&janela=yes\",\"_blank\")\'><font style=\'text-decoration:underline;\'>".$reg['ctd_descricao']."</font></span>";
            $xml->writeElement('cell', $conteudo);
            
            $conteudo = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;documento?\')){window.location=\'./anexar_documentos_candidatos.php?excluir=1&idDoc=".$reg['caq_id']."&id=".$idContrato."\';}></span>";
            $xml->writeElement('cell', $conteudo);
        }
        
        if (empty($reg['ano']))
        {
            $xml->writeElement('cell', $reg["ctd_descricao"]);
            
            $conteudo ='<form style="margin:0;" name="frm_'.$reg['ctd_id'].'" id="frm_'.$reg['ctd_id'].'" action="candidato_upload.php" target="upload_target_'.$reg['ctd_id'].'" method="post" enctype="multipart/form-data" >';
            $conteudo .='<iframe id="upload_target_'.$reg['ctd_id'].'" name="upload_target_'.$reg['ctd_id'].'" src="#" style="border:0px solid #fff;display:none;"></iframe>';
            $conteudo .='<span id="txtup_'.$reg['ctd_id'].'" >';
            $conteudo .='<input class="caixa" onchange=document.getElementById("frm_'.$reg['ctd_id'].'").submit(); name="myfile_'.$reg['ctd_id'].'" type="file" size="30" style="width: 60%;" />&nbsp;&nbsp;';
            $conteudo .='</span>';
            $conteudo .='<input name="anexo_candidato_id" type="hidden" id="anexo_candidato_id" value="'.$idContrato.'">';
            $conteudo .='<input name="tipo_documento" type="hidden" id="tipo_documento" value="'.$reg["ctd_id"].'">';
            $conteudo .='</form>';
            $xml->writeElement('cell', $conteudo);
        }
        
        
        $xml->endElement();        
    });
        
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(true);
    
    $resposta->addScript("grid('gridArquivos', true, '415', '".$conteudo."');");
    
    return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

//Exclusão de documentos
if (isset($_GET['idDoc']) && !empty($_GET['idDoc']))
{
	$sql = "SELECT * FROM ".DATABASE.".candidatos_arquivos ";
	$sql .= "WHERE candidatos_arquivos.reg_del = 0 ";
	$sql .= "AND caq_id = ".$_GET['idDoc'];
	
	$db->select($sql, 'MYSQL', true);
	
	$array_files = $db->array_select;
	
	foreach($array_files as $reg)
	{
		$arquivo = DOCUMENTOS_RH.'documentos_candidatos/'.$reg['caq_arquivo'];
		
		if (file_exists($arquivo))
		{
			unlink($arquivo);
		}
	}
	
	$usql = "UPDATE ".DATABASE.".candidatos_arquivos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE caq_id = ".$_GET['idDoc']." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$smarty->assign('erros', 'Houve uma falha ao tentar excluir o documento.');
	}
	else
	{
		$smarty->assign('erros', 'Documento excluído corretamente!');
	}
}

$smarty->assign("body_onload","xajax_atualizatabela(".$_GET['id'].");");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.setHeader("Documento, &nbsp;");
	mygrid.setInitWidths("*,*");
	mygrid.setColAlign("left,left");
	mygrid.setColTypes("ro,ro");
	mygrid.setColSorting("str,str");

	mygrid.enableMultiline(true);

	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$html = '<table class="table auto_lista">
			<tr>
				<th><label class="labels">Arquivo</label></th>
				<th><label class="labels">Excluir</label></th>
			</tr>';
			
$sql = "SELECT * FROM ".DATABASE.".candidatos_arquivos
JOIN(
	SELECT nome, year(data_inicio) ano, id FROM ".DATABASE.".candidatos WHERE id = ".$_GET['id']." AND reg_del = 0
) can
ON id = caq_candidato_id
 WHERE reg_del = 0 
 AND caq_candidato_id = ".$_GET['id'];

$db->select($sql, 'MYSQL',true);

$array_regs = $db->array_select;

foreach($array_regs as $reg)
{
	$pastaCandidato = $reg['ano'].'/'.strtoupper(tiraacentos($reg['nome']));
	
	$html .= "<tr>".
				"<td style='cursor:pointer' onclick='window.parent.open(\"../includes/documento.php?documento=".$pastaCandidato.'/'.$reg['caq_arquivo']."&caminho=DOCUMENTOS_CANDIDATOS&janela=yes\", \"_blank\")'>".$reg['caq_arquivo']."</td>".
				"<td><img style='cursor:pointer;' onclick=window.location='./anexar_documentos_candidatos.php?excluir=1&idDoc=".$reg['caq_id']."&id=".$_GET['id']."'; src='".DIR_IMAGENS."apagar.png' /></td>".
			 "</tr>";
}

$smarty->assign('html', $html);

$smarty->assign('id', $_GET['id']);

$smarty->assign('ocultarCabecalhoRodape', 'style="display:none;"');

$smarty->assign("revisao_documento","V2");

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("anexar_documentos_candidato.tpl");

?>