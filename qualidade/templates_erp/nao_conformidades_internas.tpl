<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm"  id="frm" method="POST" enctype="multipart/form-data" action="upload.php" target="upload_target">
	<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>
    <table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao campoOriginador campoResponsavel" id="btninserir" value="Salvar" onclick="xajax_insere(xajax.getFormValues('frm',true),0);" />
                            <input type="hidden" id="prefixo" name="prefixo" value="RNC" />
                   			<input name="id" id="id" value="<smarty>$id</smarty>" type="hidden" />
                   		</td>
                    </tr>
        			<tr>
        				<td valign="middle">
        					<input name="btnenviar" type="button" class="class_botao campoOriginador" id="btnenviar" onclick="xajax_insere(xajax.getFormValues('frm',true),1);" value="Salvar e enviar" />
        				</td>
					</tr>
        			<tr>
        				<td valign="middle">
                   	    <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="window.location='./formulario_reporte.php';" /></td>
					</tr>
					<tr>
        				<td valign="middle">
                   	    	<input name="btnimprimir" id="btnimprimir" type="button" class="class_botao" value="<smarty>$botao[5]</smarty>" onclick="imprimir_rnc(<smarty>$id</smarty>);" />
                   	    </td>
					</tr>
       			</table>
		  </td>
        	<td colspan="2" valign="top" class="espacamento">
            <div id="div_pac" style="width:98%; height:640px; overflow-y:scroll; overflow-x:hidden;">
            <label class="labels">Status *</label>                      
            <div id="div_status" style="width:98%; overflow:hidden; border:1px; border-color:#000; border-style:solid;">
           	  <table border="0" width="100%">
        	    <tr align="center">
        	      <td width="37%"><label class="labels">Pendente</label>
      	            <input type="radio" name="status_nc" id="status_0" value="0" checked="checked" disabled="disabled" />
        	      </td>
        	      <td width="37%"><label class="labels">Em andamento</label>
                    <input type="radio" name="status_nc" id="status_1" value="1" disabled="disabled" />
        	      </td>
        	      <td width="37%"><label class="labels">Encerrada</label>
                    <input type="radio" name="status_nc" id="status_2" value="2" disabled="disabled" />
        	      </td>
      	        </tr>
      	    </table>
            </div>
            <br />
              <table border="0" width="100%">
				<tr>
					<td width="5%"><label for="codigo" class="labels"><smarty>$campo[3]</smarty></label><br />
                  		<input name="codigo" type="text" class="caixa" id="codigo" size="18" readonly="readonly" value="<smarty>$codigo</smarty>" />
		          	
				  </td>
					<td width="5%"><label for="originador" class="labels"><smarty>$campo[4]</smarty>*</label><br />
                  		<input name="originador" type="text" class="caixa" id="originador" size="32" readonly="readonly" value="<smarty>$originador</smarty>" /></td>
						<input name="id_originador" id="id_originador" type="hidden" value="<smarty>$id_originador</smarty>" />
                    <td width="5%"><label for="setor" class="labels"><smarty>$campo[5]</smarty>*</label><br />
                  		<input name="setor" type="text" class="caixa" id="setor" size="32" readonly="readonly" value="<smarty>$setor</smarty>" /></td>
						<input name="id_setor" id="id_setor" type="hidden" value="<smarty>$id_setor</smarty>" />
                    <td width="5%"><label for="data_nc" class="labels"><smarty>$campo[6]</smarty>*</label><br />
                    <input name="data_nc" type="text" class="caixa campoOriginador" id="data_nc" size="10" onkeypress="transformaData(this, event);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onblur="return checaTamanhoData(this,10);" /></td>
                </tr>
			</table>
            <table border="0" width="100%">
       	      <tr valign="top">
        	      <td width="100%"><label class="labels">Tipo de documento*</label><br />
       	          <div id="div_doc_ref" style="width:100%; overflow:hidden; border:1px; border-color:#999; border-style:solid;"><smarty>$doc_ref</smarty></div>
                  </td>
   	          </tr>
   	          <tr valign="top">
        	      <td width="100%" class="td_sp"><label class="labels">Origem*</label><br />
       	          <div id="div_tp_origem" style="width:100%; overflow:hidden; border:1px; border-color:#999; border-style:solid;"><smarty>$tp_orig</smarty></div>
                  </td>
   	          </tr>
      	    </table>
              <table border="0" width="100%">
                <tr>
                  <td width="10%"><label for="desc_nc" class="labels">Descrição da ocorrência*</label><br />
                    <textarea name="desc_nc" id="desc_nc" cols="80" rows="5" class="caixa campoOriginador"></textarea></td>
  
                </tr>
              </table>
        	  <table border="0" width="100%">
        	    <tr valign="top">
        	      <td width="5%"><label for="escolhaos" class="labels">Projeto*</label><br />
                <select name="escolhaos" class="caixa campoOriginador" style="width:150px" id="escolhaos" onkeypress="return keySort(this);" onchange="xajax_clientes(this.value);">
                  <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                </select>
                  </td>
        	      <td width="5%"><label for="disciplina" class="labels">Disciplina*</label><br />
        	        <select name="disciplina" class="caixa campoOriginador" id="disciplina" onkeypress="return keySort(this);">
        	          <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
      	          </select></td>
        	      <td width="5%"><label for="cliente" class="labels">Cliente</label><br />
        	        <select name="cliente" class="caixa campoOriginador" style="width:430px" id="cliente" onkeypress="return keySort(this);">
        	          <smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
       	            </select>
      	        </td>
       	        </tr>
      	    </table>
        	  <table border="0" width="100%">
        	    <tr>
        	      <td width="100%"><label for="desc_acao_imediata" class="labels">Ação imediata (o que foi feito no momento para corrigir o erro?)*</label><br />
                  <textarea name="desc_acao_imediata" class="campoOriginador" id="desc_acao_imediata" cols="80" rows="5"></textarea>
                  </td>
       	        </tr>
      	    </table>
        	  <table border="0" width="100%">
        	    <tr>
        	      <td width="100%"><label for="desc_perdas" class="labels">Perdas*</label><br />
        	        <textarea name="desc_perdas" class="caixa campoOriginador" id="desc_perdas" cols="80" rows="5"></textarea></td>
      	      </tr>
      	    </table>
			<label class="labels">Procedente</label>
            <div id="div_proc" style="width:98%; overflow:hidden; border:1px; border-color:#ddd; border-style:solid;">
        	  <table border="0" width="100%">
        	    <tr align="center">
        	      <td width="40%"><label class="labels">Sim</label>
        	        <input type="radio" name="procedente" id="procedente_0" onclick="document.getElementById('tableAnaliseCausa').style.display='block';" value="1" class="caixa campoSGI"  /></td>
                  <td width="10%"> </td>
        	      <td width="40%"><label class="labels">Não</label>
        	        <input type="radio" name="procedente" id="procedente_1" onclick="document.getElementById('tableAnaliseCausa').style.display='none';" value="2" class="caixa campoSGI" /></td>
       	        </tr>
      	    </table>          
            </div>
            <table border="0" width="100%" id="tableAnaliseCausa" style='display:none;'>
       	        <tr>
        	      <td colspan="3" width="100%"><label for="desc_analise_causa" class="labels">Análise da Causa</label><br />
        	        <textarea name="desc_analise_causa" id="desc_analise_causa" cols="80" rows="5" class="campoOriginador"></textarea></td>
      	      </tr>
            </table>
            <br />
             <label class="labels"><strong>PLANO DE AÇÕES CORRETIVAS/PREVENTIVAS</strong></label>
            <table border="0" width="99%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="100%"><label class="labels">Ações complementares</label>
                      <div id="div_acao_complementar" style="width:99%;"> </div>
                      <img id="add_ac" name="add_ac" src="../imagens/add.png" style="cursor:pointer; visibility:hidden;" onclick="add_camp();" />
                      <input type="hidden" name="itens" id="itens" value="1">
                      </td>
                </tr>
              </table>             
        	  <table border="0" width="100%">
        	    <tr>
        	      <td width="100%"><label for="desc_evidencia" class="labels">Evidências das ações</label><br />
        	        <textarea name="desc_evidencia" id="desc_evidencia" cols="80" rows="5" class="caixa campoSGI"></textarea>
                  </td>
      	      </tr>
      	    </table>           
            <table border="0" width="99%">
                  <tr>
                    <td width="100%"><label class="labels">Anexos</label>
                      <div id="div_arquivos" style="width:98%;"> </div>
                      </td>
                </tr>
              </table> 
          
            <div id="div_anex" style="visibility:visible">
        	  <table border="0" width="99%" cellpadding="0" cellspacing="0">
        	    <tr>
        	      <td width="10%"><label class="labels">Arquivo</label>                
                        <div id="div_anexos" style="width:10px;">
                        	<input type="file" name="input_1" id="input_1" class="caixa campoSGI">                     
                        </div>
                        <input name="qtd" id="qtd" type="hidden" value="1" />
                        <img name="img_1" id="img_1" src="../imagens/add.png" style="visibility:hidden;cursor:pointer;margin-left:2px;" alt="Adicionar outro anexo" onclick="add_controles('div_anexos');">
                   </td> 
      	      </tr>
      	    </table>
            </div> 
        	  <table border="0" width="100%">
        	    <tr>
        	      <td width="100%"><label class="labels">Observações</label><br />
        	        <textarea name="desc_obs" id="desc_obs" cols="80" rows="5" class="caixa campoSGI"></textarea></td>
      	      </tr>
      	    </table>
      	    <div  id="tr_verificacao_eficacia" style="display:none;">
	          	<label class="labels">Verificação da eficácia</label><br />
	            <textarea name="desc_encerramento" id="desc_encerramento" cols="80" rows="5" class="caixa campoSGI campoResponsavel"></textarea>
	            <br /><br />
	            <div id="div_proc" style="width:98%; overflow:hidden; border:1px; border-color:#ddd; border-style:solid;">
		        	<table border="0" width="100%">
		        	    <tr align="center">
		        	      <td width="40%"><label class="labels">Sim</label>
		        	        <input type="radio" name="rd_eficacia" id="rd_eficacia1" value="1" class="caixa campoSGI" onclick="document.getElementById('status_2').checked = true;" /></td>
		                  <td width="20%"> </td>
		        	      <td width="40%"><label class="labels">Não</label>
		        	        <input type="radio" name="rd_eficacia" id="rd_eficacia2" value="2" class="caixa campoSGI" onclick="document.getElementById('status_1').checked = true;" /></td>
		       	        </tr>
		      	    </table>          
            	</div>
		      </div>
        	  <table border="0" width="100%">
        	  	<tr>
      	      	<td> </td>
      	      </tr>
      	      <tr>
        	      <td width="50%">
        	      <table border="0" width="100%">
        	      	<tr><td>
	        	      <label for="id_funcionario" class="labels">Responsável</label><br />
	        	        <select name="id_funcionario" class="caixa campoSGI campoResponsavel" style="width:200px" id="id_funcionario" onkeypress="return keySort(this);">
	        	          <smarty>html_options values=$option_func_values output=$option_func_output</smarty>
	       	            </select>
	        	      </td>
		        	  </tr>
                      <tr>
	        	      <td>
	        	      	<label for="data_resp" class="labels">Data</label><br />
	        	      	<input type="text" name="data_resp" id="data_resp" class="caixa campoSGI" size="10" size="10" onkeypress="transformaData(this, event);" onblur="return checaTamanhoData(this,10);"  />
	        	      </td>
        	      </tr>
        	      </table>
        	      </td>
      	      </tr>
      	    </table>            
            </div>            
            </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>