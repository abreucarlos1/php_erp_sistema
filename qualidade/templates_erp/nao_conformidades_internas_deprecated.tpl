<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm"  id="frm" method="POST" enctype="multipart/form-data" action="upload.php" target="upload_target"  >
	<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Salvar" onclick="xajax_insere(xajax.getFormValues('frm',true),0);" /></td>
                            <input type="hidden" id="prefixo" name="prefixo" value="RNC" />
                   			<input name="id" id="id" value="" type="hidden" />
                    </tr>
        			<tr>
        				<td valign="middle">
        					<input name="btnenviar" type="button" class="class_botao" id="btnenviar" onclick="xajax_insere(xajax.getFormValues('frm',true),1);" value="Salvar e enviar" /></td>
					</tr>
        			<tr>
        			  <td valign="middle"><input name="btnimprimir" type="button" class="class_botao" id="btnimprimir" onclick="imprimir();" value="<smarty>$botao[8]</smarty>" /></td>
      			  </tr>
        			<tr>
        				<td valign="middle">
                   	    <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="td_sp">
            <div id="div_pac" style="height:400px; overflow-y:scroll"> 
            <table border="0" width="95%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="7%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[2]</smarty></label>
                <select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
                  <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                </select>
                </td>
                <td width="93%" class="td_sp">&nbsp;</td>
              </tr>
              </table>
              <table border="0" width="95%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="14%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[3]</smarty></label>
                  <input name="codigo" type="text" class="caixa" id="codigo" size="18" readonly="readonly" value="<smarty>$codigo</smarty>" />
		          	
				  </td>
					<td width="14%" class="td_sp"><label class="labels">
		        </label>
					  <label class="labels">
					    <smarty>$campo[4]</smarty>
				      </label>
                  <input name="originador" type="text" class="caixa" id="originador" size="50" readonly="readonly" value="<smarty>$originador</smarty>" /></td>
					<input name="id_originador" id="id_originador" type="hidden" value="<smarty>$id_originador</smarty>" />
                    <td width="14%" class="td_sp"><label class="labels">
		        </label>
                      <label class="labels">
                        <smarty>$campo[5]</smarty>
                      </label>
                  <input name="setor" type="text" class="caixa" id="setor" size="50" readonly="readonly" value="<smarty>$setor</smarty>" /></td>
					<input name="id_setor" id="id_setor" type="hidden" value="<smarty>$id_setor</smarty>" />
                    <td width="10%" class="td_sp"><label class="labels">
					  <smarty>$campo[6]</smarty>
					  </label>
                    <input name="data" type="text" class="caixa" id="data" size="10" onkeypress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onblur="return checaTamanhoData(this,10);" /></td>
			    <td width="70%" class="td_sp">&nbsp;</td>
				</tr>
			</table>
              <table border="0" width="95%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="54%" class="td_sp"><label class="labels">
                    <smarty>$campo[7]</smarty>
                  </label>
                    <textarea name="desc_nc" id="desc_nc" cols="90" rows="5"></textarea></td>
                  <td width="46%" class="td_sp">&nbsp;</td>
                </tr>
              </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr valign="top">
        	      <td width="11%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[9]</smarty></label>
                <select name="disciplina" class="caixa" id="disciplina" onkeypress="return keySort(this);">
                  <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
                </select></td>
        	      <td width="42%" class="td_sp">&nbsp;</td>
        	      <td width="12%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[10]</smarty></label>
                <select name="cliente" class="caixa" id="cliente" onkeypress="return keySort(this);">
                  <smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
                </select>
       	          </td>
        	      <td width="35%" class="td_sp">&nbsp;</td>
       	        </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[11]</smarty>
        	      </label>
                  <textarea name="desc_acao_imediata" id="desc_acao_imediata" cols="90" rows="5"></textarea>
                  </td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
       	        </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[12]</smarty>
      	        </label>
        	        <textarea name="desc_perdas" id="desc_perdas" cols="90" rows="5"></textarea></td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[13]</smarty>
      	        </label>
        	        <textarea name="desc_eficacia" id="desc_eficacia" cols="90" rows="5" disabled="disabled"></textarea></td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[22]</smarty>
      	        </label>
        	        <textarea name="desc_evidencia" id="desc_evidencia" cols="90" rows="5" disabled="disabled"></textarea></td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
            
            <div id="div_arq" style="visibility:hidden;">
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="95%" class="td_sp"><label class="labels"><smarty>$campo[23]</smarty></label>
                
                        <div id="div_arquivos" style="border:1px; border-color:#009; border-style:solid;">
                        	&nbsp;                    
                        </div>
                   </td> 
        	      <td width="5%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
            </div>
                  
            <div id="div_anex" style="visibility:hidden;">
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="10%" class="td_sp"><label class="labels"><smarty>$campo[23]</smarty></label>
                
                        <div id="div_anexos" style="width:10px;">
                        	<input type="file" name="input_1" id="input_1">                     
                        </div>
                        <input name="qtd" id="qtd" type="hidden" value="1" />
                        <img name="img_1" id="img_1" src="../images/silk/add.gif" style="cursor:pointer; margin-left:2px;" alt="Adicionar outro anexo" onclick="add_controles('div_anexos');">
                   </td> 
        	      <td width="90%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
            </div>           
            <div id="div_status" style="width:88%; overflow:hidden; border:1px; border-color:#000; border-style:solid;">
        	  <table border="0" width="90%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="14%" class="td_sp"><label class="labels">
        	        <smarty>$campo[14]</smarty>
      	        </label></td>
        	      <td width="86%" class="td_sp">&nbsp;</td>
       	        </tr>
      	    </table>
        	  <table border="0" width="90%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="12%" class="td_sp">&nbsp;</td>
        	      <td width="17%" class="td_sp"><label class="labels">
        	        <smarty>$campo[18]</smarty></label>
      	            <input type="radio" name="status" id="status_0" value="0" disabled="disabled"  />
        	      </td>
        	      <td width="17%" class="td_sp"><label class="labels">
        	        <smarty>$campo[19]</smarty></label>
                    <input type="radio" name="status" id="status_1" value="1" disabled="disabled"  />
        	      </td>
        	      <td width="18%" class="td_sp"><label class="labels">
        	        <smarty>$campo[20]</smarty></label>
                    <input type="radio" name="status" id="status_2" value="2" disabled="disabled" />
        	      </td>
        	      <td width="36%" class="td_sp">&nbsp;</td>
      	        </tr>
      	    </table>
            </div>
            <br />
            <div id="div_proc" style="width:88%; overflow:hidden; border:1px; border-color:#000; border-style:solid;">
        	  <table border="0" width="90%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
                  <smarty>$campo[15]</smarty></label></td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
        	  <table border="0" width="90%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="11%" class="td_sp">&nbsp;</td>
        	      <td width="8%" class="td_sp"><label class="labels">Sim</label>
        	        <input type="radio" name="procedente" id="procedente_0" value="1" disabled="disabled"  /></td>
        	      <td width="9%" class="td_sp"><label class="labels">Não</label>
        	        <input type="radio" name="procedente" id="procedente_1" value="2" disabled="disabled"  /></td>
        	      <td width="72%" class="td_sp">&nbsp;</td>
       	        </tr>
      	    </table>
            </div>
            <br />
            <div id="div_plano" style="width:88%; overflow:hidden; border:1px; border-color:#000; border-style:solid;">
        	  <table border="0" width="90%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[16]</smarty>
      	        </label></td>
        	      <td width="46%" class="td_sp">&nbsp;</td>
      	      </tr>
      	    </table>
              <table border="0" width="90%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="10%" class="td_sp">&nbsp;</td>
                  <td width="9%" class="td_sp"><label class="labels">Não</label>
                    <input type="radio" name="pac" id="pac_0" value="0" disabled="disabled" onclick="document.getElementById('id_plano').selectedIndex=0;document.getElementById('id_plano').disabled=true;"  /></td>
                  <td width="11%" class="td_sp">&nbsp;</td>
                  <td width="8%" class="td_sp"><label class="labels">Sim</label>
                    <input type="radio" name="pac" id="pac_1" value="1" disabled="disabled" onclick="document.getElementById('id_plano').disabled=false;"  /></td>
                  <td width="19%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[17]</smarty></label>
                <select name="id_plano" class="caixa" id="id_plano" onkeypress="return keySort(this);" disabled="disabled">
                  <smarty>html_options values=$option_pac_values output=$option_pac_output</smarty>
                </select></td>
                <td width="43%" class="td_sp">&nbsp;</td>
                </tr>
              </table>
              </div>
            </div>
            
            </td>
        </tr>
      </table>
	  <div id="dv_rotinas" style="scrollbar-face-color : #AAAAAA; scrollbar-highlight-color : #AAAAAA; scrollbar-3dlight-color : #ffffff; scrollbar-shadow-color : #FFFFFF; scrollbar-darkshadow-color : #FFFFFF; scrollbar-track-color : #FFFFFF; scrollbar-arrow-color : #FFFFFF;">&nbsp;</div>
<label class="labels">
  <smarty>$campo[21]</smarty></label><br />
						<select name="filtro" class="caixa" id="filtro" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm',true));">
							<option value="0">GERAL</option>
                            <option value="1">PENDENTE</option>
                            <option value="2">EM ANÁLISE</option>
                            <option value="3">ATRASADOS</option>
                            <option value="4">ENCERRADOS</option>
						</select>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>