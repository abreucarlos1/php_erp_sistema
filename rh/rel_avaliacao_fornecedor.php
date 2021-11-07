<?php
/*
		Formulário de Avaliação de Fornecedor
		
		Criado por Carlos
	
		Versão 0 --> VERSÃO INICIAL : 03/06/2015
		Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(520))
{
	nao_permitido();
}

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script type="text/javascript">
function selecionarTodos(val)
{
	if (!val)
	{
		desseleciona_combo('selsetores');
	}
	else
	{
		seleciona_todos('selsetores');
	}
}
</script>

<?php
$conf = new configs();

$sql = "SELECT setor_aso, id_setor_aso FROM ".DATABASE.".setor_aso";
$sql .= "WHERE reg_del = 0 ";

$arr_values_setores = NULL;
$arr_output_setores = NULL;

$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $reg)
{
	$arr_values_setores[] = $reg['id_setor_aso'];
	$arr_output_setores[] = $reg['setor_aso'];
}


$smarty->assign("option_setores_values",$arr_values_setores);
$smarty->assign("option_setores_output",$arr_output_setores);

$sql = "SELECT ava_id, ava_titulo FROM ".DATABASE.".avaliacoes a ";
$sql .= "WHERE avaliacoes.reg_del = 0 ";

$arr_values_avaliacoes[] = '';
$arr_output_avaliacoes[] = 'SELECIONE';

$db->select($sql, 'MYSQL',true);

foreach($db->array_select as $reg)
{
		$arr_values_avaliacoes[] = $reg['ava_id'];
		$arr_output_avaliacoes[] = $reg['ava_titulo'];
}

$smarty->assign("option_avaliacoes_values",$arr_values_avaliacoes);
$smarty->assign("option_avaliacoes_output",$arr_output_avaliacoes);

$smarty->assign("campo",$conf->campos('avaliacao_fornecedor'));

$smarty->assign("revisao_documento","V2");

$smarty->assign("classe",CSS_FILE);

$smarty->display('rel_avaliacao_fornecedor.tpl');
?>