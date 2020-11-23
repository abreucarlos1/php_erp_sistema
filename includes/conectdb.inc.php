<?php
class banco_dados
{
	// Cria uma conexao com o banco de dados
	// variaveis 	$host -> servidor onde está o banco de dados
	//				$database -> nome do banco de dados onde estao as tabelas
	//Usuario Qualquer
	//Alterado em 11/06/2012
	//Carlos Abreu

	var $conexao = '';

	var $conexao_ms = '';

	function __construct()
	{
		switch(AMBIENTE)
		{
			case 1: //Ambiente de testes
				//web
				$this->host = "127.0.0.1";
				$this->pass = "root";
				$this->user = "root";
				$this->db = "mysql";
				
				//MS
				//$this->ms_host = "localhost:1433";
				//$this->ms_user = "";
				//$this->ms_pass = "";
				//$this->db_ms = "";		
			break;
			
			case 2: //Ambiente de Produ��o
				//web
				$this->host = "127.0.0.1";
				$this->pass = "root";
				$this->user = "root";
				$this->db = "mysql";
				
				//MS
				//$this->ms_host = "localhost:1433";
				//$this->ms_user = "";
				//$this->ms_pass = "";
				//$this->db_ms = "";		
			break;
			
			case 3: //Ambiente local
				//web
				$this->host = "localhost";
				$this->pass = "root";
				$this->user = "root";
				$this->db = "mysql";
				
				//MS
				$this->ms_host = "localhost:1433";
				$this->ms_user = "";
				$this->ms_pass = "";
				$this->db_ms = "";		
			break;
		}
	}
	
	//USO COMUM
	private $result = '';
	private $resultMS = '';
	public $erro = '';
	
	//MYSQL
	public $numero_registros = 0;
	public $array_select = array();
	public $insert_id = '';
	
	//MSSQL
	public $numero_registros_ms = 0;
	
	function conexao_db()
	{
		$this->conexao = mysqli_connect($this->host,$this->user,$this->pass, $this->db) or die ("A conexão com o servidor falhou.");
	}
	
	function fecha_db()
	{
		mysqli_close($this->conexao);
	}
	
	function conexao_ms_db()
	{
		$this->conexao_ms = mssql_connect($this->ms_host,$this->ms_user,$this->ms_pass) or die ("MS: A conexão com o servidor falhou.");
		mssql_select_db ($this->db_ms, $this->conexao_ms);
	}
	
	function fecha_ms_db()
	{
		mssql_close($this->conexao_ms);
	}
	
	//funcao para tratamento de erros no sistema
	private function erros($erros, $pagina = '', $tipo = 'sql')
	{
		//$errorLog = new errorLogs();

		//$errorLog->ERROR_LOG($_SESSION['id_funcionario'].' - '.$_SESSION['nome_usuario'], $erros, $pagina, $tipo);		
	}
	
	//verifica a conexao ao banco
	private function state($banco = 'MYSQL')
	{	
		if($banco=='MYSQL')
		{			
			if($this->conexao)
			{
				return TRUE;
			}
			else
			{
				return FALSE;	
			}
		}
		else
		{
			if($this->conexao_ms)
			{
				return TRUE;
			}
			else
			{
				return FALSE;	
			}	
		}
		
	}
	
	//FAZ A CONSULTA, CONFORME O TIPO
	/*
	 * $tratamentoRetorno = boolean (Popula $this->array_select ou resource)
	 * $tratamentoRetorno = Funcao Anonima (retorna do jeito que eu quiser)*/
	private function query($sql,$type = 'SELECT',$banco = 'MYSQL', $tratamentoRetorno = false)
	{
	
		$this->array_select = array();
		
		//Verifica o estado da conexao, se existir faz a consulta
		//caso contrario, realiza a conexao
		if(!$this->state($banco))
		{
			if($banco=='MYSQL')
			{	
				$this->conexao_db();
			}
			else
			{
				$this->conexao_ms_db();
			}
		}
		//SE TIPO FOR MYSQL
		if($banco=='MYSQL')
		{
			//Faz a query
			$this->result = mysqli_query($this->conexao,$sql);
			
			if(!$this->result)
			{
				$this->erro = "Erro na consulta no banco ".$banco." - ".mysqli_error($this->conexao)." - ".$sql;
			
				$this->erros($this->erro, PAGINA, 'sql');
			}
			else
			{
				switch ($type)
				{
					case 'SELECT':
						
						//retorna o numero de registros
						$this->numero_registros = mysqli_num_rows($this->result);
						
						if(is_callable($tratamentoRetorno)) 
						{
							$i = 0;
							
							$ret = array();
							
							while ($reg = mysqli_fetch_assoc($this->result))
							{
								$ret[] = $tratamentoRetorno($reg, $i);//Funcao anonima passada na chamada $db->select($sql,'MYSQL',	function($reg, $i) use(&$array_os_values){$array_os_values[$i] = $reg['id_os'];});
								
								$i++;
							}
							
							//Se for uma consulta limitada, ou seja, para paginacao
							if (strripos($sql, 'LIMIT'))
							{
								//Removendo tudo abaixo do order by 
								$ordPos = strripos($sql, 'ORDER BY');$line = __LINE__;
								$orderBy  = substr($sql, $ordPos);
								$sql = substr($sql, 0, $ordPos-1);
								
								//Criando uma subquery para totalizar 
								$sql = "SELECT COUNT(*) as total FROM (".trim($sql).") AS TOTAL";
								
								$total = mysqli_fetch_assoc(mysqli_query($this->conexao,$sql));
								//Alterando o numero de registros para o total encontrado
								$this->numero_registros = intval($total['total']);
								//Fim do tratamento da query
							}
							
							return $ret;
						}
						else if ($tratamentoRetorno)
						{
							while($reg = mysqli_fetch_assoc($this->result))
							{
								if (is_array($reg))
								{
									$this->array_select[] = $reg;
								}
							}
						}						

					break;
					
					case 'INSERT':
						//retorna o id inserido
						$this->insert_id = mysqli_insert_id($this->conexao);
											
						//retorna o numero de registros afetados
						$this->numero_registros = mysqli_affected_rows($this->conexao);
					break;
					
					case 'UPDATE':
						//retorna o numero de registros afetados
					    $this->numero_registros = mysqli_affected_rows($this->conexao);
					break;
					
					case 'DELETE':
						//retorna o numero de registros afetados
					    $this->numero_registros = mysqli_affected_rows($this->conexao);
					break;

					case 'EXEC':
						//retorna o numero de registros afetados
					    //$this->numero_registros = mysqli_affected_rows($this->conexao);
					break;
				
				}
				return $this->result;					
			}
		}
		else
		{
			//Faz a query
			$this->resultMS = mssql_query($sql,$this->conexao_ms);
			
			if(!$this->resultMS)
			{
				$this->erro = "Erro: erro na consulta no banco ".$banco." - ".mssql_get_last_message()." - ".$sql;
			
				$this->erros($this->erro, PAGINA,'sql');
			}
			else
			{
				switch ($type)
				{
					case 'SELECT':
						
						//retorna o numero de registros
						$this->numero_registros_ms = mssql_num_rows($this->resultMS);
					
					if(is_callable($tratamentoRetorno)) 
					{
						$i = 0;
						
						$ret = array();
						
						while ($reg = mssql_fetch_assoc($this->resultMS))
						{
							$ret[] = $tratamentoRetorno($reg, $i);//Funcao anonima passada na chamada $db->select($sql,'MYSQL',	function($reg, $i) use(&$array_os_values){$array_os_values[$i] = $reg['id_os'];});
							$i++;
						}
						
						return $ret;
					}
					else if ($tratamentoRetorno)
					{
						while ($reg = mssql_fetch_assoc($this->resultMS))
						{
							if (is_array($reg))
							{
								$this->array_select[] = $reg;
							}
						}
					}

					break;
				}
				
				$this->numero_registros_ms[$index] = mssql_rows_affected($result);
				
				return $this->resultMS;			
			}	
		}
	}
	
	//FUNCOES DE EXECUCAO DE QUERYS
	public function select($sql = NULL,$banco = 'MYSQL', $retornaArray = false)
	{
		//Verifica se existe a consulta
		if(is_null($sql))
		{
			$this->erro = "Erro: Sem consulta.";
			
			$this->erros($this->erro, PAGINA, 'sql');
		}
		else
		{
			return $this->query($sql,'SELECT',$banco, $retornaArray);
		}
	}		
	
	public function insert($sql = NULL,$banco = 'MYSQL')
	{
		//Verifica se existe a consulta
		if(is_null($sql))
		{
			$this->erro = "Erro: Sem consulta.";
			
			$this->erros($this->erro, PAGINA, 'sql');
		}
		else
		{
			$this->query($sql,'INSERT',$banco);
		}
	}
	
	public function update($sql = NULL, $banco = 'MYSQL')
	{
		//Verifica se existe a consulta
		if(is_null($sql))
		{
			$this->erro = "Erro: Sem consulta.";
			
			$this->erros($this->erro, PAGINA, 'sql');
		}
		else
		{
			$this->query($sql,'UPDATE',$banco);
		}
	}
	
	public function delete($sql = NULL, $banco = 'MYSQL')
	{
		//Verifica se existe a consulta
		if(is_null($sql))
		{
			$this->erro = "Erro: Sem consulta.";
			
			$this->erros($this->erro, PAGINA, 'sql');
		}
		else
		{
			$this->query($sql,'DELETE',$banco);
		}
	}

	public function exec_query($sql = NULL,$banco = 'MYSQL')
	{
		//Verifica se existe a consulta
		if(is_null($sql))
		{
			$this->erro = "Erro: Sem consulta.";
			
			$this->erros($this->erro, PAGINA, 'sql');
		}
		else
		{
			$this->query($sql,'EXEC',$banco);
		}
	}
	
	public function __destruct() 
	{
		//mysql_close($this->conexao);
		
		//mssql_close($this->conexao_ms);
	}
}	

?>