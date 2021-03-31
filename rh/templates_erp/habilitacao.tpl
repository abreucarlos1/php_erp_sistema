<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_habilitacao" id="frm_habilitacao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_habilitacao'));" value="Inserir" />
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnrelatorio" type="button" class="class_botao" id="btnrelatorio" onclick="window.location='./relatorios/relatorio_cnh_excel.php'" value="Relatório" />
					</td>
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
					<td width="8%"><label for="funcionario" class="labels">Funcionário</label><br />
						<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
						</select>
						<input type="hidden" name="id_habilitacao" id="id_habilitacao" value="" /></td>
					<td width="10%"><label for="numero_habilitacao" class="labels">nº CNH </label><br />
                      <input name="numero_habilitacao" type="text" class="caixa" id="numero_habilitacao" value="" size="20" maxlength="12" placeholder="Nºmero" /></td>
					<td width="12%"><label for="categoria" class="labels">Categoria </label><br />
                      <input name="categoria" type="text" class="caixa" id="categoria" value="" size="10" maxlength="10" placeholder="Categoria" /></td>
					<td width="12%"><label for="data_emissao" class="labels">Data da Emissão </label><br />
						<input name="data_emissao" type="text" class="caixa" id="data_emissao" onkeypress="transformaData(this, event);" value="" onblur="return checaTamanhoData(this,10);" size="10" maxlength="10" placeholder="Data" /></td>
					<td width="15%"><label for="data_vencimento" class="labels">Data do vencimento</label><br />
						<input name="data_vencimento" type="text" class="caixa" id="data_vencimento" value="" onkeypress="transformaData(this, event);" onblur="return checaTamanhoData(this,10);" size="10" maxlength="10" placeholder="Data" /></td>
				</tr>
			</table>
			<table border="0" width="100%">							  
              <tr>
                <td><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
              </tr>
            </table>
		  </td>
        </tr>
      </table>
	  <div id="habilitacao" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>