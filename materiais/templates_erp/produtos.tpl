<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery.maskedinput/src/jquery.maskedinput.js"></script>
<script src="../includes/elevatezoom-master/jquery.elevatezoom.js"></script>
<script src="../includes/validacao.js"></script>
<script src="../includes/dhtmlx_403/codebase/dhtmlx.js"></script>
<script src="../js/materiais/scripts.js"></script>

<style type"text/css">
	#gallery_01 img{border:2px solid white;}
	.active img{border:2px solid #333 !important;}
</style>

<smarty>if isset($mensagem_erro)</smarty><h3 style="color: red;text-align:center;"><smarty>$mensagem_erro</smarty></h3><smarty>/if</smarty>

<div style="height: 660px;">
<form name="frm_principal" id="frm_principal" action="<smarty>$smarty.server.PHP_SELF</smarty>?insere=1" method="POST" style="margin:0px; padding:0px;" enctype="multipart/form-data">
	<input type="hidden" id="id_produto" name="id_produto" value="<smarty>$_POST['id_produto']</smarty>" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0"> 
        <tr>
          <td width="116" valign="top" class="td_sp">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnexcluir" id="btnexcluir" type="button" class="class_botao" value="Excluir" disabled="disabled" onclick="if(confirm('Deseja excluir este item?')){xajax_excluir(document.getElementById('id_produto').value)};" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnbuscar" id="btnbuscar" type="button" class="class_botao" value="Buscar" onclick="showModalBuscar();" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table>
			</td>
			<td valign="top" height="70px">
				<table width="600px">
					<tr>
					  <td width="190px" valign="top" rowspan="3" class="td_sp">
					  	<label class="labels" style="float:left">Código</label>
					  	<input type="text" style="float:left" class="caixa codBarras" id="codigoComponente" name="codigoComponente" value="<smarty>$_POST['codigoComponente']</smarty>" onblur="preencheTela();" />
					  	<span class="icone icone-inserir" id="imgSelecionarComponentes" style="cursor:pointer" title="Selecionar componentes"></span>
					  </td>
					</tr>
					<tr>
					  <td>
					  	<label class="labels" style="float:left;width:80px;">Grupo</label>
					  	<input type="text" class="caixa" size="35" id="nomeGrupo" name="nomeGrupo" value="<smarty>$_POST['nomeGrupo']</smarty>" />
					  </td>
					  <td>
					  	<label class="labels" style="float:left">Unidade 1</label><br />
					  	<input type="text" style="float:left" class="caixa" size="10" id="unidade1" name="unidade1" value="<smarty>$_POST['unidade1']</smarty>" />
					  	<span class="icone icone-inserir selecionarUnidade" ref="unidade1" style="cursor:pointer" title="Selecionar unidade"></span>
					  </td>
					</tr>
					<tr> 
					  <td>
					  	<label class="labels" style="float:left;width:80px;">Sub Grupo</label>
					  	<input type="text" class="caixa" size="35" id="nomeSubGrupo" name="nomeSubGrupo" value="<smarty>$_POST['nomeSubGrupo']</smarty>" />
					  </td>
					  <td width="5%">
					  	<label class="labels">Peso 1</label><br />
					  	<input type="text" class="caixa" size="15" id="peso1" name="peso1" value="<smarty>$_POST['peso1']</smarty>" />
					  </td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" valign="top" width="750px">
				<table width="100%" border="0" height="100%">
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Resumida Português <sub>*Este campo não pode ser alterado</sub></label>
				  	<textarea class="caixa" id="descResPort" name="descResPort" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descResPort']</smarty></textarea>
				  </td>
				</tr>
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Resumida Inglês</label>
				  	<textarea class="caixa" id="descResIngles" name="descResIngles" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descResIngles']</smarty></textarea>
				  </td>
				</tr>
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Resumida Espanhol</label>
				  	<textarea class="caixa" id="descResEspanhol" name="descResEspanhol" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descResEspanhol']</smarty></textarea>
				  </td>
				</tr>
				
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Longa Português</label>
				  	<textarea class="caixa" id="descLongaPort" name="descLongaPort" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descLongaPort']</smarty></textarea>
				  </td>
				</tr>
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Longa Inglês</label>
				  	<textarea class="caixa" id="descLongaIngles" name="descLongaIngles" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descLongaIngles']</smarty></textarea>
				  </td>
				</tr>
				<tr>
				  <td width="5%">
				  	<label class="labels">Descrição Longa Espanhol</label>
				  	<textarea class="caixa" id="descLongaEspanhol" name="descLongaEspanhol" style="height:32px;width:100%;" cols="120"><smarty>$_POST['descLongaEspanhol']</smarty></textarea>
				  </td>
				</tr>
				<!-- <tr><td> </td></tr> -->
				<tr>
					<td>
						<label class="labels" style="float:left">Selecionar Fornecedor</label>
						<span class="icone icone-inserir cursor lista_fornecedores" title="Selecionar Fornecedor"></span>
					</td>
				</tr>
				<tr>
					<td colspan="2"><div id="div_fornecedor"></div><div id="lista_fornecedores_selecionados"></div></td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>