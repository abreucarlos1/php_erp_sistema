<?php /* Smarty version Smarty-3.1.11, created on 2021-03-29 16:00:07
         compiled from "templates_erp\footer_root.tpl" */ ?>
<?php /*%%SmartyHeaderCode:15617537246061dd67f16635-27657646%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5035773cb22b29ece9a992251ba2bcdbc1b0db52' => 
    array (
      0 => 'templates_erp\\footer_root.tpl',
      1 => 1605009162,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15617537246061dd67f16635-27657646',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ocultarCabecalhoRodape' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_6061dd6800fee9_06318028',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_6061dd6800fee9_06318028')) {function content_6061dd6800fee9_06318028($_smarty_tpl) {?>        
            <div class="rodape" id="tabelaRodapeDvmsys" align="right"  <?php if (isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?><?php echo $_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value;?>
<?php }?>>
                <img src="<?php echo @DIR_IMAGENS;?>
logo_rod.png">            
            </div>        
		</div>
	</div>
</BODY>
</HTML>
<?php }} ?>