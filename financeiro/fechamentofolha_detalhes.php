<?php
/*
	  Formulário de Detalhes do Fechamento da Folha	
	  
	  Criado por Carlos Abreu / Otávio Pamplona
	  
	  local/Nome do arquivo:
	  ../financeiro/fechamentofolha_detalhes.php
	  
	  Versão 0 --> VERSÃO INICIAL - 03/04/2006
	  Versão 1 --> Atualização da classe banco - 20/01/2015 - Carlos Abreu
	  Versão 2 --> Alteração layout - 21/07/2016 - Carlos Abreu
	  Versão 3 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");


//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(308))
{
	nao_permitido();
}

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND fechamento_folha.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_funcoes.reg_del = 0 ";
$sql .= "AND fechamento_folha.id_fechamento = '" . $_GET["id_fechamento"] . "' ";
$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";

$db->select($sql,'MYSQL',true);

$fechamento_folha = $db->array_select[0];

//Salario atual
$sql = "SELECT * FROM ".DATABASE.".salarios ";
$sql .= "WHERE salarios.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' ";
$sql .= "AND salarios.reg_del = 0 ";					

if($fechamento_folha["data_ini"]!="")
{
	$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) < '".str_replace("-","",$fechamento_folha["data_fim"])."' ";
}
else
{
	$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) < '".date('Ymd')."' ";
}

$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

$db->select($sql,'MYSQL',true);

$cont2 = $db->array_select[0];

?>

<html>
<head><title><?= $fechamento_folha["funcionario"] ?></title>
<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<form name="frm" id="frm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">

      <tr>
        <td>
		  <div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:no; overflow-x:no; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0">
						<tr class="cabecalho_tabela">
						  <td width="50%"><div align="center" style="display:inline;">
						    <div align="right">Funcionário:<?= $fechamento_folha["funcionario"] ?></div>
						  </div>
                          <input name="id_fechamento" type="hidden" id="id_fechamento" value="<?= $fechamento_folha["id_fechamento"] ?>">
                          </td>
						  <td width="32%"><div align="right"><span style="display:inline;">
						<input name="Alterar per&iacute;odo" type="button" class="btn_mini" id="Alterar per&iacute;odo" value="Alterar período" onClick="location.href='fechamentofolha_periodo.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>';" style="width:85px;">

						 <input name="Fechar" type="button" class="btn_mini" id="Fechar" value="Fechar" onClick="window.close();"  style="width:40px;">
					      </span></div></td>
						</tr>
			 	 </table>
						  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
								<tr>
						      <td width="15%" bgcolor="#EEEEEE"><div align="right"></div></td>
						      <td width="34%">&nbsp;</td>
					          <td width="20%" bgcolor="#EEEEEE"><div align="right"></div></td>
					          <td width="29%">&nbsp;</td>
					          <td width="2%">&nbsp;</td>
						    </tr>
							
							
							<tr>
							  <td bgcolor="#EEEEEE"><div align="right">Função: </div></td>
								  <td><?= $fechamento_folha["descricao"] ?></td>
								  <td bgcolor="#EEEEEE">&nbsp;</td>
								  <td><div align="right"></div></td>
							  <td>&nbsp;</td>
							</tr>
						
							<tr>
							  <td bgcolor="#EEEEEE"> <div align="right">Contrato: </div></td>
							  <td><?= $cont2[" tipo_contrato"] ?></td>
							  <td bgcolor="#EEEEEE">&nbsp;</td>
							  <td>&nbsp;</td>
							  <td>&nbsp;</td>
						    </tr>
								
						    <?php
							
							//Checa o tipo de contrato
							if($cont2[" tipo_contrato"]=="SC" || $cont2[" tipo_contrato"]=="SC+CLT")
							{							
								?>
								<tr>
								<td bgcolor="#EEEEEE"><div align="right">&nbsp;
								  Valor p/ Hora: </div></td>
								  <td>R$ <?= formatavalor($cont2["salario_hora"]) ?></td>
								  <td bgcolor="#EEEEEE">&nbsp;</td>
  								  <td>&nbsp;</td>
								  <td>&nbsp;</td>
  							    </tr>
								<?php
							}
							
							//Checa o tipo de contrato
							if($cont2[" tipo_contrato"]=="CLT" || $cont2[" tipo_contrato"]=="SC+CLT" || $cont2[" tipo_contrato"]=="SC+CLT+MENS" || $cont2[" tipo_contrato"]=="EST")
							{							
								?>
								  <tr>
								  <td bgcolor="#EEEEEE"><div align="right">Valor Registro: </div></td>
								  <td>R$ <?= formatavalor($cont2["salario_clt"]) ?></td>
								  <td bgcolor="#EEEEEE">&nbsp;</td>
								  <td>&nbsp;</td>
								  <td>&nbsp;</td>
								</tr>
								<?php
							}
							
							//Checa o tipo de contrato
							if($cont2[" tipo_contrato"]=="SC+CLT+MENS" || $cont2[" tipo_contrato"]=="SC+MENS")
							{
							
								?>
								<tr>
								<td bgcolor="#EEEEEE"><div align="right">&nbsp;
								  Valor Mensalista: </div></td>
								  <td>R$ <?= formatavalor($cont2["salario_mensalista"]) ?></td>								
								  <td bgcolor="#EEEEEE">&nbsp;</td>
								  <td>&nbsp;</td>
								  <td>&nbsp;</td>
								</tr>
								<?php
							}
							?>

							<tr>
						      <td bgcolor="#EEEEEE"><div align="right">T. H. N.:   </div></td>
						  <td><?= $fechamento_folha["total_horas_normais"] ?></td>
			              <td bgcolor="#EEEEEE"><div align="right">Valor_fgts</div></td>
			              <td>R$
                            <?= formatavalor($fechamento_folha["valor_fgts"]) ?></td>
			              <td>&nbsp;</td>
						    </tr>

							<tr>
							  <td bgcolor="#EEEEEE"><div align="right">T. H. A.: </div></td>
						      <td><?= $fechamento_folha["total_horas_adicionais"] ?></td>
					          <td bgcolor="#EEEEEE"><div align="right">Décimo Terceiro: </div></td>
					          <td>R$
                              <?= formatavalor($fechamento_folha["valor_decimo_terceiro"]) ?></td>
					          <td>&nbsp;</td>
						    </tr>

							<tr>
							  <td bgcolor="#EEEEEE"><div align="right">Medição: </div></td>
							  <td>R$ <?= formatavalor($fechamento_folha["valor_medicao"]) ?></td>
							  <td bgcolor="#EEEEEE"><div align="right">IR: (1,5%)</div></td>
							  <td>R$
                              <?= formatavalor($fechamento_folha["valor_imposto"]) ?></td>
							  <td>&nbsp;</td>
						    </tr>
							<tr>
							  <td bgcolor="#EEEEEE"><div align="right">Total Bruto: </div></td>
							  <td>R$ <?= formatavalor($fechamento_folha["valor_total"]) ?></td>
							  <td bgcolor="#EEEEEE"><div align="right">PIS / Cofins / CSL: (4,65%)</div></td>
							  <td>R$
                              <?= formatavalor($fechamento_folha["valor_pcc"]) ?></td>
							  <td>&nbsp;</td>
							</tr>

							<tr>
							  <td bgcolor="#EEEEEE"><div align="right">Total Líquido: </div></td>
							  <td>R$ <?= formatavalor($fechamento_folha["valor_pagamento"]) ?></td>
							  <td bgcolor="#EEEEEE">&nbsp;</td>
							  <td><?= ult_dia_mes(date('d/m/Y')) ?></td>
							  <td>&nbsp;</td>
							</tr>
							<tr>
							  <td bgcolor="#EEEEEE">&nbsp;</td>
							  <td>&nbsp;</td>
							  <td valign="middle" colspan="2"><input name="docs" type="button" class="btn" id="docs" value="Anexar Docs" onClick="window.open('../financeiro/cadastra_docs_forn.php?id_fechamento=<?= $fechamento_folha["id_fechamento"] ?>')">

                              
						      </td> 
                              
                              <td>&nbsp;</td>
						    </tr>
			  			</table>
			              <table width="100%" height="100" border="0">
                            <tr>
                              <td width="49%" height="198" valign="top"><table width="100%" border="0">
                                <tr>
                                  <td class="corpo_tabela_cinza"><div align="right">Férias: </div></td>
                                  <td class="corpo_tabela"> R$
                                  <?= formatavalor($fechamento_folha["valor_ferias"]) ?></td>
                                </tr>
                                <tr>
                                  <td width="49%" class="corpo_tabela_cinza">Dif. CLT Férias </td>
                                  <td width="51%" class="corpo_tabela_cinza">valor</td>
                                </tr>
                                <tr>
                                  <?php
							
							//Mostra os detalhes da Férias.
							$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
							$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' "; 
							$sql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
							$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.tipo = 'diferenca_clt_ferias' ";
							$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";							
							
							$db->select($sql,'MYSQL',true);
							
							foreach($db->array_select as $cont_clt_ferias)
							{
							
									?>
                                  <td class="corpo_tabela">&nbsp;
                                      <?= $cont_clt_ferias["descricao"] ?></td>
                                  <td class="corpo_tabela"><div align="center">R$
                                          <?= formatavalor($cont_clt_ferias["valor"]) ?>
                                  </div></td>
                                  <?php
								
							}
							
							//Se o valor de Férias for vazio
							if($db->numero_registros==0)
							{
								?>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                 <?php
							}
							
							?>
                                </tr>
                                <tr>
                                  <td height="1">&nbsp;</td>
                                  <td height="1">&nbsp;</td>
                                </tr>
                                <tr>
                                  <td class="corpo_tabela_cinza"><div align="right">Rescisão:</div></td>
                                  <td class="corpo_tabela"> R$
                                  <?= formatavalor($fechamento_folha["valor_rescisao"]) ?></td>
                                </tr>
                                <tr>
                                  <td width="49%" class="corpo_tabela_cinza">Dif. CLT Rescisão </td>
                                  <td width="51%" class="corpo_tabela_cinza">valor</td>
                                </tr>
                                <tr>
                                  <?php
							
							//Mostra os detalhes de Rescisão.
							$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
							$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' "; 
							$sql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
							$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.tipo = 'diferenca_clt_rescisao' ";
							$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
							
							$db->select($sql,'MYSQL',true);
							
							foreach($db->array_select as $cont_clt_rescisao)
							{	
									?>
                                  <td class="corpo_tabela">&nbsp;
                                      <?= $cont_clt_rescisao["descricao"] ?></td>
                                  <td class="corpo_tabela"><div align="center">R$ <?= formatavalor($cont_clt_rescisao["valor"]) ?>
                                  </div></td>
                                  <?php
								
							}
							
							//Se o valor de Rescisão for vazio
							if($db->numero_registros==0)
							{
									?>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                  <?php
							}							
							
							?>
                                </tr>
                              </table></td>
                              <td width="51%" valign="top">
                              <table width="100%" border="0">
                                <tr>
                                  <td class="corpo_tabela">&nbsp;</td>
                                  <td class="corpo_tabela">&nbsp;</td>
                                </tr>
                                <tr>
                                  <td width="50%" class="corpo_tabela_cinza">Outros descontos: </td>
                                  <td width="50%" class="corpo_tabela_cinza">Valor</td>
                                </tr>


                                  <?php
							
							//Mostra os detalhes de outros descontos.
							$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
							$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' "; 
							$sql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
							$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.tipo = 'outros_descontos' ";
							$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
							
							$db->select($sql,'MYSQL',true);
							
							foreach($db->array_select as $cont_outros_descontos)
							{							
								?>
                                <tr>								                                 
								  <td class="corpo_tabela">&nbsp;
                                      <?= $cont_outros_descontos["descricao"] ?></td>
                                  <td class="corpo_tabela"><div align="center">R$ <?= formatavalor($cont_outros_descontos["valor"]) ?>
                                  </div></td>
                                </tr>								
								
								  <?php								
							}
							
							//Se o valor de outros descontos for vazio
							if($db->numero_registros==0)
							{
									?>
                                <tr>
																	
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                </tr>								
                                  <?php
							}							
							
							?>
                                <tr>
                                  <td class="corpo_tabela_cinza">Outros acréscimos: </td>
                                  <td class="corpo_tabela_cinza">valor</td>
                                </tr>


                                  <?php
							//Mostra os detalhes de acrescimos
							$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_detalhes ";
							$sql .= "WHERE fechamento_folha_detalhes.id_funcionario = '" . $fechamento_folha["id_funcionario"] . "' "; 
							$sql .= "AND fechamento_folha_detalhes.reg_del = 0 ";
							$sql .= "AND fechamento_folha_detalhes.data_ini = '" . $fechamento_folha["data_ini"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.data_fim = '" . $fechamento_folha["data_fim"] . "' ";
							$sql .= "AND fechamento_folha_detalhes.tipo = 'outros_acrescimos' ";
							$sql .= "ORDER BY fechamento_folha_detalhes.descricao ";
							
							$db->select($sql,'MYSQL',true);
							
							foreach($db->array_select as $cont_outros_acrescimos)
							{							
									?>
                                <tr>                                
								  <td class="corpo_tabela">&nbsp;
                                      <?= $cont_outros_acrescimos["descricao"] ?></td>
                                  <td class="corpo_tabela"><div align="center">R$ <?= formatavalor($cont_outros_acrescimos["valor"]) ?>
                                  </div></td>
                                </tr>								  
                                  <?php
								
							}
							
							//Se o valor de outros acrescimos for vazio
							if($db->numero_registros==0)
							{

									?>
                                <tr>                                									
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                  <td class="corpo_tabela"><div align="center">-</div></td>
                                </tr>								  
                                  <?php
							}						
							
							?>
                                 <tr>                                									
                                  <td class="corpo_tabela"><div align="center">DESCRIÇÃO</div></td>
                                  <td class="corpo_tabela"><div align="center"><?= $fechamento_folha["observacao"] ?></div></td>
                                </tr>
                            
                              </table>
                              
                              </td>
                            </tr>
            </table>
		  </div></td>
      </tr>
</table>
	<table width="100%" border="0">
  <tr>
    <td align="right">&nbsp;</td>
  </tr>
</table>
</form>
</body>
</html>