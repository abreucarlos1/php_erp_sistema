<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:auto">
<form name="frm" id="frm" action="<smarty>$_SERVER['PHP_SELF']</smarty>" method="POST">
	<table width="100%" border="0">
	<tr>
		<td width="116" rowspan="2" valign="top" class="espacamento">
			<table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'));" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="window.close();" /></td>
				</tr>
			</table>
        </td>
	</tr>
	<tr>
		<td>
          <table width="100%" border="0">
            <tr>
              <td width="44%"><label for="funcionario" class="labels">Funcionário / Período </label><br />
              	<input name="funcionario" type="text" class="caixa" readonly="1" value="<smarty>$cont['funcionario']</smarty>" size="60">
              </td>
              <td width="56%"><label for="periodo" class="labels">Período</label><br />
              	<input name="periodo" type="text" readonly="1" class="caixa" id="periodo" size="25" value="<smarty>mysql_php($cont['data_ini'])</smarty> á <smarty>mysql_php($cont['data_fin'])</smarty>">
              </td>
            </tr>
          </table>
          <table width="100%" border="0">
            <tr>
              <td width="20%"><label for="empresa" class="labels">Empresa</label><br />
              	<input name="empresa" type="text" class="caixa" id="empresa" value="<smarty>$cont['empresa_func']</smarty>" size="60" readonly="1">
              </td>
            </tr>
          </table>
          <table width="100%" border="0">
          <tr>
            <td width="9%"><label for="nfsfunc_num" class="labels">Nº da nota</label><br />
            	<input name="nfsfunc_num" type="text" class="caixa" id="nfsfunc_num" size="10" placeholder="Nº Nota" onkeypress="num_only()">
            </td>
            <td width="8%"><label for="nfsfunc_data" class="labels">Data</label><br />
            	<input name="nfsfunc_data" type="text" class="caixa" id="nfsfunc_data" size="10"  onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="<smarty>$data</smarty>" onblur="return checaTamanhoData(this,10);" />
            </td>
            <td width="83%"><label for="nfsfunc_valor" class="labels">Valor (R$)</label><br />
            	<input name="nfsfunc_valor" type="text" class="caixa" placeholder="Valor" id="nfsfunc_valor" size="10" maxlength="9" onclick="this.value = '';" onkeydown="FormataValor(document.forms[0].nfsfunc_valor, 9, event);">
            
            	<input name="id_fechamento" type="hidden" id="id_fechamento" value="<smarty>$id_fechamento</smarty>">
              <input name="id_nfsfunc" type="hidden" id="id_nfsfunc" value="">
            </td>
          </tr>
          </table>
          <table width="100%" border="0">
          <tr>
            <td width="24%"><label for="nfsfunc_ajudacusto" class="labels">Tipo de nota:</label><br />
     			<select name="nfsfunc_ajudacusto" id="nfsfunc_ajudacusto" class="caixa" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
                  <option value="0">NOTA DE FECHAMENTO</option>
                  <option value="1">NOTA DE AJUDA DE CUSTO</option>
                  <option value="3">NOTA COMPLEMENTAR</option>
                </select>
            </td>
            <td width="76%"><label for="nfsfunc_descricao" class="labels">Descrição</label><br />
            	<input name="nfsfunc_descricao" type="text" class="caixa" placeholder="Descrição" id="nfsfunc_descricao" size="40" maxlength="255">
            </td>
          </tr>
        </table>
		</td>
	  </tr>
	</table>
    <div id="adiantamento"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>