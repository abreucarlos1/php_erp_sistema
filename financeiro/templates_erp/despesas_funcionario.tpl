<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_atualizar(xajax.getFormValues('frm'))" disabled="disabled" value="<smarty>$botao[3]</smarty>" /></td>
					</tr>
               
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" />
                        <input name="id_requisicao_despesa" type="hidden" id="id_requisicao_despesa" value="">
                        <input name="itens" type="hidden" id="itens" value="0">
                        </td>
					</tr>
       			</table>
      </td>
        	<td colspan="2" valign="top">
			<div id="my_tabbar" style="height:400px;"> 
            
              <div id="a4">
              <div id="dv_adiantamento">&nbsp;</div>     
                <div id="dv_acerto_despesas">&nbsp;</div>
            </div>
            </div>
            
            </td>
        </tr>
      </table>
	  <div id="div_despesas" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>