<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="width:100%;height:660px;">
	<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" target="_blank">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">        
	        <tr>
	          <td width="122" rowspan="2" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="xajax_inserir(xajax.getFormValues('frm'));"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			  </table></td>
	          <td width="6" rowspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top">
			  <table cellspacing="10px" cellpadding="0">
				<tr>
					<td align="left"><label class="labels">GRUPOS</label>
						<br />
	                    <select name="codigo_grupo" class="caixa" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));" id="codigo_grupo" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_grupos_values output=$option_grupos_output</smarty>
						</select>
					</td>
					<td align="left"><label class="labels">SUBGRUPOS</label><br />
				    	<select class="caixa" name="id_sub_grupo" id="id_sub_grupo">
				    		<smarty>html_options values=$option_subgrupos_values output=$option_subgrupos_output</smarty>
				    	</select>
					</td>
				</tr>
			  </table></td>
	        </tr>
	      </table>
	</form>
	<div id="codigos" style="width:100%;margin-top: 15px;"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>