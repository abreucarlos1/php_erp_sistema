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
				<input name="id_grupo" type="hidden" id="id_grupo" value="" />
				</td>
        	<td colspan="2" valign="top" class="espacamento">
			  	<table border="0" width="100%">
					<tr>
						<td width="22%"><label for="emailGrupo" class="labels">Nome do Grupo</label><br />
                        <input name="emailGrupo" type="text" class="caixa" id="emailGrupo" size="40" placeholder="E-mail" />
						</td>
					</tr>
					<tr>
						<td width="22%"><label for="id_funcionario" class="labels">Funcionário</label><br />
							<select name="id_funcionario" class="caixa" id="emailGrupo" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
							</select>
						</td>
					</tr>
					<tr>
						<td width="22%"><label for="tipoEnvio" class="labels">CC/CCO</label><br />
							<select name="tipoEnvio" class="caixa" id="tipoEnvio" onkeypress="return keySort(this);">
								<option value="to">CC</option>
								<option value="cco">CCO</option>
							</select>
						</td>
					</tr>
				</table>
  			</td>
        </tr>
      </table>	  
	  <div id="divLista" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>