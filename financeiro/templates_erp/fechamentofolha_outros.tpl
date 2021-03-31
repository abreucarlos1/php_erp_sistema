<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:auto;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'))" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="envia_valores();" /></td>
				</tr>
                  <input type="hidden" name="total_do_valor" id="total_do_valor" value="0" />
                  <input type="hidden" name="id_outros" id="id_outros" value="" />
                  <input name="codfuncionario" type="hidden" id="codfuncionario" value="<smarty>$codfuncionario</smarty>">
                  <input name="tipo" type="hidden" id="tipo" value="<smarty>$tipo</smarty>">
                  <input name="dataini" type="hidden" id="dataini" value="<smarty>$dataini</smarty>">
				  <input name="datafin" type="hidden" id="datafin" value="<smarty>$datafin</smarty>">
			</table></td>
          
        </tr>
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <div id="colaborador"><label class="labels"><strong>Funcionário: </strong><smarty>$colaborador</smarty></label></div>
          <div id="dv_tipo"><label class="labels"><strong>Tipo:</strong> <smarty>$tipo</smarty></label></div>
          <div id="dv_periodo"><label class="labels"><strong>Período:</strong> <smarty>$dataini</smarty> a <smarty>$datafin</smarty></label></div>
          <table border="0" width="100%">
				<tr>
				  <td width="19%"><label for="valor" class="labels">Valor</label><br />				
                    <input name="valor" type="text" class="caixa" id="valor" placeholder="Valor" size="10" onkeydown="FormataValor(this, 9, event)" /></td>
				  <td width="19%"><label for="descricao" class="labels">Descrição</label><br />
                  	<input name="descricao" type="text" class="caixa" placeholder="Descrição" id="descricao" size="50" /></td>
				</tr>
		  </table>
          </td>
        </tr>
      </table>
	  <div id="div_outros"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>