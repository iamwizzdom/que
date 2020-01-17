<?php
/* Smarty version 3.1.33, created on 2020-01-16 01:22:08
  from 'C:\xampp\htdocs\personal\que\system\que\error\tmp\error.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5e1facb03c1ba6_60745924',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e3a7c67da68f572136ec8f179962589cf48f7972' => 
    array (
      0 => 'C:\\xampp\\htdocs\\personal\\que\\system\\que\\error\\tmp\\error.html',
      1 => 1579129235,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5e1facb03c1ba6_60745924 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Que error page</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.42857143;
            color: #333;
            padding: 50px;
            background-color: #fff;
        }

        h2 {
            font-size: 30px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.1;
            color: inherit;
        }

        span {
            font-size: 16px;
            word-break: break-word;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }

        .alert-info hr {
            border-top-color: #a6e1ec;
        }

        hr {
            height: 0;
            -webkit-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
            margin-top: 20px;
            margin-bottom: 20px;
            border: 0;
            border-top: 1px solid #eee;
        }

        b, strong {
            font-weight: 700;
        }

        @media (min-width: 992px) {
            .col-md-offset-3 {
                margin-left: 25%;
            }

            .col-md-6 {
                float: left;
                width: 50%;
                position: relative;
                min-height: 1px;
                padding-right: 15px;
                padding-left: 15px;
            }
        }

        pre {
            display: block;
            padding: 9.5px;
            margin: 0 0 10px;
            font-size: 13px;
            line-height: 1.42857143;
            color: #333;
            word-break: break-all;
            word-wrap: break-word;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
            overflow: auto;
        }
    </style>
</head>
<body>

<div class="col-md-6 col-md-offset-3">

    <div class="alert alert-danger" role="alert">

        <div class="m-alert__text">

            <h2><?php echo $_smarty_tpl->tpl_vars['data']->value['title'];?>
</h2>

            <span><?php echo $_smarty_tpl->tpl_vars['data']->value['message'];?>
</span>

        </div>

    </div>

    <?php if ($_smarty_tpl->tpl_vars['data']->value['backtrace'] === true) {?>

    <div class="alert alert-info">
        <h3>Back Trace</h3>
        <hr>
        <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['level'])) {?><p><b>Error Level:</b> <?php echo $_smarty_tpl->tpl_vars['data']->value['level'];?>
</p><?php }?>
        <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['file'])) {?><p><b>Error File:</b> <?php echo $_smarty_tpl->tpl_vars['data']->value['file'];?>
</p><?php }?>
        <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['line'])) {?><p><b>Error Line:</b> <?php echo $_smarty_tpl->tpl_vars['data']->value['line'];?>
</p><?php }?>
        <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['context'])) {?><p><b>Error Context:</b> <?php echo debug_print($_smarty_tpl->tpl_vars['data']->value['context']);?>
</p><?php }?>
    </div>

    <?php }?>
</div>

</body>
</html><?php }
}
