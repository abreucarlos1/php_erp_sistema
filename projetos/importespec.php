<?
	
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$count = 1;

$sql = "SELECT * FROM Projetos.especpadraodet ";
$registro = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
while($regs = mysql_fetch_array($registro))
{
	$sql1 = "SELECT * FROM Projetos.especificacao_padrao, Projetos.componentes, Projetos.funcao, Projetos.tipo ";
	$sql1 .= "WHERE especificacao_padrao.id_componente = componentes.id_componente ";
	$sql1 .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
	$sql1 .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
	$sql1 .= "AND componentes.ds_componente = '" . $regs["componente"] . "' ";
	$sql1 .= "AND funcao.ds_funcao = '" . $regs["funcao"] . "' ";
	$sql1 .= "AND tipo.ds_tipo = '" . $regs["tipo"] . "' ";
	$registro1 = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados1" . $sql1);
	$regs1 = mysql_fetch_array($registro1);
	
	$isql = "INSERT INTO Projetos.especificacao_padrao_detalhes ";
	$isql .= "(id_especificacao_padrao, sequencia, id_topico, id_variavel) ";
	$isql .= "VALUES ('" .$regs1["id_especificacao_padrao"] . "', ";
	$isql .= "'" .$regs["sequencia"] . "', ";
	$isql .= "'" .$regs["topico"] . "', ";
	$isql .= "'" .$regs["variavel"] . "') ";
	$registro2 = mysql_query($isql,$db->conexao) or die("Não foi possível a insercao dos dados1" . $isql);

	$count++;	
	
}

echo $count;

$db->fecha_db();


?>
