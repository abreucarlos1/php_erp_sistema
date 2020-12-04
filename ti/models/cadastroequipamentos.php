<?php
/**
 * Classe responsável por prover os métodos para manutenção do cadastro de equipamentos
 * @author carlos.maximo
 * @since 19/09/2014
 */
class cadastroequipamentos
{
	public $db;
	public $tabela;
	public $campos;
	public $formTipos;
	public $camposOcultos;
	
	public function __construct()
	{
		$this->formTipos = array(
			'text' => array(
				'tinyint',
				'varchar',
				'int'
			),
			'textarea' => array(
				'text'
			)
		);
		
		$this->camposOcultos = array(
			'reg_del',
			'id_equipamento',
			'area'
		);
		
		$this->db = new banco_dados();
		
		$sql =
		"SELECT
		  TABLE_NAME tabela, COLUMN_NAME coluna, ORDINAL_POSITION ordem, COLUMN_DEFAULT padrao, IS_NULLABLE nulo,
		  DATA_TYPE tipo, CHARACTER_MAXIMUM_LENGTH maximoCaracteres, COLUMN_KEY chave, COLUMN_COMMENT comentario
		
		FROM information_schema.columns
		WHERE table_schema = 'ti'
		AND table_name = 'equipamentos'
		ORDER BY ORDINAL_POSITION;";
		
		$this->db->select($sql, 'MYSQL',true);
		
		//Populando a variável tabela com todos os dados da tabela para montagem da tela
		foreach ($this->db->array_select as $reg)
		{
			if (!in_array($reg['coluna'], $this->camposOcultos))
			{
				$comentario = explode('/', $reg['comentario']);
				
				$nomeLabel = $reg['coluna'];
				
				$options = '';
				
				$obrigatorio = $reg['nulo'] == 'NO' ? 'obrigatorio' : '';
				
				$for = $reg['nulo'] == 'NO' ? "for='txt_".$reg['coluna']."'" : '';
				
				if ($comentario[0] == 1)
				{
					$nomeLabel = $comentario[1];
				}
				else if ($comentario[0] == 2)
				{
					$itens = explode(',', $comentario[1]);
					
					$options .= '<option value="">Selecione...</option>';					
					foreach($itens as $k => $item)
					{
						$options .= "<option value='".$k."'>".$item."</option>";
						$this->campos[$reg['coluna']]['options'][$k] = $item;
					}
				}

				if ($options == '')
				{
					//Nome da coluna para a listagem
					$this->campos[$reg['coluna']]['nome'] = ucwords(strtolower($nomeLabel));
				
					if (in_array($reg['tipo'], $this->formTipos['text']))
					{
						$label = "<label ".$for." class='labels'>".$nomeLabel."</label>";
						$reg['campoForm'] = $label."<input type='text' name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."' value='".$reg['padrao']."' />"; 
					}
					else if(in_array($reg['tipo'], $this->formTipos['textarea']))
					{
						$label = "<label ".$for.">".$nomeLabel."</label>";
						$reg['campoForm'] = $label."<textarea name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$reg['padrao']."'</textarea>";
					}
				}
				else
				{
					//Nome da coluna para a listagem
					$this->campos[$reg['coluna']]['nome'] = ucwords(strtolower($nomeLabel));
					
					$label = "<label ".$for." class='labels'>".ucwords(strtolower($nomeLabel))."</label>";
					$reg['campoForm'] = $label."<select name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$options."</select>";
				}
			}
			else
			{
				//Nome da coluna para a listagem não está aqui pois somente campos não ocultos a tem.
				$reg['campoForm'] = "<input type='hidden' name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' value='".$reg['padrao']."' />";
			}
			
			$this->tabela[] = $reg;
		}
	}
	
	/**
	 * Método responsável por retornar a lista de equipamentos não excluídos
	 */
	public function getListaCompleta()
	{
		$retorno = array(true, array());
		
		$sql = 
		"SELECT
			*
		FROM
			ti.equipamentos
		WHERE
			equipamentos.reg_del = 0
		ORDER BY
			equipamentos.num_dvm ";
		
		$resultado = $this->db->select($sql, 'MYSQL');
		
		if ($this->db->erro != '')
		{
			$retorno = array(false, $this->db->erro);
		}
		else
		{
			$retorno = array($this->db->numero_registros, $resultado);
		}
		
		return $retorno;
	}
	
	public function inserir($_post)
	{
		$retorno = 1;
		
		$isql = 
		"INSERT INTO ti.equipamentos
			(equipamento, num_dvm, tipo, area)
		VALUES
			('".trim($_post['txt_equipamento'])."', '".trim($_post['txt_num_dvm'])."', ".$_post['txt_tipo'].", '".$_post['txt_area']."')";
		
		$this->db->insert($isql, 'MYSQL');
		
		if ($this->db->erro != '')
		{
			$retorno = 0;
		}
		
		return $retorno;
	}
	
	public function excluir($_post)
	{
		$retorno = 1;
		
		$id = $_post['id'];
		
		$usql = 
		"UPDATE ti.equipamentos
			SET reg_del = 1
			
		WHERE id_equipamento = '{$id}'";
		
		$this->db->update($usql, 'MYSQL');
		
		if ($this->db->erro != '')
		{
			$retorno = 0;
		}
		
		return $retorno;
	}
}