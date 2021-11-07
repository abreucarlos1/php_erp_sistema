<?php
/*
	Formulário de Upload de documentos de candidatos
 	Criado por Carlos
 
		local/Nome do arquivo:
		../rh/candidato_upload.php
		
		 Versão 0 --> VERSÃO INICIAL : 08/04/2016
		 Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
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
	
	$array_name[] = tiraacentos($valor["name"]); //nome do arquivo (ex. "arquivo.dwg")
	
	$array_tmp_name[] = $valor["tmp_name"];
	
	$array_type[] = $valor["type"];
}

$idCandidato = $_POST['anexo_candidato_id'];
$tipoDocumento = isset($_POST['tipo_documento']) && !empty($_POST['tipo_documento']) ? $_POST['tipo_documento'] : '';

foreach($array_name as $index=>$conteudo)
{
	if($conteudo!="")
	{
		$tmp_arq = explode(".",$conteudo);
		
		$ext = $tmp_arq[count($tmp_arq)-1];
		
		$documento_type = $array_type[$index];

		//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
		$documento_temp = $array_tmp_name[$index];
		
		$nome_arq_grav = $idCandidato.'_'.strtoupper(str_replace(' ', '_', tiraacentos($conteudo)));
		
		if (!empty($tipoDocumento))
		{
    		$sql = "SELECT ctd_descricao FROM ".DATABASE.".candidatos_tipos_documentos ";
			$sql .= "WHERE ctd_id = ".$tipoDocumento." ";
			$sql .= "AND reg_del = 0 ";
    		
			$db->select($sql, 'MYSQL', true);
    		
			$nomeReal = str_replace('/', '', $db->array_select[0]['ctd_descricao']);
			$nomeReal = str_replace(' ', '_', $nomeReal).'.'.$ext;
		    
			$funcaoChamada = "window.parent.xajax_atualizatabela({$idCandidato});";
		}
		else
		{
		    $nomeReal = $conteudo;
		    $funcaoChamada = 'window.location = "./anexar_documentos_candidatos.php?id="+'.$idCandidato.';';
		}
		
		if($documento_type=="application/pdf")
		{
			//Arquivo válido 
			if($documento_temp != "")
			{
				try{
					$sql = "SELECT nome, year(data_inicio) ano FROM ".DATABASE.".candidatos ";
					$sql .= "WHERE id = ".$idCandidato." ";
					$sql .= "AND reg_del = 0 ";
					
					$db->select($sql, 'MYSQL', true);
					
					if ($db->numero_registros == 0)
					{
						exit('
							<script>
								alert("Não foi encontrato do registro do candidato! ");
								window.location = "./anexar_documentos_candidatos.php?id="+'.$idCandidato.';
							</script>
						');
					}
					
					$anoInicio = $db->array_select[0]['ano'];
					
					//Criando a pasta do candidato no ano atual de cadastro
					$pastaCandidato = DOCUMENTOS_RH.'/documentos_candidatos/'.$anoInicio.'/'.strtoupper(tiraacentos($db->array_select[0]['nome']));
					//$pastaCandidato = './documentos_funcionarios/'.$anoInicio.'/'.strtoupper(tiraacentos($db->array_select[0]['nome']));
					if (!is_dir($pastaCandidato))
					{
						$pastaAno = DOCUMENTOS_RH.'/documentos_candidatos/'.$anoInicio;
					    //$pastaAno = './documentos_funcionarios/'.$anoInicio;
						if (!is_dir($pastaAno))
						{
							mkdir($pastaAno);
						}
						
						mkdir($pastaCandidato);
					}
					
					$uploaded = move_uploaded_file($documento_temp,$pastaCandidato.'/'.$nomeReal);
				}
				catch (Exception $e)
				{
					$erroUpload = $e->getMessage();			
				}
				
				$fileExists = file_exists($pastaCandidato.'/'.$nomeReal);
				
				if ($uploaded && $fileExists)
				{
					//Inclui os dados do  no banco de dados.
					$isql = "INSERT INTO ".DATABASE.".candidatos_arquivos (caq_candidato_id, caq_arquivo, caq_ctd_id) VALUES (";
					$isql .= "'" . $idCandidato . "', ";
					$isql .= "'" . $nomeReal . "', ";
                    $isql .= "'" . $_POST['tipo_documento'] . "') ";
					
					$db->insert($isql,'MYSQL');
					
					if ($db->erro != '')
						exit($db->erro);
					else
					{
					    
					    
						exit('
							<script>
								alert("Arquivo anexado corretamente!");
								'.$funcaoChamada.'
							</script>
						');
					}
				}
				else
				{
					exit('
						<script>
							alert("Houve uma falha ao tentar realizar o upload! ");
							'.$funcaoChamada.'
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
 						'.$funcaoChamada.'
					</script>
				');
		}
	}
}

?>