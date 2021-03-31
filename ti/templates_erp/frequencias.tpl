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
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
            <table border="0" width="100%">
				<tr>
					<td width="39%"><label for="frequencia" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <input name="frequencia" type="text" class="caixa" id="frequencia" size="50" placeholder="Frequencia" />
					<input type="hidden" name="id_ti_frequencia" id="id_ti_frequencia" value="" />
                        	
                        </td>
					<td width="10%"><label for="dias" class="labels"><smarty>$campo[3]</smarty></label><br />
					  <input name="dias" type="text" class="caixa" id="dias" size="5" maxlength="2" placeholder="Dias" onkeypress="num_only();" /></td>
					</tr>
			</table>
        	  </td>
        </tr>
      </table>
	  <div id="frequencias" style="width:100%"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>