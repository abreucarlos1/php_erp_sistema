<?php
/*
		Formulário de Upload liberações de medição
		
		Criado por Carlos
		
		local/Nome do arquivo:
		../contratos_controle/upload_bms_liberacoes.php
	
		Versão 0 --> VERSÃO INICIAL : 28/03/2018
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

if (HOST != 'localhost')
    $pastaArquivos = DOCUMENTOS_FINANCEIRO.'/pedidos/';
else
    $pastaArquivos = ROOT_DIR. '/contratos_controle/pedidos/';

$idMedicao = $_POST['idMedicaoLiberacao'];
$idItem = $_POST['idItemLiberacao'];

$nomeArquivo = $idMedicao.'_liberacao.'.$ext;
$pastaCompleta = $pastaArquivos.$nomeArquivo;

//Buscando todos os itens com valores medidos na mesma data e para o mesmo item
$sql = "SELECT
				DISTINCT medicoes.id_bms_medicao, medicao.valor_medido
			FROM
				".DATABASE.".bms_medicao medicao
				JOIN (
					SELECT * FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0
				) item ON item.id_bms_item = medicao.id_bms_item
				LEFT JOIN (
					SELECT
						DISTINCT *
					FROM
						".DATABASE.".bms_medicao
			            JOIN(
							SELECT id_bms_pedido idPedido, id_bms_item idItem, descricao descItemMesmaData FROM ".DATABASE.".bms_item WHERE bms_item.reg_del = 0
			            ) ped
			            ON ped.idItem = bms_medicao.id_bms_item
					 WHERE bms_medicao.reg_del = 0 AND valor_medido IS NOT NULL
				) medicoes
				ON medicoes.idPedido = item.id_bms_pedido
				AND medicoes.data = medicao.data
			WHERE
				medicao.id_bms_medicao = ".$idMedicao."
				AND medicao.reg_del = 0 ";

$valorMedicaoAtual = 0;
$itens = $db->select($sql, 'MYSQL', function ($reg, $i) use(&$valorMedicaoAtual){
    if (empty($valorMedicaoAtual))
        $valorMedicaoAtual = $reg['valor_medido'];
        
        return $reg['id_bms_medicao'];
});
    
$itens = implode(',', $itens);

if ($db->numero_registros == 0)
{
    exit('
			<script>
				alert("Não foram encontradas medições para anexar o documento!");
			</script>
		');
}

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
            $usql = "UPDATE ".DATABASE.".bms_medicao SET ";
            $usql .= "arquivo_liberacao = '".$nomeArquivo."' ";
            $usql .= "WHERE id_bms_medicao IN(".$itens.") ";
            $usql .= "AND reg_del = 0 ";
            
            $db->update($usql, 'MYSQL');
            if($db->erro!='')
            {
                exit('
    				<script>
    					alert("Houve uma falha ao tentar realizar o upload! ");
    				</script>
    			');
            }
            else
            {
                exit('
					<script>
                        window.parent.xajax_atualizatabela_medicoes('.$idItem.');
						alert("Arquivo anexado corretamente");
						window.parent.divPopupInst.destroi(1);
					</script>
				');
            }
        }
        else
        {
            exit('
        		<script>
        			alert("Houve uma falha ao tentar anexar o documento!");
        		</script>
        	');
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
?>