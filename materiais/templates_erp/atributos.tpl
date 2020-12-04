<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="width:100%;height:660px;">
	<form name="frm" id="frm" method="POST" style="margin:0px; padding:0px;" target="_blank">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">        
	        <tr>
	          <td rowspan="2" valign="top" class="espacamento">
			  <table cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="xajax_inserir(xajax.getFormValues('frm'));"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			  </table></td>
	          <td rowspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top">
			  <table cellspacing="10px" cellpadding="0">
				<tr>
					<td class="td_sp">
						<label class="labels">Nome do Atributo</label>
						<input type="text" class="caixa" size="80" id="nomeAtributo" name="nomeAtributo" />
					</td>
					<td class="td_sp">
						<label class="labels">Desc. Resumida</label>
						<input type="text" class="caixa" size="20" id="descResumidaAtributo" name="descResumidaAtributo" />
					</td>
					<td class="td_sp">
						<label class="labels">Compõe o Código</label><br />
						<label class="labels">Não</label> <input checked="checked" type="radio" class="caixa" id="rdoCompoeCodigo" name="rdoCompoeCodigo" value="0" />
						<label class="labels">Sim</label> <input type="radio" class="caixa" id="rdoCompoeCodigo" name="rdoCompoeCodigo" value="1" />
					</td>
				</tr>
			  </table></td>
	        </tr>
	      </table>
	      <input type="hidden" id="idAtributo" name="idAtributo" />
	</form>
	<div id="codigos" style=""></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>