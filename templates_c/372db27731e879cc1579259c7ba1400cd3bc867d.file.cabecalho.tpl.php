<?php /* Smarty version Smarty-3.1.11, created on 2021-01-07 17:20:14
         compiled from "C:\Developer\XAMPP\htdocs\erp_sistema\templates_erp\cabecalho.tpl" */ ?>
<?php /*%%SmartyHeaderCode:20270406375ff734be88c373-06258186%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '372db27731e879cc1579259c7ba1400cd3bc867d' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\cabecalho.tpl',
      1 => 1607358226,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20270406375ff734be88c373-06258186',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ocultarCabecalhoRodape' => 0,
    'larguraTotal' => 0,
    'campo' => 0,
    'versao' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff734be8a2542_07882005',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff734be8a2542_07882005')) {function content_5ff734be8a2542_07882005($_smarty_tpl) {?><div align="center" style="width:100%;">
<?php if (!isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?>
	<div <?php if (!isset($_smarty_tpl->tpl_vars['larguraTotal']->value)){?>style="width:1020px;"<?php }else{ ?>style="width:95%;"<?php }?>>
<?php }else{ ?>
	<div style="width:100%;">
<?php }?>

		<div class="header" align="left" <?php if (isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?><?php echo $_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value;?>
<?php }?>>
        	<img align="middle" src="<?php echo @DIR_IMAGENS;?>
logo_erp.png" width="302" height="70">
        </div>
        
        <div class="nome_formulario"><?php echo $_smarty_tpl->tpl_vars['campo']->value[1];?>
 - <?php echo $_smarty_tpl->tpl_vars['versao']->value;?>
</div>
        
        <div class="nav_bar" align="right" <?php if (isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?><?php echo $_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value;?>
<?php }?>>
        	<img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png"><label class="link_1"><?php echo $_SESSION['login'];?>
</label><img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png"><a href="../inicio.php" class="link_1">Inicio</a><img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png"><a href="../logout.php" class="link_1">Sair</a>            
        </div>
        
	      <!-- Loader -->
        <div id="div_loader" class="loader" style="display:none;">
        
          <!-- loader content -->
          <div class="loader-content">
            <img src="<?php echo @DIR_IMAGENS;?>
ajax-loader.gif"/>
          </div>
        
        </div>
<?php }} ?>