<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: 700px;">
<form name="frm_atividades" id="frm_atividades" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_atividades'));" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
                <input type="hidden" name="id_atividade" id="id_atividade" value="">
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="5%"><label for="setor" class="labels">Setor</label><br />
							<select name="setor" id="setor" class="caixa" onkeypress="return keySort(this);" onchange="muda_aba(this.value);">
								<smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
							</select></td>
					<td width="8%"><label for="codigo" class="labels">Código</label><br />
							<input name="codigo" type="text" class="caixa" placeholder="Código" id="codigo" size="8" /></td>
					<td width="87%"><label for="atividade" class="labels">Atividade</label><br />
							<input name="atividade" type="text" class="caixa" id="atividade" size="65" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="9%"><label for="horas" class="labels">Quantidade</label><br />
							<input name="horas" type="text" class="caixa" id="horas" size="10" /></td>
					<td width="91%"><label for="formato" class="labels">Unidade</label><br />
						<select name="formato" id="formato" class="caixa" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_formato_values output=$option_formato_output</smarty>
						</select></td>
				</tr>
			</table>
          </td>
        </tr>
      </table>
      <table width="100%">
      <tr>
      <td>
		<div id="my_tabbar" style="position: relative; width: 100%; height: 550px;"> </div>
      </td>
      </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>