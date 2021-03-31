<smarty>include file="../../templates_erp/header.tpl"</smarty>
<form name="frm_ged" id="frm_ged" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" enctype="multipart/form-data" style="margin:0px; padding:0px;">
	
    <table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="122" rowspan="2" valign="top" class="fundo_cinza">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="btn_adicionar" id="btn_adicionar" type="button" class="botao_chanfrado" value="Adicionar" onclick="popupUpload_grid(0);" disabled="disabled" /></td>
			  </tr>
				<tr>
			  	<td valign="middle" class="fundo_cinza" ><input name="btn_lat_buscar" id="btn_lat_buscar" type="button" class="botao_chanfrado" value="Buscar" onclick="dv_info('0');xajax_preencheArquivos(xajax.getFormValues('frm_ged'));" disabled/></td>	
              </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="btn_enviar" id="btn_enviar" type="button" class="botao_chanfrado" value="Solic. Emissão" onclick="popupEnvia(document.getElementById('id_os').value);" disabled="disabled"  /></td>
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="btn_checkin_sol" id="btn_checkin_sol" type="button" class="botao_chanfrado" value="Check-In" onclick="if(confirm('ATENÇÃO: Isso irá bloquear os arquivos. Confirma o check in?')){xajax_checkin(document.getElementById('id_os').value,1,0);}" disabled="disabled" /></td>
		    </tr>
				<tr>
		    	<td valign="middle" class="fundo_cinza" ><input name="btn_checkout_sol" id="btn_checkout_sol" type="button" class="botao_chanfrado" value="Check-Out" onclick="popupUpload_grid(2);" disabled="disabled" /></td>
            </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="btn_limpar" id="btn_limpar" type="button" class="botao_chanfrado" value="Limpar seleção" onclick="xajax_limparSelecaoAtual(document.getElementById('id_os').value,0);xajax_seta_checkin_checkout(document.getElementById('id_os').value);" /></td>
		    </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" ><input name="btn_relatorios" id="btn_relatorios" type="button" class="botao_chanfrado" value="Relatórios" onclick="popupRel()" disabled="disabled" /></td>
			  </tr>
				<tr>
					<td valign="middle" class="fundo_cinza"><input name="btnvoltar" id="btnvoltar" type="button" class="botao_chanfrado" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" >
                  <!-- <input type="hidden" id="caminho" name="caminho" value="./documentos" /> -->
                  <input type="hidden" id="id_ged_arquivo" name="id_ged_arquivo" value="" />
                  <input type="hidden" id="nome_arquivo" name="nome_arquivo" value="" />
				  <input type="hidden" id="ordem_lista_documentos" name="ordem_lista_documentos" value="numdvm" />
                  <input type="hidden" id="chk_excel" name="chk_excel" value="" >
                  </td>
                  
			  </tr>
				<tr>
				  <td valign="middle" class="fundo_cinza" >
					<div id="barra_busca" style="width:100%; margin:5px; margin-top:0px; margin-right:0px;"><span class="caixa" style="width:80px; height:16px; vertical-align:top; border-right:none;"><input type="text" name="busca" id="busca" style="width:100%; height:16px; font-size:9px; color:#666666; border:none; margin:0px; position:relative;" value="Busca" onclick="if(this.value=='Busca'){this.value='';}" onKeyPress="if(event.keyCode==13){return false;}" onKeyDown="if(event.keyCode==13){buscaMenu(this.value,this.id);}" title="Buscar por arquivo"></span><span class="caixa" style="height:16px; width:16px; font-size:9px; border-left:none; margin:0px; background-image:url(../images/silk/find.gif); background-position:right; background-repeat:no-repeat; padding:3px; cursor:pointer;" onclick="popupBuscaAvancada();" title="Busca Avançada p;</span></div></td>
			  </tr>
		  </table></td>
          <td width="68" rowspan="2" > </td>
          <td colspan="2"> </td>
          <td width="8" rowspan="2" class="<smarty>$classe</smarty>"> </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
			<table width="100%" border="0">
                
                <tr>
                  <td width="13%" class="td_sp"><label class="label_descricao_campos">OS</label>
				    <select name="id_os" id="id_os" class="caixa"  onChange="estado_inicial(this.value);xajax_seta_checkin_checkout(this.value);"  onkeypress="">
                      <option value="">SELECIONE</option>
					<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                  </select>                  </td>
                  <td width="13%" class="td_sp"><label class="label_descricao_campos">Disciplina</label>
                    <select name="disciplina" class="caixa"  id="disciplina" onChange="disciplinas_inicial(this.value);" onkeypress="return keySort(this);">
                      <option value="">SELECIONE</option>
					<smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
                  </select></td>
                  <td width="74%" class="td_sp"> </td>
                </tr>
              </table>
				<table width="100%" border="0">
                  <tr>
                    <td width="22%" class="td_sp"><label class="label_descricao_campos">Tipo de documento</label><br /> 

					<select name="CodAtividade" class="caixa"  id="CodAtividade" onChange="document.getElementById('btn_adicionar').disabled=true; if(this.options[this.options.selectedIndex].value) { document.getElementById('btn_adicionar').disabled=false; }  " onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                    </select>                    </td>
                    <td width="78%" class="td_sp"> </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="td_sp"><div id="teste"></div> </td>
                  </tr>         
          </table>
          
          <table width="100%" border="0">
         <tr>
          <td  class="label_descricao_campos" style="font-size:11px">Para refinar a busca, digite mais informações abaixo e clique novamente no botão "Buscar".</td>
          <td  class="label_descricao_campos" style="font-size:11px"> </td>
        </tr>
		<tr>
		  <td ><input name="txt_busca_inicial" size="100" type="text" class="caixa" id="txt_busca_inicial" onkeyup="if(event.keyCode==13){xajax_preencheArquivos(xajax.getFormValues('frm_ged'));}" value=""></td>
		  <td > <div id="div_res"></div></td>
          
        </tr>
          </table>         
          </td>
        </tr>
      </table>


<div id="div_painel" onMouseDown="" onMouseUp="" style="position:relative;">        
    <div id="tree1" setOnClickHandler="tonclick" setImagePath="../includes/dhtmlx/dhtmlxTree/codebase/imgs/" class="dhtmlxTree" style="width:28%; float:left; border-style:solid; border-color:#999999; border-width:1px; height:400px; overflow:auto; text-align:left;" oncontextmenu="return false">
     
     </div>
     
	<div id="div_separador" style="position:relative; width:1px; float:left; height:400px; border-width:2px; border-style:outset; background-color:#CCCCCC; "> </div>

	<div id="div_arquivos" style="width:70%; float:left;border-style:solid; border-color:#999999; border-width:1px; height:400px; padding:0px; overflow:auto; -moz-user-select:none;" oncontextmenu="return false;" onselectstart="return false;" unselectable="on"> </div>

	<div id="div_info" style="width:70%; visibility:hidden; float:left;border-style:solid; border-color:#ff0000; border-width:1px; height:1px; padding:0px; overflow:auto; -moz-user-select:none;"> </div>

</div>

</form>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>
