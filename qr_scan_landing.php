<?php if (isset($asset)): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Information</title>
    <link rel="stylesheet" href="path/to/your/css/style.css">

    <!-- Font Awesome (latest) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet CSS for the map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        /* General styling for mobile */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .asset-landing-page {
            max-width: 691px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            position: relative;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
        }

        .qr-code-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code-container img {
            width: 100px;
            height: 100px;
        }

        .accordion-button {
            background-color: #E0E0E0;
            color: #000;
            font-size: 16px;
            font-weight: 600;
            border: 2px solid #E0E0E0;
            border-radius: 10px;
        }

        .accordion-button:not(.collapsed) {
            background-color: #D6D6D6;
        }

        .accordion-button i {
            font-size: 20px;
            margin-right: 10px;
        }

        .accordion-body {
            background-color: #f4f4f4;
        }

        .accordion-item {
            border: none;
            margin-bottom: 10px;
        }

        .asset-info {
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        td {
            text-align: center;
        }

        .accordion-button {
            margin-bottom: 15px;
        }

        .asset-info p {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .asset-info p strong {
            width: 150px;
            text-align: left;
            font-weight: bold;
        }

        .asset-info p span {
            display: block;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            word-wrap: break-word;
            text-align: left;
            flex-grow: 1;
        }
    </style>
</head>
<body>

<div class="asset-landing-page">
    <h1>Asset Information</h1>

    <!-- QR code section -->
    <div class="qr-code-container">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($asset->id_barcode); ?>" alt="QR Code">
    </div>

    <div class="accordion" id="accordionExample">
        <!-- Asset Information -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingAssetInfo">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssetInfo" aria-expanded="true" aria-controls="collapseAssetInfo">
                    <i class="fas fa-info-circle"></i> Asset Information
                </button>
            </h2>
            <div id="collapseAssetInfo" class="accordion-collapse collapse show" aria-labelledby="headingAssetInfo">
                <div class="asset-info">
                    <p><strong>Asset ID:</strong><span><?php echo isset($asset->new_id) ? $asset->new_id : 'null'; ?></span></p>
                    <p><strong>Asset Name:</strong><span><?php echo isset($asset->new_assetname) ? $asset->new_assetname : 'null'; ?></span></p>
                    <p><strong>Asset Code:</strong><span><?php echo isset($asset->tmp_new_id) ? $asset->tmp_new_id : 'null'; ?></span></p>
                    <p><strong>Asset Type:</strong><span><?php echo isset($asset->kre_type) ? $asset->kre_type : 'null'; ?></span></p>
                    <p><strong>Location:</strong><span><?php echo isset($asset->kre_address) ? $asset->kre_address : 'null'; ?></span></p>
                    <p><strong>NBV:</strong><span><?php echo isset($asset->kre_nbv) ? number_format($asset->kre_nbv, 2, ",", ".") : 'null'; ?></span></p>
                    <p><strong>Asset Condition:</strong><span><?php echo isset($asset->id_kre_assetcondition) ? $asset->id_kre_assetcondition : 'null'; ?></span></p>
                    <p><strong>Land Area:</strong><span><?php $land_area = preg_replace('/[^0-9\.]/', '', $asset->new_luastanah); echo isset($asset->new_luastanah) && is_numeric($land_area) ? number_format($land_area, 2, ",", ".") : 'null'; ?></span></p>
                    <p><strong>Building Area:</strong><span><?php echo isset($asset->new_luasbangunan) ? number_format($asset->new_luasbangunan, 2, ",", ".") : 'null'; ?></span></p>
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSchedule">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchedule" aria-expanded="false" aria-controls="collapseSchedule">
                    <i class="fas fa-calendar-check"></i> Schedule
                </button>
            </h2>
            <div id="collapseSchedule" class="accordion-collapse collapse" aria-labelledby="headingSchedule">
                <div class="accordion-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Asset ID</th>
                                <th>Maintenance Date</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($schedule_maintenance) && !empty($schedule_maintenance)): ?>
                                <?php foreach ($schedule_maintenance as $schedule): ?>
                                    <tr>
                                        <td><?php echo $schedule->asset_no; ?></td>
                                        <td><?php echo $schedule->maintenance_date; ?></td>
                                        <td><?php echo isset($schedule->description) ? $schedule->description : '-'; ?></td>
                                        <td><?php echo isset($schedule->status) ? $schedule->status : 'Scheduled'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>AR41712000375</td>
                                    <td>2025-09-26</td>
                                    <td>Maintenance bulanan</td>
                                    <td>Scheduled</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

               <!-- Work Request -->
        <div class="accordion-item">
  <h2 class="accordion-header" id="headingRequest">
    <button
      class="accordion-button collapsed"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#collapseRequest"
      aria-expanded="false"
      aria-controls="collapseRequest"
    >
      <i class="fas fa-clipboard-list me-2"></i> Work Request
    </button>
  </h2>
  <div
    id="collapseRequest"
    class="accordion-collapse collapse"
    aria-labelledby="headingRequest"
  >
    <div class="accordion-body">

      <button class="btn btn-primary mb-3" id="addWorkRequestBtn">
        Add Work Request
      </button>

      <div class="table-responsive">
        <table id="tblWorkRequest" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>No Work Request</th>
              <th>Request Reason</th>
              <th>Request Date</th>
              <th>Maintenance Plan Date</th>
              <th>Pelaksana</th>
              <th>PIC</th>
              <th>Opsi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($work_orders) && !empty($work_orders)): ?>
              <?php foreach ($work_orders as $wr): ?>
                <tr>
                  <td>
                    <a href="#"
                       class="link-detail"
                       data-bs-toggle="modal"
                       data-bs-target="#detailModal"
                       data-no_wr="<?php echo $wr->no_wr; ?>"
                       data-asset_code="<?php echo $asset->new_id; ?>"
                       data-request_reason="<?php echo htmlspecialchars($wr->request_reason, ENT_QUOTES); ?>"
                       data-maintenance_date="<?php echo $wr->maintenance_date; ?>"
                       data-request_description="<?php echo htmlspecialchars($wr->request_description, ENT_QUOTES); ?>"
                       data-request_date="<?php echo $wr->request_datetime; ?>"
                       data-pelaksana="<?php echo htmlspecialchars($wr->pelaksana, ENT_QUOTES); ?>"
                       data-pic="<?php echo htmlspecialchars($wr->pic, ENT_QUOTES); ?>"
                       data-status="<?php echo htmlspecialchars($wr->status, ENT_QUOTES); ?>"
                       data-attachment="<?php echo !empty($wr->attachment) ? base_url($wr->attachment) : ''; ?>"
                    >
                      <?php echo $wr->no_wr; ?>
                    </a>
                  </td>
                  <td><?php echo $wr->request_reason; ?></td>
                  <td><?php echo $wr->request_datetime; ?></td>
                  <td><?php echo $wr->maintenance_date; ?></td>
                  <td><?php echo $wr->pelaksana; ?></td>
                  <td><?php echo $wr->pic; ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-warning btn-sm btn-edit-wr"
                      data-id="<?php echo $wr->id; ?>"
                      data-no_wr="<?php echo htmlspecialchars($wr->no_wr, ENT_QUOTES); ?>"
                      data-request_reason="<?php echo htmlspecialchars($wr->request_reason, ENT_QUOTES); ?>"
                      data-request_date="<?php echo htmlspecialchars($wr->request_datetime, ENT_QUOTES); ?>"
                      data-maintenance_date="<?php echo htmlspecialchars($wr->maintenance_date, ENT_QUOTES); ?>"
                      data-request_description="<?php echo htmlspecialchars($wr->request_description, ENT_QUOTES); ?>"
                      data-pelaksana="<?php echo htmlspecialchars($wr->pelaksana, ENT_QUOTES); ?>"
                      data-pic="<?php echo htmlspecialchars($wr->pic, ENT_QUOTES); ?>"
                      data-attachment="<?php echo htmlspecialchars($wr->attachment ?? '', ENT_QUOTES); ?>"
                    >
                      Edit
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- ✅ no colspan, 7 td cells -->
              <tr>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">No work requests found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>


        <!-- Work Request Form Modal -->
        <div
          class="modal fade"
          id="workRequestModal"
          tabindex="-1"
          aria-labelledby="workRequestModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="workRequestModalLabel">
                  Form Work Request
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>
              <div class="modal-body">
                <form
                  id="frmWorkRequest"
                  action="<?php echo base_url('mobile/qrassets/submit_work_request/' . $qr_value); ?>"
                  method="POST"
                  enctype="multipart/form-data"
                >
                  <!-- ✅ Hidden ID for edit -->
                  <input type="hidden" name="id" id="wr_id">

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Asset No</label>
                      <input
                        type="text"
                        class="form-control"
                        value="<?php echo $asset->new_id; ?>"
                        name="asset_no"
                        id="wr_asset_no"
                        readonly
                      />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">No WR</label>
                      <input
                        type="text"
                        class="form-control"
                        name="no_wr"
                        id="wr_no_wr"
                        value="WR-<?php echo date('Ymd'); ?>-<?php echo rand(100000,999999); ?>"
                        readonly
                      />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Request Reason</label>
                      <select class="form-select" name="request_reason" id="wr_request_reason" required>
                        <option value="">-- Select --</option>
                        <option>Preventive Maintenance</option>
                        <option>Breakdown Maintenance</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Pelaksana</label>
                      <select class="form-select" name="pelaksana" id="wr_pelaksana" required>
                        <option value="">-- Select --</option>
                        <option>Internal</option>
                        <option>Vendor</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Request Date</label>
                      <input
                        type="date"
                        class="form-control"
                        name="request_date"
                        id="wr_request_date"
                        required
                      />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Maintenance Plan Date</label>
                      <input
                        type="date"
                        class="form-control"
                        name="maintenance_date"
                        id="wr_maintenance_date"
                        required
                      />
                    </div>

                    <div class="col-12">
                      <label class="form-label">Request Description</label>
                      <textarea
                        class="form-control"
                        name="request_description"
                        id="wr_request_description"
                        rows="3"
                        required
                      ></textarea>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">PIC</label>
                      <select class="form-select" name="pic" id="wr_pic" required>
                        <option value="">-- Select --</option>
                        <option>Haji Sulam</option>
                        <option>Dedy Mizwar</option>
                        <option>User 3</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Attachment</label>
                      <input
                        type="file"
                        class="form-control"
                        name="attachment"
                        id="wr_attachment"
                        accept=".jpg,.jpeg,.png,.pdf"
                      />
                      <!-- (optional) you can show current attachment info via JS if you want -->
                    </div>
                  </div>

                  <div class="mt-3 text-end">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-bs-dismiss="modal"
                    >
                      Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                      Save Changes
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Work Request Detail Modal -->
        <div
          class="modal fade"
          id="detailModal"
          tabindex="-1"
          aria-labelledby="detailModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Information</h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>
              <div class="modal-body">
                <table class="table table-bordered">
                  <tr><th>No WR</th><td id="detail_no_wr">-</td></tr>
                  <tr><th>Asset Code</th><td id="detail_asset_code">-</td></tr>
                  <tr><th>Request Reason</th><td id="detail_request_reason">-</td></tr>
                  <tr><th>Maintenance Date</th><td id="detail_maintenance_date">-</td></tr>
                  <tr><th>Request Description</th><td id="detail_request_description">-</td></tr>
                  <tr><th>Request Date</th><td id="detail_request_date">-</td></tr>
                  <tr><th>Pelaksana</th><td id="detail_pelaksana">-</td></tr>
                  <tr><th>Nama PIC</th><td id="detail_pic">-</td></tr>
                  <tr><th>Approval Status</th><td id="detail_status">-</td></tr>
                </table>

                <h6>Attachment:</h6>
                <ul id="detail_attachments">
                  <li>No attachment</li>
                </ul>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Maintenance Deliverable -->
        <div class="accordion-item">
  <h2 class="accordion-header" id="headingDeliverable">
    <button class="accordion-button collapsed" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapseDeliverable"
            aria-expanded="false" aria-controls="collapseDeliverable">
      <i class="fas fa-cogs me-2"></i> Maintenance Deliverable
    </button>
  </h2>

  <div id="collapseDeliverable" class="accordion-collapse collapse" aria-labelledby="headingDeliverable">
    <div class="accordion-body">

      <button type="button" class="btn btn-success mb-3" id="addMaintenanceBtn">
        <i class="fas fa-plus"></i> Add Maintenance
      </button>

      <div class="table-responsive">
        <table id="tblDeliverable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>No Maintenance</th>
              <th>No Work Request</th>
              <th>Request Reason</th>
              <th>Status</th>
              <th style="width:140px;">Opsi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($maintenance_deliverables) && !empty($maintenance_deliverables)): ?>
              <?php foreach ($maintenance_deliverables as $md): ?>
                <tr>
                  <td><?php echo $md->no_maintenance; ?></td>
                  <td><?php echo $md->no_wr; ?></td>
                  <td><?php echo $md->request_reason; ?></td>
                  <td><?php echo $md->status; ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-warning btn-sm btn-edit"
                      data-id="<?php echo $md->id; ?>"
                      data-work_request_id="<?php echo $md->work_request_id; ?>"
                      data-no_maintenance="<?php echo htmlspecialchars($md->no_maintenance, ENT_QUOTES); ?>"
                      data-no_wr="<?php echo htmlspecialchars($md->no_wr, ENT_QUOTES); ?>"
                      data-request_reason="<?php echo htmlspecialchars($md->request_reason, ENT_QUOTES); ?>"
                      data-request_description="<?php echo htmlspecialchars($md->description, ENT_QUOTES); ?>"
                      data-start_date="<?php echo $md->start_date; ?>"
                      data-end_date="<?php echo $md->end_date; ?>"
                      data-status="<?php echo $md->status; ?>"
                    >
                      Edit
                    </button>

                    <a
                      href="<?php echo base_url('mobile/qrassets/delete_maintenance_deliverable/' . $md->id . '/' . $qr_value); ?>"
                      class="btn btn-danger btn-sm btn-delete"
                    >
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- ✅ no colspan, 5 td cells -->
              <tr>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">No maintenance deliverables found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

        <!-- Maintenance Form Modal -->
        <div class="modal fade" id="maintenanceModal" tabindex="-1"
             aria-labelledby="maintenanceModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="maintenanceModalLabel">Form Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form
  id="frmMaintenance"
  action="<?php echo base_url('mobile/qrassets/save_maintenance_deliverable/' . $qr_value); ?>"
  method="POST"
  enctype="multipart/form-data"
>
                  <input type="hidden" name="id" id="maintenance_id">
                  <input type="hidden" name="asset_no" value="<?php echo $asset->new_id; ?>">

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">No Maintenance</label>
                      <input type="text" class="form-control" id="no_maintenance" name="no_maintenance" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Asset No</label>
                      <input type="text" class="form-control"
                             value="<?php echo $asset->new_id; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">No Work Request</label>
                      <select class="form-select" id="work_request_id" name="work_request_id">
                        <option value="">-- Select --</option>
                        <?php if (!empty($work_orders)): foreach ($work_orders as $wo): ?>
                          <option value="<?php echo $wo->id; ?>"
                                  data-no_wr="<?php echo $wo->no_wr; ?>"
                                  data-reason="<?php echo htmlspecialchars($wo->request_reason, ENT_QUOTES); ?>">
                            <?php echo $wo->no_wr . ' - ' . $wo->request_reason; ?>
                          </option>
                        <?php endforeach; endif; ?>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Request Reason</label>
                      <input type="text" class="form-control" id="request_reason" name="request_reason">
                    </div>
                    <div class="col-12">
                      <label class="form-label">Request Description</label>
                      <textarea class="form-control" id="request_description" name="request_description" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Start Date</label>
                      <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">End Date</label>
                      <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-12">
                      <label class="form-label">Attachment (Evidence)</label>
                      <input type="file" class="form-control" id="attachment" name="attachment"
                             accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-12">
                      <label class="form-label">Maintenance Status</label>
                      <select class="form-select" id="maintenance_status" name="maintenance_status">
                        <option value="">-- Select --</option>
                        <option value="On Process">On Process</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                        <option value="Rejected">Rejected</option>
                      </select>
                    </div>
                  </div>

                  <div class="mt-4 text-end">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Work History -->
<!-- Work History -->
<div class="accordion-item">
  <h2 class="accordion-header" id="headingHistory">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false" aria-controls="collapseHistory">
      <i class="fas fa-history"></i> Work History
    </button>
  </h2>
  <div id="collapseHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory">
    <div class="accordion-body">
      <div class="table-responsive">
        <table id="tblHistory" class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>No Maintenance</th>
              <th>Request Date</th>
              <th>Maintenance Date</th>
              <th>Description</th>
              <th>Status</th>
              <th>Evidence</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($work_history)): ?>
              <?php foreach ($work_history as $history): ?>
                <tr>
                  <td><?php echo $history->no_maintenance; ?></td>
                  <td><?php echo $history->request_date; ?></td>
                  <td><?php echo $history->maintenance_date; ?></td>
                  <td><?php echo $history->description; ?></td>
                  <td><?php echo $history->status; ?></td>
                  <td>
                    <?php if (!empty($history->evidence)): ?>
                      <button
                        type="button"
                        class="btn btn-sm btn-primary btn-view-history"
                        data-evidence="<?php echo base_url($history->evidence); ?>"
                      >
                        View
                      </button>
                    <?php else: ?>
                      <span class="text-muted">No evidence</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- ✅ no colspan, 6 td cells -->
              <tr>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">-</td>
                <td class="text-center text-muted">No work history found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>



        <!-- Work History Evidence Modal -->
        <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Evidence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="historyEvidenceContainer">
                  <p>No evidence.</p>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

    </div> <!-- accordion -->
</div> <!-- page container -->

<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // ===== Helper: get all Work Request IDs already used in Maintenance Deliverable =====
  function getUsedWorkRequestIds() {
    const used = new Set();
    document.querySelectorAll("#tblDeliverable .btn-edit").forEach(function (btn) {
      const wrId = btn.dataset.work_request_id;
      if (wrId) {
        used.add(wrId);
      }
    });
    return used;
  }

  // ===== Work Request: Add / Detail / Edit =====
  const wrForm = document.getElementById("frmWorkRequest");

  // Open "Add Work Request" modal
  const addWRBtn = document.getElementById("addWorkRequestBtn");
  if (addWRBtn && wrForm) {
    addWRBtn.addEventListener("click", function () {
      wrForm.reset();
      document.getElementById("wr_id").value = "";

      // keep asset no default from HTML
      document.getElementById("wr_asset_no").value = "<?php echo $asset->new_id; ?>";

      const modal = new bootstrap.Modal(document.getElementById("workRequestModal"));
      modal.show();
    });
  }

  // Fill WR detail modal
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("link-detail")) {
      const a   = e.target;
      const get = (name, fallback = "") => a.dataset[name] || fallback;

      document.getElementById("detail_no_wr").textContent               = get("no_wr", "-");
      document.getElementById("detail_asset_code").textContent          = get("asset_code", "-");
      document.getElementById("detail_request_reason").textContent      = get("request_reason", "-");
      document.getElementById("detail_maintenance_date").textContent    = get("maintenance_date", "-");
      document.getElementById("detail_request_description").textContent = get("request_description", "-");
      document.getElementById("detail_request_date").textContent        = get("request_date", "-");
      document.getElementById("detail_pelaksana").textContent           = get("pelaksana", "-");
      document.getElementById("detail_pic").textContent                 = get("pic", "-");
      document.getElementById("detail_status").textContent              = get("status", "-");

      const attachmentUrl = get("attachment", "");
      const ul = document.getElementById("detail_attachments");
      ul.innerHTML = "";
      if (attachmentUrl) {
        const li = document.createElement("li");
        const link = document.createElement("a");
        link.href = attachmentUrl;
        link.target = "_blank";
        link.textContent = "Download attachment";
        li.appendChild(link);
        ul.appendChild(li);
      } else {
        const li = document.createElement("li");
        li.textContent = "No attachment";
        ul.appendChild(li);
      }
    }
  });

  // Edit Work Request: fill modal with existing data
  $(document).on("click", ".btn-edit-wr", function () {
    if (!wrForm) return;

    const btn = this;

    $("#wr_id").val(btn.dataset.id || "");
    $("#wr_no_wr").val(btn.dataset.no_wr || "");
    $("#wr_request_reason").val(btn.dataset.request_reason || "");
    $("#wr_pelaksana").val(btn.dataset.pelaksana || "");

    if (btn.dataset.request_date) {
      const d = btn.dataset.request_date.substring(0, 10);
      $("#wr_request_date").val(d);
    } else {
      $("#wr_request_date").val("");
    }

    if (btn.dataset.maintenance_date) {
      $("#wr_maintenance_date").val(btn.dataset.maintenance_date);
    } else {
      $("#wr_maintenance_date").val("");
    }

    $("#wr_request_description").val(btn.dataset.request_description || "");
    $("#wr_pic").val(btn.dataset.pic || "");

    const modal = new bootstrap.Modal(document.getElementById("workRequestModal"));
    modal.show();
  });

  // ===== DataTables init =====
  if (typeof $ !== "undefined" && $.fn.DataTable) {
    $("#tblWorkRequest").DataTable();
    $("#tblDeliverable").DataTable();
    $("#tblHistory").DataTable();
  }

  // ===== Maintenance Deliverable logic =====

  function nextWONumber() {
    const d   = new Date();
    const seq = String(Math.floor(Math.random() * 999999)).padStart(6, "0");
    return `WO-${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, "0")}${String(d.getDate()).padStart(2, "0")}-${seq}`;
  }

  // Auto-fill reason from WR select in Maintenance form
  document.getElementById("work_request_id")?.addEventListener("change", function () {
    const opt = this.selectedOptions[0];
    if (!opt) return;
    const reason = opt.dataset.reason || "";
    document.getElementById("request_reason").value = reason;
  });

  // 👉 When adding Maintenance:
  //    hide work requests that are already used in any maintenance_deliverable row
  document.getElementById("addMaintenanceBtn").addEventListener("click", function () {
    const form = document.getElementById("frmMaintenance");
    form.reset();
    document.getElementById("maintenance_id").value     = "";
    document.getElementById("no_maintenance").value     = nextWONumber();
    document.getElementById("maintenance_status").value = "On Process";

    const wrSelect = document.getElementById("work_request_id");
    if (wrSelect) {
      const usedIds = getUsedWorkRequestIds();
      Array.from(wrSelect.options).forEach(function (opt) {
        if (!opt.value) {
          opt.hidden = false; // "-- Select --"
          return;
        }
        // hide if this WR is already used by any maintenance
        opt.hidden = usedIds.has(opt.value);
      });
      // reset selection
      wrSelect.value = "";
    }

    const modal = new bootstrap.Modal(document.getElementById("maintenanceModal"));
    modal.show();
  });

  // 👉 When editing Maintenance:
  //    hide WRs used by other maintenance rows, but keep this row's WR visible
  $("#tblDeliverable").on("click", ".btn-edit", function (e) {
    e.preventDefault();
    const btn  = this;
    const form = document.getElementById("frmMaintenance");

    form.reset();

    document.getElementById("maintenance_id").value      = btn.dataset.id || "";
    document.getElementById("no_maintenance").value      = btn.dataset.no_maintenance || "";
    document.getElementById("request_reason").value      = btn.dataset.request_reason || "";
    document.getElementById("request_description").value = btn.dataset.request_description || "";
    document.getElementById("start_date").value          = btn.dataset.start_date || "";
    document.getElementById("end_date").value            = btn.dataset.end_date || "";
    document.getElementById("maintenance_status").value  = btn.dataset.status || "";

    const wrSelect = document.getElementById("work_request_id");
    const currentWrId = btn.dataset.work_request_id || "";

    if (wrSelect) {
      const usedIds = getUsedWorkRequestIds();
      Array.from(wrSelect.options).forEach(function (opt) {
        if (!opt.value) {
          opt.hidden = false;
          return;
        }
        // hide WRs used by other maintenances, but keep this row's WR visible
        if (usedIds.has(opt.value) && opt.value !== currentWrId) {
          opt.hidden = true;
        } else {
          opt.hidden = false;
        }
      });
      wrSelect.value = currentWrId;
    }

    const modal = new bootstrap.Modal(document.getElementById("maintenanceModal"));
    modal.show();
  });

  // Delete Maintenance confirmation
  $("#tblDeliverable").on("click", ".btn-delete", function (e) {
    if (!confirm("Delete this maintenance record?")) {
      e.preventDefault();
    }
    // After deletion, page reloads, so the freed WR will automatically be available again
  });

  // ===== Work History Evidence modal =====
  $(document).on("click", ".btn-view-history", function () {
    const evidenceUrl = this.dataset.evidence || "";
    const container   = document.getElementById("historyEvidenceContainer");
    container.innerHTML = "";

    if (evidenceUrl) {
      const link = document.createElement("a");
      link.href = evidenceUrl;
      link.target = "_blank";
      link.textContent = "Download / View evidence";
      container.appendChild(link);

      if (/\.(jpg|jpeg|png)$/i.test(evidenceUrl)) {
        const img = document.createElement("img");
        img.src = evidenceUrl;
        img.alt = "Evidence";
        img.className = "img-fluid mt-3";
        container.appendChild(img);
      }
    } else {
      container.textContent = "No evidence.";
    }

    const modal = new bootstrap.Modal(document.getElementById("historyModal"));
    modal.show();
  });
});
</script>



<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // If you actually have a <div id="map">, this will show the map.
    // If not, you can remove this block safely.
    try {
        var map = L.map('map').setView(
            [<?php echo isset($asset->latitude) ? $asset->latitude : '0'; ?>,
             <?php echo isset($asset->longitude) ? $asset->longitude : '0'; ?>],
            13
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L
L.marker([
        <?php echo isset($asset->latitude) ? $asset->latitude : '0'; ?>,
        <?php echo isset($asset->longitude) ? $asset->longitude : '0'; ?>
    ])
    .addTo(map)
    .bindPopup('<b><?php echo isset($asset->new_assetname) ? $asset->new_assetname : 'Asset'; ?></b><br><?php echo isset($asset->kre_address) ? $asset->kre_address : 'Location not available'; ?>')
    .openPopup();
</script>

</body>
</html>
<?php else: ?>
    <p>Asset not found.</p>
<?php endif; ?>
