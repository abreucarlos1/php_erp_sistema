<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btn_atualizar" type="button" class="class_botao" id="btn_atualizar" value="Inserir" onclick="if(confirm('Deseja inserir os dados do valor?')){xajax_inserir(xajax.getFormValues('frm'));}" />
				  </td>
				<tr>
				  <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			  </tr>
			      <input type="hidden" value="" id="id_regiao" name="id_regiao" />
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
              <tr>
                <td width="100%"><label for="regiao" class="labels">Região</label><br /> 
                  <input name="regiao" type="text" class="caixa" id="regiao" size="40" placeholder="Região" /></td>
              </tr>
            </table>
		</td>
        </tr>
      </table>
    <div id="regioes" style="width:100%;"> </div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>