<link href="../classes/css_geral.css" rel="stylesheet" type="text/css" />
<smarty>include file="../../templates/header.tpl"</smarty>
<form name="frm_menumanutencao" id="frm_menumanutencao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="fundo_cinza">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="middle" class="fundo_cinza" ><input name="btnvoltar" id="btnvoltar" type="button" class="botao_chanfrado" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
          <td width="132" rowspan="2" >&nbsp;</td>
          <td colspan="2">&nbsp;</td>
          <td width="6" rowspan="3" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
		    <table width="100%" border="0" style="margin-bottom:100px">
            
            <tr class="botao_cinza_larg_1" align="center">
              <td width="33%"><input name="Button1" type="button" class="botao_cinza_larg_1" value="BOT&Atilde;O 1" onclick="xajax_chamapagina('ramoatuacao')"  /></td>
              <td width="33%"><input name="Button5" type="button" class="botao_cinza_larg_1" value="BOT&Atilde;O 2" onclick="xajax_chamapagina('atividades')" /></td>
              <td width="33%"><input name="Button10" type="button" class="botao_cinza_larg_1" value="BOT&Atilde;O 3" onclick="xajax_chamapagina('setores')" /></td>
            </tr>
            <tr class="botao_cinza_larg_2" align="center">
              <td><input name="Button15" type="button" class="botao_cinza_larg_2" value="BOT&Atilde;O 4" onclick="xajax_chamapagina('alt_controlehoras')" /></td>
              <td><input name="Button20" type="button" class="botao_cinza_larg_2" value="BOT&Atilde;O 5" onclick="xajax_chamapagina('controlehoras_ext')" /></td>
              <td><input name="Button25" type="button" class="botao_cinza_larg_2" value="BOT&Atilde;O 6" onclick="xajax_chamapagina('feriados')" /></td>
            </tr>
          </table>		    </td>
        </tr>
        
        <tr>
          <td class="fundo_azul">&nbsp;</td>
          <td colspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>
      </table>
</form>
<smarty>include file="../../templates/footer.tpl"</smarty>