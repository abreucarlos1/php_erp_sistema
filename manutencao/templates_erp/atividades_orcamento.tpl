<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: auto;">
<form name="frm_orcamento" id="frm_orcamento" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_orcamento'));" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnvoltar" type="button" class="class_botao" id="btnvoltar" onclick="window.close();" value="Voltar" /></td>
				</tr>
			</table>
          </td>
           </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento"><br />
			<table border="0" width="100%">
				<tr>
					<td><label class="labels"><strong>Atividade:</strong>&nbsp;<smarty>$atividade</smarty></label>
					<input type="hidden" name="id_atividade" id="id_atividade" value="<smarty>$id_atividade</smarty>">					</td>
					<input type="hidden" name="id_setor" id="id_setor" value="<smarty>$id_setor</smarty>">	
                </tr>
			</table>
			<div id="porcentagem" style="width:100%;">&nbsp;</div></td>
        </tr>
      </table>
    <div id="orcamento" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>