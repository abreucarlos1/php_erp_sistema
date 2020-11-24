<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" disabled="disabled" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
						<input type="hidden" id="id_numdvm" name="id_numdvm" value="" />
                    </tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
            <table border="0" width="100%">
			  <tr>
					<td width="21%"><label class="labels"><smarty>$campo[2]</smarty></label><br />
					 <div id="rotulo_projeto" class="labels" style="width:150px;">&nbsp;</div>
                </td>
				<td width="20%"><label for="numcliente" class="labels"><smarty>$campo[3]</smarty></label><br />
						<input name="numcliente" type="text" class="caixa" placeholder="Número Cliente" id="numcliente">
    			</td>
					<td width="10%"><label for="cod_cliente" class="labels"><smarty>$campo[4]</smarty></label><br />
						<input name="cod_cliente" type="text" class="caixa" placeholder="Código Cliente" id="cod_cliente" size="10" maxlength="11">
                      </td>
		     	 <td width="49%"><label for="complemento" class="labels"><smarty>$campo[5]</smarty></label>
		        	<input name="complemento" type="text" class="caixa" id="complemento" placeholder="Complemento" size="50"></td>
				</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>

		      <td width="14%"><label for="id_disciplina" class="labels"><smarty>$campo[6]</smarty></label><br />
              <select name="id_disciplina" class="caixa"  id="id_disciplina" onchange="xajax_preencheCombo(this.options[this.selectedIndex].text,'id_documento');xajax_preenchesequencia('sequencia', this.value, document.getElementById('os').value);" onkeypress="return keySort(this);">
           		    <option value="">SELECIONE</option>
                		<smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
		      </select></td>
                <td width="14%"><label for="CodAtividade" class="labels"><smarty>$campo[9]</smarty></label><br />
                <select name="CodAtividade" id="CodAtividade" class="caixa" onkeypress="return keySort(this);">
                   <option value="">SELECIONE</option>
                </select>                    
                </td>
                  <td width="72%"><label class="labels"><smarty>$campo[15]</smarty></label><br />
                  	<input name="chk_listadocumentos" type="checkbox" id="chk_listadocumentos" value="1">
                  </td>
	        </tr>
		    </table>
            <table width="100%" border="0">
              <tr>
                <td width="57%"><label for="observacao" class="labels"><smarty>$campo[10]</smarty></label><br />
                 <input name="observacao" type="text" class="caixa" placeholder="Observação" id="observacao" size="70" />                    
                </td>
		      <td width="10%"><label for="numfolhas" class="labels"><smarty>$campo[7]</smarty></label><br />
                <input name="numfolhas" type="text" class="caixa" id="numfolhas" placeholder="Folhas" size="5" /></td>
		      <td width="33%"><label for="id_formato" class="labels"><smarty>$campo[8]</smarty></label><br />
               <select name="id_formato" class="caixa"  id="id_formato" onkeypress="return keySort(this);">
                		<option value="">SELECIONE</option>
                		<smarty>html_options values=$option_formato_values output=$option_formato_output</smarty>
   			    </select>
              </td>
              </tr>
            </table> 
              <table width="100%" border="0">
                <tr>
                  <td width="21%"><label for="tag" class="labels"><smarty>$campo[11]</smarty></label><br />					  
					<input name="tag" type="text" class="caixa" id="tag" placeholder="Tag" size="25" />
                  </td>
                  <td width="21%"><label for="tag2" class="labels"><smarty>$campo[12]</smarty></label><br />
                  	<input name="tag2" type="text" class="caixa" id="tag2" placeholder="Tag 2" size="25" />
                  </td>
                  <td width="21%"><label for="tag3" class="labels"><smarty>$campo[13]</smarty></label><br />
                  	<input name="tag3" type="text" class="caixa" id="tag3" placeholder="Tag 3" size="25" />
                  </td>
                  <td width="37%"><label for="tag4" class="labels"><smarty>$campo[14]</smarty></label><br />
                  	<input name="tag4" type="text" class="caixa" id="tag4" placeholder="Tag 4" size="25" />
                  </td>

                </tr>
           </table>
              <table border="0" width="100%">
                <tr>
                  <td colspan="5"><label for="busca" class="labels"><smarty>$campo[17]</smarty></label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="50" /></td>
                </tr>
                <tr>
                  <td width="15%"><label for="os" class="labels"><smarty>$campo[16]</smarty></label><br />
                    <select name="os" id="os" class="caixa" onkeypress="return keySort(this);">
                      <option value="">SELECIONE</option>
                      <option value="-1">TODAS</option>
                      <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                    </select></td>
                  <td width="14%"><label for="filtro_disciplina" class="labels"><smarty>$campo[6]</smarty></label><br />
                    <select name="filtro_disciplina" class="caixa"  id="filtro_disciplina" onkeypress="return keySort(this);">
                      <option value="-1">SELECIONE</option>
                      <smarty>html_options values=$option_setorabr_values output=$option_setorabr_output</smarty>
                    </select></td>
                  <td width="71%" valign="bottom">
                    <input name="btnbusca" id="btnbusca" type="button" class="class_botao" value="Filtrar" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));" />
                  </td>
                </tr>
           </table></td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>