{function name=class_name status=""}
    {if $status == -1} has-error {elseif $status == 0} has-warning {elseif $status == 1} has-success {/if}
{/function}