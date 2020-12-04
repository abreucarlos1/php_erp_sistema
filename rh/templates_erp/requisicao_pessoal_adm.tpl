<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_pessoal" id="frm_pessoal" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin: 0px; padding: 0px;">
	<table width="100%" border="0">
		<tr>
			<td width="116" rowspan="2" valign="top" class="espacamento">
				<table width="100%" border="0">
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
						<input type="hidden" name="id_requisicao" id="id_requisicao" value="" />
						<input type="hidden" name="id_rh_candidato" id="id_rh_candidato" value="" />
					</tr>
					<tr>
						<td>
							<input name="btn_financeiro_encerrar" onclick="xajax_encerraVaga(xajax.getFormValues('frm_pessoal'));" type="button" style="display:none;" class="class_botao" id="btn_financeiro_encerrar" value="Encerrar Vaga" />
						</td>
					</tr>
					<tr>
						<td valign="middle"><input style="display:none;" name="btnimprimir" id="btnimprimir" type="button" class="class_botao" value="Imprimir"	onclick="imprimir(document.getElementById('id_requisicao').value);" />
							</td>
					</tr>
				</table>
				<table width="116" rowspan="2">
					<tr>
						<td>
							<div id="nr_requisicao" style="font-size:18px; color:#0099CC; font-weight:bold">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td id="divAlterarStatus" style="display:none";>
							<label class="labels">Alterar Status:</label><br />
							<select id="status_alteracao" name="status_alteracao" class="caixa" onchange="if(this.value>0)if(confirm('Deseja alterar o status desta vaga?')){xajax_alterarStatus(xajax.getFormValues('frm_pessoal'));}else{this.value='';}" style="width:100px;font-size:9px;"  onkeypress="return keySort(this);">
								<option value="">SELECIONE</option>
								<smarty>html_options values=$option_filtro_values output=$option_filtro_output</smarty>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" valign="top" class="espacamento">
				<div id="my_tabbar" class="dhtmlxTabBar" style="height: 450px; width: 100%;">
					<div id="a1" name="Requisi��o de Pessoal">
					  	<table border="0" width="100%">
					  	  <tr>
					  	    <td><label class="labels" style="float:left;">Solicitante:</label>
					  	      <label class="labels" style="font-weight:bold;"><div id="solicitante" style="float:left;width:auto;">&nbsp;</div></label></td>
				  	      </tr>
				  	    </table>
					  	<table width="100%" cellpadding="3" cellspacing="0">
					  	  <tr>
					  	    <td width="13%" valign="top"><label class="labels">Tipo de vaga</label>
					  	      <table width="100%" style="border-style:dashed; border-color:#999999; border-width:1px;">
					  	        <tr>
					  	          <td width="19%"><input id="tipo" name="tipo" type="radio" value="0" onchange="" onclick="if(confirmar()){xajax_alterarTipoVaga(xajax.getFormValues('frm_pessoal'))};xajax.$('id_os').selectedIndex=0; xajax.$('id_os').options[0].text='PROPOSTA'; xajax.$('projeto').value=''; xajax.$('projeto').disabled=false; xajax.$('id_os').disabled=true;document.getElementById('btninserir').disabled=false;" />
					  	            <label class="labels">Proposta</label></td>
				  	            </tr>
					  	        <tr>
					  	          <td><input name="tipo" type="radio" id="tipo" onclick="if(confirmar()){xajax_alterarTipoVaga(xajax.getFormValues('frm_pessoal'))};xajax.$('id_os').disabled=false; xajax.$('id_os').options[0].text='SELECIONE'; xajax.$('projeto').disabled=true; document.getElementById('btninserir').disabled=false;" value="1" />
					  	            <label class="labels">Vaga&nbsp;efetiva</label></td>
				  	            </tr>
				  	          </table></td>
					  	    <td width="25%" valign="top"><label class="labels">Motivo da requisição</label>
					  	      <div id="div_cmbmotivo">
					  	        <select name="cmb_motivo" class="caixa" id="cmb_motivo" style="display:inline;" onchange="if(this.options[this.selectedIndex].value=='9') { mostra_outro(1, 'motivo'); }"  onkeypress="return keySort(this);">
					  	          <option value="" selected="selected">SELECIONE</option>
					  	          <option value="1">Nova OS</option>
					  	          <option value="2">Retrabalho OS</option>
					  	          <option value="3">Atraso de OS</option>
					  	          <option value="4">Aumento de quadro</option>
					  	          <option value="5">Movimentação interna</option>
					  	          <option value="6">Desligamento já efetivado</option>
					  	          <option value="7">Desligamento a ser efetivado</option>
					  	          <option value="8">Nova função</option>
					  	          <option value="9">Outro</option>
				  	            </select>
				  	          </div>
					  	      <div id="div_txtmotivo" style="display:none;">
					  	        <input type="text" id="txt_motivo" class="caixa" name="txt_motivo" onkeyup="if(this.value){xajax.$('img_motivo').style.display='none';}else{xajax.$('img_motivo').style.display='inline'; } " />
					  	        <img id="img_motivo" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>bt_desfazer.png" style="cursor:pointer;" class="class_botao" onclick="mostra_outro(0, 'motivo'); " title="Voltar a selecionar uma opção pr&eacute;-definida" /> </div></td>
					  	    <td width="18%"><label class="labels">Prazo</label>
					  	      <table width="11%" style="border-style:dashed; border-color:#999999; border-width:1px;">
					  	        <tr>
					  	          <td width="19%"><input id="prazo" name="prazo" type="radio" value="1" checked="checked" />
					  	            <label class="labels">Normal:&nbsp;15&nbsp;a&nbsp;30&nbsp;dias</label></td>
				  	            </tr>
					  	        <tr>
					  	          <td><input id="prazo" name="prazo" type="radio" value="2"  />
					  	            <label class="labels">Urgente:&nbsp;7&nbsp;a&nbsp;15&nbsp;dias</label></td>
				  	            </tr>
					  	        <tr>
					  	          <td><input id="prazo" name="prazo" type="radio" value="3"  />
					  	            <label class="labels">Urgent&iacute;ssimo:&nbsp;3&nbsp;a&nbsp;7&nbsp;dias</label></td>
				  	            </tr>
			  	            </table></td>
					  	    <td width="11%" valign="top"><label class="labels">Tipo&nbsp;Contrato</label>
					  	      <table width="92%" style="border-style:dashed; border-color:#999999; border-width:1px;">
					  	        <tr>
					  	          <td width="19%"><input id="contrato" name="contrato" type="radio" value="1" checked="checked" />
					  	            <label class="labels">PJ</label></td>
				  	            </tr>
					  	        <tr>
					  	          <td><input id="contrato" name="contrato" type="radio" value="2"  />
					  	            <label class="labels">CLT</label></td>
				  	            </tr>
				  	          </table></td>
					  	    <td width="33%" valign="top"><label class="labels">Categoria&nbsp;de&nbsp;contratação</label><br />
					  	      <div id="div_cmbcategoria2" style="display:inline;">
					  	        <select name="categoria_contratacao" class="caixa" id="categoria_contratacao" onchange="if(this.options[this.selectedIndex].value=='4') { mostra_outro(1, 'categoria');  } else { xajax.$('qtde_vagas').focus();}"  onkeypress="return keySort(this);">
					  	          <option value="">SELECIONE</option>
					  	          <option value="1">Efetivo</option>
					  	          <option value="2">Temporário</option>
					  	          <option value="3">Estagiário</option>
					  	          <option value="4">Outra</option>
				  	            </select>
				  	          </div>
					  	      <div id="div_txtcategoria2" style="display:none;">
					  	        <input type="text" id="txt_categoria2" class="caixa" name="txt_categoria2" onkeyup="if(this.value){xajax.$('img_categoria').style.display='none';}else{xajax.$('img_categoria').style.display='inline'; } " />
					  	        <img id="img_categoria2"  class="class_botao" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>bt_desfazer.png" style="cursor:pointer;" onclick="mostra_outro(0, 'categoria'); " title="Voltar a selecionar uma opção pr&eacute;-definida" /></div></td>
				  	      </tr>
				  	    </table>
					  	<table border="0" width="100%">
						  <tr>
							<td width="12%"><label class="labels">OS&nbsp;n&ordm;</label><br />
							  <div id="div_cmbos" style="display:inline;">
							<select name="id_os" class="caixa" id="id_os" onChange="if(this.options[this.selectedIndex].value=='outro') { mostra_outro(1,'os');xajax.$('projeto').disabled=false; } xajax_mostraDescricaoOS(this.options[this.selectedIndex].value);"  onkeypress="return keySort(this);" disabled="disabled">
							  <option value="">SELECIONE</option>
							<option value="outro">OUTRA</option>
								<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
							</select>
							  </div>
							  <div id="div_txtos" style="display:none;">
							<input type="text" id="txt_os" class="caixa" name="txt_os" onKeyUp="if(this.value){xajax.$('img_os').style.display='none';}else{xajax.$('img_os').style.display='inline'; } "><img id="img_os" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>bt_desfazer.png" style="cursor:pointer;" onclick="mostra_outro(0, 'os');xajax.$('projeto').disabled=true;" title="Voltar a selecionar uma op��o pr�-definida"></div></td>
							<td width="88%"><label class="labels">Projeto</label><br />
							<input name="projeto" type="text" class="caixa" id="projeto" size="80" disabled></td>
						  </tr>
						</table>
						<table border="0" width="100%">
						  <tr>
							<td width="29%" rowspan="2"><label class="labels">Local&nbsp;de&nbsp;trabalho</label><br /> 
							  <div id="div_cmblocais" style="display:inline;">
							<select class="caixa" name="locais[]" id="locais" onChange="if(this.options[this.selectedIndex].value=='outro') { mostra_outro(1,'locais'); }" style="width: 250px;"  onkeypress="return keySort(this);" disabled="<smarty> $disabled </smarty>">
							  <smarty>html_options values=$option_locais_values output=$option_locais_output</smarty>
							<option value="outro">OUTRO</option>
						</select>					
							  </div>
							  <div id="div_txtlocais" style="display:none;">
							<input type="text" id="txt_locais" class="caixa" name="txt_locais" onKeyUp="if(this.value){xajax.$('img_locais').style.display='none';}else{xajax.$('img_locais').style.display='inline'; } "><img id="img_locais" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>bt_desfazer.png" style="cursor:pointer;" onclick="mostra_outro(0, 'locais'); " title="Voltar a selecionar uma op��o pr�-definida"></div></td>
							<td valign="top" width="10%"><label class="labels">Qtd.de&nbsp;vagas</label><br />
								<input name="qtde_vagas" type="text" class="caixa" id="qtde_vagas" size="5" onKeyPress="num_only();" disabled="<smarty> $disabled </smarty>"></td>
							<td valign="top" width="13%"><label class="labels">Tempo&nbsp;de&nbsp;serviço</label><br /> 
								<input name="tempo_servico" type="text" class="caixa" id="tempo_servico" size="10" disabled="<smarty> $disabled </smarty>"></td>
							<td width="48%" valign="top"><label class="labels">Fun��o</label><br />
							  <select name="cargo" id="cargo" class="caixa" style="width: 250px;" onchange="xajax_preenche_escolaridade(this.value);"  onkeypress="return keySort(this);" disabled="<smarty> $disabled </smarty>">
								<option value="">SELECIONE</option>
								<smarty>html_options values=$option_cargos_values output=$option_cargos_output</smarty>
							  </select>
							</td>							
						  </tr>					  
					  </table>
						<table border="0" width="100%">
						  <tr>
							<td colspan="11" style="padding-right:0 !important">
								<table width="97%" border="0">
									<tr>
										<td width="180" valign="top">
											<label class="labels">Nivel&nbsp;de&nbsp;atua��o*</label><br />
											<select name="nivel_atuacao" class="caixa" id="nivel_atuacao" onkeypress="return keySort(this);">
												<option value="A">P / ADM. M.O.</option>
												<option value="D">DIRE��O</option>
												<option value="C">COORDENA��O</option>
												<option value="S">SUPERVIS�O</option>
												<option value="G">GER�NCIA</option>
												<option value="E" selected="selected">EXECUTANTE / INTERNO</option>
												<option value="P">PACOTE</option>
											</select>
										</td>
										<td width="89">
											<label class="labels">Equipamentos *</label><br />
											<select name="infra_ti[]" style="height: 100px;" multiple="multiple" class="caixa" id="infra_ti" title="infraestrutura">
												<smarty>html_options values=$option_infra_values output=$option_infra_output</smarty>
											</select>
										</td>
										<td width="65" >
											<label class="labels">Softwares *</label><br />
											<select name="softwares_ti[]" style="height: 100px;" multiple="multiple" class="caixa" id="softwares_ti" title="infraestrutura">
												<smarty>html_options values=$option_softwares_values output=$option_softwares_output</smarty>
											</select>
										</td>
										<td width="169">
											<label class="labels">Outros softwares *</label><br />
											<textarea name="informacoes_ti" class="caixa" style="height: 100px;" id="informacoes_ti"></textarea>
										</td>
									</tr>
								</table>
							</td>
							<td width="42%">
								<table width="100%" border="0">
									<tr>
										<td colspan="2" width="24%">
											<label class="labels" style='float:left;width:100%;'>Mobiliza��o *</label>
										</td>
									</tr>
									<tr>
									  	<td>
											<label class="labels">Devemada</label><br />
											<input type="radio" name="mobilizacao" id="mobilizacao_dvm" onclick="document.getElementById('divDetMobilizacao').style.display='';" value="0" class="caixa" />
										</td>
										<td width="76%">
											<label class="labels">Colaborador</label><br />
											<input type="radio" name="mobilizacao" id="mobilizacao_colaborador" onclick="document.getElementById('detalhes_mobilizacao').value='';document.getElementById('divDetMobilizacao').style.display='none';" value="1" class="caixa" />
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<label class="labels" style="vertical-align:middle;">Detalhes da Mobiliza��o</label><br />
											<textarea name="detalhes_mobilizacao" id="detalhes_mobilizacao" rows="1" style="height: 55px;width: 160px;" cols="25" class="caixa"></textarea>
										</td>
									</tr>
							  </table>
							</td>
						  </tr>
						</table> 
				  </div>
                  				  
				  <div id="a2" name="Requisitos do Cargo">					
					<table width="100%" border="0">
					  <tr>
						<td valign="top" width="31%"><label class="labels">Escolaridade</label><br />
						<div id="escolaridade" class="labels" style="font-weight:bold;">&nbsp;</div></td>
						<td valign="top" width="25%"><label class="labels">Tempo&nbsp;experi�ncia</label><br />
						<div id="div_experiencia" class="labels" style="font-weight:bold;">&nbsp;</div></td>
						<td width="44%" valign="top"><label class="labels">Requisitos&nbsp;do&nbsp;cargo</label><br />
							<select name="requisitos_cargo" style="width:150px;" size="5"  multiple="multiple" id="requisitos_cargo" disabled="disabled"></select>
						</td>
					  </tr>
					</table>					
					<table width="100%" border="0">
					  <tr>
						<td><label class="labels">Experi&ecirc;ncia,&nbsp;conhecimentos,&nbsp;habilidades&nbsp;em:</label><br />
                        <textarea name="experiencia" cols="100" rows="3" class="caixa" id="experiencia" disabled="<smarty> $disabled </smarty>"></textarea>
                        </td>
					  </tr>
					  <tr>
					  	<td>
					  		<label class="labels">Aspectos comportamentais</label><br />
							<textarea name="aspectos_comportamentais" cols="100" rows="2" class="caixa" id="aspectos_comportamentais"></textarea>
						</td>
					  </tr>
					  <tr>
					  	<td>
						  	<label class="labels">Reporte direto para:</label><br />
							<input type="text" name="reporte_direto" class="caixa" size="80" id="reporte_direto" />
						</td>
					  </tr>
					  <tr>
						<td>
							<label class="labels">Necessidade de Integra��o no cliente:</label><br />
                        	<table width="100%" border="0">
                            	<tr>
                                	<td width="5%">
                                        <label class="labels">Sim</label><br /> 
                                        <input type="radio" value='1' name="integracao_cliente" class="caixa" id="integracao_cliente" />
                                    </td>
                                    <td width="95%">
                                        <label class="labels">N�o</label><br />
                                        <input type="radio" value='0' name="integracao_cliente" class="caixa" id="integracao_cliente2" />
                                    </td>
                                </tr>
                            </table>   							
						</td>
					  </tr>
					</table>
				  </div>				  
				  <div id="a4" name="Recursos Humanos">
				  	<table width="100%" border="0">
					  <tr>
						<td width="37%"><label class="labels">Nome&nbsp;do&nbsp;candidato</label><br /> 
					    	<input name="nome_candidato" type="text" class="caixa" id="nome_candidato" size="50" disabled="disabled"></td>
						<td width="63%"><label class="labels">Valor&nbsp;(R$)</label><br />
					    	<input name="valor_candidato" type="text" class="caixa" id="valor_candidato" size="10" onKeyDown="FormataValor(this, 13, event);" onKeyPress="num_only();" disabled="disabled"></td>

					  </tr>
                      <tr>
						<td colspan="2"><label class="labels">Observações</label><br />
					    	<input name="observacoes_candidato" type="text" class="caixa" id="observacoes_candidato" size="65"></td>                      
                      </tr>					  
					  <tr>
						<td colspan="4"><div align="left">
						  <input name="btninserir_candidato" id="btninserir_candidato" type="button" class="class_botao" value="Inserir" onClick="xajax_insereCandidatos(xajax.getFormValues('frm_pessoal')); " disabled="disabled">
						</div></td>
					    </tr>
					  <tr>
						<td colspan="4">
							<div id="candidatos_div" style="width:800px;margin-top:25px;">&nbsp;</div>
							<input type="button" name="btnEnviarAprovados" id="btnEnviarAprovados" onclick="xajax_enviarAprovadosFinanceiro(xajax.getFormValues('frm_pessoal'));" class="class_botao" style="display:none;" value="Enviar E-mail Aprovados ao Financeiro" />
						</td>
					  </tr>
					</table>
				  </div>
				</div>
			</td>
		</tr>
	</table>
	<table style="float: right; margin-top: 10px; width: 100%;">							  
	  <tr>
		<td width="16%"><label class="labels">Busca</label><br />
			<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);">
		</td>
		<td width="84%">
			<label class="labels">Exibir por:</label><br />
			<select name="filtro" class="caixa" onchange="xajax_atualizatabela(xajax.getFormValues('frm_pessoal'));xajax.$('busca').focus();"  onkeypress="return keySort(this);">
				<option value="">SELECIONE</option>
				<smarty>html_options values=$option_filtro_values output=$option_filtro_output</smarty>
			</select>
		</td>
	  </tr>
	  <tr>
	  	<td colspan="3">
	  		<div id="requisicoes" style="width: 100%; height: 160px;">&nbsp;</div>
	  	</td>
	  </tr>
	 </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>