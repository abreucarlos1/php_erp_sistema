<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<input type='hidden' id='id_chamado' name='id_chamado' />
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle" >
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
					<td>
						<label for="busca" class="labels">Busca</label><br />
						<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="15" placeholder="Busca" />
					</td>
				</tr>
			</table>
			</td>
        </tr>        
        <tr>
          <td colspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td><label for="cliente" class="labels">Cliente *</label><br />
						<select name="cliente" class="caixa" id="cliente" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
					<td valign="top"><label for="descricao_integracao" class="labels">Descrição das necessidades (Trabalhos Críticos, NR's, etc) *</label><br />
                    <textarea class='caixa' id='descricao_integracao' cols="60" name='descricao_integracao' placeholder="Descrição"></textarea>
                    </td>
				</tr>
				<tr>
					<td><label for="funcionario" class="labels">Funcionário *<sub><i>Utilizar a tecla CTRL para selecionar vários funcionários</i></sub></label><br />
                    <select name="funcionario[]" class="caixa" multiple="multiple" id="funcionario" onchange="xajax_getFuncionariosIntegrados(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_func_values
							output=$option_func_output</smarty>
					</select>
                    </td>
				</tr>
				<tr>
					<td><label for="data" class="labels">Data Integração *</label><br />
                    <input type='text' name="data" class="caixa" id="data" size="10" maxlength="10" placeholder="Data" onkeypress="return txtBoxFormat(document.frm, 'data', '99/99/9999', event);" />
                    </td>
				</tr>
				<tr>
					<td><label for="status" class="labels">Status *</label><br />
                    <select name="status" disabled="disabled" class="caixa" id="status" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_status_values
							output=$option_status_output</smarty>
					</select>
                    </td>
				</tr>
				<tr id='trInteracao' style='display:none;'>
					<td valign="top"><label for="descricao_interacao" class="labels">Descrição da alteração *</label><br />
                    <textarea class='caixa' id='descricao_interacao' cols="60" name='descricao_interacao' placeholder="Descrição"></textarea>
                    </td>

				</tr>
			</table>
		</td>
        </tr>
      </table>
	  <div id="div_lista" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>