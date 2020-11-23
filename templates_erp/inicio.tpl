<smarty>include file="html_conf.tpl"</smarty>
<smarty>include file="header_inicio.tpl"</smarty>
<form name="frm_tela" id="frm_tela" style="margin-top:5px; padding:0px;" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
    <input type="hidden" name="preenchido" id="preenchido" value="<smarty>$preenchido</smarty>" />
    <div id="frame" style="width:100%; margin:0px; padding:0px;">&nbsp;</div>    
</form>
<smarty>include file="footer_root.tpl"</smarty>
