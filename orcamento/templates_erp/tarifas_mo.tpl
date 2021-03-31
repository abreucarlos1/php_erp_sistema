<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btn_atualizar" type="button" class="class_botao" id="btn_atualizar" value="Inserir" onclick="if(confirm('Deseja inserir os dados do valor?')){xajax_inserir(xajax.getFormValues('frm'));}" />
				  </td>
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
                <td width="13%"><label for="regiao" class="labels">Região</label><br />
                                <select name="regiao" class="caixa" id="regiao" onkeypress="return keySort(this);xajax_atualizatabela(xajax.getFormValues('frm'));" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" >
					<smarty>html_options values=$option_regiao_values output=$option_regiao_output selected=$selecionado</smarty>
                  </select>
                </td>
                <td width="13%"><label for="id_cargo" class="labels">Profissional</label><br />
                                <select name="id_cargo" class="caixa" id="id_cargo" onkeypress="return keySort(this);" >
                    <option value="0">SELECIONE</option>
					<smarty>html_options values=$option_cargo_values output=$option_cargo_output</smarty>
                  </select>
                </td>
                <td width="8%"><label for="data" class="labels">Data</label><br />
                <input name="data" type="text" class="caixa" id="data" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm, 'data', '99/99/9999', event);" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' /></td>
                <td width="56%"><label for="valor" class="labels">Valor</label><br /> 
                  <input name="valor" type="text" class="caixa" id="valor" size="7" placeholder="Valor" maxlength="8" /></td>
              </tr>
            </table>
		</td>
        </tr>
      </table>
    <div id="valores" style="width:100%;"> </div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>