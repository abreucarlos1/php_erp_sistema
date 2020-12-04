<smarty>include file="templates_erp/header.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm_ged" id="frm_ged" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" enctype="multipart/form-data">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle">
        					<input name="btn_lat_buscar" id="btn_lat_buscar" type="button" class="class_botao" value="Buscar" onclick="dv_info('0');xajax_preencheArquivos(xajax.getFormValues('frm_ged'));" disabled/>
                            </td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
        			<tr style="height:25px;">
                      <td valign="middle">
                      <input type="hidden" id="id_ged_arquivo" name="id_ged_arquivo" value="" />
                      <input type="hidden" id="nome_arquivo" name="nome_arquivo" value="" />
                      <input type="hidden" id="ordem_lista_documentos" name="ordem_lista_documentos" value="numdvm" />
                      <input type="hidden" id="chk_excel" name="chk_excel" value="" >
                      </td>
      			  </tr>
       			</table>
			</td>
       	  <td colspan="2" valign="top" class="td_sp">
            <table border="0" width="100%">
                
              <tr>
                  <td width="12%" class="td_sp"><label class="labels">Projeto</label>
				    <select name="id_os" id="id_os" class="caixa"  onChange="estado_inicial(this.value);"  onkeypress="">
                      <option value="">SELECIONE</option>
					<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                  </select>                  </td>
                  <td width="88%" class="td_sp">&nbsp;</td>
              </tr>
              </table>
            <table border="0" width="100%">
              <tr>
                <td width="12%" class="td_sp"><label class="labels">Disciplina</label>
                  <select name="disciplina" class="caixa"  id="disciplina" onchange="disciplinas_inicial(this.value);" onkeypress="return keySort(this);">
                    <option value="">SELECIONE</option>
                    <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                </select></td>
                <td class="td_sp">&nbsp;</td>
              </tr>
            </table>
				<table width="100%" border="0">
                  <tr>
                    <td width="22%" class="td_sp"><label class="labels">Tipo&nbsp;de&nbsp;documento</label><br /> 

					<select name="CodAtividade" class="caixa"  id="CodAtividade" onChange="document.getElementById('btn_adicionar').disabled=true; if(this.options[this.options.selectedIndex].value) { document.getElementById('btn_adicionar').disabled=false; }  " onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                    </select>                    </td>
                    <td width="78%" class="td_sp">&nbsp;</td>
                  </tr>
                  <!--
                  <tr>
                    <td colspan="2" class="td_sp">&nbsp;</td>
                  </tr>
                  -->         
         		 </table>
        		<table width="100%" border="0">
                <!--
                 <tr>
                  <td class="labels" style="font-size:11px">Para refinar a busca, digite mais informações abaixo e clique novamente no botão "Buscar".</td>
                  <td class="labels" style="font-size:11px">&nbsp;</td>
                </tr>
                
                <tr>
                  <td ><input name="txt_busca_inicial" size="100" type="text" class="caixa" id="txt_busca_inicial" onkeyup="if(event.keyCode==13){xajax_preencheArquivos(xajax.getFormValues('frm_ged'));}" value=""></td>
                  <td >&nbsp;<div id="div_res"></div></td>
                  
                </tr>
                -->
                  </table>            
              </td>
        </tr>
      </table>
	 <!--  <div id="controlehoras" style="scrollbar-face-color : #AAAAAA; scrollbar-highlight-color : #AAAAAA; scrollbar-3dlight-color : #ffffff; scrollbar-shadow-color : #FFFFFF; scrollbar-darkshadow-color : #FFFFFF; scrollbar-track-color : #FFFFFF; scrollbar-arrow-color : #FFFFFF;">&nbsp;</div> -->

    <div id="div_painel" onMouseDown="" onMouseUp="" style="position:relative;">        
        <div id="tree1" setOnClickHandler="tonclick" setImagePath="../includes/dhtmlx_3_6/dhtmlxTree/codebase/imgs/" class="dhtmlxTree" style="width:28%; float:left; border-style:solid; border-color:#999999; border-width:1px; height:450px; overflow:auto; text-align:left;" oncontextmenu="return false">
         
         </div>
         
        <div id="div_separador" style="position:relative; width:1px; float:left; height:450px; border-width:2px; border-style:outset; background-color:#CCCCCC; ">&nbsp;</div>
    
        <div id="div_arquivos" style="width:70%; float:left;border-style:solid; border-color:#999999; border-width:1px; height:450px; padding:0px; overflow:auto; -moz-user-select:none;" oncontextmenu="return false;" onselectstart="return false;" unselectable="on">&nbsp;</div>
    
        <div id="div_info" style="width:70%; visibility:hidden; float:left;border-style:solid; border-color:#ff0000; border-width:1px; height:1px; padding:0px; overflow:auto; -moz-user-select:none;">&nbsp;</div>
    
    </div>
</form>
</div>
<smarty>include file="templates_erp/footer.tpl"</smarty>