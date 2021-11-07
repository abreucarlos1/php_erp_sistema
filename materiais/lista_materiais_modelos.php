<?php
/*
	Lista de materiais - modelos (Listas personalizadas)
	Criado por Carlos
	Versão 0 --> VERSÃO INICIAL - 20/05/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

if (isset($_GET['salvar']) && !empty($_GET['salvar']))
{
	$descLista 	= str_replace(' ', '_', maiusculas(tiraacentos(AntiInjection::clean($_POST['desc_lista']))));
	$idLista = 0;
		
	if (empty($descLista))
	{
		$smarty->assign("mensagem_erro",'Por favor, digite um nome para o modelo');
	}
	
	if (empty($descLista))
	{
		$smarty->assign("mensagem_erro",'Por favor, digite um nome para o modelo');
	}
	
	//Upload do arquivo de modelo da lista de materiais
	if (!empty($_FILES['arquivoModelo']['name']))
	{
		$extensoes = array('xlsx');
		
		$tam  = $_FILES['arquivoModelo']['size'] / 1024 / 1024;
		$erro = $_FILES['arquivoModelo']['error'];
		$tmp_name = $_FILES['arquivoModelo']['tmp_name'];
		
		$name = explode('.', $_FILES['arquivoModelo']['name']);
		
		$nome_final = $descLista.'.'.$name[1];
		
		$retorno = array(true);
		
		if (!$erro)
		{
			if (in_array($name[1], $extensoes))
			{
				try{
					$uploaded = move_uploaded_file($tmp_name, DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$nome_final);
				}
				catch (Exception $e)
				{
					$erroUpload = $e->getMessage();
				}
				
				if (!$uploaded)
				{
					$retorno = array(false, 'Houve uma falha ao tentar subir o arquivo '.($_FILES['arquivoModelo']['name']));
				}
			}
			else
			{
				$retorno = array(false, 'O tipo do arquivo '.$_FILES['arquivoModelo']['name'].' não é xlsx');
			}
		}
		else
		{
			$retorno = array(false, $erros[$erro]);
		}
	}
	
	if (isset($_POST['id_lista']) && !empty($_POST['id_lista']))
	{
		$idLista = $_POST['id_lista'];
		
		$updateArquivo = '';
		if ($retorno[0])
			$updateArquivo = ",mlc_arquivo = '{$nome_final}'";
		
		$usql = "UPDATE ".DATABASE.".modelo_lista_cabecalho SET
					mlc_descricao = '{$descLista}'
					$updateArquivo
				 WHERE reg_del = 0 AND mlc_id = {$idLista}";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$smarty->assign('mensagem_erro', 'Houve uma falha ao tentar inserir o modelo!');
			unlink(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$nome_final);
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".modelo_lista_aplicados SET
						reg_del = 1, 
						reg_who = {$_SESSION['id_funcionario']},
						data_del = '".date('Y-m-d')."'
					WHERE mla_mlc_id = {$idLista}";
		
			$db->update($usql, 'MYSQL');
			
			$erro = false;
			
			if (count($_POST['id_cliente']) > 0)
			{
				$isql = "INSERT INTO
							".DATABASE.".modelo_lista_aplicados (mla_mlc_id, mla_cliente, mla_loja)
						 VALUES ";
				
				//INSERÇÃO DOS CLIENTES VINCULADOS A ESTA LISTA
				foreach($_POST['id_cliente'] as $k => $idCliente)
				{
					$cliente = explode('/', $idCliente);
					$isql .= $virg." ({$idLista}, '{$cliente[0]}', '{$cliente[1]}')";
					$virg = ',';
				}
				
				$db->insert($isql, 'MYSQL');
				
				if ($db->erro != '')
				{
					$smarty->assign('mensagem_erro', 'Houve uma falha ao tentar inserir o modelo!');
					$smarty->assign('post', $_POST);
					unlink(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$nome_final);
					$erro = true;
				}
			}
			
			if (!$erro)
			{
				echo '<script>alert("Modelo inserido corretamente!");</script>';
				header('Location: ./lista_materiais_modelos.php');
			}
		}
	}
	else
	{
		if (!isset($retorno[0]))
		{
			//INSERÇÃO DO CABECALHO
			$isql = "INSERT INTO
						".DATABASE.".modelo_lista_cabecalho (mlc_descricao, mlc_arquivo)
					 VALUE ('{$descLista}','{$nome_final}')";
			
			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$smarty->assign('mensagem_erro', 'Houve uma falha ao tentar inserir o modelo!');
				unlink(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$nome_final);
			}
			else
			{
				$idLista = $db->insert_id;
				
				$isql = "INSERT INTO
							".DATABASE.".modelo_lista_aplicados (mla_mlc_id, mla_cliente, mla_loja)
						 VALUES ";
				
				if (!is_array($_POST['id_cliente']))
				{
					//INSERÇÃO DOS CLIENTES VINCULADOS A ESTA LISTA
					foreach($_POST['id_cliente'] as $k => $idCliente)
					{
						$cliente = explode('/', $idCliente);
						$isql .= $virg." ({$idLista}, '{$cliente[0]}', '{$cliente[1]}')";
						$virg = ',';
					}
					
					$db->insert($isql, 'MYSQL');
				}
				
				if ($db->erro != '')
				{
					$smarty->assign('mensagem_erro', 'Houve uma falha ao tentar inserir o modelo!');
					$smarty->assign('post', $_POST);
					unlink(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$nome_final);
				}
				else
				{
					echo '<script>alert("Modelo inserido corretamente!");</script>';
					header('Location: ./lista_materiais_modelos.php');
				}
			}
		}
	}
}

function voltar()
{
	$resposta = new xajaxResponse();
	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addAssign("btninserir", "value", "Inserir");

	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupo'));");
	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");
	return $resposta;
}

function atualizatabela()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".modelo_lista_cabecalho WHERE modelo_lista_cabecalho.reg_del = 0 ORDER BY mlc_id DESC";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$xml){
		$xml->startElement('row');
			$xml->writeAttribute('id', trim($reg["mlc_id"]));
			$xml->writeElement('cell', trim($reg["mlc_descricao"]));
			$xml->writeElement('cell', trim($reg["mlc_arquivo"]));
			
			$html = "<img onclick=\'xajax_excluir(".$reg['mlc_id'].");\' style=\'cursor:pointer;\' src=\'../imagens/apagar.png\' />";
			
			$xml->writeElement('cell', $html);
		$xml->endElement();
	});
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('lista_modelos',true,'250','".$conteudo."');");
	
	return $resposta;
}

function excluir($idLista)
{
	$resposta 	= new xajaxResponse();
	$db 		= new banco_dados();
	
	$data		= date('Y-m-d');
	$idUsuario	= $_SESSION['id_funcionario'];
	
	$sql = "SELECT mlc_arquivo FROM ".DATABASE.".modelo_lista_cabecalho WHERE modelo_lista_cabecalho.mlc_id = {$idLista} AND modelo_lista_cabecalho.reg_del = 0";
	$lista = $db->select($sql, 'MYSQL', function($reg, $i){
		return $reg;
	});

	if ($db->numero_registros > 0 && file_exists(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$lista[0]['mlc_arquivo']))
	{
		$uSql = "UPDATE
					".DATABASE.".modelo_lista_cabecalho
				SET
					reg_del = 1, 
					reg_who = '{$idUsuario}',
					data_del = '".$data."'
				 WHERE
				 	mlc_id = {$idLista}";
		
		$db->update($uSql, 'MYSQL');
		
		if (empty($db->erro))
		{
			$uSql = "UPDATE
						".DATABASE.".modelo_lista_aplicados
					SET
						reg_del = 1, 
						reg_who = '{$idUsuario}',
						data_del = '".$data."'
					WHERE
				 		mla_mlc_id = {$idLista}";
		
			$db->update($uSql, 'MYSQL');
			
			if (empty($db->erro))
			{
				$uSql = "UPDATE
							".DATABASE.".modelo_lista_excel
						SET
							reg_del = 1, 
							reg_who = '{$idUsuario}',
							data_del = '".$data."'
						WHERE
				 			mle_mlc_id = {$idLista}";
			
				$db->update($uSql, 'MYSQL');
				
				if (!empty($db->erro))
				{
					$mensagem = 'Falha ao tentar excluir o modelo da lista!';
				}
				else
				{
					unlink(DIRETORIO_PROJETO.'/materiais/modelos_excel/'.$lista[0]['mlc_arquivo']);
					$mensagem = 'Modelo excluído corretamente!';;
				}
			}
			else
			{
				$mensagem = 'Falha ao tentar excluir as aplicações da lista!';
			}
		}
		else
		{
			$mensagem = 'Falha ao tentar excluir o cabeçalho da lista!';
		}
	}
	else
	{
		$mensagem = 'Modelo do excel não encontrado!';
	}
		
	$resposta->addAlert($mensagem);
	$resposta->addRedirect('./lista_materiais_modelos.php');
	
	return $resposta;
}

function parametrosExcel($idLista)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$resposta->addScript("desseleciona_combo('id_cliente');");
	$resposta->addAssign('btninserir', 'value', 'Alterar');
	
	$options = '';
	
	$sqlParametros = "SELECT * FROM ".DATABASE.".modelo_lista_parametros WHERE modelo_lista_parametros.reg_del = 0";
	
	$html = "<div style=\'height:250px;overflow:auto;\'>".
				"<form id=\'frmParametros\' name=\'frmParametros\'>".
					"<input type=\'hidden\' name=\'id_lista\' id=\'id_lista\' value=\'{$idLista}\' />";

	//Verificando se a lista já tem um modelo
	$sql = "SELECT
				*
			FROM
				".DATABASE.".modelo_lista_cabecalho
				JOIN(
					SELECT
						*
					FROM
						".DATABASE.".modelo_lista_excel
					WHERE
						modelo_lista_excel.reg_del = 0 
						AND modelo_lista_excel.mle_mlc_id = {$idLista}
					) parametros
					ON mle_mlc_id = mlc_id
			WHERE
				modelo_lista_cabecalho.reg_del = 0
			AND mlc_id = {$idLista}";
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros > 0)
	{
		$parametrosCadastrados = $db->array_select;
		
		$resposta->addAssign('desc_lista', 'value', $parametrosCadastrados[0]['mlc_descricao']);
		$resposta->addAssign('id_lista', 'value', $parametrosCadastrados[0]['mlc_id']);
		
		$sqlClientes = "SELECT mla_mlc_id, mla_cliente, mla_loja FROM ".DATABASE.".modelo_lista_aplicados WHERE modelo_lista_aplicados.mla_mlc_id = {$idLista} AND modelo_lista_aplicados.reg_del = 0";		
		$db->select($sqlClientes, 'MYSQL', function($reg, $i) use(&$resposta){
			$resposta->addScript("seleciona_combo('{$reg['mla_cliente']}/{$reg['mla_loja']}', 'id_cliente');");
		});
		
		for ($j=0; $j<count($parametrosCadastrados); $j++)
		{
			$html .=		"<div class=\'linhaParametro\'>".
								"<label class=\'labels\'>Campo*</label> ".
								"<select class=\'caixa input\' id=\'selCampo\' name=\'selCampo[]\' onchange=\'onFocusOut(this);\'>".
									"<option value=\'\'>SELECIONE</option>";
	
			//Buscamos os campos existentes no banco
			$selected = '';
			$db->select($sqlParametros, 'MYSQL', function($reg, $i) use(&$parametrosCadastrados, &$selected, &$html, &$j){
				$selected = $parametrosCadastrados[$j]['mle_campo'] == $reg['mlp_nome_campo'] || ($parametrosCadastrados[$j]['mle_formula'] == 1 && $reg['mlp_nome_campo'] == 'formula') ? "selected=\'selected\'" : '';
				$html .= "<option value=\'{$reg['mlp_nome_campo']}\' {$selected}>{$reg['mlp_parametro']}</option>";
			});
	
			$displayFormula = $parametrosCadastrados[$j]['mle_formula'] == 1 ? '' : 'display:none;';
			$formula 		= $parametrosCadastrados[$j]['mle_formula'] == 1 ? $parametrosCadastrados[$j]['mle_campo'] : '';
			$displayImg 	= !empty($parametrosCadastrados[$j]['mle_id']) ? '' : 'display:none;';
			$displayImgExcl	= $j > 0 ? '' : 'display:none;';
			
			$html .=			"</select> ".
								"<label class=\'labels\'>Célula*</label>".
								"<input type=\'text\' onKeyUp=\'permiteNovoParametro(this);\' value=\'{$parametrosCadastrados[$j]['mle_celula']}\' class=\'input caixa\' size=\'5\' name=\'celula[]\' id=\'celula[]\' maxlength=\'3\' /> ".
								"<label style=\'{$displayFormula}\' class=\'labels formula\'>Fórmula*</label>".
								"<input style=\'{$displayFormula}\' value=\'{$formula}\' type=\'text\' class=\'input formula caixa\' size=\'12\' name=\'formula[]\' id=\'formula[]\' /> ".
								"<img style=\'cursor:pointer;$displayImg\' title=\'Duplicar esta linha\' src=\'../imagens/replicar.png\' class=\'adicionar\' onclick=\'duplicarLinha(this);\' /> ".
								"<img style=\'cursor:pointer;$displayImgExcl\' title=\'Excluir esta linha\' src=\'../imagens/apagar.png\' class=\'excluir\' onclick=\'excluirLinha(this);\' />".
							"</div>";
		}
	}
	else
	{
		$html .=		"<div class=\'linhaParametro\'>".
								"<label class=\'labels\'>Campo*</label> ".
								"<select class=\'caixa input\' id=\'selCampo\' name=\'selCampo[]\' onchange=\'onFocusOut(this);\'>".
									"<option value=\'\'>SELECIONE</option>";
	
		//Buscamos os campos existentes no banco
		$selected = '';
		$db->select($sqlParametros, 'MYSQL', function($reg, $i) use(&$parametrosCadastrados, &$selected, &$html, &$j){
			$html .= "<option value=\'{$reg['mlp_nome_campo']}\'>{$reg['mlp_parametro']}</option>";
		});
		
		$displayFormula = 'display:none;';
		$formula 		= '';
		$displayImg 	= 'display:none;';
		$displayImgExcl	= 'display:none;';
		
		$html .=			"</select> ".
								"<label class=\'labels\'>Célula*</label>".
								"<input type=\'text\' onKeyUp=\'permiteNovoParametro(this);\' value=\'{$parametrosCadastrados[$j]['mle_celula']}\' class=\'input caixa\' size=\'5\' name=\'celula[]\' id=\'celula[]\' maxlength=\'3\' /> ".
								"<label style=\'{$displayFormula}\' class=\'labels formula\'>Fórmula*</label>".
								"<input style=\'{$displayFormula}\' value=\'{$formula}\' type=\'text\' class=\'input formula caixa\' size=\'12\' name=\'formula[]\' id=\'formula[]\' /> ".
								"<img style=\'cursor:pointer;$displayImg\' title=\'Duplicar esta linha\' src=\'../images/buttons_action/replicar.gif\' class=\'adicionar\' onclick=\'duplicarLinha(this);\' /> ".
								"<img style=\'cursor:pointer;$displayImgExcl\' title=\'Excluir esta linha\' src=\'../images/buttons_action/apagar.png\' class=\'excluir\' onclick=\'excluirLinha(this);\' />".
							"</div>";
	}

	$html .=		"<input type=\'button\' onclick=xajax_salvarParametros(xajax.getFormValues(\'frmParametros\')); name=\'btnSalvarParametros\' id=\'btnSalvarParametros\' value=\'Salvar\' class=\'class_botao botaoSalvarParametros\' />".
				"</form>".
			"</div>";
	
	$resposta->addScript("modal('{$html}', 'p', 'Configurações do Modelo');");
	
	return $resposta;
}

//Parametros da planilha do excel
//Salva qual célula receberá qual valor
function salvarParametros($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".modelo_lista_excel SET reg_del = 1, reg_who = '{$_SESSION['id_funcionario']}', data_del = '".date('Y-m-d')."' WHERE mle_mlc_id = {$dados_form['id_lista']}";
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar atualizar o modelo da lista!');
		return $resposta;
	}
	
	$isql = "INSERT INTO ".DATABASE.".modelo_lista_excel (mle_mlc_id, mle_campo, mle_celula, mle_formula) VALUES ";
	$virg = '';
	$erro = array();
	foreach($dados_form['selCampo'] as $k => $campo)
	{
		$celula  = $dados_form['celula'][$k];
		$formula = !empty($dados_form['formula'][$k]) ? 1 : 0;
		
		if ($formula == 1)
		{
			$campo = $dados_form['formula'][$k];
		}
		
		if (empty($celula))
			$erro[] = 'Todas as células devem ser preenchidas!';
			
		if (empty($campo))
			$erro[] = 'Todos os campos devem ser preenchidos!';

		if (empty($erro))
		{
			$isql .= $virg."({$dados_form['id_lista']}, '{$campo}', '{$celula}', '{$formula}')";
			$virg = ', ';
		}
	}
	
	if (!empty($erro))
	{
		foreach($erro as $sMsg)
			$resposta->addAlert($sMsg);
	}
	else
	{
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('ATENÇÃO: Houve uma falha ao tentar salvar as configurações do modelo!');
		}
		else
		{
			$resposta->addAlert('Configurações salvas corretamente!');
			$resposta->addScript("divPopupInst.destroi();");
		}
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvarModelo");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");
$xajax->registerFunction("parametrosExcel");
$xajax->registerFunction("salvarParametros");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function excluirLinha(el)
{
	var pai = $(el).parent();
	$(pai).remove();
}

function duplicarLinha(el)
{
	var pai = $(el).parent();
	var copia = $(pai).clone();
	$(copia).find('.input').val('');
	$(copia).find('img').hide();
	$(copia).find('.formula').hide();
	$(copia).appendTo($(pai).parent());
}

function permiteNovoParametro(el)
{
	if ($(el).val() != '')
	{
		$(el).parent().find('img').show();
		return true;
	}

	$(el).parent().find('img').hide();
	return false;
}

function onFocusOut(el)
{
	if ($(el).val() == 'formula')
		$(el).parent().find('.formula').show();
	else
		$(el).parent().find('.formula').hide();
	$(el).next().next().focus();
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("descricao lista,Arquivo,D");
	mygrid.setInitWidths("*,*,40");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");

	function doOnRowSelected(row,col)
	{
		if(col<2)
		{						
			xajax_parametrosExcel(row);

			return true;
		}
	}

	mygrid.attachEvent("onRowSelect",doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

var iniciaBusca2 =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		xajax_atualizatabela(valor, null, document.getElementById('cod_fornecedor').value);
	}
}
</script>
<?php

//SOMENTE APARECERÃO CLIENTES QUE TENHO ALGUM PROJETO
$sql =
"SELECT 
	DISTINCT A1_NREDUZ, A1_NOME, A1_COD, A1_LOJA
FROM 
	AF8010
	JOIN(
		SELECT AF2_ORCAME, AF2_DESCRI, AF2_GRPCOM FROM AF2010 WHERE AF2_DESCRI LIKE '%LISTA DE MATERIAIS%' AND D_E_L_E_T_ = ''
	) TAREFAS
	ON TAREFAS.AF2_ORCAME = AF8_PROJET
	JOIN(
		SELECT * FROM SA1010 WHERE D_E_L_E_T_ = ''
	) SA1010
	ON A1_COD = AF8_CLIENT
	AND A1_LOJA = AF8_LOJA
WHERE
	AF8010.AF8_FASE IN ('03','09','07')
	AND AF8010.D_E_L_E_T_ = ''
ORDER BY
	A1_NOME";

$db->select($sql, 'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_clientes_values = array();
$array_clientes_output = array();
foreach($db->array_select as $k => $regs)
{
	//Não vou colocar a loja porque uma lista servirá para todas as lojas da empresa
	$array_clientes_values[] = trim($regs["A1_COD"])."/".trim($regs["A1_LOJA"]);
	$array_clientes_output[] = trim($regs["A1_NOME"]);
}

$smarty->assign("option_output",$array_clientes_output);
$smarty->assign("option_values",$array_clientes_values);

$smarty->assign("larguraTotal",1);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('lista_materiais'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);
$smarty->display('lista_materiais_modelos.tpl');