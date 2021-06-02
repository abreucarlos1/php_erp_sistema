<?php
/*
		Formulário de RESERVA DE SALAS DE REUNIÃO	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/reserva_salas.php
	
		Versão 0 --> VERSÃO INICIAL : 08/03/2013 - Carlos Abreu
		Versão 1 --> Inclusão do campo Observação - 10/01/2013 - Carlos Abreu
		Versão 2 --> Atualização layout - Carlos Abreu - 10/04/2017
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(268))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("data", "value", date('d/m/Y'));
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualiza_tabs($dados_form, $default = 'JADE')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_salas ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "ORDER BY ordem, sala ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}	
	
	$array_js = NULL;
	
	foreach ($db->array_select as $salas)
	{
		$array_js[] = tiraacentos($salas["sala"]);
	}
	
	$resposta->addScript("tab(".json_encode($array_js).")");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),'".$default."');");	
	
	return $resposta;	
}

function atualizatabela($dados_form, $sala = 'JADE')
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('campos',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	semana_ini_fim($dados_form["semana"],$data_ini,$datafim);
		
	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$xml->startElement('row');
		
		$xml->startElement('cell');
			$xml->text($data_ini);
		$xml->endElement();
		
		$xml->startElement('cell');
			$xml->text(calcula_data($data_ini,"sum","day",1));
		$xml->endElement();

		$xml->startElement('cell');
			$xml->text(calcula_data($data_ini,"sum","day",2));
		$xml->endElement();
		
		$xml->startElement('cell');
			$xml->text(calcula_data($data_ini,"sum","day",3));
		$xml->endElement();

		$xml->startElement('cell');
			$xml->text($datafim);
		$xml->endElement();			
		
	$xml->endElement();	
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_reserva_salas, ".DATABASE.".rh_salas ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_reserva_salas.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_reserva_salas.reg_del = 0 ";
	$sql .= "AND rh_salas.reg_del = 0 ";
	$sql .= "AND rh_reserva_salas.data_uso BETWEEN '".php_mysql($data_ini)."' AND '".php_mysql($datafim)."' ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND rh_reserva_salas.id_sala = rh_salas.id_sala ";
	$sql .= "AND rh_salas.sala = '".$sala."' ";
	$sql .= "AND rh_reserva_salas.status_reserva = 1 ";
	$sql .= "ORDER BY rh_reserva_salas.data_uso, rh_reserva_salas.hora_uso_ini ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$array_info = NULL;
	
	foreach($db->array_select as $cont)
	{		
		$array_info[$cont["data_uso"]][] = array($cont["id_reserva_sala"],"Sol.: ".$cont["funcionario"]."<br>Obs:".$cont["observacao"],"Per.".substr($cont["hora_uso_ini"],0,5),substr($cont["hora_uso_fim"],0,5),$cont["id_funcionario"]);
	}
	
	//Mostra array datas
	foreach($array_info as $chave=>$data)
	{		
		//mostra array com valores (id)
		foreach ($data as $indice=>$funcionario)
		{
			$array_tam_date[$chave] += 1;							
		}				
	}
		
	//ordena o maior item do array
	rsort($array_tam_date);
	
	$maior_valor = array_shift($array_tam_date);
	
	//Linhas (registros) nivelado pelo maior quantidade de registros
	for($i=0;$i<$maior_valor;$i++)
	{
		$xml->startElement('row');
		//colunas (datas da semana)
		for($j=0;$j<=5;$j++)
		{
			//$array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][X]
			//ONDE X = 0 --> INDICE
			//     	   1 --> FUNCIONARIO
			//     	   2 --> HORA INI
			//         3 --> HORA FIM
			//		   4 --> ID FUNCIONARIO	 	
			
			if(($array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][4] == $_SESSION["id_funcionario"]) && $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0])
			{
				//edicao permitida
				$edicao = 'onclick=if(confirm("Confirma a exclusão da reserva selecionada?")){xajax_cancelar("'. $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0].'","'.$sala.'");} style="cursor:pointer;background-color:#FF8000;" ';				
			}
			else
			{
				if((in_array($_SESSION["id_funcionario"],array(6,953))) && $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0])
				{
					$edicao = 'onclick=if(confirm("Confirma a exclusão da reserva selecionada?")){xajax_cancelar("'. $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0].'","'.$sala.'");} style="cursor:pointer;background-color:#C6FFC6;" ';
				}
				else
				{
					$edicao = 'style="background-color:#C6FFC6;" ';
				}
			}	
			
			if($array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0])
			{						
				$texto = $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][1].'<br>'.$array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][2].' as '.$array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][3].'<br>';
			}
			else
			{					
				$texto = ' ';
			}
			
			$table = '<table width="100%">';
			$table .= '<tr>';
			$table .= '<td '.$edicao.'>';
			$table .= $texto;
			$table .= '</td>';
			$table .= '</tr>';
			$table .= '</table>';
			
			$xml->startElement('cell');
				$xml->text($table);
			$xml->endElement();			
			
		}
		
		$xml->endElement();		
	}		
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('div_".$sala."',true,'490','".$conteudo."');");	
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta))
	{
		$sala = explode('#',$dados_form["sala"]);
		
		$db = new banco_dados;
		
		if($dados_form["data"]!='' && $dados_form["hora_ini"]!='' && $dados_form["hora_fim"]!='' && $sala[0]!='')
		{
			
			$sql = "SELECT * FROM ".DATABASE.".rh_reserva_salas ";
			$sql .= "WHERE id_sala = '".$sala[0]."' ";
			$sql .= "AND reg_del = 0 ";
			$sql .= "AND data_uso = '".php_mysql($dados_form["data"])."' ";
			$sql .= "AND hora_uso_ini = '".$dados_form["hora_ini"].":00' ";
			$sql .= "AND hora_uso_fim = '".$dados_form["hora_fim"].":00' ";
			$sql .= "AND status_reserva = 1 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{		
				$isql = "INSERT INTO ".DATABASE.".rh_reserva_salas ";
				$isql .= "(id_sala, id_funcionario, observacao, data_reserva, data_uso, hora_uso_ini, hora_uso_fim) ";
				$isql .= "VALUES ('" . $dados_form["sala"] . "', ";
				$isql .= "'".$_SESSION["id_funcionario"]."', ";
				$isql .= "'".maiusculas($dados_form["observacao"])."', ";
				$isql .= "'".date('Y-m-d')."', ";
				$isql .= "'".php_mysql($dados_form["data"])."', ";
				$isql .= "'".$dados_form["hora_ini"].":00', ";
				$isql .= "'".$dados_form["hora_fim"].":00') ";

				$db->insert($isql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}				
					
				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),'".$sala[1]."');");
				
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

function cancelar($id, $sala = 'JADE')
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{
		$db = new banco_dados;

		$usql = "UPDATE ".DATABASE.".rh_reserva_salas SET ";
		$usql .= "status_reserva = 2 ";
		$usql .= "WHERE id_reserva_sala = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addAlert($msg[2]);
		
		$resposta->addScript("xajax_voltar();");

		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'),'".$sala."');");
	
	}
	
	return $resposta;
}

function periodos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;	
	
	$resposta->addAssign("inicial","innerHTML","");

	$resposta->addAssign("final","innerHTML","");

	$resposta->addScript("document.getElementById('btninserir').disabled=true;");
	
	$array_hini_values = NULL;
	
	$array_hfim_values = NULL;
	
	$data_array = explode("/", $dados_form["data"]);

	$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
	
	$data_stamp1 = mktime(0,0,0,date('m'), date('d'), date('Y'));
	
	$data_format = getdate($data_stamp);
	
	if($dados_form["sala"]!="")	
	{
			
		if($dados_form["data"] != "" && strlen($dados_form["data"])==10)
		{
			
			//monta array com os periodos default: 08:00 as 17:00 (28800s) as (61200s)
			//utilizando segundos e transformando em horas
			//com intervalo de 30 minutos (1800s)
			//retirando as exceções
			$intervalo = 1800;			
		
			//Se for dias da semana e não for feriado
			//ALTERADO EM 14/02/2012			
			//if($data_format["wday"]>0 && $data_format["wday"]<6 && !in_array($dados_form["data"],$data_feriado))
			if($data_format["wday"]>0 && $data_format["wday"]<6)
			{
				//cria o array de periodos
				for($i=28800;$i<=61200;$i+=$intervalo)
				{
					if(!in_array(substr(sec_to_time($i),0,5),$array_exc))
					{
						$array_hini_values[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
						
						$array_hfim_values[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
					}	
				}
			}
			
			//ordena o array
			asort($array_hini_values);
			
			asort($array_hfim_values);
			
			//monta o array de reserva de salas
			$sql = "SELECT * FROM ".DATABASE.".rh_reserva_salas ";	
			$sql .= "WHERE id_sala = '".$dados_form["sala"]."' ";
			$sql .= "AND reg_del = 0 ";
			$sql .= "AND data_uso = '".php_mysql($dados_form["data"])."' ";
			$sql .= "AND status_reserva = 1 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			foreach($db->array_select as $cont)
			{			
				//percorre os periodos da data
				for($l=time_to_sec($cont["hora_uso_ini"]);$l<=time_to_sec($cont["hora_uso_fim"]);$l+=$intervalo)
				{
					if(time_to_sec($cont["hora_uso_ini"])!=$l)
					{
						unset($array_hfim_values[substr(sec_to_time($l),0,5)]);	
					}
				
					if(time_to_sec($cont["hora_uso_fim"])!=$l)
					{			
						unset($array_hini_values[substr(sec_to_time($l),0,5)]);
					}						
				}
			}
				
			//ordena o array
			asort($array_hini_values);
			
			asort($array_hfim_values);		
	
			//retira o 1º elemento
			array_shift($array_hfim_values);
			
			//retira o último elemento
			array_pop($array_hini_values);
			
			//Se os combos não tiverem valores, desabilita o botão inserir
			if(count($array_hini_values)<1 || count($array_hfim_values)<1)
			{
				$resposta->addScript("document.getElementById('btninserir').disabled=true;");
			}
			else
			{			
				$resposta->addScript("document.getElementById('btninserir').disabled=false;");
				//monta o combo hora inicial
				$comboi = '<select name="hora_ini" class="caixa" id="hora_ini" onkeypress="return keySort(this);">';
				
				foreach ($array_hini_values as $valor)
				{
					$comboi .= '<option value="'.$valor.'">'.$valor.'</option>';
				}
				
				$comboi .= '</select>';
				
				//monta o combo hora inicial
				$combof = '<select name="hora_fim" class="caixa" id="hora_fim" onkeypress="return keySort(this);">';
				
				$i = 1;
				
				$select = '';
				
				foreach ($array_hfim_values as $valor)
				{
					if($i==count($array_hfim_values)-1)
					{
						$select = 'selected';
					}
					
					$combof .= '<option value="'.$valor.'" selected="'.$select.'">'.$valor.'</option>';
				}
				
				$combof .= '</select>';
			}	
			
			$resposta->addAssign("inicial","innerHTML",$comboi);
			
			$resposta->addAssign("final","innerHTML",$combof);
				
		}
		else
		{
			$resposta->addAlert("data vazia, favor preencher.");
			
			$resposta->addScript("document.getElementById('btninserir').disabled=true;");
			
			$resposta->addAssign("data","value",date('d/m/Y'));
			
			$resposta->addScript("document.getElementById('data').focus();");
			
		}
	}
	else
	{
		//não apresenta os periodos caso a OS não tenha ID
		$resposta->addAssign("inicial","innerHTML","");
		$resposta->addAssign("final","innerHTML","");	
	}

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("cancelar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("periodos");
$xajax->registerFunction("atualiza_tabs");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualiza_tabs(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>


var myTabbar;

function tab(array_tabs)
{

	var x = array_tabs.length;
	var i;	
	
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	//cria as tabs conforme itens do array
	for(i=0;i<x;i++)
	{
		if(i==0)
		{
			active = true;
		}
		else
		{
			active = false;	
		}
				
		myTabbar.addTab(array_tabs[i], array_tabs[i], null, null, active);
		
		myTabbar.tabs(array_tabs[i]).attachHTMLString('<div id="div_'+array_tabs[i]+'" > </div>');				
	}
	
	function sel_tab(idNew,idOld)
	{
		//ativa quando seleciona a tab		
		xajax_atualizatabela(xajax.getFormValues('frm'),idNew);
		
		//seleciona o combo a partir do tab
		for(i=0; i < document.getElementById('sala').options.length; i++)
		{
			str = document.getElementById('sala').options[i].value;
			
			str = str.split("#");
			
		  if(str[1] === idNew) 
		  {
			document.getElementById('sala').selectedIndex = i;
			break;
		  }
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);

	myTabbar.enableAutoReSize(true);
}

function muda_aba(id)
{
	id = id.split("#");
	
	myTabbar.tabs(id[1]).setActive();
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Segunda,Terça,Quarta,Quinta,Sexta",
		null,
		["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("*,*,*,*,*");
	mygrid.setColAlign("center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$array_salas_values = NULL;
$array_salas_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".rh_salas ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY ordem, sala ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}	


foreach ($db->array_select as $salas)
{
	$array_salas_values[] = $salas["id_sala"].'#'.tiraacentos($salas["sala"]);
	$array_salas_output[] = $salas["sala"]." - ".$salas["localizacao"];	
}

$smarty->assign("array_sala",implode('#',$array_tmp));

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('reserva_salas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("data",date('d/m/Y'));

$smarty->assign("option_sala_values",$array_salas_values);

$smarty->assign("option_sala_output",$array_salas_output);

$smarty->assign("classe",CSS_FILE);

$smarty->display('reserva_salas.tpl');

?>

