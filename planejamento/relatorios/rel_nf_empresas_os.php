<?php
/*
		Relatório NF x empresas
		
		Criado por Carlos Abreu / Otávio Pamplona  
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_nf_empresas_os.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

	require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
	
	$db = new banco_dados;

	$datas = explode("#",$_POST["periodo"]);

	$data_ini = $datas[0];
	$data_fim = $datas[1];
	
	$periodo_ini = explode("-",$data_ini);
	$periodo_fim = explode("-",$data_fim);
	
	$periodo_fechamento = $periodo_ini[0]."-" .$periodo_ini[1]. "," . $periodo_fim[0]."-" .$periodo_fim[1];
	
	$sql = "SELECT *,SUM(fechamento_folha.valor_imposto) AS IR, SUM(NfsFunc_valor) AS NfsFunc_valor FROM ".DATABASE.".empresa_funcionarios, ".DATABASE.".funcionarios, ".DATABASE.".fechamento_folha, ".DATABASE.".nf_funcionarios ";
	$sql .= "WHERE empresa_funcionarios.id_empfunc = funcionarios.id_empfunc ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND empresa_funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND nf_funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = fechamento_folha.id_funcionario ";	
	$sql .= "AND fechamento_folha.id_fechamento = nf_funcionarios.id_fechamento ";	
	$sql .= "AND fechamento_folha.periodo = '" . $periodo_fechamento . "' "; 
	$sql .= "AND nf_funcionarios.nf_ajuda_custo <> 1 ";
	$sql .= "GROUP BY empresa_funcionarios.id_empfunc, nf_funcionarios.nf_numero ";
	$sql .= "ORDER BY empresa_funcionarios.empresa_func ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $reg_empresas)
	{			
		$data .= $reg_empresas["nf_numero"] . ";" . $reg_empresas["empresa_func"] . ";" . $reg_empresas["NfsFunc_valor"] . ";" . $reg_empresas["IR"] ."\n";
	}
	
	$filename = "lista" . date("dMY");
	
	$cabecalho = "NF;Empresa Func.;Valor NF;Valor IR\n";
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$cabecalho\n$data";
	exit();
	
?>