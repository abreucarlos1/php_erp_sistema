<?php /* Smarty version Smarty-3.1.11, created on 2021-01-07 17:19:38
         compiled from "templates_erp\header_inicio.tpl" */ ?>
<?php /*%%SmartyHeaderCode:10707573555ff7349abbe428-98714708%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0f1f9fb21191d303396e21eec6772d87b2e0533c' => 
    array (
      0 => 'templates_erp\\header_inicio.tpl',
      1 => 1609873470,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '10707573555ff7349abbe428-98714708',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ocultarCabecalhoRodape' => 0,
    'larguraTotal' => 0,
    'campo' => 0,
    'versao' => 0,
    'erros' => 0,
    'err' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff7349ad192d8_27821973',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff7349ad192d8_27821973')) {function content_5ff7349ad192d8_27821973($_smarty_tpl) {?><!-- <div align="center" style="width:100%;">
	<div style="width:1020px;">
-->
<div align="center" style="width:100%;">
<?php if (!isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?>
	<div <?php if (!isset($_smarty_tpl->tpl_vars['larguraTotal']->value)){?>style="width:1020px;"<?php }else{ ?>style="width:95%;"<?php }?>>
<?php }else{ ?>
	<div style="width:100%;">
<?php }?>

		<div class="header" align="center">
        	<img align="middle" src="<?php echo @DIR_IMAGENS;?>
logo_erp.png" width="302" height="70">            
        </div>
        
        <div class="nome_formulario"><?php echo $_smarty_tpl->tpl_vars['campo']->value[1];?>
 - <?php echo $_smarty_tpl->tpl_vars['versao']->value;?>
</div>
		
        <div class="nav_bar" align="right">
        	<img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png" /><label class="link_1"><?php echo $_SESSION['login'];?>
</label><img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png" /><a href="#" onclick="troca_senha('<?php echo $_SESSION['login'];?>
','<?php echo $_SESSION['id_usuario'];?>
')" class="link_1">Trocar senha</a><img class="mini_seta" src="<?php echo @DIR_IMAGENS;?>
mini_seta.png" /><a href="logout.php" class="link_1">Sair</a>            
        </div>
				
		<?php if (isset($_smarty_tpl->tpl_vars['erros']->value)){?>
		<?php  $_smarty_tpl->tpl_vars['err'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['err']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['erros']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['err']->key => $_smarty_tpl->tpl_vars['err']->value){
$_smarty_tpl->tpl_vars['err']->_loop = true;
?>
			<h2 style="color:red;"><?php echo $_smarty_tpl->tpl_vars['err']->value['mensagem'];?>
</h2>
			<?php } ?>
		<?php }?><?php }} ?>