<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:660px;" onClick="buscaMenu();">
<form name="frm" id="frm" action="upload_orcamento.php" method="POST" target="upload_target" enctype="multipart/form-data" onsubmit="startUpload_orcamento();">
	<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe><!-- width:0;height:0; -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle">
        					<input name="btn_adicionar" id="btn_adicionar" type="submit" class="class_botao" value="Adicionar" disabled="disabled" />
                        </td>
					</tr>
        			<tr>
        				<td valign="middle">
                        <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
                    	</td>
                   	</tr>
                    <tr>
                    <td>
						<input type="hidden" id="id_arquivo" name="id_arquivo" value="" />
	                 </td>
                    </tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="td_sp">
                <table border="0" width="95%" cellpadding="0" cellspacing="0">
                  <tr>
                        <td width="10%" class="td_sp"><label class="labels"><smarty>$campo[2]</smarty></label>
                        <select name="id_proposta" id="id_proposta" class="caixa" onchange="xajax_filtra_os(this.value);if(this.value!='' && document.getElementById('documento').value!=''){document.getElementById('btn_adicionar').disabled=false;}else{document.getElementById('btn_adicionar').disabled=true}" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                        <smarty>html_options values=$option_proposta_values output=$option_proposta_output</smarty>
                      </select>
                    </td>
                    <td width="5%" class="td_sp"><label class="labels"><smarty>$campo[3]</smarty></label>
                        <select name="documento" class="caixa"  id="documento" onchange="if(this.value!='' && document.getElementById('id_proposta').value!=''){document.getElementById('btn_adicionar').disabled=false}else{document.getElementById('btn_adicionar').disabled=true}" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                        <smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
                      </select>
                    </td>                    
                        <td width="90%" class="td_sp" >&nbsp;</td>
                  </tr>
                </table>
				<table border="0" width="95%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="34%" class="td_sp"><label class="labels">
                    <smarty>$campo[4]</smarty></label>
                    <input type="file" id="arquivo" name="arquivo"/><br />
					<div id="inf_upload">&nbsp;</div> 
                    </td>
                   	<td width="9%" class="td_sp" >
						<label class="labels"><smarty>$campo[6]</smarty></label>
						<input name="revisao" size="5" type="text" class="caixa" id="revisao" value="0">
                    </td>
                   	<td width="57%" class="td_sp" >&nbsp;</td>
                  </tr>
                </table>
				<table border="0" width="95%" cellpadding="0" cellspacing="0">
                  <tr>
                        <td width="10%" class="td_sp"><label class="labels">
                          <smarty>$campo[5]</smarty></label>
						<input name="txt_busca_inicial" size="100" type="text" class="caixa" id="txt_busca_inicial" onkeyup="if(event.keyCode==13){xajax_preencheArquivos(xajax.getFormValues('frm'));}" value="">
                        
                    </td>
                   	<td width="90%" class="td_sp" >&nbsp;</td>
                  </tr>
              </table>
                
            </td>
        </tr>
      </table>
        <div id="div_painel" style="position:relative;">         
                
            <div id="tree1" style="width:28%; float:left; border-style:solid; border-color:#999999; border-width:1px; height:400px; overflow:auto; text-align:left;">&nbsp;</div>
          
            <div id="div_separador" style="position:relative; width:1px; float:left; height:400px; border-width:1px; border-style:outset; background-color:#CCCCCC; ">&nbsp;</div>
        
            <div id="div_arquivos" style="width:70%; float:left;border-style:solid; border-color:#999999; border-width:1px; height:400px; padding:0px; overflow:auto;">&nbsp;</div>
        
            <div id="div_info" style="width:70%; visibility:hidden; float:left;border-style:solid; border-color:#ff0000; border-width:1px; height:1px; padding:0px; overflow:auto;">&nbsp;</div>
        
        </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>