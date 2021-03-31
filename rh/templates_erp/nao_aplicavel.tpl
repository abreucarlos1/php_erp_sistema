<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm">
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table>
						<tr>
							<td><label for="busca" class='labels'>Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="45" placeholder="Busca" />
							<br />
							<sub><i>Digite parte do nome ou e-mail para refinar a busca</i></sub>
						</tr>
					</table>
				</td>
			</tr>
		</table><br />
		<div id="divLista" style="width:100%"> </div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>