<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="height: 660px;">
<form name="frm_unidade" id="frm_unidade" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<input type="hidden" value="<smarty>$campoReferencia</smarty>" id="campoRef" name="campoRef" />
	<input type="hidden" value="<smarty>$adicional</smarty>" id="adicional" name="adicional" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle" ><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onClick="xajax_insere(xajax.getFormValues('frm_unidade'));" /></td>
			  </tr>
				<tr>
					<td valign="middle" ><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
				<smarty>if isset($ocultarCabecalhoRodape)</smarty>
				<tr>
					<td valign="middle"><input name="btnselecionar" id="btnselecionar" onclick="<smarty>if isset($adicional)</smarty>seleciona(document.getElementById('unidade').value,document.getElementById('adicional').value)<smarty>else</smarty>seleciona(document.getElementById('unidade').value)<smarty>/if</smarty>" disabled="disabled" type="button" class="class_botao" value="Selecionar" /></td>
				</tr>
				<smarty>/if</smarty>
				<tr>
				  <td valign="middle" ><input name="id_unidade" type="hidden" id="id_unidade" value=""></td>
			  </tr>
			</table></td>
        </tr>        
        <tr>
          <td class="tp_sp">
				<table border="0" width="100%">
                  <tr>
                  	<td width="10%" class="td_sp">
                  		<label class="labels" style="float:left; width: 150px;">C&oacute;digo</label>
						<input name="codigo" type="text" class="caixa" id="codigo" size="10" maxlength="3" />
					</td>
				  </tr>
				  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Unidade</label>
               	    	<input name="unidade" type="text" class="caixa" id="unidade" size="20" />
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Descrição Português</label>
               	    	<input name="descPort" type="text" class="caixa" id="descPort" size="50" />
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Descrição Inglês</label>
               	    	<input name="descIngles" type="text" class="caixa" id="descIngles" size="50" />
               	    </td>
                  </tr>
                  <tr>
                    <td width="39%" class="td_sp">
                    	<label class="labels" style="float:left; width: 150px;">Descrição Espanhol</label>
               	    	<input name="descEsp" type="text" class="caixa" id="descEsp" size="50" />
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
	  <div id="unidades" style="width:100%;float:left"></div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>