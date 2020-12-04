<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="relatorios/rel_acessos_catraca_excel.php" method="POST">
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
				  <td width="21%"><label class="labels"><smarty>$campo[2]</smarty></label></td>
			</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td width="25%">
                <table width="24%" border="0">
						<tr>
							<td width="18%">
									<input name="intervalo" type="radio" value="0" checked="checked" onclick="document.getElementById('periodo').style.display='none';" />							</td>
							<td width="82%" align="left"><label class="labels"><smarty>$campo[3]</smarty></label></td>
						</tr>
						<tr>
							<td>
									<input name="intervalo" type="radio" value="1" onclick="document.getElementById('periodo').style.display='inline';document.getElementById('dataini').focus();" />							</td>
							<td align="left"><label class="labels"><smarty>$campo[4]</smarty></label></td>
						</tr>
					</table>
                   </td>
				<td width="75%">
                    <div id="periodo" style="display:none">
						<table width="100%" border="0">
							<tr>
								<td width="27%"><label for="dataini" class="labels"><smarty>$campo[5]</smarty></label><br />
                                <input name="dataini" type="text" class="caixa" id="dataini" size="10" placeholder="Data ini." onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'datafim', 10);" />
                                </td>
							</tr>
							<tr>
								<td><label for="datafim" class="labels"><smarty>$campo[6]</smarty></label><br />
                                <input name="datafim" type="text" class="caixa" id="datafim" size="10" placeholder="Data fin." onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'funcionario', 10);"  />
                                </td>
							</tr>
						</table>
					</div>
                </td>
			  </tr>
			  <tr>
				<td colspan="3"><label for="funcionario" class="labels"><smarty>$campo[7]</smarty></label><br />
					<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
			    	</select>
                </td>
			  </tr>
			</table>
           </td>
        </tr>
    </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>