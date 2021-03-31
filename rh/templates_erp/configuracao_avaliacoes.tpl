<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" onsubmit="xajax_salvar_avaliacao(xajax.getFormValues('frm', true));return false;">
		<iframe id="upload_target" name="upload_target" src="#" style="width: 0; height: 0; border: 0px solid #fff; display: none;"></iframe>
		<!-- width:0;height:0; -->
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="submit" class="class_botao" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table width="100%">
						<tr>
							<td><label for="ava_titulo" class="labels">Titulo Avaliação</label><br />
								<input size="100" type="text" id="ava_titulo" name="ava_titulo" class="caixa" placeholder="Título" />
								<input type="hidden" id="ava_id" name="ava_id" />
							</td>
						</tr>
						<tr>
							<td><label class="labels">Tipo de Avaliação</label><br />
								<select id="ava_tipo" name="ava_tipo" class="caixa" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_ava_values output=$option_ava_output</smarty>
								</select>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div align="left" id="a_tabbar" mode="top" class="dhtmlxTabBar" imgpath="../includes/dhtmlx_403/dhtmlxTabbar/codebase/imgs/" margin="3" style="height: 550px; width: 100%; margin-top: 20px; margin-right: 3px;" tabstyle="modern" skinColors="#F1F4F5,#F1F4F5">
			<div id="avaliacoes" width="100px" name="avaliacoes" style="margin-left: 3px;">
				<div id="div_avaliacoes" style="width: 100%;"> </div>
			</div>
			<div id="questoes" width="100px" name="questoes" style="margin-left: 3px;">
				<div id="div_questoes" style="width: 100%;"> </div>
				<input type="button" id="btnAtribuirQuestoes" class="class_botao" name="btnAtribuirQuestoes" style="width:auto;" value="Atribuir Selecionadas" onclick="xajax_atribuir_questoes(xajax.getFormValues('frm'));" />
			</div>
			<div id="configuracoes" width="100px" name="configuracoes" style="margin-left: 3px;">
				<div id="div_configuracoes" style="width: 100%;">
					<table>
						<tr>
							<td colspan="2"><label for="data_inicio_treinamento_lideranca" class="labels">Iníc p; p;Treinamen p;Liderança</label><br />
                            	<input name="data_inicio_treinamento_lideranca" type="text" class="caixa" id="data_inicio_treinamento_lideranca" size="10" onKeyPress="transformaData(this, event);" value="" placeholder="Trein. Lid" onBlur="return checaTamanhoData(this,10);" />
                            </td>
						</tr>
						<tr>
							<td colspan="2"><label for="data_inicio_treinamento_funcionarios" class="labels">Iníc p; p;Treinamen p;Colaboradores</label><br />
                            	<input name="data_inicio_treinamento_funcionarios" type="text" class="caixa" id="data_inicio_treinamento_funcionarios" size="10" onKeyPress="transformaData(this, event);" value="" placeholder="Trein. Colab." onBlur="return checaTamanhoData(this,10);" />
                            </td>
						</tr>
						<tr>
							<td width="17%"><label for="data_inicio" class="labels">Iníc p; p;Au p;Avaliação</label><br />
                            	<input name="data_inicio" type="text" class="caixa" id="data_inicio" size="10" onKeyPress="transformaData(this, event);" value="" placeholder="Auto Aval." onBlur="return checaTamanhoData(this,10);" />
                            </td>
							<td width="83%"><label for="dias_func" class="labels">Dias Colaboradores</label><br />
                            	<input name="dias_func" type="text" class="caixa" id="dias_func" size="4" placeholder="Dias" value="10" />
                            </td>

						</tr>
						<tr>
							<td><label for="data_inicio_coord" class="labels">Iníc p;Supervisão</label><br />
                            <input name="data_inicio_coord" type="text" class="caixa" id="data_inicio_coord" size="10" onKeyPress="transformaData(this, event);" value="" placeholder="Supervisão" onBlur="return checaTamanhoData(this,10);" />
                            </td>
							<td><label for="dias_sup" class="labels">Dias Supervisão</label><br />
                            <input name="dias_sup" type="text" class="caixa" id="dias_sup" size="4" value="10" placeholder="Dias" />
                            </td>

						</tr>
						<tr>
							
							<td><label for="data_inicio_consenso" class="labels">Iníc p;Consenso</label><br />
                            <input name="data_inicio_consenso" type="text" class="caixa" id="data_inicio_consenso" size="10" placeholder="Consenso" onKeyPress="transformaData(this, event);" value="" onBlur="return checaTamanhoData(this,10);" />
                            </td>
							<td><label for="dias_consenso" class="labels">Dias Consenso</label><br />
                            <input name="dias_consenso" type="text" class="caixa" id="dias_consenso" size="4" placeholder="Dias" value="15" />
                            </td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<td width="4%"><label class="labels">Alvo</label></td>
							<td width="96%">
								<input type="checkbox" name="alvo[]" id="alvo" value="1" /> <label class="labels">CLT</label><br />
								<input type="checkbox" name="alvo[]" id="alvo2" value="2" /> <label class="labels">PJ</label><br />
								<input type="checkbox" name="alvo[]" id="alvo4" value="4" onclick="if(this.checked){frm.alvo.checked=false;frm.alvo2.checked=false}" /> <label class="labels">AVULSO</label>
							</td>
						</tr>
					</table>	
				</div>
			</div>
		</div>	
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>