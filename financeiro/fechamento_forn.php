<?php
/*
		Formulário de Fechamento PJ	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/fechamento_forn.php
		
		Versão 0 --> VERSÃO INICIAL : 06/03/2012
		Versão 1 --> Alterar periodo Mensalista (01 a ult. dia mes)
		Versão 2 --> atualização classe banco de dados - 20/01/2015 - Carlos Abreu
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

$xajax->registerFunction("voltar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$db = new banco_dados;

$filtro = "";

$sql = "SELECT periodo, data_ini, data_fim, liberado FROM ".DATABASE.".fechamento_folha ";
$sql .= "WHERE fechamento_folha.liberado = '1' ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND fechamento_folha.id_funcionario = '" . $id_funcionario . "' ";
$sql .= "ORDER BY fechamento_folha.data_fim ";

$db->select($sql,'MYSQL',true);

$cont_permissao = $db->array_select[0];

if($_GET["periodo"])
{
	$datas = explode(",",$_GET["periodo"]);
	$filtro = "AND fechamento_folha.periodo = '" . $_GET["periodo"] . "' ";
}
else
{
	$filtro = "AND fechamento_folha.periodo = '" . $cont_permissao["periodo"] . "' ";
	$_GET["periodo"] = $cont_permissao["periodo"];
}

$smarty->assign("id_funcionario",$id_funcionario);

$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "LEFT JOIN ".DATABASE.".rh_cargos ON (rh_cargos.id_cargo_grupo = rh_funcoes.id_cargo_grupo) ";
$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND fechamento_folha.id_funcionario = '" . $id_funcionario . "' ";
$sql .= "AND fechamento_folha.liberado = '1' ";
$sql .= "AND fechamento_folha.excessao = '0' ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
$sql .= $filtro;

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{
	$fechamento_folha = $db->array_select[0];
		
	//Salario atual
	$sql2 = "SELECT * FROM ".DATABASE.".salarios ";
	$sql2 .= "WHERE salarios.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' ";
	$sql2 .= "AND salarios.reg_del = 0 ";
	
	if($fechamento_folha["data_fim"]!="")
	{
		$sql2 .= "AND DATE_FORMAT(data , '%Y%m%d' ) < '".str_replace("-","",$fechamento_folha["data_fim"])."' ";
	}
	else
	{
		$sql2 .= "AND DATE_FORMAT(data , '%Y%m%d' ) < '".date('Ymd')."' ";
	}
	
	$sql2 .= "ORDER BY id_salario DESC LIMIT 1 ";
	
	$db->select($sql2,'MYSQL',true);
	
	$cont2 = $db->array_select[0];	
	
	$header = '<div style="z-index:2; width: 100%; background: #FFF;margin: 0 auto; overflow: no;">';
	$footer = '</div>';	
	
	//DADOS DO FUNCIONÁRIO
	$conteudo_f = '<div class="nom">';
	$conteudo_f .= $fechamento_folha["funcionario"];
	$conteudo_f .= '</div>';
	
	//DADOS DA MEDIÇÃO
	//periodo
	$conteudo_d .= '<div class="label1">Período:</div>';
	$conteudo_d .= '<div class="label2">';
	
	if($cont2[" tipo_contrato"]!='SC+MENS' && $cont2[" tipo_contrato"]!='SC+CLT+MENS')
	{
		$conteudo_d .= mysql_php($fechamento_folha["data_ini"]).' á '.mysql_php($fechamento_folha["data_fim"]);
	}
	else
	{
		$dat = explode("/",mysql_php($fechamento_folha["data_fim"]));
		
		$timestamp = mktime(0,0,0,$dat[1],$dat[0],$dat[2]);
		
		$conteudo_d .= '01'.substr(mysql_php($fechamento_folha["data_fim"]),2).' á '.date("t",$timestamp).substr(mysql_php($fechamento_folha["data_fim"]),2);
	}
	
	$conteudo_d .= '</div>';
	
	//Função
	$conteudo_d .= '<div class="label1">Função:</div>';
	$conteudo_d .= '<div class="label2">';
	$conteudo_d .= $fechamento_folha["grupo"];
	$conteudo_d .= '</div>';
	
	//tipo Contrato
	$conteudo_d .= '<div class="label1">Contrato:</div>';
	$conteudo_d .= '<div class="label2">';
	$conteudo_d .= $cont2[" tipo_contrato"];
	$conteudo_d .= '</div>';
	
	//valor Hora (Contrato SC)
	if($cont2[" tipo_contrato"]=="SC" || $cont2[" tipo_contrato"]=="SC+CLT")
	{
		$conteudo_d .= '<div class="label1">Valor Hora:</div>';
		$conteudo_d .= '<div class="label2">R$ ';
		$conteudo_d .= formatavalor($cont2["salario_hora"]);
		$conteudo_d .= '</div>';	
	}
	
	//valor Registro (Contrato CLT ou EST)
	if($cont2[" tipo_contrato"]=="CLT" || $cont2[" tipo_contrato"]=="SC+CLT" || $cont2[" tipo_contrato"]=="SC+CLT+MENS" || $cont2[" tipo_contrato"]=="EST")
	{
		$conteudo_d .= '<div class="label1">Valor Registro:</div>';
		$conteudo_d .= '<div class="label2">R$ ';
		
		if($fechamento_folha["valor_proporcional"]>0) 
		{ 
			$conteudo_d .= formatavalor($fechamento_folha["valor_proporcional"]); 
		} 
		else
		{ 
			$conteudo_d .= formatavalor($cont2["salario_clt"]); 
		}
		
		$conteudo_d .= '</div>';
	}
	
	//valor Mensal (Contrato MENS)
	if($cont2[" tipo_contrato"]=="SC+CLT+MENS" || $cont2[" tipo_contrato"]=="SC+MENS")
	{
		$conteudo_d .= '<div class="label1">Valor Mensalista:</div>';
		$conteudo_d .= '<div class="label2">R$ ';
		$conteudo_d .= formatavalor($cont2["salario_mensalista"]);
		$conteudo_d .= '</div>';
	}
	
	//Horas Normais
	$thn = explode(":",$fechamento_folha["total_horas_normais"]);
	
	if(intval($thn[0])!=0 || intval($thn[1])!=0)
	{
		$conteudo_d .= '<div class="label1">Horas Normais:</div>';
		$conteudo_d .= '<div class="label2">';		
		$conteudo_d .= $thn[0].':'.$thn[1];
		$conteudo_d .= '</div>';
	}
	
	
	//Horas Adicionais
	$tha = explode(":",$fechamento_folha["total_horas_adicionais"]);
	
	if(intval($tha[0])!=0 || intval($tha[1])!=0)
	{
		$conteudo_d .= '<div class="label1">Horas Adicionais:</div>';
		$conteudo_d .= '<div class="label2">';	
		$conteudo_d .= $tha[0].':'.$tha[1];
		$conteudo_d .= '</div>';
	}
	
	//Medição
	$conteudo_d .= '<div class="label1">Medição:</div>';
	$conteudo_d .= '<div class="label2">R$ ';
	$conteudo_d .= formatavalor($fechamento_folha["valor_medicao"]);
	$conteudo_d .= '</div>';
		
	//DADOS CLT
	if(($cont2[" tipo_contrato"]=="CLT" || $cont2[" tipo_contrato"]=="SC+CLT" || $cont2[" tipo_contrato"]=="SC+CLT+MENS"))
	{		
		//Mostra os detalhes da Férias.
		$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
		$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.reg_del = 0 "; 
		$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.tipo = 'diferenca_clt_ferias' ";
		$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";	
		
		$db->select($sql,'MYSQL',true);
		
		$reg_clt_ferias = $db->array_select; 
		
		$num_reg_ferias = $db->numero_registros;
		
		//Mostra os detalhes de Rescisão.
		$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
		$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.reg_del = 0 "; 
		$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
		$sql .= "AND fechamento_folha_detalhes.tipo = 'diferenca_clt_rescisao' ";
		$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
		
		$db->select($sql,'MYSQL',true);
		
		$reg_clt_rescisao = $db->array_select;
		
		$num_reg_rescisao = $db->numero_registros;
		
		if($fechamento_folha["valor_ferias"]>0 || $fechamento_folha["valor_decimo_terceiro"]>0 || $fechamento_folha["valor_rescisao"]>0 || $fechamento_folha["valor_fgts"]>0 || $num_reg_ferias>0 || $num_reg_rescisao>0)
		{
			$conteudo_d .= '<div class="tit">INFORMAÇÕES CLT</div>';
			
			//férias
			if($fechamento_folha["valor_ferias"]>0)
			{		
				$conteudo_d .= '<div class="label1">Férias:</div>';
				$conteudo_d .= '<div class="label2">R$ ';
				$conteudo_d .= formatavalor($fechamento_folha["valor_ferias"]);   
				$conteudo_d .= '</div>';
			}
			
			//13º
			if($fechamento_folha["valor_decimo_terceiro"]>0)
			{
				$conteudo_d .= '<div class="label1">Décimo Terceiro:</div>';
				$conteudo_d .= '<div class="label2">R$ ';
				$conteudo_d .= formatavalor($fechamento_folha["valor_decimo_terceiro"]);	
				$conteudo_d .= '</div>';
			}
		
			//Rescisão
			if($fechamento_folha["valor_rescisao"]>0)
			{	
				$conteudo_d .= '<div class="label1">Rescisão:</div>';
				$conteudo_d .= '<div class="label2">R$ ';
				$conteudo_d .= formatavalor($fechamento_folha["valor_rescisao"]);	
				$conteudo_d .= '</div>';
			}
			
			//valor_fgts
			if($fechamento_folha["valor_fgts"]>0)
			{
				$conteudo_d .= '<div class="label1">Valor Fgts:</div>';
				$conteudo_d .= '<div class="label2">R$ ';
				$conteudo_d .= formatavalor($fechamento_folha["valor_fgts"]);	
				$conteudo_d .= '</div>';
			}
			
			//DADOS SOBRE CLT	
			if($num_reg_ferias>0)
			{
				$conteudo_d .= '<div class="tit">DIFERENÇA FÉRIAS</div>';
				
				foreach($reg_clt_ferias as $cont_clt_ferias)
				{
					$conteudo_d .= '<div class="label3">'.$cont_clt_ferias["descricao"].'</div>';
					$conteudo_d .= '<div class="label4">R$ '.formatavalor($cont_clt_ferias["valor"]).'</div>';
				}
				
			}
			
			if($num_reg_rescisao>0)
			{
				$conteudo_d .= '<div class="tit">DIFERENÇA RESCISÃO</div>';
				
				foreach($reg_clt_rescisao as $cont_clt_rescisao)
				{
					$conteudo_d .= '<div class="label3">'.$cont_clt_rescisao["descricao"].'</div>';
					$conteudo_d .= '<div class="label4">R$ '.formatavalor($cont_clt_rescisao["valor"]).'</div>';
				}
			}			
		}	
	}
	
	//DADOS DA NOTA FISCAL
	$conteudo_n .= '<div class="tit" style="margin-top: 1px;">NOTA FISCAL</div>';
	
	//valor da nota
	$conteudo_n .= '<div class="label5">valor Nota Fiscal:</div>';
	$conteudo_n .= '<div class="label2">R$ ';
	$conteudo_n .= formatavalor($fechamento_folha["valor_total"]);
	$conteudo_n .= '</div>';
	
	$periodo = explode(",",$fechamento_folha["periodo"]);
	
	$data = explode("-",$periodo[1]); //data[0] = ano, data[1] = mes
	
	$data_formada = mktime(0,0,0,$data[1]+1,1,$data[0]);//mes seguinte ao fechamento
	
	//data emissão  
	$conteudo_n .= '<div class="label5">Data de Emissão:</div>';
	$conteudo_n .= '<div class="label2"><font color=red><strong>'.date('d/m/Y',$data_formada).'</strong></font></div>';
	
	//Botão Dados  
	$conteudo_n .= '<div id="div" style="position:relative; background-color:#CCC; border-style:outset; border-width:1px; text-align:center; cursor:hand;  margin-top: 1px; margin-bottom: 10px;" onclick="mostra_dadosfat(this);">DADOS P/ FATURAMENTO</div>';
	
	//DADOS DE IMPOSTOS
	$conteudo_n .= '<div class="tit">IMPOSTOS</div>';
	
	//IR
	if($fechamento_folha["valor_imposto"]>0)
	{
		$conteudo_n .= '<div class="label5">IR(1,5%):</div>';
		$conteudo_n .= '<div class="label2">R$ ';
		$conteudo_n .= formatavalor($fechamento_folha["valor_imposto"]);
		$conteudo_n .= '</div>';
		
		$conteudo_n .= '<div> </div>';
		$conteudo_n .= '<div> </div>';			
	}
	
	if($fechamento_folha["valor_pcc"]>0)
	{
		//PIS
		$conteudo_n .= '<div class="label5">PIS(0,65%):</div>';
		$conteudo_n .= '<div class="label2">R$ ';
		$conteudo_n .= number_format((($fechamento_folha["valor_total"] * 0.65) / 100),2,",",".");		
		$conteudo_n .= '</div>';
		
		//COFINS
		$conteudo_n .= '<div class="label5">COFINS(3%):</div>';
		$conteudo_n .= '<div class="label2">R$ ';
		$conteudo_n .= number_format((($fechamento_folha["valor_total"] * 3) / 100),2,",",".");
		$conteudo_n .= '</div>';
		
		//CSLL
		$conteudo_n .= '<div class="label5">CSLL(1%):</div>';
		$conteudo_n .= '<div class="label2">R$ ';
		$conteudo_n .= number_format((($fechamento_folha["valor_total"] * 1) / 100),2,",",".");
		$conteudo_n .= '</div>';
		
		//TOTAL IMPOSTOS
		$conteudo_n .= '<div class="label5">TOTAL (PIS/COFINS/CSLL)(4,65%):</div>';
		$conteudo_n .= '<div class="label2">R$ ';
		$conteudo_n .= formatavalor($fechamento_folha["valor_pcc"]);
		$conteudo_n .= '</div>';
		
	}
	
	//INF ADICIONAIS
	//Mostra os detalhes de outros descontos.
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
	$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.reg_del = 0 "; 
	$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.tipo = 'outros_descontos' ";
	$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_outros_descontos = $db->array_select;
	
	if($db->numero_registros>0)
	{
		$conteudo_n .= '<div class="tit">OUTROS DESCONTOS</div>';
		
		foreach($reg_outros_descontos as $cont_outros_descontos)
		{
			$conteudo_n .= '<div class="label5">'.$cont_outros_descontos["descricao"].'</div>'; 
			$conteudo_n .= '<div class="label2">'.formatavalor($cont_outros_descontos["valor"]).'</div>';		
		}	
	}
	
	//Mostra os detalhes de acréscimos
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
	$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' "; 
	$sql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
	$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
	$sql .= "AND fechamento_folha_detalhes.tipo = 'outros_acrescimos' ";
	$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	$reg_outros_acrescimos = $db->array_select;
	
	if($db->numero_registros>0)
	{
		$conteudo_n .= '<div class="tit">OUTROS ACRÉSCIMOS</div>';
		
		foreach($reg_outros_acrescimos as $cont_outros_acrescimos)
		{	
			$conteudo_n .= '<div class="label5">'.$cont_outros_acrescimos["descricao"].'</div>';
			$conteudo_n .= '<div class="label2">'.formatavalor($cont_outros_acrescimos["valor"]).'</div>';
		}
	}	
	
	if($fechamento_folha["observacao"]!="")
	{	
		$conteudo_n .= '<div class="tit">INFORMAÇÕES ADICIONAIS</div>';
		
		$conteudo_n .= '<div class="label2">'.$fechamento_folha["observacao"].'</div>';
	}
	
	
	$smarty->assign("func",$header.$conteudo_f.$footer);
	$smarty->assign("dados",$header.$conteudo_d.$footer);
	$smarty->assign("nf",$header.$conteudo_n.$footer);
	$smarty->assign("documentos",$anex);	
	
	//Pegando os últimos 3 fechamentos do colaborador
	$sql = 	"SELECT * FROM ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE fechamento_folha.id_funcionario = '".$id_funcionario."' ";
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
		foreach($db->array_select as $reg)
		{
			$mesAno = explode('-', $reg['data_ini']);
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
			AND tipo_empresa IN('".$fechamento_folha['tipo_empresa']."', 0)
			AND id_fechamento_docs IS NULL
			AND calcular = 1
			ORDER BY ordem";
	
		$db->select($sql,'MYSQL', true);
		
		$reg = $db->array_select[0];
		
		if ($reg['docsFaltando'] > 0)
		{
			$smarty->assign('mensagem_bloqueio', 'Existem documentos da competência '.$competenciaAnterior.' a serem anexados.<br />Para evitar bloqueio no pagamento, regularize a situação.');
		}
}
else
{
	?>
    <script>
	alert('Não há registros neste período.');
	</script>
    <?php	
}

$smarty->assign("classe",CSS_FILE);

$smarty->display('fechamento_forn.tpl');

?>

<script>

function mostra_dadosfat(div)
{

	if(!document.getElementById('div_dadosfat'))
	{	
		var obj_dadosfat = document.createElement('div');
		
		//Define as propriedades do <DIV> do objeto obj_dadosfat
		obj_dadosfat.id = 'div_dadosfat';
		obj_dadosfat.innerHTML = '<BR><p>EMPRESA<BR><BR><BR> <BR>';
		obj_dadosfat.style.background = "#EEEEEE";
		obj_dadosfat.style.position = "absolute";
		obj_dadosfat.style.left = '-50px';
		obj_dadosfat.style.top = '16px';
	
		obj_dadosfat.style.borderRightWidth = '1px';
		obj_dadosfat.style.borderRightStyle = 'solid';
		obj_dadosfat.style.borderRightColor = '#999999';
	
		obj_dadosfat.style.borderBottomWidth = '1px';
		obj_dadosfat.style.borderBottomStyle = 'solid';
		obj_dadosfat.style.borderBottomColor = '#999999';
	
		obj_dadosfat.style.borderLeftWidth = '1px';
		obj_dadosfat.style.borderLeftStyle = 'solid';
		obj_dadosfat.style.borderLeftColor = '#FFFFFF';
		
		obj_dadosfat.style.borderTopWidth = '1px';
		obj_dadosfat.style.borderTopStyle = 'solid';
		obj_dadosfat.style.borderTopColor = '#FFFFFF';	
	
		obj_dadosfat.style.width = '230px';
	
		div.appendChild(obj_dadosfat);		
		
		div.style.borderStyle='inset';	
	
	}
	else
	{
		div.removeChild(document.getElementById('div_dadosfat'));
		div.style.borderStyle='outset';
	}

}

</script>