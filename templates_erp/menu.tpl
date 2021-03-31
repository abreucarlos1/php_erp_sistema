<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<smarty>if isset($erros)</smarty>
<smarty>foreach $erros as $err</smarty>
    <h2 style="color:red;"><smarty>$err['mensagem']</smarty></h2>
    <smarty>/foreach</smarty>
<smarty>/if</smarty>
<div id="frame" style="width:100%; height:690px;">
<form name="frm" id="frm"  method="POST" style="margin-top:5px; padding:0px;" action="">

    <table width="100%" border="0">               
        <tr>
            <td width="116" valign="top" class="espacamento">
                <table width="100%">
                    <tr>
                        <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
                    </tr>
                </table>
            </td>
            <td colspan="2" valign="top" class="espacamento"><div id="tela" style="width:100%;"> </div></td>
        </tr>
      </table>

</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>