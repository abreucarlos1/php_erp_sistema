<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" enctype="multipart/form-data">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
                <tr>
        			  <td valign="middle">
                      <input name="btn_buscar" id="btn_buscar" type="button" class="class_botao" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));"/>
                      </td>
      			  </tr>
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
                <table border="0" width="100%">
                  <tr>
                        <td width="13%"><label for="id_os" class="labels">Projeto</label><br />
                        <select name="id_os" id="id_os" class="caixa" onchange="xajax_preenche_info_os(this.value);xajax_preenchedisciplina(this.value);" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                        <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                      </select>
                    </td>
                    <td width="87%"><label for="disciplina" class="labels">Disciplina</label><br />
                        <select name="disciplina" class="caixa"  id="disciplina" onkeypress="return keySort(this);">
                      </select>
                    </td>                    
                  </tr>
                </table>
                <table width="100%" border="0">
                  <tr>
                    <td width="16%"><label class="labels">Coordenador INT:</label></td>
                    <td width="84%"><div class="labels" id="div_coordenador"> </div></td>
                  </tr>
                  <tr>
                    <td><label class="labels">Cliente:</label></td>
                    <td><div class="labels" id="div_cliente"> </div></td>
                  </tr>
                  <tr>
                    <td><label class="labels">Coordenador Cliente:</label></td>
                    <td><div class="labels" id="div_coordenador_cliente"> </div></td>
                  </tr>
                  <tr>
                    <td valign="top" colspan="2" style="border-width:1px; border-style:solid; border-color:#EDEDED; height:200px;">
                    <div id="div_versoes" style="height:200px; overflow:scroll-y;"> </div></td>
                  </tr>
                </table></td>
        </tr>
      </table>      
      <div id="div_arquivos" style="height:300px;"> </div>

</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>