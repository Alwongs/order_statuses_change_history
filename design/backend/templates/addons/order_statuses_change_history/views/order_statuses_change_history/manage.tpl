{capture name="mainbox"}

{$order_statuses = $smarty.const.STATUSES_ORDER|fn_get_simple_statuses:true:true}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="order_statuses_change_history.manage" view_type="logs"}
    {include file="addons/order_statuses_change_history/views/order_statuses_change_history/components/logs_search_form.tpl"}
{/capture}

{include file="common/pagination.tpl"}

{if $logs}
<div class="table-responsive-wrapper">
    <table class="table table--relative table-responsive">
    <thead>
        <tr>
            <th>{__("order_id")}</th>
            <th>{__("order_statuses_change_history.old_status")}</th>
            <th>{__("order_statuses_change_history.new_status")}</th>
            <th>{__("user")}</th>
            <th>{__("date")}</th>
        </tr>
    </thead>

    <tbody>
    {foreach from=$logs item="log"}
    <tr>
        <td width="14%" class="wrap" data-th="{__("order_id")}">
            {$log.order_id}
        </td>
        <td width="15%"  data-th="{__("order_statuses_change_history.old_status")}">
            {$order_statuses[$log.status_from]}
        </td>   
        <td width="15%"  data-th="{__("order_statuses_change_history.new_status")}">
            {$order_statuses[$log.status_to]}
        </td>
        <td data-th="{__("user")}">
            <a href="{"profiles.update?user_id=`$log.user_id`"|fn_url}">{$log.lastname}{if $log.firstname || $log.lastname}&nbsp;{/if}{$log.firstname}</a>
        </td>
        <td width="15%" data-th="{__("date")}">
            <span class="nowrap">{$log.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>
        </td>
    </tr>
    {/foreach}

    </tbody>
    </table>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="logs:tools"}
            <li>{btn type="list" text=__("clean_logs") href="order_statuses_change_history.clean" class="cm-confirm" method="POST"}</li>
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=__("order_statuses_change_history.order_statuses_change_history") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
