<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode === 'clean') {

        fn_order_statuses_change_change_history_cleanup_all_logs(Registry::get('runtime.company_id'));

        fn_set_notification('N', __('notice'), __('successful'));
    }

    return [CONTROLLER_STATUS_REDIRECT, 'order_statuses_change_history.manage'];
}

if ($mode === 'manage') {

    list($logs, $search) = fn_order_statuses_change_change_history_get_logs($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Tygh::$app['view']->assign([
        'logs'      => $logs,
        'search'    => $search,
        'log_types' => fn_get_log_types(),
    ]);
}
