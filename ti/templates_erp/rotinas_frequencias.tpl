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
            <table>
				<tr>
					<td>
						<label class="labels"><smarty>$campo[2]</smarty></label><br />
	                    <div id="dv_rotina">&nbsp;</div>   
						<input type="hidden" name="id_ti_rotina_frequencia" id="id_ti_rotina_frequencia" value="" />
                    </td>
					<td>
						<label class="labels"><smarty>$campo[2]</smarty></label><br />
                    	<div id="dv_frequencia">&nbsp;</div>
                    </td>
					</tr>
			</table>
        	  </td>
        </tr>
      </table>
	  <div id="frequencias" style="width:100%">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>