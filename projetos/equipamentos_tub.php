<?php
/*

		Formulário de Equipamentos	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/equipamentos_tub.php
		
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

	$sql = "UPDATE Projetos.equipamentos SET ";
	$sql .= "id_disciplina = '" . $disciplina["id_setor"] . "', ";
	$sql .= "cd_local = '" . maiusculas($_POST["cd_local"]) . "', ";
	$sql .= "ds_equipamento = '" . maiusculas($_POST["ds_equipamento"]) . "' ";
	$sql .= "WHERE id_equipamentos = '" . $_POST["id_equipamentos"] ."' ";
	
	$registros = $db->update($sql,'MYSQL');


	?>
	<script>
		alert('Equipamento atualizado com sucesso.');
	</script>
	<?php
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria sentença de Inclusão no bd
	$isql = "INSERT INTO Projetos.equipamentos ";
	$isql .= "(id_disciplina, cd_local, ds_equipamento) VALUES (";
	$isql .= "'" . $disciplina["id_setor"] . "', ";
	$isql .= "'" . maiusculas($_POST["cd_local"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_equipamento"]) . "') ";

	$registros = $db->insert($isql,'MYSQL');

	?>
	<script>
		alert('Equipamento inserido com sucesso.');
	</script>
	<?php

}


 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.equipamentos WHERE equipamentos.id_equipamentos = '".$_GET["id_equipamentos"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Equipamento excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . EQUIPAMENTOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_equipamentos, equipamento)
{
	if(confirm('Tem certeza que deseja excluir o tipo '+equipamento+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_equipamentos='+id_equipamentos+'';
	}
}

function editar(id_equipamentos)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_equipamentos='+id_equipamentos+'';
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
<form name="frm_equipamentos" method="post" action="<?= $PHP_SELF ?>" >
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
				$sql = "SELECT * FROM Projetos.equipamentos WHERE id_equipamentos= '" . $_GET["id_equipamentos"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$equipamentos = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td> </td>
                  <td align="left"> </td>
                </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="13%" class="label1">CÓD. EQUIPAMENTO </td>
                      <td width="0%"> </td>
                      <td width="13%" class="label1">EQUIPAMENTO</td>
                      <td width="0%" class="label1"> </td>
                      <td width="62%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_local" type="text" class="txt_box" id="cd_local" size="40" maxlength="3" value="<?= $equipamentos["cd_local"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_equipamento" type="text" class="txt_box" id="ds_equipamento" size="30" value="<?= $equipamentos["ds_equipamento"] ?>">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                    <tr>
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
                  <td>
				  <input name="id_equipamentos" type="hidden" id="id_equipamentos" value="<?= $equipamentos["id_equipamentos"] ?>">
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
                  <td> </td>
                  <td align="left"> </td>
                </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="13%" class="label1">cÓd. equipamento </td>
                      <td width="0%"> </td>
                      <td width="13%" class="label1">equipamento</td>
                      <td width="0%" class="label1"> </td>
                      <td width="62%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_local" type="text" class="txt_box" id="cd_local" value="<?= $_POST["cd_local"] ?>" size="40" maxlength="3">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_equipamento" type="text" class="txt_box" id="ds_equipamento" value="<?= $_POST["equipamento"] ?>" size="30">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                    <tr>
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
				  <td width="31%">DISCIPLINA</td>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "ds_equipamento";
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
				  <td width="31%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tipo','<?= $ordem ?>')">CÓD. EQUIPAMENTO </a></td>
				  <td width="28%">EQUIPAMENTO</td>
				  <td width="7%"  class="cabecalho_tabela">E</td>
				  <td width="7%"  class="cabecalho_tabela">D</td>
				  <td width="7%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					
					$sql = "SELECT * FROM Projetos.equipamentos, ".DATABASE.".setores ";
					$sql .= "WHERE id_disciplina = id_setor ";
					$sql .= "AND setor = 'TUBULAÇÃO' ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($equipamentos = mysqli_fetch_array($registro))
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
						    <?= $equipamentos["setor"] ?>
					      </div></td>
						  <td width="26%"><div align="center"><?= $equipamentos["cd_local"] ?></div></td>
						  <td width="26%"><div align="center"><?= $equipamentos["ds_equipamento"] ?></div></td>
						  <td width="2%"><div align="center"> <a href="javascript:editar('<?= $equipamentos["id_equipamentos"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="5%"><div align="center"> <a href="javascript:excluir('<?= $equipamentos["id_equipamentos"] ?>','<?= $equipamentos["ds_equipamento"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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