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
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm',true),0);" value="Salvar" /></td>
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
					<td width="14%" class="td_sp"><label class="labels">		        
		          <smarty>$campo[2]</smarty></label>
                  <input name="codigo" type="text" class="caixa" id="codigo" size="18" readonly="readonly" value="<smarty>$codigo</smarty>" />
		          <input name="id" id="id" value="" type="hidden" />	
                  <input type="hidden" id="prefixo" name="prefixo" value="PAC" />
                  </td>
					<td width="14%" class="td_sp"><label class="labels">
		        </label>
					  <label class="labels">
					    <smarty>$campo[3]</smarty>
				      </label>
                  <input name="originador" type="text" class="caixa" id="originador" size="60" readonly="readonly" value="<smarty>$originador</smarty>" /></td>
					<input name="id_originador" id="id_originador" type="hidden" value="<smarty>$id_originador</smarty>" />
                    <td width="14%" class="td_sp"><label class="labels">
		        </label>
                      <label class="labels">
                        <smarty>$campo[4]</smarty>
                      </label>
                  <input name="setor" type="text" class="caixa" id="setor" size="50" readonly="readonly" value="<smarty>$setor</smarty>" /></td>
					<input name="id_setor" id="id_setor" type="hidden" value="<smarty>$id_setor</smarty>" />
                    <td width="10%" class="td_sp"><label class="labels">
					  <smarty>$campo[5]</smarty>
					  </label>
                    <input name="data_pac" type="text" class="caixa" id="data_pac" size="12" onkeypress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onblur="return checaTamanhoData(this,10);" /></td>
			    <td width="70%" class="td_sp"> </td>
					</tr>
			</table>
            <table border="0" width="100%" cellpadding="0" cellspacing="0">
              <tr valign="top">
                <td width="97%" class="td_sp"><label class="labels">
                  <smarty>$campo[17]</smarty>
                </label>
                  <div id="div_tipo" style="width:99%; overflow:hidden; border:1px; border-color:#999; border-style:solid;">
                  <table border="0" width="95%" cellpadding="0" cellspacing="0">
                    <tr valign="middle">
                      <td width="29%" class="td_sp">
                        <input type="radio" name="tipo_acao" id="tipo_acao" value="1" />
                      	<label class="labels">
                        <smarty>$campo[18]</smarty></label>
                      </td>
                      <td width="31%" class="td_sp">
                        <input type="radio" name="tipo_acao" id="tipo_acao" value="2" />
                      	<label class="labels">
                        <smarty>$campo[19]</smarty></label>
                      </td>
                      <td width="40%" class="td_sp"> </td>
                    </tr>
                </table>
                  </div></td>
                <td width="3%" class="td_sp"> </td>
              </tr>
            </table>
            <table border="0" width="100%" cellpadding="0" cellspacing="0">
       	      <tr valign="top">
        	      <td width="97%" class="td_sp"><label class="labels"><smarty>$campo[6]</smarty></label>
       	          <div id="div_doc_ref" style="width:99%; overflow:hidden; border:1px; border-color:#999; border-style:solid;"><smarty>$doc_ref</smarty></div>
                  </td>
        	      <td width="3%" class="td_sp"> 
       	          </td>
   	          </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="74%" class="td_sp"><label class="labels">
        	        <smarty>$campo[7]</smarty>
        	      </label>
                  <textarea name="desc_nc" id="desc_nc" cols="98" rows="5"></textarea>
                  </td>
        	      <td width="26%" class="td_sp"> </td>
       	        </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="74%" class="td_sp"><label class="labels">
        	        <smarty>$campo[8]</smarty>
      	        </label>
        	        <textarea name="desc_acao" id="desc_acao" cols="98" rows="5"></textarea></td>
        	      <td width="26%" class="td_sp"> </td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="74%" class="td_sp"><label class="labels">
        	        <smarty>$campo[20]</smarty>
      	        </label>
        	        <textarea name="desc_causa" id="desc_causa" cols="98" rows="5"></textarea></td>
        	      <td width="26%" class="td_sp"> </td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="70%" class="td_sp"><label class="labels">
        	        <smarty>$campo[9]</smarty></label>
                    <div id="div_acao_complementar" style="width:99%; overflow:hidden; border:1px; border-color:#999; border-style:solid;"> </div>
                    <img src="../imagens/add.png" style="cursor:pointer" onclick="add();" />
                    <input type="hidden" name="itens" id="itens" value="1">
                    </td>
        	      <td width="30%" class="td_sp"> </td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[10]</smarty>
      	        </label>
        	        <textarea name="desc_obs" id="desc_obs" cols="98" rows="5"></textarea></td>
        	      <td width="46%" class="td_sp"> </td>
      	      </tr>
      	    </table>
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[11]</smarty>
      	        </label>
        	        <textarea name="desc_encerramento" id="desc_encerramento" cols="98" rows="5"></textarea></td>
        	      <td width="46%" class="td_sp"> </td>
      	      </tr>
      	    </table>
            
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="54%" class="td_sp"><label class="labels">
        	        <smarty>$campo[21]</smarty>
      	        </label>
        	        <textarea name="desc_evidencia" id="desc_evidencia" cols="98" rows="5" disabled="disabled"></textarea></td>
        	      <td width="46%" class="td_sp"> </td>
      	      </tr>
      	    </table>
            <div id="div_arq" style="visibility:hidden;">
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="95%" class="td_sp"><label class="labels"><smarty>$campo[22]</smarty></label>
                
                        <div id="div_arquivos" style="border:1px; border-color:#009; border-style:solid;">
                        	                     
                        </div>
                   </td> 
        	      <td width="5%" class="td_sp"> </td>
      	      </tr>
      	    </table>
            </div>
                  
            <div id="div_anex" style="visibility:hidden;">
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="10%" class="td_sp"><label class="labels"><smarty>$campo[22]</smarty></label>
                
                        <div id="div_anexos" style="width:10px;">
                        	<input type="file" name="input_1" id="input_1">                     
                        </div>
                        <input name="qtd" id="qtd" type="hidden" value="1" />
                        <img name="img_1" id="img_1" src="../imagens/add.png" style="cursor:pointer; margin-left:2px;" alt="Adicionar outro anexo" onclick="add_controles('div_anexos');">
                   </td> 
        	      <td width="90%" class="td_sp"> </td>
      	      </tr>
      	    </table>
            </div>
             
        	  <table border="0" width="95%" cellpadding="0" cellspacing="0">
        	    <tr valign="middle">
        	      <td  width="54%" class="td_sp">
      	            <input type="radio" name="status_pac" id="status_pac" value="0" checked="checked" disabled="disabled" />
        	      <label class="labels">
        	        <smarty>$campo[13]</smarty></label>
                  </td>
        	      <td width="46%" class="td_sp">
                    <input type="radio" name="status_pac" id="status_pac" value="1" disabled="disabled" />
        	      <label class="labels">
        	        <smarty>$campo[14]</smarty></label>
                  </td>
        	      <td width="46%" class="td_sp"> </td>
      	        </tr>
      	    </table>
            </div>
            
            </td>
        </tr>
      </table>
	  <div id="dv_rotinas" style="scrollbar-face-color : #AAAAAA; scrollbar-highlight-color : #AAAAAA; scrollbar-3dlight-color : #ffffff; scrollbar-shadow-color : #FFFFFF; scrollbar-darkshadow-color : #FFFFFF; scrollbar-track-color : #FFFFFF; scrollbar-arrow-color : #FFFFFF;"> </div>
<label class="labels"><smarty>$campo[16]</smarty></label><br />
						<select name="filtro" class="caixa" id="filtro" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm',true));">
							<option value="0">GERAL</option>
                            <option value="1">EM ANDAMENTO</option>
                            <option value="2">ATRASADOS</option>
                            <option value="3">ENCERRADOS</option>
						</select>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>