<?php
/*
	Formulário de Valorização Propostas	
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo:
	../orcamento/proposta_valorizacao.php
	
	Versão 0 --> VERSÃO INICIAL - Carlos Abreu - 12/04/2017
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
	Versão 2 --> Alteração da ordem das disciplinas/profissionais - 29/01/2018 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(592))
{
	nao_permitido();
}


function status_proposta($id_proposta)
{
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$id_proposta."' ";

	$db->select($sql,'MYSQL',true);
	
	$cont = $db->array_select[0];
	
	$status = $cont["id_status_fpv"];
	
	return $status;
}

function dados_proposta($numero_proposta)
{
	$db = new banco_dados;

	$array_dados = NULL;
	
	/*
	$sql = "SELECT REPLACE(AF1_DESCRI, '-', '-') AF1_DESCRI_TRATADO, * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_CLIENT = A1_COD ";
	$sql .= "AND AF1_LOJA = A1_LOJA ";
	$sql .= "AND AF1_ORCAME = '".$numero_proposta."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	else
	{
		$regs = $db->array_select[0];
		
		$array_dados["orcamento"] = $regs["AF1_ORCAME"];
		$array_dados["descricao"] = trim($regs["AF1_DESCRI_TRATADO"]);
		$array_dados["cliente"] = trim($regs["A1_NOME"]);
		$array_dados["id_cliente"] = trim($regs["A1_COD"]);
		$array_dados["loja"] = trim($regs["A1_LOJA"]);
		$array_dados["apelido"] = trim($regs["A1_APELIDO"]);
	}
	*/
	
	return $array_dados;
			
}

function indices($id_proposta = NULL)
{
	$db = new banco_dados;
	
	//seleciona os indices e margens
	$sql = "SELECT * FROM ".DATABASE.".tipos_indices, ".DATABASE.".indices_fpv, ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE indices_fpv.reg_del = 0 ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";
	$sql .= "AND tipos_indices.reg_del = 0 ";
	$sql .= "AND indices_fpv.id_tipo_indice = tipos_indices.id_tipo_indice ";
	$sql .= "AND indices_fpv.id_indice_atual = indices_fpv_historico.id_indice_fpv_historico ";
	
	//se for edição de proposta já aprovada, recupera os indices utilizados
	if(!empty($id_proposta))
	{
		$sql1 = "SELECT * FROM ".DATABASE.".propostas_indices ";
		$sql1 .= "WHERE propostas_indices.reg_del = 0 ";
		$sql1 .= "AND propostas_indices.id_proposta = '".$id_proposta."' ";
		
		$db->select($sql1,'MYSQL',true);
		
		foreach($db->array_select as $regs)
		{	
			$array_indices_hist[] = $regs["id_indice_fpv_historico"];
		}
		
		if(count($array_indices_hist)>0)
		{				
			$sql .= "AND indices_fpv_historico.id_indice_fpv_historico IN (".implode(",",$array_indices_hist).") ";
		}
	}
	
	$sql .= "GROUP BY indices_fpv.id_tipo_indice ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{	
		$array_indices[$regs["id_tipo_indice"]] = $regs["percentual"];
	}
	
	return $array_indices;		
}

function escolha_margens($dados_form)
{
	$resposta = new xajaxResponse();

	$lucro_liq = empty($dados_form["lucro_liq"])?0:str_replace(",",".",$dados_form["lucro_liq"]);
	
	if(in_array($lucro_liq,array('0','5','7.5','10')))
	{
		$resposta->addAlert('A margem escolhida já existe.');
		
		$resposta->addAssign("lucro_liq","value","12,5");
	}
	else
	{	
		if($dados_form["sel_margem"]=='')
		{
			$selecao = '7.5';
		}
		else
		{
			$selecao = $dados_form["sel_margem"];	
		}
		
		//$resposta->addAlert($selecao);
		
		$array_lucros = array('0','5','7.5','10',$lucro_liq);
		
		$combo = '<select id="sel_margem" name="sel_margem" class="caixa" onchange=xajax_preenche_categorias(xajax.getFormValues("frm"));xajax_preenche_guarda_chuva(xajax.getFormValues("frm"));>';
		
		$combo .= '<option value="">SELECIONE</option>';
		
		foreach($array_lucros as $indices)
		{
			if($indices==$selecao)
			{
				$selected = 'selected';
			}
			else
			{
				$selected = '';
			}
			
			$combo .= '<option value="'.$indices.'" '.$selected.'>'.$indices.' %</option>';
		}
		
		$combo .= '</select>';	
		
		$resposta->addAssign("div_margem", "innerHTML", $combo);
	}
	
	return $resposta;		
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");

	$resposta->addAssign("div_control_resumo", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_categorias", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_guarda_chuva_dvm", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_adm_dvm", "style.visibility", "hidden");
	
	$resposta->addAssign("btn_aplicar", "disabled", "disabled");
	
	$resposta->addAssign("btn_concluir", "disabled", "disabled");
		
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($dados_form, $busca = false)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND fase_orcamento IN ('09','04','06') "; //exportado protheus / em planejamento / aprovado
	
	if($dados_form["busca"]!='' && $busca)
	{
		  $array_valor = explode(" ",$dados_form["busca"]);
		  
		  for($x=0;$x<count($array_valor);$x++)
		  {
			  $array_valor[$x] = AntiInjection::clean($array_valor[$x]);
			  $sql_texto .= "%" . $array_valor[$x] . "%";
		  }
		  
		  $sql .= " AND (propostas.numero_proposta LIKE '".$sql_texto."' ";
		  $sql .= "OR propostas.descricao_proposta LIKE '".$sql_texto."') ";
	}
	
	if($dados_form["status"]!=0 && !$busca)
	{
		$sql .= "AND propostas.id_status_fpv = '".$dados_form["status"]."' ";
	}
	else
	{
		if(!$busca)
		{
			$sql .= "AND id_status_fpv IN ('1') "; //não importado
		}
	}

	$sql .= "ORDER BY propostas.numero_proposta DESC ";

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont)
	{
		$importar = ' ';
		
		$exportar = ' ';
		
		switch ($cont["id_status_fpv"])
		{
			case 1:	//NÃO IMPORTADO
					
				$status = '<img src="'.DIR_IMAGENS.'led_vm.png">';
				
				$titulo = 'ORÇAMENTO NÃO IMPORTADO';
				
				$importar = '<img src="'.DIR_IMAGENS.'arrow_down.png" onclick = if(confirm("Deseja importar o orçamento técnico para a valorização?")){xajax_importar("'.$cont["id_proposta"].'");}>';
				
				$exportar = ' ';
							
			break;
			
			case 2: //IMPORTADO
			
				$status = '<img src="'.DIR_IMAGENS.'led_vd.png">';
				
				$titulo = 'ORÇAMENTO IMPORTADO';
				
				$exportar = ' ';
				
				$importar = '<img src="'.DIR_IMAGENS.'arrow_down.png" onclick = if(confirm("Deseja importar o orçamento técnico para a valorização?")){xajax_importar("'.$cont["id_proposta"].'");}>';
				
			break;
			
			case 3: //VALORIZADO
			
				$status = '<img src="'.DIR_IMAGENS.'led_am.png">';
				
				$titulo = 'ORÇAMENTO VALORIZADO';
				
				$exportar = '<img src="'.DIR_IMAGENS.'arrow_up.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Protheus?")){xajax_exportar("'.$cont["id_proposta"].'");}>'; 
				
				$importar = '<img src="'.DIR_IMAGENS.'arrow_down.png" onclick = if(confirm("Deseja importar o orçamento técnico para a valorização?")){xajax_importar("'.$cont["id_proposta"].'");}>';
			break;
			
			case 4: //EXPORTADO
			
				$status = '<img src="'.DIR_IMAGENS.'led_az.png">';
				
				$titulo = 'ORÇAMENTO EXPORTADO';
				
				$exportar = ' '; 
				
				$importar = ' ';
			break;
			
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','prop_'.$cont["id_proposta"]);
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($status);
			$xml->endElement();
			$xml->writeElement ('cell',$cont["numero_proposta"]);
			$xml->writeElement ('cell',str_replace("'", '', $cont["descricao_proposta"]));
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($importar);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($exportar);
			$xml->endElement();

		$xml->endElement();	
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_dados_cliente',true,'420','".$conteudo."');");
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$temp = explode('_',$id);
	
	$id = $temp[1];
	
	switch ($temp[0])
	{
		case 'prop':	
			
			//carrega proposta
			$sql = "SELECT * FROM ".DATABASE.".propostas ";
			$sql .= "WHERE propostas.reg_del = 0 ";
			$sql .= "AND propostas.id_proposta = '".$id."' ";
		
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			$resposta->addAssign("nr_proposta", "innerHTML",$cont["numero_proposta"]);
			
			$resposta->addAssign("id_proposta", "value",$cont["id_proposta"]);
			
			$resposta->addAssign("descricao_proposta", "innerHTML",$cont["descricao_proposta"]);
			
			$resposta->addAssign("div_escopo_detalhado","innerHTML","");
			
			$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
			
			switch ($cont["id_status_fpv"])
			{
				case 2:
					$resposta->addAssign("btn_aplicar", "disabled", "");
					
					$resposta->addAssign("btn_concluir", "disabled", "");
				break;
				
				case 3:
					$resposta->addScript("seleciona_combo('" . $reg_fpv["id_regiao"] . "', 'regiao');");
							
					$resposta->addAssign("reajuste","value",$reg_fpv["percentual_reajuste"]);
					
					$resposta->addScript("seleciona_combo('" . $reg_fpv["tipo_contrato"] . "', 'contrato');");
					
					if($reg_fpv["periculosidade"])
					{
						$resposta->addScript("document.getElementById('periculosidade').checked=true;");
					}
					else
					{
						$resposta->addScript("document.getElementById('periculosidade').checked=false;");
					}
					
					$resposta->addAssign("lucro_liq","value",$reg_fpv["margem_aplicada"]);
					
					$resposta->addScript("seleciona_combo('" . $reg_fpv["percentual_margem_aplicada"] . "', 'sel_margem');");
					
					$resposta->addAssign("btn_aplicar", "disabled", "disabled");
					
					$resposta->addAssign("btn_concluir", "disabled", "disabled");	
					
				break;
				
				default:
					$resposta->addAssign("btn_aplicar", "disabled", "disabled");
					
					$resposta->addAssign("btn_concluir", "disabled", "disabled");				
					
			}
			
			
			$resposta->addAssign("div_control_resumo", "style.visibility", "");
			
			$resposta->addAssign("div_control_categorias", "style.visibility", "");
			
			$resposta->addAssign("div_control_guarda_chuva_dvm", "style.visibility", "");
			
			$resposta->addAssign("div_control_adm_dvm", "style.visibility", "");
			
		break;
		
	}
	
	return $resposta;	
}

function preenche_resumo($dados_form)
{
	$resposta = new xajaxResponse();
			
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".formatos ";
	$sql .= "WHERE formatos.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_formatos[$regs["id_formato"]] = $regs["formato"];	
	}	
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = escopo_detalhado_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";	
	$sql .= "ORDER BY escopo_geral_valorizacao.escopo_geral, setores.ordem, setores.setor, atividades.descricao ";

	$db->select($sql,'MYSQL',true);
	
	$array_resumo = $db->array_select;

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;
	
	$escopo_geral = "";
	
	$disciplina = "";
	
	$tot_setor = NULL;
	
	$cod_setor = '';
	$setor = '';
	
	$total_horas = 0;
	
	$total_geral = NULL;	
	
	//contabiliza os elementos de cada Escopo Geral
	//determina os finais de cada
	foreach($array_resumo as $regs0)
	{
		$array_count[$regs0["id_escopo_geral"]] += 1;
	}

	foreach($array_resumo as $regs)
	{
		$total_horas += $regs["horas"];
		
		//se coordenacao/planejamento = apoio
		switch ($regs["id_setor"])
		{
			case 5: //planejamento
			case 15: ///coordenação			
				$cod_setor = 99;
				$setor = 'APOIO';
			break;
			
			default:
				$cod_setor = $regs["id_setor"];
				$setor = $regs["setor"];				
		}		
		
		if($escopo_geral!=$regs["id_escopo_geral"])
		{
			$qtd_tarefas = 1;
					
			$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_escopo_geral"].'_'.$regs["id_escopo_detalhado"]);
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.'background-color:#00BBFF');
					$xml->writeAttribute('colspan','7');
					$xml->text($regs["escopo_geral"]);
				$xml->endElement();
			$xml->endElement();			
		}
		
		if($disciplina!=$cod_setor || $escopo_geral!=$regs["id_escopo_geral"])
		{
			$xml->startElement('row');
				$xml->writeAttribute('id',$cod_setor.'_'.$regs["id_escopo_detalhado"]);
				$xml->writeElement ('cell',' ');
			
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;');
					$xml->writeAttribute('colspan','6');
					$xml->text($setor);					
				$xml->endElement();
			
			$xml->endElement();											
		}
		
		//seleciona a tabela de recursos cadastrados
		$sql = "SELECT * FROM ".DATABASE.".recursos_valorizacao ";
		$sql .= "WHERE recursos_valorizacao.reg_del = 0 ";
		$sql .= "AND recursos_valorizacao.id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
		$sql .= "AND recursos_valorizacao.id_recurso <> 0 "; //recurso alocado
		
		$db->select($sql,'MYSQL',true);	
		
		//CASO tenha recursos alocados (FUN_), MUDA COR DA LINHA
		if($db->numero_registros>0)
		{
			$color = 'background-color:#66FF66';		
		}
		else
		{
			$color = '';
		}

		$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_escopo_detalhado"]);
			$xml->writeAttribute('style',$color);
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',$regs["codigo"]);
			$xml->writeElement ('cell',$regs["descricao"]." ".$regs["descricao_escopo"]);
			
			$xml->writeElement ('cell',$array_formatos[$regs["id_formato"]]);
			
			$xml->writeElement ('cell',number_format($regs["quantidade"],2,",","."));
			
			$xml->writeElement ('cell',number_format($regs["horas"],2,",","."));
			
		$xml->endElement();
		
		//MOBILIZAÇÃO
		if($array_count[$regs["id_escopo_geral"]]==$qtd_tarefas)
		{			
			//seleciona a mobilizacao (DESPESAS)
			$sql = "SELECT * FROM ".DATABASE.".mobilizacao_valorizacao, ".DATABASE.".atividades, ".DATABASE.".formatos ";
			$sql .= "WHERE mobilizacao_valorizacao.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND formatos.reg_del = 0 ";
			$sql .= "AND mobilizacao_valorizacao.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
			$sql .= "AND mobilizacao_valorizacao.id_tarefa = atividades.id_atividade ";
			$sql .= "AND atividades.id_formato = formatos.id_formato ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_mobilizacao = $db->array_select;
	
			//se hover mobilizacao mostra os registros
			if($db->numero_registros>0)
			{
				$xml->startElement('row');
					$xml->writeAttribute('id','29_'.$regs3["id_mobilizacao_valorizacao"]);
					$xml->writeElement ('cell',' ');
				
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->writeAttribute('colspan','6');
						$xml->text('MOBILIZAÇÃO');					
					$xml->endElement();
				
				$xml->endElement();	
					
				foreach($array_mobilizacao as $regs3)
				{
					$xml->startElement('row');
						$xml->writeAttribute('id',$regs["id_escopo_geral"].'='.$regs3["id_mobilizacao_valorizacao"]);
						$xml->writeAttribute('style',$color);
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',$regs3["codigo"]);
						$xml->writeElement ('cell',$regs3["descricao"]." ".$regs3["descricao_mobilizacao"]);
						
						$xml->writeElement ('cell',$array_formatos[$regs3["id_formato"]]);
						
						$xml->writeElement ('cell',number_format($regs3["qtd_necessario_orcado"],2,",","."));
						
						$xml->writeElement ('cell',' ');
						
					$xml->endElement();
				}
			}
		}
		
		$qtd_tarefas++;	
		
		//$disciplina = $regs["id_setor"];
		$disciplina = $cod_setor;
		
		$escopo_geral = $regs["id_escopo_geral"];		
	}
	
		$xml->startElement('row');
			$xml->writeAttribute('id','9999999');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');			
			$xml->writeElement ('cell',' ');
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style','font-weight:bold;');
				$xml->text('TOTAL');					
			$xml->endElement();
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style','font-weight:bold;');
				$xml->text(number_format($total_horas,2,",","."));					
			$xml->endElement();
		
		$xml->endElement();
		
	$xml->endElement();
			
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_resumo',true,'420','".$conteudoResumo."');");	
	
	return $resposta;
}

function importar($id_proposta)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	//EXCLUI OS REGISTROS DAS TABELAS
	$sql = "SELECT id_escopo_geral FROM ".DATABASE.".escopo_geral_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_escopo_geral_val = $db->array_select;	
	
	foreach($array_escopo_geral_val as $reg_escopo_val)
	{
		$sql = "SELECT id_escopo_detalhado FROM ".DATABASE.".escopo_detalhado_valorizacao ";
		$sql .= "WHERE escopo_detalhado_valorizacao.reg_del = 0 ";
		$sql .= "AND escopo_detalhado_valorizacao.id_escopo_geral = '".$reg_escopo_val["id_escopo_geral"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_escopo_detalhado = $db->array_select;
		
		foreach($array_escopo_detalhado as $reg_escopo_det)
		{
			$usql = "UPDATE ".DATABASE.".recursos_valorizacao SET ";
			$usql .= "reg_del = '1', ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE recursos_valorizacao.id_escopo_detalhado = '".$reg_escopo_det["id_escopo_detalhado"]."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');			
		}
		
		$usql = "UPDATE ".DATABASE.".escopo_detalhado_valorizacao SET ";
		$usql .= "reg_del = '1', ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE escopo_detalhado_valorizacao.id_escopo_geral = '".$reg_escopo_val["id_escopo_geral"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		$usql = "UPDATE ".DATABASE.".mobilizacao_valorizacao SET ";
		$usql .= "reg_del = '1', ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE mobilizacao_valorizacao.id_escopo_geral = '".$reg_escopo_val["id_escopo_geral"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');	
				
	}
	
	$usql = "UPDATE ".DATABASE.".escopo_geral_valorizacao SET ";
	$usql .= "reg_del = '1', ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE escopo_geral_valorizacao.id_proposta = '".$id_proposta."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');	
	
	//percorre os escopos gerais
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_escopo_geral = $db->array_select;
	
	foreach($array_escopo_geral as $reg_escopo)
	{
		//insere o escopo geral		
		$isql = "INSERT INTO ".DATABASE.".escopo_geral_valorizacao (id_proposta, escopo_geral, estado, id_local_obra) VALUES ( ";
		$isql .= "'".$reg_escopo["id_proposta"]."',";
		$isql .= "'".$reg_escopo["escopo_geral"]."', ";
		$isql .= "'".$reg_escopo["estado"]."', ";
		$isql .= "'".$reg_escopo["id_local_obra"]."') ";
		
		$db->insert($isql,'MYSQL');
		
		$id_escopo_geral = $db->insert_id;
		
		//seleciona os escopos detalhados
		$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$reg_escopo["id_escopo_geral"]."' ";
		$sql .= "AND atividades.id_atividade = escopo_detalhado.id_tarefa ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_escopo_detalhado = $db->array_select;
		
		foreach($array_escopo_detalhado as $reg_escopo_det)
		{
			//insere o escopo detalhado
			$isql = "INSERT INTO ".DATABASE.".escopo_detalhado_valorizacao (id_escopo_geral, id_tarefa, item, codigo, descricao_escopo, valor, quantidade_orcada, grau_dificuldade_orcada, horas_orcada, id_formato_orcado, quantidade, horas, id_formato) VALUES ( ";
			$isql .= "'".$id_escopo_geral."', ";
			$isql .= "'".$reg_escopo_det["id_tarefa"]."', ";
			$isql .= "'".$reg_escopo_det["item"]."', ";
			$isql .= "'".$reg_escopo_det["codigo"]."', ";
			$isql .= "'".$reg_escopo_det["descricao_escopo"]."', ";
			$isql .= "'".$reg_escopo_det["valor"]."', ";
			$isql .= "'".$reg_escopo_det["qtd_necessario"]."', ";
			$isql .= "'".$reg_escopo_det["grau_dificuldade"]."', ";
			$isql .= "'".$reg_escopo_det["totaliza_categoria"]."', ";
			$isql .= "'".$reg_escopo_det["id_formato"]."', ";
			$isql .= "'".$reg_escopo_det["qtd_necessario"]."', ";
			$isql .= "'".$reg_escopo_det["totaliza_categoria"]."', ";
			$isql .= "'".$reg_escopo_det["id_formato"]."') ";
			
			$db->insert($isql,'MYSQL');
			
			$id_escopo_detalhado = $db->insert_id;
			
			//seleciona as atividades e os recursos associados
			$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
			$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
			$sql .= "AND atividades_orcamento.reg_del = 0 ";
			$sql .= "AND rh_cargos.reg_del = 0 ";
			$sql .= "AND atividades_orcamento.id_atividade = '" . $reg_escopo_det["id_tarefa"] . "' ";
		
			$db->select($sql,'MYSQL',true);
			
			$array_porcent = NULL;
			
			$item = 0;
			
			foreach($db->array_select as $reg_por)
			{
				$calc = $reg_escopo_det["totaliza_categoria"]*($reg_por["porcentagem"]/100);
				
				//insere os recursos
				$isql = "INSERT INTO ".DATABASE.".recursos_valorizacao (id_escopo_detalhado, id_recurso_orcamento, id_escopo_geral, id_tarefa, item_escopo, item, horas_orcamento)VALUES ( ";
				$isql .= "'".$id_escopo_detalhado."', ";
				$isql .= "'".$reg_por["id_cargo_grupo"]."', ";
				$isql .= "'".$id_escopo_geral."', ";
				$isql .= "'".$reg_escopo_det["id_tarefa"]."', ";
				$isql .= "'".$reg_escopo_det["item"]."', ";
				$isql .= "'".$item."', ";
				$isql .= "'".$calc."') ";
				
				$db->insert($isql,'MYSQL');
				
				$item++;								
			}		
		}
		
		//importa a mobilizacao
		$sql = "SELECT * FROM ".DATABASE.".mobilizacao ";
		$sql .= "WHERE mobilizacao.reg_del = 0 ";
		$sql .= "AND mobilizacao.id_escopo_geral = '".$reg_escopo["id_escopo_geral"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_mobilizacao = $db->array_select;
		
		foreach($array_mobilizacao as $reg_mobilizacao)
		{
			//insere a mobilizacao
			$isql = "INSERT INTO ".DATABASE.".mobilizacao_valorizacao (id_escopo_geral, id_tipo_reembolso, taxa_administrativa, id_tarefa, item, codigo, descricao_mobilizacao, qtd_necessario_orcado, valor_mobilizacao_orcado, quantidade, valor_mobilizacao) VALUES ( ";
			$isql .= "'".$id_escopo_geral."', ";			
			$isql .= "'".$reg_mobilizacao["id_tipo_reembolso"]."', ";
			$isql .= "'".$reg_mobilizacao["taxa_administrativa"]."', ";
			$isql .= "'".$reg_mobilizacao["id_tarefa"]."', ";
			$isql .= "'".$reg_mobilizacao["item"]."', ";
			$isql .= "'".$reg_mobilizacao["codigo"]."', ";
			$isql .= "'".$reg_mobilizacao["descricao_mobilizacao"]."', ";
			$isql .= "'".$reg_mobilizacao["qtd_necessario"]."', ";
			$isql .= "'".$reg_mobilizacao["valor_mobilizacao"]."', ";
			$isql .= "'".$reg_mobilizacao["qtd_necessario"]."', ";
			$isql .= "'".$reg_mobilizacao["valor_mobilizacao"]."') ";
			
			$db->insert($isql,'MYSQL');			
		}				
	}
	
	//atualiza status da fpv para importado
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "id_status_fpv = 2 ";
	$usql .= "WHERE id_proposta = '".$id_proposta."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addAlert("Importado com sucesso.");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;	
}

//MODIFICADO - 05/02/2018
function preenche_categorias($dados_form)
{
	$resposta = new xajaxResponse();
			
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$margem = 0;
	
	$pcc = 0;
	
	$pis_cof = 0;
	
	$ind_peric = 0;
	
	$clt_custo = 0;
	
	$index_item = 0;
	
	$total_hh = 0;
	
	$total_prof = 0;
	
	//SELECIONA O STATUS DA FPV	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_fpv = $db->array_select[0];
			
	if($reg_fpv["id_status_fpv"]==3) //VALORIZAÇÃO
	{
		$array_indices = indices($dados_form["id_proposta"]);
	}
	else
	{	
		$array_indices = indices();
	}
	
	//OBTEM O CLIENTE
	$array_cli = dados_proposta($reg_fpv["numero_proposta"]);
	
	$sql = "SELECT id_empresa FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
	$sql .= "WHERE empresas.id_cod_protheus = '".$array_cli["id_cliente"]."' ";
	$sql .= "AND empresas.id_loja_protheus = '".$array_cli["loja"]."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND unidades.reg_del = 0 ";
	$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_cli = $db->array_select[0];
	
	//soma as margens
	for($i=4;$i<=8;$i++)
	{
		$margem += $array_indices[$i];
	}
	
	//soma os impostos (PCC)
	for($i=1;$i<=3;$i++)
	{
		$pcc += $array_indices[$i];
		
		if($i>=2)
		{
			$pis_cof += $array_indices[$i];	
		}		
	}
	
	$ind_reajuste = empty($dados_form["reajuste"])?0:(str_replace(",",".",$dados_form["reajuste"])/100);
	
	switch ($dados_form["contrato"])
	{
		case 1: //PJ
			
			$ind_fatork = 1;
			
			$ind_pis_cof = empty($pis_cof)?0:($pis_cof/100);
				
		break;
		
		case 2: //CLT-MES
		
			$ind_fatork = empty($array_indices[13])?0:$array_indices[13];
			
			$ind_clt_custo = empty($array_indices[15])?0:($array_indices[15]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
			
		break;
		
		case 3: //CLT-HORA
		
			$ind_fatork = empty($array_indices[12])?0:$array_indices[12];
			
			$ind_clt_custo = empty($array_indices[16])?0:($array_indices[16]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
		
		break;
	}
	
	if($dados_form["periculosidade"])
	{
		$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
	}
	
	$ind_moi_mod = empty($array_indices[11])?0:$array_indices[11];
	
	$imp_lucro = empty($array_indices[10])?0:($array_indices[10]/100);
	
	$lucro_liq = empty($dados_form["lucro_liq"])?0:str_replace(",",".",$dados_form["lucro_liq"]);		
		
	$array_lucros = array('0','5','7.5','10',$lucro_liq);
	
	foreach($array_lucros as $indice)
	{
		$imp_s_lucro = (($indice/100)/(1-$imp_lucro))-($indice/100);		
		
		$lucro_bruto = $imp_s_lucro + ($indice/100);
		
		$margem_bruto = $lucro_bruto + ($margem/100);
		
		$fator_venda[$indice] = 1/(1-($pcc/100)-$margem_bruto);		
	}
	
	//sumariza os recursos associados a proposta	
	$sql = "SELECT SUM(horas_orcamento) AS HORAS, id_recurso_orcamento, setores.id_setor FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".recursos_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao, ".DATABASE.".escopo_geral_valorizacao ";
	$sql .= "WHERE recursos_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.reg_del = 0 ";
	
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_detalhado = recursos_valorizacao.id_escopo_detalhado ";
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_geral = escopo_geral_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "GROUP BY setores.id_setor, recursos_valorizacao.id_recurso_orcamento ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recursos[$regs["id_setor"]][$regs["id_recurso_orcamento"]] += $regs["HORAS"];
	}
	
	$sql = "SELECT *, tabela_valor_mo_historico.valor AS VALOR FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo, ".DATABASE.".tabela_valor_mo_historico, ".DATABASE.".atividades_orcamento, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.id_regiao = '".$dados_form["regiao"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo.id_cargo ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_atual = tabela_valor_mo_historico.id_tabela_valor_mo_historico ";//obtem o valor atual
	
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = escopo_detalhado_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "AND atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo ";
	
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";	
	
	$sql .= "GROUP BY setores.id_setor, rh_cargos.id_cargo_grupo ";
	
	$sql .= "ORDER BY setores.ordem, rh_cargos.ordem_tarifas ";

	$db->select($sql,'MYSQL',true);
	
	$array_disciplinas = $db->array_select;
	
	//sumariza os itens de cada setores, para gerar as linhas de sub-total
	
	foreach($array_disciplinas as $regs)
	{
		if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))
		{
			$array_num[99] += 1;
		}
		else
		{
			$array_num[$regs["id_setor"]] += 1;		
		}				
	}
	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;
	
	foreach($array_disciplinas as $regs)
	{
		$hh = empty($array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]])?0:$array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]];
		
		if($hh>0)
		{
			if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))
			{
				$cod_setor = 99;
				$setor = 'APOIO';			
			}
			else
			{
				$cod_setor = $regs["id_setor"];
				$setor = $regs["setor"];		
			}
			
			if($id_setor!=$cod_setor)
			{		
				$xml->startElement('row');
				
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->writeAttribute('colspan','11');
						$xml->text($setor);					
					$xml->endElement();
							
				$xml->endElement();
				
				$index_item = 1;
				
				$subtotal_hh = 0 ;
				
				$subtotal_prof = 0;					
			}		
			
			$reajuste = $regs["VALOR"]+($regs["VALOR"]*$ind_reajuste);
			
			$valor_reajuste = ($reajuste * $ind_fatork);		
	
			$valor_hh = ($valor_reajuste/176);
			
			if($dados_form["contrato"]==1)//pj
			{		
				$valor_calculado = $valor_hh - ($valor_hh * $ind_pis_cof);
			}
			else
			{	
				//coluna sal+peric			
				$valor_peric = $valor_hh + ($valor_hh * $ind_peric);
				
				$valor_calculado = $valor_peric + ($valor_peric * $ind_clt_custo);
			}		
			
			$valor_cust_moi_mod = ($valor_calculado * $ind_moi_mod);
			
			$valor_prof = $hh * $valor_calculado;
			
			$subtotal_prof += $valor_prof;
			
			$total_prof += $valor_prof;
			
			$subtotal_hh += round($hh,2);
			
			$total_hh += round($hh,2);	
			
			$xml->startElement('row');
			
				$xml->writeElement ('cell',' ');
				
				$xml->startElement ('cell');
					$xml->text($regs["grupo"]);					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($regs["VALOR"],2,",","."));					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_reajuste,2,",","."));					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_hh,2,",","."));					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_cust_moi_mod,2,",","."));					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($hh,2,",","."));					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_prof,2,",","."));					
				$xml->endElement();
				
				//fator venda
				foreach($array_lucros as $indice_lucro)
				{
					if($indice_lucro == $dados_form["sel_margem"])
					{
						$style = 'font-weight:bold;background-color:#6F9;';
					}
					else
					{
						$style = '';
					}
					
					$valor_venda[$indice_lucro] = ($valor_cust_moi_mod * $fator_venda[$indice_lucro]);
					
					$valor_hh_dvm[$indice_lucro] = round($hh * $valor_venda[$indice_lucro],2);
					
					$subtotal_valor[$indice_lucro] += $valor_hh_dvm[$indice_lucro];
					
					$total_valor[$indice_lucro] += $valor_hh_dvm[$indice_lucro];
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text(number_format($valor_hh_dvm[$indice_lucro],2,",","."));					
					$xml->endElement();
					
				}
						
			$xml->endElement();
			
			//IMPRIME O SUBTOTAL		
			if($array_num[$cod_setor]==$index_item)
			{
				$xml->startElement('row');
				
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->text('SUBTOTAL');					
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->text(number_format($subtotal_hh,2,",","."));					
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->text('R$ '.number_format($subtotal_prof,2,",","."));					
					$xml->endElement();
					
					foreach($array_lucros as $indice_lucro)
					{
						if($indice_lucro == $dados_form["sel_margem"])
						{
							$style = 'font-weight:bold;background-color:#6F9;';
						}
						else
						{
							$style = 'font-weight:bold;';
						}
						$xml->startElement ('cell');
							$xml->writeAttribute('style',$style);
							$xml->text('R$ '.number_format($subtotal_valor[$indice_lucro],2,",","."));					
						$xml->endElement();
						
						$subtotal_valor[$indice_lucro] = 0;
					}
					
				$xml->endElement();
			}			
			
			$index_item++;
			
			$id_setor = $cod_setor;	
		}
	}
	
	$sub_mob = 0;
	
	//imprime a mobilização		
	//seleciona as atividades x categorias profissionais
	$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".tabela_valor_mobilizacao, ".DATABASE.".tabela_valor_mobilizacao_historico ";
	$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";
	$sql .= "AND tabela_valor_mobilizacao.estado = 'SP' ";
	$sql .= "AND tabela_valor_mobilizacao.id_cidade = 0 ";
	$sql .= "AND atividades.id_atividade = tabela_valor_mobilizacao.id_atividade ";
	$sql .= "AND tabela_valor_mobilizacao.id_tabela_valor_atual = tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico ";//obtem o valor atual
	
	$db->select($sql,'MYSQL',true);
	
	$array_mobilizacao = $db->array_select;
	
	foreach ($array_mobilizacao as $regs_mob)
	{
		$array_valor_mob_dvm[$regs_mob["id_atividade"]] = $regs_mob["valor"];
	}	
		
	$sql = "SELECT *, SUM(mobilizacao_valorizacao.qtd_necessario_orcado) AS qtd_necessario FROM ".DATABASE.".atividades, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".mobilizacao_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND mobilizacao_valorizacao.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = mobilizacao_valorizacao.id_escopo_geral ";
	$sql .= "AND mobilizacao_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "GROUP BY atividades.id_atividade ";

	$db->select($sql,'MYSQL',true);
	
	$array_mob = $db->array_select;
	
	//verifica se existem itens de mobilizacao
	if(count($array_mob)>0)
	{
		$xml->startElement('row');
		//$xml->writeAttribute('id',$indice.'_mob');
			$xml->startElement ('cell');
				$xml->writeAttribute('style','font-weight:bold;');
				$xml->text('MOBILIZAÇÃO');
			$xml->endElement();
		$xml->endElement();
		
		foreach($array_mob as $regs_mob)
		{
			//verifica se existe valor cadastrado no local da obra
			$sql = "SELECT tabela_valor_mobilizacao_historico.valor FROM ".DATABASE.".tabela_valor_mobilizacao, ".DATABASE.".tabela_valor_mobilizacao_historico ";
			$sql .= "WHERE tabela_valor_mobilizacao.reg_del = 0 ";
			$sql .= "AND tabela_valor_mobilizacao_historico.reg_del = 0 ";
			$sql .= "AND tabela_valor_mobilizacao.id_cidade NOT IN (0) ";
			$sql .= "AND tabela_valor_mobilizacao.id_cidade = '".$regs_mob["id_local_obra"]."' ";
			$sql .= "AND tabela_valor_mobilizacao.estado = '".$regs_mob["estado"]."' ";
			$sql .= "AND tabela_valor_mobilizacao.id_atividade = '".$regs_mob["id_atividade"]."' ";
			$sql .= "AND tabela_valor_mobilizacao.id_tabela_valor_atual = tabela_valor_mobilizacao_historico.id_tabela_valor_mobilizacao_historico ";//obtem o valor atual
			
			$db->select($sql,'MYSQL',true);
			
			$array_mobilizacao_cli = $db->array_select[0];
			
			//se tiver valor no cliente
			if($db->numero_registros>0)
			{
				$valor_custo = $array_mobilizacao_cli["valor"];
				
				$valor_uni = number_format($array_mobilizacao_cli["valor"],2,",",".");
			}
			else
			{
				$valor_custo = $array_valor_mob_dvm[$regs_mob["id_atividade"]];
				
				$valor_uni = number_format($array_valor_mob_dvm[$regs_mob["id_atividade"]],2,",",".");			
			}		
			
			$valor_cust_moi_mod = ($valor_custo * $ind_moi_mod);
			
			$sub_mob += $valor_custo*$regs_mob["qtd_necessario"];
			
			$indice++;
			
			$xml->startElement('row');
				$xml->writeElement ('cell',' ');
			
				$xml->startElement ('cell');
					$xml->text($regs_mob["descricao"]);					
				$xml->endElement();			
				
				$xml->startElement ('cell');
					$xml->text($valor_uni);					
				$xml->endElement();			
				
				$xml->startElement ('cell');
					$xml->text(' ');					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(' ');					
				$xml->endElement();			
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_cust_moi_mod,2,",","."));					
				$xml->endElement();			
				
				$xml->startElement ('cell');
					$xml->text(number_format($regs_mob["qtd_necessario"],2,",","."));					
				$xml->endElement();			
				
				$xml->startElement ('cell');
					$xml->text(number_format($valor_custo*$regs_mob["qtd_necessario"],2,",","."));					
				$xml->endElement();
				
				//fator venda
				foreach($array_lucros as $indice_lucro)
				{
					
					if($indice_lucro == $dados_form["sel_margem"])
					{
						$style = 'font-weight:bold;background-color:#6F9;';
					}
					else
					{
						$style = '';
					}
					
					$valor_venda_mob[$indice_lucro] = ($valor_cust_moi_mod * $fator_venda[$indice_lucro]);
					
					$valor_mob_dvm[$indice_lucro] = round($regs_mob["qtd_necessario"] * $valor_venda_mob[$indice_lucro],2);
					
					$subtotal_valor_mob[$indice_lucro] += $valor_mob_dvm[$indice_lucro];
					
					$total_valor[$indice_lucro] += $valor_mob_dvm[$indice_lucro];
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text(number_format($valor_mob_dvm[$indice_lucro],2,",","."));					
					$xml->endElement();
					
				}
				
			$xml->endElement();			
		}
		
		//imprime os subtotais mobilizacao
		$xml->startElement('row');
		
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style','font-weight:bold;');
				$xml->text('SUBTOTAL');					
			$xml->endElement();
			
			$xml->startElement ('cell');
				$xml->text(' ');					
			$xml->endElement();
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style','font-weight:bold;');
				$xml->text('R$ '.number_format($sub_mob,2,",","."));					
			$xml->endElement();
			
			foreach($array_lucros as $indice_lucro)
			{
				if($indice_lucro == $dados_form["sel_margem"])
				{
					$style = 'font-weight:bold;background-color:#6F9;';
				}
				else
				{
					$style = 'font-weight:bold;';
				}
				$xml->startElement ('cell');
					$xml->writeAttribute('style',$style);
					$xml->text('R$ '.number_format($subtotal_valor_mob[$indice_lucro],2,",","."));					
				$xml->endElement();
									
			}
			
		$xml->endElement();		
		
	}
	
	//IMPRIME OS TOTAIS
	$xml->startElement('row');
	
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;');
			$xml->text('TOTAL');					
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;');
			$xml->text(number_format($total_hh,2,",","."));					
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;');
			$xml->text('R$ '.number_format($total_prof,2,",","."));					
		$xml->endElement();
		
		foreach($array_lucros as $indice_lucro)
		{
			if($indice_lucro == $dados_form["sel_margem"])
			{
				$style = 'font-weight:bold;background-color:#6F9;';
			}
			else
			{
				$style = 'font-weight:bold;';
			}
			$xml->startElement ('cell');
				$xml->writeAttribute('style',$style);
				$xml->text('R$ '.number_format($total_valor[$indice_lucro],2,",","."));					
			$xml->endElement();
			
			$total_valor[$indice_lucro] = 0;										
		}
		
	$xml->endElement();
	
	$xml->endElement();
	
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_categorias',true,'350','".$conteudoResumo."');");		
	
	return $resposta;
}

function preenche_guarda_chuva_dvm($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$margem = 0;
	
	$pcc = 0;
	
	$pis_cof = 0;
	
	$ind_peric = 0;
	
	$clt_custo = 0;
	
	$index_item = 0;
	
	$total_hh = 0;
	
	$total_prof = 0;	
	
	//SELECIONA A PROPOSTA	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_proposta = $db->array_select[0];
	
	if($reg_proposta["id_status_fpv"]==3) //VALORIZAÇÃO
	{
		$array_indices = indices(true);
	}
	else
	{	
		$array_indices = indices(false);
	}
	
	//soma as margens
	for($i=4;$i<=8;$i++)
	{
		$margem += $array_indices[$i];
	}
	
	//soma os impostos (PCC)
	for($i=1;$i<=3;$i++)
	{
		$pcc += $array_indices[$i];
		
		if($i>=2)
		{
			$pis_cof += $array_indices[$i];	
		}		
	}
	
	$ind_reajuste = empty($dados_form["reajuste"])?0:(str_replace(",",".",$dados_form["reajuste"])/100);
	
	switch ($dados_form["contrato"])
	{
		case 1: //PJ
			
			$ind_fatork = 1;
			
			$ind_pis_cof = empty($pis_cof)?0:($pis_cof/100);
				
		break;
		
		case 2: //CLT-MES
		
			$ind_fatork = empty($array_indices[13])?0:$array_indices[13];
			
			$ind_clt_custo = empty($array_indices[15])?0:($array_indices[15]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
			
		break;
		
		case 3: //CLT-HORA
		
			$ind_fatork = empty($array_indices[12])?0:$array_indices[12];
			
			$ind_clt_custo = empty($array_indices[16])?0:($array_indices[16]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
		
		break;
	}
	
	if($dados_form["periculosidade"])
	{
		$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
	}
	
	$ind_moi_mod = empty($array_indices[11])?0:$array_indices[11];
	
	$imp_lucro = empty($array_indices[10])?0:($array_indices[10]/100);
	
	$lucro_liq = empty($dados_form["lucro_liq"])?0:str_replace(",",".",$dados_form["lucro_liq"]);		
		
	$array_lucros = array('0','5','7.5','10',$lucro_liq);
	
	foreach($array_lucros as $indice)
	{
		$imp_s_lucro = (($indice/100)/(1-$imp_lucro))-($indice/100);		
		
		$lucro_bruto = $imp_s_lucro + ($indice/100);
		
		$margem_bruto = $lucro_bruto + ($margem/100);
		
		$fator_venda[$indice] = 1/(1-($pcc/100)-$margem_bruto);
	}
	
	$array_cliente = dados_proposta($reg_proposta["numero_proposta"]);
	
	//seleciona o cliente
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "WHERE id_cod_protheus = '".$array_cliente["id_cliente"]."' ";
	$sql .= "AND id_loja_protheus = '".$array_cliente["loja"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_cliente = $db->array_select[0]; 
	
	//sumariza os recursos associados a proposta
	$sql = "SELECT SUM(horas_orcamento) AS HORAS, id_recurso_orcamento, setores.id_setor FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".recursos_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao, ".DATABASE.".escopo_geral_valorizacao ";
	$sql .= "WHERE recursos_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.reg_del = 0 ";
	
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_detalhado = recursos_valorizacao.id_escopo_detalhado ";
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_geral = escopo_geral_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "GROUP BY setores.id_setor, recursos_valorizacao.id_recurso_orcamento ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recursos[$regs["id_setor"]][$regs["id_recurso_orcamento"]] += $regs["HORAS"];
	}
	
	//seleciona as atividades x categorias profissionais (cliente guarda-chuva)	
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo_cliente, ".DATABASE.".tabela_valor_mo_historico_cliente ";
	$sql .= "WHERE tabela_valor_mo_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_cliente.id_cliente = '".$reg_cliente["id_empresa"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo_cliente.id_cargo ";
	$sql .= "AND tabela_valor_mo_cliente.id_tabela_valor_cliente_atual = tabela_valor_mo_historico_cliente.id_tabela_valor_mo_historico_cliente ";//obtem o valor atual
	$sql .= "ORDER BY rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);
	
	//preenche os valores dos profissionais
	foreach($db->array_select as $regs_cliente)
	{
		$valor_dvm_tmp = empty($regs_cliente["valor_interno"])?0:$regs_cliente["valor_interno"];
		
		$array_valor_dvm[$regs_cliente["id_cargo_grupo"]]['valor'] = $valor_dvm_tmp;
	}	
	
	$sql = "SELECT *, tabela_valor_mo_historico.valor AS VALOR FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo, ".DATABASE.".tabela_valor_mo_historico, ".DATABASE.".atividades_orcamento, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.id_regiao = '".$dados_form["regiao"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo.id_cargo ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_atual = tabela_valor_mo_historico.id_tabela_valor_mo_historico ";//obtem o valor atual
	
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = escopo_detalhado_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "AND atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo ";
	
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";	
	
	$sql .= "GROUP BY setores.id_setor, rh_cargos.id_cargo_grupo ";
	
	$sql .= "ORDER BY setores.ordem, rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_disciplinas = $db->array_select;
	
	//sumariza os itens de cada categoria, para gerar a linha de sub-total
	foreach($array_disciplinas as $regs)
	{
		if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))//planejamento
		{
			$array_num[99] += 1;
		}
		else
		{
			$array_num[$regs["id_setor"]] += 1;		
		}				
	}	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;

	foreach($array_disciplinas as $regs)
	{
		$hh = empty($array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]])?0:$array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]];
		
		if($hh>0)
		{
			if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))
			{
				$cod_setor = 99;
				$setor = 'APOIO';			
			}
			else
			{
				$cod_setor = $regs["id_setor"];
				$setor = $regs["setor"];		
			}
						
			//imprime a primeira coluna (Categorias)
			if($id_setor!=$cod_setor)
			{		
				$xml->startElement('row');
				
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->writeAttribute('colspan','13');
						$xml->text($setor);					
					$xml->endElement();
							
				$xml->endElement();
				
				$index_item = 1;
				
				$subtotal_hh = 0 ;
				
				$subtotal_prof = 0;					
			}				
			
			$reajuste = $regs["VALOR"]+($regs["VALOR"]*$ind_reajuste);
			
			$valor_reajuste = ($reajuste * $ind_fatork);		
	
			$valor_hh = ($valor_reajuste/176);
			
			if($dados_form["contrato"]==1)//pj
			{		
				$valor_calculado = $valor_hh - ($valor_hh * $ind_pis_cof);
			}
			else
			{	
				//coluna sal+peric			
				$valor_peric = $valor_hh + ($valor_hh * $ind_peric);
				
				$valor_calculado = $valor_peric + ($valor_peric * $ind_clt_custo);
			}		
			
			$valor_cust_moi_mod = ($valor_calculado * $ind_moi_mod);			
	
			$xml->startElement('row');
				$xml->writeElement ('cell',' ');
			
				$xml->startElement ('cell');
					$xml->text($regs["grupo"]);					
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->text(number_format($array_valor_dvm[$regs["id_cargo_grupo"]]['valor'],2,",","."));					
				$xml->endElement();
				
				//fator venda - horas
				foreach($array_lucros as $indice_lucro)
				{
					$valor_venda[$indice_lucro] = ($valor_cust_moi_mod * $fator_venda[$indice_lucro]);
					
					$valor_cliente_dvm[$indice_lucro] = empty($array_valor_dvm[$regs["id_cargo_grupo"]]['valor'])?0:$array_valor_dvm[$regs["id_cargo_grupo"]]['valor']; 
					
					if($valor_cliente_dvm[$indice_lucro]>0)
					{
						$fator_cliente_dvm[$indice_lucro] = ($valor_venda[$indice_lucro]/$valor_cliente_dvm[$indice_lucro]);
					}
					else
					{
						$fator_cliente_dvm[$indice_lucro] = 0;
					}
					
					if($indice_lucro == $dados_form["sel_margem"])
					{
						$style = 'font-weight:bold;background-color:#6F9;';
					}
					else
					{
						$style = '';
					}
					
					$cli_hh[$indice_lucro] = round($hh * $fator_cliente_dvm[$indice_lucro],2);
					
					$subtotal_hh[$indice_lucro] += $cli_hh[$indice_lucro];
					
					$total_hh[$indice_lucro] += $cli_hh[$indice_lucro];				
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text(number_format($cli_hh[$indice_lucro],2,",","."));					
					$xml->endElement();					
				}
				
				$xml->startElement ('cell');
					$xml->text(' ');					
				$xml->endElement();	
				
				//fator venda - valor
				foreach($array_lucros as $indice_lucro)
				{				
					if($indice_lucro == $dados_form["sel_margem"])
					{
						$style = 'font-weight:bold;background-color:#6F9;';
					}
					else
					{
						$style = '';
					}
					
					$valor_hh_dvm = round($cli_hh[$indice_lucro] * $valor_cliente_dvm[$indice_lucro],2);
					
					$subtotal_valor_hh[$indice_lucro] += $valor_hh_dvm;
					
					$total_valor_hh[$indice_lucro] += $valor_hh_dvm;					
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text('R$ '.number_format($valor_hh_dvm,2,",","."));					
					$xml->endElement();					
				}
				
			$xml->endElement();			
	
			//IMPRIME O SUBTOTAL		
			if($array_num[$cod_setor]==$index_item)
			{
				$xml->startElement('row');				
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->text('SUBTOTAL');					
					$xml->endElement();
					
					foreach($array_lucros as $indice_lucro)
					{
						if($indice_lucro == $dados_form["sel_margem"])
						{
							$style = 'font-weight:bold;background-color:#6F9;';
						}
						else
						{
							$style = 'font-weight:bold;';
						}
						
						$xml->startElement ('cell');
							$xml->writeAttribute('style',$style);
							$xml->text(number_format($subtotal_hh[$indice_lucro],2,",","."));					
						$xml->endElement();
						
						$subtotal_hh[$indice_lucro] = 0;								
					}
					
					$xml->writeElement ('cell',' ');
					
					foreach($array_lucros as $indice_lucro)
					{
						if($indice_lucro == $dados_form["sel_margem"])
						{
							$style = 'font-weight:bold;background-color:#6F9;';
						}
						else
						{
							$style = 'font-weight:bold;';
						}
						
						$xml->startElement ('cell');
							$xml->writeAttribute('style',$style);
							$xml->text('R$ '.number_format($subtotal_valor_hh[$indice_lucro],2,",","."));					
						$xml->endElement();
						
						$subtotal_valor_hh[$indice_lucro] = 0;									
					}
					
				$xml->endElement();			
			}			
			
			$index_item++;
			
			$id_setor = $cod_setor;	

		}		
	}
	
	//IMPRIME OS TOTAIS	
	$xml->startElement('row');
		
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;');
			$xml->text('TOTAL');					
		$xml->endElement();
		
		foreach($array_lucros as $indice_lucro)
		{
			if($indice_lucro == $dados_form["sel_margem"])
			{
				$style = 'font-weight:bold;background-color:#6F9;';
			}
			else
			{
				$style = 'font-weight:bold;';
			}
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style',$style);
				$xml->text(number_format($total_hh[$indice_lucro],2,",","."));					
			$xml->endElement();
			
			$total_hh[$indice_lucro] = 0;	
						
		}
		
		$xml->writeElement ('cell',' ');
		
		foreach($array_lucros as $indice_lucro)
		{
			if($indice_lucro == $dados_form["sel_margem"])
			{
				$style = 'font-weight:bold;background-color:#6F9;';
			}
			else
			{
				$style = 'font-weight:bold;';
			}
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style',$style);
				$xml->text('R$ '.number_format($total_valor_hh[$indice_lucro],2,",","."));					
			$xml->endElement();
			
			$total_valor_hh[$indice_lucro] = 0;						
		}
		
	$xml->endElement();	
	
	$xml->endElement();
				
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_guarda_chuva_dvm',true,'350','".$conteudoResumo."');");
	
	//Mostra os indices utilizados
	$i = 1;
	
	foreach($array_lucros as $indice)
	{		
		$resposta->addAssign("dgd_".$i,"innerHTML",number_format($fator_venda[$indice],4,",","."));
		
		$i++;		
	}
	
	$resposta->addScript("xajax_escolha_margens(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function preenche_adm_dvm($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;	
	
	//SELECIONA A PROPOSTA	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_proposta = $db->array_select[0];
	
	if($reg_proposta["id_status_fpv"]==3) //VALORIZAÇÃO
	{
		$array_indices = indices(true);
	}
	else
	{	
		$array_indices = indices(false);
	}
	
	$array_cliente = dados_proposta($reg_proposta["numero_proposta"]);
	
	//soma as margens
	for($i=4;$i<=8;$i++)
	{
		$margem += $array_indices[$i];
	}
	
	//soma os impostos (PCC)
	for($i=1;$i<=3;$i++)
	{
		$pcc += $array_indices[$i];
		
		if($i>=2)
		{
			$pis_cof += $array_indices[$i];	
		}		
	}
	
	$ind_reajuste = empty($dados_form["reajuste"])?0:(str_replace(",",".",$dados_form["reajuste"])/100);
	
	switch ($dados_form["contrato"])
	{
		case 1: //PJ
			
			$ind_fatork = 1;
			
			$ind_pis_cof = empty($pis_cof)?0:($pis_cof/100);
				
		break;
		
		case 2: //CLT-MES
		
			$ind_fatork = empty($array_indices[13])?0:$array_indices[13];
			
			$ind_clt_custo = empty($array_indices[15])?0:($array_indices[15]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
			
		break;
		
		case 3: //CLT-HORA
		
			$ind_fatork = empty($array_indices[12])?0:$array_indices[12];
			
			$ind_clt_custo = empty($array_indices[16])?0:($array_indices[16]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
		
		break;
	}
	
	if($dados_form["periculosidade"])
	{
		$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
	}
	
	$ind_moi_mod = empty($array_indices[11])?0:$array_indices[11];
	
	$imp_lucro = empty($array_indices[10])?0:($array_indices[10]/100);
	
	$lucro_liq = empty($dados_form["lucro_liq"])?0:str_replace(",",".",$dados_form["lucro_liq"]);		
		
	$array_lucros = array('0','5','7.5','10',$lucro_liq);
	
	foreach($array_lucros as $indice)
	{
		$imp_s_lucro = (($indice/100)/(1-$imp_lucro))-($indice/100);		
		
		$lucro_bruto = $imp_s_lucro + ($indice/100);
		
		$margem_bruto = $lucro_bruto + ($margem/100);
		
		$fator_venda[$indice] = 1/(1-($pcc/100)-$margem_bruto);
	}
	
	//seleciona o cliente
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "WHERE id_cod_protheus = '".$array_cliente["id_cliente"]."' ";
	$sql .= "AND id_loja_protheus = '".$array_cliente["loja"]."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_cliente = $db->array_select[0]; 
	
	//sumariza os recursos associados a proposta

	$sql = "SELECT SUM(horas_orcamento) AS HORAS, id_recurso_orcamento, setores.id_setor FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".recursos_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao, ".DATABASE.".escopo_geral_valorizacao ";
	$sql .= "WHERE recursos_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.reg_del = 0 ";
	
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_detalhado = recursos_valorizacao.id_escopo_detalhado ";
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_geral = escopo_geral_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "GROUP BY setores.id_setor, recursos_valorizacao.id_recurso_orcamento ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recursos[$regs["id_setor"]][$regs["id_recurso_orcamento"]] += $regs["HORAS"];
	}
	
	//seleciona as atividades x categorias profissionais (cliente guarda-chuva)
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo_cliente, ".DATABASE.".tabela_valor_mo_historico_cliente ";
	$sql .= "WHERE tabela_valor_mo_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico_cliente.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_cliente.id_cliente = '".$reg_cliente["id_empresa"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo_cliente.id_cargo ";
	$sql .= "AND tabela_valor_mo_cliente.id_tabela_valor_cliente_atual = tabela_valor_mo_historico_cliente.id_tabela_valor_mo_historico_cliente ";//obtem o valor atual
	$sql .= "ORDER BY rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);
	
	//preenche os valores dos profissionais
	foreach($db->array_select as $regs_cliente)
	{
		$valor_dvm_tmp = empty($regs_cliente["valor_cli"])?0:$regs_cliente["valor_cli"];
		
		$array_valor_dvm[$regs_cliente["id_cargo_grupo"]]['valor'] = $valor_dvm_tmp;
	}	
	
	//seleciona as atividades x categorias profissionais
	
	$sql = "SELECT *, tabela_valor_mo_historico.valor AS VALOR FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo, ".DATABASE.".tabela_valor_mo_historico, ".DATABASE.".atividades_orcamento, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.id_regiao = '".$dados_form["regiao"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo.id_cargo ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_atual = tabela_valor_mo_historico.id_tabela_valor_mo_historico ";//obtem o valor atual
	
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = escopo_detalhado_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "AND atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo ";
	
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";	
	
	$sql .= "GROUP BY setores.id_setor, rh_cargos.id_cargo_grupo ";
	
	$sql .= "ORDER BY setores.ordem, rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_disciplinas = $db->array_select;
	
	//sumariza os itens de cada categoria, para gerar a linha de sub-total
	foreach($array_disciplinas as $regs)
	{
		if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))//planejamento
		{
			$array_num[99] += 1;
		}
		else
		{
			$array_num[$regs["id_setor"]] += 1;		
		}				
	}	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;

	$indice = 0;
	
	$index_item = 0;
		
	foreach($array_disciplinas as $regs)
	{
		$hh = empty($array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]])?0:$array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]];
	
		if($hh>0)
		{	
			//imprime a primeira coluna (Categorias)
			if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))
			{
				$cod_setor = 99;
				$setor = 'APOIO';			
			}
			else
			{
				$cod_setor = $regs["id_setor"];
				$setor = $regs["setor"];		
			}
						
			//imprime a primeira coluna (Categorias)
			if($id_setor!=$cod_setor)
			{		
				$xml->startElement('row');
				
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->writeAttribute('colspan','10');
						$xml->text($setor);					
					$xml->endElement();
							
				$xml->endElement();
				
				$index_item = 1;
				
				$subtotal_hh = 0 ;
				
				$subtotal_prof = 0;					
			}			
			
			$subtotal_hh += $hh;
			
			$total_hh += $hh;		
			
			$valor_uni_total = $hh * $array_valor_dvm[$regs["id_cargo_grupo"]]['valor'];
			
			$subtotal_valor += $valor_uni_total;
			
			$total_valor += $valor_uni_total;	
			
			$valor_cust_moi_mod = ($array_valor_dvm[$regs["id_cargo_grupo"]]['valor'] / $ind_moi_mod);	
	
				$xml->startElement('row');
					
					$xml->writeElement ('cell',' ');
				
					$xml->startElement ('cell');
						$xml->text($regs["grupo"]);					
					$xml->endElement();
					
					$xml->startElement ('cell');
						
						$xml->text(number_format($array_valor_dvm[$regs["id_cargo_grupo"]]['valor'],2,",","."));					
					$xml->endElement();
					
					$xml->startElement ('cell');
						
						$xml->text(number_format($hh,2,",","."));					
					$xml->endElement();
					
					$xml->startElement ('cell');
						
						$xml->text(number_format($valor_uni_total,2,",","."));					
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->text(' ');					
					$xml->endElement();
						
					//fator venda - valor
					foreach($array_lucros as $indice_lucro)
					{
						$valor_custo[$indice_lucro] = ($valor_cust_moi_mod / $fator_venda[$indice_lucro]);
						
						$cli_custo[$indice_lucro] = round($hh * $valor_custo[$indice_lucro],2);
						
						$subtotal_custo[$indice_lucro] += $cli_custo[$indice_lucro];
						
						$total_custo[$indice_lucro] += $cli_custo[$indice_lucro];				
						
						$xml->startElement ('cell');
							$xml->writeAttribute('style',$style);
							$xml->text(number_format($cli_custo[$indice_lucro],2,",","."));					
						$xml->endElement();					
					}
					
				$xml->endElement();
			
	
			//IMPRIME O SUBTOTAL					
			if($array_num[$cod_setor]==$index_item)
			{
				$xml->startElement('row');
				
					$xml->writeElement ('cell',' ');
					$xml->writeElement ('cell',' ');
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;');
						$xml->text('SUBTOTAL');					
					$xml->endElement();
					
					$style = 'font-weight:bold;';
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text(number_format($subtotal_hh,2,",","."));					
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$style);
						$xml->text('R$ '.number_format($subtotal_valor,2,",","."));					
					$xml->endElement();
					
					$xml->writeElement ('cell',' ');				
					
					foreach($array_lucros as $indice_lucro)
					{
					
						$style = 'font-weight:bold;';
						
						$xml->startElement ('cell');
							$xml->writeAttribute('style',$style);
							$xml->text('R$ '.number_format($subtotal_custo[$indice_lucro],2,",","."));					
						$xml->endElement();
						
						$subtotal_custo[$indice_lucro] = 0;								
					}
									
					$subtotal_hh = 0;
					
					$subtotal_valor = 0;
					
				$xml->endElement();			
			}					
			
			$index_item++;
			
			$id_setor = $cod_setor;				
		}
	}
	
	//imprime os totais	
	$xml->startElement('row');
		
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;');
			$xml->text('TOTAL');					
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style',$style);
			$xml->text(number_format($total_hh,2,",","."));					
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style',$style);
			$xml->text('R$ '.number_format($total_valor,2,",","."));					
		$xml->endElement();
		
		$xml->writeElement ('cell',' ');		
		
		foreach($array_lucros as $indice_lucro)
		{
			
			$style = 'font-weight:bold;';
			
			$xml->startElement ('cell');
				$xml->writeAttribute('style',$style);
				$xml->text('R$ '.number_format($total_custo[$indice_lucro],2,",","."));					
			$xml->endElement();
			
			$total_custo[$indice_lucro] = 0;						
		}
		
	$xml->endElement();	
	
	$xml->endElement();
				
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_adm_dvm',true,'390','".$conteudoResumo."');");
	
	//Mostra os indices utilizados
	$i = 1;
	
	foreach($array_lucros as $indice)
	{		
		$resposta->addAssign("dgc_".$i,"innerHTML",number_format($fator_venda[$indice],4,",","."));
		
		$i++;		
	}	
	
	$resposta->addScript("xajax_escolha_margens(xajax.getFormValues('frm'))");
	
	return $resposta;
}

function concluir_proposta($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;	
	
	//SELECIONA A PROPOSTA	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_proposta = $db->array_select[0];
	
	$array_cliente = dados_proposta($reg_proposta["numero_proposta"]);
	
	if($reg_proposta["id_status_fpv"]==3) //VALORIZAÇÃO
	{
		$array_indices = indices(true);
	}
	else
	{	
		$array_indices = indices(false);
	}
	
	//soma as margens
	for($i=4;$i<=8;$i++)
	{
		$margem += $array_indices[$i];
	}
	
	//soma os impostos (PCC)
	for($i=1;$i<=3;$i++)
	{
		$pcc += $array_indices[$i];
		
		if($i>=2)
		{
			$pis_cof += $array_indices[$i];	
		}		
	}
	
	$ind_reajuste = empty($dados_form["reajuste"])?0:(str_replace(",",".",$dados_form["reajuste"])/100);
	
	switch ($dados_form["contrato"])
	{
		case 1: //PJ
			
			$ind_fatork = 1;
			
			$ind_pis_cof = empty($pis_cof)?0:($pis_cof/100);
				
		break;
		
		case 2: //CLT-MES
		
			$ind_fatork = empty($array_indices[13])?0:$array_indices[13];
			
			$ind_clt_custo = empty($array_indices[15])?0:($array_indices[15]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
			
		break;
		
		case 3: //CLT-HORA
		
			$ind_fatork = empty($array_indices[12])?0:$array_indices[12];
			
			$ind_clt_custo = empty($array_indices[16])?0:($array_indices[16]/100);
			
			if($dados_form["periculosidade"])
			{
				$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
			}
		
		break;
	}
	
	if($dados_form["periculosidade"])
	{
		$ind_peric = empty($array_indices[14])?0:($array_indices[14]/100);
	}
	
	$ind_moi_mod = empty($array_indices[11])?0:$array_indices[11];
	
	$imp_lucro = empty($array_indices[10])?0:($array_indices[10]/100);
	
	$imp_s_lucro = (($dados_form["sel_margem"]/100)/(1-$imp_lucro))-($dados_form["sel_margem"]/100);		
	
	$lucro_bruto = $imp_s_lucro + ($dados_form["sel_margem"]/100);
	
	$margem_bruto = $lucro_bruto + ($margem/100);
	
	$fator_venda = 1/(1-($pcc/100)-$margem_bruto);
	
	//EXCLUI OS REGISTROS DAS TABELAS
	$usql = "UPDATE ".DATABASE.".propostas_indices SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_proposta = '".$dados_form["id_proposta"]."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$usql = "UPDATE ".DATABASE.".propostas_valorizacao SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_proposta = '".$dados_form["id_proposta"]."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	//seleciona o cliente
	$sql = "SELECT * FROM ".DATABASE.".empresas ";
	$sql .= "WHERE id_cod_protheus = '".$array_cliente["id_cliente"]."' ";
	$sql .= "AND id_loja_protheus = '".$array_cliente["loja"]."' ";
	$sql .= "AND empresas.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_cliente = $db->array_select[0]; 
	
	//sumariza os indices e margens
	$sql = "SELECT * FROM ".DATABASE.".indices_fpv, ".DATABASE.".indices_fpv_historico ";
	$sql .= "WHERE indices_fpv.reg_del = 0 ";
	$sql .= "AND indices_fpv_historico.reg_del = 0 ";
	$sql .= "AND indices_fpv.id_indice_atual = indices_fpv_historico.id_indice_fpv_historico ";
	$sql .= "GROUP BY indices_fpv.id_tipo_indice ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{	
		$array_indices[$regs["id_tipo_indice"]] = $regs["percentual"];
		
		//INSERE OS ITENS UTILIZADOS
		$isql = "INSERT INTO ".DATABASE.".propostas_indices (id_proposta, 	id_indice_fpv_historico) VALUES ( ";
		$isql .= "'".$dados_form["id_proposta"]."', ";
		$isql .= "'".$regs["id_indice_fpv_historico"]."')";
		
		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
	}
	
	//sumariza os recursos associados a proposta	
	$sql = "SELECT SUM(horas_orcamento) AS HORAS, id_recurso_orcamento, setores.id_setor FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".recursos_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao, ".DATABASE.".escopo_geral_valorizacao ";
	$sql .= "WHERE recursos_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_geral_valorizacao.reg_del = 0 ";
	
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_detalhado = recursos_valorizacao.id_escopo_detalhado ";
	$sql .= "AND escopo_detalhado_valorizacao.id_escopo_geral = escopo_geral_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "GROUP BY setores.id_setor, recursos_valorizacao.id_recurso_orcamento ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		$array_recursos[$regs["id_setor"]][$regs["id_recurso_orcamento"]] += $regs["HORAS"];
	}
	
	//seleciona as atividades x categorias profissionais (cliente guarda-chuva)
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo_cliente, ".DATABASE.".tabela_valor_mo_historico_cliente ";
	$sql .= "WHERE tabela_valor_mo_cliente.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico_cliente.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_cliente.id_cliente = '".$reg_cliente["id_empresa"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo_cliente.id_cargo ";
	$sql .= "AND tabela_valor_mo_cliente.id_tabela_valor_cliente_atual = tabela_valor_mo_historico_cliente.id_tabela_valor_mo_historico_cliente ";//obtem o valor atual
	$sql .= "ORDER BY rh_cargos.ordem_tarifas ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs_cliente)
	{
		
		$valor_dvm_tmp = empty($regs_cliente["valor_interno"])?0:$regs_cliente["valor_interno"];
		
		$valor_cli_tmp = empty($regs_cliente["valor_cli"])?0:$regs_cliente["valor_cli"];
		
		$array_valor_dvm[$regs_cliente["id_cargo_grupo"]]['valor'] = $valor_dvm_tmp;
		
		$array_valor_cliente[$regs_cliente["id_cargo_grupo"]]['valor'] = $valor_cli_tmp;
		
		//utilizado para gravar na tabela proposta_valorização
		$array_mo_cliente_historico[$regs_cliente["id_cargo_grupo"]] = $regs_cliente["id_tabela_valor_mo_historico_cliente"];
	}	
	
	//seleciona as atividades x categorias profissionais
	$sql = "SELECT *, tabela_valor_mo_historico.valor AS VALOR FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".rh_cargos, ".DATABASE.".tabela_valor_mo, ".DATABASE.".tabela_valor_mo_historico, ".DATABASE.".atividades_orcamento, ".DATABASE.".escopo_geral_valorizacao, ".DATABASE.".escopo_detalhado_valorizacao ";
	$sql .= "WHERE escopo_geral_valorizacao.reg_del = 0 ";
	$sql .= "AND escopo_detalhado_valorizacao.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND rh_cargos.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo_historico.reg_del = 0 ";
	$sql .= "AND tabela_valor_mo.id_regiao = '".$dados_form["regiao"]."' ";
	$sql .= "AND rh_cargos.id_cargo_grupo = tabela_valor_mo.id_cargo ";
	$sql .= "AND tabela_valor_mo.id_tabela_valor_atual = tabela_valor_mo_historico.id_tabela_valor_mo_historico ";//obtem o valor atual
	
	$sql .= "AND escopo_geral_valorizacao.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND escopo_geral_valorizacao.id_escopo_geral = escopo_detalhado_valorizacao.id_escopo_geral ";
	$sql .= "AND escopo_detalhado_valorizacao.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";
	
	$sql .= "AND atividades.id_atividade = atividades_orcamento.id_atividade ";
	$sql .= "AND atividades_orcamento.id_cargo = rh_cargos.id_cargo_grupo ";
	
	$sql .= "AND setores.abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";	
	
	$sql .= "GROUP BY setores.id_setor, rh_cargos.id_cargo_grupo ";
	
	$sql .= "ORDER BY setores.ordem, rh_cargos.ordem_tarifas ";

	$db->select($sql,'MYSQL',true);
	
	$array_disciplinas = $db->array_select;
	
	//sumariza os itens de cada setores, para gerar as linhas de sub-total
	foreach($array_disciplinas as $regs)
	{
		if($regs["id_categoria"]==6 || $regs["id_categoria"]==1 || in_array($regs["id_setor"],array(5)))
		{
			$array_num[99] += 1;
		}
		else
		{
			$array_num[$regs["id_setor"]] += 1;		
		}				
	}	

	//sumariza os itens de cada categoria, para gerar a linha de sub-total		
	foreach($array_disciplinas as $regs)
	{		
		
		$hh = empty($array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]])?0:$array_recursos[$regs["id_setor"]][$regs["id_cargo_grupo"]];
		
		$reajuste = $regs["VALOR"]+($regs["VALOR"]*$ind_reajuste);
		
		$valor_reajuste = ($reajuste * $ind_fatork);		

		$valor_hh = ($valor_reajuste/176);
		
		if($dados_form["contrato"]==1)//pj
		{		
			$valor_calculado = $valor_hh - ($valor_hh * $ind_pis_cof);
		}
		else
		{	
			$valor_peric = $valor_hh + ($valor_hh * $ind_peric);
			
			$valor_calculado = $valor_peric + ($valor_peric * $ind_clt_custo);
		}		
		
		$valor_cust_moi_mod = ($valor_calculado * $ind_moi_mod);
		
		$valor_venda = ($valor_cust_moi_mod * $fator_venda);
		
		//VALOR
		$valor_hh_dvm = round($hh * $valor_venda,2);
		
		$valor_cliente = $array_valor_cliente[$regs["id_cargo_grupo"]]['valor']; 
		
		if($valor_cliente>0)
		{
			$fator_cliente = ($valor_venda/$valor_cliente);
		}
		else
		{
			$fator_cliente = 0;
		}
		
		//HORAS CLIENTE
		$cli_hh = round($hh * $fator_cliente,2);
		
		//VALOR CLIENTE
		$valor_hh_cli = round($cli_hh * $valor_cliente,2);
		
		$valor_cliente_interno= $array_valor_dvm[$regs["id_cargo_grupo"]]['valor']; 
		
		if($valor_cliente_dvm>0)
		{
			$fator_cliente_dvm = ($valor_venda/$valor_cliente_dvm);
		}
		else
		{
			$fator_cliente_dvm = 0;
		}
		
		//HORAS CLIENTE
		$cli_hh_dvm = round($hh * $fator_cliente_dvm,2);
		
		//VALOR CLIENTE 
		$valor_hh_cli_dvm = round($cli_hh_dvm * $valor_cliente_dvm,2);
		
		//insere os valores na tabela de valorização (historico)
		$isql = "INSERT INTO ".DATABASE.".propostas_valorizacao (id_proposta, id_cargo, id_tabela_mo_historico, id_tabela_mo_historico_cliente, horas_interna, valor_interno, horas_cliente, valor_cliente, horas_cliente_interno, valor_cliente_dvm) VALUES ( ";
		$isql .= "'".$dados_form["id_proposta"]."', ";
		$isql .= "'".$regs["id_cargo_grupo"]."', ";
		$isql .= "'".$regs["id_tabela_valor_mo_historico"]."', ";
		$isql .= "'".$array_mo_cliente_historico[$regs["id_cargo_grupo"]]."', ";
		$isql .= "'".$hh."', ";
		$isql .= "'".$valor_hh_dvm."', ";
		$isql .= "'".$cli_hh."', ";
		$isql .= "'".$valor_hh_cli."', ";
		$isql .= "'".$cli_hh_dvm."', ";
		$isql .= "'".$valor_hh_cli_dvm."') ";
	
		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
	}
	
	//atualiza campos na proposta com os indices utilizados	
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "id_regiao = '".$dados_form["regiao"]."', ";
	$usql .= "percentual_reajuste = '".number_format(str_replace(",",".",$dados_form["reajuste"]),2,'.','')."', ";
	$usql .= "tipo_contrato = '".$dados_form["contrato"]."', ";
	$usql .= "margem_aplicada = '".number_format(str_replace(",",".",$dados_form["lucro_liq"]),2,'.','')."', ";
	$usql .= "percentual_margem_aplicada = '".number_format(str_replace(",",".",$dados_form["sel_margem"]),2,'.','')."', ";
	$usql .= "id_executante = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "id_status_fpv = '3' "; //valorizado
	$usql .= "WHERE id_proposta = '".$dados_form["id_proposta"]."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$resposta->addAlert('Proposta valorizada.');
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("preenche_resumo");
$xajax->registerFunction("importar");
$xajax->registerFunction("preenche_categorias");
$xajax->registerFunction("preenche_guarda_chuva_dvm");
$xajax->registerFunction("preenche_adm_dvm");
$xajax->registerFunction("escolha_margens");
$xajax->registerFunction("concluir_proposta");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_escolha_margens(xajax.getFormValues('frm'));xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script language="javascript" type="text/javascript">

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	function sel_tab(idNew,idOld)
	{
		//ativa quando seleciona a tab		
		switch(idNew)
		{
			case "a10_":
			
				document.getElementById('status').disabled = false;
				
				document.getElementById('btnvoltar').disabled = false;
				
				xajax_atualizatabela(xajax.getFormValues('frm'));
															
			break;
			
			case "a20_":
				
				
			break;
			
			case "a30_":
				
			break;
			
			case "a40_":
				
				document.getElementById('status').disabled = true;
				
				document.getElementById('btnvoltar').disabled = true;
					
				xajax_preenche_resumo(xajax.getFormValues('frm'));
			break;
			
			case "a50_":
				
				document.getElementById('status').disabled = true;
				
				document.getElementById('btnvoltar').disabled = true;
					
				xajax_preenche_categorias(xajax.getFormValues('frm'));
			break;
			
			case "a60_":
				
				document.getElementById('status').disabled = true;
				
				document.getElementById('btnvoltar').disabled = true;
					
				xajax_preenche_guarda_chuva(xajax.getFormValues('frm'));
			break;
			
			case "a70_":
				
				document.getElementById('status').disabled = true;
				
				document.getElementById('btnvoltar').disabled = true;
					
				xajax_preenche_guarda_chuva_dvm(xajax.getFormValues('frm'));
			break;
			
			case "a80_":
				
				document.getElementById('status').disabled = true;
				
				document.getElementById('btnvoltar').disabled = true;
					
				xajax_preenche_adm_dvm(xajax.getFormValues('frm'));
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a10_", "Proposta", null, null, true);

	myTabbar.addTab("a40_", "Resumo");
	
	myTabbar.addTab("a50_", "Contrato padrão");
	
	myTabbar.addTab("a70_", "Contrato Guarda-chuva Preço Global");
	
	myTabbar.addTab("a80_", "Contrato Guarda-chuva ADM");
	
	myTabbar.tabs("a10_").attachObject("a10");

	myTabbar.tabs("a40_").attachObject("a40");
	
	myTabbar.tabs("a50_").attachObject("a50");
	
	myTabbar.tabs("a70_").attachObject("a70");
	
	myTabbar.tabs("a80_").attachObject("a80");
	
	myTabbar.enableAutoReSize(true);
}


function grid(tabela, autoh, height, xml)
{
	if (tabela != 'div_resumo')
	{
		switch (tabela)
		{
			case 'div_dados_cliente':
			
				mygrid1 = new dhtmlXGridObject(tabela);
				mygrid1.enableAutoHeight(autoh,height);
				mygrid1.enableRowsHover(true,'cor_mouseover');
			
				function doOnRowSelected1(row,col)
				{
					if(col<=3)
					{						
						xajax_editar(row);
			
						return true;
					}
				}
				
				mygrid1.attachEvent("onRowSelect",doOnRowSelected1);	
				mygrid1.setHeader(" ,Proposta, Descrição,I,E",
					null,
					["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				mygrid1.setInitWidths("22,80,*,35,35");
				mygrid1.setColAlign("center,left,left,center,center");
				mygrid1.setColTypes("ro,ro,ro,ro,ro");
				mygrid1.setColSorting("str,str,str,str,str");
				
				mygrid1.setSkin("dhx_skyblue");
				mygrid1.enableMultiselect(true);
				mygrid1.enableCollSpan(true);	
				mygrid1.init();
				mygrid1.loadXMLString(xml);
				
			break;
			
			case 'div_categorias':
			
				var text_margem;
			
				text_margem = document.getElementById('lucro_liq').value;
				
				text_margem = text_margem.toString().replace( ",", "." );
				
				mygrid5_resumo = new dhtmlXGridObject(tabela);
		
				mygrid5_resumo.enableAutoHeight(autoh,height);
				mygrid5_resumo.enableRowsHover(true,'cor_mouseover');
				
				mygrid5_resumo.setHeader("Categoria,Profissional,Custo/Mês,R$/Mês,R$/Hh,Custo<br />MOI/MOD,Horas/Qtd,Custo/<br />Profissional,Valor venda,#cspan,#cspan,#cspan,#cspan",
										null,
										["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				
				mygrid5_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","0%","5%","7.5%","10%",""+text_margem+"%"],
										["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				
				mygrid5_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","<div id='dv_cat1'> </div>","<div id='dv_cat2'> </div>","<div id='dv_cat3'> </div>","<div id='dv_cat4'> </div>","<div id='dv_cat5'> </div>"],
										["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				
				mygrid5_resumo.setInitWidths("85,160,75,60,60,70,70,90,90,90,90,90,90");
				mygrid5_resumo.setColAlign("left,left,right,right,right,right,right,right,right,right,right,right,right");
				mygrid5_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid5_resumo.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str");
			
				mygrid5_resumo.setSkin("dhx_skyblue");
				mygrid5_resumo.enableMultiselect(true);
				mygrid5_resumo.enableCollSpan(true);
				
				mygrid5_resumo.init();
				mygrid5_resumo.loadXMLString(xml);
			break;
			
			case 'div_guarda_chuva_dvm':
			
				var text_margem;
			
				text_margem = document.getElementById('lucro_liq').value;
				
				text_margem = text_margem.toString().replace( ",", "." );
			
				mygrid7_resumo = new dhtmlXGridObject(tabela);
		
				mygrid7_resumo.enableAutoHeight(autoh,height);
				mygrid7_resumo.enableRowsHover(true,'cor_mouseover');				

				mygrid7_resumo.setHeader("Categoria,Profissional,R$/Hh no<br />Cliente,Horas,#cspan,#cspan,#cspan,#cspan, ,Valor venda,#cspan,#cspan,#cspan,#cspan",
										null,
										["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
				
				mygrid7_resumo.attachHeader(["#rspan","#rspan","#rspan","0%","5%","7.5%","10%",""+text_margem+"%"," ","0%","5%","7.5%","10%",""+text_margem+"%"],
											["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
				
				mygrid7_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan"," ","<div id='dgd_1'> </div>","<div id='dgd_2'> </div>","<div id='dgd_3'> </div>","<div id='dgd_4'> </div>","<div id='dgd_5'> </div>"],
											["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);

				
				mygrid7_resumo.setInitWidths("85,160,70,60,60,60,60,60,50,80,80,80,80,80");
				mygrid7_resumo.setColAlign("left,left,left,right,right,right,right,right,right,right,right,right,right,right");
				mygrid7_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid7_resumo.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	
				mygrid7_resumo.setSkin("dhx_skyblue");
				mygrid7_resumo.enableMultiselect(true);
				mygrid7_resumo.enableCollSpan(true);
					
				mygrid7_resumo.init();
				mygrid7_resumo.loadXMLString(xml);
			break;
			
			case 'div_adm_dvm':
			
				var text_margem;
			
				text_margem = document.getElementById('lucro_liq').value;
				
				text_margem = text_margem.toString().replace( ",", "." );
			
				mygrid8_resumo = new dhtmlXGridObject(tabela);
		
				mygrid8_resumo.enableAutoHeight(autoh,height);
				mygrid8_resumo.enableRowsHover(true,'cor_mouseover');				

				mygrid8_resumo.setHeader("Categoria,Profissional,R$/Hh no<br />Cliente,Horas,Valor venda, ,Valor custo,#cspan,#cspan,#cspan,#cspan",
										null,
										["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
				
				mygrid8_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","0%","5%","7.5%","10%",""+text_margem+"%"],
											["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
				
				//mygrid8_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan"," ","<div id='dgd_1'> </div>","<div id='dgd_2'> </div>","<div id='dgd_3'> </div>","<div id='dgd_4'> </div>","<div id='dgd_5'> </div>"],
				//							["text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:left;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);

				
				mygrid8_resumo.setInitWidths("85,160,70,70,80,50,65,65,65,65,65");
				mygrid8_resumo.setColAlign("left,left,left,right,right,right,right,right,right,right,right");
				mygrid8_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid8_resumo.setColSorting("str,str,str,str,str,str,str,str,str,str,str");

				mygrid8_resumo.setSkin("dhx_skyblue");
				mygrid8_resumo.enableMultiselect(true);
				mygrid8_resumo.enableCollSpan(true);
					
				mygrid8_resumo.init();
				mygrid8_resumo.loadXMLString(xml);
			break;	
										
		}
	}
	else
	{
		mygrid_resumo = new dhtmlXGridObject(tabela);

		mygrid_resumo.enableAutoHeight(autoh,height);
		mygrid_resumo.enableRowsHover(true,'cor_mouseover');
		
		mygrid_resumo.setHeader("Escopo Geral,Disciplina,Tarefa,Descrição,Fmt,Qtd,Horas");
		mygrid_resumo.setInitWidths("85,80,50,*,*,60,60");
		mygrid_resumo.setColAlign("left,left,left,left,left,left,left");
		mygrid_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro");
		mygrid_resumo.setColSorting("str,str,str,str,str,str,str");

		mygrid_resumo.setSkin("dhx_skyblue");
	    mygrid_resumo.enableMultiselect(true);
	    mygrid_resumo.enableCollSpan(true);
			
		mygrid_resumo.init();
		mygrid_resumo.loadXMLString(xml);
		mygrid_resumo.setSizes();
	}	
}

//seta os campos conforme tipo de contrato
function muda_contrato(id_contrato)
{
	
	switch (id_contrato)
	{
		case '1': //PJ
			document.getElementById('div_per').style.visibility = "hidden";			
			document.getElementById('div_ins').style.visibility = "hidden";
			
		break;
		
		case '2': //CLT-MES
		case '3': //CLT-HORA

			document.getElementById('div_per').style.visibility = "visible";
			document.getElementById('div_ins').style.visibility = "visible";		
		break;		
	}

}

function imprimir()
{

}

</script>

<?php
$conf = new configs();

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE abreviacao NOT IN ('ADM','DES','CMS','CON','COM','FIN','GOB','MON','SUP','MAT','OUT','GER','TIN','RHM') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_disciplina_values[] = $regs["id_setor"];
	$array_disciplina_output[] = $regs["setor"];
}

$array_status = array(0=>'TODAS',4=>'EXPORTADO PROTHEUS',3=>'VALORIZADO',2=>'IMPORTADO',1=>'NÃO IMPORTADO');

foreach ($array_status as $chave=>$valor)
{
	$array_status_values[] = $chave;
	$array_status_output[] = $valor;
}

//seleciona as regioes
$sql = "SELECT * FROM ".DATABASE.".regiao ";
$sql .= "WHERE regiao.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_regiao_values[] = $regs["id_regiao"];
	$array_regiao_output[] = $regs["regiao"];
}

$smarty->assign("option_disciplina_values",$array_disciplina_values);
$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("option_regiao_values",$array_regiao_values);
$smarty->assign("option_regiao_output",$array_regiao_output);
$smarty->assign("selecionado","1");

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('proposta_valorizacao'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('proposta_valorizacao.tpl');

?>

