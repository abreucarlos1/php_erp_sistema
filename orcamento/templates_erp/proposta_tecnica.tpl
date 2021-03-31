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
            <table width="100%">
            <tr>
            <td valign="middle">
              <input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'));">
            </td>
          </tr>
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
                    	</td>
                   	</tr>
       			</table>
			<table width="100%" border="0">
              <tr>
                <td width="23%"><label for="status" class="labels">Status</label><br />
                  <select name="status" class="caixa" id="status" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'));">
                    <option value="">TODAS</option>
                    <smarty>html_options values=$option_status_values output=$option_status_output selected=1</smarty>
                </select>
                </td>
             </tr>
            </table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">           
            <table width="100%" border="0">
              <tr>
                <td width="16%"><label class="labels">Proposta</label><br />
                <!-- <div class="labels" style="font-weight:bold" id="nr_proposta"> </div>-->
                <input name="nr_proposta" type="text" class="caixa" id="nr_proposta" size="14">
                </td>
                  
                <td width="16%"><label class="labels">Descrição</label><br />
                <!-- <div class="labels" style="font-weight:bold" id="descri_proposta"> </div> -->
                  <input name="descri_proposta" type="text" class="caixa" id="descri_proposta" size="100">
                </td>
                <td width="78%"><label for="exec_1" class="labels">Executante 1</label><br /> 
                <select name="exec_1" class="caixa" id="exec_1" onkeypress="return keySort(this);">
                <smarty>html_options values=$option_exec1_values output=$option_exec1_output</smarty>
                </select></td>
                <input type="hidden" id="id_proposta" name="id_proposta" value="" />
                <input type="hidden" id="chk_del" name="chk_del[]" value="">
              </tr>
            </table>
			<table border="0" width="100%">
				<tr>
					<td width="37%"><label for="busca" class="labels">Buscar</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" size="50" />  
                    <input name="btnbuscar" id="btnbuscar" type="button" class="class_botao" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm'),true);" />
                    </td>
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
                <div id="div_control_autoriza" style="visibility:none">       
                  <table width="100%">
                    <tr>
                      <td width="19%"><label for="disciplina_aut" class="labels">Disciplina</label><br />
                        <select name="disciplina_aut" class="caixa" id="disciplina_aut" onkeypress="return keySort(this);" onchange="xajax_mostra_autorizacao(xajax.getFormValues('frm'));">
                          <option value="">ESCOLHA A DISCIPLINA</option>
                          <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
                      </select></td>								
                      <td width="81%"><input name="btn_email" type="button" id="btn_email" class="class_botao" value="Enviar e-mail" disabled="disabled" onclick="if(confirm('Deseja enviar e-mail aos colaboradores autorizados?')){xajax_email(xajax.getFormValues('frm'))};"></td>
                    </tr>
                  </table>
                  <table width="100%" border="1" >
                    <tr valign="top">
                      <td width="50%" valign="top"><div id="div_aut_colab" style="width:99%;"> </div></td>
                      <td width="50%" valign="top"><div id="div_autorizados" style="width:99%;"> </div></td>
                    </tr>
                  </table>
                </div>
            </div>
            
			  <div id="a17">
                <div id="div_control_subcontrato" style="visibility:hidden; display:none;">
                  <table width="100%">
                    <tr>
                      <td width="27%"><label for="subcontratado" class="labels">Subcontratado</label><br />           
                            <input name="subcontratado" type="text" class="caixa" id="subcontratado" placeholder="Subcontratado" size="40">
                            <input name="h_subcontratado" id="h_subcontratado" type="hidden">
                      </td>
                      <td width="33%"><label for="descritivo" class="labels">Descritivo</label><br />           
                            <input name="descritivo" type="text" class="caixa" id="descritivo" placeholder="Descritivo" size="50">
                      </td>
                      <td width="10%"><label for="valor_subcontrato" class="labels">Valor</label><br />           
                            <input name="valor_subcontrato" type="text" class="caixa" id="valor_subcontrato" size="15" placeholder="Valor" onkeypress = "num_only();">
                      </td>
                      <td width="30%"><input name="btn_subcontratado" class="class_botao" type="button" id="btn_subcontratado" value="Inserir" onclick="xajax_inc_subcontratado(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                 
                  <div id="div_subcontratados"> </div>
                </div>          
            </div>              
              <div id="a20">
                <div id="div_control_escopo_geral" style="visibility:hidden; display:none;">
                  <table width="100%">
                    <tr>
                      <td width="20%"><label for="escopogeral" class="labels">Escopo Geral</label><br />           
                            <input name="escopogeral" type="text" class="caixa" id="escopogeral" placeholder="Escopo Geral" size="50">
                            <input name="h_escopogeral" id="h_escopogeral" type="hidden">
                      </td>
                    <td width="5%"><label for="id_estado" class="labels">Estado</label><br />
                       <select name="id_estado" class="caixa" id="id_estado" onkeypress="return keySort(this);xajax_cidades(xajax.getFormValues('frm'));" onchange="xajax_cidades(xajax.getFormValues('frm'));" >
                        <smarty>html_options values=$option_estado_values output=$option_estado_output selected=$selecionado1</smarty>
                      </select>
                    </td>
                    <td width="95%"><label for="id_cidade" class="labels">Local obra</label><br />
                      <select name="id_cidade" class="caixa" id="id_cidade" onkeypress="return keySort(this);" >
                      </select>
                    </td>                              
                    </tr>
                  </table>
                  <table width="100%">
                    <tr>
                      <td width="80%"><input name="btn_escopo" type="button" id="btn_escopo" class="class_botao" value="Inserir" onclick="xajax_inc_escopogeral(xajax.getFormValues('frm'));"></td>            
                    </tr>
                  </table>                   
                  <div id="div_escopo_geral"> </div>
                </div>          
            </div>
              
              <div id="a30">
                <div id="div_control_escopo_detalhado" style="visibility:hidden">       
                  <table width="100%">
                    <tr>
                      <td width="9%"><label class="labels">Escopo Geral</label><br />
                        <div id="escop"> </div>
                      </td>
                      <td width="91%"><label class="labels">Disciplina</label><br />
                        <div id="div_disciplina"> </div>
                      </td>								
                    </tr>
                  </table>
                  <div id="div_escopo_detalhado" style="width:99%; margin-bottom:10px;"> </div>
                  <input name="btn_escopodet" type="button" id="btn_escopodet" value="Concluir" disabled="disabled" class="class_botao" onclick="xajax_inc_escopodetalhado(xajax.getFormValues('frm',true));">
                  <input name="btn_cancela" type="button" id="btn_cancela" value="Cancelar" disabled="disabled" class="class_botao" onclick="xajax_status_usuario(xajax.getFormValues('frm',true),1);">
                </div>
            </div>
            
              <div id="a35">
                <div id="div_control_mobilizacao" style="visibility:hidden;">       
                  <table width="100%">
                    <tr>
                      <td width="12%"><label class="labels">Escopo Geral</label><br />
                        <div id="mobilizacao"> </div>
                      </td>
                      <td width="15%"><label class="labels">Tipo reembolso</label><br />
                      <select name="id_tipo_reembolso" class="caixa" id="id_tipo_reembolso" onkeypress="return keySort(this);" onchange="if(this.value==2 || this.value==3){document.getElementById('taxa_adm').style.display='inline';if(this.value==2){document.getElementById('taxa_administrativa').value=0}else{document.getElementById('taxa_administrativa').value=10}}else{document.getElementById('taxa_administrativa').value=0;document.getElementById('taxa_adm').style.display='none'}" >
                      <option value="1">NOTA FISCAL</option>
                      <option value="2">NOTA DÉBITO</option>
                      <option value="3">NOTA DÉBITO SEM COMP.</option>
                      </select>
                      </td>
                      <td width="73%"><div id="taxa_adm" style="display:none;"><label class="labels">Taxa administrativa</label><br />
                        <input name="taxa_administrativa" type="text" class="caixa" id="taxa_administrativa" size="5" placeholder="Taxa administrativa" value="10" onkeypress = "num_only();">
                        </div>
                      </td>
                    </tr>
                  </table>
                  <div id="div_mobilizacao" style="width:99%; margin-bottom:10px;"> </div>
                  <input name="btn_mobilizacao" type="button" id="btn_mobilizacao" value="Concluir" disabled="disabled" class="class_botao" onclick="xajax_inc_mobilizacao(xajax.getFormValues('frm',true));">
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
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>