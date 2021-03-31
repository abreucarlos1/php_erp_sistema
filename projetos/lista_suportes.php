<?php
/*

		Formulário de lista válvulas	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/lista_suportes.php
		
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
	$sql = "SELECT * FROM Projetos.lista_suportes WHERE ";
	$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
	$sql .= "AND id_linha = '" . $_POST["id_linha"] . "' ";
	$sql .= "AND cd_posicao = '" . maiusculas($_POST["cd_posicao"]) . "' ";
	$sql .= "AND id_suporte = '" . $_POST["id_suporte"] . "' ";
	$sql .= "AND cd_tag = '" . maiusculas($_POST["cd_tag"]) . "' ";
	$sql .= "AND nr_elevacao = '" . maiusculas($_POST["nr_elevacao"]) . "' ";
	$sql .= "AND nr_quantidade = '" . $_POST["nr_quantidade"] . "' ";
	$sql .= "AND nr_h = '" . $_POST["nr_h"] . "' ";
	$sql .= "AND nr_l = '" . $_POST["nr_l"] . "' ";
	$sql .= "AND nr_a = '" . $_POST["nr_a"] . "' ";
	$sql .= "AND nr_b = '" . $_POST["nr_b"] . "' ";
	$sql .= "AND nr_c = '" . $_POST["nr_c"] . "' ";
	$sql .= "AND ds_planta = '" . maiusculas($_POST["ds_planta"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Suporte já cadastrado no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		$sql = "UPDATE Projetos.lista_suportes SET ";
		$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "', ";
		$sql .= "id_suporte = '" . $_POST["id_suporte"] . "', ";
		$sql .= "id_linha = '" . $_POST["id_linha"] . "', ";
		$sql .= "cd_posicao = '" . maiusculas($_POST["cd_posicao"]) . "', ";
		$sql .= "cd_tag = '" . maiusculas($_POST["cd_tag"]) . "', ";
		$sql .= "nr_elevacao = '" . maiusculas($_POST["nr_elevacao"]) . "', ";
		$sql .= "nr_quantidade = '" . $_POST["nr_quantidade"] . "', ";
		$sql .= "nr_h = '" . $_POST["nr_h"] . "', ";
		$sql .= "nr_l = '" . $_POST["nr_l"] . "', ";
		$sql .= "nr_a = '" . $_POST["nr_a"] . "', ";
		$sql .= "nr_b = '" . $_POST["nr_b"] . "', ";
		$sql .= "nr_c = '" . $_POST["nr_c"] . "', ";
		$sql .= "ds_planta = '" . maiusculas($_POST["ds_planta"]) . "', ";
		$sql .= "nr_revisao = '" . $_POST["nr_revisao"] . "' ";
		
		$sql .= "WHERE id_lista_suporte = '" . $_POST["id_lista_suporte"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
			
		?>
		<script>
			alert('Suporte atualizado com sucesso.');
		</script>
		<?php
	}

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.lista_suportes WHERE ";
	$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
	$sql .= "AND id_linha = '" . $_POST["id_linha"] . "' ";
	$sql .= "AND cd_posicao = '" . maiusculas($_POST["cd_posicao"]) . "' ";
	$sql .= "AND id_suporte = '" . $_POST["id_suporte"] . "' ";
	$sql .= "AND cd_tag = '" . maiusculas($_POST["cd_tag"]) . "' ";
	$sql .= "AND nr_elevacao = '" . maiusculas($_POST["nr_elevacao"]) . "' ";
	$sql .= "AND nr_quantidade = '" . $_POST["nr_quantidade"] . "' ";
	$sql .= "AND nr_h = '" . $_POST["nr_h"] . "' ";
	$sql .= "AND nr_l = '" . $_POST["nr_l"] . "' ";
	$sql .= "AND nr_a = '" . $_POST["nr_a"] . "' ";
	$sql .= "AND nr_b = '" . $_POST["nr_b"] . "' ";
	$sql .= "AND nr_c = '" . $_POST["nr_c"] . "' ";
	$sql .= "AND ds_planta = '" . maiusculas($_POST["ds_planta"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('Suporte já cadastrado no banco de dados.');
		</script>
		<?php	
	}
	else
	{
	
		//Cria sentença de Inclusão no bd
		$isql = "INSERT INTO Projetos.lista_suportes ";
		$isql .= "(id_subsistema, id_suporte, id_linha, cd_posicao, cd_tag, nr_elevacao, ";
		$isql .= "nr_quantidade, nr_h, nr_l, nr_a, ";
		$isql .= "nr_b, nr_c, ds_planta, nr_revisao) VALUES (";
		$isql .= "'" . $_POST["id_subsistema"] . "', ";
		$isql .= "'" . $_POST["id_suporte"] . "', ";
		$isql .= "'" . $_POST["id_linha"] . "', ";
		$isql .= "'" . maiusculas($_POST["cd_posicao"]) . "', ";
		$isql .= "'" . maiusculas($_POST["cd_tag"]) . "', ";
		$isql .= "'" . maiusculas($_POST["nr_elevacao"]) . "', ";
		$isql .= "'" . $_POST["nr_quantidade"] . "', ";
		$isql .= "'" . $_POST["nr_h"] . "', ";
		$isql .= "'" . $_POST["nr_l"] . "', ";
		$isql .= "'" . $_POST["nr_a"] . "', ";
		$isql .= "'" . $_POST["nr_b"] . "', ";
		$isql .= "'" . $_POST["nr_c"] . "', ";
		$isql .= "'" . $_POST["ds_planta"] . "', ";
		$isql .= "'" . $_POST["nr_revisao"] . "') ";
	
		$registros = $db->insert($isql,'MYSQL');
		
		?>
		<script>
			alert('Suporte inserido com sucesso.');
		</script>
		<?php
	}

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.lista_suportes WHERE id_lista_suporte = '".$_GET["id_lista_suporte"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Suporte excluído com sucesso.');
	</script>
	<?php
}

?>

<html>
<head>
<title>: : . LISTA SUPORTES .  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_lista_suporte, suporte)
{
	if(confirm('Tem certeza que deseja excluir o suporte '+suporte+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_lista_suporte='+id_lista_suporte+'';
	}
}

function editar(id_lista_suporte)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_lista_suporte='+id_lista_suporte+'';
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
				$sql = "SELECT * FROM Projetos.lista_suportes WHERE id_lista_suporte= '" . $_GET["id_lista_suporte"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$lista_suportes = mysqli_fetch_array($registro); 
			 
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
                      <td width="10%"><span class="label1">suporte</span></td>
                      <td width="1%"> </td>
                      <td width="18%">POSIÇÃO</td>
                      <td width="1%"> </td>
                      <td width="46%"> </td>
                      <td width="2%"> </td>
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
                        <option value="<?= $cont["id_subsistema"] ?>" <?php if($lista_suportes["id_subsistema"]==$cont["id_subsistema"]) { echo "selected"; } ?>>
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
						$sql .= "AND subsistema.id_subsistema = '".$lista_suportes["id_subsistema"]."' ";
						$sql .= "AND area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY fluidos.cd_fluido, locais.nr_sequencia, locais.nr_diametro  ";
						
						$reg_equipamentos = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont["id_local"] ?>" <?php if($lista_suportes["id_linha"]==$cont["id_local"]) { echo "selected"; } ?>>
                        <?= $cont["cd_fluido"]."-".$cont["nr_diametro"]."-".$cont["cd_material"]."-".$cont["nr_sequencia"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_suporte" class="txt_box" id="id_suporte" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg_equipamentos = $db->select($sql,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_tipo_suporte"] ?>" <?php if($lista_suportes["id_suporte"]==$cont_equipamentos["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_tipo_suporte"] . " - " . $cont_equipamentos["ds_tipo_suporte"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_posicao" type="text" class="txt_box" id="cd_posicao" value="<?= str_replace('\"',"&quot;",$lista_suportes["ds_posicao"]) ?>" size="20" maxlength="20">
                      </font></td>
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
                      <td width="9%">TAG</td>
                      <td width="1%"> </td>
                      <td width="9%">ELEVAÇÃO</td>
                      <td width="1%"> </td>
                      <td width="10%">QUANTIDADE</td>
                      <td width="1%"> </td>
                      <td width="9%">H</td>
                      <td width="1%"> </td>
                      <td width="56%">l</td>
                      <td width="3%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_tag" type="text" class="txt_box" id="cd_tag" value="<?= str_replace('"',"&quot;",$lista_suportes["cd_tag"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= str_replace('"',"&quot;",$lista_suportes["nr_elevacao"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_quantidade" type="text" class="txt_box" id="nr_quantidade" value="<?= str_replace('\"',"&quot;",$lista_suportes["nr_quantidade"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_h" type="text" class="txt_box" id="nr_h" value="<?= $lista_suportes["nr_h"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_l" type="text" class="txt_box" id="nr_l" value="<?= $lista_suportes["nr_l"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="9%">A</td>
                      <td width="1%"> </td>
                      <td width="9%">B</td>
                      <td width="1%"> </td>
                      <td width="9%">C</td>
                      <td width="1%"> </td>
                      <td width="16%">PLANTA</td>
                      <td width="29%">REVISÃO</td>
                      <td width="23%"> </td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_a" type="text" class="txt_box" id="nr_a" value="<?= $lista_suportes["nr_a"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_b" type="text" class="txt_box" id="nr_b" value="<?= $lista_suportes["nr_b"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_c" type="text" class="txt_box" id="nr_c" value="<?= $lista_suportes["nr_c"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_planta" type="text" class="txt_box" id="ds_planta" value="<?= $lista_suportes["ds_planta"] ?>" size="40">
                      </font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $lista_suportes["nr_revisao"] ?>" size="20">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_lista_suporte" type="hidden" id="id_lista_suporte" value="<?= $lista_suportes["id_lista_suporte"] ?>">
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
                      <td width="11%" class="label1">SUBSISTEMA</td>
                      <td width="1%"> </td>
                      <td width="10%">LINHA</td>
                      <td width="1%"> </td>
                      <td width="10%">SUPORTE</td>
                      <td width="1%"> </td>
                      <td width="11%">POSIÇÃO</td>
                      <td width="48%"> </td>
                      <td width="7%"> </td>
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
							<option value="<?= $cont["id_local"] ?>" <?php if($_POST["id_linha"]==$cont["id_local"]) { echo "selected"; } ?>>
							<?= $cont["cd_fluido"]."-".$cont["nr_diametro"]."-".$cont["cd_material"]."-".$cont["nr_sequencia"] ?>
							</option>
							<?php
								
							}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_suporte" class="txt_box" id="id_suporte" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.tipos_suportes ";
						$sql .= "ORDER BY cd_tipo_suporte ";
						
						$reg_equipamentos = $db->select($sql,'MYSQL');
						
						while($cont_equipamentos = mysqli_fetch_array($reg_equipamentos))
						{
							?>
                        <option value="<?= $cont_equipamentos["id_tipo_suporte"] ?>" <?php if($_POST["id_suporte"]==$cont_equipamentos["id_tipo_suporte"]) { echo "selected"; } ?>>
                        <?= $cont_equipamentos["cd_tipo_suporte"] . " - " . $cont_equipamentos["ds_tipo_suporte"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_posicao" type="text" class="txt_box" id="cd_posicao" value="<?= str_replace('\"',"&quot;",$_POST["cd_posicao"]) ?>" size="20" maxlength="20">
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
                      <td width="9%">TAG</td>
                      <td width="1%"> </td>
                      <td width="9%">ELEVAÇÃO</td>
                      <td width="1%"> </td>
                      <td width="10%">QUANTIDADE</td>
                      <td width="9%">H</td>
                      <td width="15%"><span class="label1">L</span></td>
                      <td width="1%"> </td>
                      <td width="9%"> </td>
                      <td width="31%"> </td>
                      <td width="5%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_tag" type="text" class="txt_box" id="cd_tag" value="<?= str_replace('"',"&quot;",$_POST["cd_tag"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_elevacao" type="text" class="txt_box" id="nr_elevacao" value="<?= str_replace('"',"&quot;",$_POST["nr_elevacao"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_quantidade" type="text" class="txt_box" id="nr_quantidade" value="<?= str_replace('\"',"&quot;",$_POST["nr_quantidade"]) ?>" size="20" maxlength="20">
                      </font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_h" type="text" class="txt_box" id="nr_h" value="<?= $_POST["nr_h"] ?>" size="20" maxlength="5">
                      </font></td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_l" type="text" class="txt_box" id="nr_l" value="<?= $_POST["nr_l"] ?>" size="20" maxlength="20">
                      </font></td>
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
                      <td width="9%">A</td>
                      <td width="1%"> </td>
                      <td width="9%">B </td>
                      <td width="1%"> </td>
                      <td width="9%">C</td>
                      <td width="1%"> </td>
                      <td width="17%">PLANTA</td>
                      <td width="1%"> </td>
                      <td width="50%">REVISÃO</td>
                      <td width="2%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_a" type="text" class="txt_box" id="nr_a" value="<?= $_POST["nr_a"] ?>" size="20" maxlength="5">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_b" type="text" class="txt_box" id="nr_b" value="<?= $_POST["nr_b"] ?>" size="20" maxlength="5">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_c" type="text" class="txt_box" id="nr_c" value="<?= $_POST["nr_c"] ?>" size="20" maxlength="5">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_planta" type="text" class="txt_box" id="ds_planta" value="<?= $_POST["ds_planta"] ?>" size="40">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="20">
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
				  <td width="22%">SUBSISTEMA</td>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "ds_planta";
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
				  <td width="14%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_local','<?= $ordem ?>')">LINHA</a></td>
				  <td width="12%">PLANTA</td>
				  <td width="9%">POSIÇÃO</td>
				  <td width="15%">SUPORTE</td>
				  <td width="10%">ELEVAÇÃO</td>
				  <td width="11%">QUANTIDADE</td>
				  <td width="3%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			
					// Mostra os funcionários
					$sql = "SELECT *, locais.nr_sequencia AS seq_local, locais.nr_diametro AS dm_local FROM Projetos.area, Projetos.subsistema, Projetos.locais, Projetos.fluidos, Projetos.materiais, Projetos.tipos_suportes, Projetos.lista_suportes ";
					$sql .= "WHERE subsistema.id_subsistema = lista_suportes.id_subsistema ";
					$sql .= "AND subsistema.id_area = area.id_area ";
					$sql .= "AND lista_suportes.id_linha = locais.id_local ";
					$sql .= "AND locais.id_fluido = fluidos.id_fluido ";
					$sql .= "AND locais.id_material = materiais.id_material ";
					$sql .= "AND lista_suportes.id_suporte = tipos_suportes.id_tipo_suporte ";
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
						    <?= $locais["nr_subsistema"] . " - " . $locais["subsistema"] ?>
					      </div></td>
						  <td width="14%"><div align="center">
						    <?= $locais["cd_fluido"]."-".$locais["dm_local"]."-".$locais["cd_material"]."-".$locais["seq_local"] ?></div></td>
						  <td width="12%"><div align="center">
                            <?= $locais["ds_planta"] ?>
					      </div></td>
						  <td width="9%"><div align="center">
					      <?= $locais["cd_posicao"] ?></div></td>
						  <td width="15%"><div align="center">
						    <?= $locais["cd_tipo_suporte"] ?>
						  </div></td>
						  <td width="10%"><div align="center">
                            <?= $locais["nr_elevacao"] ?>
                          </div></td>
						  <td width="13%"><div align="center">
                            <?= $locais["nr_quantidade"] ?>
                          </div></td>
						  <td width="2%"><div align="center"><a href="javascript:editar('<?= $locais["id_lista_suporte"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="3%"><div align="center"><a href="javascript:excluir('<?= $locais["id_lista_suporte"] ?>','<?= $locais["ds_tipo_suporte"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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