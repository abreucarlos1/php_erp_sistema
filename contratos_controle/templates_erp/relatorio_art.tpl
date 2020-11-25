<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="relatorios/rel_form_art.php" method="POST">
	<table width="100%" border="0">                 
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="submit" class="class_botao" id="btninserir" value="<smarty>$botao[8]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="18%"><label for="escolhacoord" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="escolhacoord" class="caixa" id="escolhacoord" onchange="xajax_preencheos(this.value);" onkeypress="return keySort(this);">
					    <smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
			        </select></td>
					</tr>
			  <tr>
				<td width="13%"><label for="escolhaos" class="labels"><smarty>$campo[3]</smarty></label><br />
					<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
					<option value="">SELECIONE</option>  
				  </select></td>
				</tr>
			</table>
		  </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>