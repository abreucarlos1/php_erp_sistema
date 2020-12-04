<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_integracao" id="frm_integracao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_integracao'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnrelatorio" id="btnrelatorio" type="button" class="class_botao" value="Relat�rio" onclick='window.open("./relatorios/rel_integracao_excel.php", "_blank");' /></td>
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
					<td width="13%"><label for="funcionario" class="labels">Funcionário</label><br />
						<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
						</select>
						<input type="hidden" name="id_integracao" id="id_integracao" value="" /></td>
					<td width="13%"><label for="cracha" class="labels">Cracha </label><br />
                    <input name="cracha" type="text" class="caixa" id="cracha" value="" size="12" maxlength="10" placeholder="Cracha" /></td>
					<td width="18%"><label for="local_trabalho" class="labels">Local&nbsp;de&nbsp;trabalho</label><br />
                      <select name="local_trabalho" class="caixa" id="local_trabalho" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_local_values output=$option_local_output</smarty>
                    </select></td>
				</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="21%" valign="top"><label for="data_integracao" class="labels">Data&nbsp;da&nbsp;Integração </label><br />
              <input name="data_integracao" type="text" class="caixa" id="data_integracao" onkeypress="transformaData(this, event);" value="<smarty>$data_integracao</smarty>" onblur="xajax_calcula_vencimento(this.value,vigencia.value);return checaTamanhoData(this,10);" size="10" maxlength="10" /></td>
		      <td width="18%" valign="top"><label for="vigencia" class="labels">Vig&ecirc;ncia&nbsp;(Meses)</label><br />
              <input name="vigencia" type="text" class="caixa" id="vigencia" value="12" onblur="xajax_calcula_vencimento(data_integracao.value,this.value);" size="8" maxlength="2" /></td>
		      <td width="27%" valign="top"><label for="data_vencimento" class="labels">Vencimento&nbsp;da&nbsp;Integração </label><br />
              <input name="data_vencimento" type="text" class="caixa" id="data_vencimento" value="" size="10" maxlength="10" readonly="readonly" /></td>
		      <td width="34%" valign="top"><label for="observacoes" class="labels">Follow Up</label><br />
		      	<textarea cols="25" name="observacoes" id="observacoes"></textarea>
		      </td>
	        </tr>
		    </table>
          	<table border="0" width="100%">
			  <tr>
				<td><label class="labels">Busca</label><br />
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" placeholder="Busca"></td>
			  </tr>
			</table>
		  </td>
        </tr>
      </table>
	  <div id="integracao" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>