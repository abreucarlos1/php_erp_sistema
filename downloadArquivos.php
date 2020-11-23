<?php
/*
	Processamento de arquivos para download
	Criado por Carlos Eduardo

	local/Nome do arquivo:
	../includes/downloadArquivo.php
	
	data de criação
	Versão 0 --> VERSÃO INICIAL : 13/12/2014
*/	

$caminho = explode(DIRECTORY_SEPARATOR, __FILE__);
array_pop($caminho);
$caminho = implode(DIRECTORY_SEPARATOR, $caminho);

$arquivo = str_replace('../', '', $_GET['documento']);
$arquivo = str_replace('/', DIRECTORY_SEPARATOR, $arquivo);
$arquivo = str_replace('\\', DIRECTORY_SEPARATOR, $arquivo);
$arquivo = $caminho.DIRECTORY_SEPARATOR.$arquivo;

downloadFile("$arquivo");
?>