<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: 700px;">
<form name="frm_setores" id="frm_setores" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle" >
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_setores'));" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="11%"><label for="abreviacao" class="labels">Abreviação</label><br />
							<input name="abreviacao" type="text" class="caixa" placeholder="Abreviação" id="abreviacao" size="15" />
							<input type="hidden" name="id_setor" id="id_setor" value="" /></td>
					<td width="37%"><label for="setor" class="labels">Setor</label><br />
							<input name="setor" type="text" class="caixa" id="setor" placeholder="Setor" size="50" /></td>
					<td width="52%"><label for="sigla" class="labels">Sigla</label><br />
						<input name="sigla" type="text" class="caixa" id="sigla" placeholder="Sigla" size="6" maxlength="2" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="11%"><label class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="30" />
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="setores" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>