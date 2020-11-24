<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;	
}
</style>
<div id="frame" style="width:100%; height:700px;" onclick="buscaMenu();">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" onSubmit="return false">
    <table width="100%" border="0">        
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle"><input name="btn_enviar" id="btn_enviar" type="button" class="class_botao" value="Emitir" onClick="if(confirm('Confirma o envio dos arquivos selecionados para o cliente?')){xajax_enviaPacote(xajax.getFormValues('frm'));}" disabled="disabled" /></td>
			  </tr>
				<tr>
				  <td valign="middle"><input name="btn_retorno" id="btn_retorno" type="button" class="class_botao" value="Retorno" onClick="if(confirm('Confirma o retorno do cliente?')){xajax_retornaCliente(xajax.getFormValues('frm')); }" disabled="disabled" /></td>
			  </tr>
				<tr>
				  <td valign="middle"><input name="btn_visualizar" id="btn_visualizar" type="button" class="class_botao" value="Visualizar&nbsp;GRDs" onClick="mostraGrds(xajax.$('id_ged_pacote').value);" disabled="disabled" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
				<tr>
				  <td>
                  <input name="id_numdvm" type="hidden" value="">
                  <input type="hidden" name="id_ged_pacote" id="id_ged_pacote" value="">
                  <input type="hidden" name="num_pacote" id="num_pacote" value="">
                  <input type="hidden" name="OS" id="OS" value="">
                  </td>
			  </tr>
				<tr>
				  <td valign="middle" align="center"><label for="periodos" class="labels">Visualizar:</label><br />
                  <select id="periodos" name="periodos" class="caixa" onchange="xajax_atualizatabela(this.options[this.selectedIndex].value);"><smarty>html_options values=$option_periodos_values output=$option_periodos_output selected=$periodo_atual</smarty></select></td>
			  </tr>
				<tr>
				  <td valign="middle" align="center"><label for="txt_busca" class="labels">Busca:</label><br />
                  <input type="text" name="txt_busca" class="caixa" id="txt_busca" placeholder="Busca" onKeyPress="if(event.keyCode=='13'){xajax_atualizatabela(this.value,'busca');}" size="17" title="Critérios: Nº Pacote, Nº OS, Nome do arquivo, Número DVM, Número Cliente, Solicitante ou Data"></td>
			  </tr>
		  </table></td>
          <td colspan="2" valign="top" class="espacamento">
            <table width="100%" border="0">
              <tr>
                <td width="20%"><label class="labels">Pacote:</label></td>
                <td width="80%"><div id="div_pacote" class="labels">&nbsp;</div></td>
              </tr>
              <tr>
                <td><label class="labels">Coordenador&nbsp;DVM:</label></td>
                <td><div class="labels" id="div_coordenador">&nbsp;</div></td>
              </tr>
              <tr>
                <td><label class="labels">Cliente:</label></td>
                <td><div class="labels" id="div_cliente">&nbsp;</div></td>
              </tr>
              <tr>
                <td><label class="labels">Coordenador&nbsp;Cliente:</label></td>
                <td><div class="labels" id="div_coordenador_cliente">&nbsp;</div></td>
              </tr>
              <tr>
                <td><label class="labels">OS:</label></td>
                <td><div class="labels" id="div_os">&nbsp;</div></td>
              </tr>
              <tr>
                <td><label class="labels">Emitido&nbsp;em:</label></td>
                <td><div class="labels" id="informacao_emissao">&nbsp;</div></td>
              </tr>
              <tr>
                <td colspan="2"><div id="div_preview" style="display:none;"><label class="labels">Pré-visualizar:</label><img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>bt_visualizar.png" alt="Pré-visualizar" style="cursor:pointer;" onClick="window.open('relatorios/rel_ged_grd.php?id_ged_pacote='+xajax.$('id_ged_pacote').value);"></div></td>
              </tr>
              <tr>
                <td valign="top" colspan="2" style="border-width:1px; border-style:solid; border-color:#EDEDED; height:200px;">
                <div id="div_conteudo_pacotes" style="height:200px; overflow:scroll-y;">&nbsp;</div>
               </td>               
              </tr>
              <tr>
              	<td colspan="2">
                <div id="barra_status" style="width:100%; border-color:#CCCCCC; border-width:1px; border-style:solid; font-family:sans-serif; font-size:10px; padding:2px; margin-top:2px;">&nbsp;</div>
                </td>
              </tr>
            </table></td>
        </tr>
      </table>
      <div id="div_ged_pacotes" style="height:300px;">&nbsp;</div>   
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>