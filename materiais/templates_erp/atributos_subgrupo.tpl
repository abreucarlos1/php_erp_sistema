<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="width:100%;height:660px;">
<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" target="_blank">
	<input type="hidden" class="caixa" name="id_atr_sub" id="id_atr_sub" value="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="xajax_inserir(xajax.getFormValues('frm'));"/></td>
			</tr>
			<tr>
				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			</tr>
		  </table></td>
          <td width="6" rowspan="2" class="<smarty>$classe</smarty>"> </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
		  <table cellspacing="10px" cellpadding="0">
			<tr>
				<td align="left"><label class="labels">GRUPOS</label>
					<br />
                    <select name="codigo_grupo" class="caixa" onchange="if(this.value!='')xajax_getSubGrupos(xajax.getFormValues('frm'));" id="codigo_grupo" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_grupos_values output=$option_grupos_output selected=$selected_grupo</smarty>
					</select>
				</td>
				<td align="left"><label class="labels">SUBGRUPOS</label><br />
			    	<select class="caixa" name="id_sub_grupo" id="id_sub_grupo" onchange="if(this.value!='')xajax_atualizatabela(xajax.getFormValues('frm'));"></select>
				</td>
				<td align="left"><label class="labels">ATRIBUTOS</label><br />
			    	<select class="caixa" name="id_atributo" id="id_atributo"></select>
				</td>
				<td align="left"><label class="labels">ORDEM</label><br />
			    	<input type="text" class="caixa" name="ordem" id="ordem" value="" size="5" />
				</td>
				<td align="left"><label class="labels">COMPÕE FAMÍLIA</label><br />
			    	<input type="radio" class="caixa" name="rdoCompoeCodigo" id="rdoCompoeCodigo1" value="1" /> <label class="labels">Sim</label>
			    	<input type="radio" class="caixa" name="rdoCompoeCodigo" id="rdoCompoeCodigo2" value="0" /> <label class="labels">Não</label>
				</td>
			</tr>
		  </table></td>
        </tr>
      </table>
</form>
<i class="labels" id="legendaAtributos" style="float:left;display:none;">Clique sobre o botão <img src="../imagens/btn_detalhes.png" /> para editar seus valores</i>
<div id="codigos" style="width:100%;margin-top: 15px;"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>