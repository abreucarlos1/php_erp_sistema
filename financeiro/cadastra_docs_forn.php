<?php
/*
		Formulário de Cadastro de DOCUMENTOS DE FORNECEDORES	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/cadastra_docs_forn.php
		
		Versão 0 --> VERSÃO INICIAL : 03/01/2012
		Versão 1 --> ALTERAÇÃO A PEDIDO DO ADMINISTRATIVO MUDANÇAS DIVERSAS MEDIANTE TAP : 08/01/2015
		Versão 2 --> alteração do caminho para gravação dos arquivos - Carlos Abreu - 06/07/2016
		Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$conf = new configs();

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
	$sql .= "WHERE fechamento_folha.id_fechamento = '".$_GET["id_fechamento"]."' ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		exit($db->erro);
	}
	
	$cont0 = $db->array_select[0];
	
	if ($db->numero_registros == 0)
	{
		$resposta->addAlert('Não foram encontrados dados para montagem da lista!');
	}
	else
	{
		//Trazendo os documentos que o colaborador terá que cadastrar
		$sql = "SELECT *
				FROM
				".DATABASE.".fechamento_tipos_tributos a
				LEFT JOIN (
				  SELECT id_fechamento_docs, competencia, id_fechamento_tipos_tributos as tipos, conferido, documento, excessao, permite_anexos, reg_del as deletado
				  FROM ".DATABASE.".fechamento_documentos
				  JOIN (
				  	SELECT id_fechamento as fechamento, excessao, permite_anexos FROM ".DATABASE.".fechamento_folha
				  	WHERE fechamento_folha.id_fechamento = '".$_GET["id_fechamento"]."'
				  	AND fechamento_folha.reg_del = 0
				  ) fechamento
				  ON fechamento = id_fechamento
				  WHERE id_fechamento = '".$_GET["id_fechamento"]."'
				) docs
				ON docs.tipos = id_fechamento_tipos_tributos AND docs.deletado = 0 
				WHERE id_fechamento_tipos_tributos not in(11)
				AND reg_del = 0
				AND tipo_empresa IN(".$cont0['tipo_empresa'].", 0) 
				ORDER BY ordem";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert($db->erro);
		}
		
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(false);
		
		$xml->startElement('rows');
		
		$i = 0;
		
		$excessao = 0;
		
		foreach($db->array_select as $reg)
		{
			$xml->startElement('row');
			$xml->writeElement('cell', $reg['fechamento_tipos_tributos']);
			
			if (trim($reg['id_fechamento_docs']) == '')
			{
				$conteudo ='<form style="margin:0;" name="frm_'.$reg['id_fechamento_tipos_tributos'].'" id="frm_'.$reg['id_fechamento_tipos_tributos'].'" action="upload.php" target="upload_target_'.$reg['id_fechamento_tipos_tributos'].'" method="post" enctype="multipart/form-data" >';						
				$conteudo .='<iframe id="upload_target_'.$reg['id_fechamento_tipos_tributos'].'" name="upload_target_'.$reg['id_fechamento_tipos_tributos'].'" src="#" style="border:0px solid #fff;display:none;"></iframe>';
				$conteudo .='<span id="txtup_'.$reg['id_fechamento_tipos_tributos'].'" >';
				$conteudo .='<input class="caixa" onchange=document.getElementById("frm_'.$reg['id_fechamento_tipos_tributos'].'").submit(); name="myfile_'.$reg['id_fechamento_tipos_tributos'].'" type="file" size="30" style="width: 60%;" />&nbsp;&nbsp;';
				$conteudo .='</span>';
				$conteudo .='<input name="id_fechamento" type="hidden" id="id_fechamento" value="'.$_GET["id_fechamento"].'">';
				$conteudo .='<input name="tipo_tributo" type="hidden" id="tipo_tributo" value="'.$reg["id_fechamento_tipos_tributos"].'">';		
				$conteudo .='</form>';
				
				$xml->writeElement('cell', $conteudo);
				$xml->writeElement('cell', '&nbsp;');
			}
			else
			{
				$xml->writeElement('cell', '<a href="../includes/documento.php?documento='.DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$reg["documento"].'">VISUALIZAR</a>');
				
				if(intval($reg["conferido"])==0 || $reg["conferido"]==2)
				{
					if($reg["conferido"]==2)
					{
						$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'impressora.png" />');
					}
					else
					{
						$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'bt_relogio.png" />');
					}
					
					$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;documento?")){xajax_excluir("'.$reg['id_fechamento_docs'].'");}; />');
				}
				else
				{
					$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'bt_relogio.png" />');
					$xml->writeElement('cell', '&nbsp;');
				}
			}
			
			$xml->endElement();
			
			$i++;
		}
		
		$xml->endElement();
		
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('documentos', true, '510', '".$conteudo."');");
	}
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();	
		
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
	$sql .= "WHERE fechamento_documentos.id_fechamento_docs = '" . $id . "' ";
	$sql .= "AND fechamento_documentos.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		exit("Não foi possível fazer a seleção. ".$db->erro);
	}
	
	$cont = $db->array_select[0];
	
	if(unlink(DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$cont["documento"]) || !file_exists(DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$cont["documento"]))
	{

		$usql = "UPDATE ".DATABASE.".fechamento_documentos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE fechamento_documentos.id_fechamento_docs = '".$id."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			exit($db->erro);
		}
		
		$resposta->addAlert("Registro excluído com sucesso.");
		
		$resposta->addScript('window.location = "./cadastra_docs_forn.php?id_fechamento='.$cont["id_fechamento"].'"');
	}
	else
	{
		$resposta->addAlert("Erro ao excluir o documento.");	
	}

	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
var items = 0;

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader('Documento,Upload,Situação,D');
	mygrid.setInitWidths("*,*,100,100");
	mygrid.setColAlign("left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function add_campo()
{
	//exemplo de adicionar campos dinâmicos á um formulário
   //incrementa o indice do campo	
   items++;

   //cria o elemento combobox		
   var combo_box = document.createElement('select');	
   
   //percorre o formulário para encontrar os elementos		
   for ( i=0; i < document.forms['frm'].elements.length; i++) 
   { 
	  //caso seja um select
	  if (document.forms['frm'].elements[i].type == 'select-one')
	  {		
		//seta as propriedades do combobox
		combo_box.name = 'tipo_tributo_'+items;
		combo_box.className = 'caixa';
		combo_box.onkeypress = 'return keySort(this);';
		
		//percorre os elementos do combobox inicial (que esta no formulário)
		for(j=0;j<document.getElementById('frm').elements.item(i).length;j++)
		{
			//atribui o item e suas propriedades
			var choice = document.createElement('option');
			choice.value = document.getElementById('tipo_tributo_0').options[j].value;
			choice.appendChild(document.createTextNode(document.getElementById('tipo_tributo_0').options[j].text));
			
			//apenda ao combo criado
			combo_box.appendChild(choice);
		}
		
		//obtem a qtd de itens do combo
		qtd_itens = document.getElementById('frm').elements.item(i).length;
		
		break;
	  }
   } 
   
   //verifica se a qtd itens é menor que a quantidade do combo
   if(items<(qtd_itens-1))
   {
		//atribui o campo file
		texto = "<input name='documento_"+items+"' type='file' class='caixa' id='documento_"+items+"' />";
		
		//cria os elementos
		var txt1 = document.createElement('&nbsp');
		var txt2 = document.createElement(texto);
		var txt3 = document.createElement('<br>');
		
		//atribui ao div alvo
		document.getElementById('arquivos').appendChild(combo_box);
		document.getElementById('arquivos').appendChild(txt1);
		document.getElementById('arquivos').appendChild(txt2);
		document.getElementById('arquivos').appendChild(txt3);
		
		//atribui ao hidden o numero atual do item
		document.frm.itens.value = document.frm.itens.value = items;
   }
   else
   {
		alert('Não pode inserir mais itens.');   
   }
}
</script>

<?php
if (isset($_GET['op']) && $_GET['op'] == 'inserir')
{
	if (insere($_POST))
	{
		//Por algum motivo, a linha abaixo não está funcionando nos servidores Linux, apenas no localhost (windows)
		//header('Location: '.$_SERVER['HTTP_REFERER']);
		echo '<script>
			alert("Registro inserido com sucesso");
			window.location = "'.$_SERVER['HTTP_REFERER'].'";
			</script>
		';
	}
	else
	{
		echo '<script>
			alert("Houve uma falha ao tentar inserir o registro.");
			window.location = "'.$_SERVER['HTTP_REFERER'].'";
			</script>
		';
	}
}

$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
$sql .= "WHERE fechamento_folha.id_fechamento = '".$_GET["id_fechamento"]."' ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit($db->erro);
}

$cont0 = $db->array_select[0];

//Pegando os últimos 3 fechamentos do colaborador
$sql = "SELECT * FROM ".DATABASE.".fechamento_folha ";
$sql .= "WHERE id_funcionario = '".$cont0["id_funcionario"]."' ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "ORDER BY fechamento_folha.data_ini DESC, fechamento_folha.data_fim DESC LIMIT 0, 8 ";

$db->select($sql, 'MYSQL',true);

$array_tributo_values[] = "";
$array_tributo_output[] = "SELECIONE";

$arrayCompetencias_label = array();
$arrayCompetencias_value = array();

if ($db->erro != '')
{
	exit($db->erro);
}
else
{
	foreach ($db->array_select as $reg)
	{
		$mesAno = explode(',', $reg['periodo']);
		$mesAno = explode('-', $mesAno[0]);
		$arrayCompetencias_label[] = $mesAno[1].'/'.$mesAno[0];
		$arrayCompetencias_value[] = $reg['id_fechamento'];
	}
}

$arrayCompetenciasDesc = array_reverse($arrayCompetencias_value);
$arrayMesDesc = array_reverse($arrayCompetencias_label);

$fechamentoAnterior = $arrayCompetenciasDesc[count($arrayCompetenciasDesc)-2];
$competenciaAnterior = $arrayMesDesc[count($arrayMesDesc)-2];

//Procurando pelo mês anterior para ver se o colaborador não entregou algum documento
$sql = "SELECT count(*) docsFaltando
		FROM
		".DATABASE.".fechamento_tipos_tributos a
		LEFT JOIN (
		  SELECT id_fechamento_docs, competencia, id_fechamento_tipos_tributos as tipos, conferido, documento, excessao, permite_anexos, reg_del as deletado
		  FROM ".DATABASE.".fechamento_documentos 
		  JOIN (
		  	SELECT id_fechamento as fechamento, excessao, permite_anexos FROM ".DATABASE.".fechamento_folha
		  	WHERE fechamento_folha.id_fechamento = '".$fechamentoAnterior."'
		  	AND fechamento_folha.reg_del = 0
		  ) fechamento
		  ON fechamento = id_fechamento
		  WHERE id_fechamento = '".$fechamentoAnterior."'
		) docs
		ON docs.tipos = id_fechamento_tipos_tributos AND docs.deletado = 0 
		WHERE id_fechamento_tipos_tributos not in(11)
		AND reg_del = 0 
		AND tipo_empresa IN(".$cont0['tipo_empresa'].", 0)
		AND id_fechamento_docs IS NULL
		AND calcular = 1
		ORDER BY ordem";

		$db->select($sql,'MYSQL', true);
		
		$reg = $db->array_select[0];

		if ($reg['docsFaltando'] > 0)
		{
			$smarty->assign('mensagem_bloqueio', 'Existem documentos da competência '.$competenciaAnterior.' a serem anexados.<br />Para evitar bloqueio no pagamento, regularize a situação.');
		}

$smarty->assign('competencia_options', array($arrayCompetencias_label, $arrayCompetencias_value));

$smarty->assign("tributo_values",$array_tributo_values);
$smarty->assign("tributo_output",$array_tributo_output);

$smarty->assign("nome_colaborador",$cont0["funcionario"]);

$smarty->assign("id_fechamento",$_GET["id_fechamento"]);

$smarty->assign('revisao_documento', 'V4');

$smarty->assign("campo",$conf->campos('cadastro_documentos'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('cadastra_docs_forn.tpl');

?>