<?php
/*
	Formulário de Quadro de ausências	
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../rh/quadro_ausencias.php
	
	Versão 0 --> VERSÃO INICIAL - 28/10/2011
	Versão 1 --> Inclusão das permissões - 11/09/2014 - Carlos Abreu
	Versão 2 --> Atualização layout - Carlos Abreu - 07/04/2017
	Versão 3 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(196))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnexcluir","onclick","");
	
	$resposta->addScript("document.getElementById('btnexcluir').disabled=true;");
	
	$resposta->addAssign("data","value",date("d/m/Y"));
	
	$resposta->addAssign("id_quadro_ausencia","value","");
	
	$resposta->addAssign("complemento","value","");

	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;

	semana_ini_fim($dados_form["semana"],$data_ini,$datafim);
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_quadro_ausencias ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_quadro_ausencias.id_funcionario ";
	$sql .= "AND rh_quadro_ausencias.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND rh_quadro_ausencias.data BETWEEN '".php_mysql($data_ini)."' AND '".php_mysql($datafim)."' ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	$sql .= "ORDER BY rh_quadro_ausencias.data ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{		
		$array_info = NULL;
		
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
		
		foreach($db->array_select as $cont)
		{		
			$array_info[$cont["data"]][] = array($cont["id_quadro_ausencia"],$cont["funcionario"],$cont["id_motivo"],$cont["observacao"],$cont["id_funcionario"]);
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
			//colunas (datas da semana)			
			$xml->startElement('row');
			
			for($j=0;$j<=5;$j++)
			{
				//$array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][X]
				//ONDE X = 0 --> INDICE
				//     	   1 --> FUNCIONARIO
				//     	   2 --> MOTIVO
				//         3 --> OBS
				//		   4 --> ID FUNCIONARIO	 	
				
				$motivo = "";
			
				switch ($array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][2])
				{
					case 1:
						$motivo = 'REUNIÃO';
					break;
					
					case 2:
						$motivo = 'TRABALHO EXTERNO';
					break;
					
					case 3:
						$motivo = 'PARTICULAR';
					break;
					
					case 4:
						$motivo = 'FOLGA';
					break;
					
					default : $motivo = '';
				}
				
				if(($array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][4] == $_SESSION["id_funcionario"]) && $motivo!="")
				{
					//edicao permitida
					$edicao = 'onclick=xajax_editar("'. $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0].'"); style="cursor:pointer;background-color:#FF8000;" ';
				
				}
				else
				{
					if((in_array($_SESSION["id_funcionario"],array(953))) && $motivo!="")
					{
						$edicao = 'onclick=xajax_editar("'. $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][0].'"); style="cursor:pointer;background-color:#C6FFC6;" ';
					}
					else
					{
						$edicao = 'style="background-color:#C6FFC6;" ';
					}
				}	

				$table = '<table width="100%">';
				$table .= '<tr>';
				$table .= '<td '.$edicao.'>';
				$table .= $array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][1].'<br>'.$motivo.'<br>'.wordwrap(maiusculas($array_info[php_mysql(calcula_data($data_ini,"sum","day",$j))][$i][3]),35,'<br>').'<br>';
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
		
		$resposta->addScript("grid('ausencias',true,'450','".$conteudo."');");

	}

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo sub-modulos = 109
	{
		$sql = "SELECT * FROM ".DATABASE.".rh_quadro_ausencias ";
		$sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
		$sql .= "AND rh_quadro_ausencias.reg_del = 0 ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND id_motivo = '".$dados_form["tipo_motivo"]."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			if($db->numero_registros>0)
			{
				$resposta->addAlert('Já existe registro deste funcionário nesta data.');
			}
			else
			{
				if($dados_form["data"] != "" && strlen($dados_form["data"])==10 && $dados_form["id_funcionario"] !=0 && $dados_form["tipo_motivo"]!="")
				{
					$isql = "INSERT INTO ".DATABASE.".rh_quadro_ausencias ";
					$isql .= "(id_funcionario, id_motivo, observacao, data) ";
					$isql .= "VALUES ('" . $dados_form["id_funcionario"] . "', ";
					$isql .= "'" . $dados_form["tipo_motivo"] . "', ";
					$isql .= "'" . strip_tags(addslashes(maiusculas($dados_form["complemento"]))) . "', ";
					$isql .= "'" . php_mysql($dados_form["data"]) . "') ";
			
					$db->insert($isql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{
						$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
					}
				}
				else
				{
					$resposta->addAlert('Favor preencher todos os campos.');	
				}
			}
		}
	}
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".rh_quadro_ausencias ";
	$sql .= "WHERE funcionarios.id_funcionario = rh_quadro_ausencias.id_funcionario ";
	$sql .= "AND rh_quadro_ausencias.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del =  0 ";
	$sql .= "AND rh_quadro_ausencias.id_quadro_ausencia = '" . $id . "' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{		
		$cont = $db->array_select[0];	
		
		$resposta->addAssign('id_quadro_ausencia','value',$cont["id_quadro_ausencia"]);
		
		$resposta->addAssign('id_funcionario','value',$cont["id_funcionario"]);
		
		$resposta->addAssign('nome_func','innerHTML',$cont["funcionario"]);
		
		$resposta->addAssign('data','value',mysql_php($cont["data"]));
		
		$resposta->addScript("document.getElementById('btnexcluir').disabled=false;");
		
		$resposta->addScript("seleciona_combo('" . $cont["id_motivo"] . "', 'tipo_motivo');");
	
		$resposta->addScript("seleciona_combo('" . $cont["id_funcionario"] . "', 'funcionario');");
	
		$resposta->addAssign("complemento","value",$cont["observacao"]);
		
		$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
		
		$resposta->addAssign("btninserir", "value", "Atualizar");
		
		$resposta->addEvent("btnexcluir", "onclick", "if(confirm('Deseja excluir o agendamento ".mysql_php($cont["data"])." ".$cont["funcionario"]."?')){xajax_excluir('".$id."');};");
		
		$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	
	}	
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{	
		$sql = "SELECT * FROM ".DATABASE.".rh_quadro_ausencias ";
		$sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
		$sql .= "AND rh_quadro_ausencias.reg_del = 0 ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND id_motivo = '".$dados_form["tipo_motivo"]."' ";
		$sql .= "AND id_quadro_ausencia <> '".$dados_form["id_quadro_ausencia"]."' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{	
			if($db->numero_registros>0)
			{
				$resposta->addAlert('Já existe registro deste funcionário nesta data.');
			}
			else
			{
				if($dados_form["data"] != "" && strlen($dados_form["data"])==10 && $dados_form["id_funcionario"] !=0 && $dados_form["tipo_motivo"]!="")
				{
					$usql = "UPDATE ".DATABASE.".rh_quadro_ausencias SET ";
					$usql .= "data = '" . php_mysql($dados_form["data"]) ."', ";
					$usql .= "id_motivo = '".$dados_form["tipo_motivo"]."', ";
					$usql .= "id_funcionario = '" . $dados_form["id_funcionario"] ."', ";
					$usql .= "observacao = '" . strip_tags(maiusculas($dados_form["complemento"])) ."' ";						
					$usql .= "WHERE id_quadro_ausencia = '" . $dados_form["id_quadro_ausencia"] ."' ";

					$db->update($usql,'MYSQL');
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
				
				}
				else
				{
					$resposta->addAlert('Favor preencher todos os campos.');	
				}
			}
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	if($conf->checa_permissao(2,$resposta))
	{
		$usql = "UPDATE ".DATABASE.".rh_quadro_ausencias SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_quadro_ausencia = '" . $id ."' ";

		$db->update($usql,'MYSQL');
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$resposta->addScript("xajax_voltar();");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	
	return $resposta;
}

function seleciona_func($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;	
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$id."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{		
		$regs = $db->array_select[0];
		
		$resposta->addAssign('id_funcionario','value',$regs["id_funcionario"]);
		
		$resposta->addAssign('nome_func','innerHTML',$regs["funcionario"]);
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("seleciona_func");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));document.getElementById('data').focus();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

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

$smarty->assign("campo",$conf->campos('quadro_ausencias'));

$smarty->assign("botao",$conf->botoes());

$msg = $conf->msg();

$array_func_values = NULL;
$array_func_output = NULL;

$array_func_values[] = "";
$array_func_output[] = "SELECIONE";

//permite inserção
$insere = $conf->checa_permissao(16,$resposta);

if($insere)
{
	$smarty->assign("alter","");
}
else
{
	$smarty->assign("alter","disabled=true");
}


//Libera inserção para outros funcionarios caso o funcionario tenha permissão
//if(in_array($_SESSION["id_funcionario"],array(6,953)))
//{
	//$smarty->assign("display","inline");
//}
//else
//{
	$smarty->assign("display","none");	
//}

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND nivel_atuacao IN ('D','C','S','G') ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_func_values[] = $regs["id_funcionario"];
	$array_func_output[] = $regs["funcionario"];
	
	//Libera inserção caso o funcionario tenha nivel de atuação
	if($_SESSION["id_funcionario"]==$regs["id_funcionario"])
	{
		$smarty->assign("alter","");
	}	
}

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('quadro_ausencias'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_values",$array_func_values);

$smarty->assign("option_output",$array_func_output);

$smarty->assign("nome_funcionario",$_SESSION["nome_usuario"]);

$smarty->assign("cod_funcionario",$_SESSION["id_funcionario"]);

$smarty->assign("data", date('d/m/Y'));

$smarty->assign("nome_formulario","QUADRO DE AUSÊNCIAS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('quadro_ausencias.tpl');

?>