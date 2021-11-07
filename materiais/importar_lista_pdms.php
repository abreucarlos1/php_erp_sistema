<?php
/*
	Exportação e importação de dados
	Criado por Carlos 
	
	Versão 0 --> VERSÃO INICIAL - 13/05/2016	
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '20M');

header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

$tmpName 	= $_FILES['arquivoImportacao']['tmp_name'];
$name		= $_FILES['arquivoImportacao']['name'];

$fp	= fopen($tmpName, 'r');

$primeiraLinha = true;
while(($linha = fgets($fp)) !== false)
{
	$reg = explode(';', $linha);
	
	//PULA A PRIMEIRA LINHA E OS ITENS SEM CODIGO DE BARRAS
	if ($primeiraLinha == true || empty($reg[1]) || empty($reg[0]))
	{
		$primeiraLinha = false;
		continue;
	}
		
	$descArquivos[] = $reg[0];
	$codBarras[] 	= $reg[1];

	//Verificando se o codigo e barras é válido, senão ignora o item
	$codBarrasTemp = intval(AntiInjection::formatarGenerico($reg[1], '#############'));
	if ($codBarrasTemp == 0)
		continue;	
	
	//Quando for tubo, dividir por 1000, visto que o PDMS e o Plant3D os exibem em milimetros e o sistema está em metros
	$testeTubo = explode('.', $reg[1]);
	$dividir = $testeTubo[0].$testeTubo[1] == '01001' ? 1000 : 1;
	
	$arquivos[$reg[0]][trim($reg[1])] = array(
		'qtd' => $reg[2] / $dividir
	);
}

$arquivosCompl = array();

//BUSCA O DOCUMENTO PASSADO PELO PDMS
$sql = 
"SELECT
	*
FROM 
	".DATABASE.".ged_arquivos
	JOIN(
		SELECT id_os, id_numero_interno idNumdvm, id_disciplina FROM ".DATABASE.".numeros_interno WHERE numeros_interno.reg_del = 0
    ) numeros_interno
	ON idNumdvm = id_numero_interno
	JOIN(
		SELECT OS, id_os idOs FROM ".DATABASE.".OS WHERE OS.reg_del = 0
	)OS
    ON idOs = id_os
WHERE
	ged_arquivos.reg_del = 0 
	AND descricao IN('".implode("','", $descArquivos)."')";

$db->select($sql, 'MYSQL', function($reg, $i) use(&$arquivosCompl){
	$arquivosCompl[$reg['descricao']]['id_ged_arquivo'] = $reg['id_ged_arquivo'];
	$arquivosCompl[$reg['descricao']]['id_os'] = $reg['id_os'];
	$arquivosCompl[$reg['descricao']]['id_disciplina'] = $reg['id_disciplina'];
});

/*BUSCA OS CODIGOS E UNIDADE DO PRODUTO*/
$sql = "SELECT * FROM ".DATABASE.".produto WHERE produto.cod_barras IN ('".implode("','", $codBarras)."') AND produto.reg_del = 0 AND produto.atual = 1;";

$idGedArquivos = array();
$listaCodBarras = array();
$db->select($sql, 'MYSQL', function($reg, $i) use(&$idGedArquivos, &$listaCodBarras){
	$idGedArquivos[$reg['cod_barras']]['id_produto'] = $reg['id_produto'];
	$idGedArquivos[$reg['cod_barras']]['unidade'] 	 = $reg['unidade1'];
	
	$listaCodBarras[$reg['cod_barras']] = $reg['cod_barras'];
});

fclose($fr);

$idFunc = $_SESSION['id_funcionario'];

$log = '<h2>LOGS DA OPERAÇÃO DE IMPORTAÇÃO DA(s) LISTA(s)</h2>';

//Começando a importação
foreach($arquivos as $descArq => $produto)
{
	$produtosLista = array_keys($produto);

	if (!isset($arquivosCompl[$descArq]['id_os']))
	{
		$log .= '<h3><font color="red">O documento '.$descArq.' NÃO ESTA NO GED</font></h3>';
	}
	else
	{
		//Busca as especs da lista a ser criada
		$sql = 
	"SELECT
		DISTINCT cod_barras, id_produto, id_familia
	FROM
		".DATABASE.".espec_lista
	    JOIN(
			SELECT p.cod_barras, p.id_produto, c.id_familia FROM ".DATABASE.".produto p
			LEFT JOIN ".DATABASE.".componentes c ON c.cod_barras = p.cod_barras
			WHERE p.reg_del = 0 AND c.reg_del = 0 AND p.cod_barras IN('".implode("','", $produtosLista)."') AND atual = 1
	    ) produto
	    ON cod_barras = el_cod_barras
	    JOIN(
			SELECT ec_os, ec_id FROM ".DATABASE.".espec_cabecalho WHERE espec_cabecalho.reg_del = 0 AND espec_cabecalho.ec_os = ".$arquivosCompl[$descArq]['id_os']."
	    ) ec
	    ON el_ec_id = ec_id
		WHERE espec_lista.reg_del = 0 
	ORDER BY
		id_produto";
		
		$resEspec = $db->select($sql, 'MYSQL');
		
		$listaSemFamilia = array();
		
		//A consulta acima neste momento serve apenas para verificar se a OS desejada tem especs, permitindo assim a inserção de uma lista
		if ($db->numero_registros > 0)
		{
			//Montando a lista de produtos da espec 
			while($regsEspec = mysqli_fetch_assoc($resEspec))
			{
				$produtosListaEspec[$regsEspec['cod_barras']] = $regsEspec['id_produto'];
				
				if (empty($regsEspec['id_familia']))
					$listaSemFamilia[$regsEspec['cod_barras']] = $regsEspec['id_produto'];
			}
			
			//Verificando se o arquivo já possui cabecalho criado, para não criar lixo no banco de dados
			$sql = "SELECT * FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_ged_arquivo = '".$arquivosCompl[$descArq]['id_ged_arquivo']."'";
			$db->select($sql, 'MYSQL', true);
			
			if ($db->numero_registros == 0)
			{
				//Inserindo o cabecalho e pegando o código gerado
				$isql = "INSERT INTO ".DATABASE.".lista_materiais_cabecalho (data_cadastro, data_revisao) VALUES ('".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."');";
				$db->insert($isql, 'MYSQL');
				$idCabecalho = $db->insert_id;
				$complCabecalho = 'criado';
			}
			else
			{
				$idCabecalho = $db->array_select[0]['id_lista_materiais_cabecalho'];
				$complCabecalho = 'alterado';
			}
			
			if ($db->erro == '')
			{
				$log .= '<h3>Cabeçalho Nº '.$idCabecalho.' '.$complCabecalho.'<h3>';
				foreach($produto as $codBar => $qtd)
				{
					//Caso o item a ser inserido não esteja na lista da espec criada 
					if (!array_key_exists($codBar, $produtosListaEspec))
					{
						$log .= '<h4><font color="red">Item '.$codBar.' não está na espec desta os</font></h4>';
						continue;
					}
					
					$idArquivo		= $arquivosCompl[$descArq]['id_ged_arquivo'];
					$idOs	 		= $arquivosCompl[$descArq]['id_os'];
					$idDisciplina 	= $arquivosCompl[$descArq]['id_disciplina'];
					
					$unidade 		= $idGedArquivos[$codBar]['unidade'];
					//$idProduto 		= $idGedArquivos[$codBar]['id_produto'];
					$idProduto		= $codBar;
					$qtd 			= $qtd['qtd'];
					$per 			= 0;
					$data			= date('Y-m-d H:i:s');
					
					//Verificando se o item selecionado já está na lista do arquivo
					$sql = "SELECT m.id_lista_materiais, m.id_lista_materiais_versoes, MAX(revisao_documento) revisao_documento
							FROM ".DATABASE.".lista_materiais m
							JOIN ".DATABASE.".lista_materiais_versoes v ON v.id_lista_materiais = m.id_lista_materiais AND v.reg_del = 0
							WHERE m.reg_del = 0 AND m.cod_barras = '".$idProduto."' AND m.id_lista_materiais_cabecalho = ".$idCabecalho."
							GROUP BY m.id_lista_materiais, m.id_lista_materiais_versoes";
					
					$db->select($sql, 'MYSQL', true);
										
					if ($db->numero_registros == 0)
					{
						//Inserindo a lista de materiais gerada
						$isql = "INSERT INTO ".DATABASE.".lista_materiais 
								(id_ged_arquivo, cod_barras, id_os, id_funcionario, data_inclusao, id_lista_materiais_cabecalho, id_disciplina) VALUES ";
											
						$isql 			.= "(".$idArquivo.", '".$idProduto."', ".$idOs.", ".$idFunc.", '".$data."', ".$idCabecalho.", ".$idDisciplina.")";
						$db->insert($isql, 'MYSQL');
						$idLm = $db->insert_id;
						$complLm = 'criada';
						$revisao_documento = 0;
						
						if (isset($listaSemFamilia))
						{
							$complLm .= '. (<font color="green">Porém, o produto '.$idProduto.' NÃO POSSUI FAMILIA CADASTRADA';
						}
					}
					else
					{
						$complLm = 'alterada';
						$idLm = $db->array_select[0]['id_lista_materiais'];
						$idLv = $db->array_select[0]['id_lista_materiais_versoes'];
						$revisao_documento = $db->array_select[0]['revisao_documento']+1;
						
						//Excluindo a versão já existente mantendo o histórico
						//O restante do fluxo continua normalmente com a inserção da nova versão e a Atualização da lista de materiais atualmente selecionada
						$usql = "UPDATE ".DATABASE.".lista_materiais_versoes SET reg_del = 0, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE id_lista_materiais_versoes = ".$idLv;
						$db->update($usql, 'MYSQL');
					}
					
					if ($db->erro == '')
					{
						$log .= '<h4>Lista Nº '.$idLm.' '.$complLm.'<h4>';
						
						//INSERINDO A VERSÃO DO ITEM DA LISTA
						$isql = "INSERT INTO
									".DATABASE.".lista_materiais_versoes
									(id_lista_materiais, id_funcionario, data_versao, revisao_documento, unidade, qtd, margem, id_lista_materiais_cabecalho, cod_barras)
								VALUES
									(".$idLm.", ".$idFunc.", '".$data."', ".$revisao_documento.", '".$unidade."', ".$qtd.", 0, ".$idCabecalho.", '".$idProduto."')";
						
						$db->insert($isql, 'MYSQL');
						$idLv = $db->insert_id;
						
						if ($db->erro == '')
						{
							$log .= '<h5>Versão '.$revisao_documento.' da Lista Nº '.$idLv.' criada<h5>';
								
							$usql = "UPDATE ".DATABASE.".lista_materiais SET id_lista_materiais_versoes = ".$idLv." WHERE id_lista_materiais = ".$idLm." AND reg_del = 0 ";
							$db->update($usql, 'MYSQL');
						}
						else
						{
							$log .= '<h5><font color="red">ERRO LV: ('.$isql.')</font></h5>';
						}
					}
					else
					{
						$log .= '<h4><font color="red">ERRO LM: ('.$isql.')</font></h4>';
					}
				}
			}
		}
		else
		{
			$log .= '<h3><font color="red">NÃO FORAM ENCONTRADOS OS PRODUTOS OU SPECS PARA ESTA OS '.$db->erro.'</font></h3>';
		}
	}
}
$log .= '<h2>FIM DA OPERAÇÃO DE IMPORTAÇÃO DA(s) LISTA(s)</h2>';

exit($log);