<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>

<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_financeiro" id="frm_financeiro" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">
		<tr>
        	<td colspan="2" valign="top">
                <table width="100%" border="0">
                    <tr>
                        <td>
                            <div id="numero" style="float:left; font-size: 18px; color: #0099CC; font-weight: bold"> </div>
                            <div style="float:right"><span class="icone icone-fechar cursor" onclick="divPopupInst.destroi();" width="20px;"></span></div>
                        </td>
                    </tr>
                </table>
                <table width="800px" border="0">
                    <tr>
                        <td><label class="labels"><strong>Dados Principais</strong></label></td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" border="0">
                                <tr>
                                    <td width="16%"><label for="financeiro_status" class="labels">Status</label><br />
                                        <select name="financeiro_status" class="caixa" id="financeiro_status" <smarty>$disabled</smarty> >
                                            <option value="" selected>SELECIONE</option>
                                            <smarty>html_options values=$option_financeiro_status_values output=$option_financeiro_status_output selected=$selecionado_1</smarty>
                                    </select>
                                    </td>
                                    <td width="30%"><label class="labels">Tipo de Contrato</label><br />
                                        <select name="financeiro_tipo_contrato" class="caixa" id="financeiro_tipo_contrato" <smarty>$disabled</smarty> >
                                            <option value="" selected>SELECIONE</option>
                                            <smarty>html_options values=$option_tipo_contrato_values output=$option_tipo_contrato_output selected=$selecionado_2</smarty>
                                    </select>
                                    </td>
                                    <td width="30%"><label class="labels">Nome</label><br />
                                    <input class="caixa" name="financeiro_nome" type="text" disabled="disabled" id="financeiro_nome" size="40" /></td>
                                </tr>
                            </table>
                            <table width="100%" border="0">
                                <tr>
                                    <td width="30%"><label class="labels">Data de início</label><br />
                                        <input name="financeiro_data_inicio" type="text" class="caixa" id="financeiro_data_inicio" value="<smarty>$data_inicio</smarty>" size="15" maxlength="10" onkeypress="transformaData(this, event);" <smarty>$disabled</smarty> />
                                     </td>
                                     <smarty>$pj1</smarty>
                                </tr>
                            </table> 
                        <td>
                    </tr>
                </table>
                <table width="800px" border="0">
                    <tr>
                        <td><label class="labels"><strong>A ser preenchido pela Diretoria Administrativa Financeira</strong></label></td>
                    </tr>
                    <tr>
                        <td>
                        <label class="labels">Salário / Tarifa</label>
                        </td>                	
                    </tr>        
                    <tr>
                        <td>
                             <table border="0">
                                <tr>
                                    <smarty>$registro</smarty>
                                    <td><label class="labels">Ajuda de Custo (R$)</label><br />
                                        <input name="financeiro_ajudacusto" type="text" class="caixa" id="financeiro_ajudacusto" size="10" onclick="this.value=''" onKeyDown="FormataValor(this, 13, event);" onKeyPress="num_only();" value="<smarty>$salario_ajudacusto</smarty>" <smarty>$disabled</smarty> >
                                    </td>
                                    <td ><label class="labels"><smarty>$financeiro_horaextra</smarty></label><br />
                                        <input name="financeiro_horaextra" type="text" class="caixa" id="financeiro_horaextra" size="10" onclick="this.value=''" onKeyDown="FormataValor(this, 13, event);" onKeyPress="num_only();" value="<smarty>$salario_horaextra</smarty>" <smarty>$disabled</smarty> >
                                    </td>
                                    <td ><label class="labels"><smarty>$financeiro_horaextra_feriado</smarty></label><br />
                                        <input name="financeiro_horaextra_feriado" type="text" class="caixa" id="financeiro_horaextra" size="10" onclick="this.value=''" onKeyDown="FormataValor(this, 13, event);" onKeyPress="num_only();" value="<smarty>$salario_horaextra_feriado</smarty>" <smarty>$disabled</smarty> >
                                    </td>
                                    <td><label class="labels">Periculosidade 30%</label><br />
                                        <input name="financeiro_periculosidade" type="text" class="caixa" id="financeiro_periculosidade" size="10" onclick="this.value=''" onKeyDown="FormataValor(this, 13, event);" onKeyPress="num_only();" value="<smarty>$adicional_periculosidade</smarty>" <smarty>$disabled</smarty> >
                                    </td>
                                </tr>
                                <tr>
                                    <smarty>$pj2</smarty>
                                    <td><input name="financeiro_ajudacusto_tipo" type="radio" value="1" <smarty>$chk_ajuda_custo1</smarty>  <smarty>$disabled</smarty> /><label class="labels">Mensal</label>
                                        <input name="financeiro_ajudacusto_tipo" type="radio" value="2" <smarty>$chk_ajuda_custo2</smarty>  <smarty>$disabled</smarty> /><label class="labels">Hora</label></td>
                                    
                                    <td><input name="financeiro_horaextra_tipo" type="radio" value="1" <smarty>$chk_horaextra_custo1</smarty>  <smarty>$disabled</smarty> /><label class="labels">Mensal</label>
                                        <input name="financeiro_horaextra_tipo" type="radio" value="2" <smarty>$chk_horaextra_custo2</smarty>  <smarty>$disabled</smarty> /><label class="labels">Hora</label></td>                                
                                    
                                    <td><input name="financeiro_horaextra_feriado_tipo" type="radio" value="1" <smarty>$chk_horaextra_custo1</smarty>  <smarty>$disabled</smarty> /><label class="labels">Mensal</label>
                                        <input name="financeiro_horaextra_feriado_tipo" type="radio" value="2" <smarty>$chk_horaextra_custo2</smarty>  <smarty>$disabled</smarty> /><label class="labels">Hora</label></td>   
    
                                    <td><input name="financeiro_periculosidade_tipo" type="radio" value="1" <smarty>$chk_financeiro_periculosidade_tipo1</smarty>  <smarty>$disabled</smarty> /><label class="labels">Mensal</label>
                                        <input name="financeiro_periculosidade_tipo" type="radio" value="2" <smarty>$chk_financeiro_periculosidade_tipo2</smarty>  <smarty>$disabled</smarty> /><label class="labels">Hora</label></td>
    
                                </tr>
                            </table>                   
                        </td>
                    </tr>
              	</table>
                <table width="800px" border="0">
                    <tr>
                        <td><label class="labels">Inclusões na tarifa do PJ</strong></label></td>
                    </tr>
                    <tr>
                    	<td>
                            <table width="100%" border="0">
                                <tr>
                                    <td><input name="financeiro_chk_transporte" id="financeiro_chk_transporte" type="checkbox" id="financeiro_chk_transporte" value="1" <smarty>$financeiro_chk_transporte</smarty>  <smarty>$disabled</smarty> /><label class="labels">Transporte</label></td>
                                    <td><input name="financeiro_chk_refeicao" id="financeiro_chk_refeicao" type="checkbox" id="financeiro_chk_refeicao" value="1" <smarty>$financeiro_chk_refeicao</smarty>  <smarty>$disabled</smarty> /><label class="labels">Refeição</label></td>
                                    <td><input name="financeiro_chk_hotel" type="checkbox" id="financeiro_chk_hotel" value="1" <smarty>$financeiro_chk_hotel</smarty>  <smarty>$disabled</smarty> /><label class="labels">Hotel</label></td>
                                    <td><input name="financeiro_chk_outros" type="checkbox" id="financeiro_chk_outros" value="1" <smarty>$financeiro_chk_outros</smarty>  <smarty>$disabled</smarty> /><label class="labels">Outros</label></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table width="800px" border="0">
                    <tr>
                        <td><label class="labels">Local de trabalho</strong></label><br />
							<select name="financeiro_local_trabalho" id="financeiro_local_trabalho" class="caixa" <smarty>$disabled</smarty> >
								<option value="">SELECIONE</option>
								<smarty>html_options values=$option_local_trabalho_values output=$option_local_trabalho_output selected=$selecionado_3</smarty>

						</select>
                        </td>
                    </tr>
                    <tr>
                    	<td>
                        <table cellspacing="0" cellpadding="0">
                        	<tr>
                            	<td>
                                	<label class="labels">Forma de pagamento</label>
                                </td>
                            </tr>
                            <tr>
                                <td><input name="financeiro_chk_unibanco" type="checkbox" id="financeiro_chk_unibanco" value="1" <smarty>$financeiro_chk_unibanco</smarty>  <smarty>$disabled</smarty> /><label class="labels">Banco</label>
									<input name="financeiro_chk_doc" type="checkbox" id="financeiro_chk_doc" value="1" <smarty>$financeiro_chk_doc</smarty>  <smarty>$disabled</smarty> /><label class="labels">DOC</label>
									<input name="financeiro_chk_cheque" type="checkbox" id="financeiro_chk_cheque" value="1" <smarty>$financeiro_chk_cheque</smarty>  <smarty>$disabled</smarty> /><label class="labels">Cheque</label>
									<input name="financeiro_chk_moeda" type="checkbox" id="financeiro_chk_moeda" value="1" <smarty>$financeiro_chk_moeda</smarty>  <smarty>$disabled</smarty> /><label class="labels">Esp&eacute;cie</label>
								</td>
                            </tr>
                        </table>
                        </td>
                    </tr>
                </table>
                <table width="800px" border="0">
                    <tr>
                        <td><label class="labels">Infraestrutura / Softwares TI</strong></label></td>
                    </tr>
                    <tr>
                    	<td>
                            <table border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                  <td valign="top"><label class="labels">Equipamentos*</label></td>
                                  <td valign="top"><label class="labels">Softwares*</label></td>
                                  <td valign="top"><label class="labels">Protheus:</label></td>
                                  <td valign="top"><label class="labels">SISTEMA:</label></td>
                                  <td valign="top"><label class="labels">Outros Softwares:</label></td>						
                              </tr>
                              <tr>
                                  <td><select name="infra_ti[]" style="height: 80px;" multiple="multiple" class="caixa" id="infra_ti" title="infraestrutura" >
										<smarty>html_options values=$option_infra_values output=$option_infra_output</smarty>
                                      </select>
                                  </td>
                                  <td>
                                      <select name="softwares_ti[]" style="height: 80px;" class="caixa" multiple="multiple" id="softwares_ti" title="softwares" >
										<smarty>html_options values=$option_softwares_values output=$option_softwares_output</smarty>
                                      </select>
                                  </td>
                                  <td>
                                      <textarea name="protheusModulos" id="protheusModulos" class="caixa" rows="4" cols="15"></textarea>
                                  </td>
                                  <td>
                                      <textarea id="dvmsysModulos" name="dvmsysModulos"  class="caixa" rows="4" cols="15"></textarea>
                                  </td>
                                  <td>
                                      <textarea name="outrosSoftwares" id="outrosSoftwares" class="caixa" rows="4" cols="15"></textarea>
                                  </td>
                              </tr>
                          </table>
                        </td>
                    </tr>
                </table>
                <table width="800px" border="0" cellspacing="0" cellpadding="0">
                	<tr>
                    	<td>
                        	<label class="labels">Observações</label>
                        </td>
                    </tr>
                    <tr>
                        <td width="67%">
                            <textarea name="financeiro_observacoes" cols="110" rows="2" class="caixa" id="financeiro_observacoes" <smarty>$disabled</smarty> ><smarty>$observacoes</smarty></textarea></td>
                    </tr>
                </table>
                <table width="800px" border="0">
                    <tr>
                        <td><input name="btn_financeiro_atualizar" type="button" class="class_botao" id="btn_financeiro_atualizar" onclick="xajax_atualizar(xajax.getFormValues('frm_financeiro'),<smarty>$id_requisicao</smarty>);" value="Atualizar" <smarty>$disabled</smarty> />
                
                		<smarty>$imprimir</smarty>
						
                         <input name="btn_financeiro_voltar" type="button" class="class_botao" id="btn_financeiro_voltar" onclick="divPopupInst.destroi();" value="Voltar" /> 
                         <input type="hidden" name="financeiro_id_rh_candidato" id="financeiro_id_rh_candidato" value="<smarty>$id_candidato</smarty>" /></td>
                    </tr>
                </table>
            </td>
        </tr>
      </table>
</form>
</div>