{if isset($header.http)}

    {if is_array($header.http) && count($header.http) > 0}

        {foreach $header.http as $value}

            {if is_array($value) && array_key_exists('message', $value) && array_key_exists('status', $value)}

                {if $value.status == ERROR}
                    <div class="alert alert-danger alert-dismissible text-center fade show" role="alert">
                        <span>{$value.message}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {elseif $value.status == WARNING}
                    <div class="alert alert-warning alert-dismissible text-center fade show" role="alert">
                        <span>{$value.message}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {elseif $value.status == SUCCESS}
                    <div class="alert alert-success alert-dismissible text-center fade show" role="alert">
                        <span>{$value.message}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}

            {/if}

        {/foreach}

    {/if}

{/if}