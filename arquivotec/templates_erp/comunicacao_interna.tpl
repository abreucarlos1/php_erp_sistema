<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload_referencias();" style="margin:0px; padding:0px;">
	<iframe id="upload_target" name="upload_target" src="#" style="height:0px;width:0px;border:0px solid #fff;display:none;"></iframe>
    <table width="100%" border="0">        
        <tr>
		  <td width="122" valign="top" class="espacamento">
		    <table width="100%" border="0">
					<tr>
					  <td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Inserir" /></td>
				    </tr>
					<tr>
						<td valign="middle"><input name="btnlimpar" id="btnlimpar" type="button" class="class_botao" value="Limpar" onClick="document.getElementById('frm').reset();" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
					</tr>
						<tr>
							<td><label for="busca" class="labels">Busca</label><br />
                            <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="xajax_atualizatabela(xajax.getFormValues('frm'));" size="15"></td>
						</tr>
					<tr>
					  <td valign="middle"><input name="id_documento_referencia" id="id_documento_referencia" type="hidden" value="">
	                  <input type="hidden" name="acao" id="acao" value="incluir" />
                      <input type="hidden" name="funcao" id="funcao" value="comunicacao_interna" />
                      <input type="hidden" value="3" id="tipo_doc" name="tipo_doc" />
                      </td>
				  </tr>
			  </table>
		  </td>
          <td colspan="2" valign="top" class="espacamento">
			<table border="0" width="100%">
              <tr>
                <td width="14%"><label for="id_os" class="labels">OS*</label><br />
                  <select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'),true);document.getElementById('btninserir').value='Inserir';document.getElementById('acao').value='incluir';" >
                    <option value="">SELECIONE</option>
                    <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
                  </select>
                
                <td width="86%">&nbsp;</td>
              </tr>
            </table>            
            <table border="0" width="100%">              
              <tr>
               
                <td width="24%"><label for="numdocumento" class="labels">Nº&nbsp;Documento</label><br />
                    <input name="numdocumento" type="text" class="caixa" id="numdocumento" placeholder="Número Documento" size="25" maxlength="50" />
                </td>
                <td width="24%"><label for="titulo" class="labels">Título/Assunto</label><br />
                	<input name="titulo" type="text" class="caixa" id="titulo" placeholder="Título" size="25" /></td>
                <td width="24%"><label for="palavras_chave" class="labels">Palavras-chave</label><br />
                	<input name="palavras_chave" type="text" class="caixa" id="palavras_chave" placeholder="Palavras chave" size="25"/></td>
              	<td width="30%"><label for="origem" class="labels">Origem</label><br />
              		<input name="origem" type="text" class="caixa" id="origem" placeholder="Origem" size="25" /></td>
              </tr>
            </table>
            <table border="0" width="100%">
              <tr>
                <td width="6%"><label for="revisao" class="labels">Revisão</label><br />
                	<input name="revisao" type="text" class="caixa" id="revisao" size="5" value="0" />
                </td>
                <td width="8%"><label for="data_registro" class="labels">Data</label><br />
                	<input name="data_registro" type="text" class="caixa" id="data_registro" size="10" onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" />
                </td>
                <td width="86%"><label class="labels">Arquivo*</label><br />
                	<input type="file" name="arquivo" id="arquivo" class="caixa" />
                  </td>
              </tr>
            </table>
            <table border="0" width="100%">
                <tr>
                <td width="6%"><label for="servico" class="labels">Serviço</label><br /> 
                    <select name="servico" class="caixa" id="servico" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_servico_values output=$option_servico_output</smarty>
                    </select>
                </td>
              </tr>
            </table>
            <div id="com_interna" style="display:block;">
			<table border="0" width="100%">
			  <tr>
			    <td width="40%"><label for="texto_ci" class="labels">Texto:</label><br />
		        <textarea name="texto_ci" id="texto_ci" cols="80" rows="5" class="caixa"></textarea></td>
		      </tr>
		    </table>
			</div>
            <p style="display:none;" id="inf_upload">&nbsp;</p>
           </td>
        </tr>
      </table>
    <div id="div_docs_referencia" style="height:260px;"><span class="labels" style="font-weight:bold">Selecione uma OS</span></div>      

</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>