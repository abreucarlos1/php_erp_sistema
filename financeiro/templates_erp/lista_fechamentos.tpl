<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="listafechamentos" method="post" action="<smarty>$PHP_SELF</smarty>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td>
			<div id="tbheader" style="position:relative; width:100%; height:18px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="52%"><a href="#" class="cabecalho_tabela" onclick="ordenar('funcionario','<smarty>$ordem</smarty>')">FECHAMENTO</a></td>
				  <td width="43%">LIBERADO</td>
				  <td width="5%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border="0" id="tabelaLista">
				<smarty>$htmlLista</smarty>
			  </table>
			</div>
		</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
            <tr>
              <td width="55%"><input type="hidden" name="acao" value="alterar"><input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                <input name="Voltar" type="submit" class="btn" id="Voltar" value="Voltar" onclick="window.close()"></td>
              <td width="45%"> </td>
            </tr>
          </table></td>
      </tr>
    </table>
	</td>
  </tr>
</table>
</form>
</center>
</body>