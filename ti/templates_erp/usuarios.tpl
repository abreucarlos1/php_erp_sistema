<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="21%"><label for="login" class="labels">Login</label><br />
						<input name="login" type="text" class="caixa" id="login" size="50" placeholder="Login" />
						<input name="id_usuario" type="hidden" id="id_usuario" value="" />
                    </td>
					<td width="21%"><label for="senha" class="labels">Senha</label><br />
						<input name="senha" type="text" class="caixa" id="senha" size="50" placeholder="Senha" />
                    </td>
					<td width="21%"><label for="email" class="labels">Email</label><br />
						<input name="email" type="text" class="caixa" id="email" size="50" placeholder="E-mail" />
                    </td>
					<td width="29%"><label for="perfil" class="labels">Perfil</label><br />
							<select name="perfil" class="caixa" id="perfil" onkeypress="return keySort(this);">
							<option value="1">ADMINISTRADOR</option>
							<option value="2" selected="true">USUÁRIO</option>

						</select></td>

                        <td width="29%"><label for="condicao" class="labels">Condição</label><br />
                        <select name="condicao" class="caixa" id="condicao" onkeypress="return keySort(this);">
                        <option value="1" selected="true">ATIVO</option>
                        <option value="0">INATIVO</option>                   

                    </select></td>

				</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels">Pesquisar</label><br />
					<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
				</tr>
			</table>		  </td>
        </tr>
      </table>
	  <div id="usuarios" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>