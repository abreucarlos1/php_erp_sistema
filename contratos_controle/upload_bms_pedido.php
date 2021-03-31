<?php
/*
		Formulário de Upload BMS	
		
		Criado por Carlos Eduardo  
		
		local/Nome do arquivo:
		../contratos_controle/upload_bms_pedido.php
	
		Versão 0 --> VERSÃO INICIAL : 07/06/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
//require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados();

$dados_form = $_POST;

$resposta = false;

$erro = false;

$name = $_FILES['myfile']["name"];
$documento_temp = $_FILES['myfile']["tmp_name"];
$documento_type = $_FILES['myfile']["type"];

$tmp_arq = explode(".",$name);
$ext = $tmp_arq[count($tmp_arq)-1];							  

$pastaArquivos = DOCUMENTOS_FINANCEIRO.'/pedidos/';

$tipoArquivo = $_POST['tipo_arquivo'];
$os = $_POST['os'];
$nomeArquivo = $tipoArquivo.'_'.$os.'_'.str_replace(' ', '_', $_POST['nome_arquivo']).'.'.$ext;
$pastaCompleta = $pastaArquivos.$nomeArquivo;

//São 3 campos possíveis, arquivo_pedido, arquivo_contrato e arquivo_proposta
$campo = 'arquivo_'.$tipoArquivo;

if ($campo != 'arquivo_')
{
    $sql = "SELECT id_bms_pedido FROM ".DATABASE.".bms_pedido ";
	$sql .= "WHERE bms_pedido.reg_del = 0 ";
	$sql .= "AND ".$campo." = '".$nomeArquivo."' ";
	
    $db->select($sql, 'MYSQL');
    
    if ($db->numero_registros > 0)
    {
        exit('
    			<script>
    				alert("Já existe um documento com este nome, por favor, altere-o e anexe novamente!");
    			</script>
    		');
    }
    
    if(!empty($dados_form["nome_arquivo"]) && !empty($os))
    {
    	if(minusculas($ext)=="pdf")
    	{
    		//Arquivo válido 
    		if($documento_temp != "")
    		{
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
    			    $sql = "SELECT id_bms_pedido FROM ".DATABASE.".bms_pedido WHERE reg_del = 0 AND os = ".$os;
    			    $db->select($sql, 'MYSQL', true);
    			    
    			    if ($db->numero_registros == 0)
    			    {
    			        $isql = "INSERT INTO ".DATABASE.".bms_pedido (id_os, alterado_manualmente, ".$campo.") VALUES ";
    			        $isql .= "('".$os."', 1, '".$nomeArquivo."')";
    			        
    			        $db->insert($isql, 'MYSQL');
    			        $idBmsPedido = $db->insert_id;
    			    }
    			    else
    			    {
    			        $idBmsPedido = $db->array_select[0]['id_bms_pedido'];
    			        
        			    $usql = "UPDATE ".DATABASE.".bms_pedido SET alterado_manualmente = 1, ".$campo." = '".$nomeArquivo."' ";
    					$usql .= "WHERE id_bms_pedido = ".$idBmsPedido." ";
    					$usql .= "AND reg_del = 0 ";
                       
    				    $db->update($usql, 'MYSQL');
    			    }
    			    
    			    if ($db->erro == '')
    			    {
    			        $corpo = "<span style=\"font-family:Arial, Helvetica, sans-serif; font-size:11px; padding:0px; margin:0px;\"><P>Foi anexado ao sistema BMS o pedido abaixo:</P>";
    			        $corpo .= "<P>Nº: " . str_replace(' ', '_', $_POST['nome_arquivo']) . "</P>";
    			        $corpo .= "<p>tipo de documento: ".$tipoArquivo."</p>";
    			        $corpo .= "<P>data: " . date("d/m/Y") . "</P>";
    			        $corpo .= "<P>Colaborador: " . $_SESSION["nome_usuario"] . "</P>";
    			     
    			        if (ENVIA_EMAIL)
    			        {
        			        $params 			= array();
        			        $params['from']	    = "ti@dominio.com.br";
        			        $params['from_name']= "ARQUIVO ANEXADO NO SISTEMA BMS";
        			        $params['subject'] 	= "ARQUIVO ANEXADO NO SISTEMA BMS";
        			        
        			        $mail = new email($params, 'bms_pedido_anexado');
							
							$mail->addAttachment($pastaCompleta);
							
							$mail->montaCorpoEmail($corpo);
        			        
        			        if(!$mail->Send())
        			        {
        			            exit('
                					<script>
                						alert("Falha ao tentar enviar o e-mail, porém, o arquivo foi anexado corretamente!");
                                        window.parent.xajax_atualizatabela();
                                        //window.parent.divPopupInst.destroi();
                					</script>
                				');
        			        }
    			        }
    			        
                        exit('
                    			<script>
                                    alert("Arquivo anexado corretamente"); 
                                    //window.parent.xajax_atualizatabela();
                                    window.parent.xajax_modal_anexar_pedido('.$os.');
                                    window.parent.divPopupInst.destroi();
                    			</script>
                    		');	
    			    }
    			    else
    			    {
    			        exit('
        					<script>
        						alert("Houve uma falha ao tentar realizar o upload! ");
        					</script>
        				');
    			    }
    			}
    			else
    			{
    				exit('
    					<script>
    						alert("Houve uma falha ao tentar realizar o upload! ");
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
    				</script>
    			');
    	}
    }
    else
    {
    	exit('
    			<script>
    				alert("Por favor, digite o Numero do pedido e anexe novamente!");
    			</script>
    		');	
    }
}
?>