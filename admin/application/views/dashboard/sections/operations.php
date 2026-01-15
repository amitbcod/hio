<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'operations';
$this->load->view('dashboard/header', $header_data);
// normalize variable used in view
$operations = isset($service_operations) ? $service_operations : (isset($operations) ? $operations : NULL);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">SERVICE OPERATIONS</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Operating hours and service details</p>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-10">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <form method="POST">
                                <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Service Details</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="service_location" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Service Location <span style="color: #dc3545;">*</span></label>
                                        <select id="service_location" name="service_location" class="form-control" required 
                                                style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                                <option value="">-- select --</option>
                                                <option value="fixed" <?php echo (isset($operations->service_location) && $operations->service_location == 'fixed') ? 'selected' : ''; ?>>Fixed Location</option>
                                                <option value="gps" <?php echo (isset($operations->is_gps_location) && $operations->is_gps_location) ? 'selected' : ''; ?>>GPS / Mobile</option>
                                                <option value="multiple" <?php echo (isset($operations->service_location) && $operations->service_location == 'multiple') ? 'selected' : ''; ?>>Multiple Locations</option>
                                            </select>
                                        </div>

                                    <div class="col-md-6 mb-3" id="gps_coords_row" style="display: <?php echo (isset($operations->is_gps_location) && $operations->is_gps_location) ? 'block' : 'none'; ?>;">
                                        <label for="gps_coordinates" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">GPS Coordinates (lat,lng)</label>
                                        <input type="text" id="gps_coordinates" name="gps_coordinates" class="form-control" value="<?php echo isset($operations->gps_coordinates) ? $operations->gps_coordinates : ''; ?>" placeholder="e.g. -20.1609,57.5012" 
                                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                        </div>
                                    </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 12px;">Operating Area <span style="color: #dc3545;">*</span></label>
                                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                                            <?php $regions = array('North','South','East','West','Central');
                                            $selected_areas = array();
                                            if (!empty($operations->operating_areas)) {
                                                $selected_areas = is_array($operations->operating_areas) ? $operations->operating_areas : json_decode($operations->operating_areas, TRUE);
                                            }
                                            foreach ($regions as $r): ?>
                                                <label style="display: flex; align-items: center; cursor: pointer;">
                                                    <input type="checkbox" name="operating_areas[]" value="<?php echo $r; ?>" <?php echo in_array($r, $selected_areas) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer; margin-right: 6px;"> 
                                                    <span style="color: #555; font-size: 14px;"><?php echo $r; ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                            <label style="display: flex; align-items: center; cursor: pointer; margin-left: 10px;">
                                                <input type="checkbox" id="is_nationwide" name="is_nationwide" value="1" <?php echo (isset($operations->is_nationwide) && $operations->is_nationwide) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer; margin-right: 6px;"> 
                                                <span style="color: #555; font-size: 14px; font-weight: 500;">Nationwide</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="has_pickup_dropoff" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Pickup / Drop-off Options</label>
                                        <select id="has_pickup_dropoff" name="has_pickup_dropoff" class="form-control" 
                                                style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                                <option value="0" <?php echo (empty($operations->has_pickup_dropoff) || !$operations->has_pickup_dropoff) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo (!empty($operations->has_pickup_dropoff) && $operations->has_pickup_dropoff) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        </div>

                                    <div class="col-md-6" id="pickup_details_box" style="display: <?php echo (!empty($operations->has_pickup_dropoff) && $operations->has_pickup_dropoff) ? 'block' : 'none'; ?>;">
                                        <label style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Pickup / Drop-off Details</label>
                                        <div class="form-group">
                                            <input type="number" step="0.01" name="pickup_dropoff_surcharge" id="pickup_dropoff_surcharge" class="form-control mb-2" placeholder="Surcharge amount" value="<?php echo isset($operations->pickup_dropoff_surcharge) ? $operations->pickup_dropoff_surcharge : ''; ?>" 
                                                   style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; margin-bottom: 10px;">
                                            <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 10px;">
                                                <input type="checkbox" name="pickup_dropoff_free" value="1" <?php echo (!empty($operations->pickup_dropoff_free)) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer; margin-right: 6px;"> 
                                                <span style="color: #555; font-size: 14px;">Free Of Charge</span>
                                            </label>
                                            <textarea name="pickup_dropoff_details" class="form-control mt-2" placeholder="Additional details" 
                                                      style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"><?php echo isset($operations->pickup_dropoff_details) ? htmlspecialchars($operations->pickup_dropoff_details) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Emergency Contact <span style="color: #dc3545;">*</span></label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" required value="<?php echo isset($operations->emergency_contact_name) ? $operations->emergency_contact_name : ''; ?>" 
                                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="emergency_contact_phone" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Phone <span style="color: #dc3545;">*</span></label>
                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" required value="<?php echo isset($operations->emergency_contact_phone) ? $operations->emergency_contact_phone : ''; ?>" 
                                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="emergency_contact_email" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Email <span style="color: #dc3545;">*</span></label>
                                        <input type="email" id="emergency_contact_email" name="emergency_contact_email" class="form-control" required value="<?php echo isset($operations->emergency_contact_email) ? $operations->emergency_contact_email : ''; ?>" 
                                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Operating Hours & Schedule</h5>

                            <div style="background-color: #e7f3ff; border-left: 4px solid #4169E1; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px;">
                                <h6 style="color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Service Operations Configuration</h6>
                                <p style="margin-bottom: 0; color: #555; font-size: 13px; line-height: 1.5;">Set your operating hours, service availability, and operational policies below.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="monday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Monday - Opening Time</label>
                                    <input type="time" class="form-control" id="monday_open" name="monday_open" 
                                           value="<?php echo isset($operations->monday_open) ? $operations->monday_open : '09:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="monday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Monday - Closing Time</label>
                                    <input type="time" class="form-control" id="monday_close" name="monday_close" 
                                           value="<?php echo isset($operations->monday_close) ? $operations->monday_close : '18:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tuesday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Tuesday - Opening Time</label>
                                    <input type="time" class="form-control" id="tuesday_open" name="tuesday_open" 
                                           value="<?php echo isset($operations->tuesday_open) ? $operations->tuesday_open : '09:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tuesday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Tuesday - Closing Time</label>
                                    <input type="time" class="form-control" id="tuesday_close" name="tuesday_close" 
                                           value="<?php echo isset($operations->tuesday_close) ? $operations->tuesday_close : '18:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="wednesday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Wednesday - Opening Time</label>
                                    <input type="time" class="form-control" id="wednesday_open" name="wednesday_open" 
                                           value="<?php echo isset($operations->wednesday_open) ? $operations->wednesday_open : '09:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="wednesday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Wednesday - Closing Time</label>
                                    <input type="time" class="form-control" id="wednesday_close" name="wednesday_close" 
                                           value="<?php echo isset($operations->wednesday_close) ? $operations->wednesday_close : '18:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="thursday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Thursday - Opening Time</label>
                                    <input type="time" class="form-control" id="thursday_open" name="thursday_open" 
                                           value="<?php echo isset($operations->thursday_open) ? $operations->thursday_open : '09:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="thursday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Thursday - Closing Time</label>
                                    <input type="time" class="form-control" id="thursday_close" name="thursday_close" 
                                           value="<?php echo isset($operations->thursday_close) ? $operations->thursday_close : '18:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="friday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Friday - Opening Time</label>
                                    <input type="time" class="form-control" id="friday_open" name="friday_open" 
                                           value="<?php echo isset($operations->friday_open) ? $operations->friday_open : '09:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="friday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Friday - Closing Time</label>
                                    <input type="time" class="form-control" id="friday_close" name="friday_close" 
                                           value="<?php echo isset($operations->friday_close) ? $operations->friday_close : '18:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="saturday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Saturday - Opening Time</label>
                                    <input type="time" class="form-control" id="saturday_open" name="saturday_open" 
                                           value="<?php echo isset($operations->saturday_open) ? $operations->saturday_open : '10:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="saturday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Saturday - Closing Time</label>
                                    <input type="time" class="form-control" id="saturday_close" name="saturday_close" 
                                           value="<?php echo isset($operations->saturday_close) ? $operations->saturday_close : '17:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sunday_open" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Sunday - Opening Time</label>
                                    <input type="time" class="form-control" id="sunday_open" name="sunday_open" 
                                           value="<?php echo isset($operations->sunday_open) ? $operations->sunday_open : '10:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sunday_close" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Sunday - Closing Time</label>
                                    <input type="time" class="form-control" id="sunday_close" name="sunday_close" 
                                           value="<?php echo isset($operations->sunday_close) ? $operations->sunday_close : '17:00'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="service_notes" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Special Operating Notes</label>
                                    <textarea class="form-control" id="service_notes" name="service_notes" rows="4" 
                                              style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"><?php echo isset($operations->service_notes) ? htmlspecialchars($operations->service_notes) : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn" 
                                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px; transition: background-color 0.3s;"
                                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save Operating Hours</button>
                                    <a href="<?php echo site_url('dashboard'); ?>" class="btn" 
                                       style="background-color: #6c757d; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                                       onmouseover="this.style.backgroundColor='#5a6268'" 
                                       onmouseout="this.style.backgroundColor='#6c757d'">Back</a>
                                </div>
                            </div>
                        </form>
                        <script>
                                (function(){
                                    var loc = document.getElementById('service_location');
                                    var gpsRow = document.getElementById('gps_coords_row');
                                    var pickupSel = document.getElementById('has_pickup_dropoff');
                                    var pickupBox = document.getElementById('pickup_details_box');
                                    var form = document.querySelector('form');

                                    if (loc) loc.addEventListener('change', function(){
                                        if (loc.value === 'gps') gpsRow.style.display = 'block'; else gpsRow.style.display = 'none';
                                    });
                                    if (pickupSel) pickupSel.addEventListener('change', function(){
                                        if (pickupSel.value === '1') pickupBox.style.display = 'block'; else pickupBox.style.display = 'none';
                                    });

                                    form.addEventListener('submit', function(e){
                                        // require at least one operating area or nationwide
                                        var checked = document.querySelectorAll('input[name="operating_areas[]"]:checked');
                                        var nationwide = document.getElementById('is_nationwide');
                                        if ((checked.length === 0) && (!nationwide || !nationwide.checked)) {
                                            alert('Please select at least one Operating Area or mark Nationwide.');
                                            e.preventDefault();
                                            return false;
                                        }
                                        // emergency contact validation
                                        var en = document.getElementById('emergency_contact_name');
                                        var ep = document.getElementById('emergency_contact_phone');
                                        var ee = document.getElementById('emergency_contact_email');
                                        if (!en.value.trim() || !ep.value.trim() || !ee.value.trim()) {
                                            alert('Please fill emergency contact name, phone and email.');
                                            e.preventDefault();
                                            return false;
                                        }
                                    });
                                })();
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
