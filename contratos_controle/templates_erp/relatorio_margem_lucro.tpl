<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="relatorios/rel_margem_lucro.php" method="POST">
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
		      <td width="9%"><label for="escolhafase" class="labels"><smarty>$campo[4]</smarty></label><br />
		        <select name="escolhafase" class="caixa" id="escolhafase" onchange="xajax_preenchecliente(this.value);" onkeypress="return keySort(this);">
		          <smarty>html_options values=$option_fase_values output=$option_fase_output</smarty>
	            </select></td>
	        </tr>
			  <tr>
					<td width="9%"><label for="escolhacliente" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="escolhacliente" class="caixa" id="escolhacliente" onchange="xajax_preencheos(this.value,document.getElementById('escolhafase').value);" onkeypress="return keySort(this);">
			        </select></td>
					</tr>
			  <tr>
				<td width="5%"><label for="escolhaos" class="labels"><smarty>$campo[3]</smarty></label><br />
					<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
				  </select></td>
				</tr>
		    </table>
		  </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>