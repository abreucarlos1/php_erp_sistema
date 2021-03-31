<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:750px;">
<form name="frm" id="frm" action="relatorios/rel_notebooks_os.php" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Gerar Excel" onclick="frm.submit();" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
			  	<table border="0">
					<tr>
	                    <td>
	                    	<label for="id_os" class="labels">OS</label><br />
		                    <select name="id_os" class="caixa" id="id_os">
								<option value="">SELECIONE</option>
								<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
							</select>
	                    </td>
						<td>
							<label for="dataini" class="labels">Data inicial</label><br />
	                    	<input name="dataini" id="dataini" type="text" size="10" class="caixa" onKeyPress="transformaData(this, event);" onBlur="verificaDataErro(this.value, 'dataini');" />
	                    </td>
						<td>
							<label for="datafin" class="labels">Data final</label><br />
	                    	<input name="datafim" id="datafim" type="text" size="10" class="caixa" onKeyPress="transformaData(this, event);" onBlur="verificaDataErro(this.value, 'datafin');" />
	                    </td>
	                    <td>
	                    	<input type="button" name="filtrar" id="filtrar" class="class_botao" value="Filtrar" onclick="showLoader();xajax_atualizatabela(xajax.getFormValues('frm'));" />
	                    </td>
					</tr>
				</table>
			</td>
        </tr>
        <tr><td colspan="3"><div id="numRegistros" class="labels" style="text-align:right;"></div></td></tr>
      </table>
	  <div id="lista" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>