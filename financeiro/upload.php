<?php
/*
		Formulario de upload de documentos	
		
		Criado por Carlos 
		
		local/Nome do arquivo:
		../financeiro/upload.php
	
		Versão 0 --> VERSÃO INICIAL : 27/06/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
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

if($dados_form["id_fechamento"]!="" || $dados_form["id_fechamento"]>0)
{
	foreach($array_name as $index=>$conteudo)
	{
		if($conteudo!="")
		{
			$tmp_arq = explode(".",$conteudo);
			
			$ext = $tmp_arq[count($tmp_arq)-1];							  
			
			$documento_type = $array_type[$index];
	
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
			$documento_temp = $array_tmp_name[$index]; 
			
			//if($curriculo_type=="application/msword" || $curriculo_type=="text/plain" || $curriculo_type=="text/richtext" || $curriculo_type=="application/pdf")
			if($documento_type=="application/pdf")
			{
				//Arquivo válido 
				if($documento_temp != "")
				{
					$sql = "SELECT * FROM ".DATABASE.".fechamento_folha ";
					$sql .= "WHERE fechamento_folha.id_fechamento = '".$dados_form["id_fechamento"]."' ";
					$sql .= "AND fechamento_folha.reg_del = 0 ";
					
					$db->select($sql,'MYSQL',true);
				
					$cont0 = $db->array_select[0];
					
					$str_compet = explode(",",$cont0["periodo"]);
						
					$competencia = explode("-",$str_compet[0]);	
					
					$sql = "SELECT * FROM ".DATABASE.".fechamento_tipos_tributos ";
					$sql .= "WHERE fechamento_tipos_tributos.id_fechamento_tipos_tributos = '".$dados_form["tipo_tributo"]."' ";
					$sql .= "AND fechamento_tipos_tributos.reg_del = 0 ";
	
					$db->select($sql,'MYSQL',true);
					
					$cont = $db->array_select[0];						
											
					$sql = "SELECT * FROM ".DATABASE.".fechamento_documentos ";
					$sql .= "WHERE fechamento_documentos.id_fechamento_tipos_tributos = '".$dados_form["tipo_tributo"]."' ";
					$sql .= "AND fechamento_documentos.reg_del = 0 ";
					$sql .= "AND fechamento_documentos.id_fechamento = '".$dados_form["id_fechamento"]."' ";						

					$db->select($sql,'MYSQL',true);
					
					if($db->numero_registros <= 0)
					{											
						//Nome do arquivo gravado fisicamente
						$nome_arq_grav = "comprovante_". tiraacentos($cont["fechamento_tipos_tributos"]) ."_". $competencia[1].$competencia[0]. "_" .$dados_form["id_fechamento"]. "." . $ext;
						
						if(!is_dir(DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ))
						{						
							if(!mkdir(DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ,0777,true))
							{
								exit('
									<script>
										alert("Erro ao criar pasta!");
										window.parent.location = "./cadastra_docs_forn.php?id_fechamento='.$dados_form["id_fechamento"].'";
									</script>
								');
							}						
						}						
						
						try{
							$uploaded = move_uploaded_file($documento_temp,DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$nome_arq_grav);
						}
						catch (Exception $e)
						{
							$erroUpload = $e->getMessage();			
						}
						
						$fileExists = file_exists(DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$nome_arq_grav);
						
						if ($uploaded && $fileExists)
						{
							//Inclui os dados do  no banco de dados.
							$isql = "INSERT INTO ".DATABASE.".fechamento_documentos (id_fechamento, id_fechamento_tipos_tributos, competencia, documento, data_carregamento) VALUES (";
							$isql .= "'" . $dados_form["id_fechamento"] . "', ";
							$isql .= "'" . $dados_form["tipo_tributo"] . "', ";
							$isql .= "'" . $competencia[1].$competencia[0] . "', ";
							$isql .= "'" . $nome_arq_grav . "', ";
							$isql .= "'".date("Y-m-d")."') ";
							
							$db->insert($isql,'MYSQL');
							
							if ($db->erro != '')
								exit($db->erro);
							else
								exit('
									<script>
										alert("Arquivo anexado corretamente!");
										window.parent.location = "./cadastra_docs_forn.php?id_fechamento='.$dados_form["id_fechamento"].'";
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
									alert("Já foi registrado o documento selecionado!");
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