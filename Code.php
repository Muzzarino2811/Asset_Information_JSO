<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * Class Code
 *
 * Model
 * @property General_model  $general
 * @property Encryptlib     $encryptlib
 */
class Code extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        redirect(base_url());
    }

    public function read($code)
    {
        $code = $this->general->get_data("mst_code", array(
            "code" => array(
                SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
                SQL_CONDITION_VALUE => $code
            )
        ))->row();

        if ($code->object_type == "assets") {
            $this->read_type_assets($code);
        } else {

        }
    }

    public function fetch_asset_data($asset_id)
{
    // Fetch the asset data based on the given asset_id
    $asset = $this->general->get_data("__dynamics_assets", array(
        "id" => array(
            SQL_CONDITION_OPERATOR  => SQL_WHERE_EQUAL,
            SQL_CONDITION_VALUE     => $asset_id
        )
    ))->row();

    if ($asset) {
        // Return the asset data in a format that can be used on the landing page
        $data = array(
            'asset_id' => $asset->new_id,
            'asset_name' => $asset->new_assetname,
            'asset_group' => $asset->new_description1,
            'asset_type' => $asset->new_assettype, // Ensure you map this correctly from your database
            'location' => $asset->new_location,   // Similarly map from the asset table
            'land_area' => $asset->new_luasanah,
            'building_area' => $asset->new_luasbangunan,
            'ownership' => $asset->new_buktikepemilikan, // Assuming this is the ownership info
            'ownership_number' => $asset->new_buktikepemilikanid // If you have this field
        );

        return $data;
    } else {
        // If no asset is found, return a default response
        return array('error' => 'Asset not found');
    }
}


    protected function read_type_assets($data_code)
    {
        if (!empty($this->session->userdata("id")) && !$this->agent->is_mobile()){
            $url    = base_url("modules/assets/data/detail/".$this->encryptlib->encode($data_code->object_id));
            redirect($url);
        }

        $this->load->library("datatable");
        $this->load->library("microsoft_crm");

        $this->datatable->set_initial("dynamics-assets-dashboard-detail");
        $this->rebuilt_fields_assets();

        $asset = $this->general->get_data("__dynamics_assets", array(
            "id" => array(
                SQL_CONDITION_OPERATOR  => SQL_WHERE_EQUAL,
                SQL_CONDITION_VALUE     => $data_code->object_id
            )
        ))->row();
        $assets = $this->datatable->get_detail($data_code->object_id, "detail");

        $child_conditions["_kre_assetgroup_value"] = array(
            SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
            SQL_CONDITION_VALUE => $asset->_kre_assetgroup_value
        );
        $child_conditions["_new_parentasset_value"] = array(
            SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
            SQL_CONDITION_VALUE => $asset->new_assetid
        );

        $childs = $this->general->get_data("__dynamics_assets", $child_conditions, null, null, null, null, array(
            "id",
            "new_id",
        ))->result();

        $ids_asset = array(
            $asset->new_id
        );
        foreach ($childs as $child) {
            $ids_asset[] = $child->new_id;
        }

        $title = $asset->new_id . " - " . $asset->new_assetname;

        if (!empty($assets["original"]->id_barcode)) {
            $assets["barcode"] = $this->utils->get_barcode_single($asset->id_barcode);
        }

        // Image
        $images = array();
        if (!empty($asset->asset_image)) {
            $tmp_images = @json_decode($asset->asset_image);

            if (!empty($tmp_images)) {
                foreach ($tmp_images as $image) {
                    $images[] = base_url($this->microsoft_crm->_image_dir . "/" . $image);
                }
            }

            

        }

        



        $this->template->data["_core_css"][]    = "public/lib/plugins/leaflet/leaflet.css";
        $this->template->data["_core_css"][]    = "public/lib/plugins/leaflet/leaflet.fullscreen.css";
        $this->template->data["_core_css"][]    = "public/lib/plugins/leaflet/leaflet_awesome_number_markers.css";

        $this->template->data["_core_js"][]     = "public/lib/plugins/leaflet/leaflet.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/leaflet/Leaflet.fullscreen.min.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/leaflet/leaflet_awesome_number_markers.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/leaflet/leaflet-color-markers.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/jquery-autocomplete/jquery.autocomplete.js";
        $this->template->data["_core_js"][]     = "https://www.gstatic.com/charts/loader.js";

        $this->template->data["_core_js"][]     = "public/lib/plugins/highcharts/highcharts.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/highcharts/highcharts-3d.js";
        $this->template->data["_core_js"][]     = "public/lib/plugins/highcharts/modules/accessibility.js";

        $this->template->data["_support_js"][]  = "public/js/public/assets.js";

        $this->template->data["title"] = $title;
        $this->template->data["assets"] = $assets;
        $this->template->data["images"] = $images;

        $this->template->generate_public("home/assets/detail");
    }

    protected function rebuilt_fields_assets()
    {
        $this->_fields_master   = array_merge($this->microsoft_crm->_fields_list, $this->microsoft_crm->_fields_master);
        $this->_fields_master   = $this->microsoft_crm->_fields_list;

        // Employee List
        foreach ($this->_fields_master as $field => $fields)
        {
            if (!in_array($field, array("kre_kondisi", "kre_statusaset")))
            {
                $initial_field_search   = $this->utils->search_array($this->datatable->_initial->fields, "field", $field, false, true);

                if (!empty($initial_field_search))
                {
                    $initial_field_key      = $initial_field_search["key"];
                    $initial_fields          = $this->datatable->_initial->fields[$initial_field_key];

                    if (!empty($initial_fields->datatable->render))
                    {
                        if (!empty($fields["table"]))
                        {
                            $filters        = array();

                            $tmp_filter     = $this->general->get_data($fields["table"], $fields["conditions"], $fields["label"], "ASC")->result_array();
                            foreach ($tmp_filter as $item)
                            {
                                $filters[$item[$fields["key"]]]    = $item[$fields["label"]];
                            }

                        }
                        else
                        {
                            $filters        = $fields;
                        }

                        $this->_fields_master[$field]   = array(
                            "label" => $initial_fields->datatable->label,
                            "list"  => $filters
                        );

                        if (!empty($fields["table"]))
                        {
                            $this->_fields_master[$field]["source"]     = $fields;
                        }

                        $this->datatable->_initial->fields[$initial_field_key]->type            = DATA_TYPE_SELECT_LIST_KEY;
                        $this->datatable->_initial->fields[$initial_field_key]->other["list"]   = $filters;
                    }
                    else
                    {
                        unset($this->_fields_master[$field]);
                    }
                }
            }

        }
    }
}
