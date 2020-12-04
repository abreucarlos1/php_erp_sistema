<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="item" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="item" class="caixa" id="item" onkeypress="return keySort(this);">
					    <smarty>html_options values=$option_item_values output=$option_item_output</smarty>
				      </select>
						<input name="id_sgi_controle" type="hidden" id="id_sgi_controle" value="" /></td>
					<td width="91%"><label for="requisito" class="labels"><smarty>$campo[3]</smarty></label><br />
					  <select name="requisito" class="caixa" id="requisito" onkeypress="return keySort(this);">
					    <smarty>html_options values=$option_requisito_values output=$option_requisito_output</smarty>
				      </select></td>
					</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="9%"><label for="data_realizacao" class="labels"><smarty>$campo[4]</smarty></label><br />
		        <input name="data_realizacao" type="text" class="caixa" id="data_realizacao" onkeypress="transformaData(this, event);" value="<smarty>$data_realizacao</smarty>" onblur="xajax_calcula_vencimento(this.value,vigencia.value);return checaTamanhoData(this,10);" size="10" maxlength="10" /></td>
		      <td width="9%"><label for="vigencia" class="labels"><smarty>$campo[5]</smarty></label><br />
              <input name="vigencia" type="text" class="caixa" id="vigencia" value="12" onblur="xajax_calcula_vencimento(data_realizacao.value,this.value);" size="8" maxlength="2" /></td>
		      <td width="82%"><label for="data_vencimento" class="labels"><smarty>$campo[6]</smarty></label><br />
                <input name="data_vencimento" type="text" class="caixa" id="data_vencimento" value="" placeholder="Data venc." size="10" maxlength="10" readonly="readonly" /></td>
	        </tr>
		    </table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[7]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
				</tr>

			</table></td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>