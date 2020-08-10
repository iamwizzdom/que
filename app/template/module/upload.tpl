<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$header.title}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }

        .alert {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        .alert-heading {
            color: inherit;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success{
            color:#155724;
            background-color:#d4edda;
            border-color:#c3e6cb
        }
    </style>

    {foreach $css as $value}
    <link href="{$value}" rel="stylesheet" type="text/css">
    {/foreach}

</head>

<body data-gr-c-s-loaded="true">
<div class="flex-center position-ref full-height">

    <div class="content">
        <div class="title m-b-md">{$data.title}</div>

        {if isset($alert.error)}
            <div class="alert alert-danger" role="alert">

                <h3 class="alert-heading">{$alert.error.title}</h3>

                {if is_array($alert.error.message)}

                    {$count = 1}

                    {foreach $alert.error.message as $value}
                        {if $count > 1}<br>{/if}<span>{$count++}. {$value}</span>
                    {/foreach}

                {else}

                    <span>{$alert.error.message}</span>

                {/if}

            </div>
        {elseif isset($alert.success)}
            <div class="alert alert-success" role="alert">

                <h3 class="alert-heading">{$alert.success.title}</h3>

                {if is_array($alert.success.message)}

                    {$count = 1}

                    {foreach $alert.success.message as $value}
                        {if $count > 1}<br>{/if}<span>{$count++}. {$value}</span>
                    {/foreach}

                {else}

                    <span>{$alert.success.message}</span>

                {/if}

            </div>
        {/if}

        {$form->formOpen(route('file-upload'), ['method' => 'post'], true)}
            <div>
                {$form->formElement('input', '', ['type' => 'text', 'name' => 'filename', 'placeholder' => 'File name'])}
                <span>{$form['error.filename']}</span> |
                {$form->formElement('input', '', ['type' => 'file', 'name' => 'file'])}
                <span>{$form['error.file']}</span>
            </div>
            {$form->formElement('button', 'Upload', ['type' => 'submit', 'style' => 'margin-top: 20px;'])}
        {$form->formClose()}

    </div>
</div>


</body>

</html>
