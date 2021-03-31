<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btngerar" id="btngerar" type="button" class="class_botao" value="Gerar Book" onclick="if(document.getElementById('id_os').value!=''){xajax_gerabook(xajax.getFormValues('frm'));}else{alert('É necessário preencher todos os campos!');}" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
			<table width="100%" border="0">
            <tr>
              <td colspan="3" align="left"><label for="id_os" class="labels">OS / Projeto</label><br />
			    <select name="id_os" id="id_os" class="caixa" style="width:100%;" onchange="xajax_disciplinas(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
			      <option value="">Selecione</option>
			      <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
			      </select>			    </td>
              </tr>
            
            <tr>
              <td><label for="disciplina" class="labels">Disciplina</label><br />
                <select name="disciplina" id="disciplina" class="caixa" onchange="xajax_buscaAtividades(xajax.getFormValues('frm'));" onkeypress="return keySort(this);">
              </select></td>
            </tr>
            <tr>
              <td><label class="labels">Atividade</label><br />
                <select name="atividade" id="atividade" class="caixa" onkeypress="return keySort(this);">
                 
              </select></td>
            </tr>
            <tr>
            	<td>
            		<label class="labels" style="float:left;width:70px;">Impressão</label><input style="float:left;" type="radio" name="rdoImpressao" id="rdoImpressao" value="1" /><br />
            		<label class="labels" style="float:left;width:70px;">Padrão</label><input style="float:left;" type="radio" name="rdoImpressao" id="rdoImpressao" checked="checked" value="0" />
            	</td>
            </tr>
            </table>          
          </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>