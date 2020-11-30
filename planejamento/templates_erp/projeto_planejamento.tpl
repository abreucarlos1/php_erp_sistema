<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
                        <input name="btnimprimir" id="btnimprimir" type="button" class="class_botao" value="Relat&oacute;rio" disabled="disabled" onclick="if(document.getElementById('id_proposta').value!=0){imprimir()};" />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">           
            <table width="100%" border="0">
              <tr>
                <td width="19%"><label class="labels">Proposta</label><br />
                <div class="labels" style="font-weight:bold" id="nr_proposta">&nbsp;</div></td>
                <td width="81%"><label class="labels">Descri&ccedil;ao</label><br />
                <div class="labels" style="font-weight:bold" id="descri_proposta">&nbsp;</div></td>
                <input type="hidden" id="id_proposta" name="id_proposta" value="" />
                
                <input type="hidden" id="row_id" name="row_id" value="" />
                
                <input type="hidden" id="chk_del" name="chk_del[]" value="">
              </tr>
            </table>
			<table width="100%" border="0">
              <tr>
                <td width="23%"><label for="status" class="labels">Status</label><br />
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <smarty>html_options values=$option_status_values output=$option_status_output selected=1</smarty>
                </select>
                </td>
              </tr>
            </table>                   
                         
            </td>
        </tr>
        <tr>
          <td colspan="3" valign="top">
            <div id="my_tabbar" style="height:600px;">            
              <div id="a10">     
                <div id="div_dados_cliente">&nbsp;</div>
              </div>
            
              <div id="a20">
                <div id="div_control_escopo_geral" style="visibility:hidden; display:none;">
                  <table width="100%">
                    <tr>
                      <td width="10%">
                          <label for="escopogeral" class="labels">Escopo&nbsp;Geral</label><br />           
                            <input name="escopogeral" type="text" class="caixa" id="escopogeral" placeholder="Escopo Geral" size="70">
                            <input name="h_escopogeral" id="h_escopogeral" type="hidden">
                      </td>
                      <td width="11%" valign="bottom"><input name="btn_escopo" type="button" id="btn_escopo" value="Inserir" class="class_botao" onclick="xajax_inc_escopogeral(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                  
                  <div id="div_escopo_geral">&nbsp;</div>
                </div>          
            </div>
              
              <div id="a30">
                <div id="div_control_escopo_detalhado" style="visibility:hidden">       
                  <table width="100%">
                    <tr>
                      <td width="10%"><label class="labels">Escopo&nbsp;Geral</label><br />
                        <div id="escop">&nbsp;</div>
                      </td>
                      <td width="90%"><label class="labels">Disciplina</label><br />
                        <div id="div_disciplina">&nbsp;</div>
                      </td>								
                    </tr>
                  </table>
                  <div id="div_escopo_detalhado" style="width:99%; ">&nbsp;</div>
                  <input name="btn_escopodet" type="button" id="btn_escopodet" value="Concluir" disabled="disabled" onclick="xajax_inc_escopodetalhado(xajax.getFormValues('frm',true));">
               </div>
            </div>
              
              <div id="a40">
                <div id="div_control_resumo" style="visibility:hidden"> 
                  <div id="div_resumo" style="width:99%">&nbsp;</div>
                  <div id="barra_btn_quant">&nbsp;</div>
                </div>
            </div> 
          </div>               
          </td>
        </tr>
</table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>