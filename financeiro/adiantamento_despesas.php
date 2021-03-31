<?php
/*
		Formulário de Adiantamento Despesas	
		
		Criado por Carlos Abreu   
		
		local/Nome do arquivo:
		../financeiro/adiantamento_despesas.php
		
		Versão 0 --> VERSÃO INICIAL (23/02/2007)
		Versão 1 --> Atualização rotinas banco de dados, implementação templates Smarty (03/07/2008)
		Versão 2 --> Atualização layout : 06/11/2013 - Carlos Abreu
		Versão 3 --> Mudança na forma de requisitar despesas - 13/06/2014 - Carlos Abreu
		Versão 4 --> Melhorias GRID - 03/08/2015 - Carlos Abreu
		Versão 5 --> Alterações das informações despesas - 30/05/2016 - Carlos Abreu
		Versão 6 --> Alterações das imagens - 12/07/2016 - Carlos Abreu
		Versão 7 --> atualização layout - Carlos Abreu - 27/03/2017
		Versão 8 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
		Versão 9 --> Adicionados filtros pedidos pela   em - 01/12/2017 - Carlos Eduardo
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(330))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addAssign("id_requisicao_despesa", "value", "");

	$resposta->addAssign("local","value","");
	
	$resposta->addAssign("modelo","value","");
	
	$resposta->addAssign("placa","value","");
	
	$resposta->addAssign("data","value",date('d/m/Y'));
	
	$resposta->addAssign("valor","value","0");
	
	$resposta->addAssign("necessidades","innerHTML","");
	
	$resposta->addAssign("itens_nec","innerHTML","");		
	
	$resposta->addAssign("funcionarios","innerHTML","");
	
	$resposta->addAssign("dv_acerto_despesas","innerHTML","");
	
	$resposta->addAssign("div_acerto","innerHTML","");	
	
	$resposta->addAssign("div_button","innerHTML","");
	
	$resposta->addAssign("btninserir", "disabled", "true");
			
	$resposta->addAssign("btninserir", "value", "Atualizar");	
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar","onclick","history.back();");
	
	return $resposta;
}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('adiantamento_despesas',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados();

	//Novos filtros
	$filtros = '';
	if (!empty($dados_form['os']))
	{
	    $filtros .= ' AND ordem_servico.id_os = '.intval($dados_form['os']);
	}
	
	if (!empty($dados_form['funcionario']))
	{
	    $filtros .= ' AND id_funcionario = '.intval($dados_form['funcionario']);
	}
	
	if (!empty($dados_form['data_adiantamento']))
	{
	    $filtros .= " AND data_adiantamento = '".php_mysql($dados_form['data_adiantamento'])."'";
	}
	
	//apresenta os registros
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".requisicao_despesas, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE funcionarios.id_funcionario = requisicao_despesas.responsavel_despesas ";
	$sql .= "AND requisicao_despesas.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND requisicao_despesas.status = '".$dados_form["status"]."' ";
	$sql .= "AND requisicao_despesas.id_os = ordem_servico.id_os ";
	$sql .= $filtros.' ';
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
		$xml->startElement('rows') ;
		
		$reg = $db->array_select;
			
		foreach($reg as $cont_desp)
		{			
			//sumariza a requisicao
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
				
				$imprimir = " ";	
				
				switch ($cont_desp["status"])
				{
					case 0:
						$status = 'REQUISITADO';
					break;
					
					case 1:
						$status = 'ADIANTAMENTO';
						
						$imprimir = '<img src="'.DIR_IMAGENS.'impressora.png" onclick=imprimir("'.$cont_desp["id_requisicao_despesa"].'",0); align="center"  style="cursor:pointer;" title="Clique para imprimir">';
					break;
					
					case 2:
						$status = 'DESPESAS';
					break;
					
					case 3:
						$status = 'ACERTADO';
					break;
					
					case 4:
						$status = 'REJEITADO';
					break;				
				}
				
				if($cont_desp["status"]!=3)
				{
					$del = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma a exclusão da requisição selecionada?")){xajax_excluir("' . $cont_desp["id_requisicao_despesa"] . '");}>';
				}
				else
				{
					$del = ' ';
				}
				
				$xml->startElement('row');
					$xml->writeAttribute('id','req_'.$cont_desp["id_requisicao_despesa"]);
					$xml->writeElement ('cell'," ");
					$xml->writeElement ('cell',sprintf("%05d",$cont_desp["id_requisicao_despesa"]));
					$xml->writeElement ('cell',sprintf("%010d",$cont_desp["os"]));
					$xml->writeElement ('cell',mysql_php($cont_desp["data_adiantamento"]));
					$xml->writeElement ('cell',$cont_desp["atividade"]);
					$xml->writeElement ('cell',$cont_desp["funcionario"]);
					$xml->writeElement ('cell',substr(mysql_php($cont_desp["periodo_inicial"]),0,10) . ' á ' . substr(mysql_php($cont_desp["periodo_final"]),0,10));
					$xml->writeElement ('cell',number_format($cont_desp["valor_adiantamento"],2,",","."));
					$xml->writeElement ('cell',number_format($cont["total_despesas"],2,",","."));
					$xml->writeElement ('cell',$status);					
					$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'procurar.png" onclick=imprimir("'.$cont_desp["id_requisicao_despesa"].'",1); align="center"  style="cursor:pointer;" title="Clique para imprimir">');
					$xml->writeElement ('cell',$imprimir);
					$xml->writeElement ('cell',$del);
				$xml->endElement();
				
				
			}
		}
		
		$xml->endElement();
				
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addScript("grid('adiantamento_despesas',true,'350','".$conteudo."');");
		
		$resposta->addScript("combo('');");	
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
		
			$resposta->addAssign("id_requisicao_despesa", "value", $id_requisicao_despesa);
			
			/*
			//obtem as despesas cadastradas no orçamento	
			$sql = "SELECT AF2010.AF2_ORCAME, AF2010.AF2_TAREFA, AF2010.AF2_COMPOS, AF2010.AF2_DESCRI, AF2010.AF2_QUANT FROM AF1010 WITH(NOLOCK), AF2010 WITH(NOLOCK) ";
			$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF1010.AF1_ORCAME = '".sprintf("%010d",$cont["os"])."' "; 
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
				$cont1 = $db->array_select;
						
				foreach($cont1 as $regs1)
				{
					$sql = "SELECT SUM(AF4_VALOR) AS VALOR FROM AF4010 WITH(NOLOCK) ";
					$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
					$sql .= "AND AF4_ORCAME = '".$regs1["AF2_ORCAME"]."' "; 
					$sql .= "AND AF4_TAREFA = '".$regs1["AF2_TAREFA"]."' ";

					$db->select($sql,'MSSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
					else
					{			
						$regs_orc1 = $db->array_select[0];
						
						$array_items_qtd[trim($regs1["AF2_COMPOS"])] += $regs1["AF2_QUANT"];
						
						$array_items_vlr[trim($regs1["AF2_COMPOS"])] += $regs_orc1["VALOR"];

					}
					
					$array_items_desp[trim($regs1["AF2_COMPOS"])] = trim($regs1["AF2_DESCRI"]);					
					
				}
				
				//obtem O TOTAL DO ORCAMENTO
				$sql = "SELECT SUM(AF4_VALOR) AS ORCADO FROM AF4010 WITH(NOLOCK) ";
				$sql .= "WHERE AF4010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF4010.AF4_ORCAME = '".sprintf("%010d",$cont["os"])."' "; 
			
				$db->select($sql,'MSSQL',true);
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{		
					$regs_orc = $db->array_select[0];
				}						
				
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
					$reg2 = $db->array_select;
											
					foreach($reg2 as $cont2)
					{
						$array_qtd[$cont2["cod_necessidade"]] += $cont2["quantidade"];
						
						$array_vlr[$cont2["cod_necessidade"]] += $cont2["valor_despesa"];
						
						if($cont2["cod_necessidade"]=='DES99')
						{
							$item[$cont2["cod_necessidade"]] = $cont2["item"];
						}
						
						if($cont2["cod_necessidade"]=='DES98')
						{
							$hora[$cont2["cod_necessidade"]]['hora_ini'] = $cont2["hora_ini"];
							
							$hora[$cont2["cod_necessidade"]]['hora_fim'] = $cont2["hora_fim"];
						}
						
					}
							
					$cobrar_cliente = $cont["cobrar_cliente"]?"SIM":"NÃO";
					
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
						
						//monta a tabela de necessidades
						$header = '<table width="100%" border="0">';
						
						$footer = '</table>';
						
						$conteudo = '	<tr>';
						$conteudo .= '	 <td colspan="2"><label class="labels"><strong>Projeto: </strong>'.sprintf("%010d",$cont["os"]).' - '.$cont["descricao"].'</label></td>';
						$conteudo .= '	</tr>';
						
						if($cont["id_os"]==3803)//se OS 900
						{
							$conteudo .= '	<tr>';
							$conteudo .= '	 <td colspan="2"><label class="labels"><font color="#FF0000"><strong>OS destino: </strong>'.$cont["os_destino"].'</font></label></td>';
							$conteudo .= '	</tr>';
						}
						
						$conteudo .= '	<tr>';
						$conteudo .= '	 <td colspan="2"><label class="labels"><strong>Cliente: </strong>'.$cont["empresa"]. ' - '.$cont["cidade"].'</label></td>';
						$conteudo .= '	</tr>';
						
						$conteudo .= '	<tr>';
						$conteudo .= '	 <td colspan="2"><label class="labels"><strong>valor orçado: R$ </strong>'.number_format($regs_orc["ORCADO"],2,',','').'</label></td>';
						$conteudo .= '	</tr>';
						
						$conteudo .= '	<tr>';
						$conteudo .= '	 <td colspan="2"><label class="labels"><strong>valor consumido: R$ </strong>'. number_format($regs_vlr["valor"],2,',','').'</label></td>';
						$conteudo .= '	</tr>';
						
						$conteudo .= '	<tr>';
						$conteudo .= '	 <td width="10%"><label for="valor" class="labels"><strong>valor adiantamento:</strong></label><br><input name="valor" type="text" class="caixa" id="valor" value="'.number_format($cont["valor_adiantamento"],2,',','').'" size="8" maxlength="7" onkeyup=xajax_virgula_ponto("valor",this.value)></td>';
						$conteudo .= '	 <td><label class="labels"><strong>data</strong></label><br><input name="data" type="text" class="caixa" id="data" size="10" onkeypress=transformaData(this, event); onkeyup=return autoTab(this, 10); value="'.date('d/m/Y').'" onblur=return checaTamanhoData(this,10); /></td>';
						$conteudo .= '	</tr>';
				
						$conteudo .= '	<tr>';
						$conteudo .= '	 <td colspan="2"><label class="labels"><strong>Cobrar cliente:</strong> '.$cobrar_cliente.'</td>';
						$conteudo .= '	</tr>';
						
						$resposta->addAssign("necessidades","innerHTML",$header.$conteudo.$footer);
						
						$xml->openMemory();
						$xml->setIndent(false);
						$xml->startElement('rows') ;
						
						$i = 1;
						
						foreach ($array_qtd as $codigo=>$qtd)
						{
							if($codigo=='DES98') //COMBUSTIVEL (DEVE SER CARRO FROTA)
							{
								$item_desp = $array_items_desp[$codigo];
								
								$modelo = '<input type="text" name="modelo" class="caixa" id="modelo" size="20" value="'.$cont["veiculo_modelo"].'">';
																
								$placa = '<input type="text" name="placa" class="caixa" id="placa" size="10" value="'.$cont["veiculo_placa"] .'" maxlength="8" onkeypress=valida(event,this);>';
								
								$hora_i = $hora[$codigo]['hora_ini'];
								
								$hora_f = $hora[$codigo]['hora_fim'];
							}
							else
							{

								if($codigo=='DES99' || $item[$codigo]!='')
								{
									$item_desp = $item[$codigo];
								}
								else
								{
									$item_desp = $array_items_desp[$codigo];
								}
								
								$modelo = ' ';
								
								$placa = ' ';
								
								$hora_i = ' ';	
								
								$hora_f = ' ';								
							}
							
							$xml->startElement('row');
								$xml->writeAttribute('id','nec_'.$i);
								$xml->writeElement ('cell',$item_desp);
								$xml->writeElement ('cell',$array_items_qtd[$codigo]);
								$xml->writeElement ('cell',number_format($array_items_vlr[$codigo],2,',',''));
								$xml->writeElement ('cell',$qtd);
								$xml->writeElement ('cell',$hora_i);
								$xml->writeElement ('cell',$hora_f);								
								$xml->writeElement ('cell',$modelo);
								$xml->writeElement ('cell',$placa);								
							$xml->endElement();	
							
							$i++;
						}
						
						$xml->endElement();
								
						$conteudo1 = $xml->outputMemory(false);
						
						$resposta->addScript("grid('itens_nec',true,'400','".$conteudo1."');");						
						
						//monta a tabela funcionários
						//filtra os funcionarios da requisição
						$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas_funcionarios, ".DATABASE.".funcionarios ";
						$sql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '" . $id_requisicao_despesa . "' ";
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

							$xml->openMemory();
							$xml->setIndent(false);
							$xml->startElement('rows') ;
									
							foreach($db->array_select as $cont3)
							{
								$xml->startElement('row');
									$xml->writeAttribute('id','fun_'.$cont3["id_requisicao_despesas_funcionario"]);
									$xml->writeElement ('cell',$cont3["funcionario"]);
								$xml->endElement();									
							}
							
							$xml->endElement();
									
							$conteudo2 = $xml->outputMemory(false);
							
							$resposta->addScript("grid('funcionarios',true,'400','".$conteudo2."');");							
							
							//monta acertos
							
							//mostra as despesas declaradas quando o status for despesas/acertos
							//if($cont["status"]==2 || $cont["status"]==3)
							//{
								$conteudo_acert = '<tr>';
								$conteudo_acert .= '<td><label class="labels"><strong>valor adiantamento R$: </strong>'.number_format($cont["valor_adiantamento"],2,',','.').'</label></td>';
								$conteudo_acert .= '</tr>';
								
								$resposta->addAssign("dv_acerto_despesas","innerHTML",$header.$conteudo_acert.$footer);
								
								$xml->openMemory();
								$xml->setIndent(false);
								$xml->startElement('rows') ;
								
								$i = 1;
								
								foreach ($array_vlr as $codigo=>$valor)
								{
									
									if($codigo=='DES99' || $item[$codigo]!='')
									{
										$item_acert = $item[$codigo];
									}
									else
									{
										$item_acert = $array_items_desp[$codigo];
									}
									
									$xml->startElement('row');
										$xml->writeAttribute('id','acert_'.$i);
										$xml->writeElement ('cell',$item_acert);
										$xml->writeElement ('cell',number_format($valor,2,',','.'));
									$xml->endElement();
									
									$i++;		
									
								}
								
								$xml->endElement();
										
								$conteudo3 = $xml->outputMemory(false);
								
								$resposta->addScript("grid('div_acerto',true,'400','".$conteudo3."');");
								
								switch ($cont["status"])
								{
									case '0':
									case '1':
										$aprova = 'disabled="disabled"';
										$reprova = '';
									break;
									
									case '2':
										$aprova = '';
										$reprova = '';
									break;
									
									case '3':
									case '4':
										$aprova = 'disabled="disabled"';
										$reprova = 'disabled="disabled"';
									break;	
								}
																								
								//if($cont["status"]==2)
								//{
									$buttons = '<input type="button" class="class_botao" '.$aprova.' value="Aprovado" onclick=if(acerto(1)){xajax_acerto_funcionario("'.$id_requisicao_despesa.'",1);}>  ';
									$buttons .= '<input type="button" class="class_botao" '.$reprova.' value="Reprovado" onclick=if(acerto(2)){xajax_acerto_funcionario("'.$id_requisicao_despesa.'",2);}>';
									
									$resposta->addAssign("div_button","innerHTML",$buttons);
								//}								
							//}						
						
							if($cont["status"]>=2)
							{
								$resposta->addAssign("btninserir", "disabled", "true");
							}
							else
							{
								$resposta->addAssign("btninserir", "disabled", "");
							}
						
						}					
					}
				
				}
			
			}
			
			*/
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
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo acoes = 112
	{
		if($dados_form["valor"]!='' || $dados_form["data"]!='')
		{
			//filtra a requisicao
			$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas ";
			$sql .= "WHERE requisicao_despesas.id_requisicao_despesa = '" . $dados_form["id_requisicao_despesa"] . "' ";
			$sql .= "AND requisicao_despesas.reg_del = 0 ";
		
			//FAZ O SELECT
			$db->select($sql,'MYSQL', true);
				
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				$resposta->addAlert('Erro '.$sql);
			}
			else
			{		
				$cont = $db->array_select[0];
							
				$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
				$usql .= "data_adiantamento = '" . php_mysql($dados_form["data"]) . "', ";
				$usql .= "valor_adiantamento = '" . $dados_form["valor"] . "', ";
				$usql .= "veiculo_modelo = '" . maiusculas($dados_form["modelo"]) . "', ";
				$usql .= "veiculo_placa = '" . maiusculas($dados_form["placa"]) . "', ";
				$usql .= "status = 1 ";
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
					$texto .='	  <td><strong>Adiantamento Requisição de Despesa </strong></td>';
					$texto .=' </tr>';
					$texto .=' <tr>';
					$texto .='	<td>Requisição nº: '.sprintf("%05d",$dados_form["id_requisicao_despesa"]).'</td>';
					$texto .=' </tr>';
					$texto .=' <tr>';
					$texto .='	<td>Já esta disponível no sistema o adiantamento.</td>';
					$texto .=' </tr>';
					$texto .=' <tr>';
					$texto .='	  <td><strong>Valor R$: </strong>'.number_format($dados_form["valor"],2,',','').'</td>';
					$texto .=' </tr>';
					$texto .=' <tr>';
					$texto .='	  <td><strong>Data adiantamento</strong>'.$dados_form["data"].'</td>';
					$texto .=' </tr>';
					
					if($dados_form["modelo"]!="")
					{
						$texto .=' <tr>';
						$texto .='	  <td><strong>Veículo:</strong>'.maiusculas($dados_form["modelo"]).' - placa: '.maiusculas($dados_form["placa"]).'</td>';
						$texto .=' </tr>';
					}
					
					$texto .=' <tr>';
					$texto .='	  <td>Favor entrar em contato com o setor FINANCEIRO.</td>';
					$texto .=' </tr>';
					
					$texto .= '</table>';
					
					$params 			= array();
					$params['from']		= "financeiro@dominio.com.br";
					$params['from_name']= "REQUISIÇÃO DESPESAS - ADIANTAMENTO";
					$params['subject'] 	= "REQUISIÇÃO DESPESAS - ADIANTAMENTO";
					
					$params['emails']['to'][] = array('email' => "financeiro@dominio.com.br", 'nome' => "Financeiro");
					
					if($array_funcionarios[1][$cont["id_funcionario"]])//solicitante
					{
						$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["id_funcionario"]], 'nome' => $array_funcionarios[0][$cont["id_funcionario"]]);
					}
					
					if($array_funcionarios[1][$cont["responsavel_despesas"]]) //responsavel
					{
						$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["responsavel_despesas"]], 'nome' => $array_funcionarios[0][$cont["responsavel_despesas"]]);
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
						$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
					}

					$resposta->addAlert("Adiantamento atualizado com sucesso.");

				}
			}			
		}
		else
		{	
			$resposta->addAlert("Os campos devem estar preenchidos.");
		}
	}

	$resposta->addScript("xajax_voltar();");	

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function virgula_ponto($campo, $valor = '')
{
	$resposta = new xajaxResponse();
	
	if(strstr($valor,',')!=FALSE)
	{
		$valor = str_replace(',','.',$valor);
		
		$resposta -> addAssign($campo, "value", $valor);
	}
	
	return $resposta;		
}

function acerto_funcionario($id, $tipo)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$erro = false;
	
	//filtra a requisicao
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
		
		//soma os valores
		$sql = "SELECT SUM(valor_despesa) AS total_despesas FROM ".DATABASE.".requisicao_despesas_necessidades ";
		$sql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '" . $id . "' ";
		$sql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert('Erro '.$sql);
		}
		else
		{			
			$cont1 = $db->array_select[0];	
					
			if($tipo==1) //acerto
			{
				$status = "ACEITE";
				
				$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
				$usql .= "data_acerto = '".date('Y-m-d')."', ";
				$usql .= "status = 3 ";
				$usql .= "WHERE id_requisicao_despesa = '" . $id ."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					$erro = true;
				}
				else
				{
					$resposta->addAlert("Adiantamento aprovado com sucesso.");
				}
			}
			else
			{
				$status = "REJEITADO";
				
				$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
				$usql .= "status = 4 ";
				$usql .= "WHERE id_requisicao_despesa = '" . $id ."' ";
				$usql .= "AND reg_del = 0 ";

				$db->update($usql,'MYSQL');
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					$erro = true;
				}
				else
				{
					$resposta->addAlert("Adiantamento reprovado com sucesso.");
				}			
			}
			
			//se não houve erro, envia o e-mail
			if(!$erro)
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
				$texto .='	  <td><strong>Acerto Requisição de Despesa </strong></td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Requisição nº: </strong>'.sprintf("%05d",$id).'</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Data acerto: </strong>'.$cont["data_prestacao_contas"].'</td>';
				$texto .=' </tr>';
				$texto .=' <tr>';
				$texto .='	<td><strong>Valor declarado: </strong>'.number_format($cont1["total_despesas"],2,',','.').'</td>';
				$texto .=' </tr>';
				$texto .='<br>';
				$texto .=' <tr>';
				$texto .='	<td><strong>STATUS: '.$status.'</strong></td>';
				$texto .=' </tr>';
				$texto .='<br>';
				
				//caso rejeitado
				if($tipo==2)
				{
					$texto .=' <tr>';
					$texto .='	<td><strong>Favor refazer a declaração de despesas e/ou procurar o setor financeiro para maiores detalhes.</strong></td>';
					$texto .=' </tr>';
				}
				
				$texto .='</table>';
				
				$params 			= array();
				$params['from']		= "financeiro@dominio.com.br";
				$params['from_name']= "REQUISIÇÃO DESPESAS - ".$status;
				$params['subject'] 	= "REQUISIÇÃO DESPESAS - ".$status;
				
				$params['emails']['to'][] = array('email' => "financeiro@dominio.com.br", 'nome' => "FINANCEIRO");
				
				if($array_funcionarios[1][$cont["id_funcionario"]])//solicitante
				{
					$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["id_funcionario"]], 'nome' => $array_funcionarios[0][$cont["id_funcionario"]]);
				}
				
				if($array_funcionarios[1][$cont["responsavel_despesas"]]) //responsavel
				{
					$params['emails']['to'][] = array('email' => $array_funcionarios[1][$cont["responsavel_despesas"]], 'nome' => $array_funcionarios[0][$cont["responsavel_despesas"]]);
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
					$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
				}

			}

		}
	}		
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_voltar();");
	
	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$db = new banco_dados;	

	$id = str_replace(array('req_', 'acert_', 'nec_'), '', $id);
		
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
	    return $resposta;
	}
	else
	{
	    foreach($db->array_select as $regs)
	    {
	        $array_funcionarios[0][$regs_func["id_funcionario"]] = $regs_func["funcionario"];
	        $array_funcionarios[1][$regs_func["id_funcionario"]] = $regs_func["email"];
	    }
	}
	
	//seleciona a despesa
	$sql = "SELECT * FROM ".DATABASE.".requisicao_despesas ";
	$sql .= "WHERE requisicao_despesas.id_requisicao_despesa IN(".$id.") ";
	$sql .= "AND requisicao_despesas.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert('Erro '.$sql);
		return $resposta;
	}
	else
	{
		$params 			= array();
		$params['from']		= $array_funcionarios[1][$cont["responsavel_despesas"]];
		$params['from_name']= "REQUISIÇÃO DESPESAS - CANCELAMENTO";
		$params['subject'] 	= "REQUISIÇÃO DESPESAS: CANCELAMENTO";
		$params['emails']['to'][] = array('email' => "financeiro@dominio.com.br", 'nome' => "FINANCEIRO");
		$texto = 'As requisções a seguir foram canceladas.<br />';		
		
	    foreach($db->array_select as $cont)
		{
		    $id = $cont['id_requisicao_despesa'];
		    
    		$usql = "UPDATE ".DATABASE.".requisicao_despesas SET ";
    		$usql .= "requisicao_despesas.reg_del = 1, ";
    		$usql .= "requisicao_despesas.data_del = '".date('Y-m-d')."', ";
    		$usql .= "requisicao_despesas.reg_who = '".$_SESSION["id_funcionario"]."' ";
    		$usql .= "WHERE requisicao_despesas.id_requisicao_despesa = '".$id."' ";
    		$usql .= "AND requisicao_despesas.reg_del = 0 ";
    
    		$db->update($usql,'MYSQL');
    
    		if($db->erro!='')
    		{
    			$resposta->addAlert('Erro '.$sql);
    			return $resposta;
    		}
    		else
    		{					
    			$usql = "UPDATE ".DATABASE.".requisicao_despesas_funcionarios SET ";
    			$usql .= "requisicao_despesas_funcionarios.reg_del = 1, ";
    			$usql .= "requisicao_despesas_funcionarios.data_del = '".date('Y-m-d')."' ,";
    			$usql .= "requisicao_despesas_funcionarios.reg_who = '".$_SESSION["id_funcionario"]."' ";
    			$usql .= "WHERE requisicao_despesas_funcionarios.id_requisicao_despesa = '".$id."' ";
    			$usql .= "AND requisicao_despesas_funcionarios.reg_del = 0 ";
    
    			$db->update($usql,'MYSQL');
    
    			if($db->erro!='')
    			{
    				$resposta->addAlert('Erro '.$sql);
    				return $resposta;
    			}
    			else
    			{						
    				$usql = "UPDATE ".DATABASE.".requisicao_despesas_necessidades SET ";
    				$usql .= "requisicao_despesas_necessidades.reg_del = 1, ";
    				$usql .= "requisicao_despesas_necessidades.data_del = '".date('Y-m-d')."', ";
    				$usql .= "requisicao_despesas_necessidades.reg_who = '".$_SESSION["id_funcionario"]."' ";
    				$usql .= "WHERE requisicao_despesas_necessidades.id_requisicao_despesa = '".$id."' ";
    				$usql .= "AND requisicao_despesas_necessidades.reg_del = 0 ";
    
    				$db->update($usql,'MYSQL');
    
    				if($db->erro!='')
    				{
    					$resposta->addAlert('Erro '.$sql);
    					return $resposta;
    				}
    				else
    				{
    				    $texto .= sprintf("%05d",$id).'<br />';     				
    				}
    			}
    		}
		}

		if (ENVIA_EMAIL)
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
	
	$resposta->addAlert("Registro(s) excluidos corretamente!");

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("editar");
$xajax->registerFunction("acerto_funcionario");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("virgula_ponto");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function excluir_itens_selecionados()
{
	if (confirm('Deseja excluir os itens selecionados?'))
	{
    	var idsSelecionados = mygrid.getCheckedRows(0);
    	xajax_excluir(idsSelecionados);
	}
}

function desbloquearBotaoExcluir()
{
	if (mygrid.getCheckedRows(0) != "")
		document.getElementById('btnexcluir_selecionados').disabled=false;
	else
		document.getElementById('btnexcluir_selecionados').disabled=true;
}

function valida(e_event,cmp)
{
	return txtBoxFormat(document.getElementById('frm'), cmp.id, "!!!-9999",e_event);	
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");

	myTabbar.addTab("a0_", "Filtros");
	myTabbar.addTab("a1_", "Necessidades");
	myTabbar.addTab("a2_", "Colaboradores");
	myTabbar.addTab("a3_", "Acerto");

	myTabbar.tabs("a0_").attachObject("a0");
	myTabbar.tabs("a1_").attachObject("a1");
	myTabbar.tabs("a2_").attachObject("a2");
	myTabbar.tabs("a3_").attachObject("a3");
	
	myTabbar.tabs("a0_").setActive();
	
	myTabbar.enableAutoReSize(true);
	
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setImagePath("<?php echo INCLUDE_JS; ?>dhtmlx_403/codebase/imgs/");
	
	switch (tabela)
	{
		case 'adiantamento_despesas':
		
			function doOnRowSelected1(row,col)
			{
				if(col<=8 && col>=1)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected1);		

			
			var chkAll = '<input type="checkbox" id="chkTodos" style="margin:0;" onclick="mygrid.checkAll(this.checked);desbloquearBotaoExcluir();" />';
			
			mygrid.setHeader(chkAll+",Req nº, Projeto, Data adiant., Atividade/Obs., Responsável, Período, Valor adiant., Valor declar., Status, R, I, D",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("60,60,80,80,*,*,*,*,80,80,25,25,25");
			mygrid.setColAlign("center,center,center,left,left,left,left,right,right,center,center,center,center");
			mygrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str,str,str,str,str,str,str,str,str,str");

			mygrid.attachEvent("onCheck", function(rId,cInd,state){
				desbloquearBotaoExcluir();
			});
		break;
		
		case 'itens_nec':	

			mygrid.setHeader("Item, Qtd orç., Vlr. orç., Qtd solic., Hora ini., Hora fim, Modelo, Placa",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("200,*,*,*,*,*,*,*");
			mygrid.setColAlign("center,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");
		break;
		
		case 'funcionarios':	

			mygrid.setHeader("funcionario",
				null,
				["text-align:left"]);
			mygrid.setInitWidths("*");
			mygrid.setColAlign("left");
			mygrid.setColTypes("ro");
			mygrid.setColSorting("str");
		break;
		
		case 'div_acerto':	

			mygrid.setHeader("Item, Valor declarado",
				null,
				["text-align:center","text-align:center"]);
			mygrid.setInitWidths("*,*");
			mygrid.setColAlign("center,center");
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

function acerto(tipo)
{
	if(tipo==1)//aprovado
	{	
		if(confirm('Tem certeza em aprovar as despesas?'))
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
		if(confirm('Tem certeza em reprovar as despesas?'))
		{
			return true;
		}
		else
		{
			return false;
		}		
	}	 
}

function imprimir(id_requisicao, rel_desp)
{
	if(rel_desp==0)
	{
		window.open('relatorios/rel_termo_responsabilidade.php?id_requisicao='+id_requisicao, '_blank');
	}
	else
	{
		window.open('relatorios/rel_despesas_semanal.php?id_requisicao_despesa='+id_requisicao, '_blank');	
	}
}


</script>

<?php

$conf = new configs();

$array_os_values[] = "";
$array_os_output[] = "SELECIONE";

/*
$sql = "SELECT AF8_PROJET, AF8_REVISA, AF8_DESCRI  FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.AF9_COMPOS <> '' ";
$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
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
    $array_os_output[] = sprintf('%05d', intval($regs["AF8_PROJET"]))." - ".trim($regs["AF8_DESCRI"]);
}
*/

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$array_func_values[] = "";
$array_func_output[] = "SELECIONE";

$sql = "SELECT funcionario, id_funcionario FROM ".DATABASE.".funcionarios WHERE reg_del = 0 AND situacao <> 'DESLIGADO' ORDER BY funcionario;";
$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $regs)
{
    $array_func_values[] = $regs['id_funcionario'];
    $array_func_output[] = $regs['funcionario'];
}

$smarty->assign("option_func_values",$array_func_values);
$smarty->assign("option_func_output",$array_func_output);

$smarty->assign("revisao_documento","V9");

$smarty->assign("campo",$conf->campos('adiantamento_despesas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('adiantamento_despesas.tpl');
?>