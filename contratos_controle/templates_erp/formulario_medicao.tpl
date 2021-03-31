<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action=""  method="POST">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%">
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td width="678" colspan="2" align="left" valign="top" class="espacamento">
            <table border="0" width="100%">
                <tr>
                  <td width="5%"><label for="escolhacoord" class="labels">Coordenador</label><br />
                    <select name="escolhacoord" class="caixa" id="escolhacoord" onchange="xajax_preencheos(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
                    <smarty>html_options values=$option_coord_values output=$option_coord_output</smarty>
                    </select>
                    <input name="id_medicao" id="id_medicao" type="hidden" value="" />
                    </td>
                </tr>
                  <tr>
                    <td width="6%"><label for="escolhaos" class="labels">Projeto</label><br />
                        <select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);" onchange="xajax_preencheestrut(this.value);if(this.value!=0){document.getElementById('btn_inserir').disabled=false}else{document.getElementById('btn_inserir').disabled=true};xajax_atualizatabela(xajax.getFormValues('frm'));">
                      </select></td>
                    </tr>
                <tr>
                  <td width="5%"><label for="escolhaedt" class="labels">Disciplina</label><br />
                    <select name="escolhaedt" class="caixa" id="escolhaedt" onchange="xajax_preenchevalor(xajax.getFormValues('frm'));xajax_atualizatabela(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
                    </select></td>
                </tr>            
		    </table>
	      </td>
        </tr>
        <tr>
          <td colspan="3" valign="top">
			<table border="0" width="100%">
		    <tr>
		      <td width="20%"><label for="total_os" class="labels">Valor total/Projeto</label><br />
                <input class="caixa" type="text" name="total_os" id="total_os" value="0" size="25" readonly="readonly" />
                </td>
		      	<td width="13%"><label for="valor" class="labels">Valor total/EDT</label><br />
                <input class="caixa" type="text" name="valor" id="valor" value="0" size="20" readonly="readonly" />
                </td>
				<td width="13%"><label for="ajuste" class="labels">Ajuste</label><br />
                <input class="caixa" type="text" name="ajuste" id="ajuste" value="0" size="10" onkeyup="xajax_calcula(xajax.getFormValues('frm'),'ajuste');" /></td>
				<td width="54%"><label for="valor_ajuste" class="labels">Valor ajustado</label><br />
                <input class="caixa" type="text" name="valor_ajuste" id="valor_ajuste" value="0" size="20" readonly="readonly" /></td>
		    </tr>
            <tr>
              <td width="20%"><label class="labels">Período</label><br />
              <div style="display:block">
                  <select name="mes" class="caixa" id="mes" onkeypress="return keySort(this);">
                      <smarty>html_options values=$option_per_values selected=$option_per_id output=$option_per_output</smarty>
                  </select><input class="caixa" type="text" name="ano" id="ano" value="<smarty>$ano</smarty>" size="5" />
              </div>
              </td>
		      <td width="13%"><label for="percentual" class="labels">Perc. med.</label><br />
                <input class="caixa" type="text" name="percentual" id="percentual" value="0" size="10" onkeyup="xajax_calcula(xajax.getFormValues('frm'),'valor');" /></td>
		      <td width="13%"><label for="valor_med" class="labels">Valor med.</label><br />
                <input class="caixa" type="text" name="valor_med" id="valor_med" value="0" size="20" onkeyup="xajax_calcula(xajax.getFormValues('frm'),'percent');" /></td>
		      <td width="54%" valign="bottom">
              	<input type="button" class="class_botao" value="Inserir" name="btn_inserir" id="btn_inserir" disabled="disabled" onclick="xajax_insere(xajax.getFormValues('frm'))" /></td>
	        </tr>
		    </table>
          </td>
        </tr>
      </table>
	  <div id="medicoes" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>