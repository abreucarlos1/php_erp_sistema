<?
/*

		Formul�rio de DEFINI��O DE EQUIPES	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../os/definirequipe.php
		
		data de cria��o: 26/08/2005
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Atualização LAYOUT - 31/03/2006
		Versão 2 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
		
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

switch ($_POST["acao"])
{
	
	// Caso a��o seja salvar...
	case 'salvar':
		
		// Seleciona os m�dulos cadastrados
		$sql_area = "SELECT * FROM Projetos.area ";
		
		$reg_area = $db->select($sql_area,'MYSQL');

		while ($area = mysqli_fetch_array($reg_area))
			{
				
				if($_POST[$area["nr_area"]])
				{
					
					$temp = explode("#",$_POST["os"]); 
					
					$sql_areas ="SELECT * FROM Projetos.area ";
					$sql_areas .= "WHERE nr_area = '" . $area["nr_area"] . "' ";
					
					$reg_areas = $db->select($sql_areas,'MYSQL');
					
					$areas = mysqli_fetch_array($reg_areas);					
									
					$inc_area = "INSERT INTO Projetos.area (id_os, ds_projeto, nr_area, ds_area, ds_divisao, id_cliente) ";
					$inc_area .= "VALUES ('" . $temp[0] . "','" .$temp["1"] . "', ";
					$inc_area .= "'" . $areas["nr_area"] . "','" . $areas["ds_area"] . "', ";
					$inc_area .= "'" . $areas["ds_divisao"] . "', '" . $areas["id_cliente"] . "' )";
										
					$reg0 = $db->insert($inc_area,'MYSQL');
					
					$iarea = $db->insert_id;
					
					$sql_locais = "SELECT * FROM Projetos.locais ";					
					$sql_locais .= "WHERE id_area = '" . $areas["id_area"] . "' ";
					
					$reg_locais = $db->select($sql_locais,'MYSQL');
					
					while($locais = mysqli_fetch_array($reg_locais))
					{
						$inc_locais = "INSERT INTO Projetos.locais (id_area, id_disciplina, id_equipamento, id_classepressao, ";
						$inc_locais .= " nr_sequencia, cd_trecho, ds_complemento, nr_elevacao, nr_eixox, nr_eixoy, ";
						$inc_locais .= " ds_abrigado, id_classearea, ds_descricao, cd_localizacao, nr_capacidade, nr_pressao, ";
						$inc_locais .= " nr_temperatura, nr_densidade, nr_viscosidade, nr_condutividade, nr_vazao, nr_altura, ";
						$inc_locais .= " ds_npsh, nr_revisao, nr_diametro, id_fluido, id_material, ds_trecho, ";
						$inc_locais .= " ds_inicio, ds_fim, ds_fluxograma, ds_isometrico, nr_isolamento) ";
						$inc_locais .= " VALUES ('" . $iarea . "','" .$locais["id_disciplina"] . "', '" .$locais["id_equipamento"] . "', '" .$locais["id_classepressao"] . "', ";
						$inc_locais .= " '" .$locais["nr_sequencia"] . "', '" .$locais["cd_trecho"] . "', '" .$locais["ds_complemento"] . "', '" .$locais["nr_elevacao"] . "', ";
						$inc_locais .= " '" .$locais["nr_eixox"] . "', '" .$locais["nr_eixoy"] . "', '" .$locais["ds_abrigado"] . "', '" .$locais["id_classearea"] . "', ";
						$inc_locais .= " '" .$locais["ds_descricao"] . "', '" .$locais["cd_localizacao"] . "', '" .$locais["nr_capacidade"] . "', '" .$locais["nr_pressao"] . "', ";
						$inc_locais .= " '" .$locais["nr_temperatura"] . "', '" .$locais["nr_densidade"] . "', '" .$locais["nr_viscosidade"] . "', '" .$locais["nr_condutividade"] . "', ";
						$inc_locais .= " '" .$locais["nr_vazao"] . "', '" .$locais["nr_altura"] . "', '" .$locais["ds_npsh"] . "', '" .$locais["nr_revisao"] . "', '" .$locais["nr_diametro"] . "', ";
						$inc_locais .= " '" .$locais["id_fluido"] . "', '" .$locais["id_material"] . "', '" .$locais["ds_trecho"] . "', '" .$locais["ds_inicio"] . "',  '" .$locais["ds_fim"] . "',";
						$inc_locais .= " '" .$locais["ds_fluxograma"] . "', '" .$locais["ds_isometrico"] . "', '" .$locais["nr_isolacao"] . "') ";
					
						$reg1 = $db->insert($inc_locais,'MYSQL');
					
						$ilocal = $db->insert_id;
						
						$local_var[$locais["id_local"]] = $ilocal;
						
						$sql_racks ="SELECT * FROM Projetos.racks ";
						$sql_racks .= "WHERE id_local = '".$locais["id_local"]."' ";
						
						$reg_racks = $db->select($sql_racks,'MYSQL');
						
						while($racks = mysqli_fetch_array($reg_racks))
						{
							$inc_racks = "INSERT INTO Projetos.racks (id_local, id_devices, nr_rack, cd_fabricante, nr_capacidade, nr_revisao) ";
							$inc_racks .= " VALUES('" .$ilocal. "', '".$racks["id_devices"]."', '".$racks["nr_rack"]."', ";
							$inc_racks .= " '".$racks["cd_fabricante"]."', '".$racks["nr_capacidade"]."', '".$racks["nr_revisao"]."') ";
							
							$reg2 = $db->insert($inc_racks,'MYSQL');
						
							$iracks = $db->insert_id;
							
							$sql_slots ="SELECT * FROM Projetos.slots ";
							$sql_slots .= "WHERE id_racks = '".$racks["id_racks"]."' ";
							
							$reg_slots = $db->select($sql_slots,'MYSQL');
							
							while($slots = mysqli_fetch_array($reg_slots))
							{
								$inc_slots = "INSERT INTO Projetos.slots (id_racks, id_cartoes, nr_slot, nr_serie, nr_cspc, nr_revisao) ";
								$inc_slots .= " VALUES('" .$iracks. "', '".$slots["id_cartoes"]."', '".$slots["nr_slot"]."', ";
								$inc_slots .= " '".$slots["nr_serie"]."', '".$slots["nr_cspc"]."', '".$slots["nr_revisao"]."') ";
								
								$reg3 = $db->insert($inc_slots,'MYSQL');
								
								$islots = $db->insert_id;
								
								$slots_var[$slots["id_slots"]] = $islots;

							}
						}				
					
					}
					
					$sql_sub ="SELECT * FROM Projetos.subsistema ";
					$sql_sub .= "WHERE id_area = '" . $areas["id_area"] . "' ";
					
					$reg_sub = $db->select($sql_sub,'MYSQL');
					
					while($sub = mysqli_fetch_array($reg_sub))
					{				
						$inc_sub = "INSERT INTO Projetos.subsistema (id_area, nr_subsistema, subsistema) ";
						$inc_sub .= "VALUES ('" . $iarea . "','" .$sub["nr_subsistema"] . "', ";
						$inc_sub .= "'" . $sub["subsistema"] . "') ";
						
						$reg4 = $db->insert($inc_sub,'MYSQL');
					
						$isubsistema = $db->insert_id;
						
						$subs_var[$sub["id_subsistema"]] = $isubsistema;
						
						$sql_lival = "SELECT * FROM Projetos.lista_valvulas WHERE id_subsistema = '" .$sub["id_subsistema"] . "' ";
						
						$reg_lival = $db->select($sql_lival,'MYSQL');
						
						while($lival = mysqli_fetch_array($reg_lival))
						{
							$inc_lival = "INSERT INTO Projetos.lista_valvulas (id_subsistema, id_equipamento, id_valvula, id_classepressao, ";
							$inc_lival .= " nr_sequencia, nr_diametro, id_acionamento, id_conexao, id_norma, ds_tag_cliente, ";
							$inc_lival .= " id_linha, ds_tie_in, nr_revisao ) ";
							$inc_lival .= " VALUES ('" . $isubsistema . "','" .$lival["id_equipamento"] . "', '" .$lival["id_valvula"] . "', ";
							$inc_lival .= " '" .$lival["id_classepressao"] . "', '" .$lival["nr_sequencia"] . "', '" .$lival["nr_diametro"] . "', ";
							$inc_lival .= " '" .$lival["id_acionamento"] . "', '" .$lival["id_conexao"] . "','" .$lival["id_norma"] . "', ";
							$inc_lival .= " '" .$lival["ds_tag_cliente"] . "', '" .$local_var[$lival["id_linha"]] . "', '" .$lival["ds_tie_in"] . "', ";
							$inc_lival .= " '" .$lival["nr_revisao"] . "') ";
						
							$reg5 = $db->insert($inc_lival,'MYSQL');
						}
						
						
						$sql_lisup = "SELECT * FROM Projetos.lista_suportes WHERE id_subsistema = '" .$sub["id_subsistema"] . "' ";
						
						$reg_lisup = $db->select($sql_lisup,'MYSQL');
						
						while($lisup = mysqli_fetch_array($reg_lisup))
						{
							$inc_lisup = "INSERT INTO Projetos.lista_suportes (id_subsistema, id_suporte, cd_posicao, cd_tag, ";
							$inc_lisup .= " nr_elevacao, nr_quantidade, nr_h, nr_l, nr_a, nr_b, ";
							$inc_lisup .= " id_linha, nr_c, ds_planta, nr_revisao ) ";
							$inc_lisup .= " VALUES ('" . $isubsistema . "','" .$lisup["id_suporte"] . "', '" .$lisup["cd_posicao"] . "', ";
							$inc_lisup .= " '" .$lisup["cd_tag"] . "', '" .$lisup["nr_elevacao"] . "', '" .$lisup["nr_quantidade"] . "', ";
							$inc_lisup .= " '" .$lisup["nr_h"] . "', '" .$lisup["nr_l"] . "','" .$lisup["nr_a"] . "', ";
							$inc_lisup .= " '" .$lisup["nr_b"] . "', '" .$local_var[$lisup["id_linha"]] . "', '" .$lisup["nr_c"] . "', ";
							$inc_lisup .= " '" .$lisup["ds_planta"] . "', '" .$lisup["nr_revisao"] . "') ";
						
							$reg6 = $db->insert($inc_lisup,'MYSQL');
						}
						
						
						$sql_malha ="SELECT * FROM Projetos.malhas ";
						$sql_malha .= "WHERE id_subsistema = '" . $sub["id_subsistema"] . "' ";
						
						$reg_malha = $db->select($sql_malha,'MYSQL');
						
						while($malha = mysqli_fetch_array($reg_malha))
						{				
							$inc_malha = "INSERT INTO Projetos.malhas (id_subsistema, id_processo, nr_malha, tp_malha, ds_servico) ";
							$inc_malha .= "VALUES ('" . $isubsistema . "','" .$malha["id_processo"] . "', ";
							$inc_malha .= "'" . $malha["nr_malha"] . "', '" . $malha["tp_malha"] . "', '" . $malha["ds_servico"] . "' ) ";
							
							$reg7 = $db->insert($inc_malha,'MYSQL');
						
							$imalha = $db->insert_id;
							
							$sql_comp ="SELECT * FROM Projetos.componentes ";
							$sql_comp .= "WHERE id_malha = '" . $malha["id_malha"] . "' ";
							
							$reg_comp = $db->select($sql_comp,'MYSQL');
							
							while($comp = mysqli_fetch_array($reg_comp))
							{				
								$inc_comp = "INSERT INTO Projetos.componentes (id_malha, id_funcao, id_local, id_dispositivo, id_tipo, cd_tag_eq) ";
								$inc_comp .= "VALUES ('" . $imalha . "','" .$comp["id_funcao"] . "', '" .$comp["id_local"] . "', ";
								$inc_comp .= "'" . $comp["id_dispositivo"] . "', '" . $comp["id_tipo"] . "', '" . $comp["cd_tag_eq"] . "' ) ";
								
								$reg8 = $db->insert($inc_comp,'MYSQL');
							
								$icomp = $db->insert_id;
								
								$comp_var[$comp["id_componente"]] = $icomp;								
								
								$sql_tec ="SELECT * FROM Projetos.especificacao_tecnica ";
								$sql_tec .= "WHERE id_componente = '" . $comp["id_componente"] . "' ";
								
								$reg_tec = $db->select($sql_tec,'MYSQL');
								
								while($tec = mysqli_fetch_array($reg_tec))
								{				
									$inc_tec = "INSERT INTO Projetos.especificacao_tecnica (id_componente, id_especificacao_padrao) ";
									$inc_tec .= "VALUES ('" . $icomp . "','" .$tec["id_especificacao_padrao"] . "')";
									
									$reg9 = $db->insert($inc_tec,'MYSQL');
								
									$iespec = $db->insert_id;
									
									$sql_det ="SELECT * FROM Projetos.especificacao_tecnica_detalhes ";
									$sql_det .= "WHERE id_especificacao_tecnica = '" . $tec["id_especificacao_tecnica"] . "' ";
									
									$reg_det = $db->select($sql_det,'MYSQL');
									
									while($det = mysqli_fetch_array($reg_det))
									{				
										$inc_det = "INSERT INTO Projetos.especificacao_tecnica_detalhes (id_especificacao_tecnica, id_especificacao_detalhe, conteudo ) ";
										$inc_det .= "VALUES ('" . $iespec . "','" .$det["id_especificacao_detalhe"] . "', '" .$det["conteudo"] . "' )";
										
										$reg10 = $db->insert($inc_det,'MYSQL');
									}
								
								}
													
							}
											
						}					
					}
								
					
					$sql_sub ="SELECT * FROM Projetos.subsistema ";
					$sql_sub .= "WHERE id_area = '" . $areas["id_area"] . "' ";
					
					$reg_sub = $db->select($sql_sub,'MYSQL');
					
					while($regs_sub = mysqli_fetch_array($reg_sub))
					{
						$sql_cabos = "SELECT * FROM Projetos.cabos ";
						$sql_cabos .= "WHERE id_subsistema = '".$regs_sub["id_subsistema"]."' ";
						
						$reg_cabos = $db->select($sql_cabos,'MYSQL');
						
						while($regs_cabos = mysqli_fetch_array($reg_cabos))
						{
							$inc_cabos = "INSERT INTO Projetos.cabos (id_subsistema, id_componente, ds_diferencial, id_cabo_tipo, ds_origem, id_local_origem, ";
							$inc_cabos .= "	id_destino, id_local_destino, nr_comprimento, ds_rotas, ds_observacao) ";
							$inc_cabos .= " VALUES('" .$subs_var[$regs_cabos["id_subsistema"]]. "', '".$comp_var[$regs_cabos["id_componente"]]."', '".$regs_cabos["ds_diferencial"]."', ";
							$inc_cabos .= " '".$regs_cabos["id_cabo_tipo"]."', '".$regs_cabos["ds_origem"]."', '".$local_var[$regs_cabos["id_local_origem"]]."', ";
							$inc_cabos .= " '".$comp_var[$regs_cabos["id_destino"]]."', '".$local_var[$regs_cabos["id_local_destino"]]."', '".$regs_cabos["nr_comprimento"]."', ";
							$inc_cabos .= " '".$regs_cabos["ds_rotas"]."', '".$regs_cabos["ds_observacao"]."') ";
							
							$reg11 = $db->insert($inc_cabos,'MYSQL');
						}
					}
					
					$sql_locais = "SELECT * FROM Projetos.locais ";
					$sql_locais .= "WHERE id_area = '".$areas["id_area"]."' ";
					
					$reg_locais = $db->select($sql_locais,'MYSQL');
					
					while($regs_locais = mysqli_fetch_array($reg_locais))
					{
						$sql_racks ="SELECT * FROM Projetos.racks ";
						$sql_racks .= "WHERE id_local = '".$regs_locais["id_local"]."' ";
						
						$reg_racks = $db->select($sql_racks,'MYSQL');
						
						while($regs_racks = mysqli_fetch_array($reg_racks))
						{
							$sql_slots ="SELECT * FROM Projetos.slots ";
							$sql_slots .= "WHERE id_racks = '".$regs_racks["id_racks"]."' ";
							
							$reg_slots = $db->select($sql_slots,'MYSQL');
							
							while($regs_slots = mysqli_fetch_array($reg_slots))
							{
								$sql_end ="SELECT * FROM Projetos.enderecos ";
								$sql_end .= "WHERE id_slots = '".$regs_slots["id_slots"]."' ";
								
								$reg_end = $db->select($sql_end,'MYSQL');
								
								while($regs_end = mysqli_fetch_array($reg_end))
								{
									
									$inc_end = "INSERT INTO Projetos.enderecos (id_slots, cd_endereco, cd_atributo, nr_canal, id_componente) ";
									$inc_end .= " VALUES('" .$slots_var[$regs_end["id_slots"]]. "', '".$regs_end["cd_endereco"]."', '".$regs_end["cd_atributo"]."', ";
									$inc_end .= " '".$regs_end["nr_canal"]."', '".$comp_var[$regs_end["id_componente"]]."') ";
									
									$reg12 = $db->insert($inc_end,'MYSQL');						
									
								}					
							}				
						
						}			
					}
						
				
				}
				
				
			}
	
	break;

}

//Exclui o registro do banco de dados - Desativado.
 
?>

<html>
<head>
<title>: : . IMPORTAR �REA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<!-- Javascript para declara��o de vari�veis / checagem do estilo - MAC/PC -->

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>


//Fun��o para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}

// Altera o status do objeto
function altera()
{
	// Atribui a variavel incluir o valor editar e envia o formulario
	document.forms["frm_areas"].acao.value = 'editar';
	document.frm_areas.submit();
	
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body">
<center>
<form name="frm_areas" id="frm_areas" method="post" action="<?= $PHP_SELF ?>">
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
		  <table width="100%" class="corpo_tabela">
				<tr>
				  <td class="label1">&nbsp;</td>
				  <td class="label1">
				  <table width="100%" border="0">
                    <tr>
                      <td width="10%" class="label1">CLIENTE</td>
                      <td width="1%">&nbsp;</td>
                      <td width="29%" class="label1">&nbsp;</td>
                      <td width="1%" class="label1">&nbsp;</td>
                      <td width="59%" class="label1">&nbsp;</td>
                    </tr>
                    <tr>
                      <td>
					  <select name="cliente" class="txt_box"  id="cliente" onChange="altera()" onkeypress="return keySort(this);">
                        <option value="" selected>SELECIONE</option>
                        <?

							$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".OS, Projetos.area ";
							$sql .= "WHERE empresas.id_empresa_erp = OS.id_empresa_erp ";
							$sql .= "AND OS.id_os = area.id_os ";
							$sql .= "GROUP BY empresas.empresa ";
							
							$registro = $db->select($sql,'MYSQL');
							// Preenche o combobox com os setores
							
							while ($regs = mysqli_fetch_array($registro))
							{
								?>
                        		<option value="<?= $regs["id_empresa_erp"] ?>" <? if($regs["id_empresa_erp"]==$_POST["cliente"]) { echo "selected"; } ?>>
                          		<?= $regs["empresa"] ?>
                          		</option>
                        		<?
							}
						
							?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
				  </td>
				  </tr>
				<tr>
				  <td class="label1">&nbsp;</td>
				  <td class="label1">
				  <table width="100%" border="0">
				  <tr class="label1">
				  <td>OS - DESTINO </td>
				  </tr>
				  
				  <tr>
				  <td><select name="os" class="txt_box"  id="os" <? if(!$_POST["cliente"]){ echo 'disabled'; }  ?> onkeypress="return keySort(this);">
                    <option value="" selected>SELECIONE</option>
                    <?
							
							$sql = "SELECT * FROM ".DATABASE.".OS ";
							$sql .= "WHERE OS.id_empresa_erp NOT LIKE '".$_POST["cliente"]. "' ";
							$sql .= "ORDER BY OS ";
							
							$registro = $db->select($sql,'MYSQL');
							// Preenche o combobox com os setores
							
							while ($regs = mysqli_fetch_array($registro))
							{
								?>
                    <option value="<?= $regs["id_os"]."#". $regs["descricao"] ?>" <? if($regs["id_os"]==$_POST["os"]) { echo "selected"; } ?>>
                    <?= $regs["os"]." - ". $regs["descricao"] ?>
                    </option>
                    <?
							}
						
							?>
                  </select>				  </td>
				  </tr>
				  </table>
				  
				  <table width="100%" border="0">
				  <?
						
						$sql1 = "SELECT * FROM Projetos.area, ".DATABASE.".OS ";
						$sql1 .= "WHERE id_empresa_erp ='" . $_POST["cliente"] . "' ";
						$sql1 .= "AND OS.id_os = area.id_os ";
						$sql1 .= "GROUP BY nr_area ";
						
						$registro = $db->select($sql1,'MYSQL');
						// Preenche o checkbox
						$c = 0;
						while ($regs = mysqli_fetch_array($registro))
							{

								if($c%2)
								{
								?>
								  
								  
								  <td width="50%" class="label1"><input name="<?= $regs["nr_area"] ?>" type="checkbox" id="tag" value=1>
								  <input name="text" type="text" class="txt_box" value="<?= $regs["nr_area"]." - ".$regs["ds_area"] ?>" size="80" readonly="yes">&nbsp;</td>					  				
								  
								  </tr>	
								<?
								}
								else
								{
								?>
								  <tr>
								  <td width="50%" class="label1"><input name="<?= $regs["nr_area"] ?>" type="checkbox" id="tag" value=1>
								  <input name="text" type="text" class="txt_box" value="<?= $regs["nr_area"]." - ".$regs["ds_area"] ?>" size="80" readonly="yes" >&nbsp;</td>					  				
								  <?								
								}
								$c++;
							}
												
											
				  ?>
                  </table>
				  </td>
				  </tr>
				<tr>
				  <td width="3%" class="label1">&nbsp;</td>
				  <td class="label1">
				  <input name="acao" id="acao" type="hidden" value="salvar">
                  <input name="Incluir" type="submit" class="btn" id="Incluir" value="IMPORTAR">
                  <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onClick="javascript:window.close()">				  </td>
				  </tr>
			  </table>
		</td>
      </tr>
      <tr>
        <td>

		</td>
  </tr>
</table>

</form>
</center>
</body>
</html>