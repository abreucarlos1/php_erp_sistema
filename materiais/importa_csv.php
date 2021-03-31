<?php
/*
	Formulário de importacao CSV
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	
	../materiais/importa_csv.php
	
	Versão 0 --> VERSÃO INICIAL - 15/12/2008
	Versão 1 --> Atualização classe banco de dados - 21/01/2015 - Carlos Abreu
*/	
require("../includes/include_form.inc.php");

$smarty = new Smarty();

$smarty->left_delimiter = "<smarty>";

$smarty->right_delimiter = "</smarty>";

$smarty->template_dir = "templates";

$smarty->compile_check = true;

$smarty->force_compile = true;

$db = new banco_dados;

function importa($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($_FILES["arquivo"]["name"]=="")
	{
		$resposta -> addAlert("Arquivo deve ser carregado.");		
	}
	else
	{
		$sql = "SELECT * FROM materiais_1.`lista de materiais para compra` ";
		$sql .= "WHERE nr_rev = '" . $dados_form["versao_documento"] . "' ";
		$sql .= "AND nr_lista = '" . $dados_form["nr_dvm"] . "' ";		
		
		$reg = $db->select($sql,'MYSQL');
		
		if($db->numero_registros > 0)
		{
			$resposta -> addAlert("A revisão ".$dados_form["versao_documento"]." deste documento já esta carregada");		
		}	
		else
		{
			$arquivo_temp = $_FILES["arquivo"]["tmp_name"];
			
			$tmp_arq = explode(".",$_FILES["arquivo"]["name"]);
			
			$ext = $tmp_arq[count($tmp_arq)-1];
			
			//formato do código:
			// matcode -->codigo do banco devemada
			// spref --> codigo petrobras
			// quantidade --> itens
			
			if($ext=='csv' || $ext=='txt')
			{
				$file = fopen($arquivo_temp,"r");
				
				while(!feof($file))
				{
					
					$cont = fgets($file);
					
					if(strlen($cont)>0)
					{
						$str_linha = explode(";",$cont);
						
						//MATCODE
						$str_matcod[$str_linha[0]] = $str_linha[0];
						
						//SPREF
						$str_spref[$str_linha[0]] = $str_linha[1];
						
						//QUANTIDADE
						$str_qtd[$str_linha[0]] += $str_linha[2];
					}				
					
				}			
	
				fclose($file);			
				
				foreach($str_matcod as $chave=>$valor)
				{
					//$mat .=  $valor;
					//$mat .=  $str_spref[$valor]." -> ";
					//$mat .=  $str_qtd[$valor]."\n";
					
					$matcod = explode("-",$valor);
					
					//Busca os Kits no banco de especificações
					$sql = "SELECT *, SUM(quantidade*'".$str_qtd[$valor]."') AS total FROM  materiais_1.`especificacao de materiais`, materiais_1.`composicao_kit` ";
					$sql .= "WHERE `especificacao de materiais`.cd_descricao_ref = '".$matcod[0]."' ";
					$sql .= "AND `especificacao de materiais`.cd_descricao = '".$matcod[1]."' ";
					$sql .= "AND `especificacao de materiais`.cd_material = '".$matcod[2]."' ";
					$sql .= "AND `especificacao de materiais`.cd_descricao_ref = `composicao_kit`.cd_descricao_ref ";
					$sql .= "AND `especificacao de materiais`.cd_descricao = `composicao_kit`.cd_descricao ";
					$sql .= "AND `especificacao de materiais`.cd_material = `composicao_kit`.cd_material ";
					$sql .= "GROUP BY `especificacao de materiais`.cd_descricao_ref, `especificacao de materiais`.cd_descricao, `especificacao de materiais`.cd_material ";
					
					$reg = $db->select($sql,'MYSQL');

					while($cont = mysqli_fetch_assoc($reg))
					{
						$sql = "INSERT INTO materiais_1.`lista de materiais para compra` (nr_docum, nr_lista, nr_rev, nr_area, ds_obs, cliente, cod_cliente, cd_descricao_ref, cd_descricao, cd_material, nr_qtde) VALUES(";
						$sql .= "'" . $dados_form["nr_cliente"] . "', ";
						$sql .= "'" . $dados_form["nr_dvm"] . "', ";
						$sql .= "'" . $dados_form["versao_documento"] . "', ";
						$sql .= "'" . $dados_form["area"] . "', ";
						$sql .= "'" . $dados_form["programa"] . "', ";
						$sql .= "'" . $dados_form["cliente"] . "', ";
						$sql .= "'" . $str_spref[$valor] . "', ";
						$sql .= "'" . $cont["comp_cd_descricao_ref"] . "', ";
						$sql .= "'" . $cont["comp_cd_descricao"] . "', ";
						$sql .= "'" . $cont["comp_cd_material"] . "', ";
						$sql .= "'" . $cont["total"] . "') ";
	
						$db->insert($sql,'MYSQL');
					
					}

					$sql = "INSERT INTO materiais_1.`lista de materiais para compra` (nr_docum, nr_lista, nr_rev, nr_area, ds_obs, cliente, cod_cliente, cd_descricao_ref, cd_descricao, cd_material, nr_qtde) VALUES(";
					$sql .= "'" . $dados_form["nr_cliente"] . "', ";
					$sql .= "'" . $dados_form["nr_dvm"] . "', ";
					$sql .= "'" . $dados_form["versao_documento"] . "', ";
					$sql .= "'" . $dados_form["area"] . "', ";
					$sql .= "'" . $dados_form["programa"] . "', ";
					$sql .= "'" . $dados_form["cliente"] . "', ";
					$sql .= "'" . $str_spref[$valor] . "', ";
					$sql .= "'" . $matcod[0] . "', ";
					$sql .= "'" . $matcod[1] . "', ";
					$sql .= "'" . $matcod[2] . "', ";
					$sql .= "'" . $str_qtd[$valor] . "') ";

					$db->insert($sql,'MYSQL');
					
				}
			}
			else
			{
				$resposta -> addAlert("Arquivo com extensão errada. Somente arquivos *.csv ou *.txt");
			}
		}
	
	}	
	
	return $resposta;
}

$xajax->registerFunction("importa");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

?>
<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script language="javascript">
function grid()
{	
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,500);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");	
}
</script>

<?php
$smarty->assign("nome_formulario","IMPORTA CSV(PDMS) - V1");

$smarty->assign("classe","setor_proj");

$smarty->display('importa_csv.tpl');
?>