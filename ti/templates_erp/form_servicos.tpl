<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<body>
	<form id="frmCadastro" name="frmCadastro">
	<div class="auto_form">
		<smarty>foreach $tabela as $reg</smarty>
			<smarty>if $reg['campoForm'] != ''</smarty>
				<smarty>$reg['campoForm']</smarty>
    		<smarty>/if</smarty>
	  	<smarty>/foreach</smarty>
	  	</div>		  
	</form>
</body>