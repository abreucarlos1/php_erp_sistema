<div align="center" style="width:100%;">
	<div style="width:1020px;">
		<div class="header" align="center">
        	<img align="middle" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>logo_erp.png" width="302" height="70">            
        </div>
        
        <div class="nome_formulario"><smarty>$campo[1]</smarty>&nbsp;-&nbsp;<smarty>$versao</smarty></div>
		
        <div class="nav_bar" align="right">
        	<img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="#" onclick="troca_senha('<smarty>$smarty.session.login</smarty>','<smarty>$smarty.session.id_usuario</smarty>')" class="link_1">Trocar&nbsp;senha</a><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="logout.php" class="link_1">Sair</a>            
        </div>
				
		<smarty>if isset($erros)</smarty>
		<smarty>foreach $erros as $err</smarty>
			<h2 style="color:red;"><smarty>$err['mensagem']</smarty></h2>
			<smarty>/foreach</smarty>
		<smarty>/if</smarty>