<?php
/*
		Versão 0 --> VERSÃO INICIAL - 28/01/2015
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados;

$dados_form = $_POST;

$resposta = false;

$erro = false;

foreach($_FILES as $chave=>$valor)
{
	$indice = explode("_",$chave);
	
	$array_name[$indice[1]] = tiraacentos($valor["name"]); //nome do arquivo (ex. "arquivo.dwg")
	
	$array_tmp_name[$indice[1]] = $valor["tmp_name"];
	
	$array_type[$indice[1]] = $valor["type"];
}

//Informações do contrato
$sql = "SELECT 
	f.funcionario, pc.id_contrato
FROM 
	".DATABASE.".funcionarios f 
    JOIN ".DATABASE.".pj_contratos pc ON pc.id_funcionario = f.id_funcionario AND pc.reg_del = 0 
WHERE pc.id_contrato = ".$dados_form["id_contrato"]." 
AND f.reg_del = 0 ";


$db->select($sql, 'MYSQL', true);

$pastaArquivos = DOCUMENTOS_RH.'/documentos_funcionarios/';
$nomePasta = str_replace(' ', '_', maiusculas(tiraacentos($db->array_select[0]['funcionario'])));
$pastaCompleta = $pastaArquivos.$nomePasta;

if(!empty($dados_form["id_contrato"]))
{
	foreach($array_name as $index=>$conteudo)
	{
		if($conteudo!="")
		{
			$tmp_arq = explode(".",$conteudo);
			
			$ext = $tmp_arq[count($tmp_arq)-1];							  
			
			$documento_type = $array_type[$index];
	
			$documento_temp = $array_tmp_name[$index]; 
			
			if($documento_type=="application/pdf" || $documento_type=="image/jpeg")
			{
				//Arquivo válido 
				if($documento_temp != "")
				{
					//Nome do arquivo gravado fisicamente
					$nome_arq_grav = str_replace(' ', '_', $conteudo);
					if(!is_dir($pastaCompleta))
					{						
						if(!mkdir($pastaCompleta,0777,true))
						{
							exit('
								<script>
									alert("Erro ao criar pasta!");
								</script>
							');
						}						
					}						
					
					try{
						$uploaded = move_uploaded_file($documento_temp, $pastaCompleta.'/'.$nome_arq_grav);
					}
					catch (Exception $e)
					{
						$erroUpload = $e->getMessage();			
					}
					
					$fileExists = file_exists($pastaCompleta.'/'.$nome_arq_grav);
					
					if ($uploaded && $fileExists)
					{
						if (!isset($dados_form['pcd_id']) || empty($dados_form['pcd_id']))
						{
							$isql = "INSERT INTO ".DATABASE.".pj_contratos_x_documentos ";
							$isql .= "(pcd_id_contrato, pcd_titulo, pcd_arquivo) VALUES ";
							$isql .= "(".$dados_form["id_contrato"].", '".$conteudo."', '".$nomePasta."/".$nome_arq_grav."')";
						
							$db->insert($isql,'MYSQL');
						}
						else
						{
							$usql = "UPDATE ".DATABASE.".pj_contratos_x_documentos SET ";
							$usql .= "pcd_arquivo = '".$nomePasta.'/'.$nome_arq_grav."' ";
							$usql .= "WHERE pcd_id = ".$dados_form['pcd_id']." ";
							$usql .= "AND reg_del = 0 ";
							
							$db->update($usql,'MYSQL');
						}
						
						if ($db->erro != '')
							exit($db->erro);
						else
							exit('
								<script>
									alert("Arquivo anexado corretamente!");
									window.parent.xajax_listaArquivos("'.$dados_form['id_contrato'].'");
									window.parent.frm_0.reset();
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
			}
			else
			{
				exit('
						<script>
							alert("O tipo de arquivo anexado não é permitido");
						</script>
					');
			}
		}
	}
}
else
{
	exit('
			<script>
				alert("Não foi possível incluir o documento");
			</script>
		');	
}	

?>