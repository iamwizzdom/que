{if isset($alert.error)}
    <div class="m-alert m-alert--icon alert alert-danger" role="alert">

        <div class="m-alert__icon">
            <i class="fa fa-warning"></i>
        </div>

        <div class="m-alert__text">

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

        {if isset($alert.error.button)}
            <div class="m-alert__actions">
                <a href="{$alert.error.button.url}" class="btn btn-sm btn-default btn-rounded" data-override="true"
                        {if isset($alert.error.button.option) && $alert.error.button.option == ALERT_BUTTON_OPTION_NEW_TAB}
                            target="_blank"
                        {elseif isset($alert.error.button.option) && $alert.error.button.option == ALERT_BUTTON_OPTION_POP_UP}
                        {literal}
                            onclick="window.open(this.href, '', 'resizable=yes,status=no,location=no,toolbar=no,menubar=no,fullscreen=no,scrollbars=no,dependent=no'); return false;"
                        {/literal}
                        {/if}
                >
                    {$alert.error.button.title}
                </a>
            </div>
        {/if}

    </div>
{elseif isset($alert.warning)}
    <div class="m-alert m-alert--icon alert alert-warning" role="alert">

        <div class="m-alert__icon">
            <i class="fa fa-warning"></i>
        </div>

        <div class="m-alert__text">

            <h3 class="alert-heading">{$alert.warning.title}</h3>

            {if is_array($alert.warning.message)}

                {$count = 1}

                {foreach $alert.warning.message as $value}
                    {if $count > 1}<br>{/if}<span>{$count++}. {$value}</span>
                {/foreach}

            {else}
                <span>{$alert.warning.message}</span>
            {/if}

        </div>

        {if isset($alert.warning.button)}
            <div class="m-alert__actions">
                <a href="{$alert.warning.button.url}" class="btn btn-sm btn-default btn-rounded" data-override="true"
                        {if isset($alert.warning.button.option) && $alert.warning.button.option == ALERT_BUTTON_OPTION_NEW_TAB}
                            target="_blank"
                        {elseif isset($alert.warning.button.option) && $alert.warning.button.option == ALERT_BUTTON_OPTION_POP_UP}
                        {literal}
                            onclick="window.open(this.href, '', 'resizable=yes,status=no,location=no,toolbar=no,menubar=no,fullscreen=no,scrollbars=no,dependent=no'); return false;"
                        {/literal}
                        {/if}
                >
                    {$alert.warning.button.title}
                </a>
            </div>
        {/if}

    </div>
{elseif isset($alert.success)}
    <div class="m-alert m-alert--icon alert alert-success" role="alert">

        <div class="m-alert__icon">
            <i class="fa fa-check"></i>
        </div>

        <div class="m-alert__text">

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

        {if isset($alert.success.button)}
            <div class="m-alert__actions">
                <a href="{$alert.success.button.url}" class="btn btn-sm btn-default btn-rounded" data-override="true"
                        {if isset($alert.success.button.option) && $alert.success.button.option == ALERT_BUTTON_OPTION_NEW_TAB}
                            target="_blank"
                        {elseif isset($alert.success.button.option) && $alert.success.button.option == ALERT_BUTTON_OPTION_POP_UP}
                        {literal}
                            onclick="window.open(this.href, '', 'resizable=yes,status=no,location=no,toolbar=no,menubar=no,fullscreen=no,scrollbars=no,dependent=no'); return false;"
                        {/literal}
                        {/if}
                >
                    {$alert.success.button.title}
                </a>
            </div>
        {/if}

    </div>
{/if}