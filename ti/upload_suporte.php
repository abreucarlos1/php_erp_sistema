<?php
/*
	Formulario de upload de documentos	
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo:
	../ti/uplode_suporte.php

	Versao 0 --> VERSAO INICIAL : 20/12/2017
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados;

$resposta = false;
$erro = false;

$nomeArquivo = tiraacentos($_FILES['anexos_atendimento']['name']); //nome do arquivo (ex. "arquivo.dwg")
$tmp_arq = explode(".",$nomeArquivo);
$nomeArquivo = md5($tmp_arq[0].date('h:i:s')).'.'.$tmp_arq[1];
$tmpNome = $_FILES['anexos_atendimento']['tmp_name'];

if(!empty($tmpNome))
{
	$ext = $tmp_arq[count($tmp_arq)-1];
	
	$uploaded = move_uploaded_file($tmpNome,DOCUMENTOS_CHAMADOS.$nomeArquivo);
	
	$fileExists = file_exists(DOCUMENTOS_CHAMADOS.$nomeArquivo);
	if ($uploaded && $fileExists)
	{
        //Inclui os dados do  no banco de dados.
        $usql = "UPDATE suporte.interacoes SET i_anexo = '".$nomeArquivo."' WHERE i_id = ".$_POST['idInteracao'];
        $db->update($usql,'MYSQL');
        
        if ($db->erro != '')
        	exit($db->erro);
        else
        {
        	exit('
        		<script>
        			alert("Arquivo anexado corretamente ao chamado #'.$_POST['cId'].'");
        			window.parent.xajax_finalizar_gravacao('.$_POST['cId'].');
        		</script>
        	');
        }
    }
}
else
{
    exit('
		<script>
			window.parent.xajax_finalizar_gravacao('.$_POST['cId'].');
		</script>
	');	
}