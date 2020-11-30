<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
       					<input name="btninserir" type="button" class="class_botao" id="btninserir" onClick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" />					</td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnimportar" <smarty>$autorizado</smarty> type="button" class="class_botao" id="btnimportar" onclick="location.href='<smarty>$smarty.server.PHP_SELF</smarty>?acao=exportar&type=outlook&codempresa='+document.getElementById('empresa').value+'';" value="Outlook" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
		    	</tr>
   			</table></td>
          <td colspan="2" valign="top" class="espacamento">
		  <div id="my_tabbar" style="height:400px;"></div>
		  	<div id="a0">
			  <table border="0" width="100%">
					<tr>
						<td width="15%"><label for="empresa" class="labels">Empresa</label><br />
							<select name="empresa" class="caixa" id="empresa" onchange="xajax_atualizatabela(this.value,'empresa');" onkeypress="return keySort(this);">
							<option value="">Selecione...</option>
							<smarty>while $options = mysqli_fetch_assoc($res_empresa) </smarty>
							<option value="<smarty>$options['id_empresa_erp']</smarty>"><smarty>$options["Empresa"]</smarty><smarty>$options["Unidade"]</smarty></option>
							<smarty>/while</smarty>
							</select>					</td>
						<td width="85%"><input name="id_contato" id="id_contato" type="hidden" value="" /></td>
					</tr>				
				</table>
          	  <table border="0" width="100%">
				<tr>
					<td width="51%"><label for="contato" class="labels">Contato</label><br />
						<input name="contato" type="text" class="caixa" placeholder="Nome" id="contato" size="55" /></td>
					<td width="49%"><label for="situacao" class="labels">Situaçao</label><br />
						<select name="situacao" class="caixa" id="situacao" onkeypress="return keySort(this);">
							<option value="0">INATIVO</option>
							<option value="1" selected="selected">ATIVO</option>
						</select>
                     </td>
				</tr>
			</table>            
          	  <table border="0" width="100%">
				<tr>
					<td width="14%"><label for="telefone" class="labels">Telefone</label><br />
						<input name="telefone" type="text" placeholder="Telefone" class="caixa" id="telefone" onKeyPress="return txtBoxFormat(document.frm, 'telefone', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
					<td width="14%"><label for="celular"  class="labels">Celular</label><br />
							<input name="celular" type="text" placeholder="Celular" class="caixa" id="celular" onkeypress="return txtBoxFormat(document.frm, 'celular', '(99) 9999-9999', event);" size="15" maxlength="14"/>					</td>
					<td width="72%"><label for="fax_contato" class="labels">Fax</label><br />
						<input name="fax_contato" type="text" class="caixa" id="fax_contato" onkeypress="return txtBoxFormat(document.frm, 'fax_contato', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
				</tr>
			</table>
            
          	  <table border="0" width="100%">
				<tr>
					<td width="28%"><label for="cargo" class="labels">Cargo</label><br />
							<input name="cargo" type="text" placeholder="Cargo" class="caixa" id="cargo" size="30" />
					</td>
					<td width="28%"><label for="departamento" class="labels">Departamento</label><br />
                    		<input name="departamento" type="text" placeholder="Departamento" class="caixa" id="departamento" size="30" />
					</td>
					<td width="44%"><label for="decisao" class="labels">Decisão</label><br />
							<select name="decisao" class="caixa" id="decisao" onkeypress="return keySort(this);">
								<option value="0" selected="selected">NÃO</option>
								<option value="1" >SIM</option>
							</select>
					</td>
				</tr>
			</table>
          	  <table border="0" width="100%">
				<tr>
					<td width="47%"><label for="email" class="labels">E-mail</label><br />
						<input name="email" type="text" placeholder="Email" class="caixa" id="email" size="50"/></td>
					<td width="53%"><label for="senha" class="labels">Senha</label><br />
				    <input name="senha" type="password" placeholder="Senha" class="caixa" id="senha" size="20"/></td>
				</tr>
			</table>
          	  <table border="0" width="100%">
			  <tr>
					<td width="7%"><label class="labels">Busca</label><br />
                    	<input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" />
                    </td>
				</tr>
			</table>
			</div>
			 <div id="a1">
			<table border="0" width="100%">
				<tr>
					<td width="51%"><label for="data_nascimento" class="labels">Data&nbsp;nascimento</label><br />
						<input name="data_nascimento" type="text" class="caixa" id="data_nascimento" onKeyPress="return txtBoxFormat(document.frm, 'data_nascimento', '99/99/9999', event);" maxlength="10" size="12"/></td>
				</tr>
				<tr>
					<td width="51%"><label for="nome_secretaria" class="labels">Nome&nbsp;secretaria</label><br />
						<input name="nome_secretaria" type="text" class="caixa" id="nome_secretaria" size="55" />
					</td>
					<td width="49%"><label for="telefone_secretaria" class="labels">Telefone&nbsp;secretaria</label><br />
						<input name="telefone_secretaria" type="text" class="caixa" id="telefone_secretaria" onKeyPress="return txtBoxFormat(document.frm, 'telefone_secretaria', '(99) 9999-9999', event);" size="12" maxlength="14"/>
					</td>
				</tr>
                
			</table>
			</div>
		  
		 </td>
        </tr>
      </table>
      <br />
	  <div id="contatos" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>