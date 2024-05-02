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

use Tygh\Enum\NotificationSeverity;
use Tygh\Registry;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Executes after order status is changed, allows you to perform additional operations.
 *
 * @param int    $order_id           Order identifier
 * @param string $status_to          New order status (one char)
 * @param string $status_from        Old order status (one char)
 * @param array  $force_notification Array with notification rules
 * @param bool   $place_order        True, if this function have been called inside of fn_place_order function
 * @param array  $order_info         Order information
 * @param array  $edp_data           Downloadable products data
 */
function fn_order_statuses_change_history_change_order_status_post($order_id, $status_to, $status_from, $force_notification, $place_order, $order_info, $edp_data)
{
    if ($status_to != $status_from) {

        $log_data = [
            'order_id' => $order_id,
            'status_from' => $status_from,
            'status_to' => $status_to, 
            'user_id' => Tygh::$app['session']['auth']['user_id'],
            'timestamp' => time()
        ];
    
        db_query("INSERT INTO ?:order_statuses_change_logs ?e", $log_data);
    }
}

/**
 * Returns logs
 *
 * @param array $params         Search parameters
 * @param int   $items_per_page Items per page
 *
 * @return array Logs with search parameters
 */
function fn_order_statuses_change_change_history_get_logs($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('logs', $params);

    $default_params = [
        'page'           => 1,
        'items_per_page' => $items_per_page,
        'limit'          => 0
    ];

    $params = array_merge($default_params, $params);

    $sortings = [
        'timestamp' => ['?:order_statuses_change_logs.timestamp', '?:order_statuses_change_logs.log_id'],
        'user'      => ['?:users.lastname', '?:users.firstname'],
    ];

    $fields = [
        '?:order_statuses_change_logs.*',
        '?:users.firstname',
        '?:users.lastname'
    ];

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $join = "LEFT JOIN ?:users USING(user_id)";

    $condition = '';

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($time_from, $time_to) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:order_statuses_change_logs.timestamp >= ?i AND ?:order_statuses_change_logs.timestamp <= ?i)", $time_from, $time_to);
    }

    if (isset($params['q_user']) && fn_string_not_empty($params['q_user'])) {
        $user_names = array_values(array_filter(explode(' ', $params['q_user'])));
        $get_search_condition_user = function ($user_name) {
            return db_quote(
                ' AND (?:users.firstname LIKE ?l OR ?:users.lastname LIKE ?l)',
                "%{$user_name}%",
                "%{$user_name}%"
            );
        };
        $condition = implode([$condition, implode(array_map($get_search_condition_user, $user_names))]);
    }

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND ?:order_statuses_change_logs.company_id = ?i", Registry::get('runtime.company_id'));
    } elseif (!empty($params['company_ids'])) {
        $condition .= fn_get_company_condition('?:order_statuses_change_logs.company_id', true, $params['company_ids']);
    }

    $limit = '';

    if (!empty($params['limit'])) {
        $limit = db_quote('LIMIT 0, ?i', $params['limit']);
    } elseif (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:order_statuses_change_logs.log_id)) FROM ?:order_statuses_change_logs ?p WHERE 1 ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $data = db_get_array("SELECT " . join(', ', $fields) . " FROM ?:order_statuses_change_logs ?p WHERE 1 ?p $sorting $limit", $join, $condition);

    return [$data, $params];
}

/**
 * Cleanups all logs
 *
 * @param int|null $company_id Company identifier
 */
function fn_order_statuses_change_change_history_cleanup_all_logs($company_id = null)
{
    if ($company_id) {
        db_query('DELETE FROM ?:order_statuses_change_logs WHERE company_id = ?i', $company_id);
    } else {
        db_query('TRUNCATE TABLE ?:order_statuses_change_logs');
    }
}
