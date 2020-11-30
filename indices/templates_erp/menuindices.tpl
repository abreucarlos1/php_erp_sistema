<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%;height: 700px;">
<form name="frm_menumanutencao" id="frm_menumanutencao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		    <table width="100%" border="0">
            
            <tr align="center">
              <td class="tabela_body"><input name="Button1" type="button" class="class_botao_menu_hab" value="HORAS DE RETRABALHO" onclick="location.href='horas_retrabalho.php';" /></td>
              <td class="tabela_body"><input name="Button5" type="button" class="class_botao_menu_hab" value="HORAS/A1 EQUIVALENTE" onclick="location.href='a1_equivalente.php';" /></td>
            </tr>
          </table>		    </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>