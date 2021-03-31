<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_tipoaumento" id="frm_treinamento" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
	  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_tipoaumento'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					<input type="hidden" name="id_tipo_aumento" id="id_tipo_aumento" value="" />
                </tr>
			</table></td>
      </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
				  <td width="44%"><label for="tioaumento" class="labels">Tipos de Aumento</label><br />
                  <input name="tipoaumento" type="text" class="caixa" id="tipoaumento" size="50" placeholder="Tipo aumento" /></td>
		    </tr>
			</table>
			<table border="0" width="100%">
				<tr>
					<td width="44%"><label for="busca" class="labels">Busca</label><br />							
					<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" placeholder="Busca" size="50" />
					  </td>
				</tr>
			</table></td>
        </tr>
      </table>
  <div id="mostratipoaumento" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>