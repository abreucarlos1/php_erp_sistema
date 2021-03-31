<smarty>include file="html_conf.tpl"</smarty>
<smarty>include file="header_index.tpl"</smarty>
<form name="frm_login" id="frm_login" method="POST" action="<smarty>$smarty.server.PHP_SELF</smarty>">
    <input type="hidden" name="pagina" id="pagina" value="<smarty>$pagina</smarty>">
    <div class="fieldset">
        <label for="login" class="labels">Usuário</label><br />
        <input name="login" id="login" class="caixa" style="text-transform:none;" type="text" placeholder="login" value="<smarty>$user</smarty>" size="40"/><br />
        <label for="senha" class="labels">Senha</label><br />
        <input name="senha" id="senha" type="password" class="caixa" style="text-transform:none;" placeholder="Senha" onkeypress="if(event.keyCode==13){xajax_autenticacao(xajax.getFormValues('frm_login'));}" size="40" /><br />
        <div onclick="esqueceusenha()"><label class="esq_senha">Esqueci minha senha</label></div><br />
        <button type="button" autofocus class="class_botao" onclick="xajax_autenticacao(xajax.getFormValues('frm_login'));">Entrar</button><br />
        <div class="alerta_erro" id="mensagem"> </div>
    </div>        
</form>
<smarty>include file="footer_root.tpl"</smarty>
