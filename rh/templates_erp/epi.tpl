<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div style="height: 660px;">
<form name="frm_epi" id="frm_epi" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<input type="hidden" value="<smarty>$campoReferencia</smarty>" id="campoRef" name="campoRef" />
	<input type="hidden" value="<smarty>$adicional</smarty>" id="adicional" name="adicional" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle" ><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onClick="xajax_salvar(xajax.getFormValues('frm_epi'));" /></td>
			  </tr>
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td class="tp_sp">
				<table border="0" width="100%">
                  <tr>
                  	<td width="10%" class="td_sp">
					</td>
				  </tr>
				  <tr>
                    <td width="39%" class="td_sp">
                  		<label class="labels" style="float:left; width: 150px;">Descrição EPI</label>
               	    	<input name="descricao_epi" type="text" class="caixa" id="descricao_epi" size="80" placeholder="Descrição do EPI" />
               	    	<input name="id_epi" type="hidden" id="id_epi" value="">
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">CA</label>
               	    	<input name="ca" type="text" class="caixa" id="ca" size="15" placeholder="Código CA" />
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Fabricante</label>
               	    	<input name="fabricante" type="text" class="caixa" id="fabricante" size="80" placeholder="Nome do Fabricante"/>
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Validade C.A.</label>
               	    	<input name="vencimento" type="text" class="caixa" placeholder="Data Validade" id="vencimento" size="12" onkeyup="transformaData(this, event);" />
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Observações</label>
               	    	<textarea name="obs" class="caixa" id="obs" cols="78" placeholder="Observações gerais" rows="5"></textarea>
               	    </td>
                  </tr>
                  <tr>
                  	<td>
                  		<label class="labels" style="float:left; width: 150px;">Busca</label>
                  		<input name="busca" id="busca" size="55" type="text" placeholder="EPI, Vencimento, Observação, Fabricante" class="caixa" value="" onkeyup="iniciaBusca.verifica(this);" />
                  	</td>
                  </tr>
                </table>                          
          </td>
        </tr>
        
        <tr>
          <td class="fundo_azul">&nbsp;</td>
          <td colspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>
      </table>
	  <div id="div_epi" style="width:100%;float:left"></div>      
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>