<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_os" id="frm_os" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
                
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm_os'));" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
					<td><label for="status" class="labels">Visualizar&nbsp;Status</label><br />
                  <select name="status" class="caixa" id="status" onchange="xajax_atualizatabela(xajax.getFormValues('frm_os'));" onkeypress="return keySort(this);">
					<smarty>html_options values=$option_status_values output=$option_status_output selected=$option_status_selected </smarty>
                </select></td>
				</tr>
			</table>
          </td>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
          <tr>
                <td width="8%"><label class="labels">Projeto</label><br />
                  <select name="os" class="caixa" id="os" onchange="xajax_disciplinas(xajax.getFormValues('frm_os'));">
                    <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                  </select></td>
              </tr>
           </table>
          <table width="100%" border="0">
          <tr>
                <td width="8%"><label for="motivo" class="labels">Motivo</label><br />
                  <select name="motivo" class="caixa" id="motivo" onchange="xajax_disciplinas(xajax.getFormValues('frm_os'));ativa_campos(this.value);" onkeypress="return keySort(this);">
                    <smarty>html_options values=$option_motivo_values output=$option_motivo_output</smarty>
                </select></td>
              </tr>
           </table>
			<table width="100%" border="0">
              <tr>
                <td width="22%" valign="top"><label for="disciplina" class="labels">Disciplina</label><br />
                  <select name="disciplina" class="caixa" id="disciplina" onkeypress="return keySort(this);" onchange="xajax_atividades(xajax.getFormValues('frm_os'));" >
                    <option value="">ESCOLHA A DISCIPLINA</option>
                </select></td>
              </tr>
            </table>
          	<table width="100%" border="0">
              <tr>
                <td width="15%"><label for="atividade" class="labels">Atividade</label><br />
                  <select name="atividade" class="caixa" id="atividade" onkeypress="return keySort(this);">
                    <option value="">ESCOLHA A ATIVIDADE</option>
                </select></td>
              </tr>
            </table>
          	<table width="100%" border="0">
          	  <tr>
          	    <td width="9%">
                  <label class="labels">Horas</label><br />
                <input name="qtdhoras" type="text" class="caixa" id="qtdhoras" size="10" maxlength="10" value="0" onkeypress="num_only();" />
                  </td>
				<td width="7%"><label id="label_formato" for="formato" class="labels" style="display:none;">Formato</label><br />
          	      <select name="formato" class="caixa" style="display:none;" id="formato" onkeypress="return keySort(this);">
                    <smarty>html_options values=$option_formato_values output=$option_formato_output</smarty>
       	          </select>                 
                 </td>
          	    <td width="84%"><label id="label_qtd_formato" for="qtd_formato" class="labels" style="display:none;">Quantidade</label><br />
                <input name="qtd_formato" type="text" class="caixa" style="display:none;" id="qtd_formato" size="5" maxlength="5" value="0" onkeypress="num_only();" />                
                </td>
       	      </tr>
       	    </table>
          	<table width="100%" border="0">
          	  <tr>
          	    <td width="15%"><label for="observacao" class="labels">Observação</label><br />
                <input name="observacao" type="text" class="caixa" id="observacao" size="100" maxlength="200" /></td>
       	      </tr>
       	    </table>
		</td>
        </tr>
      </table>
  <div id="habilitados" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>