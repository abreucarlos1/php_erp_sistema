<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_escolaridade" id="frm_escolaridade" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_escolaridade'));" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table>
		  </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="25%"><label for="escolaridade" class="labels">Escolaridade</label><br />
							<input name="escolaridade" type="text" class="caixa" id="escolaridade" size="50" placeholder="Escolaridade" />
							<input type="hidden" name="id_escolaridade" id="id_escolaridade" value="" />
							</td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td><label for="busca" class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="50" placeholder="Busca" /></td>
				</tr>
			</table></td>
        </tr>
      </table>
	  <div id="escolaridades" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>