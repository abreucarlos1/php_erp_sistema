<div align="center" style="width:100%;">
<smarty>if !isset($ocultarCabecalhoRodape)</smarty>
	<div <smarty>if !isset($larguraTotal)</smarty>style="width:1020px;"<smarty>else</smarty>style="width:95%;"<smarty>/if</smarty>>
<smarty>else</smarty>
	<div style="width:100%;">
<smarty>/if</smarty>

		<div class="header" align="left" <smarty>if isset($ocultarCabecalhoRodape)</smarty><smarty>$ocultarCabecalhoRodape</smarty><smarty>/if</smarty>>
        	<img align="middle" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>logo_erp.png" width="302" height="70">
        </div>
        
        <div class="nome_formulario"><smarty>$campo[1]</smarty>&nbsp;-&nbsp;<smarty>$versao</smarty></div>
        
        <div class="nav_bar" align="right" <smarty>if isset($ocultarCabecalhoRodape)</smarty><smarty>$ocultarCabecalhoRodape</smarty><smarty>/if</smarty>>
        	<img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><a href="../inicio.php" class="link_1">Inicio</a><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png"><a href="../logout.php" class="link_1">Sair</a>            
        </div>
        
	      <!-- Loader -->
        <div id="div_loader" class="loader" style="display:none;">
        
          <!-- loader content -->
          <div class="loader-content">
            <img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>ajax-loader.gif"/>
          </div>
        
        </div>
