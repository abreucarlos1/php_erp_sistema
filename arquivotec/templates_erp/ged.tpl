<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:770px;" onclick="buscaMenu();">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" enctype="multipart/form-data">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btn_adicionar" id="btn_adicionar" type="button" class="class_botao" value="Adicionar" onClick="popupUpload_grid(0);" disabled="disabled" />
                        </td>
					</tr>
        			<tr>
        			  <td valign="middle">
                      <input name="btn_lat_buscar" id="btn_lat_buscar" type="button" class="class_botao" value="Buscar" onclick="dv_info('0');xajax_preencheArquivos(xajax.getFormValues('frm'));" disabled="disabled"/>
                      </td>
      			  </tr>
        			<tr>
        				<td valign="middle">
                        <input name="btn_enviar" id="btn_enviar" type="button" class="class_botao" value="Solic. Emissão" onClick="popupEnvia(document.getElementById('id_os').value);" disabled="disabled"  />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btn_checkin_sol" id="btn_checkin_sol" type="button" class="class_botao" value="Check-In" onclick="if(confirm('ATENÇÃO: Isso irá bloquear os arquivos. Confirma o check-in?')){xajax_checkin(document.getElementById('id_os').value,1,0);}" disabled="disabled" />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btn_checkout_sol" id="btn_checkout_sol" type="button" class="class_botao" value="Check-Out" onclick="popupUpload_grid(2);" disabled="disabled" />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btn_limpar" id="btn_limpar" type="button" class="class_botao" value="Limpar seleção" onclick="xajax_limparSelecaoAtual(document.getElementById('id_os').value,0);xajax_seta_checkin_checkout(document.getElementById('id_os').value);" />
                    	</td>
                   	</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btn_relatorios" id="btn_relatorios" type="button" class="class_botao" value="Relatórios" onClick="popupRel();" disabled="disabled" />
                    	</td>
                   	</tr>                    
                    
        			<tr>
        				<td valign="middle">
                        <input name="btn_sol_desbloqueio" id="btn_sol_desbloqueio" type="button" class="class_botao" value="Sol.&nbsp;Desbloqueio" onclick="popupSolDesBloq(document.getElementById('id_os').value);" disabled="disabled" />
                    	</td>
                   	</tr>
                    
        			<tr>
        				<td valign="middle">
                        <input name="btn_desbloqueio" id="btn_desbloqueio" type="button" class="class_botao" value="Desbloqueio" onclick="xajax_desbloq_massa(xajax.getFormValues('frm'))" disabled="disabled" />
                    	</td>
                   	</tr>
                    
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
                    	</td>
                   	</tr>
                    <tr>
                    <td>
	                    <div id="barra_busca" style="width:100%;">
	                    	<label class="labels"><a href="javascript:void(0)" onclick="popupBuscaAvancada(document.getElementById('id_os').value,document.getElementById('disciplina').value);">Busca</a></label>&nbsp;<span class="caixa" style="border-left:none; width:16px; margin:0px; background-image:url(../imagens/find.png); background-position:right; background-repeat:no-repeat; cursor:pointer;" onclick="popupBuscaAvancada(document.getElementById('id_os').value,document.getElementById('disciplina').value);" title="Busca Avançada">&nbsp;</span><br />
	                    		<input type="text" name="busca" id="busca" value="Busca" class="caixa" size="15" placeholder="Busca" onclick="if(this.value=='Busca'){this.value='';}" onKeyPress="if(event.keyCode==13){return false;}" onKeyDown="if(event.keyCode==13){buscaMenu(this.value,this.id);}">
	                    		                    	
	                    </div>
						<input type="hidden" id="id_ged_arquivo" name="id_ged_arquivo" value="" />
						<input type="hidden" id="nome_arquivo" name="nome_arquivo" value="" />
						<input type="hidden" id="ordem_lista_documentos" name="ordem_lista_documentos" value="numdvm" />
						<input type="hidden" id="chk_excel" name="chk_excel" value="" >
						<input type="hidden" id="chk_emitidos" name="chk_emitidos" value="0" >
	                 </td>
                    </tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
                <table border="0" width="100%">
                  <tr>
                        <td width="10%" class="td_sp"><label class="labels">OS</label>
                        <select name="id_os" id="id_os" class="caixa" onchange="estado_inicial(this.value);xajax_seta_checkin_checkout(this.value);" onblur="estado_inicial(this.value);xajax_seta_checkin_checkout(this.value);">
                          <option value="">SELECIONE</option>
                        <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                      </select>
                    </td>
                    <td width="87%"><label for="disciplina" class="labels">Disciplina</label><br />
                        <select name="disciplina" class="caixa"  id="disciplina" onchange="disciplinas_inicial(this.value);" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                        <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                      </select>
                    </td>                    
                  </tr>
                </table>
				<table border="0" width="100%">
                  <tr>
                        <td width="10%" class="td_sp"><label for="CodAtividade" class="labels">Tarefa/Atividade</label><br />
                        <select name="id_atividade" class="caixa"  id="id_atividade" onchange="document.getElementById('btn_adicionar').disabled=true; if(this.options[this.options.selectedIndex].value) { document.getElementById('btn_adicionar').disabled=false; }" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                    </select>
                    </td>
                  </tr>
                </table>
                <table border="0" width="100%">
                	<tr>
                  	<td width="10%"><label for="servico" class="labels">Serviço</label><br /> 
						<select name="servico" class="caixa" id="servico" onkeypress="return keySort(this);">
                        	<smarty>html_options values=$option_servico_values output=$option_servico_output</smarty>
						</select>
					</td>
                  </tr>
                </table>
				<table border="0" width="100%">
                  <tr>
                        <td width="10%"><label class="labels">Busca</label><br />
						<input name="txt_busca_inicial" size="100" type="text" placeholder="Busca" class="caixa" id="txt_busca_inicial" onkeyup="if(event.keyCode==13){xajax_preencheArquivos(xajax.getFormValues('frm'));}" value="">
                        
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2"><div id="div_res">&nbsp;</div></td>
                  </tr>
                </table>
            </td>
        </tr>
      </table>
        <div id="div_painel" style="position:relative;">         
                
            <div id="tree1" setOnClickHandler="tonclick" setImagePath="../includes/dhtmlx_403/codebase/imgs/dhxtree_skyblue/" xclass="dhtmlxTree" style="width:28%; float:left; border-style:solid; border-color:#999999; border-width:1px; height:400px; overflow:auto; text-align:left;">&nbsp;</div>
          
            <div id="div_separador" style="position:relative; width:1px; float:left; height:400px; border-width:1px; border-style:outset; background-color:#CCCCCC; ">&nbsp;</div>
        
            <div id="div_arquivos" style="width:70%; float:left;border-style:solid; border-color:#999999; border-width:1px; height:400px; padding:0px; overflow:auto;">&nbsp;</div>
        
            <div id="div_info" style="width:70%; visibility:hidden; float:left;border-style:solid; border-color:#ff0000; border-width:1px; height:1px; padding:0px; overflow:auto;">&nbsp;</div>
        
        </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>