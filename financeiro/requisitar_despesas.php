<?php
/*
	Formulário de Requisitar de Despesas	
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	../financeiro/requisitar_despesas.php

	Versão 0 --> VERSÃO INICIAL : 01/01/2005
	Versão 1 --> Impl. template Smarty, XAJAX, classe do banco, Grid, atualização do layout 27/08/2008
	Versão 2 --> Atualização / modificação - 10/06/2014 - Carlos Abreu
	Versão 3 --> Melhorias  GRID - 31/07/2015 - Carlos Abreu
	Versão 4 --> Incluido verificação de necessidades - 06/10/2015 - Carlos Abreu
	Versão 5 --> Alterações das informações despesas - 30/05/2016 - Carlos Abreu
	Versão 6 --> Alterações classe banco, inclusao e-mail fernanda - 02/09/2016 - Carlos Abreu
	Versão 7 --> atualização layout - Carlos Abreu - 28/03/2017 
	Versão 8 --> inclusão de OS destino para contabilização - 25/04/2017 - Carlos Abreu	
	Versão 9 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(329))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addScriptCall("remove_controls('items_1','qtd_itens')");
	
	$resposta->addAssign("dv_orcado","innerHTML","");
	
	$resposta->addAssign("num_sol","innerHTML","");
	
	$resposta->addAssign("vlr_orc","value",0);
	
	$resposta->addAssign("dv_consumido","innerHTML","");
	
	$resposta->addAssign("vlr_sol","value",0);	
	
	$resposta->addAssign("qtd_itensnec","value", 0);
	
	$resposta->addAssign("div_necessidades", "innerHTML", "");
	
	$resposta->addAssign("periodo_ini", "value", date('d/m/Y'));
	
	$resposta->addAssign("periodo_fim", "value", date('d/m/Y'));
	
	$resposta->addAssign("btninserir", "value", "Inserir");
	
	$resposta->addScript("document.getElementById('btninserir').disabled='disabled'");
	
	$resposta->addEvent("btninserir", "onclick", "if(inserir()){xajax_insere(xajax.getFormValues('frm'));};");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function os_destino($id_os)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$id_os = explode("#",$id_os);
	
	/*
	if($id_os[0]==3803) //OS 900
	{	
		$sql = "SELECT AF1_ORCAME, AF1_DESCRI  FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
		$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF2010.AF2_COMPOS <> '' ";
		$sql .= "AND AF2010.AF2_ORCAME = AF1010.AF1_ORCAME ";
		$sql .= "AND AF1010.AF1_FASE IN ('04','09') ";	
		$sql .= "AND AF1_ORCAME NOT IN ('0000000801','0000000900','0000000998','0000000803','0000001501','0000001301') ";	
		$sql .= "GROUP BY AF1010.AF1_ORCAME, AF1010.AF1_DESCRI  ";
		$sql .= "ORDER BY AF1010.AF1_ORCAME ";
		
		$db->select($sql,'MSSQL',true);
		
		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$combo = '<select id="os_destino" name="os_destino" class="caixa" onkeypress=return keySort(this);>';
		
		$combo .= '<option value="">SELECIONE</option>';
		
		foreach($db->array_select as $regs)
		{
			$combo .= '<option value="'.trim($regs["AF1_ORCAME"]).'">'.trim($regs["AF1_ORCAME"])." - ".trim($regs["AF1_DESCRI"]).'</option>';
		}	
		
		$combo .= '</select>';	
		
		$resposta->addAssign("combo_os_destino", "innerHTML", $combo);
		
		$resposta->addScript("document.getElementById('div_os_destino').style.display = 'inline';");	
	}
	else
	{
		$resposta->addScript("document.getElementById('div_os_destino').style.display = 'none';");
	}
	*/
	
	return $resposta;		
}

function despesas($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$os = NULL;
	
	$qtd_vlr = 0;
	
	$array_qtd = NULL;
		
	$os = explode("#",$dados_form["os"]);//0 - id_os / 1 - Projeto
	
	$db = new banco_dados;
	
	//contabiliza as quantidades já solicitadas para compor o valor projetado
	$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".requisicao_despesas_necessidades ";
	$sql .= "WHERE requisicao_despesas.reg_del = 0 ";
	$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";
	$sql .= "AND requisicao_despesas.id_os = '".$os[0]."' ";
	$sql .= "AND requisicao_despesas.id_requisicao_despesa = requisicao_despesas_necessidades.id_requisicao_despesa ";
	
	if(!empty($dados_form["id_requisicao_despesa"]))
	{
		$sql .= "AND requisicao_despesas_necessidades.id_requisicao_despesa = '".$dados_form["id_requisicao_despesa"]."' "; 
	}

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{	
		foreach($db->array_select as $regs_qtd)
		{
			$array_qtd[$regs_qtd["cod_necessidade"]] += $regs_qtd["quantidade"];
			
			//se carro frota
			if($regs_qtd["cod_necessidade"]=='DES98')
			{
				$array_periodo[$regs_qtd["cod_necessidade"]]['inicial'] = $regs_qtd["hora_ini"]; 
				
				$array_periodo[$regs_qtd["cod_necessidade"]]['final'] = $regs_qtd["hora_fim"];
			}
			
			//se outras despesas, pega o texto
			if($regs_qtd["cod_necessidade"]=='DES99')
			{
				$array_out_desp[$regs_qtd["cod_necessidade"]] = $regs_qtd["item"]; 
			}
			
			$valor_solicitado += $regs_qtd["quantidade"]*$regs_qtd["valor_unitario"];	
		}
	
		/*
		//obtem O TOTAL DO ORCAMENTO
		$sql = "SELECT SUM(AF4_VALOR) AS ORCADO FROM AF4010 WITH(NOLOCK) ";
		$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF4010.AF4_ORCAME = '".$os[1]."' "; 
	
		$db->select($sql,'MSSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$regs3 = $db->array_select[0];
			
			//seleciona as despesas cadastradas nas tarefas
			$sql = "SELECT AF2010.AF2_ORCAME, AF2010.AF2_TAREFA, AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
			$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF1010.AF1_ORCAME = '".$os[1]."' "; 
			$sql .= "AND AF2010.AF2_ORCAME = AF1010.AF1_ORCAME ";
			$sql .= "AND AF2010.AF2_COMPOS <> '' ";	
			$sql .= "AND LEFT(AF2010.AF2_COMPOS,3) = 'DES' ";
			$sql .= "GROUP BY AF2010.AF2_ORCAME, AF2010.AF2_TAREFA, AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT ";
			$sql .= "ORDER BY AF2010.AF2_COMPOS, AF2010.AF2_DESCRI ";
			
			$db->select($sql,'MSSQL',true);
						
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				if($db->numero_registros_ms==0)
				{
					$resposta->addAlert('Não há despesas cadastradas no orçamento.');
					
					$resposta->addScript('document.getElementById("btninserir").disabled=true;');
				}
				else
				{
					$cont1 = $db->array_select;
								
					$resposta->addScript('document.getElementById("btninserir").disabled=false;');
					
					if($valor_solicitado>=$regs3["ORCADO"])
					{
						$st = '#F00';
					}
					else
					{
						$st = '#000';
					}
					
					//mostra os valores
					$resposta->addAssign("dv_orcado","innerHTML",number_format($regs3["ORCADO"],2,',',''));
					
					$resposta->addAssign("vlr_orc","value",$regs3["ORCADO"]);
					
					$resposta->addAssign("dv_consumido","innerHTML",number_format($valor_solicitado,2,',',''));
					
					$resposta->addAssign("vlr_sol","value",$valor_solicitado);
					
					$resposta->addAssign("dv_consumido","style.color",$st);
					
					$i = 1;
	
					$xml->openMemory();
					$xml->setIndent(false);
					$xml->startElement('rows') ;			
					
					foreach($cont1 as $regs)
					{			
						$sql = "SELECT SUM(AF4_VALOR) AS VALOR FROM AF4010 WITH(NOLOCK) ";
						$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
						$sql .= "AND AF4010.AF4_ORCAME = '".$regs["AF2_ORCAME"]."' "; 
						$sql .= "AND AF4_TAREFA = '".$regs["AF2_TAREFA"]."' ";
	
						$db->select($sql,'MSSQL',true);
	
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						else
						{			
							$regs2 = $db->array_select[0];
							
							$qtd_vlr = $array_qtd[trim($regs["AF2_COMPOS"])]?$array_qtd[trim($regs["AF2_COMPOS"])]:0;
							
							if($qtd_vlr>=$regs["AF2_QUANT"])
							{
								$style = 'color:#F00';
							}
							else
							{
								$style = 'color:#0F0';	
							}
							
							//se for edição, preenche os campos com valores
							if(!empty($dados_form["id_requisicao_despesa"]))
							{
								$out_desp = $array_out_desp[trim($regs["AF2_COMPOS"])];
								
								$hi = $array_periodo[trim($regs["AF2_COMPOS"])]["inicial"];
								
								$hf = $array_periodo[trim($regs["AF2_COMPOS"])]["final"];
								
								if($array_qtd[trim($regs["AF2_COMPOS"])]!='')
								{
									$valor = $array_qtd[trim($regs["AF2_COMPOS"])];
								}
								else
								{
									$valor = 0;	
								}
							}
							else
							{
								$out_desp = trim($regs["AF2_DESCRI"]);
								
								$hi = '';
								
								$hf = '';
								
								$valor = 0;																
							}							
							
							//se outras despesas, deixa campo editavel
							if(trim($regs["AF2_COMPOS"])=='DES99')
							{
								$item = '<input name="itm_'.$i.'" type="text" class="caixa" style="color:#F00" id="itm_'.$i.'" size="50" value="'.$out_desp.'">';
							}
							else
							{
								$item = trim($regs["AF2_DESCRI"]);
							}
							
							$var = '99:99';							
							
							//se carro frota, mostra campos de horario
							if(trim($regs["AF2_COMPOS"])=='DES98')
							{
								$horai = '<input name="horai_'.$i.'" type="text" class="caixa" style="color:#F00" id="horai_'.$i.'" size="10" maxlength="5" value="'.$hi.'" onkeypress=valida(event,this);>';

								$horaf = '<input name="horaf_'.$i.'" type="text" class="caixa" style="color:#F00" id="horaf_'.$i.'" size="10" maxlength="5" value="'.$hf.'" onkeypress=valida(event,this);>';
							}
							else
							{
								$horai = ' ';
								
								$horaf = ' ';
							}						

							
							$inputs = '<input name="qtd_'.$i.'" type="text" class="caixa" style="text-align:right" id="qtd_'.$i.'" size="10" value="'.$valor.'" onkeypress=num_only();>';
							
							$inputs .= '<input name="itensnec_'.$i.'" type="hidden" id="itensnec_'.$i.'"  value="'.trim($regs["AF2_COMPOS"]).'" />';
							
							$inputs .= '<input name="vlruni_'.$i.'" type="hidden" id="vlruni_'.$i.'"  value="'.($regs2["VALOR"]/$regs["AF2_QUANT"]).'" />';
							
							$xml->startElement('row');
								$xml->writeAttribute('id','req_'.$i);		
								$xml->writeElement('cell',$item);
								$xml->writeElement('cell',$regs["AF2_QUANT"]);
								$xml->startElement('cell');
									$xml->writeAttribute('style',$style);
									$xml->text($qtd_vlr);
								$xml->endElement();							
								$xml->writeElement('cell',number_format($regs2["VALOR"],2,',',''));
								$xml->writeElement('cell',$inputs);
								
								$xml->writeElement('cell',$horai);
								$xml->writeElement('cell',$horaf);
								
							$xml->endElement();								
						}
						
						$i++;
					}
					
					$resposta->addAssign("qtd_itensnec","value", $i);		
	
					$xml->endElement();
							
					$conteudo = $xml->outputMemory(false);
				}
				
				$resposta->addScript("grid('div_necessidades',true,'200','".$conteudo."');");
				
			}
		}
		*/	
	}
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$conf = new configs();
	
	$params = array();
	
	$os = NULL;
	
	//contabiliza as requisições não acertadas pelo responsável para não permitir novas requisições
	$sql = "SELECT COUNT(*) AS REGISTROS FROM ".DATABASE.".requisicao_despesas ";
	$sql .= "WHERE requisicao_despesas.responsavel_despesas = '" . $dados_form["responsavel"] . "' ";
	$sql .= "AND requisicao_despesas.status = 2 "; //SOMA TODOS, MENOS OS ACERTADOS
	$sql .= "AND requisicao_despesas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
		
	if($db->erro!='')
	{
		$resposta->addAlert('Erro '.$sql);
	}
	else
	{		
		$cont = $db->array_select[0];
	
		if($cont["REGISTROS"]<=2 || true)//se tiver mais que 2 requisições "penduradas"
		{	
			if ($dados_form["data"]!= "" && strlen($dados_form["data"])==10 && $dados_form["os"]!="" && $dados_form["responsavel"]!="" && $dados_form["cobrar_cliente"]!="")
			{					
				$os = explode("#",$dados_form["os"]);//0 - id_os / 1 - Projeto
				
				//verifica a OS 900
				/*
				if($os[0]==3803)
				{
					if($dados_form["os_destino"]=='')
					{
						$resposta->addAlert('Favor informar a OS de destino da despesa.');
						
						return $resposta;
					}
				}
				*/
				
				//verifica se foi escolhida as necessidades
				$qtd_nec = false;
				
				for($i=1;$i<$dados_form["qtd_itensnec"];$i++)
				{
					if($dados_form["qtd_".$i]!='0')
					{
						$qtd_nec = true;
					}
				}
				
				if($qtd_nec)
				{
					//insere a requisição
					$isql = "INSERT INTO ".DATABASE.".requisicao_despesas ";
					$isql .= "(id_os, os_destino, id_funcionario, data_requisicao, periodo_inicial, periodo_final, atividade, ";
					$isql .= "responsavel_despesas, cobrar_cliente) ";
					$isql .= "VALUES ('" . $os[0] . "', ";
					$isql .= "'" . $dados_form["os_destino"] . "', ";
					$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
					$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
					$isql .= "'" . php_mysql($dados_form["periodo_ini"]) . "', ";
					$isql .= "'" . php_mysql($dados_form["periodo_fim"]) . "', ";
					$isql .= "'" . maiusculas(trim($dados_form["atividade"])) . "', ";
					$isql .= "'" . $dados_form["responsavel"] . "', ";
					$isql .= "'" . $dados_form["cobrar_cliente"] . "') ";

					$db->insert($isql,'MYSQL');
						
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{			
						$id_requisicao = $db->insert_id;
						
						//insere os funcionarios
						for($i=1;$i<=$dados_form["qtd_itens"];$i++)
						{
							if($dados_form["items_".$i]!='')
							{
								$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios ";
								$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '".$id_requisicao."' ";
								$sql .= "AND requisicao_despesas_funcionarios.id_funcionario = '".$dados_form["items_".$i]."' ";
								$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
	
								$db->select($sql,'MYSQL',true);
	
								if($db->erro!='')
								{
									$resposta->addAlert('Erro '.$sql);
								}
								else
								{								
									if($db->numero_registros==0)
									{				
										$isql = "INSERT INTO ".DATABASE.".requisicao_despesas_funcionarios ";
										$isql .= "(id_requisicao_despesa, id_funcionario) ";
										$isql .= "VALUES ('".$id_requisicao."', ";
										$isql .= "'".$dados_form["items_".$i]."') ";
	
										$db->insert($isql,'MYSQL');
	
										if($db->erro!='')
										{
											$resposta->addAlert('Erro '.$isql);
											
											return $resposta;
										}
									}
								}
							}
						}					
						
						//insere os itens de necessidade
						for($i=1;$i<$dados_form["qtd_itensnec"];$i++)
						{
							if($dados_form["qtd_".$i]!='0')
							{
								$qtd_nec = true;
										
								$isql = "INSERT INTO ".DATABASE.".requisicao_despesas_necessidades ";
								$isql .= "(id_requisicao_despesa, cod_necessidade, item, hora_ini, hora_fim, quantidade, valor_unitario) ";
								$isql .= "VALUES ('".$id_requisicao."', ";
								$isql .= "'".$dados_form["itensnec_".$i]."', ";
								
								if($dados_form["itensnec_".$i]=='DES99')
								{
									$isql .= "'".maiusculas($dados_form["itm_".$i])."', ";
								}
								else
								{
									$isql .= "'', ";
								}
								
								$isql .= "'".$dados_form["horai_".$i]."', ";
								$isql .= "'".$dados_form["horaf_".$i]."', ";
								
								$isql .= "'".$dados_form["qtd_".$i]."', ";
								$isql .= "'".$dados_form["vlruni_".$i]."') ";
	
								$db->insert($isql,'MYSQL');
	
								if($db->erro!='')
								{
									$resposta->addAlert('Erro '.$isql);
									
									return $resposta;
								}				
							}
						}
						
						//obtem as despesas cadastradas no orçamento
						/*
						$sql = "SELECT AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
						$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
						$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
						$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$os[1])."' "; 
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
							
							//obtem o cliente
							$sql = "SELECT A1_NOME, A1_MUN FROM AF1010, SA1010 ";
							$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
							$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
							$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
							$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
							$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$os[1])."' ";
							
							$db->select($sql,'MSSQL', true);
		
							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
							else
							{
								$regs_client = $db->array_select[0];
							}					
									
							//coordenador/responsavel pela requisição
							$sql = "SELECT funcionarios.id_funcionario, funcionarios.funcionario, usuarios.email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
							$sql .= "WHERE usuarios.id_funcionario = funcionarios.id_funcionario ";
							$sql .= "AND usuarios.reg_del = 0 ";
							$sql .= "AND funcionarios.reg_del = 0 ";
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
							$texto .='	  <td colspan="5"><strong>Requisição de Despesa </strong></td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">Requisição nº: '.sprintf("%05d",$id_requisicao).'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">data solicitação: '. $dados_form["data"] .' </td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">Solicitante: '. $array_funcionarios[0][$_SESSION["id_funcionario"]] .'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">Responsável pelas despesas/veiculo: '. $array_funcionarios[0][$dados_form["responsavel"]] . '</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">OS: '. sprintf("%010d",$os[1]) .' - '.trim($regs_client["A1_NOME"]).' - '.trim($regs_client["A1_MUN"]).'</td>';
							$texto .=' </tr>';
							
							//se OS 900, informa a contabilidade
							if($os[0]==3803)
							{
								$params['emails']['to'][] = array('email' => "contabilidade@".DOMINIO, 'nome' => "Contabilidade");
								
								$texto .=' <tr>';
								$texto .='	<td colspan="5"><strong><font color="#FF0000">A contabilidade, favor lançar estas despesas na OS a seguir:</font></strong></td>';
								$texto .=' </tr>';
								
								$texto .=' <tr>';
								$texto .='	<td colspan="5"><strong><font color="#FF0000">OS DESTINO: '. $dados_form["os_destino"] .'</font></strong></td>';
								$texto .=' </tr>';
							}							
			
							$texto .=' <tr>';
							$texto .='	<td colspan="5">Atividade/Observação: '. maiusculas($dados_form["atividade"]) .'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5">Período: '. $dados_form["periodo_ini"] . ' a '. $dados_form["periodo_fim"] . '</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5"> </td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td colspan="5" align="center"><strong>Necessidades</strong></td>';
							$texto .=' </tr>';
							
							//filtra as necessidades requisitadas
							$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
							$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $id_requisicao . "' ";
							$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

							$db->select($sql,'MYSQL',true);

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
							else
							{
								$reg2 = $db->array_select;
																	
								$necessidades = '<tr>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>ITEM</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>QUANTIDADE</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>VALOR (R$)</STRONG></TD>';
								
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>HORA INI</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>HORA FIM</STRONG></TD>';
								
								$necessidades .= '</tr>';
								
								$total = 0;
										
								foreach($reg2 as $cont2)
								{
									$total_despesa += $cont2["quantidade"]*$cont2["valor_unitario"];
									
									$necessidades .= '<tr>';
									
									if($cont2["cod_necessidade"]=='DES99')
									{
										$item = $cont2["item"];
									}
									else
									{
										$item = $array_items_desp[$cont2["cod_necessidade"]];	
									}
						
									//if($cont2["cod_necessidade"]=='DES99' || $item!='')
									//{
										$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$item.'</td>';
									//}
									//else
									//{
										//$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$array_items_desp[$cont2["cod_necessidade"]].'</td>';
									//}
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["quantidade"].'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.number_format(($cont2["quantidade"]*$cont2["valor_unitario"]),2,',','').'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["hora_ini"].'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["hora_fim"].'</td>';
									
									
									$necessidades .= '</tr>';
								}				
								
								$cobrar_cliente = $dados_form["cobrar_cliente"]?"SIM":"NÃO";
								
								$texto .= $necessidades;
								
								$texto .=' <tr>';
								$texto .='	<td colspan="5"> </td>';
								$texto .=' </tr>';
								
								$texto .=' <tr>';
								$texto .='	<td colspan="5">Total despesas:R$ '.number_format($total_despesa,2,',','').'</td>';
								$texto .=' </tr>';
								
								$texto .=' <tr>';
								$texto .='	<td colspan="5"> </td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td colspan="5">Despesas cobradas do cliente: '. $cobrar_cliente .'</td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td colspan="5"> </td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td colspan="5" align="center"><strong>Funcionários</strong></td>';
								$texto .=' </tr>';
								
								//filtra os funcionarios da requisição
								$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios, ".DATABASE.".funcionarios ";
								$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '" . $id_requisicao . "' ";
								$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
								$sql .= "AND funcionarios.reg_del = 0 ";
								$sql .= "AND requisicao_despesas_funcionarios.id_funcionario = funcionarios.id_funcionario ";								
								$sql .= "ORDER BY funcionario ";

								$db->select($sql,'MYSQL',true);

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
								else
								{											
									foreach($db->array_select as $cont3)
									{
										$texto .= '	<tr>';
										$texto .= '	 <td colspan="5">'.$cont3["funcionario"].'</td>';
										$texto .= '	</tr>';
									}

									$texto .='</table>';									
									
									$params['from']		= $array_funcionarios[1][$dados_form["responsavel"]];
									$params['from_name']= "REQUISIÇÃO DESPESAS - REQUISIÇÃO";
									$params['subject'] 	= "REQUISIÇÃO DESPESAS - REQUISIÇÃO";
									
									$params['emails']['to'][] = array('email' => "nome1@".DOMINIO, 'nome' => "Nome1");
									//$params['emails']['to'][] = array('email' => "nome2@".DOMINIO, 'nome' => "Nome2");
									
									//se cobrar cliente
									if($dados_form["cobrar_cliente"])
									{
										$params['emails']['to'][] = array('email' => "nome1@".DOMINIO, 'nome' => "Nome1");
									}
									
									if($array_funcionarios[1][$_SESSION["id_funcionario"]]) //solicitante
									{
										$params['emails']['to'][] = array('email' => $array_funcionarios[1][$_SESSION["id_funcionario"]], 'nome' => $array_funcionarios[0][$_SESSION["id_funcionario"]]);
									}
									
									if($array_funcionarios[1][$dados_form["responsavel"]]) //responsavel
									{
										$params['emails']['to'][] = array('email' => $array_funcionarios[1][$dados_form["responsavel"]], 'nome' => $array_funcionarios[0][$dados_form["responsavel"]]);
									}
								
									$mail = new email($params);
									$mail->montaCorpoEmail($texto);
									
									if(!$mail->Send())
									{
										$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
									}
									else
									{
										$resposta->addAlert("Requisição cadastrada com sucesso.");
									}

									$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
									
									$resposta->addScript("xajax_voltar();");
								}
							}
	
						}
						*/
					}
				}
				else
				{
					$resposta->addAlert("Deve quantificar alguma necessidade.");	
				}
			
			}
			else
			{
				$resposta->addAlert("Preencha corretamente todos os campos necessários!");		
			}
		}
		else
		{
			$resposta->addAlert("Você tem 2 ou mais requisições em pendência com o setor Financeiro.\nFavor contatar o setor Financeiro.");	
		}
	}
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".funcionarios, ".DATABASE.".requisicao_despesas ";
	$sql .= "WHERE requisicao_despesas.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND requisicao_despesas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = requisicao_despesas.id_os ";
	$sql .= "AND funcionarios.id_funcionario = requisicao_despesas.responsavel_despesas ";
	$sql .= "AND requisicao_despesas.status = '".$dados_form["status"]."' "; //mostra os requisitados
	$sql .= "AND ordem_servico.id_os_status IN (1,14,16) "; //andamento/sem cronograma/adm	
	$sql .= "GROUP BY requisicao_despesas.id_requisicao_despesa ";
	$sql .= "ORDER BY requisicao_despesas.data_requisicao DESC, requisicao_despesas.id_requisicao_despesa ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert('Erro '.$sql);
	}
	else
	{
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows') ;
							
		foreach($db->array_select as $cont_despesas)
		{
			switch ($cont_despesas["status"])
			{
				case 0:
					$status = 'REQUISITADO';
				break;
				
				case 1:
					$status = 'ADIANTAMENTO';
				break;
				
				case 2:
					$status = 'DESPESAS';
				break;
				
				case 3:
					$status = 'ACERTADO';
				break;
				
				case 4:
					$status = 'REPROVADO';
				break;
					
			}		
			
			//contabiliza os valores das requisição
			$sql = "SELECT SUM(quantidade*valor_unitario) AS valor FROM ".DATABASE.".requisicao_despesas_necessidades ";
			$sql .= "WHERE requisicao_despesas_necessidades.reg_del = 0 ";
			$sql .= "AND requisicao_despesas_necessidades.id_requisicao_despesa = '".$cont_despesas["id_requisicao_despesa"]."' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				$regs_vlr = $db->array_select[0];		
				
				$cobrar_cliente = $cont_despesas["cobrar_cliente"]?"SIM":"NÃO";
				
				if($cont_despesas["status"]==0)
				{
					$delreg = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma a exclusão da requisição selecionada?")){xajax_excluir("' . $cont_despesas["id_requisicao_despesa"] . '");}>';
				}
				else
				{
					$delreg = ' ';
				}
				
				$xml->startElement('row');
					$xml->writeAttribute('id','req_'.$cont_despesas["id_requisicao_despesa"]);
					$xml->writeElement ('cell',sprintf("%05d",$cont_despesas["id_requisicao_despesa"]));
					$xml->writeElement ('cell',sprintf("%010d",$cont_despesas["os"]));
					$xml->writeElement ('cell',mysql_php($cont_despesas["data_requisicao"]));
					$xml->writeElement ('cell',$cont_despesas["atividade"]);
					$xml->writeElement ('cell',$cont_despesas["funcionario"]);
					$xml->writeElement ('cell',substr(mysql_php($cont_despesas["periodo_inicial"]),0,10) . ' á ' . substr(mysql_php($cont_despesas["periodo_final"]),0,10));
					$xml->writeElement ('cell',number_format($regs_vlr["valor"],2));
					$xml->writeElement ('cell',$cobrar_cliente);
					$xml->writeElement ('cell',$status);
					$xml->writeElement ('cell',$delreg);
				$xml->endElement();	
				
			}
	
		}
		
		$xml->endElement();
				
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('requisicao',true,'500','".$conteudo."');");
		
	}

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$db = new banco_dados;
	
	if($conf->checa_permissao(2,$resposta)) //id_sub_modulo acoes = 112
	{		
		$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas ";
		$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $id . "' ";
		$sql .= "AND requisicao_despesas.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert('Erro '.$sql);
		}
		else
		{		
			$cont = $db->array_select[0];		
								
			$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
			$usql .= "requisicao_despesas.reg_del = 1, ";
			$usql .= "requisicao_despesas.data_del = '".date('Y-m-d')."', ";
			$usql .= "requisicao_despesas.reg_who = '".$_SESSION["id_funcionario"]."' ";
			$usql .= "WHERE requisicao_despesas.id_requisicao_despesa = '".$id."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert('Erro '.$sql);
			}
			else
			{					
				$usql = "UPDATE ".DATABASE.".requisicao_despesas_funcionarios SET ";
				$usql .= "requisicao_despesas_funcionarios.reg_del = 1, ";
				$usql .= "requisicao_despesas_funcionarios.data_del = '".date('Y-m-d')."', ";
				$usql .= "requisicao_despesas_funcionarios.reg_who = '".$_SESSION["id_funcionario"]."' ";
				$usql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '".$id."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert('Erro '.$sql);
				}
				else
				{						
					$usql = "UPDATE ".DATABASE.".requisicao_despesas_necessidades SET ";
					$usql .= "requisicao_despesas_necessidades.reg_del = 1, ";
					$usql .= "requisicao_despesas_necessidades.data_del = '".date('Y-m-d')."', ";
					$usql .= "requisicao_despesas_necessidades.reg_who = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '".$id."' ";
					$usql .= "AND reg_del = 0 ";

					$db->update($usql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert('Erro '.$sql);
					}
					else
					{
						//coordenador/responsavel pela requisição
						$sql = "SELECT funcionarios.id_funcionario, funcionarios.funcionario, usuarios.email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
						$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
						$sql .= "AND usuarios.reg_del = 0 ";
						$sql .= "AND funcionarios.reg_del = 0 ";
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
													
						$texto = 'A requisição '.sprintf("%05d",$id).' foi cancelada.';		
					
						$params 			= array();
						$params['from']		= $array_funcionarios[1][$cont["id_funcionario"]];
						$params['from_name']= "REQUISIÇÃO DESPESAS - CANCELAMENTO";
						$params['subject'] 	= "REQUISIÇÃO DESPESAS: ".sprintf("%05d",$id)." - CANCELAMENTO";
						
						$params['emails']['to'][] = array('email' => "financeiro@".DOMINIO, 'nome' => "Financeiro");
						
						if($cont["cobrar_cliente"])
						{
							
						}
						
						if($array_funcionarios[1][$cont["id_funcionario"]]) //SOLICITANTE
						{
							$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["id_funcionario"]], 'nome' => $array_funcionarios[0][$cont["id_funcionario"]]);
						}
						
						if($array_funcionarios[1][$cont["responsavel_despesas"]]) //RESPONSAVEL
						{
							$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["responsavel_despesas"]], 'nome' => $array_funcionarios[0][$cont["responsavel_despesas"]]);
						}
					
						if(ENVIA_EMAIL)
						{

							$mail = new email($params);
							
							$mail->montaCorpoEmail($text);
							
							if(!$mail->Send())
							{
								$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
							}
						}
						else 
						{
							$resposta->addScriptCall('modal', $text, '300_650', 'Conteúdo email', 1);
						}

						$resposta -> addAlert("Excluído com sucesso.");

						$resposta -> addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");														
					}
				}
			}
		}	
	}

	return $resposta;
}

function editar($id_requisicao_despesa)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$temp = explode('_',$id_requisicao_despesa);
	
	$id_requisicao_despesa = $temp[1];

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$conteudo = "";
	
	$conteudo_func = "";
	
	$hora_i = '';
	
	$hora_f = '';
	
	if($id_requisicao_despesa!='')
	{		
		//monta o array funcionários
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.reg_del = 0 ";
		$sql .= "ORDER BY funcionario ";

		$db->select($sql,'MYSQL',true);
		
		foreach($db->array_select as $cont)
		{
			$array_func[$cont["id_funcionario"]] = $cont["funcionario"];
		}		
			
		//Filtra as requisicao e as OS
		$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
		$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $id_requisicao_despesa . "' ";
		$sql .= "AND requisicao_despesas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
		$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";		

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{				
			$cont = $db->array_select[0];
			
			if(in_array($cont["status"],array('0','4')))
			{
				$resposta->addAssign("id_requisicao_despesa", "value", $id_requisicao_despesa);
				
				$resposta->addAssign("num_sol","innerHTML","<strong>Nº :</strong><br>".sprintf("%04d",$id_requisicao_despesa));
				
				$resposta->addAssign("nome_func","innerHTML",$array_func[$cont["id_funcionario"]]);
				
				$resposta->addAssign("data","value",date('d/m/Y'));			
				
				$resposta->addScript("seleciona_combo('".$cont["id_os"]."#".sprintf("%010d",$cont["os"])."','os');");
				
				$resposta->addAssign("atividade","value",$cont["atividade"]);
				
				$resposta->addAssign("periodo_ini","value",mysql_php($cont["periodo_inicial"]));
				
				$resposta->addAssign("periodo_fim","value",mysql_php($cont["periodo_final"]));
				
				$resposta->addScript("seleciona_combo('".$cont["responsavel_despesas"]."','responsavel');");
				
				if($cont["cobrar_cliente"])
				{
					$resposta->addScript("document.getElementById('radio1').checked=true");
					$resposta->addScript("document.getElementById('radio2').checked=false");
				}
				else
				{
					$resposta->addScript("document.getElementById('radio1').checked=false");
					$resposta->addScript("document.getElementById('radio2').checked=true");			
				}
				
				//filtra os funcionarios da requisição
				$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios ";
				$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '" . $id_requisicao_despesa . "' ";
				$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
	
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$array_func_desp = $db->array_select;
				
				$cont_num = $db->numero_registros;
				
				$resposta->addAssign("qtd_itens","value","1");
				
				$j = 10;
				
				//remove os combos
				for($i=1;$i<10;$i++)
				{
					$resposta->addScript("para1=document.getElementById('items_".$j."');noPai1=para1.parentNode;noPai1.removeChild(para1);");
					
					$resposta->addScript("para2=document.getElementById('divcontr_".$j."');noPai2=para2.parentNode;noPai2.removeChild(para2);");
					
					$j--;				
				}			
				
				//adiciona os combos
				for($i=1;$i<$cont_num;$i++)
				{
					$resposta->addScript("add_controles('div_colaborador','divcontr_1','items_1','qtd_itens')");
				}
				
				$i = 1;
				
				//seleciona os funcionarios
				foreach($array_func_desp as $func)
				{
					$resposta->addScript("seleciona_combo('".$func["id_funcionario"]."','items_".$i."');");
				
					$i++;				
				}
				
				$resposta->addScript("xajax_despesas(xajax.getFormValues('frm'));");
				
				$resposta->addScript("document.getElementById('btninserir').disabled=false");
				
				$resposta->addAssign("btninserir", "value", "Atualizar");
			
				$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
			}
			else
			{
				$resposta->addAssign("necessidades","innerHTML","");
				
				$resposta->addAssign("itens_nec","innerHTML","");		
				
				$resposta->addAssign("funcionarios","innerHTML","");
				
				$resposta->addAssign("dv_acerto_despesas","innerHTML","");
				
				$resposta->addAssign("div_acerto","innerHTML","");	
				
				$resposta->addAssign("div_button","innerHTML","");
			}
		}	
	}
	else
	{	
		$resposta->addAssign("necessidades","innerHTML","");
		
		$resposta->addAssign("itens_nec","innerHTML","");		
		
		$resposta->addAssign("funcionarios","innerHTML","");
		
		$resposta->addAssign("dv_acerto_despesas","innerHTML","");
		
		$resposta->addAssign("div_acerto","innerHTML","");	
		
		$resposta->addAssign("div_button","innerHTML","");
	}	

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$db = new banco_dados;
	
	$msg = $conf->msg($resposta);
	
	$os = NULL;
	
	//contabiliza as requisições não acertadas pelo responsável para não permitir novas requisições
	$sql = "SELECT COUNT(*) AS REGISTROS FROM ".DATABASE.".requisicao_despesas ";
	$sql .= "WHERE requisicao_despesas.responsavel_despesas = '" . $dados_form["responsavel"] . "' ";
	$sql .= "AND requisicao_despesas.status = 2 "; //SOMA TODOS, MENOS OS ACERTADOS
	$sql .= "AND requisicao_despesas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
		
	if($db->erro!='')
	{
		$resposta->addAlert('Erro '.$sql);
	}
	else
	{		
		$cont = $db->array_select[0];
	
		if($cont["REGISTROS"]<=2 || true)//se tiver mais que 2 requisições "penduradas"
		{	
			if ($dados_form["data"]!= "" && strlen($dados_form["data"])==10 && $dados_form["os"]!="" && $dados_form["responsavel"]!="" && $dados_form["cobrar_cliente"]!="")
			{					
				$os = explode("#",$dados_form["os"]);//0 - id_os / 1 - Projeto
				
				//verifica se foi escolhida as necessidades
				$qtd_nec = false;
				
				for($i=1;$i<$dados_form["qtd_itensnec"];$i++)
				{
					if($dados_form["qtd_".$i]!='0')
					{
						$qtd_nec = true;
					}
				}
				
				if($qtd_nec)
				{
					//altera a requisição
					$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
					$usql .= "id_os = '".$os[0]."', ";
					$usql .= "data_requisicao = '".php_mysql($dados_form["data"])."', ";
					$usql .= "periodo_inicial = '".php_mysql($dados_form["periodo_ini"])."', ";
					$usql .= "periodo_final = '".php_mysql($dados_form["periodo_fim"])."', ";
					$usql .= "atividade = '".maiusculas(trim($dados_form["atividade"]))."', ";
					$usql .= "responsavel_despesas = '".$dados_form["responsavel"]."', ";
					$usql .= "cobrar_cliente = '".$dados_form["cobrar_cliente"]."', ";
					$usql .= "status = '0' "; //requisitado
					$usql .= "WHERE id_requisicao_despesa = '".$dados_form["id_requisicao_despesa"]."' ";
					$usql .= "AND reg_del = 0 ";

					$db->update($usql,'MYSQL');
						
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{
						//exclui os funcionarios para que sejam incluidos
						$usql = "UPDATE ".DATABASE.".requisicao_despesas_funcionarios SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "data_del = '".date('Y-m-d')."', ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
						$usql .= "WHERE id_requisicao_despesa = '".$dados_form["id_requisicao_despesa"]."' ";
						$usql .= "AND reg_del = 0 ";
							
						$db->update($usql,'MYSQL');
							
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
									
						//insere os funcionarios
						for($i=1;$i<=$dados_form["qtd_itens"];$i++)
						{
							if($dados_form["items_".$i]!='')
							{
								$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios ";
								$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '".$dados_form["id_requisicao_despesa"]."' ";
								$sql .= "AND requisicao_despesas_funcionarios.id_funcionario = '".$dados_form["items_".$i]."' ";
								$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
	
								$db->select($sql,'MYSQL',true);
	
								if($db->erro!='')
								{
									$resposta->addAlert('Erro '.$sql);
								}
								else
								{
									//se não tem no banco, insere							
									if($db->numero_registros==0)
									{				
										$isql = "INSERT INTO ".DATABASE.".requisicao_despesas_funcionarios ";
										$isql .= "(id_requisicao_despesa, id_funcionario) ";
										$isql .= "VALUES ('".$dados_form["id_requisicao_despesa"]."', ";
										$isql .= "'".$dados_form["items_".$i]."') ";
	
										$db->insert($isql,'MYSQL');
	
										if($db->erro!='')
										{
											$resposta->addAlert('Erro '.$isql);
											
											return $resposta;
										}
									}
								}
							}
						}					
						
						//insere os itens de necessidade
						for($i=1;$i<$dados_form["qtd_itensnec"];$i++)
						{
							if($dados_form["qtd_".$i]!='0')
							{
								$qtd_nec = true;
								
								//verifica se já existe o item cadastrado
								$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
								$sql .= "WHERE requisicao_despesas_necessidades.reg_del = 0 ";
								$sql .= "AND requisicao_despesas_necessidades.id_requisicao_despesa = '".$dados_form["id_requisicao_despesa"]."' ";
								$sql .= "AND requisicao_despesas_necessidades.cod_necessidade = '".$dados_form["itensnec_".$i]."' ";
								
								$db->select($sql,'MYSQL',true);
									
								if($db->erro!='')
								{
									$resposta->addAlert('Erro '.$sql);
								}
								
								$array_nec = $db->array_select[0];
								
								if($dados_form["itensnec_".$i]=='DES99')
								{
									$item = maiusculas($dados_form["itm_".$i]);
								}
								else
								{
									$item = '';
								}
								
								//se não possuir no banco
								if($db->numero_registros==0)
								{										
									$isql = "INSERT INTO ".DATABASE.".requisicao_despesas_necessidades ";
									$isql .= "(id_requisicao_despesa, cod_necessidade, item, hora_ini, hora_fim, quantidade, valor_unitario) ";
									$isql .= "VALUES ('".$dados_form["id_requisicao_despesa"]."', ";
									$isql .= "'".$dados_form["itensnec_".$i]."', ";
									$isql .= "'".$item."', ";									
									$isql .= "'".$dados_form["horai_".$i]."', ";
									$isql .= "'".$dados_form["horaf_".$i]."', ";									
									$isql .= "'".$dados_form["qtd_".$i]."', ";
									$isql .= "'".$dados_form["vlruni_".$i]."') ";
		
									$db->insert($isql,'MYSQL');
		
									if($db->erro!='')
									{
										$resposta->addAlert('Erro '.$isql);
										
										return $resposta;
									}
								}
								else
								{
									
									$usql = "UPDATE ".DATABASE.".requisicao_despesas_necessidades SET ";
									$usql .= "item = '".$item."', ";									
									$usql .= "hora_ini = '".$dados_form["horai_".$i]."', ";
									$usql .= "hora_fim = '".$dados_form["horaf_".$i]."', ";
									$usql .= "quantidade = '".$dados_form["qtd_".$i]."', ";
									$usql .= "valor_unitario = '".$dados_form["vlruni_".$i]."' ";
									$usql .= "WHERE id_requisicao_despesas_necessidade = '".$array_nec["id_requisicao_despesas_necessidade"]."' ";
									$usql .= "AND reg_del = 0 ";
									
									$db->update($usql,'MYSQL');
		
									if($db->erro!='')
									{
										$resposta->addAlert('Erro '.$usql);
										
										return $resposta;
									}
										
								}
							}
						}
						
						/*
						//obtem as despesas cadastradas no orçamento
						$sql = "SELECT AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
						$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
						$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
						$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$os[1])."' "; 
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
							
							//obtem o cliente
							$sql = "SELECT A1_NOME, A1_MUN FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
							$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
							$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
							$sql .= "AND AF1010.AF1_CLIENT = SA1010.A1_COD ";
							$sql .= "AND AF1010.AF1_LOJA = SA1010.A1_LOJA ";
							$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$os[1])."' ";
							
							$db->select($sql,'MSSQL', true);
		
							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
							else
							{
								$regs_client = $db->array_select[0];
							}					
									
							//coordenador/responsavel pela requisição
							$sql = "SELECT funcionarios.id_funcionario, funcionarios.funcionario, usuarios.email FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
							$sql .= "WHERE usuarios.id_funcionario = funcionarios.id_funcionario ";
							$sql .= "AND usuarios.reg_del = 0 ";
							$sql .= "AND funcionarios.reg_del = 0 ";
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
							$texto .='	  <td><strong>Requisição de Despesa </strong></td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>Requisição nº: '.sprintf("%05d",$dados_form["id_requisicao_despesa"]).'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>data solicitação: '. $dados_form["data"] .' </td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>Solicitante: '. $array_funcionarios[0][$_SESSION["id_funcionario"]] .'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>Responsável pelas despesas/veiculo: '. $array_funcionarios[0][$dados_form["responsavel"]] . '</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>OS: '. sprintf("%010d",$os[1]) .' - '.trim($regs_client["A1_NOME"]).' - '.trim($regs_client["A1_MUN"]).'</td>';
							$texto .=' </tr>';
			
							$texto .=' <tr>';
							$texto .='	<td>Atividade/Observação: '. maiusculas($dados_form["atividade"]) .'</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td>Período: '. $dados_form["periodo_ini"] . ' a '. $dados_form["periodo_fim"] . '</td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td> </td>';
							$texto .=' </tr>';
							$texto .=' <tr>';
							$texto .='	<td align="center"><strong>Necessidades</strong></td>';
							$texto .=' </tr>';
							
							//filtra as necessidades requisitadas
							$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_necessidades ";
							$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $dados_form["id_requisicao_despesa"] . "' ";
							$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

							$db->select($sql,'MYSQL',true);

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}
							else
							{
								$reg2 = $db->array_select;
																	
								$necessidades = '<tr>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>ITEM</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>QUANTIDADE</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>VALOR (R$)</STRONG></TD>';
								
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>HORA INI</STRONG></TD>';
								$necessidades .= '<td style="text-align:center;border:solid; border-color:#000; border-width:1px;"><strong>HORA FIM</STRONG></TD>';
								
								$necessidades .= '</tr>';
								
								$total = 0;
										
								foreach($reg2 as $cont2)
								{
									$total_despesa += $cont2["quantidade"]*$cont2["valor_unitario"];
									
									$necessidades .= '<tr>';
									
									if($cont2["cod_necessidade"]=='DES99')
									{
										$item = $cont2["item"];
									}
									else
									{
										$item = $array_items_desp[$cont2["cod_necessidade"]];	
									}

									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$item.'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["quantidade"].'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.number_format(($cont2["quantidade"]*$cont2["valor_unitario"]),2,',','').'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["hora_ini"].'</td>';
									
									$necessidades .= '	 <td style="text-align:center;border:solid; border-color:#CCC; border-width:1px;">'.$cont2["hora_fim"].'</td>';
									
									
									$necessidades .= '</tr>';
								}				
								
								$cobrar_cliente = $dados_form["cobrar_cliente"]?"SIM":"NÃO";
								
								$texto .= $necessidades;
								
								$texto .=' <tr>';
								$texto .='	<td> </td>';
								$texto .=' </tr>';
								
								$texto .=' <tr>';
								$texto .='	<td>Total despesas:R$ '.number_format($total_despesa,2,',','').'</td>';
								$texto .=' </tr>';
								
								$texto .=' <tr>';
								$texto .='	<td> </td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td>Despesas cobradas do cliente: '. $cobrar_cliente .'</td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td> </td>';
								$texto .=' </tr>';
								$texto .=' <tr>';
								$texto .='	<td align="center"><strong>Funcionários</strong></td>';
								$texto .=' </tr>';
								
								//filtra os funcionarios da requisição
								$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios, ".DATABASE.".funcionarios ";
								$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '" . $dados_form["id_requisicao_despesa"] . "' ";
								$sql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
								$sql .= "AND funcionarios.reg_del = 0 ";
								$sql .= "AND requisicao_despesas_funcionarios.id_funcionario = funcionarios.id_funcionario ";								
								$sql .= "ORDER BY funcionario ";

								$db->select($sql,'MYSQL',true);

								if($db->erro!='')
								{
									$resposta->addAlert($db->erro);
								}
								else
								{											
									foreach($db->array_select as $cont3)
									{
										$texto .= '	<tr>';
										$texto .= '	 <td>'.$cont3["funcionario"].'</td>';
										$texto .= '	</tr>';
									}

									$texto .='</table>';
									
									$params 			= array();
									$params['from']		= $array_funcionarios[1][$dados_form["responsavel"]];
									$params['from_name']= "REQUISIÇÃO DESPESAS - REQUISIÇÃO - ALTERAÇÃO";
									$params['subject'] 	= "REQUISIÇÃO DESPESAS - REQUISIÇÃO - ALTERAÇÃO";
									
									$params['emails']['to'][] = array('email' => "nome1@".DOMINIO, 'nome' => "Nome 1");
									//$params['emails']['to'][] = array('email' => "nome2@".DOMINIO, 'nome' => "Nome 2");
									
									//se cobrar cliente
									if($dados_form["cobrar_cliente"])
									{
										$params['emails']['to'][] = array('email' => "nome3@".DOMINIO, 'nome' => "Nome 3");
									}
									
									if($array_funcionarios[1][$_SESSION["id_funcionario"]]) //solicitante
									{
										$params['emails']['to'][] = array('email' => $array_funcionarios[1][$_SESSION["id_funcionario"]], 'nome' => $array_funcionarios[0][$_SESSION["id_funcionario"]]);
									}
									
									if($array_funcionarios[1][$dados_form["responsavel"]]) //responsavel
									{
										$params['emails']['to'][] = array('email' => $array_funcionarios[1][$dados_form["responsavel"]], 'nome' => $array_funcionarios[0][$dados_form["responsavel"]]);
									}
								
									$mail = new email($params);
									$mail->montaCorpoEmail($texto);
									
									if(!$mail->Send())
									{
										$resposta->addAlert("Erro ao enviar e-mail!!! ".$mail->ErrorInfo);
									}
									else
									{
										$resposta->addAlert("Requisição cadastrada com sucesso.");
									}

									$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
									
									$resposta->addScript("xajax_voltar();");
								}
							}
	
						}
						*/

					}
				}
				else
				{
					$resposta->addAlert("Deve quantificar alguma necessidade.");	
				}
			
			}
			else
			{
				$resposta->addAlert("Preencha corretamente todos os campos necessários!");		
			}
		}
		else
		{
			$resposta->addAlert("Você tem 2 ou mais requisições em pendência com o setor Financeiro.\nFavor contatar o setor Financeiro.");	
		}
	}

	$resposta->addScript("xajax_voltar();");	

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("despesas");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("os_destino");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela(xajax.getFormValues('frm'));");

$db = new banco_dados;

$conf = new configs();

$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_funcionario_values[] = "0";
$array_funcionario_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont)
{
	$array_funcionario_values[] = $cont["id_funcionario"];
	$array_funcionario_output[] = $cont["funcionario"];
}

$array_os_values[] = "";
$array_os_output[] = "SELECIONE O PROJETO";

/*
$sql = "SELECT AF8_PROJET, AF8_REVISA, AF8_DESCRI  FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.AF9_COMPOS <> '' ";
$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";

if(!in_array($_SESSION["id_funcionario"],array(6,226,12)))
{
	$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$_SESSION["id_funcionario"])."' ";
}

$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET  ";
$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA  ";
$sql .= "AND AF8010.AF8_FASE IN ('03','09','07') "; //andamento e adm e sem crono OR AF8010.AF8_FASE = '09'

$sql .= "AND AF8_PROJET NOT IN ('0000000801','0000000998','0000000803','0000001501','0000001301') ";

$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI  ";
$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";

$db->select($sql,'MSSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{	
	$os = intval($regs["AF8_PROJET"]); //retira os zeros a esquerda
	
	$sql = "SELECT * FROM  ".DATABASE.".OS ";
	$sql .= "WHERE os.os = '". (string)$os."' ";
	$sql .= "AND OS.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];

	$array_os_values[] = $regs1["id_os"]."#".trim($regs["AF8_PROJET"]);
	$array_os_output[] = trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);
}
*/

$smarty->assign("revisao_documento","V9");

$smarty->assign("campo",$conf->campos('requisitar_despesas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_funcionario",$_SESSION["nome_usuario"]);									
			
$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_resp_values",$array_funcionario_values);
$smarty->assign("option_resp_output",$array_funcionario_output);

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("classe",CSS_FILE);
			
$smarty->assign("nome_formulario","REQUISITAR DESPESAS");

$smarty->display('requisitar_despesas.tpl');


?>
	
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function valida(e_event,cmp)
{
	return txtBoxFormat(document.getElementById('frm'), cmp.id, "99:99",e_event);	
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");

	myTabbar.addTab("a1_", "Dados");
	myTabbar.addTab("a2_", "Necessidades");
	
	myTabbar.tabs("a1_").attachObject("a1");
	myTabbar.tabs("a2_").attachObject("a2");
	
	myTabbar.tabs("a1_").setActive();
	
	myTabbar.enableAutoReSize(true);
	
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch (tabela)
	{
		case 'requisicao':
		
			function doOnRowSelected1(row,col)
			{
				if(col<=8)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);	
			
			mygrid.setHeader("Nº, Projeto, Data solic., Atividade/Obs., Responsável, Período, valor, Cobrar cliente, Status, D",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("60,80,80,*,*,*,*,80,80,25");
			mygrid.setColAlign("center,center,left,left,left,left,right,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
		break;
		
		case 'div_necessidades':	

			mygrid.setHeader("Item, Qtd. Orç., Qtd Proj., Valor Orç., Qtd. Solic., Hora ini., Hora fim",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("*,*,*,*,*,*,*");
			mygrid.setColAlign("center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");
		break;
	
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
	
}

//calcula os valores inseridos e o orçado
function calc()
{
	var i, qtd_item, qtd, vlr, total, orcado, solicitado;
	
	total = 0;
	
	qtd = 0;
	
	vlr = 0;
	
	orcado = 0;
	
	solicitado = 0;
	
	qtd_item = document.getElementById('qtd_itensnec').value;
	
	orcado = document.getElementById('vlr_orc').value;
	
	solicitado = document.getElementById('vlr_sol').value;
	
	for(i=1;i<qtd_item;i++)
	{
		if(document.getElementById('qtd_'+i).value!=0)
		{
			qtd = document.getElementById('qtd_'+i).value;
			
			vlr = document.getElementById('vlruni_'+i).value;
			
			total += qtd*vlr;
		}
	}

	solicit = parseFloat(solicitado)+parseFloat(total);
	
	if(parseFloat(solicit)>parseFloat(orcado))
	{
		if(confirm('Os valores das despesas excedem ao valor orçado inicialmente, deseja continuar?'))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	else
	{
		return true		
	}

}

function inserir()
{
	//verifica se os valores estão dentro do orçado
	if(calc())
	{
		if(confirm('Deseja enviar a requisição de despesas para o setor Financeiro?'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

//função que adiciona campos no div
function add_controles(div_container,div_control,combo_orig,qtd)
{	
	var t,z,w,id_item,sel,texto, combo,dv,ndv;
	
	t = document.getElementById(combo_orig).name;
	
	w = t.split('_');
	
	dv = document.getElementById(div_control).id;
	
	ndv = dv.split('_');
	
	id_item = document.getElementById(qtd).value;

	for (z = 0; z < document.getElementById(combo_orig).length; z++) 
	{	
		sel = document.getElementById(combo_orig).innerHTML;	
	}
	
	id_item++;
	
	texto = document.getElementById(div_container).innerHTML;
	
	combo = "<div id='"+ndv[0]+"_"+id_item+"' style='float:left;width:100%;'><select name='"+w[0]+"_"+id_item+"' id='"+w[0]+"_"+id_item+"' class='caixa' onkeypress='return keySort(this);'>"+sel+"</select></div>";
	
	document.getElementById(div_container).innerHTML = texto + combo;
	
	document.getElementById(w[0]+"_"+id_item).selectedIndex="0";
	
	document.getElementById(qtd).value = id_item;

}

function remove_controles(div_control,qtd)
{
	var id_item, para1, noPai1, dv, ndv;
	
	dv = document.getElementById(div_control).id;
	
	ndv = dv.split('_');
	
	id_item = document.getElementById(qtd).value;
	
	if(id_item>1)
	{
		para1  = document.getElementById(ndv[0]+"_"+id_item);
		
		noPai1 = para1.parentNode;
		
		noPai1.removeChild(para1);
		
		id_item--;
		
		document.getElementById(qtd).value = id_item;
	}

}

function remove_controls(div_control,qtd)
{
	var id_item, dv, ndv;
	
	id_item = document.getElementById(qtd).value;
	
	dv = document.getElementById(div_control).id;
	
	ndv = dv.split('_');	
	
	for(i=id_item;i>0;i--)
	{
		remove_controles(ndv[0]+'_'+i,qtd);
	}
	
	document.getElementById(qtd).value = 1;		
}

</script>