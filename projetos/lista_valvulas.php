<?php
/*
		Formulário de lista válvulas	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/lista_valvulas.php
		
		data de criação: 05/06/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016	
*/
	
//Obtém os dados do usuário
session_start();

if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usuário não logado! Redireciona para a página de login
	header("Location: ../index.php");
	exit;
}
		
	
//include ("../includes/layout.php");
include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;

$sql1 = "SELECT * FROM ".DATABASE.".setores ";
$sql1 .= "WHERE setor = 'TUBULAÇÃO' ";

$regis = $db->select($sql1,'MYSQL');

$disciplina = mysqli_fetch_array($regis);

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	$sql = "SELECT * FROM Projetos.lista_valvulas WHERE ";
	$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
	$sql .= "AND id_valvula = '" . $_POST["id_valvula"] . "' ";
	$sql .= "AND id_linha = '" . $_POST["id_linha"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_acionamento = '" . $_POST["id_acionamento"] . "' ";
	$sql .= "AND id_conexao = '" . $_POST["id_conexao"] . "' ";
	$sql .= "AND id_classepressao = '" . $_POST["id_classepressao"] . "' ";
	$sql .= "AND nr_sequencia = '" . $_POST["nr_sequencia"] . "' ";
	$sql .= "AND nr_diametro = '" . $_POST["nr_diametro"] . "' ";
	$sql .= "AND id_norma = '" . $_POST["id_norma"] . "' ";
	$sql .= "AND ds_tag_cliente = '" . $_POST["ds_tag_cliente"] . "' ";
	$sql .= "AND ds_tie_in = '" . maiusculas($_POST["ds_tie_in"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Válvula já cadastrada no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		$sql = "UPDATE Projetos.lista_valvulas SET ";
		$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "', ";
		$sql .= "id_valvula = '" . $_POST["id_valvula"] . "', ";
		$sql .= "id_linha = '" . $_POST["id_linha"] . "', ";
		$sql .= "id_equipamento = '" . $_POST["id_equipamento"] . "', ";
		$sql .= "id_acionamento = '" . $_POST["id_acionamento"] . "', ";
		$sql .= "id_conexao = '" . $_POST["id_conexao"] . "', ";
		$sql .= "id_classepressao = '" . $_POST["id_classepressao"] . "', ";
		$sql .= "nr_sequencia = '" . $_POST["nr_sequencia"] . "', ";
		$sql .= "nr_diametro = '" . $_POST["nr_diametro"] . "', ";
		$sql .= "id_norma = '" . $_POST["id_norma"] . "', ";
		$sql .= "ds_tag_cliente = '" . $_POST["ds_tag_cliente"] . "', ";
		$sql .= "ds_tie_in = '" . maiusculas($_POST["ds_tie_in"]) . "', ";
		$sql .= "nr_revisao = '" . $_POST["nr_revisao"] . "' ";
		
		$sql .= "WHERE id_lista_valvula = '" . $_POST["id_lista_valvula"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
			
		?>
		<script>
			alert('Válvula atualizada com sucesso.');
		</script>
		<?php
	}
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.lista_valvulas WHERE ";
	$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
	$sql .= "AND id_valvula = '" . $_POST["id_valvula"] . "' ";
	$sql .= "AND id_linha = '" . $_POST["id_linha"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_acionamento = '" . $_POST["id_acionamento"] . "' ";
	$sql .= "AND id_conexao = '" . $_POST["id_conexao"] . "' ";
	$sql .= "AND id_classepressao = '" . $_POST["id_classepressao"] . "' ";
	$sql .= "AND nr_sequencia = '" . $_POST["nr_sequencia"] . "' ";
	$sql .= "AND nr_diametro = '" . $_POST["nr_diametro"] . "' ";
	$sql .= "AND id_norma = '" . $_POST["id_norma"] . "' ";
	$sql .= "AND ds_tag_cliente = '" . $_POST["ds_tag_cliente"] . "' ";
	$sql .= "AND ds_tie_in = '" . maiusculas($_POST["ds_tie_in"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Válvula já cadastrada no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.lista_valvulas ";
		$isql .= "(id_subsistema, id_valvula, id_linha, id_equipamento, id_acionamento, ";
		$isql .= "id_conexao, id_classepressao, nr_sequencia, nr_diametro, ";
		$isql .= "id_norma, ds_tag_cliente, ds_tie_in, nr_revisao) VALUES (";
		$isql .= "'" . $_POST["id_subsistema"] . "', ";
		$isql .= "'" . $_POST["id_valvula"] . "', ";
		$isql .= "'" . $_POST["id_linha"] . "', ";
		$isql .= "'" . $_POST["id_equipamento"] . "', ";
		$isql .= "'" . $_POST["id_acionamento"] . "', ";
		$isql .= "'" . $_POST["id_conexao"] . "', ";
		$isql .= "'" . $_POST["id_classepressao"] . "', ";
		$isql .= "'" . $_POST["nr_sequencia"] . "', ";
		$isql .= "'" . $_POST["nr_diametro"] . "', ";
		$isql .= "'" . $_POST["id_norma"] . "', ";
		$isql .= "'" . $_POST["ds_tag_cliente"] . "', ";
		$isql .= "'" . $_POST["ds_tie_in"] . "', ";
		$isql .= "'" . $_POST["nr_revisao"] . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
		
		?>
		<script>
			alert('Válvula inserida com sucesso.');
		</script>
		<?php
	}

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.lista_valvulas WHERE id_lista_valvula = '".$_GET["id_lista_valvula"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Válvula excluída com sucesso.');
	</script>
	<?php
}

?>

<html>
<head>
<title>: : . LISTA VÁLVULAS .  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_lista_valvula, valvula)
{
	if(confirm('Tem certeza que deseja excluir a válvula '+valvula+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_lista_valvula='+id_lista_valvula+'';
	}
}

function editar(id_lista_valvula)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_lista_valvula='+id_lista_valvula+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Função para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}


//Função para preenchimento dos comboboxes dinâmicos.
function preenchecombo(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?php

$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.subsistema, Projetos.fluidos, Projetos.materiais, Projetos.locais ";
$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos)  ";
$sql .= "WHERE setores.id_setor = locais.id_disciplina ";
$sql .= "AND setores.setor = 'TUBULAÇÃO' ";
$sql .= "AND locais.id_area = area.id_area ";
$sql .= "AND locais.id_fluido = fluidos.id_fluido ";
$sql .= "AND locais.id_material = materiais.id_material ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND area.id_os = '" .$_SESSION["id_os"] . "' ";
$sql .= "ORDER BY fluidos.cd_fluido, locais.nr_sequencia, locais.nr_diametro  ";

$reg = $db->select($sql,'MYSQL');


	while ($cont = mysqli_fetch_array($reg))
	{
		//$nome = str_replace("\r\n","",$cont["nome_contato"]);
		?>
		if(itembox.value=='<?= $cont["id_subsistema"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["cd_fluido"]."-".$cont["nr_diametro"]."-".$cont["cd_material"]."-".$cont["nr_sequencia"] ?>','<?= $cont["id_local"] ?>');
		}
		<?php 
	} 
	?>
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_local" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
	  <tr>
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.lista_valvulas WHERE id_lista_valvula= '" . $_GET["id_lista_valvula"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$lista_valvulas = mysqli_fetch_array($registro); 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%" class="label1">SUBSISTEMA</td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">LINHA</span></td>
                      <td width="1%"> </td>
                      <td width="11%">EQUIPAMENTO</td>
                      <td width="1%"> </td>
                      <td width="17%"><span class="label1">TIPO DE VÁLVULA</span></td>
                      <td width="1%"> </td>
                      <td width="9%"> </td>
                      <td width="1%"> </td>
                      <td width="37%"> </td>
                      <td width="1%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_subsistema" class="txt_box" id="id_subsistema" onclick="preenchecombo(this.form.id_linha, this)" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql .= "AND subsistema.id_area = area.id_area ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_subsistema"] ?>" <?php if($lista_valvulas["id_subsistema"]==$cont["id_subsistema"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " " . $cont["nr_subsistema"]. " - " . $cont["subsistema"]  ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_linha" class="txt_box" id="id_linha" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php

						$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.subsistema, Projetos.fluidos, Projetos.materiais, Projetos.locais ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos)  ";
						$sql .= "WHERE setores.id_setor = locais.id_disciplina ";
						$sql .= "AND setores.setor = 'TUBULAÇÃO' ";
						$sql .= "AND locais.id_area = area.id_area ";
						$sql .= "AND locais.id_fluido = fluidos.id_fluido ";
						$sql .= "AND locais.id_material = materiais.id_material ";
						$sql .= "AND subsistema.id_area = area.id_area ";
						$sql .= "AND subsistema.id_subsistema = '".$lista_valvulas["id_subsistema"]."' ";
						$sql .= "AND area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY fluidos.cd_fluido, locais.nr_sequencia, locais.nr_diametro  ";
						
						$reg_equipamentos = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont["id_local"] ?>" <?php if($lista_valvulas["id_linha"]==$cont["id_local"]) { echo "selected"; } ?>>
                        <?= $cont["cd_fluido"]."-".$cont["nr_diametro"]."-".$cont["cd_material"]."-".$cont["nr_sequencia"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos, ".DATABASE.".setores ";
						$sql_equipamentos .= "WHERE equipamentos.id_disciplina = setores.Codsetor ";
						$sql_equipamentos .= "AND setores.setor = 'TUBULAÇÃO' ";
						$sql_equipamentos .= "ORDER BY equipamentos.ds_equipamento ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($lista_valvulas["id_equipamentos"]==$cont_equipamentos["id_equipamento"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_valvula" class="txt_box" id="id_valvula" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.valvulas ";
						$sql_equipamentos .= "ORDER BY cd_valvula ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_valvula"] ?>" <?php if($lista_valvulas["id_valvula"]==$cont_equipamentos["id_valvula"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_valvula"] . " - " . $cont_equipamentos["ds_valvula"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="9%">ACIONAMENTO</td>
                      <td width="1%"> </td>
                      <td width="9%">CONEXÃO</td>
                      <td width="1%"> </td>
                      <td width="10%">PRESSÃO</td>
                      <td width="1%"> </td>
                      <td width="9%">SEQUÊNCIA</td>
                      <td width="1%"> </td>
                      <td width="56%">DIÂMETRO</td>
                      <td width="3%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_acionamento" class="txt_box" id="id_equipamentos" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.acionamentos ";
						$sql_equipamentos .= "ORDER BY cd_acionamento ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_acionamento"] ?>" <?php if($lista_valvulas["id_acionamento"]==$cont_equipamentos["id_acionamento"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_acionamento"] . " - " . $cont_equipamentos["ds_acionamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_conexao" class="txt_box" id="id_conexao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						$sql_equipamentos = "SELECT * FROM Projetos.conexoes ";
						$sql_equipamentos .= "ORDER BY cd_conexao ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_conexao"] ?>" <?php if($lista_valvulas["id_conexao"]==$cont_equipamentos["id_conexao"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_conexao"] . " - " . $cont_equipamentos["ds_conexao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_classepressao" class="txt_box" id="id_classepressao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.classe_pressao ";
						$sql_equipamentos .= "ORDER BY cd_classepressao ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_classepressao"] ?>" <?php if($lista_valvulas["id_classepressao"]==$cont_equipamentos["id_classepressao"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_classepressao"] . " - " . $cont_equipamentos["ds_classepressao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" value="<?= $lista_valvulas["nr_sequencia"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_diametro" type="text" class="txt_box" id="nr_diametro" value="<?= str_replace('"',"&quot;",$lista_valvulas["nr_diametro"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%">NORMA</td>
                      <td width="1%"> </td>
                      <td width="15%">CÓDIGO CLIENTE </td>
                      <td width="1%"> </td>
                      <td width="15%">TIE-IN</td>
                      <td width="1%"> </td>
                      <td width="55%"> </td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_norma" class="txt_box" id="id_norma" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						$sql_equipamentos = "SELECT * FROM Projetos.normas ";
						$sql_equipamentos .= "ORDER BY ds_norma ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_norma"] ?>" <?php if($lista_valvulas["id_norma"]==$cont_equipamentos["id_norma"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["ds_norma"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tag_cliente" type="text" class="txt_box" id="ds_tag_cliente" value="<?= $lista_valvulas["ds_tag_cliente"] ?>" size="35">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tie_in" type="text" class="txt_box" id="ds_tie_in" value="<?= $lista_valvulas["ds_tie_in"] ?>" size="35">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $lista_valvulas["nr_revisao"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_lista_valvula" type="hidden" id="id_lista_valvula" value="<?= $lista_valvulas["id_lista_valvula"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?php
			
			 }
			else
			{
			  ?>
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%" class="label1">SUBSISTEMA</td>
                      <td width="1%"> </td>
                      <td width="10%">LINHA</td>
                      <td width="1%"> </td>
                      <td width="11%">EQUIPAMENTO</td>
                      <td width="1%"> </td>
                      <td width="17%">TIPO DE VÁLVULA </td>
                      <td width="1%"> </td>
                      <td width="9%"> </td>
                      <td width="11%"> </td>
                      <td width="20%"> </td>
                      <td width="8%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_subsistema" class="txt_box" id="id_subsistema" onclick="preenchecombo(this.form.id_linha, this)" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						
						$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql .= "AND subsistema.id_area = area.id_area ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_subsistema"] ?>" <?php if($_POST["id_subsistema"]==$cont["id_subsistema"]) { echo "selected"; } ?>>
                          <?= $cont["nr_area"] . " " . $cont["nr_subsistema"]. " - " . $cont["subsistema"]  ?>
                          </option>
                          <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_linha" class="txt_box" id="id_linha" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
						  <?php
							$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.subsistema, Projetos.fluidos, Projetos.materiais, Projetos.locais ";
							$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos)  ";
							$sql .= "WHERE setores.id_setor = locais.id_disciplina ";
							$sql .= "AND setores.setor = 'TUBULAÇÃO' ";
							$sql .= "AND locais.id_area = area.id_area ";
							$sql .= "AND locais.id_fluido = fluidos.id_fluido ";
							$sql .= "AND locais.id_material = materiais.id_material ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
							$sql .= "AND area.id_os = '" .$_SESSION["id_os"] . "' ";
							$sql .= "ORDER BY fluidos.cd_fluido, locais.nr_sequencia, locais.nr_diametro  ";
							
							$reg_equipamentos = $db->select($sql,'MYSQL');
							
							while($cont = mysqli_fetch_array($reg_equipamentos))
							{
								?>
							<option value="<?= $cont["id_local"] ?>" <?php if($lista_valvulas["id_linha"]==$cont["id_local"]) { echo "selected"; } ?>>
							<?= $cont["cd_fluido"]."-".$cont["nr_diametro"]."-".$cont["cd_material"]."-".$cont["nr_sequencia"] ?>
							</option>
							<?php
								
							}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php

						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos, ".DATABASE.".setores ";
						$sql_equipamentos .= "WHERE equipamentos.id_disciplina = setores.Codsetor ";
						$sql_equipamentos .= "AND setores.setor = 'TUBULAÇÃO' ";
						$sql_equipamentos .= "ORDER BY equipamentos.ds_equipamento ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($_POST["id_equipamento"]==$cont_equipamentos["id_equipamentos"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_valvula" class="txt_box" id="id_valvula" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.valvulas ";
						$sql_equipamentos .= "ORDER BY cd_valvula ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_valvula"] ?>" <?php if($_POST["id_valvula"]==$cont_equipamentos["id_valvula"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_valvula"] . " - " . $cont_equipamentos["ds_valvula"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="11%">ACIONAMENTO</td>
                      <td width="1%"> </td>
                      <td width="10%">CONEXÃO</td>
                      <td width="1%"> </td>
                      <td width="10%">PRESSÃO</td>
                      <td width="1%"> </td>
                      <td width="9%">SEQUÊNCIA</td>
                      <td width="1%"> </td>
                      <td width="48%">DIÂMETRO</td>
                      <td width="3%"> </td>
                      <td width="5%"> </td>
                      </tr>
                    <tr>
                      <td><select name="id_acionamento" class="txt_box" id="id_acionamento" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.acionamentos ";
						$sql_equipamentos .= "ORDER BY cd_acionamento ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_acionamento"] ?>" <?php if($_POST["id_acionamento"]==$cont_equipamentos["id_acionamento"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_acionamento"] . " - " . $cont_equipamentos["ds_acionamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_conexao" class="txt_box" id="id_conexao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.conexoes ";
						$sql_equipamentos .= "ORDER BY cd_conexao ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_conexao"] ?>" <?php if($_POST["id_conexao"]==$cont_equipamentos["id_conexao"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_conexao"] . " - " . $cont_equipamentos["ds_conexao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_classepressao" class="txt_box" id="id_classepressao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php

						$sql_equipamentos = "SELECT * FROM Projetos.classe_pressao ";
						$sql_equipamentos .= "ORDER BY cd_classepressao ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_classepressao"] ?>" <?php if($_POST["id_classepressao"]==$cont_equipamentos["id_classepressao"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_classepressao"] . " - " . $cont_equipamentos["ds_classepressao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" value="<?= $_POST["nr_sequencia"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_diametro" type="text" class="txt_box" id="nr_diametro" value="<?= str_replace('\"',"&quot;",$_POST["nr_diametro"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%">NORMA</td>
                      <td width="1%"> </td>
                      <td width="15%">cÓDIGO CLIENTE </td>
                      <td width="1%"> </td>
                      <td width="15%">TIE-IN</td>
                      <td width="1%"> </td>
                      <td width="55%">REVISÃO</td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_norma" class="txt_box" id="id_norma" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.normas ";
						$sql_equipamentos .= "ORDER BY ds_norma ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_norma"] ?>" <?php if($_POST["id_norma"]==$cont_equipamentos["id_norma"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["ds_norma"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tag_cliente" type="text" class="txt_box" id="ds_tag_cliente" value="<?= $_POST["ds_tag_cliente"] ?>" size="35">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_tie_in" type="text" class="txt_box" id="ds_tie_in" value="<?= $_POST["ds_tie_in"] ?>" size="35">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
			 <?php
			}
			?>
			
			
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="26%">SUBSISTEMA</td>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "nr_sequencia";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordenação
				  ?>
				  <td width="17%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_local','<?= $ordem ?>')">LINHA</a></td>
				  <td width="10%">TAG</td>
				  <td width="14%">TIPO</td>
				  <td width="10%">DIÂMETRO</td>
				  <td width="15%">CONEXÃO</td>
				  <td width="2%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="4%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php

					$sql = "SELECT *, locais.nr_sequencia AS seq_local, locais.nr_diametro AS dm_local FROM Projetos.area, Projetos.subsistema, Projetos.locais, Projetos.fluidos, Projetos.materiais, Projetos.conexoes, Projetos.lista_valvulas ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (lista_valvulas.id_equipamento = equipamentos.id_equipamentos) ";
					$sql .= "LEFT JOIN Projetos.valvulas ON (lista_valvulas.id_valvula = valvulas.id_valvula )";
					$sql .= "WHERE subsistema.id_subsistema = lista_valvulas.id_subsistema ";
					$sql .= "AND subsistema.id_area = area.id_area ";
					//$sql .= "AND lista_valvulas.id_equipamento = equipamentos.id_equipamentos ";
					//$sql .= "AND lista_valvulas.id_valvula = valvulas.id_valvula ";
					$sql .= "AND lista_valvulas.id_linha = locais.id_local ";
					$sql .= "AND locais.id_fluido = fluidos.id_fluido ";
					$sql .= "AND locais.id_material = materiais.id_material ";
					$sql .= "AND lista_valvulas.id_conexao = conexoes.id_conexao ";
					$sql .= "AND area.id_os= '" . $_SESSION["id_os"]. "' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($locais = mysqli_fetch_array($registro))
					{
					
						if($i%2)
						{
						// escuro
						$cor = "#F0F0F0";
						
						}
						else
						{
						//claro

						$cor = "#FFFFFF";
						}
						$i++;							

						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="26%"><div align="center">
						    <?= $locais["nr_subsistema"] . " - " . $locais["subsistema"] ?>
					      </div></td>
						  <td width="17%"><div align="center">
						    <?= $locais["cd_fluido"]."-".$locais["dm_local"]."-".$locais["cd_material"]."-".$locais["seq_local"] ?></div></td>
						  <td width="10%"><div align="center">
					      <?= $locais["cd_local"] . " - " . $locais["nr_sequencia"] ?></div></td>
						  <td width="14%"><div align="center">
						    <?php 
								if($locais["ds_valvula"])
								{
									echo $locais["ds_valvula"];
								}
								else
								{
									echo $locais["ds_equipamento"];
								}
							?>
						  </div></td>
						  <td width="10%"><div align="center">
                            <?= $locais["nr_diametro"] ?>
                          </div></td>
						  <td width="15%"><div align="center">
                            <?= $locais["ds_conexao"] ?>
                          </div></td>
						  <td width="2%"><div align="center"><a href="javascript:editar('<?= $locais["id_lista_valvula"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="6%"><div align="center"><a href="javascript:excluir('<?= $locais["id_lista_valvula"] ?>','<?= $locais["nr_sequencia"] . " " . $locais["ds_descricao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
					}
				
				?>
			  </table>
			</div>
		</td>
      </tr>
      
    </table>
	</td>
  </tr>
</table>
</form>
</center>
</body>
</html>