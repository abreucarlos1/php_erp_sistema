<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;"> 
<form name="frm" id="frm" action="<smarty>$redirect</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="if (document.getElementById('codfuncionario').value == 0){ alert('Selecione um funcionário!'); return false;} else document.getElementById('frm').submit();"  />
					</td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
                <input type="hidden" name="externo" id="externo" value="<smarty>$externo</smarty>" />
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td colspan="2"><label class="labels">Equipe</label>                  
                  <smarty>$check_equipe</smarty>
                  </td>
		    </tr>
				<tr>
				  <td>
                  <smarty>$combo_atuacao</smarty>
                </td>
                </tr>
				<tr>
					<td width="8%"><label for="codfuncionario" class="labels">Funcionário</label><br />
							<select name="codfuncionario" class="caixa" id="codfuncionario" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_values output=$option_output</smarty>
							</select>
                        </td>
				</tr>
			</table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>