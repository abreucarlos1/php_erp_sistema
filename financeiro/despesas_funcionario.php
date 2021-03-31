<?php
/*
		Formulário de Despesas de Funcionarios	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/despesas_funcionario.php
		
		Versão 0 --> VERSÃO INICIAL - 01/01/2005
		Versão 1 --> Impl. template Smarty, atualização do layout (22/08/2008)
		Versão 2 --> Atualização Layout : 05/11/2013 - Carlos Abreu
		Versão 3 --> Atualização na forma de acertos - 16/06/2014 - Carlos Abreu
		Versão 4 --> Melhoria na grid, inclusao do campo OUTRAS DESPESAS - 30/07/2015 - Carlos Abreu
		Versão 5 --> Alteraçães das informaçães despesas - 30/05/2016 - Carlos Abreu
		Versão 6 --> Atualização interface - 08/07/2016 - Carlos Abreu
		Versão 7 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 8 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(331))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("dv_acerto_despesas","innerHTML","");
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	
	$resposta->addAssign("btninserir", "disabled", "true");
	
	$resposta->addEvent("btnvoltar","onclick","history.back();");

	return $resposta;
}

function virgula_ponto($campo, $valor = '')
{
	$resposta = new xajaxResponse();
	
	if(strstr($valor,',')!=FALSE)
	{
		$valor = str_replace(',','.',$valor);
		
		$resposta->addAssign($campo, "value", $valor);
	}
	
	return $resposta;
}

function calcula($dados_form)
{
	$resposta = new xajaxResponse();

	$soma = 0;
	
	for($i=0;$i<=$dados_form["itens"];$i++)
	{
		$soma += $dados_form["valor_".$i];
	}
	
	$resposta->addAssign("txt_total","value",number_format($soma,2,',',''));

	return $resposta;
		
}  

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$conteudo = "";
	
	$campos = $conf->campos('despesas_semanais',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;	
	
	$chars = array("'","\"",")","(","\\","/");

	//apresenta os registros
	$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE funcionarios.id_funcionario = requisicao_despesas.responsavel_despesas ";
	$sql .= "AND requisicao_despesas.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND requisicao_despesas.status IN (1,2,4) ";
	$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
	$sql .= "AND requisicao_despesas.responsavel_despesas = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "ORDER BY data_adiantamento ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');
		
		$reg = $db->array_select;
			
		foreach($reg as $cont_desp)
		{
			//filtra a requisicao
			$sql = "SELECT SUM(valor_despesa) AS total_despesas FROM ".DATABASE.".requisicao_despesas_necessidades ";
			$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $cont_desp["id_requisicao_despesa"] . "' ";
			$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert('Erro '.$sql);
			}
			else
			{			
				$cont = $db->array_select[0];		
					
				$imprimir = ' ';
				
				switch ($cont_desp["status"])
				{			
					case 0:
						$status = 'REQUISITADO';
					break;
					
					case 1:
						$status = 'ADIANTAMENTO';
					break;
					
					case 2:
						$status = 'DESPESAS';
						
						$imprimir = '<img src="'.DIR_IMAGENS.'impressora.png" onclick="imprimir('.$cont_desp["id_requisicao_despesa"].');" style="cursor:pointer;" title="Clique para imprimir">';
					break;
					
					case 3:
						$status = 'ACERTADO';
					break;
					
					case 4:
						$status = 'REJEITADO';
					break;		
							
				}

				$xml->startElement('row');
					$xml->writeAttribute('id','desp_'.$cont_desp["id_requisicao_despesa"]);
					$xml->writeElement ('cell',sprintf("%05d",$cont_desp["id_requisicao_despesa"]));
					$xml->writeElement ('cell',sprintf("%010d",$cont_desp["os"]));
					$xml->writeElement ('cell',mysql_php($cont_desp["data_adiantamento"]));
					$xml->writeElement ('cell',mysql_php($cont_desp["data_prestacao_contas"]));
					$xml->writeElement ('cell',$cont_desp["atividade"]);
					$xml->writeElement ('cell',substr(mysql_php($cont_desp["periodo_inicial"]),0,10) . ' á ' . substr(mysql_php($cont_desp["periodo_final"]),0,10));
					$xml->writeElement ('cell',number_format($cont_desp["valor_adiantamento"],2,",",""));
					$xml->writeElement ('cell',number_format($cont["total_despesas"],2,",",""));
					$xml->writeElement ('cell',$status);
					$xml->writeElement ('cell',$imprimir);
				$xml->endElement();	
				
			}
		}
		
		$xml->endElement();
				
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('div_despesas',true,'500','".$conteudo."');");

	}

	return $resposta;
}

function editar($id_requisicao_despesa)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$conteudo = "";
	
	$conteudo_func = "";
	
	$temp = explode('_',$id_requisicao_despesa);
	
	$id_requisicao_despesa = $temp[1];
		
	if($id_requisicao_despesa!='')
	{		
		//Filtra as requisicao e as OS
		$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $id_requisicao_despesa . "' ";
		$sql .= "AND requisicao_despesas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{			
			$cont = $db->array_select[0];
						
			$resposta->addAssign("id_requisicao_despesa", "value", $id_requisicao_despesa);
				
			if(in_array($cont["status"],array(1,2,4)))
			{
				$resposta->addAssign("btninserir", "disabled", "");
			}
			else
			{
				$resposta->addAssign("btninserir", "disabled", "true");
			}
			
			//obtem as despesas cadastradas no orçamento
			/*	
			$sql = "SELECT AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010, AF2010 ";
			$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$cont["os"])."' "; 
			$sql .= "AND AF2010.AF2_ORCAME = AF1010.AF1_ORCAME ";
			$sql .= "AND AF2010.AF2_COMPOS <> '' ";	
			$sql .= "AND LEFT(AF2010.AF2_COMPOS,3) = 'DES' ";
			$sql .= "GROUP BY AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT ";
			$sql .= "ORDER BY AF2010.AF2_DESCRI ";

			$db->select($sql,'MSSQL', true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{		
				foreach($db->array_select as $regs1)
				{
					$array_items_desp[trim($regs1["AF2_COMPOS"])] = trim($regs1["AF2_DESCRI"]);
				}
				
				if($cont["data_prestacao_contas"]=='0000-00-00' || $cont["data_prestacao_contas"]=='')
				{
					$data_prest = date('d/m/Y');
				}
				else
				{
					$data_prest = mysql_php($cont["data_prestacao_contas"]);
				}
		
				//monta a tabela de despesas declaradas
				$conteudo_acert = '<table width="99%" border="0"><tr>';
				$conteudo_acert .= '<td width="10%"><label class="labels"><strong>valor adiantamento R$: </strong>'.number_format($cont["valor_adiantamento"],2,',','.').'<label></td>';
				$conteudo_acert .= '</tr>';
				
				$conteudo_acert .= '<tr>';
				$conteudo_acert .= '<td><label class="labels"><strong>data prestação contas: </strong><label><input name="data_prestacao_contas" type="text" class="caixa" id="data_prestacao_contas" size="12"  onkeypress=transformaData(this, event); onkeyup=return autoTab(this, 10); value="'.$data_prest.'" onblur=return checaTamanhoData(this,10); /></td>';
				$conteudo_acert .= '</tr></table>';
				
				$resposta->addAssign("dv_adiantamento","innerHTML",$conteudo_acert);				
				
				//filtra as necessidades requisitadas
				$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
				$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $id_requisicao_despesa . "' ";
				$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{
					$regs2 = $db->array_select;
									
					$i = 0;
					
					$xml->openMemory();
					$xml->setIndent(false);
					$xml->startElement('rows') ;
							
					foreach($regs2 as $cont2)
					{
						if($cont2["cod_necessidade"]=='DES99')
						{
							$item[$cont2["cod_necessidade"]] = $cont2["item"];
						}
			
						if($cont2["cod_necessidade"]=='DES99' || $item[$cont2["cod_necessidade"]]!='')
						{
							$despesa = utf8_encode($item[$cont2["cod_necessidade"]]);
						}
						else
						{
							$despesa = utf8_encode($array_items_desp[$cont2["cod_necessidade"]]);
						}
						
						$campo = '<input name="valor_'.$i.'" type="text" class="caixa" style="text-align:right;" id="valor_'.$i.'" value="'.number_format($cont2["valor_despesa"],2,',','').'" size="15" maxlength="7" onkeypress=num_only() onkeyup=xajax_virgula_ponto("valor_'.$i.'",this.value);xajax_calcula(xajax.getFormValues("frm"));>';
						$campo .= '<input name="id_necessidade_'.$i.'" type="hidden" id="id_necessidade_'.$i.'" value="'.$cont2["id_requisicao_despesas_necessidade"].'">';
						
						$xml->startElement('row');
							$xml->writeAttribute('id','itens_'.$i);
							$xml->writeElement ('cell',$despesa);
							$xml->writeElement ('cell',$campo);
						$xml->endElement();	
						$i++;
					}
					
					//se tiver OUTRAS DESPESAS no ORCAMENTO apresenta o campo					
					if(array_key_exists("DES99",$array_items_desp))
					{
						
						$campo_outras = '<input name="itm_'.$i.'" type="text" class="caixa" style="color:#F00" id="itm_'.$i.'" size="50" value="OUTRAS DESPESAS">';
						$campo_desp = '<input name="valor_'.$i.'" type="text" class="caixa" style="text-align:right;" id="valor_'.$i.'" value="0.00" size="15" maxlength="7" onkeypress=num_only() onkeyup=xajax_virgula_ponto("valor_'.$i.'",this.value);xajax_calcula(xajax.getFormValues("frm"));>';
						$campo_desp .= '<input name="outras_'.$i.'" type="hidden" id="outras_'.$i.'" value="1">';
						
						$xml->startElement('row');
							$xml->writeAttribute('id','itens_'.$i);
							$xml->writeElement ('cell',$campo_outras);
							$xml->writeElement ('cell',$campo_desp);
						$xml->endElement();	
						
						$i++;
					}						
					
					$resposta->addAssign("itens","value",$i);
					
					//contabiliza os valores da requisição
					$sql = "SELECT SUM(quantidade*valor_unitario) AS valor, SUM(valor_despesa) as total_despesa FROM ".DATABASE.".requisicao_despesas_necessidades ";
					$sql .= "WHERE requisicao_despesas_necessidades.reg_del = 0 ";
					$sql .= "AND requisicao_despesas_necessidades.id_requisicao_despesa = '".$id_requisicao_despesa."' ";

					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{					
						$regs_vlr = $db->array_select[0];		
						
						$xml->startElement('row');
						
							$xml->writeAttribute('id','itens_'.$i++);
							
							$xml->startElement('cell');
								$xml->writeAttribute('style','text-align:right;font-weight:bold;');
								$xml->text('Total');
							$xml->endElement();

							$xml->writeElement('cell','<input name="txt_total" type="text" class="caixa" style="border:none!important;text-align:right;font-weight:bold;" id="txt_total" value="'.number_format($regs_vlr["total_despesa"],2,',','').'" size="13" readonly="readonly">');
							
						$xml->endElement();	
						
					}
					
					$xml->endElement();
							
					$conteudo = $xml->outputMemory(false);
					
					$resposta->addScript("grid('dv_acerto_despesas',true,'400','".$conteudo."');");
					
				}			
			}
			*/
		}
	}
	else
	{	
		$resposta->addAssign("dv_acerto_despesas","innerHTML","");
		
		$resposta->addAssign("itens","value","0");	
	}
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo acoes = 112
	{		
		//filtra a requisicao
		
		$sql = "SELECT ordem_servico.descricao, ordem_servico.os, a.* FROM ".DATABASE.".requisicao_despesas a
                JOIN ".DATABASE.".ordem_servico ON ordem_servico.id_os = a.id_os and ordem_servico.reg_del = 0 
                WHERE a.id_requisicao_despesa = '" . $dados_form["id_requisicao_despesa"] . "'
                AND a.reg_del = 0";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert('Erro '.$sql);
		}
		else
		{		
			$cont = $db->array_select[0];		
					
			$valor_total = 0;
				
			for($i=0;$i<=$dados_form["itens"];$i++)
			{						
				$valor_total += $dados_form["valor_".$i];
				
				$usql = "UPDATE ".DATABASE.".requisicao_despesas_necessidades SET ";
				$usql .= "valor_despesa = '" . $dados_form["valor_".$i] . "' ";
				$usql .= "WHERE id_requisicao_despesas_necessidade = '" . $dados_form["id_necessidade_".$i] ."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				//insere OUTRAS DESPESAS caso tenha valor !=0
				if($dados_form["outras_".$i] && $dados_form["valor_".$i]!=0)
				{
					$isql = "INSERT INTO ".DATABASE.".requisicao_despesas_necessidades ";
					$isql .= "(id_requisicao_despesa, cod_necessidade, item, quantidade, valor_despesa) ";
					$isql .= "VALUES ('".$dados_form["id_requisicao_despesa"]."', ";
					$isql .= "'DES99', ";
					$isql .= "'".maiusculas(tiraacentos($dados_form["itm_".$i]))."', ";
					$isql .= "'1', ";
					$isql .= "'".$dados_form["valor_".$i]."') ";
					
					$db->insert($isql,'MYSQL');
				}						
			}
			
			$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
			$usql .= "status = 2, ";
			$usql .= "data_prestacao_contas = '".php_mysql($dados_form["data_prestacao_contas"])."' ";
			$usql .= "WHERE id_requisicao_despesa = '" . $dados_form["id_requisicao_despesa"] ."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{				
				//coordenador/responsavel pela requisição
				$sql = "SELECT funcionarios.id_funcionario, funcionarios.funcionario, usuarios.email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
				$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND usuarios.reg_del = 0 ";
				$sql .= "AND funcionarios.situacao = 'ATIVO' ";
				
				$db->select($sql,'MYSQL',true);
					
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{				
					foreach($db->array_select as $regs_func)
					{
						$array_funcionarios[0][$regs_func["id_funcionario"]] = $regs_func["funcionario"];
						$array_funcionarios[1][$regs_func["id_funcionario"]] = $regs_func["email"];
					}
				}
							
				$texto = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
				$texto .=' <tr>';
				$texto .='	  <td><strong>Acerto Requisição de Despesa da OS '.sprintf('%05d', $cont['OS']).' '.$cont['descricao'].'</strong></td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Requisição nº: </strong>'.sprintf("%05d",$dados_form["id_requisicao_despesa"]).'</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Data acerto: </strong>'.$dados_form["data_prestacao_contas"].'</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Valor declarado: R$ </strong>'.number_format($valor_total,2,',','').'</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td>Já está lançado os valores das despesas no sistema.</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td>Favor imprimir o Relatório de despesas juntamente com os comprovantes ao setor financeiro para conferência e baixa.</td>';
				$texto .=' </tr>';						
				$texto .='</table>';
				
				$params = array();
				
				$params['from']	= $array_funcionarios[1][$_SESSION["id_funcionario"]];
				$params['from_name'] = "REQUISIÇÃO DESPESAS: ".sprintf("%05d",$dados_form["id_requisicao_despesa"])." - ACERTO";
				$params['subject'] = "REQUISIÇÃO DESPESAS: ".sprintf("%05d",$dados_form["id_requisicao_despesa"])." - ACERTO";
				
				$params['emails']['to'][] = array('email' => "financeiro@dominio.com.br", 'nome' => "Financeiro");
				$params['emails']['to'][] = array('email' => "planejamento@dominio.com.br", 'nome' => "Grupo Planejamento");
				
				if($array_funcionarios[1][$cont["id_funcionario"]])//solicitante
				{
					$params['emails']['cc'][] = array('email' => $array_funcionarios[1][$cont["id_funcionario"]], 'nome' => $array_funcionarios[0][$cont["id_funcionario"]]);
				}
				
				if($array_funcionarios[1][$_SESSION["id_funcionario"]]) //responsavel
				{
					$params['emails']['cc'][] = array('email' => $array_funcionarios[1][$_SESSION["id_funcionario"]], 'nome' => $array_funcionarios[0][$_SESSION["id_funcionario"]]);
				}

				if(ENVIA_EMAIL)
				{				
					$mail = new email($params);
					
					$mail->montaCorpoEmail($texto);
					
					if(!$mail->Send())
					{
						$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
					}
				}
				else 
				{
					$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 3);
				}		
				
			}
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");

		}
	}
	
	$resposta->addScript("xajax_voltar();");

	return $resposta;		
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("virgula_ponto");
$xajax->registerFunction("calcula");
$xajax->registerFunction("atualizar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela(xajax.getFormValues('frm'))");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");

	myTabbar.addTab("a4_", "Acerto");
	
	myTabbar.tabs("a4_").attachObject("a4");
	
	myTabbar.tabs("a4_").setActive();
	
	myTabbar.enableAutoReSize(true);
	
}

function grid(tabela, autoh, height, xml)
{

	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch (tabela)
	{
		case 'div_despesas':	
	
			function doOnRowSelected1(row,col)
			{
				if(col<=8)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);	
			mygrid.setHeader("Req nº, Projeto, Data Adiant., Data prest., Atividade/Obs., Período, Valor Adiant., Valor declar., Status, I",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("60,80,80,80,*,140,85,85,80,25");
			mygrid.setColAlign("center,center,left,left,left,left,right,right,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
		break;
		
		case 'dv_acerto_despesas':	

			mygrid.setHeader("Item, valor",
				null,
				["text-align:center","text-align:left"]);
			mygrid.setInitWidths("*,*");
			mygrid.setColAlign("left,left");
			mygrid.setColTypes("ro,ro");
			mygrid.setColSorting("str,str");
		break;
	
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
	
}


function imprimir(id_requisicao_despesa)
{
	window.open('relatorios/rel_despesas_semanal.php?id_requisicao_despesa='+id_requisicao_despesa, '_blank');
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V8");

$smarty->assign("campo",$conf->campos('despesas_semanais'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('despesas_funcionario.tpl');

?>