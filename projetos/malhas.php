<?php
/*

		Formulário de MALHAS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/malhas.php
		
		data de criação: 05/04/2006
		
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
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	$sql = "SELECT * FROM Projetos.malhas ";
	$sql .= "WHERE id_processo = '".$_POST["id_processo"] . "' ";
	$sql .= "AND nr_malha = '".$_POST["nr_malha"] . "' ";
	$sql .= "AND nr_malha_seq = '".$_POST["nr_malha_seq"] . "' ";
	$sql .= "AND tp_malha = '".$_POST["tp_malha"]. "' ";
	$sql .= "AND new_malha = '".$_POST["new_malha"]. "' ";
	$sql .= "AND id_subsistema = '".$_POST["id_subsistema"]. "' ";
	$sql .= "AND ds_servico = '".maiusculas($_POST["ds_servico"]) . "' ";
	$sql .= "AND id_malha <> '".$_POST["id_malha"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Malha já cadastrada no banco de dados.');
			</script>
			<?php
		}
	else
		{

		
			$sql = "UPDATE Projetos.malhas SET ";
			$sql .= "id_processo = '" . $_POST["id_processo"] . "', ";
			$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "', ";
			$sql .= "nr_malha = '".$_POST["nr_malha"] . "', ";
			$sql .= "nr_malha_seq = '".$_POST["nr_malha_seq"] . "', ";
			$sql .= "new_malha = '" . $_POST["new_malha"] . "', ";
			$sql .= "ds_servico = '" . maiusculas($_POST["ds_servico"]) . "' ";
			$sql .= "WHERE id_malha = '" . $_POST["id_malha"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
		
			?>
			<script>
				alert('Malha atualizada com sucesso.');
			</script>
			<?php
	}
}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$sql = "SELECT * FROM Projetos.malhas ";
	$sql .= "WHERE id_processo = '".$_POST["id_processo"] . "' ";
	$sql .= "AND nr_malha = '".$_POST["nr_malha"] . "' ";
	$sql .= "AND nr_malha_seq = '".$_POST["nr_malha_seq"] . "' ";
	$sql .= "AND id_subsistema = '" . $_POST["id_subsistema"] . "' ";
	
	$verify = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	if ($regs>0)
		{
			?>
			<script>
				alert('Malha já cadastrada no banco de dados.');
			</script>
			<?php
		}
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.malhas ";
			$isql .= "(id_subsistema, id_processo, nr_malha, nr_malha_seq, tp_malha, new_malha, ds_servico) ";
			$isql .= "VALUES ('" . $_POST["id_subsistema"] . "', '" . $_POST["id_processo"] ."', ";
			$isql .= "'" . $_POST["nr_malha"] . "', '" . $_POST["nr_malha_seq"] . "', '" . $_POST["tp_malha"] . "', ";
			$isql .= "'" . $_POST["new_malha"] . "', '" . maiusculas($_POST["ds_servico"]) . "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			?>
			<script>
				alert('Malha inserida com sucesso.');
			</script>
			<?php

		}
}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	
	$dsql = "DELETE FROM Projetos.malhas WHERE id_malha = '".$_GET["id_malha"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	$dsql = "DELETE FROM Projetos.componentes WHERE id_malha = '".$_GET["id_malha"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Malha excluída com sucesso.');
	</script>
	<?php
}


?>

<html>
<head>
<title>: : . MALHAS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_malha, ds_malha)
{
	if(confirm('Tem certeza que deseja excluir a malha '+ds_malha+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_malha='+id_malha+'';
	}
}

function editar(id_malha)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_malha='+id_malha+'';
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
<form name="frm_malhas" method="post" action="<?= $PHP_SELF ?>">
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
				$sql = "SELECT * FROM Projetos.malhas WHERE id_malha= '" . $_GET["id_malha"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$malhas = mysqli_fetch_array($registro); 	
			 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="10%"><span class="label1">subsistema</span></td>
                      <td width="0%"> </td>
                      <td width="9%" class="label1">processo</td>
                      <td width="0%" class="label1"> </td>
                      <td width="8%" class="label1">nr. malha </td>
                      <td width="2%" class="label1"> </td>
                      <td width="13%" class="label1">DIFERENCIADOR</td>
                      <td width="2%" class="label1"> </td>
                      <td width="13%" class="label1">tipo malha </td>
                      <td width="0%" class="label1"> </td>
                      <td width="43%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td height="44"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_subsistema" class="txt_box" id="id_subsistema" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
						  	$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
							$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"]. "' ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "ORDER BY nr_subsistema, subsistema ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          			<option value="<?= $regs["id_subsistema"] ?>"<?php if($regs["id_subsistema"]==$malhas["id_subsistema"]){ echo 'selected';} ?>>
                            		<?= $regs["nr_area"] . " - " .$regs["nr_subsistema"] . " - " . $regs["subsistema"] ?>
                            		</option>
                          			<?php
								}
						  ?>
                        </select>
                      </font></td>
                      <td> </td>
                      <td><select name="id_processo" id="id_processo" class="txt_box" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
									
									//Popula a combo-box de Função.
									$sql = "SELECT * FROM Projetos.processo ";
									//$sql .= "GROUP BY ds_processo ";
									$sql .= "ORDER BY ds_processo ";
									
									$regdescricao = $db->select($sql,'MYSQL');
									
									while ($reg = mysqli_fetch_array($regdescricao))
									{
										?>
										<option value="<?= $reg["id_processo"] ?>"<?php if ($malhas["id_processo"]==$reg["id_processo"]){ echo 'selected';}?>>
										<?= $reg["processo"] . " - " . $reg["ds_processo"] . " - " . $reg["norma"] ?>
										</option>
										<?php
									}
								
								
							?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_malha" type="text" class="txt_box" id="nr_malha" size="20" value="<?= $malhas["nr_malha"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_malha_seq" type="text" class="txt_box" id="nr_malha_seq" size="20" value="<?= $malhas["nr_malha_seq"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="tp_malha" class="txt_box" id="tp_malha" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
						  	$sql = "SELECT * FROM Projetos.tipos ";
							$sql .= "ORDER BY ds_tipo ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["tipo"] ?>"<?php if($regs["tipo"]==$malhas["tp_malha"]){ echo 'selected';} ?>>
                          <?= $regs["ds_tipo"] ?>
                          </option>
                          <?php
								}
							?>
                        </select>
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
                      <td width="21%"><span class="label1">serviÇO</span></td>
                      <td width="3%"> </td>
                      <td width="58%">NOVA MALHA </td>
                      <td width="9%"> </td>
                      <td width="9%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_servico" type="text" class="txt_box" id="ds_servico" value="<?= $malhas["ds_servico"]?>" size="50" maxlength="50" >
                      </font></td>
                      <td> </td>
                      <td><input name="new_malha" type="checkbox" id="new_malha" value="1" <?php if($malhas["new_malha"]){ echo 'checked';} ?>></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_malha" type="hidden" id="id_malha" value="<?= $malhas["id_malha"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Inserir4" type="button" class="btn" id="Inserir22" value="VOLTAR" onclick="javascript:history.back();"></td>
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
                      <td width="10%"><span class="label1">subsistema</span></td>
                      <td width="0%"> </td>
                      <td width="9%" class="label1">PROCESSO</td>
                      <td width="0%" class="label1"> </td>
                      <td width="8%" class="label1">Nº malha </td>
                      <td width="2%" class="label1"> </td>
                      <td width="12%" class="label1">DIFERENCIADOR</td>
                      <td width="2%" class="label1"> </td>
                      <td width="10%" class="label1">TIPO MALHA  </td>
                      <td width="1%" class="label1"> </td>
                      <td width="46%" class="label1"> </td>
                    </tr>
                    <tr>
                      <td height="44"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_subsistema" class="txt_box" id="id_subsistema" onkeypress="return keySort(this);">
						<option value="">SELECIONE</option>
                          <?php
						  	$sql = "SELECT * FROM Projetos.subsistema, Projetos.area ";
							$sql .= "WHERE area.id_os = '" .$_SESSION["id_os"]. "' ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "ORDER BY nr_subsistema, subsistema";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
									<option value="<?= $regs["id_subsistema"] ?>"<?php if($regs["id_subsistema"]==$_POST["id_subsistema"]){ echo 'selected';}?>><?= $regs["nr_area"] . " - " .$regs["nr_subsistema"] . " - " . $regs["subsistema"] ?></option>
									<?php
								}
							?>
                         </select>
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_processo" id="id_processo" class="txt_box" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
									
									//Popula a combo-box de Função.
									$sql = "SELECT * FROM Projetos.processo ";
									//$sql .= "GROUP BY ds_processo ";
									$sql .= "ORDER BY ds_processo ";
									
									$regdescricao = $db->select($sql,'MYSQL');
									
									while ($reg = mysqli_fetch_array($regdescricao))
									{
											?>
										  <option value="<?= $reg["id_processo"] ?>"<?php if ($_POST["id_processo"]==$reg["id_processo"]){ echo 'selected';}?>>
										  <?= $reg["processo"] . " - " . $reg["ds_processo"] . " - " . $reg["norma"] ?>
										  </option>
										  <?php
									}
								
								
							?>
                        </select>
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_malha" type="text" class="txt_box" id="nr_malha" size="20" value="<?= $_POST["nr_malha"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_malha_seq" type="text" class="txt_box" id="nr_malha_seq" size="20" value="<?= $_POST["nr_malha_seq"] ?>">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="tp_malha" class="txt_box" id="tp_malha" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
						  	$sql = "SELECT * FROM Projetos.tipos ";
							$sql .= "ORDER BY ds_tipo ";
							
							$reg = $db->select($sql,'MYSQL');
							
							while ($regs = mysqli_fetch_array($reg))
								{
									?>
                          <option value="<?= $regs["tipo"] ?>"<?php if($regs["tipo"]==$_POST["tp_malha"]){ echo 'selected'; }?>>
                            <?= $regs["ds_tipo"] ?>
                          </option>
                          <?php
								}
							?>
                        </select>
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
                      <td width="21%"><span class="label1">SERVIÇO</span></td>
                      <td width="3%"> </td>
                      <td width="13%">nova malha </td>
                      <td width="1%"> </td>
                      <td width="62%"> </td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_servico" type="text" class="txt_box" id="ds_servico" value="<?= $_POST["ds_servico"] ?>" size="50" maxlength="50">
                      </font></td>
                      <td> </td>
                      <td><input name="new_malha" type="checkbox" id="new_malha" value="1" checked ></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="INSERIR">
                    <input name="Inserir2" type="button" class="btn" id="Inserir2" value="VOLTAR" onclick="javascript:history.back();">
                    <input name="Inserir3" type="button" class="btn" id="Inserir3" value="COMPONENTES" onclick="javascript:location.href='componentes.php'">
                    <input name="Inserir4" type="button" class="btn" id="Inserir4" value="EQUIPAMENTOS" onclick="javascript:location.href='equipamentos.php'"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td class="label1">regs:<font size="2" face="Arial, Helvetica, sans-serif">
                    <?php
							$sql = "SELECT * FROM Projetos.malhas, Projetos.processo, Projetos.subsistema, Projetos.tipos, Projetos.area ";
							$sql .= "WHERE malhas.id_subsistema=subsistema.id_subsistema ";
							$sql .= "AND malhas.tp_malha=tipos.tipo ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "GROUP BY malhas.id_malha ";
							
							$regs = $db->select($sql,'MYSQL');
							
							$regcounter = $db->numero_registros;
							
							echo $regcounter;
						?>

                  </font></td>
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
						$campo = "nr_subsistema, processo, nr_malha, nr_malha_seq ";
					}
					else
					{
						$campo = $_GET["campo"];
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
				  <td width="17%"><a href="#" class="cabecalho_tabela" onclick="ordenar('nr_subsistema','<?= $ordem ?>')">SUBSISTEMA</a></td>
				  <td width="14%"><a href="#" class="cabecalho_tabela" onclick="ordenar('processo','<?= $ordem ?>')">PROCESSO</a></td>
				  <td width="11%"><a href="#" class="cabecalho_tabela" onclick="ordenar('nr_malha','<?= $ordem ?>')">Nº MALHA </a></td>
				  <td width="13%"><a href="#" class="cabecalho_tabela" onclick="ordenar('tp_malha','<?= $ordem ?>')">TIPO MALHA </a></td>
				  <td width="33%"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_servico','<?= $ordem ?>')">SERVIÇO</a></td>
				  <td width="6%"><a href="#" class="cabecalho_tabela" onclick="ordenar('new_malha','<?= $ordem ?>')">NOVA</a></td>
				  <td width="2%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
			
					// Mostra os funcionários
					
					$sql = "SELECT * FROM Projetos.malhas, Projetos.processo, Projetos.subsistema, Projetos.tipos, Projetos.area ";
					$sql .= "WHERE malhas.id_subsistema=subsistema.id_subsistema ";
					$sql .= "AND malhas.tp_malha=tipos.tipo ";
					$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
					$sql .= "AND subsistema.id_area = area.id_area ";
					$sql .= "AND malhas.id_processo = processo.id_processo ";
					$sql .= "GROUP BY malhas.id_malha ";
					$sql .= "ORDER BY '" . $campo ."' ".$ordem.", nr_malha ";

					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($malhas = mysqli_fetch_array($registro))
					{
						/*
							acrescenta zeros a esquerda
						*/
						if($malhas["processo"]!='D')
						{
							$nrmalha = sprintf("%03d",$malhas["nr_malha"]);
						}
						else
						{
							$nrmalha = $malhas["nr_malha"];
						}
						
						if($malhas["nr_malha_seq"]!='')
						{
							$nrseq = '.'.$malhas["nr_malha_seq"];
						}
						else
						{
							$nrseq = ' ';
						}
						if($nrseq == '.0')
						{
							$nrseq=' ';
						}
						
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
						  <td width="17%"><div align="center"><?= $malhas["nr_subsistema"]." - ".$malhas["subsistema"] ?></div></td>
						  <td width="14%"><div align="center"><?= $malhas["processo"]." - ".$malhas["ds_processo"] ?></div></td>
						  <td width="11%"><div align="center">
						    <?= $nrmalha.$nrseq ?>
					      </div></td>
						  <td width="13%"><div align="center">
                            <?= $malhas["ds_tipo"] ?>
                          </div></td>
						  <td width="33%"><div align="center"><?= $malhas["ds_servico"] ?></div></td>
						  
						  <td width="6%"><div align="center"><?php if($malhas["new_malha"]){?><img src="../images/buttons/aprovado.gif" width="16" height="16" border="0"><?php
						 } ?> </div></td>
						  <td width="3%"><div align="center">
						 <a href="#" onclick="editar('<?= $malhas["id_malha"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>						  
						 </div></td> 
					      <td width="3%"><div align="center"> <a href="#" onclick="excluir('<?= $malhas["id_malha"] ?>','<?= str_replace('"','',$malhas["ds_servico"]) ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
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
