<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle">
                   	    <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
            <table>
				<tr>
					<td><label for="data" class="labels"><smarty>$campo[2]</smarty></label><br />
                    <input name="data" type="text" class="caixa" id="data" size="10" onKeyPress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onBlur="return checaTamanhoData(this,10);" />  
                        </td>
					<td><label for="analista" class="labels"><smarty>$campo[3]</smarty></label><br />
                    <select name="analista" class="caixa" id="analista" onkeypress="return keySort(this);" onchange="xajax_rotinas(xajax.getFormValues('frm'));xajax_atualizatabela(xajax.getFormValues('frm'));">
							<smarty>html_options values=$option_analistas_values output=$option_analistas_output</smarty>
						</select> </td>
					<td><label for="cb_rotinas" class="labels"><smarty>$campo[4]</smarty></label><br />
                    <select name="cb_rotinas" class="caixa" id="cb_rotinas" onkeypress="return keySort(this);">
							<option value="">SELECIONE</option>
						</select></td>
					</tr>
			</table>
        	  <table>
        	    <tr valign="top">
        	      <td><label for="observacao" class="labels"><smarty>$campo[5]</smarty></label><br />
       	          <input name="observacao" id="observacao" placeholder="Observação" class="caixa" size="100" maxlength="255" value="ROTINA REALIZADA"></td>
       	        </tr>
      	    </table>
        	  <table>
        	    <tr>
        	      <td><label class="labels"><smarty>$campo[6]</smarty></label><br />
        	        <input name="semana" id="semana" type="text" class="caixa" readonly="readonly" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" size="15"/>
        	        <img src="../imagens/cal.png" style="cursor:pointer;" width="16" height="16" border="0" alt="Escolha a data" onclick="javascript:NewCssCal('semana');"  /></td>
        	      <td><input class="class_botao" type="button" name="button" id="button" value="Seleciona" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));" /></td>
      	      </tr>
      	    </table></td>
        </tr>
      </table>
	  <div id="dv_rotinas" style="width:100%"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>