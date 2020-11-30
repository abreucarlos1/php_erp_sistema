<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
    <form name="frm" id="frm" method="POST">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">                
            <tr>
                <td width="116" valign="top" class="espacamento">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="middle">
                                <input name="btninserir" type="button" class="class_botao" id="btninserir" disabled="disabled" onclick="xajax_modal_transferir(xajax.getFormValues('frm'));" value="Transferir" />
                            </td>
                        </tr>
                        <tr>
                            <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
                        </tr>
                    </table>
                </td>
                <td colspan="2" valign="top">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="td_sp"><label class="labels">Funcionários com apontamentos na OS</label><br />
                            <!-- Este módulo está preparado para trabalhar com multiple, então, se necessário basta adicionar este atributo no select abaixo -->
                                <select name="funcionarios[]" class="caixa" id="funcionarios" onkeypress="return keySort(this);">
                                    <smarty>html_options values=$option_funcs_values output=$option_funcs_output</smarty>
                                </select>
                            </td>
                            <td>
                            	<label class="labels">Período De</label><input name="periodo_de" type="text" class="caixa" id="periodo_de" size="10" onKeyPress="transformaData(this, event);" onBlur="return checaTamanhoData(this,10);" />&nbsp;
                            	<label class="labels">Até</label><input name="periodo_ate" type="text" class="caixa" id="periodo_ate" size="10" onKeyPress="transformaData(this, event);" onBlur="return checaTamanhoData(this,10);" />
                            </td>
                            <td>
                            	<input type="hidden" name="idHoras" id="idHoras" />
                            	<input type="button" value="Filtrar" onclick="if(funcionarios.value>0)xajax_atualizatabela(xajax.getFormValues('frm'));" class="class_botao" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
    <div id="div_apontamentos" style="height:600px;"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>