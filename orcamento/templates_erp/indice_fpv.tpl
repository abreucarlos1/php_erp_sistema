<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btn_atualizar" type="button" class="class_botao" id="btn_atualizar" value="Inserir" onclick="if(confirm('Deseja inserir os dados do índice?')){xajax_inserir(xajax.getFormValues('frm'));}" />
				  </td>
				<tr>
				  <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			  </tr>
			      <input type="hidden" value="" id="id_indice" name="id_indice" />
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
              <tr>
                <td width="13%"><label for="tipo_indice" class="labels">Tipo&nbsp;índice</label><br />
                                <select name="tipo_indice" class="caixa" id="tipo_indice" onkeypress="return keySort(this);" >
                                <smarty>html_options values=$option_indice_values output=$option_indice_output</smarty>
                  </select>
                </td>
                <td width="8%"><label for="data" class="labels">Data</label><br />
                <input name="data" type="text" class="caixa" id="data" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm, 'data', '99/99/9999', event);" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' /></td>
                <td width="56%"><label for="percentual" class="labels">Percentual/Valor</label><br /> 
                  <input name="percentual" type="text" class="caixa" id="percentual" size="7" placeholder="Percentual" maxlength="7" /></td>
              </tr>
            </table>
		</td>
        </tr>
      </table>
    <div id="indices" style="width:100%;">&nbsp;</div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>