<?php
/**
 * Classe responsável por prover informações básicas para os demais models
 * @author carlos.maximo
 * @since 17/04/2015
 */
class auto_form
{
	public $db;
	public $tabela;
	public $campos;
	public $larguras = array();
	public $formTipos;
	public $camposOcultos;
	
	public function __construct($db = 'ti', $table = 'gestao_mudancas')
	{
		$this->formTipos = array(
			'text' => array(
				'tinyint',
				'varchar',
				'int',
				'date',
				'float'
			),
			'textarea' => array(
				'text'
			)
		);
		
		$this->db = new banco_dados();
		
		$sql =
		"SELECT
		  TABLE_NAME tabela, COLUMN_NAME coluna, ORDINAL_POSITION ordem, COLUMN_DEFAULT padrao, IS_NULLABLE nulo,
		  DATA_TYPE tipo, CHARACTER_MAXIMUM_LENGTH maximoCaracteres, COLUMN_KEY chave, COLUMN_COMMENT comentario
		
		FROM information_schema.columns
		WHERE table_schema = '".$db."'
		AND table_name = '".$table."'";
		
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
				$for = $reg['nulo'] == 'NO' ? 'for="txt_'.$reg['coluna'].'" ' : '';
				
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
						$label = "<div class='auto_form'><label ".$for." class='labels'>".$nomeLabel."</label>";
						$reg['campoForm'] = $label."<input type='text' name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."' value='".$reg['padrao']."' /></div>"; 
					}
					else if(in_array($reg['tipo'], $this->formTipos['textarea']))
					{
						$label = "<div class='auto_form'><label ".$for." class='labels'>".$nomeLabel."</label>";
						$reg['campoForm'] = $label."<textarea name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$reg['padrao']."</textarea></div>";
					}
				}
				else
				{
					//Nome da coluna para a listagem
					$this->campos[$reg['coluna']]['nome'] = ucwords(strtolower($nomeLabel));
					
					$label = "<div class='auto_form'><label ".$for." class='labels'>".ucwords(strtolower($nomeLabel))."</label>";
					$reg['campoForm'] = $label."<select name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$options."</select></div>";
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
}