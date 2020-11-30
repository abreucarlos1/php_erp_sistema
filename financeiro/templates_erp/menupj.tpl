<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<smarty>if isset($erros)</smarty>
<smarty>foreach $erros as $err</smarty>
    <h2 style="color:red;"><smarty>$err</smarty></h2>
    <smarty>/foreach</smarty>
<smarty>/if</smarty>
<div id="frame" style="width:100%; height:690px;">
<form name="frm" id="frm" style="margin-top:5px; padding:0px;" method="POST" action="<smarty>$smarty.server.PHP_SELF</smarty>">
	<table width="100%" border="0">        
        <tr>
		  <td width="116" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onClick="history.back();" /></td>
				</tr>
			</table>
           </td>
          <td colspan="2" valign="top" class="espacamento">
		    <table width="100%" border="0" style="margin-bottom:100px">            
            <tr align="center">
              <td class="tabela_body"><input name="Button1" type="button" class="class_botao_menu_hab" id="Button1" value="FECHAMENTO"  onclick="xajax_chamapagina('fechamento')" /></td>
              <td class="tabela_body"><input name="Button2" type="button" class="class_botao_menu_hab" id="Button2" value="ANEXAR DOCUMENTOS" onclick="xajax_chamapagina('anexar')" /></td>
              <td class="tabela_body"><input name="Button3" type="button" class="class_botao_menu_hab" id="Button3" value="FECHAMENTO MODELO"  onclick="xajax_chamapagina('modelo')" /></td>
            </tr>
          </table>		   
          </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>