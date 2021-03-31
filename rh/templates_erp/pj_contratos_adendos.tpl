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
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="window.close();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
				  <td width="23%" valign="top"><label class="labels"><smarty>$campo[7]</smarty></label><br />					
				  <label><smarty>$num_contrato</smarty></label>
                  <input name="id_contrato" type="hidden" id="id_contrato" value="<smarty>$id_contrato</smarty>" /></td>				  
                  <td width="77%" valign="top"><label for="tipo_adendo" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="tipo_adendo" class="caixa" id="tipo_adendo" onkeypress="return keySort(this);" onchange="xajax_campos(xajax.getFormValues('frm'));">
					    <smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
				      </select>
                  </td>
					</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td valign="top">
              <div id="div_campos" class="labels"> </div></td>
	        </tr>
		    </table>
		  </td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>