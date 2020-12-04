<?
/*

		Formul�rio de Dispositivos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/dispositivos.php
		
		data de cria��o: 06/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> ALTERA��O PARA COMPONENTE
		Versão 2 --> ALTERA��O PARA DISPOSITIVO - 26/04/2006
		Versão 3 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016

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
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;


//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	$sql = "SELECT * FROM Projetos.dispositivos ";
	$sql .= "WHERE dispositivo = '".maiusculas($_POST["dispositivo"])."' ";
	$sql .= "AND ds_dispositivo = '". maiusculas($_POST["ds_dispositivo"]) . "' ";
	//$sql .= "AND id_processo = '". $_POST["id_processo"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Dispositivo j� cadastrado no banco de dados.');
				
			</script>
			<?
		}
	else
		{

		
			$sql = "UPDATE Projetos.dispositivos SET ";
			$sql .= "dispositivo = '" . maiusculas($_POST["dispositivo"]) . "', ";
			$sql .= "ds_dispositivo = '" . maiusculas($_POST["ds_dispositivo"]) . "' ";
			$sql .= "WHERE id_dispositivo = '" . $_POST["id_dispositivo"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
	
			?>
			<script>
				alert('Dispositivo atualizado com sucesso.');
			</script>
			<?
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.dispositivos ";
	$sql .= "WHERE dispositivo = '".maiusculas($_POST["dispositivo"])."' ";
	$sql .= "AND ds_dispositivo = '". maiusculas($_POST["ds_dispositivo"]) . "' ";
	//$sql .= "AND id_processo = '". $_POST["id_processo"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Dispositivo j� cadastrado no banco de dados.');
				
			</script>
			<?
		}
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.dispositivos ";
			$incsql .= "(dispositivo, ds_dispositivo) ";
			$incsql .= "VALUES ('" . maiusculas($_POST["dispositivo"]) . "', ";
			$incsql .= "'" . maiusculas($_POST["ds_dispositivo"]) ."') ";

			$registros = $db->insert($incsql,'MYSQL');

			?>
			<script>
				alert('Dispositivo inserido com sucesso.');
			</script>
			<?
		}

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.dispositivos WHERE id_dispositivo = '".$_GET["id_dispositivo"]."' ";
	
	$db->delete($dsql,'MYSQL');	
	
	?>
	<script>
		alert('Dispositivo exclu�do com sucesso.');
	</script>
	<?
}
?>

<html>
<head>
<title>: : . DISPOSITIVOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_dispositivo, ds_dispositivo)
{
	if(confirm('Tem certeza que deseja excluir o dispositivo '+ds_dispositivo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_dispositivo='+id_dispositivo+'';
	}
}

function editar(id_dispositivo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_dispositivo='+id_dispositivo+'';
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
<form name="frm_dispositivos" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.dispositivos WHERE id_dispositivo= '" . $_GET["id_dispositivo"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$dispositivo = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="16%"><span class="label1">C&Oacute;D. dispositivo </span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="28%" class="label1">dispositivo</td>
                      <td width="1%">&nbsp;</td>
                      <td width="54%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="dispositivo" type="text" class="txt_box" id="dispositivo" value="<?= $dispositivo["dispositivo"] ?>" size="25" maxlength="5">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_dispositivo" type="text" class="txt_box" id="ds_dispositivo" value="<?= $dispositivo["ds_dispositivo"] ?>" size="50">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_dispositivo" type="hidden" id="id_dispositivo" value="<?= $dispositivo["id_dispositivo"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onClick="javascript:history.back();"></td>
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
                  <td align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="16%"><span class="label1">C&Oacute;D. dispositivo </span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="29%" class="label1">dispositivo </td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="53%" class="label1">&nbsp;</td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="dispositivo" type="text" class="txt_box" id="dispositivo" size="25" maxlength="5" value="<?= $_POST["dispositivo"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_dispositivo" type="text" class="txt_box" id="ds_dispositivo" size="50" value="<?= $_POST["ds_dispositivo"] ?>">
                      </font></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onClick="javascript:history.back();">
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="TIPO" onClick="javascript:location.href='tipo.php'"></td>
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
				  <?
					// Controle de ordena��o
					if($_GET["campo"]=='')
					{
						$campo = "dispositivo";
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
				  <td width="20%"><div align="left"><a href="#" class="cabecalho_tabela" onClick="ordenar('dispositivo','<?= $ordem ?>')">C&Oacute;D. DISPOSITIVO </a></div></td>
				  <td width="69%"><div align="left"><a href="#" class="cabecalho_tabela" onClick="ordenar('ds_dispositivo','<?= $ordem ?>')">DISPOSITIVO</a></div></td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?

			
					// Mostra os funcion�rios
					
					$sql = "SELECT * FROM Projetos.dispositivos ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($dispositivo = mysqli_fetch_array($registro))
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
						  <td width="20%" height="18"><div align="left"><?= $dispositivo["dispositivo"] ?></div><div align="left"></div><div align="left"></div></td>
						  <td width="70%"><div align="left">
						    <?= $dispositivo["ds_dispositivo"] ?>
					      </div></td>
						  <td width="5%"><div align="center">
						 <a href="#" onClick="editar('<?= $dispositivo["id_dispositivo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="5%"><div align="center"> <a href="#" onClick="excluir('<?= $dispositivo["id_dispositivo"] ?>','<?= $dispositivo["ds_dispositivo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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