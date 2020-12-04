<?php
class chamados_integracao_model{
	public $db = '';
	
	public function __construct()
	{
		$this->db = new banco_dados();
	}
	
	public function inserir($dados_form)
	{
		$retorno = array(1);
		$minDias = 0;

		if(!empty($dados_form["cliente"]) && !empty($dados_form["funcionario"]) && !empty($dados_form["descricao_integracao"]))
		{
			$sql = "SELECT ci_id_funcionario, ci_id_cliente FROM ".DATABASE.".chamados_integracao ";
			$sql .= "WHERE reg_del = 0 ";
			$sql .= "AND ci_id_funcionario = '".$dados_form["funcionario"]."' ";
			$sql .= "AND ci_id_cliente = '".$dados_form["cliente"]."' ";
			
			$reg = $this->db->select($sql,'MYSQL');
	
			if($this->db->numero_registros==0)
			{
				//Regras de data m�nima para integra��o no cliente
				$data = $dados_form['data'];
				
				$dif = dif_datas_weekend(date('d/m/Y'), $data);
				
				//A regra � que o m�nimo de dias para abertura do chamado s�o 8
				if ($dif < $minDias || empty($dados_form['data']))
				{
					$dataMinima = dateAddWithoutWeekEnds(date('Y-m-d'), $minDias, 'd/m/Y');
					$retorno = array(0,'A data m�nima para integra��o � '.$dataMinima);
				}
				else
				{
					foreach($dados_form['funcionario'] as $func)
					{
    					$isql = "INSERT INTO ".DATABASE.".chamados_integracao ";
    					$isql .= "(ci_id_cliente, ci_id_funcionario, ci_data, ci_cis_id, ci_desc, ci_id_funcionario_abertura) ";
    					$isql .= "VALUES ";
    					$isql .= "(".$dados_form['cliente'].", ";
    					$isql .= $func.", ";
    					$isql .= "'".php_mysql($dados_form['data'])."', ";
    					$isql .= "1, ";//Aguardando atendimento
    					$isql .= "'".maiusculas($dados_form['descricao_integracao'])."', ";
    					$isql .= $_SESSION['id_funcionario'].") ";
    					
    					$this->db->insert($isql,'MYSQL');
    					$idChamados[] = $this->db->insert_id;
					}
										
					if ($this->db->erro != '')
					{
						$retorno = array(0, "Houve uma falha ao tentar inserir o registro! ".$this->db->erro);
					}
					else
					{
						//Inserindo a intera��o
						$isql = "INSERT INTO ".DATABASE.".chamados_integracao_interacoes ";
						$isql .= "(cii_ci_id, cii_desc, cii_cis_id, cii_id_funcionario, cii_data) ";
						$isql .= "VALUES ";
						
						foreach($idChamados as $chamado)
						{
						    $isql .= $virgula."(".$chamado.", ";
    						$isql .= "'".maiusculas($dados_form['descricao_integracao'])."', ";
    						$isql .= "1, ";//Aguardando atendimento
    						$isql .= $_SESSION['id_funcionario'].", ";
    						$isql .= "'".date('Y-m-d')."' )";
    						
    						$virgula = ',';
						}
						
						$this->db->insert($isql,'MYSQL');
						
						$sql = "SELECT empresa, id_empresa_erp, descricao, unidade FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
						$sql .= "WHERE empresas.id_unidade = unidades.id_unidade AND id_empresa_erp = ".$dados_form['cliente']." ";
						$sql .= "AND empresas.reg_del = 0 ";
						$sql .= "AND unidades.reg_del = 0 ";
						
						$this->db->select($sql, 'MYSQL', true);
						
						$dadosCliente = $this->db->array_select[0];
						
						//email
						$params 			= array();
						$params['from']		= "recrutamento@dominio.com.br";
						$params['from_name']= "RECURSOS HUMANOS";
						$params['subject'] 	= "NOVA SOLICITA��O DE INTEGRA��O (".$dadosCliente['empresa'].' '.$dadosCliente['descricao'].' '.$dadosCliente['unidade'].")";

                        		$sql = 
                        "SELECT
                        	DISTINCT funcionario, email, id_funcionario, CASE WHEN id_funcionario = ci_id_funcionario THEN '*' ELSE '' END func
                        FROM
                        	".DATABASE.".funcionarios
                        	JOIN(
                        		SELECT email, id_funcionario id_funcionario, reg_del as deletado FROM ".DATABASE.".usuarios
                        	) usuario
                        	ON id_funcionario = id_funcionario AND deletado = 0
                            JOIN ".DATABASE.".chamados_integracao ci ON ci.reg_del = 0 AND ci_id IN(".implode(',', $idChamados).")
                        WHERE
                        	situacao = 'ATIVO'
							AND funcionarios.reg_del = 0  
							AND id_funcionario IN (ci_id_funcionario, ci_id_funcionario_abertura)";
						
						$this->db->select($sql, 'MYSQL', true);
												
						$corpo = "<b>Novo(s) chamado(s) de integra��o N�ms: ".implode(',', $idChamados)."</b><br /><br />";
						
						foreach($this->db->array_select as $func)
						{
							$params['emails']['to'][] = array('email' => $func['email'], 'nome' => $func['funcionario']);
							
							if ($func['func'] == '*')
							    $corpo .= "<b>Funcion�rio:</b> ".$func['funcionario'].".<br />";
						    else if ($func['id_funcionario'] == $func['ci_id_funcionario_abertura'])
						        $corpo .= "<b>Solicitante:</b> ".$func['funcionario'].".<br />";
						}						
						
						$corpo .= "<b>Cliente</b>: ".$dadosCliente['empresa'].' '.$dadosCliente['descricao'].' '.$dadosCliente['unidade'].'<br />';
						$corpo .= "<b>data</b>: ".$dados_form['data']."<br />";
						$corpo .= "<b>Descri��o</b>: ".maiusculas($dados_form['descricao_integracao']).'<br />';
						
						//Pegando o n�mero de chamados n�o encerrados at� o momento
						$sql = "SELECT COUNT(ci_id) numChamados FROM ".DATABASE.".chamados_integracao ";
						$sql .= "WHERE reg_del = 0 ";
						$sql .= "AND ci_cis_id NOT IN(5) ";
						
						$this->db->select($sql, 'MYSQL', true);
						
						$corpo .= "<b>Posi��o na fila:</b> ".$this->db->array_select[0]['numChamados'].'�<br />';
						
						$sql = "SELECT
                					cis_desc
                				FROM
                					".DATABASE.".chamados_integracao_status
                				WHERE
                					reg_del = 0 AND cis_id = 1";
						
						$this->db->select($sql, 'MYSQL', true);
						
						$corpo .= "<b>status</b>: ".$this->db->array_select[0]['cis_desc'].'<br />';
						
						$mail = new email($params, 'chamados_integracao_cliente');
						$mail->montaCorpoEmail($corpo);
						$mail->Send();
						$retorno = array(1, "Registro realizado corretamente!");
					}
				}			
			}
			else
			{
				$retorno = array(0, "Registro j� existente no banco de dados");			
			}		
		}
		else
		{
			$retorno = array(0, "ATEN��O: Todos os campos devem estar preenchidos");
		}
		
		return $retorno;
	}
}
?>