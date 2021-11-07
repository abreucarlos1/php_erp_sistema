<?php
/*

		Formulário de Devices (Dispositivos)	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/devices.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Inclusão CLIENTE - 04/05/2006
		Versão 2 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016

		
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
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{

	$sql = "UPDATE Projetos.devices SET ";
	$sql .= "id_cliente = '" . $_POST["id_cliente"] . "', ";
	$sql .= "cd_dispositivo = '" . maiusculas($_POST["cd_dispositivo"]) . "', ";
	$sql .= "ds_servico = '" . maiusculas($_POST["ds_servico"]) . "', ";
	$sql .= "ds_fabricante = '" . maiusculas($_POST["ds_fabricante"]) . "', ";
	$sql .= "ds_modelo = '" . maiusculas($_POST["ds_modelo"]) . "', ";
	$sql .= "ds_rede = '" . maiusculas($_POST["ds_rede"]) . "', ";
	$sql .= "nr_no = '" . maiusculas($_POST["nr_no"]) . "', ";
	$sql .= "nr_revisao = '" . $_POST["nr_revisao"] . "' ";

	$sql .= "WHERE devices.id_devices = '" . $_POST["id_devices"] ."' ";
	
	$regis = $db->update($sql,'MYSQL');

	?>
	<script>
		alert('Device atualizado com sucesso.');
	</script>
	<?php
	
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{

	//Cria sentença de Inclusão no bd
	$isql = "INSERT INTO Projetos.devices ";
	$isql .= "(cd_dispositivo, ds_servico, ds_fabricante, ds_modelo, ds_rede, nr_no, nr_revisao) VALUES (";
	$isql .= "'" . maiusculas($_POST["cd_dispositivo"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_servico"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_fabricante"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_modelo"]) . "', ";
	$isql .= "'" . maiusculas($_POST["ds_rede"]) . "', ";
	$isql .= "'" . maiusculas($_POST["nr_no"]) . "', ";
	$isql .= "'" . $_POST["nr_revisao"] . "') ";

	$registros = $db->insert($isql,'MYSQL');

	?>
	<script>
		alert('Device / Dispositivo inserido com sucesso.');
	</script>
	<?php

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.devices WHERE devices.id_devices = '".$_GET["id_devices"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Device / Dispositivo excluído com sucesso.');
	</script>
	<?php
}
?>

<html>
<head>
<title>: : . DEVICES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_devices, cd_dispositivo)
{
	if(confirm('Tem certeza que deseja excluir o device/dispositivo '+cd_dispositivo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_devices='+id_devices+'';
	}
}

function editar(id_devices)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_devices='+id_devices+'';
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


function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_devices" method="post" action="<?= $PHP_SELF ?>" enctype="multipart/form-data">
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
				$sql = "SELECT * FROM Projetos.devices WHERE devices.id_devices= '" . $_GET["id_devices"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$devices = mysqli_fetch_array($registro); 	
			 
			 
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
                    <tr>
                      <td width="10%" class="label1">CLIENTE</td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">DISPOSITIVO</span></td>
                      <td width="1%"> </td>
                      <td width="33%"><span class="label1">SERVIÇO</span></td>
                      <td width="4%"> </td>
                      <td width="38%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_cliente" class="txt_box" id="id_cliente" onkeypress="return keySort(this);">
                          <option value="">NENHUMA</option>
                          <?php
						  	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
							$sql .= "WHERE empresas.id_unidade=unidades.id_unidade ORDER BY empresa";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["id_empresa"] ?>"<?php if($regs["id_empresa"]==$devices["id_cliente"]){ echo 'selected';} ?>>
                            <?= $regs["empresa"]. " - " .$regs["descricao"] . " - " . $regs["unidade"] ?>
                            </option>
                          <?php
								}
							?>
                        </select>
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_dispositivo" type="text" class="txt_box" id="cd_dispositivo" size="30" maxlength="20" value="<?= $devices["cd_dispositivo"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_servico" type="text" class="txt_box" id="ds_servico" value="<?= $devices["ds_servico"] ?>" size="30" maxlength="25">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%"><span class="label1">FABRICANTE</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">MODELO</span></td>
                      <td width="0%"> </td>
                      <td width="34%"> </td>
                      <td width="4%"> </td>
                      <td width="35%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" value="<?= $devices["ds_fabricante"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_modelo" type="text" class="txt_box" id="ds_modelo" value="<?= $devices["ds_modelo"] ?>" size="30" maxlength="10">
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
                  <td align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="13%" class="label1">REDE</td>
                      <td width="1%" class="label1"> </td>
                      <td width="13%" class="label1">NÓ</td>
                      <td width="1%"> </td>
                      <td width="26%" class="label1">REVISÃO</td>
                      <td width="3%" class="label1"> </td>
                      <td width="43%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_rede" type="text" class="txt_box" id="ds_rede" value="<?= $devices["ds_rede"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_no" type="text" class="txt_box" id="nr_no" value="<?= $devices["nr_no"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $devices["nr_revisao"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_devices" type="hidden" id="id_devices" value="<?= $devices["id_devices"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Racks2" type="button" class="btn" id="Racks2" value="VOLTAR" onclick="javascript:history.back();"></td>
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
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="10%" class="label1">cliente</td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">DISPOSITIVO</span></td>
                      <td width="1%"> </td>
                      <td width="33%"><span class="label1">SERVIÇO</span></td>
                      <td width="4%"> </td>
                      <td width="38%"> </td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_cliente" class="txt_box" id="id_cliente" onkeypress="return keySort(this);">
                          <option value="">NENHUMA</option>
                          <?php

						  	$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidades ";
							$sql .= "WHERE empresas.id_unidade=unidades.id_unidade ORDER BY empresa";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["id_empresa"] ?>"<?php if($regs["id_empresa"]==$areas["id_cliente"]){ echo 'selected';} ?>>
                            <?= $regs["empresa"]. " - " .$regs["descricao"] . " - " . $regs["unidade"] ?>
                            </option>
                          <?php
								}
							?>
                        </select>
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="cd_dispositivo" type="text" class="txt_box" id="cd_dispositivo" size="30" maxlength="20" value="<?= $_POST["cd_dispositivo"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_servico" type="text" class="txt_box" id="ds_servico" value="<?= $_POST["ds_servico"] ?>" size="30" maxlength="25">
                      </font></td>
                      <td> </td>
                      <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr>
                      <td width="13%"><span class="label1">FABRICANTE</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">MODELO</span></td>
                      <td width="0%"> </td>
                      <td width="34%"> </td>
                      <td width="4%"> </td>
                      <td width="35%"> </td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" value="<?= $_POST["ds_fabricante"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_modelo" type="text" class="txt_box" id="ds_modelo" value="<?= $_POST["ds_modelo"] ?>" size="30" maxlength="10">
                      </font></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="13%" class="label1">REDE</td>
                      <td width="1%" class="label1"> </td>
                      <td width="13%" class="label1">NÓ</td>
                      <td width="1%"> </td>
                      <td width="26%" class="label1">REVISÃO</td>
                      <td width="3%" class="label1"> </td>
                      <td width="43%" class="label1"> </td>
                      </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_rede" type="text" class="txt_box" id="ds_rede" value="<?= $_POST["ds_rede"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_no" type="text" class="txt_box" id="nr_no" value="<?= $_POST["nr_no"] ?>" size="30" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="30" maxlength="20">
                      </font></td>
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
                    <input name="Racks2" type="button" class="btn" id="Racks2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Racks" type="button" class="btn" id="Racks" value="Racks" onclick="javascript:location.href='racks.php';"></td>
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
				  
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "cd_dispositivo";
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
				  <td width="22%">CLIENTE</td>
				  <td width="14%"><a href="#" class="cabecalho_tabela" onclick="ordenar('cd_local','<?= $ordem ?>')">DISPOSITIVO</a></td>
				  <td width="20%">SERVIÇO</td>
				  <td width="18%">FABRICANTE</td>
				  <td width="18%">MODELO</td>				  
				  <td width="3%" class="cabecalho_tabela">E</td>
				  <td width="3%" class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			
					// Mostra os funcionários
					$sql = "SELECT * FROM Projetos.devices ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$regcounter = $db->numero_registros;
					
					$i=0;
					
					while ($devices = mysqli_fetch_array($registro))
					{
					
						
			
						// Mostra os funcionários
						$sql1 = "SELECT * FROM ".DATABASE.".empresas ";
						$sql1 .= "WHERE id_empresa = '" .$devices["id_cliente"] . "' ";
						
						$registros = $db->select($sql1,'MYSQL');
						
						$empresa = mysqli_fetch_array($registros);
						
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
						    <?= $empresa["empresa"] ?>
					      </div></td>
						  <td width="14%"><div align="center"><?= $devices["cd_dispositivo"] ?></div></td>
						  <td width="20%"><div align="center"><?= $devices["ds_servico"] ?></div></td>
						  <td width="18%"><div align="center"><?= $devices["ds_fabricante"] ?></div></td>
						  <td width="19%"><div align="center"><?= $devices["ds_modelo"] ?></div></td>
						  <td width="3%"><div align="center"> <a href="javascript:editar('<?= $devices["id_devices"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="4%"><div align="center"> <a href="javascript:excluir('<?= $devices["id_devices"] ?>','<?= $devices["cd_dispositivo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
					}
					// Libera a memória
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