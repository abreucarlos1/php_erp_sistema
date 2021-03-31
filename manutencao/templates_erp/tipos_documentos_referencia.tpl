<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
				  <td width="13%"><label for="id_disciplina" class="labels">Disciplina</label><br />
                    <select name="id_disciplina" class="caixa"  id="id_disciplina" onkeypress="return keySort(this);" onchange="xajax_preencheAtividades(this.options[this.selectedIndex].value); ">
                      <option value="">SELECIONE</option>
                      <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                  </select>
                  <input type="hidden" name="id_tipo" id="id_tipo" value="" /></td>
				  <td width="17%"><label for="id_tipo_ref" class="labels">Tipo Doc. Referência:</label><br />
                    <select name="id_tipo_ref" class="caixa"  id="id_tipo_ref" onkeypress="return keySort(this);">
                      <option value="">SELECIONE</option>
                      <smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
                  </select></td>
					<td width="37%"><label for="tipo_doc" class="labels">Documento Referência</label><br />
                    <input name="tipo_doc" type="text" class="caixa" id="tipo_doc" placeholder="Tipo documento" size="40" /></td>
					<td width="33%"><label for="abreviacao" class="labels">Abreviação</label><br />
                    <input name="abreviacao" type="text" class="caixa" id="abreviacao" size="10" placeholder="Abreviação" maxlength="3" /></td>
				</tr>
			</table>
		  <table border="0" width="100%">
			<tr>
					<td width="11%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="30" />
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="setores" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>