<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Qrassets
 *
 * Clone controller for QR mobile asset page
 * to avoid touching original Assets controller.
 *
 * @property Template_mobile    $template_mobile
 * @property General_model      $general
 * @property Encryptlib         $encryptlib
 * @property Datatable          $datatable
 * @property Formlib            $formlib
 * @property Microsoft_crm      $microsoft_crm
 * @property Microsoft_ax       $microsoft_ax
 * @property Assetslib          $assetslib
 * @property Dashboardlib       $dashboardlib
 */
class Qrassets extends MY_Controller
{
    const TAB_DASHBOARD = "dashboard";
    const TAB_DATA      = "data";

    public function __construct()
    {
        parent::__construct();
        $this->load->library("template_mobile");
        $this->load->library("microsoft_crm");
        $this->load->library("microsoft_ax");
        $this->load->library("dashboardlib");
        $this->load->library("assetslib");
        $this->library_support();

        // Make sure _user exists before touching properties (because Qrassets skips auth)
        if (is_object($this->_user) && !empty($this->_user->modules_mobile)) {
            $modules = $this->utils->search_array(
                $this->_user->modules_mobile,
                "name",
                "module-assets",
                false,
                true
            );
            $modules = !empty($modules["data"]) ? $modules["data"] : array();
        } else {
            $modules = array();
        }

        // Header icon (kept same as original)
        if (!empty($modules["icon_images"])) {
            $this->template_mobile->_title_header =
                "<a href='" . base_url("mobile/assets") . "'>" .
                "<img src='" . $modules["icon_images"] . "' alt='logo' class='logo ml-10'>" .
                " <span class='mt- ml-10 text-black'>ASSET MANAGEMENT</span></a>";
        }

        // Set default tab and load submenu
        $this->template_mobile->data["tab"]     =
            !empty($this->input->get("tab")) ? $this->input->get("tab") : "dashboard";
        $this->template_mobile->data["submenu"] =
            $this->load->view("mobile/assets/submenu", $this->template_mobile->data, true);
    }

    // ===================== ASSET INFORMATION =====================

    // Old "view" – probably not used by QR flow, but kept for completeness
    public function view($asset_id)
    {
        $asset = $this->general->get_data("assets_table", array('id' => $asset_id))->row();

        if ($asset) {
            $data['asset']          = $asset;
            $data['qr_value']       = $asset->id_barcode;
            $data['asset_managers'] =
                $this->general->get_data('users', array('role' => 'asset_manager'))->result();

            $this->load->view('mobile/assets/detail', $data);
        } else {
            show_404();
        }
    }

    /**
     * Landing page after QR scan
     * Route: mobile/qr/scan/{qr_value} → Qrassets::scan_landing
     */
    public function scan_landing($qr_value)
    {
        // 1) Find mapping in mst_code by "code" = QR value
        $asset_code = $this->general->get_data("mst_code", array(
            "code" => array(
                SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
                SQL_CONDITION_VALUE    => $qr_value
            )
        ))->row();

        if (!$asset_code) {
            show_404();
        }

        // 2) Find the asset in __dynamics_assets by id from mst_code
        $asset = $this->general->get_data("__dynamics_assets", array(
            "id" => array(
                SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
                SQL_CONDITION_VALUE    => $asset_code->id
            )
        ))->row();

        if (!$asset) {
            show_404();
        }

        // 3) Load related data using asset->new_id as asset_no
        $this->load->database();

        // ✅ table: work_requests
        $work_orders = $this->db
            ->get_where('work_requests', array('asset_no' => $asset->new_id))
            ->result();

        $maintenance_deliverables = $this->db
            ->get_where('maintenance_deliverable', array('asset_no' => $asset->new_id))
            ->result();

        $work_history = $this->db
            ->get_where('work_history', array('asset_no' => $asset->new_id))
            ->result();

        $schedule_maintenance = $this->db
            ->get_where('schedule_maintenance', array('asset_no' => $asset->new_id))
            ->result();

        $data['qr_value']                 = $qr_value;
        $data['asset']                    = $asset;
        $data['work_orders']              = $work_orders;
        $data['maintenance_deliverables'] = $maintenance_deliverables;
        $data['work_history']             = $work_history;
        $data['schedule_maintenance']     = $schedule_maintenance;

        $this->load->view('mobile/assets/qr_scan_landing', $data);
    }

    // ===================== SCHEDULE (UPLOAD FROM BIG WEB) =====================

    // Not used in mobile QR flow, but here for completeness
    public function upload_schedule($asset_no)
    {
        $schedule_date           = $this->input->post('schedule_date');
        $maintenance_description = $this->input->post('maintenance_description');

        if (empty($schedule_date) || empty($maintenance_description)) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('qrassets/schedule_maintenance/' . $asset_no);
        }

        $schedule_data = array(
            'asset_no'         => $asset_no,
            'maintenance_date' => $schedule_date,
            'description'      => $maintenance_description,
            'status'           => 'scheduled',
        );

        $this->general->insert_data('schedule_maintenance', $schedule_data);

        redirect('qrassets/schedule_maintenance/' . $asset_no);
    }

    public function schedule_maintenance($asset_no)
    {
        $schedule_maintenance = $this->general->get_data('schedule_maintenance', array(
            'asset_no'            => $asset_no,
            'status'              => 'scheduled',
            'maintenance_date >=' => date('Y-m-d')
        ))->result();

        $data['schedule_maintenance'] = $schedule_maintenance;
        $this->load->view('mobile/assets/schedule_maintenance', $data);
    }

    // ===================== WORK REQUEST =====================

/**
 * Create or update a work request (Preventive / Breakdown)
 * URL: mobile/qrassets/submit_work_request/{qr_value}
 * Uses asset_no from form (asset->new_id)
 */
public function submit_work_request($qr_value)
{
    $this->load->database();

    $id                  = $this->input->post('id');  // null for new, value for edit
    $asset_no            = $this->input->post('asset_no');  // __dynamics_assets.new_id
    $no_wr               = $this->input->post('no_wr');
    $request_reason      = $this->input->post('request_reason');
    $pelaksana           = $this->input->post('pelaksana');
    $request_date        = $this->input->post('request_date');      // Y-m-d
    $maintenance_date    = $this->input->post('maintenance_date');  // Y-m-d
    $request_description = $this->input->post('request_description');
    $pic                 = $this->input->post('pic');

    // If editing, load existing row (for status & attachment)
    $existing = null;
    if (!empty($id)) {
        $existing = $this->db->get_where('work_requests', ['id' => $id])->row();
        if (!$existing) {
            // If somehow ID is invalid, treat as new
            $id = null;
        }
    }

    // Optional attachment
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $config = array(
            'upload_path'   => './uploads/work_request/',
            'allowed_types' => 'jpg|jpeg|png|pdf',
            'max_size'      => 4096,
        );
        $this->load->library('upload');
        $this->upload->initialize($config);

        if ($this->upload->do_upload('attachment')) {
            $upload_data = $this->upload->data();
            $attachment  = 'uploads/work_request/' . $upload_data['file_name'];
        } else {
            // log error if upload fails
            log_message('error', 'Work Request attachment upload error: ' . $this->upload->display_errors('', ''));
        }
    }

    // Base row data
    $row = array(
        'asset_no'            => $asset_no,
        'no_wr'               => $no_wr,
        'request_reason'      => $request_reason,
        'pelaksana'           => $pelaksana,
        'request_datetime'    => $request_date,      // you can append time if needed
        'maintenance_date'    => $maintenance_date,
        'request_description' => $request_description,
        'pic'                 => $pic,
        // New WR = Pending; Edit WR keeps existing status
        'status'              => $existing ? $existing->status : 'Pending',
    );

    // Attachment: only override when new file uploaded, otherwise keep existing
    if ($attachment !== null) {
        $row['attachment'] = $attachment;
    } elseif ($existing && !empty($existing->attachment)) {
        $row['attachment'] = $existing->attachment;
    } else {
        $row['attachment'] = null;
    }

    // Insert or update
    if (!empty($id) && $existing) {
        $this->db->where('id', $id);
        $this->db->update('work_requests', $row);
    } else {
        $this->db->insert('work_requests', $row);
    }

    // Back to the same QR page
    redirect('mobile/qr/scan/' . $qr_value);
}


    // ===================== MAINTENANCE DELIVERABLE =====================

/**
 * PIC / Worker save or update maintenance deliverable
 * URL: mobile/qrassets/save_maintenance_deliverable/{qr_value}
 */
public function save_maintenance_deliverable($qr_value)
{
    $this->load->database();

    $id                  = $this->input->post('id'); // for edit
    $asset_no            = $this->input->post('asset_no');   // __dynamics_assets.new_id
    $work_request_id     = $this->input->post('work_request_id');
    $no_maintenance      = $this->input->post('no_maintenance');
    $request_reason      = $this->input->post('request_reason');
    $request_description = $this->input->post('request_description');
    $start_date          = $this->input->post('start_date');
    $end_date            = $this->input->post('end_date');
    $status              = $this->input->post('maintenance_status');

    // Pull related WR info if provided
    $wr = null;
    if (!empty($work_request_id)) {
        // correct table name
        $wr = $this->db->get_where('work_requests', array('id' => $work_request_id))->row();
    }

    // Evidence attachment upload
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $config = array(
            'upload_path'   => './uploads/maintenance/',
            'allowed_types' => 'jpg|jpeg|png|pdf',
            'max_size'      => 4096,
        );

        $this->load->library('upload');
        $this->upload->initialize($config);

        if ($this->upload->do_upload('attachment')) {
            $upload_data = $this->upload->data();
            $attachment  = 'uploads/maintenance/' . $upload_data['file_name'];
        } else {
            // Log error so you can see why upload failed if it does
            log_message('error', 'Maintenance attachment upload error: ' . $this->upload->display_errors('', ''));
        }
    }

    // Base data to save/update
    $data = array(
        'asset_no'        => $asset_no,
        'work_request_id' => $work_request_id,
        'no_maintenance'  => $no_maintenance,
        'no_wr'           => $wr ? $wr->no_wr : null,
        'request_reason'  => $request_reason ?: ($wr->request_reason ?? null),
        'description'     => $request_description,
        'start_date'      => $start_date,
        'end_date'        => $end_date,
        'status'          => $status ?: 'On Process',
    );

    // --- IMPORTANT PART: keep / set attachment correctly ---
    if ($attachment !== null) {
        // New file just uploaded
        $data['attachment'] = $attachment;
    } elseif (!empty($id)) {
        // Edit mode and no new upload: keep existing attachment from DB
        $existingMd = $this->db
            ->get_where('maintenance_deliverable', ['id' => $id])
            ->row();

        if ($existingMd && !empty($existingMd->attachment)) {
            $data['attachment'] = $existingMd->attachment;
        }
    }
    // --- END attachment handling ---

    // Insert or update main table
if (!empty($id)) {
    $this->db->where('id', $id);
    $this->db->update('maintenance_deliverable', $data);
} else {
    $this->db->insert('maintenance_deliverable', $data);
    $id = $this->db->insert_id();
}

// ✅ Work History rule:
// - If status = Completed  -> upsert into work_history
// - If status != Completed -> delete existing row from work_history
if (!empty($status) && strtolower($status) === 'completed') {
    // create / update work_history
    $this->_sync_work_history($asset_no, $id, $wr, $data);
} else {
    // remove any history linked to this maintenance
    $this->db->delete('work_history', ['maintenance_id' => $id]);
}

redirect('mobile/qr/scan/' . $qr_value);

}


    /**
     * Delete maintenance deliverable + related history
     * URL: mobile/qrassets/delete_maintenance_deliverable/{id}/{qr_value}
     */
    public function delete_maintenance_deliverable($id, $qr_value)
    {
        $this->load->database();

        $this->db->delete('maintenance_deliverable', array('id' => $id));
        $this->db->delete('work_history', array('maintenance_id' => $id));

        redirect('mobile/qr/scan/' . $qr_value);
    }

    // ===================== WORK HISTORY =====================

/**
 * Internal helper to upsert work_history row
 * $asset_no = __dynamics_assets.new_id
 */
private function _sync_work_history($asset_no, $maintenance_id, $wr, $md)
{
    $this->load->database();

    $existing = $this->db
        ->get_where('work_history', array('maintenance_id' => $maintenance_id))
        ->row();

    $row = array(
        'asset_no'        => $asset_no,
        'maintenance_id'  => $maintenance_id,
        'work_request_id' => $wr ? $wr->id : null,
        'no_wr'           => $wr ? $wr->no_wr : null,
        'no_maintenance'  => $md['no_maintenance'],
        'request_date'    => $wr ? $wr->request_datetime : null,
        'maintenance_date'=> !empty($md['end_date']) ? $md['end_date'] : $md['start_date'],
        'description'     => $md['description'],
        'status'          => 'Completed',
    );

    // Evidence sync: only override if we actually have an attachment
    if (!empty($md['attachment'])) {
        $row['evidence'] = $md['attachment'];
    } elseif ($existing && !empty($existing->evidence)) {
        // keep old evidence if updating and no new attachment provided
        $row['evidence'] = $existing->evidence;
    } else {
        $row['evidence'] = null;
    }

    if ($existing) {
        $this->db->where('id', $existing->id);
        $this->db->update('work_history', $row);
    } else {
        $this->db->insert('work_history', $row);
    }
}


    // Optional separate page (not used by QR view)
    public function work_history($asset_no)
    {
        $this->load->database();

        $work_history = $this->db
            ->get_where('work_history', array('asset_no' => $asset_no))
            ->result();

        $data['work_history'] = $work_history;

        $this->load->view('mobile/assets/work_history', $data);
    }

    /**
 * Optional explicit "end_work" endpoint
 * URL: mobile/qrassets/end_work/{maintenance_id}/{qr_value}
 */
public function end_work($maintenance_id, $qr_value)
{
    $this->load->database();

    $evidence = $this->input->post('evidence');

    $md = $this->db->get_where('maintenance_deliverable', array('id' => $maintenance_id))->row();
    if (!$md) {
        show_404();
    }

    // Update main maintenance status + attachment (if provided)
    $update = array('status' => 'Completed');
    if (!empty($evidence)) {
        $update['attachment'] = $evidence;
    }

    $this->db->where('id', $maintenance_id);
    $this->db->update('maintenance_deliverable', $update);

    // Reload MD after update so we have fresh attachment if needed
    $md = $this->db->get_where('maintenance_deliverable', array('id' => $maintenance_id))->row();

    $wr = null;
    if (!empty($md->work_request_id)) {
        $wr = $this->db->get_where('work_requests', array('id' => $md->work_request_id))->row();
    }

    $md_arr = array(
        'no_maintenance' => $md->no_maintenance,
        'description'    => $md->description,
        'start_date'     => $md->start_date,
        'end_date'       => $md->end_date,
        'attachment'     => !empty($evidence) ? $evidence : $md->attachment,
    );

    $this->_sync_work_history($md->asset_no, $maintenance_id, $wr, $md_arr);

    redirect('mobile/qr/scan/' . $qr_value);
}


    // ===================== WORK ORDER (not essential for QR page) =====================

    public function work_order($asset_no)
    {
        $this->load->database();

        $user_id = $this->session->userdata('user_id');

        // ✅ correct table name
        $work_orders = $this->db->get_where('work_requests', array(
            'asset_no'         => $asset_no,
            'asset_manager_id' => $user_id,
        ))->result();

        if (empty($work_orders)) {
            show_error('You are not authorized to view these work orders.', 403);
        }

        $data['work_orders'] = $work_orders;
        $this->load->view('mobile/assets/work_order', $data);
    }

    // ===================== MAPS (unchanged from original) =====================

    public function get_assets_maps()
    {
        if ($this->input->is_ajax_request() && ($_SERVER["REQUEST_METHOD"] === "GET"))
        {
            $result     = array();
            $conditions = array(
                "latitude"  => array(
                    SQL_CONDITION_OPERATOR  => SQL_WHERE_NOT_EQUAL,
                    SQL_CONDITION_VALUE     => ""
                ),
                "longitude" => array(
                    SQL_CONDITION_OPERATOR  => SQL_WHERE_NOT_EQUAL,
                    SQL_CONDITION_VALUE     => ""
                ),
            );

            $maps = $this->general->get_data("__dynamics_assets", $conditions, null, null, null, null, array(
                "new_id",
                "new_assetid",
                "_kre_assetgroup_value",
                "new_assetname",
                "kre_address",
                "latitude",
                "longitude",
                "asset_image",
                "__dynamics_assets_type.new_type as '_new_assettype_value'",
                "__dynamics_assets_zonasi.kre_zonasi_text",
                "__dynamics_assets_zonasi.kre_zonasi_color",
                "__dynamics_assets_zonasi.kre_zonasi_description",
            ), array(
                array(
                    "table"     => "__dynamics_assets_type",
                    "condition" => "__dynamics_assets_type.new_assettypeid = __dynamics_assets._new_assettype_value",
                    "type"      => "LEFT"
                ),
                array(
                    "table"     => "__dynamics_assets_zonasi",
                    "condition" => "__dynamics_assets_zonasi.kre_zonasi = __dynamics_assets.kre_zonasi",
                    "type"      => "LEFT"
                )
            ))->result();

            foreach ($maps as $map)
            {
                $list_image = "";

                if (!empty($map->asset_image))
                {
                    $images = @json_decode($map->asset_image);

                    if (!empty($images))
                    {
                        foreach ($images as $image)
                        {
                            $image_loc  = base_url($this->microsoft_crm->_image_dir."/".$image);
                            $list_image .= "<img src='".$image_loc."' style='margin: 2px; height: 50px;'>";
                        }
                    }
                }

                if (!empty($list_image))
                {
                    $list_image = "<div style='margin-bottom: 10px;'>".$list_image."</div>";
                }

                $nbv = $this->microsoft_crm->get_trans_nbv($map);

                $content  = $list_image;
                $content .= "<table>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>Asset ID</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td nowrap valign='top'>".$map->new_id."</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>Asset Name</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td nowrap valign='top'>".$map->new_assetname."</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>Zonasi Description</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td nowrap valign='top'>".(!empty($map->kre_zonasi_description) ? $map->kre_zonasi_description : "-")."</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>Asset Type</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td nowrap valign='top'>".$map->_new_assettype_value."</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>NBV</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td nowrap valign='top'>".number_format($nbv, 2, ",", ".")."</td>";
                $content .= "</tr>";
                $content .= "<tr>";
                $content .= "<td nowrap valign='top'>Alamat</td>";
                $content .= "<td nowrap valign='top' style='padding: 0px 5px !important; text-align: center'>:</td>";
                $content .= "<td valign='top'>".$map->kre_address."</td>";
                $content .= "</tr>";
                $content .= "</table>";
                $content .= "<button type='button' class='btn btn-small btn-info mt-15 btn-show-asset-detail' data-id='".$map->new_assetid."'>View Detail</button>";

                $icon = "blue";

                if (!empty($map->kre_zonasi_color) && !empty($this->microsoft_crm->_marker_color[$map->kre_zonasi_color])) {
                    $icon = $this->microsoft_crm->_marker_color[$map->kre_zonasi_color];
                }

                $result[] = array(
                    "id"        => $map->new_id,
                    "text"      => "[".$map->new_id."] ".$map->new_assetname,
                    "title"     => $content,
                    "latitude"  => $map->latitude,
                    "longitude" => $map->longitude,
                    "icon"      => $icon
                );
            }

            header("Content-Type: application/json");
            echo json_encode($result);
        }
    }

    // ===================== SUPPORT / LIBRARIES =====================

    protected function library_support($page = "")
    {
        if ($this->_method == "index")
        {
            $this->template_mobile->data["_core_css"][] = "public/lib/plugins/leaflet/leaflet.css";
            $this->template_mobile->data["_core_css"][] = "public/lib/plugins/leaflet/leaflet.fullscreen.css";
            $this->template_mobile->data["_core_css"][] = "public/lib/plugins/leaflet/leaflet_awesome_number_markers.css";

            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/jquery-number/jquery.number.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/leaflet/leaflet.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/leaflet/Leaflet.fullscreen.min.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/leaflet/leaflet_awesome_number_markers.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/leaflet/leaflet-color-markers.js";
            $this->template_mobile->data["_core_js"][]  = "https://www.gstatic.com/charts/loader.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/highcharts/highcharts.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/highcharts/highcharts-3d.js";
            $this->template_mobile->data["_core_js"][]  = "public/lib/plugins/highcharts/modules/accessibility.js";
        }

        $this->template_mobile->data["_support_js"][] = "public/lib/finapp/js/datatable.js";
        $this->template_mobile->data["_support_js"][] = "public/lib/finapp/js/assets.js";
    }
}
