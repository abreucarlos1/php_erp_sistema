<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="height: 660px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<input name="id_fornecedor" type="hidden" class="caixa" id="id_fornecedor" size="10" maxlength="3" onkeypress="num_only();" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="td_sp">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="<smarty>$botao[1]</smarty>" onclick="xajax_insere(xajax.getFormValues('frm'));" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnimportar" id="btnimportar" type="button" class="class_botao" value="<smarty>$botao[5]</smarty>" onclick="abrirArquivoImportacao();" /></td>
				</tr>
				<tr>
				  <td valign="middle"><input name="id_grupo" type="hidden" id="id_grupo" value=""></td>
			  </tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
				<table border="0">
                  <tr>
                    <td class="td_sp" colspan="4"><label class="labels">Razão Social</label>
						<input name="razao_social" type="text" class="caixa" id="razao_social" maxlength="255" size="69" />
					</td>
					<td colspan="2" class="td_sp"><label class="labels">Nome Fantasia</label><br />
						<input name="nome_fantasia" type="text" class="caixa" id="nome_fantasia" maxlength="255" size="65" />
					</td>
                  </tr>
                  
                  <tr>
                    <td class="td_sp" colspan="3"><label class="labels">Logradouro</label>
						<input name="logradouro" type="text" class="caixa" id="logradouro" maxlength="255" size="59">
					</td>
					<td class="td_sp"><label class="labels">Nºm.</label><br />
						<input name="numero" type="text" class="caixa" id="numero" maxlength="5" size="5">
					</td>
					<td class="td_sp"><label class="labels">Complemento</label><br />
						<input name="complemento" type="text" class="caixa" id="complemento" maxlength="255" size="30">
					</td>
					<td class="td_sp"><label class="labels">Bairro</label><br />
						<input name="bairro" type="text" class="caixa" id="bairro" maxlength="255" size="30">
					</td>
                  </tr>
                  
                  <tr>
                    <td colspan="3" class="td_sp"><label class="labels">UF</label>
						<select name="uf" class="caixa" id="uf" onkeypress="return keySort(this);" style="width: 100%;" onchange="xajax_getMunicipiosUF(this.value);">
							<smarty>html_options values=$option_uf_values output=$option_uf_output</smarty>
						</select>
					</td>
					<td colspan="3" class="td_sp"><label class="labels">Municipio</label>
						<select name="municipio" class="caixa" id="municipio" onkeypress="return keySort(this);" style="width: 100%;">
							<option value=''>Selecione...</option>
						</select>
					</td>
                  </tr>
                </table>                          
          </td>
        </tr>
        
        <tr>
          <td class="fundo_azul"> </td>
          <td colspan="2" class="<smarty>$classe</smarty>"> </td>
        </tr>
      </table>
    <i class="labels" id="legendaAtributos" style="float:left;">Clique sobre o botão <img src="../imagens/btn_detalhes.png" /> para editar seus valores</i>
	<div id="fornecedores" style="width:100%;"></div>	
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>