<smarty>include file="../../templates_erp/header.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:660px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="78" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
		  </td>
        	<td width="797" colspan="2" valign="top" class="td_sp">           
            <table width="93%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="6%" class="td_sp"><label class="labels">Proposta</label>
                <div class="labels" style="font-weight:bold" id="nr_proposta"> </div></td>
                <td width="17%" class="td_sp"><label class="labels">Descrição</label>
                <div class="labels" style="font-weight:bold" id="descri_proposta"> </div></td>
                <input type="hidden" id="id_proposta" name="id_proposta" value="" />
                <input type="hidden" id="chk_del" name="chk_del[]" value="">
              </tr>
            </table>
			<table width="93%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="23%" class="td_sp"><label class="labels">Status</label>
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <option value="">TODAS</option>
                    <smarty>html_options values=$option_status_values output=$option_status_output selected=1</smarty>
                </select>
                </td>
                <td width="77%" class="td_sp"> </td>                
              </tr>
            </table>                   
                         
            </td>
        </tr>
        <tr>
          <td colspan="3" valign="top">
            <div id="my_tabbar" style="height:600px;"> </div>               
              
              <div id="a10">     
                <div id="div_dados_cliente"> </div>
            </div>
              
              <div id="a15">
                <div id="div_control_autoriza" style="visibility:hidden">       
                  <table width="93%">
                    <tr>
                      <td width="22%"><label class="labels">Disciplina</label>
                        <select name="disciplina_aut" class="caixa" id="disciplina_aut" onkeypress="return keySort(this);" onchange="xajax_mostra_autorizacao(xajax.getFormValues('frm'));">
                          <option value="">ESCOLHA A DISCIPLINA</option>
                          <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
                      </select></td>								
                      <td width="78%"><input name="btn_email" type="button" id="btn_email" value="Enviar e-mail" disabled="disabled" onclick="if(confirm('Deseja enviar e-mail aos colaboradores autorizados?')){xajax_email(xajax.getFormValues('frm'))};"></td>
                    </tr>
                  </table>
                  <table width="99%" border="1" >
                    <tr valign="top">
                      <td width="50%" valign="top"><div id="div_aut_colab" style="width:99%;"> </div></td>
                      <td width="50%" valign="top"><div id="div_autorizados" style="width:99%;"> </div></td>
                    </tr>
                  </table>
                </div>
            </div>
            
			  <div id="a17">
                <div id="div_control_subcontrato" style="visibility:hidden; display:none;">
                  <table width="99%">
                    <tr>
                      <td width="36%">
                          <label class="labels">Subcontratado</label>           
                            <input name="subcontratado" type="text" class="caixa" id="subcontratado" size="60">
                            <input name="h_subcontratado" id="h_subcontratado" type="hidden">
                      </td>
                      <td width="35%">
                          <label class="labels">Descritivo</label>           
                            <input name="descritivo" type="text" class="caixa" id="descritivo" size="60">
                      </td>
                      <td width="13%">
                          <label class="labels">Valor</label>           
                            <input name="valor_subcontrato" type="text" class="caixa" id="valor_subcontrato" size="20" onkeypress = "num_only();">
                      </td>
                      <td width="16%" valign="bottom"><input name="btn_subcontratado" type="button" id="btn_subcontratado" value="Inserir" onclick="xajax_inc_subcontratado(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                 
                  <div id="div_subcontratados"> </div>
                </div>          
            </div>              
              <div id="a20">
                <div id="div_control_escopo_geral" style="visibility:hidden; display:none;">
                  <table width="99%">
                    <tr>
                      <td width="10%">
                          <label class="labels">Escopo Geral</label>           
                            <input name="escopogeral" type="text" class="caixa" id="escopogeral" size="100">
                            <input name="h_escopogeral" id="h_escopogeral" type="hidden">
                      </td>
                      <td width="11%" valign="bottom"><input name="btn_escopo" type="button" id="btn_escopo" value="Inserir" onclick="xajax_inc_escopogeral(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                  
                  <div id="div_escopo_geral"> </div>
                </div>          
            </div>
              
              <div id="a30">
                <div id="div_control_escopo_detalhado" style="visibility:hidden">       
                  <table width="99%">
                    <tr>
                      <td width="18%"><label class="labels">Escopo Geral</label>
                        <div id="escop"> </div>
                      </td>
                      <td width="22%"><label class="labels">Disciplina</label>
                        <div id="div_disciplina"> </div>
                      </td>								
                      <td width="60%"> </td>
                    </tr>
                  </table>
                  <div id="div_escopo_detalhado" style="width:99%; "> </div>
                  <input name="btn_escopodet" type="button" id="btn_escopodet" value="Concluir" disabled="disabled" onclick="xajax_inc_escopodetalhado(xajax.getFormValues('frm',true));">
                  <input name="btn_cancela" type="button" id="btn_cancela" value="Cancelar" disabled="disabled" onclick="xajax_status_usuario(xajax.getFormValues('frm',true),1);">
                </div>
            </div>
              
              <div id="a40">
                <div id="div_control_resumo" style="visibility:hidden"> 
                  <div id="div_resumo" style="width:99%"> </div>
                  <div id="barra_btn_quant">
                  </div>
                </div>
            </div>               
          </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>