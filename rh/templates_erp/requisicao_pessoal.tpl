<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style type="text/css">

.divCadastroCandidato{
	float: left;
	margin: 5px;
	margin-right: 20px;
	width: auto;
}

.divCadastroCandidatoLinha{
	float: left;
	width: 100%;
	border-bottom: dashed 1px #bbb;
	border-right: dashed 1px #bbb;
	border-left: dashed 1px #bbb;
	padding-bottom: 5px;
}

.first{
	border-top: dashed 1px #bbb;
}


</style>

<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="button"  onclick="xajax_insere(xajax.getFormValues('frm'));" class="class_botao" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
						<tr>
							<td>
								<div id="nr_requisicao" style="font-size:18px; color:#0099CC; font-weight:bold"> </div>
							</td>
						</tr>
						<tr>
							<td>
								<label class="labels">Exibir por:</label><br />
								<select name="filtro" class="caixa" style='width:110px;font-size:11px' onchange="xajax_atualizatabela(xajax.getFormValues('frm')); xajax.$('busca').focus();" onkeypress="return keySort(this);">
								<option value="">SELECIONE</option>
									<smarty>html_options values=$option_filtro_values output=$option_filtro_output</smarty>
								</select>
							</td>
						</tr>
					</table>
				</td>
				<td class="espacamento">
					<table width="100%">
						<tr>
							<td>
								<div id="a_tabbar" class="dhtmlxTabBar" style="width:100%; height:480px;">
									<div id="a1" name="Requisição de Pessoal" style='padding:5px; height: 435px; overflow:auto;'>
										<div class='divCadastroCandidatoLinha first'>
											<div class='divCadastroCandidato'>
												<label class='labels'><strong>Solicitante</strong></label><br />
												<div id="solicitante"> </div>
											</div>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<div class='divCadastroCandidato'>
												<label class='labels'><strong>Tipo de vaga *</strong></label><br />
												<input id="tipo" name="tipo" type="radio" value="0" onclick="xajax.$('id_os').selectedIndex=0; xajax.$('id_os').options[0].text='PROPOSTA'; xajax.$('projeto').value=''; xajax.$('projeto').disabled=false; xajax.$('id_os').disabled=true;document.getElementById('btninserir').disabled=false;" />
                      							<label class="labels">Proposta</label><br />
                      							
                      							<input name="tipo" type="radio" id="tipo" onclick="xajax.$('id_os').disabled=false; xajax.$('id_os').options[0].text='SELECIONE'; xajax.$('projeto').disabled=true; document.getElementById('btninserir').disabled=false;" value="1" />
                      							<label class="labels">Vaga efetiva</label>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Motivo da requisição *</strong></label><br />
												<div id="div_cmbmotivo">
													  <select name="cmb_motivo" class="caixa" id="cmb_motivo" style="display:inline;" onChange="if(this.options[this.selectedIndex].value=='9') { mostra_outro(1, 'motivo');}" onkeypress="return keySort(this);">
														<option value="" selected>SELECIONE</option>
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
												  	<input type="text" id="txt_motivo" class="caixa" name="txt_motivo" onKeyUp="if(this.value){xajax.$('img_motivo').style.display='none';}else{xajax.$('img_motivo').style.display='inline'; } "><span class="icone icone-desfazer cursor" id="img_motivo" onclick="mostra_outro(0, 'motivo'); " title="Voltar a selecionar uma opção pré-definida"></span>
												  </div>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Prazo *</strong></label><br />
												
												<input id="prazo" name="prazo" type="radio" value="1" checked="checked" />
	                      						<label class="labels">Normal: 15 a 30 dias</label><br />
	                      						
	                      						<input id="prazo" name="prazo" type="radio" value="2"  />
                      							<label class="labels">Urgente: 7 a 15 dias</label><br />
                      							
                      							<input id="prazo" name="prazo" type="radio" value="3"  />
                      							<label class="labels">Urgentíssimo: 3 a 7 dias</label>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Tipo Contrato *</strong></label><br />
												
												<input id="contrato" name="contrato" type="radio" value="1" checked="checked" />
                        						<label class="labels">PJ</label><br />
                        						
                        						<input id="contrato" name="contrato" type="radio" value="2"  />
                        						<label class="labels">CLT</label>
											</div>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Cat. de contr. *</strong></label><br />
												
												<div id="div_cmbcategoria" style="display:inline;">
							                    <select name="categoria_contratacao" class="caixa" id="categoria_contratacao" onchange="if(this.options[this.selectedIndex].value=='4') { mostra_outro(1, 'categoria');  } else { xajax.$('qtde_vagas').focus();}" onkeypress="return keySort(this);">
							                      <option value="">SELECIONE</option>
							                      <option value="1">Efetivo</option>
							                      <option value="2">Temporário</option>
							                      <option value="3">Estagiário</option>
							                      <option value="4">Outra</option>
							                    </select>
							                  	</div>
							                  
							                  	<div id="div_txtcategoria" style="display:none;">
								                  <input type="text" id="txt_categoria" class="caixa" name="txt_categoria" onkeyup="if(this.value){xajax.$('img_categoria').style.display='none';}else{xajax.$('img_categoria').style.display='inline'; } " />
								                  <span class="icone icone-desfazer cursor" id="img_categoria" onclick="mostra_outro(0, 'categoria'); " title="Voltar a selecionar uma opção pr&eacute;-definida"></span>
								                </div>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>OS nº / Projeto *</strong></label><br />
												
												<div id="div_cmbos" style="display:inline;">
													<select name="id_os" style='width:300px;' class="caixa" id="id_os" onChange="if(this.options[this.selectedIndex].value=='outro') { mostra_outro(1,'os');xajax.$('projeto').disabled=false; } xajax_mostraDescricaoOS(this.options[this.selectedIndex].value); " disabled>
													  	<option value="">SELECIONE</option>
														<option value="outro">OUTRA</option>
														<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
													</select>
												</div>
												<div id="div_txtos" style="display:none;">
													<input type="text" id="txt_os" size="7" class="caixa" name="txt_os" onKeyUp="if(this.value){xajax.$('img_os').style.display='none';}else{xajax.$('img_os').style.display='inline'; } "><span class="icone icone-desfazer cursor" id="img_os" onclick="mostra_outro(0, 'os');xajax.$('projeto').disabled=true;" title="Voltar a selecionar uma opção pré-definida"></span>
													<input name="projeto" type="text" class="caixa" id="projeto" size="30" disabled>
												</div>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Local de trabalho *</strong></label><br />
												
												<div id="div_cmblocais" style="display:inline;">
													<select class="caixa" style='width:300px;' name="locais" id="locais" onChange="if(this.options[this.selectedIndex].value=='outro'){mostra_outro(1,'locais');};liberarIntegracao(this.value);" onkeypress="return keySort(this);">
														<smarty>html_options values=$option_locais_values output=$option_locais_output</smarty>
														<option value="outro">OUTRO</option>
													</select>					
											    </div>
											    <div id="div_txtlocais" style="display:none;">
													<input type="text" id="txt_locais" class="caixa" name="txt_locais" onKeyUp="if(this.value){xajax.$('img_locais').style.display='none';}else{xajax.$('img_locais').style.display='inline'; } "><span class="icone icone-desfazer cursor" id="img_locais" onclick="mostra_outro(0, 'locais'); " title="Voltar a selecionar uma opção pré-definida"></span>
												</div>
											</div>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Qtd.de vagas *</strong></label><br />
												<input name="qtde_vagas" type="text" class="caixa" id="qtde_vagas" size="5" onKeyPress="num_only();">
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Tempo de serviço *</strong></label><br />
												<input name="tempo_servico" type="text" class="caixa" id="tempo_servico" size="10">
											</div>
											
											<div class='divCadastroCandidato'>
												<label class='labels'><strong>Função *</strong></label><br />
												<select name="cargo" id="cargo" class="caixa" onchange="xajax_preenche_escolaridade(this.value);" onkeypress="return keySort(this);">
													<option value="">SELECIONE</option>
													<smarty>html_options values=$option_cargos_values output=$option_cargos_output</smarty>
											    </select>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Nivel de atuação*</strong></label><br />
												<select name="nivel_atuacao" class="caixa" id="nivel_atuacao" onkeypress="return keySort(this);">
													<option value="A">P / ADM. M.O.</option>
													<option value="D">DIREÇÃO</option>
													<option value="C">COORDENAÇÃO</option>
													<option value="S">SUPERVISÃO</option>
													<option value="G">GERÊNCIA</option>
													<option value="E" selected="selected">EXECUTANTE / INTERNO</option>
													<option value="P">PACOTE</option>
												</select>
											</div>
										</div>
									
										<div class='divCadastroCandidatoLinha' style="width:45%">
											<label class="labels" style='float:left;width:100%;'><sub>Utilizar a tecla CTRL para selecionar mais de um item abaixo</sub></label>
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Equipamentos *</strong></label><br />
												<select name="infra_ti[]" style="height: 100px;" multiple="multiple" class="caixa" id="infra_ti" title="infraestrutura">
													<smarty>html_options values=$option_infra_values output=$option_infra_output</smarty>
												</select>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Softwares *</strong></label><br />
												<select name="infra_ti[]" style="height: 100px;" multiple="multiple" class="caixa" id="infra_ti" title="infraestrutura">
													<smarty>html_options values=$option_softwares_values output=$option_softwares_output</smarty>
												</select>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Outros softwares *</strong></label><br />
												<textarea name="informacoes_ti" class="caixa" style="height: 100px;" id="informacoes_ti"></textarea>
											</div>
										</div>
										
										<div class='divCadastroCandidatoLinha' style="width:39%">
											<label class="labels" style='float:left;width:100%;'><strong>Mobilização *</strong></label>
											<div class='divCadastroCandidato'>
												<label class="labels"> </label>
												<input type="radio" name="mobilizacao" id="mobilizacao_dvm" onclick="document.getElementById('divDetMobilizacao').style.display='';" value="0" class="caixa" />
											</div>
											<div class='divCadastroCandidato'>
												<label class="labels">Colaborador</label>
												<input type="radio" name="mobilizacao" id="mobilizacao_colaborador" onclick="document.getElementById('detalhes_mobilizacao').value='';document.getElementById('divDetMobilizacao').style.display='none';" value="1" class="caixa" />
											</div>
											<div class='divCadastroCandidato' id="divDetMobilizacao" style="display:none;">
												<label class="labels" style="vertical-align:top;">Detalhes da Mobilização</label>
												<textarea name="detalhes_mobilizacao" id="detalhes_mobilizacao" rows="1" style="height: 55px;width: 250px;" cols="15" class="caixa"></textarea>
											</div>
										</div>
									</div>
									
									<div id="a2" name="Requisitos do Cargo" style='padding:5px;'>
										<div class='divCadastroCandidatoLinha first'>
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Escolaridade *</strong></label><br />
												<div id="escolaridade" class="labels"> </div>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Tempo na atividade *</strong></label><br />
												<div id="div_experiencia" class="labels"> </div>
											</div>
											
											<div class='divCadastroCandidato'>
												<label class="labels"><strong>Requisitos do cargo *</strong></label><br />
												<select name="requisitos_cargo" style="width:150px;" size="5" multiple="multiple" id="requisitos_cargo"></select>
											</div>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<label class="labels">Experiência, conhecimentos, habilidades em:</label><br />
											<textarea name="experiencia" cols="80" rows="2" class="caixa" id="experiencia"></textarea>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<label class="labels">Digite os aspectos comportamentais</label><br />
											<textarea name="aspectos_comportamentais" cols="80" rows="2" class="caixa" id="aspectos_comportamentais"></textarea>
										</div>
										
										<div class='divCadastroCandidatoLinha'>
											<label class="labels">Reporte direto para:</label><br />
											<input type="text" name="reporte_direto" class="caixa" size="80" id="reporte_direto" />
										</div>
										<div class='divCadastroCandidatoLinha'>
											<label class="labels">Necessidade de Integração no cliente:</label><br />
											<label class="labels">Sim</label> <input type="radio" value='1' disabled='disabled' name="integracao_cliente" class="caixa" id="integracao_cliente" />
											<label class="labels">Não</label> <input type="radio" value='0' disabled='disabled' name="integracao_cliente" class="caixa" id="integracao_cliente2" />
										</div>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>		
		<div id="div_requisicoes" style="width:100%;"> </div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>