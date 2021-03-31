<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_salvar(xajax.getFormValues('frm'));" value="<smarty>$botoes[1]</smarty>" />
					</td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnrelatorios" type="button" class="class_botao" id="btnrelatorios" onclick="xajax_abreJanela();" value="<smarty>$botoes[18]</smarty>" />					
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botoes[2]</smarty>" onclick="history.back();" /></td>
				</tr>
				<tr>
	                <td><label for="busca" class="labels">Busca</label><br />
	                	<input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onKeyUp="iniciaBusca2.verifica(this);" size="15">
	                </td>
              	</tr>
			</table></td>
        </tr>
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%">
          	<tr>
              <td width="5%" valign="top" rowspan="4"><label for="funcionario" class="labels">Funcionário</label><br />
                  <select name="funcionario[]" class="caixa" id="funcionario" multiple="multiple"  style="height: 140px;" onkeypress="return keySort(this);">
                      <smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
                  </select>
                  <input type="hidden" name="idCabecalho" id="idCabecalho" value="" />
                  <input type="hidden" name="idItem" id="idItem" value="" /></td>
              <td width="4%" valign="top"><label for="treinamento" class="labels">Treinamento</label><br />
					<select onchange="xajax_buscaVigencia(this.value);" name="treinamento" class="caixa" id="treinamento" onkeypress="return keySort(this);" style="width:300px;">
						<smarty>html_options values=$option_treina_values output=$option_treina_output</smarty>
					</select>
				</td>
				<td width="96%" valign="top"><label for="tipo" class="labels">Classificação</label><br />
					<select name="tipo" class="caixa" id="tipo" onkeypress="return keySort(this);">
					<smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
				</select></td>
            </tr>
             <tr>
	        	<td width="5%" colspan="2">
	        		<table>
	        			<tr>
	        				<td width="5%"><label for="data_treinamento" class="labels">Data Trein.</label><br />
								<input name="data_treinamento" type="text" class="caixa" id="data_treinamento" onkeypress="transformaData(this, event);" value="<smarty>$data_treinamento</smarty>" onblur="xajax_calcula_vencimento(this.value,vigencia.value);return checaTamanhoData(this,10);" size="10" maxlength="10" />
								<input name="data_observacao_status" type="hidden" class="caixa" id="data_observacao_status" />
							</td>
							<td width="5%">
								<label class="labels">Vigência (Meses)</label><br />
								<input name="vigencia" type="text" class="caixa" readonly="readonly" id="vigencia" size="8" maxlength="2" />
							</td>
							<td width="5%">
								<label class="labels">Venc. Trein.</label><br />
								<input name="data_vencimento" type="text" class="caixa" readonly="readonly" id="data_vencimento" size="10" maxlength="10" readonly="readonly" />
							</td>
						</tr>
						<tr>
							<td width="5%" nowrap="nowrap">
								<label for="duracao" class="labels">Carga Horária</label><br />
								<input name="duracao" type="text" class="caixa" id="duracao" value="" placeholder="Duração" size="8" />
							</td>
							<td width="5%" nowrap="nowrap">
								<label for="valor" class="labels">Valor Hora</label><br />
								<input name="valor" type="text" class="caixa" id="valor" value="" size="8" placeholder="Valor" />
								<textarea name="observacoes_situacao" type="text" id="observacoes_situacao" value="" style="display:none;"></textarea>
								<input type="text" name="id_funcionario_verificacao" type="text" id="id_funcionario_verificacao" value="" style="display:none;"></textarea>
							</td>
							<td width="5%" valign="top"><label for="situacao" class="labels">Situação</label><br />
								<input type='hidden' id='avaliar_eficacia' name='avaliar_eficacia' />
								<select name="situacao" class="caixa" id="situacao" onchange="abreJanelaSituacao(this.value, xajax.$('idCabecalho').value,'<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>',frm.avaliar_eficacia.value);">
									<smarty>html_options values=$option_valores_values['situacao'] output=$option_valores_output['situacao']</smarty>
								</select>
							</td>
							<td width="75%">
								<label for="valor" class="labels">Renovar Trein.</label><br />
								<select class='caixa' name='selRenovar' id='selRenovar'>
									<option value='1' selected="selected">S</option>
									<option value='0'>N</option>
								</select>
							</td>    
						</tr>
					</table>
				</td>
	        </tr>
          </table>
        </td>
        </tr>
      </table>
      <div id="div_filtros" style="float:left; text-align:left;height:40px">
      	<label class="labels">Filtros</label><br />
      	<input onclick="iniciaBusca2.verifica(document.frm.busca);" type="radio" id="outrosFiltros" name="outrosFiltros" value="todos" class="caixa" /><label class="labels">Todos</label>
      	<input onclick="iniciaBusca2.verifica(document.frm.busca);" type="radio" id="outrosFiltros" name="outrosFiltros" value="concluidos" class="caixa" /><label class="labels">Concluídos</label>
      	<input onclick="iniciaBusca2.verifica(document.frm.busca);" type="radio" id="outrosFiltros" name="outrosFiltros" value="pendentes realizar" class="caixa" checked="checked" /><label class="labels">Realização Pendente</label>
      	<input onclick="iniciaBusca2.verifica(document.frm.busca);" type="radio" id="outrosFiltros" name="outrosFiltros" value="pendentes eficacia" class="caixa" /><label class="labels">Eficácia Pendente</label>
      	<input onclick="iniciaBusca2.verifica(document.frm.busca);" type="radio" id="outrosFiltros" name="outrosFiltros" value="nao renovaveis" class="caixa" /><label class="labels">Não Renováveis</label>
      </div>
	  <div id="treinamentos_efetuados" style="width:100%;"> </div>
      <label class="labels" style="float:left;display:none;"><i>Atenção: alterar apenas itens planejados</i></label>
      <div style="float:right;"><label class="labels" id="numero_registros"></label></div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>