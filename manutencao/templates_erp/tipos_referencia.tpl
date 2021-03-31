<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" />					</td>
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
				  <td width="37%"><label for="tipo_referencia" class="labels">Tipo Referência*</label><br />
                    <input name="tipo_referencia" type="text" class="caixa" id="tipo_referencia" placeholder="Tipo referência" size="50" />
                <input type="hidden" name="id_tipo" id="id_tipo" value="" /></td>
					<td width="63%"><label class="labels">Grava estrutura disciplina</label><br />
                      <select name="grv_disc" class="caixa" id="grv_disc" onkeypress="return keySort(this);">
                        <option value="1">SIM</option>
                        <option value="0" selected="selected">NÃO</option>
                    </select></td>
				</tr>
			</table>
          	<table border="0" width="100%">
  				<tr>
                	<td width="39%"><label class="labels">Diretório Base*</label><br />
                    <input name="pasta_base" type="text" class="caixa" id="pasta_base" placeholder="Diretório base" size="50" maxlength="30" />
                    </td>
                </tr>
            </table>
                
          	<table border="0" width="100%">
				<tr>
					<td width="11%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="30" />
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="setores" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>