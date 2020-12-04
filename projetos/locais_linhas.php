<?
/*
		Formul�rio de Locais equipamentos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/locais_equip.php
		
		data de cria��o: 12/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
		
*/
	
//Obt�m os dados do usu�rio
session_start();

if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php");
	exit;
}
		
	
//include ("../includes/layout.php");
include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
$sql .= "WHERE OS.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";

$reg = $db->select($sql,'MYSQL');

$cliente = mysqli_fetch_array($reg);



$sql1 = "SELECT * FROM ".DATABASE.".setores ";
$sql1 .= "WHERE setor = 'TUBULA��O' ";

$regis = $db->select($sql1,'MYSQL');

$disciplina = mysqli_fetch_array($regis);


//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND nr_diametro = '" . $_POST["nr_diametro"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND id_fluido = '" . $_POST["id_fluido"] . "' ";
	$sql .= "AND id_material = '" . $_POST["id_material"] . "' ";
	$sql .= "AND cd_trecho = '" . $_POST["cd_trecho"] . "' ";
	$sql .= "AND ds_inicio = '" . maiusculas($_POST["ds_inicio"]) . "' ";
	$sql .= "AND ds_fim = '" . maiusculas($_POST["ds_fim"]) . "' ";
	$sql .= "AND ds_fluxograma = '" . maiusculas($_POST["ds_fluxograma"]) . "' ";
	$sql .= "AND ds_isometrico = '" . maiusculas($_POST["ds_isometrico"]) . "' ";
	$sql .= "AND nr_temperatura = '" . $_POST["nr_temperatura"] . "' ";
	$sql .= "AND nr_vazao = '" . $_POST["nr_vazao"] . "' ";
	$sql .= "AND nr_pressao = '" . $_POST["nr_pressao"] . "' ";
	$sql .= "AND nr_densidade = '" . $_POST["nr_densidade"] . "' ";
	$sql .= "AND nr_viscosidade = '" . $_POST["nr_viscosidade"] . "' ";
	$sql .= "AND nr_condutividade = '" . $_POST["nr_condutividade"] . "' ";
	$sql .= "AND nr_isolamento = '" . maiusculas($_POST["nr_isolamento"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	$sql .= "AND ds_complemento = '" . $_POST["ds_complemento"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local j� cadastrado no banco de dados.');
		</script>
		<?	
	}
	else
	{
	
		$sql = "UPDATE Projetos.locais SET ";
		$sql .= "id_area = '" . $_POST["id_area"] . "', ";
		$sql .= "nr_diametro = '" . $_POST["nr_diametro"] . "', ";
		$sql .= "nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "', ";
		$sql .= "id_fluido = '" . $_POST["id_fluido"] . "', ";
		$sql .= "ds_complemento = '" . $_POST["ds_complemento"] . "', ";
		$sql .= "id_disciplina = '" . $disciplina["id_setor"] . "', ";
		$sql .= "id_material = '" . $_POST["id_material"] . "', ";
		$sql .= "cd_trecho = '" . $_POST["cd_trecho"] . "', ";
		$sql .= "ds_inicio = '" . maiusculas($_POST["ds_inicio"]) . "', ";
		$sql .= "ds_fim = '" . maiusculas($_POST["ds_fim"]) . "', ";
		$sql .= "ds_fluxograma = '" . maiusculas($_POST["ds_fluxograma"]) . "', ";
		$sql .= "ds_isometrico = '" . maiusculas($_POST["ds_isometrico"]) . "', ";
		$sql .= "nr_temperatura = '" . $_POST["nr_temperatura"] . "', ";
		$sql .= "nr_vazao = '" . $_POST["nr_vazao"] . "', ";
		$sql .= "nr_pressao = '" . $_POST["nr_pressao"] . "', ";
		$sql .= "nr_densidade = '" . $_POST["nr_densidade"] . "', ";
		$sql .= "nr_viscosidade = '" . $_POST["nr_viscosidade"] . "', ";
		$sql .= "nr_condutividade = '" . $_POST["nr_condutividade"] . "', ";
		$sql .= "nr_isolamento = '" . maiusculas($_POST["nr_isolamento"]) . "', ";
		$sql .= "nr_revisao = '" . $_POST["nr_revisao"] . "' ";
		
		$sql .= "WHERE id_local = '" . $_POST["id_local"] ."' ";
		
		$registros = $db->update($sql,'MYSQL');
			
		?>
		<script>
			alert('local atualizado com sucesso.');
		</script>
		<?
	}

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.locais WHERE ";
	$sql .= "id_area = '" . $_POST["id_area"] . "' ";
	$sql .= "AND nr_diametro = '" . $_POST["nr_diametro"] . "' ";
	$sql .= "AND id_disciplina = '" . $disciplina["id_setor"] . "' ";
	$sql .= "AND nr_sequencia = '" . maiusculas($_POST["nr_sequencia"]) . "' ";
	$sql .= "AND id_fluido = '" . $_POST["id_fluido"] . "' ";
	$sql .= "AND id_material = '" . $_POST["id_material"] . "' ";
	$sql .= "AND cd_trecho = '" . $_POST["cd_trecho"] . "' ";
	$sql .= "AND ds_inicio = '" . maiusculas($_POST["ds_inicio"]) . "' ";
	$sql .= "AND ds_fim = '" . maiusculas($_POST["ds_fim"]) . "' ";
	$sql .= "AND ds_fluxograma = '" . maiusculas($_POST["ds_fluxograma"]) . "' ";
	$sql .= "AND ds_isometrico = '" . maiusculas($_POST["ds_isometrico"]) . "' ";
	$sql .= "AND nr_temperatura = '" . $_POST["nr_temperatura"] . "' ";
	$sql .= "AND nr_vazao = '" . $_POST["nr_vazao"] . "' ";
	$sql .= "AND nr_pressao = '" . $_POST["nr_pressao"] . "' ";
	$sql .= "AND nr_densidade = '" . $_POST["nr_densidade"] . "' ";
	$sql .= "AND nr_viscosidade = '" . $_POST["nr_viscosidade"] . "' ";
	$sql .= "AND nr_condutividade = '" . $_POST["nr_condutividade"] . "' ";
	$sql .= "AND nr_isolamento = '" . maiusculas($_POST["nr_isolamento"]) . "' ";
	$sql .= "AND nr_revisao = '" . $_POST["nr_revisao"] . "' ";
	$sql .= "AND ds_complemento = '" . $_POST["ds_complemento"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	if($db->numero_registros>0)
	{
		?>
		<script>
			alert('local j� cadastrado no banco de dados.');
		</script>
		<?	
	}
	else
	{
	
		//Cria senten�a de Inclusão no bd
		$incsql = "INSERT INTO Projetos.locais ";
		$incsql .= "(id_area, id_disciplina, id_fluido, id_material, ds_complemento, nr_diametro, nr_sequencia, ";
		$incsql .= "cd_trecho, ds_inicio, ds_fim, ds_fluxograma, ds_isometrico, ";
		$incsql .= "nr_vazao, nr_temperatura, nr_pressao, nr_densidade, nr_viscosidade, ";
		$incsql .= "nr_condutividade, nr_isolamento, nr_revisao ) VALUES (";
		$incsql .= "'" . $_POST["id_area"] . "', ";
		$incsql .= "'" . $disciplina["id_setor"] . "', ";
		$incsql .= "'" . $_POST["id_fluido"] . "', ";
		$incsql .= "'" . $_POST["id_material"] . "', ";
		$incsql .= "'" . $_POST["ds_complemento"] . "', ";
		$incsql .= "'" . $_POST["nr_diametro"] . "', ";
		$incsql .= "'" . $_POST["nr_sequencia"] . "', ";
		$incsql .= "'" . $_POST["ds_trecho"] . "', ";
		$incsql .= "'" . maiusculas($_POST["ds_inicio"]) . "', ";
		$incsql .= "'" . maiusculas($_POST["ds_fim"]) . "', ";
		$incsql .= "'" . $_POST["ds_fluxograma"] . "', ";
		$incsql .= "'" . $_POST["ds_isometrico"] . "', ";
		$incsql .= "'" . $_POST["nr_vazao"] . "', ";
		$incsql .= "'" . $_POST["nr_temperatura"] . "', ";
		$incsql .= "'" . $_POST["nr_pressao"] . "', ";
		$incsql .= "'" . $_POST["nr_densidade"] . "', ";
		$incsql .= "'" . $_POST["nr_viscosidade"] . "', ";
		$incsql .= "'" . $_POST["nr_condutividade"] . "', ";	
		$incsql .= "'" . maiusculas($_POST["nr_isolamento"]) . "', ";		
		$incsql .= "'" . $_POST["nr_revisao"] . "') ";
	
		$registros = $db->insert($incsql,'MYSQL');
		
		?>
		<script>
			alert('local inserido com sucesso.');
		</script>
		<?
	}

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.locais WHERE id_local = '".$_GET["id_local"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('local exclu�do com sucesso.');
	</script>
	<?
}

?>

<html>
<head>
<title>: : . LINHAS TUB .  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
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

//Fun��o para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
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
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;</td>
      </tr>
	  <tr>
        <td>
		
			
			<?
			
			// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
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
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%" class="label1">&Aacute;REA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="12%"><span class="label1">DI&Acirc;METRO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">flu&Iacute;do</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">SEQU&Ecirc;NCIA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="46%">COMPLEMENTO</td>
                      <td width="2%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_area"] ?>" <? if($locais["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
							</option>
							<?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_diametro" type="text" class="txt_box" id="nr_diametro" size="20" maxlength="20" value="<?= str_replace('"',"&quot;",$locais["nr_diametro"]) ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><select name="id_fluido" class="txt_box" id="id_fluido" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						$sql = "SELECT * FROM Projetos.fluidos ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_fluido"] ?>" <? if($locais["id_fluido"]==$cont["id_fluido"]) { echo "selected"; } ?>>
                        <?= $cont["cd_fluido"] . " - " . $cont["ds_fluido"]. " / " . $cont["cliente"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="20" maxlength="20" value="<?= $locais["nr_sequencia"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_complemento" type="text" class="txt_box" id="ds_complemento" size="20" maxlength="20" value="<?= $locais["ds_complemento"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%">MATERIAL</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">trecho</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">inicio</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">t&Eacute;RMINO</td>
                      <td width="1%">&nbsp;</td>
                      <td width="47%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_material" class="txt_box" id="id_material" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?

						$sql = "SELECT * FROM Projetos.materiais ";
						$sql .= "ORDER BY mat_cliente, cd_material ";
					
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_material"] ?>" <? if($locais["id_material"]==$cont["id_material"]) { echo "selected"; } ?>>
                          <?= $cont["cd_material"] . " - " . $cont["ds_material"] . " / " . $cont["mat_cliente"] ?>
                          </option>
                          <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_trecho" type="text" class="txt_box" id="cd_trecho" size="30" maxlength="20" value="<?= $locais["cd_trecho"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_inicio" type="text" class="txt_box" id="ds_inicio" size="30" maxlength="20" value="<?= $locais["ds_inicio"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fim" type="text" class="txt_box" id="ds_fim" size="30" maxlength="20" value="<?= $locais["ds_fim"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="13%">FLUXOGRAMA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="7%">isom&Eacute;trico</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%">vaz&Atilde;O</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">PRESS&Atilde;O</td>
                      <td width="1%">&nbsp;</td>
                      <td width="49%">&nbsp;</td>
                      <td width="4%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fluxograma" type="text" class="txt_box" id="ds_fluxograma" value="<?= $locais["ds_fluxograma"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_isometrico" type="text" class="txt_box" id="ds_isometrico" value="<?= $locais["ds_isometrico"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_vazao" type="text" class="txt_box" id="nr_vazao" value="<?= $locais["nr_vazao"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_pressao" id="nr_pressao" type="text" class="txt_box"  value="<?= $locais["nr_pressao"] ?>" size="18" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="12%">TEMPERATURA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="9%">DENSIDADE</td>
                      <td width="1%">&nbsp;</td>
                      <td width="11%">VISCOSIDADE</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">condutividade</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">isolamento</td>
                      <td width="1%">&nbsp;</td>
                      <td width="8%">revis&Atilde;o</td>
                      <td width="29%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_temperatura" type="text" class="txt_box" id="nr_temperatura" value="<?= $locais["nr_temperatura"] ?>" size="28" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_densidade" type="text" class="txt_box" id="nr_densidade" value="<?= $locais["nr_densidade"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_viscosidade" type="text" class="txt_box" id="nr_viscosidade" value="<?= $locais["nr_viscosidade"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_condutividade" id="nr_condutividade" type="text" class="txt_box"  value="<?= $locais["nr_condutividade"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_isolamento" id="nr_isolamento" type="text" class="txt_box" value="<?= $locais["nr_isolamento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $locais["nr_revisao"] ?>" size="18" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_local" type="hidden" id="id_local" value="<?= $locais["id_local"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?
			
			 }
			else
			{
			  ?>
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="13%" class="label1">&Aacute;REA</td>
                      <td width="3%">&nbsp;</td>
                      <td width="12%">DI&Acirc;METRO</td>
                      <td width="3%">&nbsp;</td>
                      <td width="13%">flu&Iacute;do</td>
                      <td width="3%">&nbsp;</td>
                      <td width="12%">SEQU&Ecirc;NCIA</td>
                      <td width="2%">&nbsp;</td>
                      <td width="36%">COMPLEMENTO</td>
                      <td width="3%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_area" class="txt_box" id="id_area" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?
						
						$sql = "SELECT * FROM Projetos.area ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_area"] ?>" <? if($_POST["id_area"]==$cont["id_area"]) { echo "selected"; } ?>>
                          <?= $cont["nr_area"] . " - " . $cont["ds_area"] ?>
                          </option>
                          <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_diametro" type="text" class="txt_box" id="nr_diametro" size="20" maxlength="20" value="<?= str_replace('\"',"&quot;",$_POST["nr_diametro"]) ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><select name="id_fluido" class="txt_box" id="id_fluido" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?

						$sql = "SELECT * FROM Projetos.fluidos ";
						$sql .= "ORDER BY cliente, cd_fluido ";
					
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_fluido"] ?>" <? if($_POST["id_fluido"]==$cont["id_fluido"]) { echo "selected"; } ?>>
                          <?= $cont["cd_fluido"] . " - " . $cont["ds_fluido"]. " / " . $cont["cliente"] ?>
                          </option>
                          <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_sequencia" type="text" class="txt_box" id="nr_sequencia" size="20" maxlength="20" value="<?= $_POST["nr_sequencia"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_complemento" type="text" class="txt_box" id="ds_complemento" size="20" maxlength="20" value="<?= $_POST["nr_sequencia"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%">MATERIAL</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">trecho</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">inicio</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">t&Eacute;RMINO</td>
                      <td width="1%">&nbsp;</td>
                      <td width="47%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><select name="id_material" class="txt_box" id="id_material" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?

						$sql = "SELECT * FROM Projetos.materiais ";
						$sql .= "ORDER BY mat_cliente, cd_material";
					
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_material"] ?>" <? if($_POST["id_material"]==$cont["id_material"]) { echo "selected"; } ?>>
                        <?= $cont["cd_material"] . " - " . $cont["ds_material"] . " / " . $cont["mat_cliente"] ?>
                        </option>
                        <?
							
						}
						
						?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_trecho" type="text" class="txt_box" id="cd_trecho" size="30" maxlength="20" value="<?= $_POST["cd_trecho"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_inicio" type="text" class="txt_box" id="ds_inicio" size="30" maxlength="20" value="<?= $_POST["ds_inicio"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fim" type="text" class="txt_box" id="ds_fim" size="30" maxlength="20" value="<?= $_POST["ds_fim"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="13%"><span class="label1">FLUXOGRAMA</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="7%">isom&Eacute;trico</td>
                      <td width="1%">&nbsp;</td>
                      <td width="10%"><span class="label1">vaz&Atilde;O</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%"><span class="label1">PRESS&Atilde;O</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="49%">&nbsp;</td>
                      <td width="4%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fluxograma" type="text" class="txt_box" id="ds_fluxograma" value="<?= $_POST["ds_fluxograma"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_isometrico" type="text" class="txt_box" id="ds_isometrico" value="<?= $_POST["ds_isometrico"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_vazao" type="text" class="txt_box" id="nr_vazao" value="<?= $_POST["nr_vazao"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_pressao" id="nr_pressao" type="text" class="txt_box" value="<?= $_POST["nr_pressao"] ?>" size="18" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="12%">TEMPERATURA</td>
                      <td width="1%">&nbsp;</td>
                      <td width="9%">DENSIDADE</td>
                      <td width="1%">&nbsp;</td>
                      <td width="11%">VISCOSIDADE</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">condutividade</td>
                      <td width="1%">&nbsp;</td>
                      <td width="13%">isolamento</td>
                      <td width="1%">&nbsp;</td>
                      <td width="8%">revis&Atilde;o</td>
                      <td width="29%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_temperatura" type="text" class="txt_box" id="nr_temperatura" value="<?= $_POST["nr_temperatura"] ?>" size="28" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_densidade" type="text" class="txt_box" id="nr_densidade" value="<?= $_POST["nr_densidade"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_viscosidade" type="text" class="txt_box" id="nr_viscosidade" value="<?= $_POST["nr_viscosidade"] ?>" size="20" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_condutividade" id="nr_condutividade" type="text" class="txt_box" value="<?= $_POST["nr_condutividade"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_isolamento" id="nr_isolamento" type="text" class="txt_box" value="<?= $_POST["nr_isolamento"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="18" maxlength="20">
                      </font></td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Equipamentos" type="button" class="btn" id="Equipamentos" value="COMPONENTES" onClick="javascript:location.href='componentes.php';"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
			 <?
			}
			?>
			
			
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="11%">&Aacute;REA</td>
				  <td width="25%">LINHA</td>
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = "nr_sequencia, cd_trecho ";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordena��o
				  ?>
				  <td width="18%"><a href="#" class="cabecalho_tabela" onClick="ordenar('cd_local','<?= $ordem ?>')">TRECHO</a></td>
				  <td width="13%">INICIO</td>
				  <td width="22%">T&Eacute;RMINO</td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="4%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
				
			
					// Mostra os funcion�rios
					$sql = "SELECT * FROM Projetos.area, Projetos.locais, Projetos.fluidos, Projetos.materiais ";
					//$sql .= "WHERE locais.id_equipamento = equipamentos.id_equipamentos ";
					$sql .= "WHERE locais.id_disciplina = '" .$disciplina["id_setor"]. "' ";
					$sql .= "AND locais.id_area = area.id_area ";
					$sql .= "AND area.id_os= '" . $_SESSION["id_os"]. "' ";
					$sql .= "AND fluidos.id_fluido = locais.id_fluido ";
					$sql .= "AND materiais.id_material = locais.id_material ";
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
						
						
						$client = explode(' ',$cliente["empresa"]);
						
						if($client[0]=='SUZANO')
						{
							$linha = $locais["cd_fluido"]." - ".$locais["nr_diametro"]." - ".$locais["cd_material"]." - ". $locais["ds_complemento"]." - ".$locais["nr_sequencia"];
						}
						else
						{
							$linha = $locais["cd_fluido"]. " - " . $locais["nr_sequencia"]. " - " . $locais["cd_material"]. " - " . $locais["nr_diametro"];
						}							

						?>
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">
						  <td width="11%"><div align="center">
                            <?= $locais["nr_area"] ?>
                          </div></td>
						  <td width="25%"><div align="center">
						    <?= $linha //$locais["cd_fluido"]. " - " . $locais["nr_sequencia"]. " - " . $locais["cd_material"]. " - " . $locais["nr_diametro"] ?>
					      	
						  </div></td>
						  <td width="18%"><div align="center"><?= $locais["cd_trecho"] ?></div></td>
						  <td width="13%"><div align="center"><?= $locais["ds_inicio"] ?></div></td>
						  <td width="23%"><div align="center"><?= $locais["ds_fim"] ?></div></td>
						  <td width="4%"><div align="center"><a href="javascript:editar('<?= $locais["id_local"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="6%"><div align="center"><a href="javascript:excluir('<?= $locais["id_local"] ?>','<?= $locais["ds_trecho"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?
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
