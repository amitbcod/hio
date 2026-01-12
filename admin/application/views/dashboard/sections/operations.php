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
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">Service Operations</h4>
                    <p class="text-muted mb-0">Operating hours and service details</p>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                    <h5 class="mb-3">Service Details</h5>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="service_location" class="form-label">Service Location <span class="text-danger">*</span></label>
                                            <select id="service_location" name="service_location" class="form-control" required>
                                                <option value="">-- select --</option>
                                                <option value="fixed" <?php echo (isset($operations->service_location) && $operations->service_location == 'fixed') ? 'selected' : ''; ?>>Fixed Location</option>
                                                <option value="gps" <?php echo (isset($operations->is_gps_location) && $operations->is_gps_location) ? 'selected' : ''; ?>>GPS / Mobile</option>
                                                <option value="multiple" <?php echo (isset($operations->service_location) && $operations->service_location == 'multiple') ? 'selected' : ''; ?>>Multiple Locations</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3" id="gps_coords_row" style="display: <?php echo (isset($operations->is_gps_location) && $operations->is_gps_location) ? 'block' : 'none'; ?>;">
                                            <label for="gps_coordinates" class="form-label">GPS Coordinates (lat,lng)</label>
                                            <input type="text" id="gps_coordinates" name="gps_coordinates" class="form-control" value="<?php echo isset($operations->gps_coordinates) ? $operations->gps_coordinates : ''; ?>" placeholder="e.g. -20.1609,57.5012">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label class="form-label">Operating Area <span class="text-danger">*</span></label>
                                            <div class="form-check">
                                                <?php $regions = array('North','South','East','West','Central');
                                                $selected_areas = array();
                                                if (!empty($operations->operating_areas)) {
                                                    $selected_areas = is_array($operations->operating_areas) ? $operations->operating_areas : json_decode($operations->operating_areas, TRUE);
                                                }
                                                foreach ($regions as $r): ?>
                                                    <label class="mr-3"><input type="checkbox" name="operating_areas[]" value="<?php echo $r; ?>" <?php echo in_array($r, $selected_areas) ? 'checked' : ''; ?>> <?php echo $r; ?></label>
                                                <?php endforeach; ?>
                                                <label class="ml-3"><input type="checkbox" id="is_nationwide" name="is_nationwide" value="1" <?php echo (isset($operations->is_nationwide) && $operations->is_nationwide) ? 'checked' : ''; ?>> Nationwide</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label for="has_pickup_dropoff" class="form-label">Pickup / Drop-off Options</label>
                                            <select id="has_pickup_dropoff" name="has_pickup_dropoff" class="form-control">
                                                <option value="0" <?php echo (empty($operations->has_pickup_dropoff) || !$operations->has_pickup_dropoff) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo (!empty($operations->has_pickup_dropoff) && $operations->has_pickup_dropoff) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6" id="pickup_details_box" style="display: <?php echo (!empty($operations->has_pickup_dropoff) && $operations->has_pickup_dropoff) ? 'block' : 'none'; ?>;">
                                            <label class="form-label">Pickup / Drop-off Details</label>
                                            <div class="form-group">
                                                <input type="number" step="0.01" name="pickup_dropoff_surcharge" id="pickup_dropoff_surcharge" class="form-control mb-2" placeholder="Surcharge amount" value="<?php echo isset($operations->pickup_dropoff_surcharge) ? $operations->pickup_dropoff_surcharge : ''; ?>">
                                                <label><input type="checkbox" name="pickup_dropoff_free" value="1" <?php echo (!empty($operations->pickup_dropoff_free)) ? 'checked' : ''; ?>> Free Of Charge</label>
                                                <textarea name="pickup_dropoff_details" class="form-control mt-2" placeholder="Additional details"><?php echo isset($operations->pickup_dropoff_details) ? htmlspecialchars($operations->pickup_dropoff_details) : ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="emergency_contact_name" class="form-label">Emergency Contact <span class="text-danger">*</span></label>
                                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" required value="<?php echo isset($operations->emergency_contact_name) ? $operations->emergency_contact_name : ''; ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="emergency_contact_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" required value="<?php echo isset($operations->emergency_contact_phone) ? $operations->emergency_contact_phone : ''; ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="emergency_contact_email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" id="emergency_contact_email" name="emergency_contact_email" class="form-control" required value="<?php echo isset($operations->emergency_contact_email) ? $operations->emergency_contact_email : ''; ?>">
                                        </div>
                                    </div>

                                    <hr>
                                <h5 class="mb-3">Operating Hours & Schedule</h5>

                                <div class="alert alert-info">
                                    <h6>Service Operations Configuration</h6>
                                    <p class="mb-0">Set your operating hours, service availability, and operational policies below.</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="monday_open" class="form-label">Monday - Opening Time</label>
                                        <input type="time" class="form-control" id="monday_open" name="monday_open" 
                                               value="<?php echo isset($operations->monday_open) ? $operations->monday_open : '09:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="monday_close" class="form-label">Monday - Closing Time</label>
                                        <input type="time" class="form-control" id="monday_close" name="monday_close" 
                                               value="<?php echo isset($operations->monday_close) ? $operations->monday_close : '18:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tuesday_open" class="form-label">Tuesday - Opening Time</label>
                                        <input type="time" class="form-control" id="tuesday_open" name="tuesday_open" 
                                               value="<?php echo isset($operations->tuesday_open) ? $operations->tuesday_open : '09:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tuesday_close" class="form-label">Tuesday - Closing Time</label>
                                        <input type="time" class="form-control" id="tuesday_close" name="tuesday_close" 
                                               value="<?php echo isset($operations->tuesday_close) ? $operations->tuesday_close : '18:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="wednesday_open" class="form-label">Wednesday - Opening Time</label>
                                        <input type="time" class="form-control" id="wednesday_open" name="wednesday_open" 
                                               value="<?php echo isset($operations->wednesday_open) ? $operations->wednesday_open : '09:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="wednesday_close" class="form-label">Wednesday - Closing Time</label>
                                        <input type="time" class="form-control" id="wednesday_close" name="wednesday_close" 
                                               value="<?php echo isset($operations->wednesday_close) ? $operations->wednesday_close : '18:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="thursday_open" class="form-label">Thursday - Opening Time</label>
                                        <input type="time" class="form-control" id="thursday_open" name="thursday_open" 
                                               value="<?php echo isset($operations->thursday_open) ? $operations->thursday_open : '09:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="thursday_close" class="form-label">Thursday - Closing Time</label>
                                        <input type="time" class="form-control" id="thursday_close" name="thursday_close" 
                                               value="<?php echo isset($operations->thursday_close) ? $operations->thursday_close : '18:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="friday_open" class="form-label">Friday - Opening Time</label>
                                        <input type="time" class="form-control" id="friday_open" name="friday_open" 
                                               value="<?php echo isset($operations->friday_open) ? $operations->friday_open : '09:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="friday_close" class="form-label">Friday - Closing Time</label>
                                        <input type="time" class="form-control" id="friday_close" name="friday_close" 
                                               value="<?php echo isset($operations->friday_close) ? $operations->friday_close : '18:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="saturday_open" class="form-label">Saturday - Opening Time</label>
                                        <input type="time" class="form-control" id="saturday_open" name="saturday_open" 
                                               value="<?php echo isset($operations->saturday_open) ? $operations->saturday_open : '10:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="saturday_close" class="form-label">Saturday - Closing Time</label>
                                        <input type="time" class="form-control" id="saturday_close" name="saturday_close" 
                                               value="<?php echo isset($operations->saturday_close) ? $operations->saturday_close : '17:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="sunday_open" class="form-label">Sunday - Opening Time</label>
                                        <input type="time" class="form-control" id="sunday_open" name="sunday_open" 
                                               value="<?php echo isset($operations->sunday_open) ? $operations->sunday_open : '10:00'; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="sunday_close" class="form-label">Sunday - Closing Time</label>
                                        <input type="time" class="form-control" id="sunday_close" name="sunday_close" 
                                               value="<?php echo isset($operations->sunday_close) ? $operations->sunday_close : '17:00'; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="service_notes" class="form-label">Special Operating Notes</label>
                                        <textarea class="form-control" id="service_notes" name="service_notes" rows="4"><?php echo isset($operations->service_notes) ? htmlspecialchars($operations->service_notes) : ''; ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-info">Save Operating Hours</button>
                                        <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-secondary">Back</a>
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
