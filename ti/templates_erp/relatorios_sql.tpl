<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="./relatorios/relatorios_sql_excel.php" method="POST" target="_blank">
	  <table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_salvarConsulta(xajax.getFormValues('frm'));frm.submit();" value="<smarty>$botao[8]</smarty>" /></td>
					</tr>
					<tr>
        				<td valign="middle"><input name="btnvisualizar" id="btnvisualizar" type="button" class="class_botao" value="<smarty>$botao[19]</smarty>" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
				<input name="id_grupo" type="hidden" id="id_grupo" value="" />
			</td>
       		<td colspan="2" valign="top" class="espacamento">
			  	<table>
			  		<tr>
			  			<td colspan="2">
			  				<label for="query" class="labels">Descrição da consulta</label><br />
			  				<input type="text" id="nome_consulta" name="nome_consulta" class="caixa" size="70" />
			  			</td>
			  		</tr>
					<tr>
						<td colspan="2">
							<label for="query" class="labels">Consulta SQL</label><br />
	                        <textarea name="query" type="text" class="" id="query" placeholder="CONSULTA SQL" style="width:100%;" rows="10"></textarea>
	                        <input type="hidden" id="id_consulta" name="id_consulta" />
						</td>
					</tr>
					<tr>
						<td width="10%">
							<label class="labels">MYSQL</label><input type="radio" id="rdo_tipo_mysql" name="rdo_tipo" value="MYSQL" checked="checked" /><br />
							<label class="labels">MSSQL</label><input type="radio" id="rdo_tipo_mssql" name="rdo_tipo" value="MSSQL" /><br />
						</td>
						<td>
							<label for="query" class="labels">Consulta SQL</label><br />
							<select id="selConsultas" name="selConsultas" class="caixa" onchange="xajax_editar(this.value)">
								<smarty>html_options values=$option_sql_values output=$option_sql_output</smarty>
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