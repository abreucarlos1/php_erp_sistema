<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:auto">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
    <div id="fechamentos"> </div>
    <input name="btninserir"  tabindex="4" type="button" class="class_botao" id="btninserir" onclick="xajax_alterar(xajax.getFormValues('frm'))" value="Liberar" />
    <input name="btnvoltar" tabindex="15" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="window.close();" />
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>