<?php
/*
        Formulário de Upload Arquivo Morto
        
        Criado por Carlos Eduardo  
        
        local/Nome do arquivo:
        ../contratos_controle/upload_bms_pedido.php
        
        Versão 0 --> VERSÃO INICIAL : 19/02/2018
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados();

$dados_form = $_POST;

$resposta = false;

$erro = false;

if (!empty($_FILES['fileArquivoMorto']["name"]))
{
    $name = $_FILES['fileArquivoMorto']["name"];
    $documento_temp = $_FILES['fileArquivoMorto']["tmp_name"];
    $documento_type = $_FILES['fileArquivoMorto']["type"];
    
    $tmp_arq = explode(".",$name);
    $ext = $tmp_arq[count($tmp_arq)-1];
    
    $pastaArquivos = DOCUMENTOS_GED.'ARQUIVO_MORTO/';
    
    $nomeArquivo = 'arquivo_morto.zip';
    $nomeArquivoBackup = 'arquivo_morto_backup.zip';
    $pastaCompleta = $pastaArquivos.$nomeArquivo;
    $pastaCompletaBackup = $pastaArquivos.$nomeArquivoBackup;
    
    if($ext=="zip")
    {
        //Arquivo válido
        if($documento_temp != "")
        {
            //APAGA O ARQUIVO BACKUP SE EXISTIR
            unlink($pastaCompletaBackup);
            
            //RENOMEIA O ARQUIVO PARA BACKUP
            rename($pastaCompleta, $pastaCompletaBackup);
            
            //SOBE O NOVO ARQUIVO, SUBSTITUINDO O ANTERIOR
            try{
                $uploaded = move_uploaded_file($documento_temp, $pastaCompleta);
            }
            catch (Exception $e)
            {
                $erroUpload = $e->getMessage();
            }
            
            $fileExists = file_exists($pastaCompleta);
            
            if ($uploaded && $fileExists)
            {
                $revisao_documento = 0;
                
                $sql = "SELECT revisao_documento revisao_documento FROM ".DATABASE.".arquivo_morto_versoes WHERE reg_del = 0 ORDER BY revisao_documento DESC";
                $db->select($sql, 'MYSQL', function($reg, $i) use(&$revisao_documento){
                    $revisao_documento = $reg['revisao_documento'];
                });
                
                $usql = "UPDATE ".DATABASE.".arquivo_morto_versoes SET status = 1 WHERE revisao_documento = ".$revisao_documento;
                
                $db->update($usql, 'MYSQL');
    
                exit('
        			<script>
                        alert("Arquivo bloqueado corretamente!");
                        window.parent.xajax_atualizatabela_versoes();
        			</script>
        		');
            }
        }
    }
    else
    {
        exit('
    		<script>
    			alert("O tipo de arquivo anexado não é permitido!");
                window.parent.xajax_liberarBloquear(1);
    		</script>
    	');
        
    }
}
else if (!empty($_FILES['fileArquivoDescarte']["name"]))
{
    $name = $_FILES['fileArquivoDescarte']["name"];
    $documento_temp = $_FILES['fileArquivoDescarte']["tmp_name"];
    $documento_type = $_FILES['fileArquivoDescarte']["type"];
    
    $tmp_arq = explode(".",$name);
    $ext = $tmp_arq[count($tmp_arq)-1];
    
    if (HOST == 'localhost')
    {
        $pastaArquivos = './modelos_excel/';
    }
    else
    {
        $pastaArquivos = DOCUMENTOS_GED.'ARQUIVO_MORTO/_versoes/';
    }
    
    $nomeArquivo = md5('documento_'.date('YmdHis')).'.xlsx';
    $pastaCompleta = $pastaArquivos.$nomeArquivo;
    $anoReferencia = $_POST['anoReferencia'];
    
    if (!empty($anoReferencia))
    {
        if($ext=="xlsx")
        {
            //Arquivo válido
            if($documento_temp != "")
            {
                //SOBE O NOVO ARQUIVO, SUBSTITUINDO O ANTERIOR
                try{
                    $uploaded = move_uploaded_file($documento_temp, $pastaCompleta);
                }
                catch (Exception $e)
                {
                    $erroUpload = $e->getMessage();
                }
                
                $fileExists = file_exists($pastaCompleta);
                
                if ($uploaded && $fileExists)
                {
                    $id = 0;
                    
                    $sql = "SELECT MAX(id) id FROM ".DATABASE.".arquivo_morto_descartes WHERE reg_del = 0";
                    $db->select($sql, 'MYSQL', function($reg, $i) use(&$id){
                        $id = $reg['id'];
                    });
                    
                    $usql = "UPDATE ".DATABASE.".arquivo_morto_descartes SET status = 1, nome_arquivo = '".$nomeArquivo."', ano_referencia = '".$anoReferencia."' WHERE id = ".$id;
                    $db->update($usql, 'MYSQL');
                        
                    exit('
            			<script>
                            alert("Arquivo bloqueado corretamente!");
                            window.parent.xajax_atualizatabela_descartes();
                            window.parent.document.getElementById("frm").reset();
            			</script>
            		');
                }
            }
        }
        else
        {
            exit('
        		<script>
        			alert("O tipo de arquivo anexado não é permitido!");
                    window.parent.xajax_liberarBloquearDescarte(1);
        		</script>
        	');
            
        }
    }
    else
    {
        exit('
        		<script>
        			alert("Por favor, escolha o ano de referencia!");
        		</script>
        	');
    }
}
?>