<?php
/*
		Formulário de Locais equipamentos	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/locais_equip.php
		
		data de criação: 12/05/2006
		
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
$sql1 .= "WHERE setor = 'MECÂNICA' ";

$regis = $db->select($sql1,'MYSQL');

$disciplina = mysqli_fetch_array($regis);

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND ds_descricao = '" . maiusculas($_POST["ds_descricao"]) . "' ";
	$sql .= "AND cd_localizacao = '" . $_POST["cd_localizacao"] . "' ";
	$sql .= "AND nr_elevacao = '" . $_POST["nr_elevacao"] . "' ";
	$sql .= "AND nr_eixox = '" . $_POST["nr_eixox"] . "' ";
	$sql .= "AND nr_eixoy = '" . $_POST["nr_eixoy"] . "' ";
	$sql .= "AND nr_capacidade = '" . $_POST["nr_capacidade"] . "' ";
	$sql .= "AND nr_pressao = '" . $_POST["nr_pressao"] . "' ";
	$sql .= "AND nr_temperatura = '" . $_POST["nr_temperatura"] . "' ";
	$sql .= "AND id_classepressao = '" . $_POST["id_classepressao"] . "' ";
	$sql .= "AND nr_altura = '" . $_POST["nr_altura"] . "' ";
	$sql .= "AND ds_npsh = '" . $_POST["ds_npsh"] . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local já cadastrado no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		$sql = "UPDATE Projetos.locais SET ";
		$sql .= "id_area = '" . $_POST["id_area"] . "', ";
		$sql .= "id_equipamento = '" . $_POST["id_equipamento"] . "', ";
		$sql .= "id_disciplina = '" . $disciplina["id_setor"] . "', ";
		$sql .= "nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "', ";
		$sql .= "ds_descricao = '" . maiusculas($_POST["ds_descricao"]) . "', ";
		$sql .= "cd_localizacao = '" . $_POST["cd_localizacao"] . "', ";
		$sql .= "nr_elevacao = '" . $_POST["nr_elevacao"] . "', ";
		$sql .= "nr_eixox = '" . $_POST["nr_eixox"] . "', ";
		$sql .= "nr_eixoy = '" . $_POST["nr_eixoy"] . "', ";
		$sql .= "nr_capacidade = '" . $_POST["nr_capacidade"] . "', ";
		$sql .= "nr_pressao = '" . $_POST["nr_pressao"] . "', ";
		$sql .= "nr_temperatura = '" . $_POST["nr_temperatura"] . "', ";
		$sql .= "id_classepressao = '" . $_POST["id_classepressao"] . "', ";
		$sql .= "nr_altura = '" . $_POST["nr_altura"] . "', ";
		$sql .= "ds_npsh = '" . $_POST["ds_npsh"] . "', ";
		$sql .= "nr_revisao = '" . $_POST["nr_revisao"] . "' ";		
		$sql .= "WHERE id_local = '" . $_POST["id_local"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
			
		?>
		<script>
			alert('local atualizado com sucesso.');
		</script>
		<?php
	}

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND id_equipamento = '" . $_POST["id_equipamento"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND ds_descricao = '" . maiusculas($_POST["ds_descricao"]) . "' ";
	$sql .= "AND cd_localizacao = '" . $_POST["cd_localizacao"] . "' ";
	$sql .= "AND nr_elevacao = '" . $_POST["nr_elevacao"] . "' ";
	$sql .= "AND nr_eixox = '" . $_POST["nr_eixox"] . "' ";
	$sql .= "AND nr_eixoy = '" . $_POST["nr_eixoy"] . "' ";
	$sql .= "AND nr_capacidade = '" . $_POST["nr_capacidade"] . "' ";
	$sql .= "AND nr_pressao = '" . $_POST["nr_pressao"] . "' ";
	$sql .= "AND nr_temperatura = '" . $_POST["nr_temperatura"] . "' ";
	$sql .= "AND id_classepressao = '" . $_POST["id_classepressao"] . "' ";
	$sql .= "AND nr_altura = '" . $_POST["nr_altura"] . "' ";
	$sql .= "AND ds_npsh = '" . $_POST["ds_npsh"] . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local já cadastrado no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.locais ";
		$isql .= "(id_area, id_disciplina, id_equipamento, nr_sequencia, ds_descricao, nr_elevacao, ";
		$isql .= "nr_eixox, nr_eixoy, cd_localizacao, nr_capacidade, nr_pressao, ";
		$isql .= "id_classepressao, nr_temperatura, nr_altura, ds_npsh, nr_revisao) VALUES (";
		$isql .= "'" . $_POST["id_area"] . "', ";
		$isql .= "'" . $disciplina["id_setor"] . "', ";
		$isql .= "'" . $_POST["id_equipamento"] . "', ";
		$isql .= "'" . maiusculas($_POST["nr_sequencia"]) . "', ";
		$isql .= "'" . maiusculas($_POST["ds_descricao"]) . "', ";
		$isql .= "'" . $_POST["nr_elevacao"] . "', ";
		$isql .= "'" . $_POST["nr_eixox"] . "', ";
		$isql .= "'" . $_POST["nr_eixoy"] . "', ";
		$isql .= "'" . $_POST["cd_localizacao"] . "', ";
		$isql .= "'" . $_POST["nr_capacidade"] . "', ";
		$isql .= "'" . $_POST["nr_pressao"] . "', ";
		$isql .= "'" . $_POST["id_classepressao"] . "', ";
		$isql .= "'" . $_POST["nr_temperatura"] . "', ";
		$isql .= "'" . $_POST["nr_altura"] . "', ";
		$isql .= "'" . $_POST["ds_npsh"] . "', ";	
		$isql .= "'" . $_POST["nr_revisao"] . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
		
		?>
		<script>
			alert('local inserido com sucesso.');
		</script>
		<?php
	}

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.locais WHERE id_local = '".$_GET["id_local"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('local excluído com sucesso.');
	</script>
	<?php
}

?>

<html>
<head>
<title>: : . LOCAIS MEC.  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_local, nrcd_localtrecho)
{
	if(confirm('Tem certeza que deseja excluir o local '+nrcd_localtrecho+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_local='+id_local+'';
	}
}

function editar(id_local)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_local='+id_local+'';
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


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="id_local" id="id_local" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.locais WHERE id_local= '" . $_GET["id_local"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$locais = mysqli_fetch_array($registro); 
			 
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
                      <td width="10%" class="label1">ÁREA</td>
                      <td width="1%"> </td>
                      <td width="12%"><span class="label1">EQUIPAMENTOS</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">SEQUÊNCIA</span></td>
                      <td width="1%"> </td>
                      <td width="13%">DESCRIÇÃO</td>
                      <td width="1%"> </td>
                      <td width="46%">COD. LOCALIZAÇÃO </td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onChange="javascript:document.forms[0].nr_local.focus()">
                        <option value="">SELECIONE</option>
                        <?php
						
			
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_area"] ?>" <?php if($locais["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
							</option>
							<?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento">
                        <option value="">SELECIONE</option>
                        <?php
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						$sql_equipamentos .= "WHERE id_disciplina='" . $disciplina["id_setor"] . "' ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($locais["id_equipamentos"]==$cont_equipamentos["id_equipamento"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="30" maxlength="20" value="<?= $locais["nr_sequencia"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_descricao" type="text" class="txt_box" id="ds_descricao" size="30" maxlength="20" value="<?= $locais["ds_descricao"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_localizacao" type="text" class="txt_box" id="cd_localizacao" size="30" maxlength="20" value="<?= $locais["cd_localizacao"] ?>">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%"><span class="label1">ELEVAÇÃO</span></td>
                      <td width="1%"> </td>
                      <td width="7%"><span class="label1">EIXO (X)</span></td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">CAPACIDADE</span></td>
                      <td width="1%"> </td>
                      <td width="49%"><span class="label1">ALTURA</span></td>
                      <td width="4%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= formatavalor($locais["nr_elevacao"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixox" type="text" class="txt_box" id="nr_eixox" value="<?= $locais["nr_eixox"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixoy" type="text" class="txt_box" id="nr_eixoy" value="<?= $locais["nr_eixoy"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_capacidade" type="text" class="txt_box" id="nr_capacidade" value="<?= formatavalor($locais["nr_capacidade"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_altura" type="text" class="txt_box" id="nr_altura" value="<?= formatavalor($locais["nr_altura"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="15%">TEMPERATURA</td>
                      <td width="3%"> </td>
                      <td width="11%">PRESSÃO</td>
                      <td width="3%"> </td>
                      <td width="19%"><span class="label1">CLASSE PRESSÃO </span></td>
                      <td width="2%"> </td>
                      <td width="8%"><span class="label1">NPSH</span></td>
                      <td width="3%"> </td>
                      <td width="33%">REVISÃO</td>
                      <td width="3%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_temperatura" type="text" class="txt_box" id="nr_temperatura" value="<?= formatavalor($locais["nr_temperatura"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_pressao" type="text" class="txt_box" id="nr_pressao" value="<?= formatavalor($locais["nr_pressao"]) ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><select name="id_classepressao" class="txt_box" id="id_classepressao">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.classe_pressao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_classepressao"] ?>"<?php if($locais["id_classepressao"]==$cont["id_classepressao"]){ echo 'selected';} ?>>
							<?= $cont["cd_classepressao"] . " - " . $cont["ds_classepressao"] ?>
							</option>
							<?php		
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_npsh" type="text" class="txt_box" id="ds_npsh" value="<?= $locais["ds_npsh"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $locais["nr_revisao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_local" type="hidden" id="id_local" value="<?= $locais["id_local"] ?>">
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
                      <td width="10%" class="label1">ÁREA</td>
                      <td width="1%"> </td>
                      <td width="12%">EQUIPAMENTO</td>
                      <td width="1%"> </td>
                      <td width="13%">SEQUÊNCIA</td>
                      <td width="1%"> </td>
                      <td width="13%">DESCRIÇÃO</td>
                      <td width="1%"> </td>
                      <td width="46%">COD. LOCALIZAÇÃO </td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_area"] ?>" <?php if($_POST["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
                          <?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_equipamento" class="txt_box" id="id_equipamento">
                          <option value="">SELECIONE</option>
                          <?php
						
						$sql_equipamentos = "SELECT * FROM Projetos.equipamentos ";
						$sql_equipamentos .= "WHERE id_disciplina='" . $disciplina["id_setor"] . "' ";
						
						$reg_equipamentos = $db->select($sql_equipamentos,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                          <option value="<?= $cont_equipamentos["id_equipamentos"] ?>" <?php if($_POST["id_equipamento"]==$cont_equipamentos["id_equipamento"]) { echo "selected"; } ?>>
                          <?= $cont_equipamentos["cd_local"] . " - " . $cont_equipamentos["ds_equipamento"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="30" maxlength="20" value="<?= $_POST["nr_sequencia"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_descricao" type="text" class="txt_box" id="ds_descricao" size="30" maxlength="20" value="<?= $_POST["ds_descricao"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_localizacao" type="text" class="txt_box" id="cd_localizacao" size="30" maxlength="20" value="<?= $_POST["cd_localizacao"] ?>">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%"><span class="label1">ELEVAÇÃO</span></td>
                      <td width="1%"> </td>
                      <td width="7%"><span class="label1">EIXO (X)</span></td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">COLUNA (Y)</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">CAPACIDADE</span></td>
                      <td width="1%"> </td>
                      <td width="49%"><span class="label1">ALTURA</span></td>
                      <td width="4%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= $_POST["nr_elevacao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixox" type="text" class="txt_box" id="nr_eixox" value="<?= $_POST["nr_eixox"] ?>" size="15" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_eixoy" type="text" class="txt_box" id="nr_eixoy" value="<?= $_POST["nr_eixoy"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_capacidade" type="text" class="txt_box" id="nr_capacidade" value="<?= $_POST["nr_capacidade"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_altura" type="text" class="txt_box" id="nr_altura" value="<?= $_POST["nr_altura"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="15%">TEMPERATURA</td>
                      <td width="3%"> </td>
                      <td width="11%">PRESSÃO</td>
                      <td width="3%"> </td>
                      <td width="19%">CLASSE PRESSÃO </td>
                      <td width="2%"> </td>
                      <td width="8%">NPSH</td>
                      <td width="3%"> </td>
                      <td width="33%">REVISÃO</td>
                      <td width="3%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_temperatura" type="text" class="txt_box" id="nr_temperatura" value="<?= $_POST["nr_temperatura"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_pressao" type="text" class="txt_box" id="nr_pressao" value="<?= $_POST["nr_pressao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><select name="id_classepressao" class="txt_box" id="id_classepressao">
                          <option value="">SELECIONE</option>
                          <?php

						$sql = "SELECT * FROM Projetos.classe_pressao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_classepressao"] ?>" <?php if($_POST["id_classepressao"]==$cont["id_classepressao"]) { echo "selected"; } ?>>
                          <?= $cont["cd_classepressao"] . " - " . $cont["ds_classepressao"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_npsh" type="text" class="txt_box" id="ds_npsh" value="<?= $_POST["ds_npsh"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="30" maxlength="20">
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
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Equipamentos" type="button" class="btn" id="Equipamentos" value="COMPONENTES" onclick="javascript:location.href='componentes.php';"></td>
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
				  <td width="22%">ÁREA</td>
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
				  <td width="22%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_local','<?= $ordem ?>')">EQUIPAMENTO</a></td>
				  <td width="16%">SEQUÊNCIA</td>
				  <td width="28%">DESCRIÇÃO</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php

					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.equipamentos ";
					$sql .= "WHERE locais.id_equipamento = equipamentos.id_equipamentos ";
					$sql .= "AND equipamentos.id_disciplina = '" .$disciplina["id_setor"]. "' ";
					$sql .= "AND locais.id_area = area.id_area ";
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
						  <td width="22%"><div align="center">
						    <?= $locais["nr_area"] . " - " . $locais["ds_area"] ?>
					      </div></td>
						  <td width="22%"><div align="center"><?= $locais["cd_local"] . " - " . $locais["ds_equipamento"] ?></div></td>
						  <td width="17%"><div align="center"><?= $locais["nr_sequencia"] ?></div></td>
						  <td width="29%"><div align="center"><?= $locais["ds_descricao"] ?></div></td>
						  <td width="5%"><div align="center"><a href="javascript:editar('<?= $locais["id_local"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="5%"><div align="center"><a href="javascript:excluir('<?= $locais["id_local"] ?>','<?= $locais["nr_sequencia"] . " " . $locais["ds_descricao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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