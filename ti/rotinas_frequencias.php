<?php 
/*
		Formulário de rotinas x frequencias
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		
		Versão 0 --> VERSÃO INICIAL - 21/02/2014
		Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - Carlos Abreu - 13/11/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		Versão 4 --> Layout responsivo - 06/02/2018 - Carlos Eduardo
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(320))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;

}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('ti_rotinas_frequencias',$resposta);
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_frequencias, ".DATABASE.".ti_rotinas, ".DATABASE.".ti_frequencias ";
	$sql .= "WHERE ti_rotinas_frequencias.reg_del = 0 ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";
	$sql .= "AND ti_frequencias.reg_del = 0 ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_rotina = ti_rotinas.id_ti_rotina ";
	$sql .= "AND ti_rotinas_frequencias.id_ti_frequencia = ti_frequencias.id_ti_frequencia ";
	$sql .= "ORDER BY ti_rotina ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$xml->startElement('row');
		    $xml->writeAttribute('id',$regs["id_ti_rotinas_frequencia"]);
			
			$xml->startElement('cell');
				$xml->text($regs["ti_rotina"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["ti_frequencia"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$regs["ti_rotina"].'")){xajax_excluir("'.$regs["id_ti_rotinas_frequencia"].'","'.$regs["ti_rotina"] . '");}>');
			$xml->endElement();
		
		$xml->endElement();	
		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('frequencias',true,'420','".$conteudo."');");
	
	$resposta->addScript("xajax_rotinas_frequencia(xajax.getFormValues('frm'));");

	return $resposta;
}

function insere($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		if($dados_form["frequencia"]!='' && $dados_form["rotina"]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".ti_rotinas_frequencias ";
			$sql .= "WHERE ti_rotinas_frequencias.id_ti_frequencia = '".$dados_form["frequencia"]."' ";
			$sql .= "AND ti_rotinas_frequencias.id_ti_rotina = '".$dados_form["rotina"]."' ";
			$sql .= "AND ti_rotinas_frequencias.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".ti_rotinas_frequencias ";
				$isql .= "(id_ti_rotina, id_ti_frequencia) ";
				$isql .= "VALUES ('" . $dados_form["rotina"] . "', ";
				$isql .= "'" . $dados_form["frequencia"] . "') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addScript('xajax_voltar();');
			
				$resposta->addAlert($msg[1]);
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}
	
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
			
	}	

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(8,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".ti_rotinas_frequencias SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE ti_rotinas_frequencias.id_ti_rotinas_frequencia = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$resposta->addAlert($what . $msg[3]);	
		}

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}

	return $resposta;
}

function rotinas_frequencia($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;	
	
	$resposta->addAssign("dv_rotina","innerHTML","");

	$resposta->addAssign("dv_frequencia","innerHTML","");
		
	//monta o combo hora inicial
	$comboi = '<select name="rotina" class="caixa" id="rotina">';
	
	$resposta->addAssign("dv_rotina","innerHTML",$comboi);
	
	//Percorre a tabela de rotinas
	$sql = "SELECT * FROM ".DATABASE.".ti_rotinas ";	
	$sql .= "WHERE ti_rotinas.id_ti_rotina NOT IN "; 
	$sql .= "(SELECT id_ti_rotina FROM ".DATABASE.".ti_rotinas_frequencias WHERE ti_rotinas_frequencias.reg_del = 0) ";
	$sql .= "AND ti_rotinas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $cont)
	{
		$comboi .= '<option value="'.$cont["id_ti_rotina"].'">'.$cont["ti_rotina"].'</option>';		
	}
	
	$comboi .= '</select>';

	$combof = '<select name="frequencia" class="caixa" id="frequencia">';

	//Percorre a tabela de frequencias
	$sql = "SELECT * FROM ".DATABASE.".ti_frequencias ";
	$sql .= "WHERE reg_del = 0 ";	

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $cont)
	{
		$combof .= '<option value="'.$cont["id_ti_frequencia"].'">'.$cont["ti_frequencia"].'</option>';		
	}	
					
	$combof .= '</select>';
	
	$resposta->addAssign("dv_rotina","innerHTML",$comboi);
	
	$resposta->addAssign("dv_frequencia","innerHTML",$combof);
		
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("rotinas_frequencia");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

//captura a tecla enter
document.onkeypress = keyhandler;

function keyhandler(e) 
{
	if (document.layers)
		Key = e.which;
	else
		Key = window.event.keyCode;

	if (Key != 0)
		if (Key == 13)
			//alert('Enter key press');
			xajax_insere(xajax.getFormValues('frm'));
}


function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Rotina,Frequência,D",
		null,
		["text-align:left","text-align:left","text-align:center"]);
	mygrid.setInitWidths("*,100,25");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	//mygrid.enableSmartRendering(true,32);
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('ti_rotinas_frequencias'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","ROTINAS X FREQUÊNCIA");

$smarty->assign("classe",CSS_FILE);

$smarty->display('rotinas_frequencias.tpl');

?>