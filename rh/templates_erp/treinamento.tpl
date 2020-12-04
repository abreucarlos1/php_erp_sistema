<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botoes[1]</smarty>" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botoes[2]</smarty>" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="25%">
						<label for="treinamento" class="labels">Treinamento</label><br />
						<input name="treinamento" type="text" class="caixa" id="treinamento" placeholder="Treinamento" size="50" />
						<input type="hidden" name="id_treinamento" id="id_treinamento" value="" />
					</td>
					<td width="35%">
						<label for="avaliar_eficacia" class="labels">Avaliar Eficácia</label><br />
						<label class="labels">Sim</label> <input name="avaliar_eficacia" checked="checked" type="radio" class="caixa" id="avaliar_eficacia" value='1' />
						<label class="labels">Não</label> <input name="avaliar_eficacia" type="radio" class="caixa" id="avaliar_eficacia" value='0' />
					</td>
					<td width="50%">
						<label for="vigencia" class="labels">Vig&ecirc;ncia</label><br />
						<input name="vigencia" type="text" class="caixa" placeholder="Meses" id="vigencia" size="5" />
					</td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td><label for="busca" class="labels">Busca</label><br />
						<input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onkeyup="iniciaBusca.verifica(this);" size="50" /></td>
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="treinamentos" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>