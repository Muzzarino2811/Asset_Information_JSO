<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

$route['default_controller']   = 'home/feeds';
$route['404_override']         = 'error_404';
$route['translate_uri_dashes'] = TRUE;

// Home
$route['home']   = 'home/feeds';
$route['mobile'] = 'mobile/home';

// ==================== WEB ROUTES ====================

$route['code/read/(:any)'] = 'code/read/$1';
$route['qr/scan/(:any)']   = 'qr/scan/$1';

// ==================== MOBILE QR FLOW ====================
// This is the page you open after scanning QR:
// http://localhost/jso/mobile/qr/scan/{code}
$route['mobile/qr/scan/(:any)'] = 'mobile/qrassets/scan_landing/$1';

// Existing mobile/assets routes (original implementation – leave as-is)
$route['mobile/assets/view/(:any)']         = 'mobile/assets/view/$1';
$route['mobile/assets/schedule/(:any)']     = 'mobile/assets/schedule_maintenance/$1';
$route['mobile/assets/request/(:any)']      = 'mobile/assets/submit_work_request/$1';
$route['mobile/assets/order/(:any)']        = 'mobile/assets/work_order/$1';
$route['mobile/assets/history/(:any)']      = 'mobile/assets/work_history/$1';
$route['mobile/assets/get_assets_maps']     = 'mobile/assets/get_assets_maps';

// Asset details (web)
$route['mobile/assets/asset_details/(:num)'] = 'code/asset_details/$1';

// Original web Assets controller routes
$route['asset/details/(:any)']  = 'Assets/view/$1';
$route['asset/schedule/(:any)'] = 'Assets/schedule/$1';
$route['asset/request/(:any)']  = 'Assets/request/$1';
$route['asset/order/(:any)']    = 'Assets/order/$1';
$route['asset/history/(:any)']   = 'Assets/history/$1';

// ==================== MOBILE/QRASSETS SANDBOX ====================
// These technically work without explicit routes,
// but we keep them here for clarity.
$route['mobile/qrassets/scan_landing/(:any)']                 = 'mobile/qrassets/scan_landing/$1';
$route['mobile/qrassets/submit_work_request/(:any)']          = 'mobile/qrassets/submit_work_request/$1';
$route['mobile/qrassets/save_maintenance_deliverable/(:any)'] = 'mobile/qrassets/save_maintenance_deliverable/$1';
$route['mobile/qrassets/delete_maintenance_deliverable/(:num)/(:any)']
    = 'mobile/qrassets/delete_maintenance_deliverable/$1/$2';
$route['mobile/qrassets/end_work/(:num)/(:any)']
    = 'mobile/qrassets/end_work/$1/$2';
