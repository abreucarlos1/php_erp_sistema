<?php 
/*
	  Formulário de Reporte
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../qualidade/formulario_reporte.php
	  
	  Versão 0 --> VERSÃO INICIAL - 24/07/2015
	  Versão 1 --> atualização layout - Carlos Abreu - 03/04/2017
	  Versão 2 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(583))
{
	nao_permitido();
}

//colaboradores sgi
function permit_colab_sgi()
{
	$array_sgi = NULL;

	//$array_sgi = array(6=>6,978=>978,871=>871,576=>576,1142=>1142);
		
	return $array_sgi;	
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('nao_conformidades_internas',$resposta);
	
	switch($dados_form["filtro"])
	{
		//geral
		case 0:
			$filtro = "";
		break;
		
		//pendentes
		case 1:
			$filtro = "AND nao_conformidades.status = 0 ";
		break;
		
		//em analise
		case 2:
			$filtro1 = "AND nao_conformidades.data_criacao >= '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
			$filtro = "AND nao_conformidades.status = 1 ";
		break;
		
		//atrasados
		case 3:
			$filtro1 = "AND nao_conformidades.data_criacao < '".php_mysql(calcula_data(date('d/m/Y'),'sub','day',15))."' ";
			$filtro = "AND nao_conformidades.status = 1 ";
		break;
		
		//encerrados
		case 4:
			$filtro = "AND nao_conformidades.status = 2 ";
		break;		
	}
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".nao_conformidades ";
	$sql .= "LEFT JOIN ".DATABASE.".ordem_servico ON (nao_conformidades.id_os = ordem_servico.id_os) ";
	$sql .= "JOIN (SELECT * FROM ".DATABASE.".tipo_origem) tpo ON tpo.id_tipo_origem = nao_conformidades.id_tipo_origem ";
	$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
	$sql .= "AND nao_conformidades.id_funcionario_criador = funcionarios.id_funcionario ";
	
	$sql .= $filtro;
	$sql .= $filtro1;
	$sql .= "ORDER BY nao_conformidades.data_criacao ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$array_nc = $db->array_select;
	
	foreach($array_nc as $regs)
	{	
		$title = "";
		
		if($regs["status"]==0)//pendente
		{
			if ($regs["data_criacao"]>=php_mysql(calcula_data(date('d/m/Y'),'sub','day',15)))
			{
				//atrasadas
				//led am
				$img = '<img style="cursor:pointer;" title="ATRASADA" src="'.DIR_IMAGENS.'led_am.png">';
				$title = "ATRASADA";
			}
			else 
			{
				//led vm
				$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'led_vm.png">';
				$title = "PENDENTE";
			}
		}
		else
		{
			if($regs["status"]==2)//encerrada
			{
				//led vm
				$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'led_az.png">';
				$title = "ENCERRADA";
			}
			else
			{	
				//em analise				
				if($regs["status"]==1)
				{
					//led vd
					$img = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'led_vd.png">';
					$title = "EM ANDAMENTO";
				}
			}
		}		
		
		$sql = "SELECT * FROM ".DATABASE.".setores  ";
		$sql .= "WHERE setores.id_setor = '".$regs["id_disciplina"]."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];
		
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade  ";
		$sql .= "WHERE empresas.id_empresa_erp = '".$regs["id_cliente"]."' ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs3 = $db->array_select[0];
		
		switch ($regs["procedente"])
		{
			case 1: 
				$procedente = "SIM";
			break; 
			
			case 2: 
				$procedente = "NAO";
			break;
			
			default: $procedente = "";	
		}
		
		//permite excluir
		if(($regs["envio_email"]==0 && $regs["status"]==0) || (in_array($_SESSION["id_funcionario"],permit_colab_sgi())))
		{
			$img_del = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick = if(confirm("Deseja&nbsp;apagar&nbsp;este&nbsp;registro?")){xajax_excluir("'.$regs["id_nao_conformidade"].'","'.$regs["cod_nao_conformidade"].'");}>';
		}
		else
		{
			$img_del = "&nbsp;";
		}		
		
		if($regs["os"]!=0)
		{
			$os = sprintf("%010d",$regs["os"]);
		}
		else
		{
			$os = "NAO APLICÁVEL";	
		}
		
		$salvoEnviado = $regs['envio_email'] > 0 ? 'ENVIADO' : 'SALVO';
		
		$origem = wordwrap($regs['tipo_origem'].': '.$regs['desc_outros'].$regs['desc_outros_cliente'].$regs['desc_outros_fornec'], 20, '<br />\n');
				
		$xml->startElement('row');
		    $xml->writeAttribute('id','nc_'.$regs["id_nao_conformidade"]);
			$xml->writeElement ('cell',$regs["cod_nao_conformidade"]);
			$xml->writeElement ('cell',mysql_php($regs["data_criacao"]));
			$xml->writeElement ('cell',$os);
			
			$xml->writeElement ('cell',$origem);
			$xml->writeElement ('cell',$regs1["setor"]);
			$xml->writeElement ('cell',$regs3["empresa"]." - ".$regs3["descricao"]);
			$xml->startElement('cell');
				$xml->writeAttribute('title',$title);
				$xml->text($img);
			$xml->endElement();
			$xml->writeElement ('cell',$salvoEnviado);
			$xml->writeElement ('cell',$procedente);
			$xml->writeElement ('cell','<img style="cursor:pointer;" src="'.DIR_IMAGENS.'impressora.png" onclick=imprimir_rnc('.$regs["id_nao_conformidade"].');>');
			$xml->writeElement ('cell',$img_del);
		$xml->endElement();	
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('dv_rotinas',true,'550','".$conteudo."');");

	return $resposta;
}

function WordTruncate($input, $numWords) 
{
	if(str_word_count($input,0)>$numWords)
	{
		$WordKey = str_word_count($input,1);
		$WordIndex = array_flip(str_word_count($input,2));
		return substr($input,0,$WordIndex[$WordKey[$numWords]]);
	}
	else 
	{
		return $input;
	}
} 

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$temp = explode('_',$id);
	
	$id = $temp[1];

	$resposta->addScript("insere_nc(".$id.")");
	$resposta->addScript("xajax_voltar();");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_RNC/";
	
	$erro = false;
				
	$usql = "UPDATE ".DATABASE.".nao_conformidades SET ";
	$usql .= "nao_conformidades.nao_conformidade_delete = 1, ";
	$usql .= "nao_conformidade_delete_who = '".$_SESSION["id_funcionario"]."' ";
	$usql .= "WHERE nao_conformidades.id_nao_conformidade = '".$id."' ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		$erro = true;
	}
	else
	{
		$sql = "SELECT * FROM ".DATABASE.".nao_conformidades_anexos ";
		$sql .= "WHERE nao_conformidades_anexos.id_nao_conformidade = '".$id."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}
		else
		{				
			foreach($db->array_select as $regs)
			{
				$del = unlink($diretorio.$regs["anexo"]);
				
				if(!$del)
				{
					$erro = true;	
				}					
				
				$usql = "UPDATE ".DATABASE.".nao_conformidades_anexos SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
				$usql .= "WHERE id_nao_conformidade_anexo = '".$regs["id_nao_conformidade_anexo"]."' ";
				
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					$erro = true;
				}
			}
		}
	}
	
	if($erro)
	{
		$resposta->addAlert('Erro ao excluir o registro');
	}
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	$resposta->addScript("xajax_voltar();");

	return $resposta;
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addEvent("btnvoltar", "onclick", "location.href='../../'");

	return $resposta;
}	

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("voltar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function anexos(id_nc)
{
	document.getElementById('id').value = id_nc;

	document.getElementById('frm').submit();		
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected1(row,col)
	{
		if(col<=5)
		{
			xajax_editar(row);
  
			return true;
		}
	}
	
	mygrid.attachEvent("onRowSelect",doOnRowSelected1);	
	
	//mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Código,Data,OS,Origem,Disciplina,Cliente,Status,Enviado/Salvo,Procedente,I,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);

	mygrid.setInitWidths("110,100,100,170,250,*,70,150,100,35,35");
	mygrid.setColAlign("center,left,left,left,left,left,left,left,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function insere_nc(id)
{
	document.getElementById('frm').action = './nao_conformidades_internas.php?id='+id;
	document.getElementById('frm').submit();	
}

function imprimir()
{
	document.getElementById('frm').action = './relatorios/rel_nc_excel.php';
	document.getElementById('frm').target = '_blank';
	document.getElementById('frm').submit();	
}

function imprimir_rnc(id_rnc)
{
	window.open('relatorios/rel_rnc.php?id_rnc='+id_rnc, '_blank');
}

function open_file(documento,path)
{
	window.open("../includes/documento.php?documento="+documento+"&caminho="+path,"_blank");	
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V2");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('formulario_reporte'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","FORMULARIO DE REPORTE");

$smarty->assign("classe",CSS_FILE);

$smarty->display('formulario_reporte.tpl');

?>
