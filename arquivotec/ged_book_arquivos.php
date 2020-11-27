<?php
/**
 *
 *		Formulário de Book Arquivos	
 *		
 *		Criado por Carlos Abreu  
 *		
 *		local/Nome do arquivo:
 *		../arquivotec/ged_book_arquivos.php
 *		
 *		Versão 0 --> VERSÃO INICIAL - 27/04/2016 - Carlos Abreu
 *		Versão 4 --> atualizção layout - Carlos Abreu - 23/03/2017
 *		Versão 5 --> unificação tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
 *		Versão 6 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
 */	


ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '990M');
ini_set('upload_max_filesize', '990M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(283))
{
	nao_permitido();
}

$conf = new configs();

//UsuÁrios de sistemas podem gerar books com tamanhos gigantes
/*
if (in_array($_SESSION['id_funcionario'], array(6,978)))
{
	ini_set('memory_limit', '512M');
}
*/

$xajax->registerFunction("gerabook");
$xajax->registerFunction("disciplinas");
$xajax->registerFunction("buscaAtividades");

$xajax->processRequests();

function gerabook($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	if(!isset($_SESSION["id_funcionario"]))
	{
		$resposta->addAlert("A sessÃo expirou. É necessário efetuar o login novamente.");
	}
	else
	{
	
		$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
		$sql .= "WHERE ordem_servico.id_os = '".$dados_form["id_os"]."' ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			exit("Erro ao selecionar os dados das disciplinas: ".$db->erro);
		}

		$reg0 = $db->array_select[0];	
		
		//Seleciona os dados do arquivo
		$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
		$sql .= "WHERE numeros_interno.reg_del = 0 ";
		$sql .= "AND ged_arquivos.reg_del = 0 ";
		$sql .= "AND ged_versoes.reg_del = 0 ";
		$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
		$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
		$sql .= "AND numeros_interno.id_os = '".$dados_form["id_os"]."' ";
		
		if($dados_form["disciplina"]!=0)
		{
			$sql .= "AND numeros_interno.id_disciplina = '".$dados_form["disciplina"]."' ";
		}
		
		if($dados_form["atividade"]!=0)
		{
			$sql .= "AND numeros_interno.id_atividade = '".$dados_form["atividade"]."' ";
		}
		
		$db->select($sql,'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Erro ao selecionar os dados do arquivo: ".$db->erro);
		}

		//Forma o array com os arquivos a serem incluídos no arquivo ZIP
		foreach($db->array_select as $reg2)
		{
			if(is_file(DOCUMENTOS_GED . $reg2["base"] . "/" . $reg2["os"] . "/" . substr($reg2["os"],0,4) . "-DISCIPLINAS/" . $reg2["disciplina"] . "/" . $reg2["atividade"] . "/" . $reg2["strarquivo"]))
			{
				$array_arquivos[$reg2["disciplina"] . "/" . $reg2["atividade"]."/".$reg2["strarquivo"]] = DOCUMENTOS_GED . $reg2["base"] . "/" . $reg2["os"] . "/" . substr($reg2["os"],0,4) . "-DISCIPLINAS/" . $reg2["disciplina"] . "/" . $reg2["atividade"] . "/" . $reg2["strarquivo"];
				
				$array_nome_arquivo[DOCUMENTOS_GED . $reg2["base"] . "/" . $reg2["os"] . "/" . substr($reg2["os"],0,4) . "-DISCIPLINAS/" . $reg2["disciplina"] . "/" . $reg2["atividade"] . "/" . $reg2["strarquivo"]] = $reg2["nome_arquivo"];
			}
		}
		
		if(count($array_arquivos)>0)
		{
			if(!mkdir("documentos_book") && !is_dir("documentos_book"))
			{
				$resposta->addAlert("Erro ao tentar criar a pasta temporária no servidor.");
			}
			else
			{
				if(is_file("documentos_book/".$reg0["os"].".zip"))
				{
					unlink("documentos_book/".$reg0["os"].".zip");	
				}
				
				ob_start();
				
				$zip = new ZipArchive();
				
				$filename = "documentos_book/".$reg0["os"].".zip";
				
				if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) 
				{
					$resposta->addAlert("Erro ao criar o arquivo.");
				}
				else
				{
					$time_start = microtime(true);
					
					foreach($array_arquivos as $pastas=>$arquivos)
					{	
					    if ($dados_form['rdoImpressao'] == 1)
					    {
    					    $arrPastas = explode('/', $pastas);
    					    //Retirando um indice do caminho da pasta para facilitar a impressão não tendo que abrir todas as pastas
    					    array_pop($arrPastas);
    					    array_pop($arrPastas);
    					    $pastas = implode('/', $arrPastas);
    					    //printLog($pastas, true);
					    }
					    
						$zip->addFile($arquivos,$pastas."/".$array_nome_arquivo[$arquivos]);
						
						$time_end = microtime(true);
						
						$timelast = $time_end - $time_start;
						
						//se demorar + de n segundos, sai da rotina
						if($timelast>=300)
						{
							$resposta->addAlert('Não foi possivel gerar o arquivo com todos os documentos devido a quantidade de arquivos. Tente fazer um filtro por disciplina/atividade');
							
							break;						
						}						
					}						
				}
				
				$zip->close();
				
				$resposta->addScript('open_doc('.ROOT_DIR.'/arquivotec/documentos_book/'.$reg0["os"].".zip".'")');
			}			
		}
		else
		{
			$resposta->addAlert("Não há documentos disponíveis para a operação de download.");
		}				
	}	

	return $resposta;
}

function disciplinas($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
		
	//Seleciona os dados do arquivo
	$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".setores ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
	$sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
	$sql .= "AND numeros_interno.id_os = '".$dados_form["id_os"]."' ";
	$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "GROUP BY setores.id_setor ";
	$sql .= "ORDER BY setor ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível realizar a seleção: ".$db->erro);
	}
	
	$resposta->addScript("combo_destino = document.getElementById('disciplina');");
	
	$resposta->addScriptCall("limpa_combo('disciplina')");
	
	$resposta->addScript("combo_destino = document.getElementById('atividade');");
	
	$resposta->addScriptCall("limpa_combo('atividade')");
	
	if($db->numero_registros>0)
	{
		$resposta->addScript('addOption("disciplina", "TODAS", "0")');
		
		$resposta->addScript('addOption("atividade", "TODAS", "0")');
	}
	
	foreach($db->array_select as $regs)
	{
		$resposta->addScript('addOption("disciplina", "'.$regs['setor'].'", "'.$regs['id_setor'].'")');
	}
	
	return $resposta;
}

function buscaAtividades($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT atividades.descricao, atividades.id_atividade FROM ".DATABASE.".atividades, ".DATABASE.".numeros_interno ";
	$sql .= "WHERE numeros_interno.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
	$sql .= "AND numeros_interno.id_disciplina = '".$dados_form["disciplina"]."' ";
	$sql .= "AND numeros_interno.id_os = '".$dados_form["id_os"]."' ";
	$sql .= "AND atividades.solicitacao = '1' ";
	$sql .= "GROUP BY atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$resposta->addScript("combo_destino = document.getElementById('atividade');");
	
	$resposta->addScriptCall("limpa_combo('atividade')");
	
	if($db->numero_registros>0)
	{
		$resposta->addScript('addOption("atividade", "TODAS", "0")');
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addScript('addOption("atividade", "'.$reg['descricao'].'", "'.$reg['id_atividade'].'")');
	}
	
	return $resposta;
}

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>datetimepicker/datetimepicker_css.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

</script>

<?php
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
$sql .= "WHERE ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico_status.id_os_status IN (1,2,3,5,13,14,16,17,18,19) ";
$sql .= "AND ordem_servico.os < 60000 ";
$sql .= "GROUP BY ordem_servico.os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Erro ao tentar selecionar os dados: ".$db->erro);
}

foreach($db->array_select as $reg_os)
{
	$array_os_values[] = $reg_os["id_os"];
	$array_os_output[] = sprintf("%05d",$reg_os["os"]) . " - " . substr($reg_os["descricao"],0,80) . " - " . $reg_os["empresa"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("revisao_documento","V6");

$smarty->assign("campo",$conf->campos('book_documentos_projeto'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged_book_arquivos.tpl');
?>