<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_unidades" id="frm_unidades" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_unidades'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
                <input type="hidden" name="id_unidade" id="id_unidade" value="">
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table border="0" width="100%">
				<tr>
					<td width="12%"><label for="abreviacao" class="labels">Abreviação</label><br />
							<input name="abreviacao" type="text" class="caixa" placeholder="Abreviacao" id="abreviacao" size="15" /></td>
					<td width="88%"><label for="unidade" class="labels">Unidade</label><br />
							<input name="unidade" type="text" class="caixa" placeholder="Unidade" id="unidade" size="50" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="11%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onkeyup="iniciaBusca.verifica(this);" size="30" />
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="unidades" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>