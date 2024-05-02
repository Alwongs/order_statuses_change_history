<div class="sidebar-row">
    <h6>{__("admin_search_title")}</h6>
    <form action="{""|fn_url}" name="logs_form" method="get">
        <input type="hidden" name="object" value="{$smarty.request.object}">

        {capture name="simple_search"}
            {include file="common/period_selector.tpl" period=$search.period extra="" display="form" button="false"}
        {/capture}

        {capture name="advanced_search"}
            <div class="group form-horizontal">
                <div class="control-group">
                    <label class="control-label">{__("user")}:</label>
                    <div class="controls">
                        <input type="text" name="q_user" size="30" value="{$search.q_user}">
                    </div>
                </div>
            </div>
        {/capture}

        {include file="common/advanced_search.tpl" advanced_search=$smarty.capture.advanced_search simple_search=$smarty.capture.simple_search dispatch="order_statuses_change_history.manage" view_type="logs"}
    </form>
</div>
