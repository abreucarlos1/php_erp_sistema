<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btn_atualizar" type="button" class="class_botao" id="btn_atualizar" value="Inserir" onclick="if(confirm('Deseja inserir os dados do valor?')){xajax_inserir(xajax.getFormValues('frm'));}" /></td>
       			</tr>
				<tr>
				  <td valign="middle"><input name="btn_destino" type="button" class="class_botao" id="btn_destino" value="Copiar valores" onclick="if(confirm('Deseja inserir os dados da origem?')){copia_origem(document.getElementById('cliente').value)};" disabled="disabled" /></td>
       			</tr>
				<tr>
				  <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			  </tr>
			      <input type="hidden" value="" id="id_valor" name="id_valor" />
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
              <tr>
                <td colspan="4"><label for="cliente" class="labels">Cliente</label><br />
                                <select name="cliente" class="caixa" id="cliente" onkeypress="return keySort(this);" onchange="if(this.value){document.getElementById('btn_destino').disabled = false;}else{document.getElementById('btn_destino').disabled = true;};xajax_atualizatabela(xajax.getFormValues('frm'));" >
					<smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
                  </select>
                </td>

              </tr>
              <tr>
                <td width="13%"><label for="id_atividade" class="labels">Despesa</label><br />
                                <select name="id_atividade" class="caixa" id="id_atividade" onkeypress="return keySort(this);" >
                    <option value="0">SELECIONE</option>
					<smarty>html_options values=$option_atividade_values output=$option_atividade_output</smarty>
                  </select>
                </td>
                <td width="8%"><label for="data" class="labels">Data</label><br />
                <input name="data" type="text" class="caixa" id="data" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm, 'data', '99/99/9999', event);" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' /></td>
                <td width="11%"><label for="valor_dvm" class="labels">Valor/Hora</label><br /> 
                  <input name="valor_dvm" type="text" class="caixa" id="valor_dvm" size="7" placeholder="Valor" maxlength="8" /></td>
                <td width="68%"><label for="valor_cli" class="labels">Valor/H Cliente</label><br /> 
                  <input name="valor_cli" type="text" class="caixa" id="valor_cli" size="7" placeholder="Valor" maxlength="8" /></td>
              </tr>
            </table>
		</td>
        </tr>
      </table>
    <div id="valores" style="width:100%;"> </div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>