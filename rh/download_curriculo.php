<?php
/*
	Formulário de Visualização de Curriculo
	Criado por Carlos Eduardo

	local/Nome do arquivo:
	../rh/download_curriculo.php
	
	data de criação
	Versão 0 --> VERSÃO INICIAL : 13/12/2014
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$conf = new configs();

$sql = "SELECT * FROM bd_site.DADOS ";
$sql .= "WHERE DAD_NOME NOT LIKE '' ";
$sql .= "AND DADOS.reg_del = 0 ";
$sql .= "AND DADOS.UID = '".$_GET["uid"]."' ";

$db->select($sql, 'MYSQL',true);

if ($db->erro != '')
{
	exit('Não foi encontrado o curriculo selecionado na pasta de arquivos! '.$db->erro);
}

$dados = $db->array_select[0];

$caminho = explode(DIRECTORY_SEPARATOR, __FILE__);
array_pop($caminho);
array_pop($caminho);
$caminho = implode(DIRECTORY_SEPARATOR, $caminho);
$arquivo = str_replace('../', '', $dados['LinkDoc']);

$arquivo = str_replace('/', DIRECTORY_SEPARATOR, $arquivo);
$arquivo = str_replace('\\', DIRECTORY_SEPARATOR, $arquivo);
$arquivo = $caminho.DIRECTORY_SEPARATOR.$arquivo;

downloadFile($arquivo);
?>