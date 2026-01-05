<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller {

    function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel');
		$this->load->model('CommonModel');
		$this->load->helper('jwt_helper');

	}


	public function driver_login() {
		if (empty($_POST)) {
			echo json_encode(['flag' => 0, 'msg' => "Please enter all mandatory fields."]);
			exit;
		}

		$email = isset($_POST['inputEmail']) ? trim($_POST['inputEmail']) : '';
		$password = isset($_POST['inputPassword']) ? trim($_POST['inputPassword']) : '';

		if (empty($email) || empty($password)) {
			echo json_encode(['flag' => 0, 'msg' => "Please enter all mandatory fields."]);
			exit;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo json_encode(['flag' => 0, 'msg' => "Please enter a valid Email address."]);
			exit;
		}

		$this->db->where('email', $email);
		$driver = $this->db->get('driver_details')->row();

		if (empty($driver)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => "Email not registered."]);
			exit;
		}

		if (isset($driver->status) && $driver->status != 1) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => "You are not allowed to login."]);
			exit;
		}
		if (!password_verify($password, $driver->password)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => "Invalid credentials."]);
			exit;
		}

		$LoginID = $driver->id;
		$LoginToken = $this->generateToken();

		$payload = [
			'LoginID' => $LoginID,
			'FirstName' => $driver->first_name,
			'Email' => $driver->email
		];
		$jwtToken = generate_jwt($payload);

		// Get client IP
		$driver_ip = $this->input->ip_address();

		// Store token in database
		$this->db->insert('driver_tokens', [
			'driver_id' => $LoginID,
			'token' => $jwtToken,
			'driver_ip' => $driver_ip,
			'status' => 0, // active
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Store JWT in session (optional)
		$sessionArr = [
			'LoginID' => $LoginID,
			'LoginToken' => $LoginToken,
			'UserRole' => 'driver',
			'JWTToken' => $jwtToken,
		];
		$this->session->set_userdata($sessionArr);

		echo json_encode([
			'flag' => 1,
			'status' => 200,
			'msg' => "Logged in successfully.",
			'token_id' => $jwtToken,
			'first_name' => $driver->first_name,
			'last_name' => $driver->last_name,
		]);
		exit;
	}

	public function get_driver_details() {

		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		
		
		// if (empty($jwtToken)) {
		// 	echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token not provided.', 'data' => []]);
		// 	exit;
		// }
		
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}
		$driver_id = $tokenRow->driver_id; // ✅ correct
		// print_r($driver_id);die;

		try {
			$data['driver_data'] = $this->CommonModel->get_driver_details($driver_id);
			if (!empty($data['driver_data']->profile_photo)) {
				$data['driver_data']->profile_photo = BASE_URL . 'admin/admin/public/images/' . $data['driver_data']->profile_photo;

			}
			echo json_encode([
				'flag' => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg' => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data' => !empty($data) ? $data : []
			]);
		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}
		exit;
	}

	public function driver_edit()
	{
		// $id = $this->input->post('id');
		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		
		
		// if (empty($jwtToken)) {
		// 	echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token not provided.', 'data' => []]);
		// 	exit;
		// }
		
		$tokenRow = $this->db->where('token', $jwtToken)
                     ->where('status', 0)
                     ->get('driver_tokens')
                     ->row();

		// ❌ If no active token found → invalid/expired token
		if (!$tokenRow) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'Token expired or invalid.',
				'data' => []
			]);
			exit;
		}

		$first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');
		$mobile_no = $this->input->post('mobile_no');
		// die;

		// Prepare data for insertion
		$data = [
			'id' => $tokenRow->driver_id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'mobile_no' => $mobile_no,
		];

		// Insert trip into the database
		$res = $this->CommonModel->driver_edit($data);
		// echo $this->db->last_Query();die();
		// echo $res;die;
		if ($res) {
			echo json_encode(['flag' => 1,'status' => 200, 'msg' => 'Profile updated successfully!']);
		} else {
			echo json_encode(['flag' => 0,'status' => 200, 'msg' => 'Unable to update profile. Please try again.']);
		}

		exit;
	}

	public function driver_edit_documents()
	{
		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		
		
		// if (empty($jwtToken)) {
		// 	echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token not provided.', 'data' => []]);
		// 	exit;
		// }
		
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		// ❌ If no active token found → invalid/expired token
		if (!$tokenRow) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'Token expired or invalid.',
				'data' => []
			]);
			exit;
		}


		$id = $tokenRow->driver_id;
		
		$profile_photo = null;

    	// ✅ Handle Base64 Image
		$base64_image = $this->input->post('profile_photo');
		if (!empty($base64_image)) {
			$upload_path = SIS_SERVER_PATH . 'admin/admin/public/images/';

			// Ensure directory exists
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0777, true);
			}

			// Extract the base64 string if it includes the data URI prefix
			if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
				$image_type = strtolower($type[1]); // jpg, png, gif, etc.
				$base64_image = substr($base64_image, strpos($base64_image, ',') + 1);
			} else {
				$image_type = 'jpg'; // default fallback
			}

			$base64_image = str_replace(' ', '+', $base64_image);
			$decoded_image = base64_decode($base64_image);

			if ($decoded_image === false) {
				echo json_encode(['flag' => 0, 'status' => 400, 'msg' => 'Invalid base64 image data.']);
				exit;
			}

			// Generate unique filename
			$file_name = uniqid('driver_', true) . '.' . $image_type;
			$file_path = $upload_path . $file_name;

			// Save the image
			if (file_put_contents($file_path, $decoded_image)) {
				$profile_photo = $file_name;
			} else {
				echo json_encode(['flag' => 0, 'status' => 400, 'msg' => 'Failed to save image file.']);
				exit;
			}
		}
		// Update database
		$updateData = [
			'profile_photo' => $profile_photo,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		$this->db->where('id', $id);
		$update = $this->db->update('driver_details', $updateData);

		if ($update) {
			echo json_encode(['flag' => 1, 'status' => 200, 'msg' => 'Profile image Updated Successfully!']);
		} else {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Unable to update profile image. Please try again.']);
		}
		exit;
	}


	public function get_pickup_listing() {
		// Get JWT token from POST or GET
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$date = $this->input->post('date');

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token is required.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			// Get pickup listing with merchant info
			$data = $this->CommonModel->get_pickup_listing($driver_id, $date);

			// Function to get lat/lng from address (Google Maps API)
			// function getLatLng($address) {
			// 	$apiKey = 'YOUR_GOOGLE_MAPS_API_KEY';
			// 	$address = urlencode($address);
			// 	$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

			// 	$resp_json = file_get_contents($url);
			// 	$resp = json_decode($resp_json, true);

			// 	if($resp['status'] == 'OK'){
			// 		return [
			// 			'lat' => $resp['results'][0]['geometry']['location']['lat'],
			// 			'lng' => $resp['results'][0]['geometry']['location']['lng']
			// 		];
			// 	}
			// 	return ['lat' => null, 'lng' => null];
			// }

			// // Add lat/lng to each merchant entry
			// if(!empty($data)){
			// 	foreach($data as &$pickup){
			// 		$coords = getLatLng($pickup->company_address);
			// 		$pickup->lat = $coords['lat'];
			// 		$pickup->lng = $coords['lng'];
			// 	}
			// }

			echo json_encode([
				'flag' => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg' => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data' => !empty($data) ? $data : []
			]);

		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}
		exit;
	}


	public function get_delivery_listing() {

		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		$date = $this->input->post('date');
		
		
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}
		$driver_id = $tokenRow->driver_id; // ✅ correct
		// print_r($driver_id);die;

		try {
			$data = $this->CommonModel->get_delivery_listing($driver_id,$date);
			echo json_encode([
				'flag' => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg' => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data' => !empty($data) ? $data : []
			]);
		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}
		exit;
	}
	public function get_pickup_order_details() {

		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		$order_id = $this->input->post('order_id');
		
		
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}
		$driver_id = $tokenRow->driver_id; // ✅ correct
		// print_r($driver_id);die;

		try {
			$data = $this->CommonModel->get_pickup_order_details($driver_id,$order_id);
			echo json_encode([
				'flag' => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg' => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data' => !empty($data) ? $data : []
			]);
		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}
		exit;
	}

	public function get_delivery_order_details() {

		if (empty($jwtToken)) {
			$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		}
		$order_id = $this->input->post('order_id');
		$is_parent_level = $this->input->post('is_parent_level');

		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}
		$driver_id = $tokenRow->driver_id; // ✅ correct
		// print_r($driver_id);die;

		try {
			$data = $this->CommonModel->get_delivery_order_details($driver_id,$order_id,$is_parent_level);
			echo json_encode([
				'flag' => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg' => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data' => !empty($data) ? $data : []
			]);
		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}
		exit;
	}

	public function get_today_route()
	{
		// ✅ Get token
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$date     = $this->input->post('date');

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 400, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// ✅ Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 401, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			$data = [];

			$data = $this->CommonModel->get_today_route($driver_id, $date);
			// ✅ Fetch route data depending on type

			echo json_encode([
				'flag'   => !empty($data) ? 1 : 0,
				'status' => 200,
				'msg'    => !empty($data) ? 'Data retrieved successfully.' : 'No data found.',
				'data'   => !empty($data) ? $data : []
			]);

		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 500,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}

		exit;
	}

	public function update_order_status()
	{
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$date     = $this->input->post('date');           
		$type     = strtolower(trim($this->input->post('type'))); // 'pickup' or 'delivery'
		$status   = $this->input->post('status');        
		$order_id = $this->input->post('order_id');      
		$is_parent_level = $this->input->post('is_parent_level'); 

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			// Determine table and status field
			if ($type === 'pickup') {
				$table = 'b2b_orders_pickup_details';
				$status_field = 'pickup_status';
			} elseif ($type === 'delivery') {
				$table = 'b2b_orders_delivery_details';
				$status_field = 'delivery_status';
			} else {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Invalid type. Must be pickup or delivery.', 'data' => []]);
				exit;
			}

			// Build update data
			$updateData = [
				$status_field => $status,
				'updated_at'  => date('Y-m-d H:i:s')
			];

			// Set WHERE conditions
			$this->db->where('driver_id', $driver_id);

			if (!empty($order_id)) {
				if ($type === 'delivery' && $is_parent_level == 1) {
					$this->db->where('webshop_order_id', $order_id);
				} else {
					$this->db->where('order_id', $order_id);
				}
			}

			if (!empty($date)) {
				$converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
				$this->db->where('DATE(created_at)', $converted_date);
			}

			// Execute update
			$this->db->update($table, $updateData);
			$affected = $this->db->affected_rows();

			if ($affected > 0) {
				echo json_encode([
					'flag' => 1,
					'status' => 200,
					'msg' => ucfirst($type) . ' status updated successfully.',
					'data' => [
						'status' => $status,
						'orders_updated_count' => $affected,
						'order_id' => $order_id ?? 'all'
					]
				]);
			} else {
				echo json_encode([
					'flag' => 0,
					'status' => 200,
					'msg' => 'No records updated. Please check order ID, date, or driver assignment.',
					'data' => []
				]);
			}

		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
				'data' => []
			]);
		}

		exit;
	}

	public function pickup_proof_image_upload_details()
	{
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$image    = $this->input->post('image'); // base64 encoded image from POST
		$order_id = $this->input->post('order_id'); // optional

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
			->where('status', 0)
			->get('driver_tokens')
			->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			$updateData = [];

			if (!empty($image)) {

				// Paths for main image and thumbnail
				$mainPath1 = SIS_SERVER_PATH . 'admin/admin/public/images/pickup/';
				$mainPath2 = SIS_SERVER_PATH . 'admin/admin/public/images/pickup/thumbnail/';

				// Make directories if not exist
				if (!is_dir($mainPath1)) mkdir($mainPath1, 0777, true);
				if (!is_dir($mainPath2)) mkdir($mainPath2, 0777, true);

				// Check and decode base64
				if (strpos($image, 'data:image') === 0) {
					preg_match('/^data:image\/(\w+);base64,/', $image, $match);
					$ext = isset($match[1]) ? $match[1] : 'jpg';
					$image = preg_replace('/^data:image\/(\w+);base64,/', '', $image);
					$image = base64_decode($image);
				} else {
					$ext = 'jpg'; // fallback
					$image = base64_decode($image);
				}

				if ($image === false) {
					log_message('error', 'Failed to decode base64 image');
					return;
				}

				// Generate filenames
				$fileName1 = 'pickup_' . $driver_id . '_' . time() . '.' . $ext;
				$fileName2 = 'pickup_' . $driver_id . '_' . time() . '_thumb.' . $ext;

				// Full paths
				$filePath1 = $mainPath1 . $fileName1;
				$filePath2 = $mainPath2 . $fileName2;

				// Save main image
				if (!file_put_contents($filePath1, $image)) {
					log_message('error', 'Failed to save main image: ' . $filePath1);
				}

				// Save same image to thumbnail folder
				if (!file_put_contents($filePath2, $image)) {
					log_message('error', 'Failed to save thumbnail image: ' . $filePath2);
				}

				// Resize thumbnail
				$config['image_library'] = 'gd2';
				$config['source_image'] = $filePath2;
				$config['new_image'] = $filePath2;
				$config['maintain_ratio'] = TRUE;
				$config['width'] = 150;
				$config['height'] = 150;

				$this->load->library('image_lib', $config);
				if (!$this->image_lib->resize()) {
					log_message('error', 'Thumbnail resize failed: ' . $this->image_lib->display_errors());
				}
				$this->image_lib->clear();

				// Update DB
				$updateData['image'] = $fileName1;
				$updateData['thumbnail'] = $fileName2;
			}


			$updateData['updated_at'] = date('Y-m-d H:i:s');

			if (empty($updateData)) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'No data to update.', 'data' => []]);
				exit;
			}

			$this->db->where('driver_id', $driver_id);
			$this->db->where('order_id', $order_id);
			$this->db->update('b2b_orders_pickup_details', $updateData);
			$affected = $this->db->affected_rows();

			if ($affected > 0) {
				// echo $fileName1;
				// echo $fileName2;
				// echo BASE_URL . 'admin/admin/public/images/pickup/' . $fileName1;
				// echo BASE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $fileName2;
				// die();
				echo json_encode([
					'flag' => 1,
					'status' => 200,
					'msg' => 'Uploaded Pickup image successfully.',
					'image_url' => BASE_URL . 'admin/admin/public/images/pickup/' . $fileName1,
					'thumb_url' => BASE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $fileName2
				]);
			} else {
				echo json_encode([
					'flag' => 0,
					'status' => 200,
					'msg' => 'Unable to upload image. Please try again.',
				]);
			}
		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
			]);
		}

		exit;
	}
	public function delivery_image_upload_details()
	{
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$image   = $this->input->post('image'); // image file name or path
		$order_id = $this->input->post('order_id'); // required
		$is_parent_level = $this->input->post('is_parent_level'); // optional

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			$updateData = [];
			if (!empty($image)) {

				// Paths for main image and thumbnail
				$mainPath1 = SIS_SERVER_PATH . 'admin/admin/public/images/delivery/';
				$mainPath2 = SIS_SERVER_PATH . 'admin/admin/public/images/delivery/thumbnail/';

				// Make directories if not exist
				if (!is_dir($mainPath1)) mkdir($mainPath1, 0777, true);
				if (!is_dir($mainPath2)) mkdir($mainPath2, 0777, true);

				// Check and decode base64
				if (strpos($image, 'data:image') === 0) {
					preg_match('/^data:image\/(\w+);base64,/', $image, $match);
					$ext = isset($match[1]) ? $match[1] : 'jpg';
					$image = preg_replace('/^data:image\/(\w+);base64,/', '', $image);
					$image = base64_decode($image);
				} else {
					$ext = 'jpg'; // fallback
					$image = base64_decode($image);
				}

				if ($image === false) {
					log_message('error', 'Failed to decode base64 image');
					return;
				}

				// Generate filenames
				$fileName1 = 'delivery_' . $driver_id . '_' . time() . '.' . $ext;
				$fileName2 = 'delivery_' . $driver_id . '_' . time() . '_thumb.' . $ext;

				// Full paths
				$filePath1 = $mainPath1 . $fileName1;
				$filePath2 = $mainPath2 . $fileName2;

				// Save main image
				if (!file_put_contents($filePath1, $image)) {
					log_message('error', 'Failed to save main image: ' . $filePath1);
				}

				// Save same image to thumbnail folder
				if (!file_put_contents($filePath2, $image)) {
					log_message('error', 'Failed to save thumbnail image: ' . $filePath2);
				}

				// Resize thumbnail
				$config['image_library'] = 'gd2';
				$config['source_image'] = $filePath2;
				$config['new_image'] = $filePath2;
				$config['maintain_ratio'] = TRUE;
				$config['width'] = 150;
				$config['height'] = 150;

				$this->load->library('image_lib', $config);
				if (!$this->image_lib->resize()) {
					log_message('error', 'Thumbnail resize failed: ' . $this->image_lib->display_errors());
				}
				$this->image_lib->clear();

				// Update DB
				$updateData['image'] = $fileName1;
				$updateData['thumbnail'] = $fileName2;
			}

			$updateData['updated_at'] = date('Y-m-d H:i:s');

			if (empty($updateData)) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'No data to update.', 'data' => []]);
				exit;
			}

			// Set correct WHERE based on parent level
			if ($is_parent_level == 1) {
				$this->db->where('webshop_order_id', $order_id);
			} else {
				$this->db->where('order_id', $order_id);
			}

			// First check if there's a row with delivery_status = 3
			$this->db->where('driver_id', $driver_id);
			$this->db->where('delivery_status', 3);
			$this->db->from('b2b_orders_delivery_details');
			$exists_status3 = $this->db->count_all_results();

			if ($exists_status3 > 0) {
				// If status=3 exists, update image there
				if ($is_parent_level == 1) {
					$this->db->where('webshop_order_id', $order_id);
				} else {
					$this->db->where('order_id', $order_id);
				}
				$this->db->where('delivery_status', 3);
			} else {
				// Otherwise, update image in status=1 row
				if ($is_parent_level == 1) {
					$this->db->where('webshop_order_id', $order_id);
				} else {
					$this->db->where('order_id', $order_id);
				}
				$this->db->where('delivery_status', 1);
			}

			$this->db->update('b2b_orders_delivery_details', $updateData);
			$affected = $this->db->affected_rows();

			if ($affected > 0) {
				echo json_encode([
					'flag' => 1,
					'status' => 200,
					'msg' => 'Delivery image uploaded successfully.',
					'image_url' => BASE_URL . 'admin/admin/public/images/delivery/' . $fileName1,
					'thumb_url' => BASE_URL . 'admin/admin/public/images/delivery/thumbnail/' . $fileName2
				]);
			} else {
				echo json_encode([
					'flag' => 0,
					'status' => 200,
					'msg' => 'Unable to upload image. Please try again.',
				]);
			}


		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
			]);
		}

		exit;
	}
	public function pickup_image_upload_details()
	{
		$jwtToken   = $this->input->post('token_id') ?? $this->input->get('token_id');
		$image      = $this->input->post('image'); // base64 image
		$order_id   = $this->input->post('order_id'); 
		$product_id = $this->input->post('product_id'); 

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			$fileName1 = '';
			$fileName2 = '';

			if (!empty($image)) {
				// Paths
				$mainPath1 = SIS_SERVER_PATH . 'admin/admin/public/images/pickup/';
				$mainPath2 = SIS_SERVER_PATH . 'admin/admin/public/images/pickup/thumbnail/';
				if (!is_dir($mainPath1)) mkdir($mainPath1, 0777, true);
				if (!is_dir($mainPath2)) mkdir($mainPath2, 0777, true);

				// Decode base64
				if (strpos($image, 'data:image') === 0) {
					preg_match('/^data:image\/(\w+);base64,/', $image, $match);
					$ext = isset($match[1]) ? $match[1] : 'jpg';
					$image = preg_replace('/^data:image\/(\w+);base64,/', '', $image);
				} else {
					$ext = 'jpg';
				}
				$image = base64_decode($image);

				if ($image === false) {
					echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Invalid base64 image.']);
					exit;
				}

				$timestamp = time();
				$fileName1 = "pickup_{$driver_id}_{$timestamp}.{$ext}";
				$fileName2 = "pickup_{$driver_id}_{$timestamp}_thumb.{$ext}";
				file_put_contents($mainPath1 . $fileName1, $image);
				file_put_contents($mainPath2 . $fileName2, $image);

				// Resize thumbnail
				$config = [
					'image_library' => 'gd2',
					'source_image'  => $mainPath2 . $fileName2,
					'new_image'     => $mainPath2 . $fileName2,
					'maintain_ratio'=> TRUE,
					'width'         => 150,
					'height'        => 150
				];
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				$this->image_lib->clear();
			}

			// Find pickup detail
			$pickupDetail = $this->db->where('driver_id', $driver_id)
									->where('order_id', $order_id)
									->get('b2b_orders_pickup_details')
									->row();

			if (!$pickupDetail) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Pickup detail not found for this order.']);
				exit;
			}

			// Decode product_details
			$productDetails = json_decode($pickupDetail->product_details, true);
			if (!is_array($productDetails)) $productDetails = [];

			// Update product_id details
			if (!isset($productDetails[$product_id])) {
				$productDetails[$product_id] = [];
			}

			$productDetails[$product_id]['pickup_image'] = $fileName1;
			$productDetails[$product_id]['pickup_thumb'] = $fileName2;
			$productDetails[$product_id]['updated_at']   = date('Y-m-d H:i:s');

			// Update DB
			$updateData = [
				'product_details' => json_encode($productDetails, JSON_UNESCAPED_SLASHES),
				'updated_at'      => date('Y-m-d H:i:s')
			];

			$this->db->where('id', $pickupDetail->id);
			$success = $this->db->update('b2b_orders_pickup_details', $updateData);

			if ($success) {
				echo json_encode([
					'flag'      => 1,
					'status'    => 200,
					'msg'       => 'Uploaded pickup image successfully.',
					'image_url' => BASE_URL . 'admin/admin/public/images/pickup/' . $fileName1,
					'thumb_url' => BASE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $fileName2
				]);
			} else {
				// Log query for debugging
				log_message('error', 'DB Update failed: ' . $this->db->last_query());
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Failed to update pickup image.']);
			}

		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'Error: ' . $e->getMessage()
			]);
		}

		exit;
	}

	public function update_failed_attempt()
	{
		$jwtToken = $this->input->post('token_id') ?? $this->input->get('token_id');
		$reason   = $this->input->post('reason'); 
		$order_id = $this->input->post('order_id'); 
		$is_parent_level = $this->input->post('is_parent_level'); 

		if (empty($jwtToken)) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token missing.', 'data' => []]);
			exit;
		}

		// Validate token
		$tokenRow = $this->db->where('token', $jwtToken)
							->where('status', 0)
							->get('driver_tokens')
							->row();

		if (!$tokenRow) {
			echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Token inactive or not found.', 'data' => []]);
			exit;
		}

		$driver_id = $tokenRow->driver_id;

		try {
			$updateData = [];
			if (!empty($reason)) {
				$updateData['reason_for_attempt_failed'] = $reason;
			}
			$updateData['updated_at'] = date('Y-m-d H:i:s');



			if (empty($updateData)) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'No data to update.', 'data' => []]);
				exit;
			}

			// Get latest delivery_attempt_no
			$this->db->select_max('delivery_attempt_no', 'latest_attempt');
			if ($is_parent_level == 1) {
				$this->db->where('webshop_order_id', $order_id);
			} else {
				$this->db->where('order_id', $order_id);
			}
			$this->db->where('driver_id', $driver_id);
			$query = $this->db->get('b2b_orders_delivery_details');
			$row = $query->row();

			if (!$row || !$row->latest_attempt) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'No delivery attempt found.', 'data' => []]);
				exit;
			}

			$latest_attempt = $row->latest_attempt;

			// Map delivery_attempt_no to allowed delivery_status
			$allowed_statuses = [];
			if ($latest_attempt == 1) {
				$new_status = 2;
				$allowed_statuses = [1, 2]; // shipped or failed attempt 1
			} elseif ($latest_attempt == 2) {
				$allowed_statuses = [3, 4]; // attempt 2 or failed attempt 2
				$new_status = 4;
			} elseif ($latest_attempt == 3) {
				$allowed_statuses = [5, 6]; // attempt 3 or failed attempt 3
				$new_status = 6;
			}
			$updateData['delivery_status'] = $new_status;


			if (empty($allowed_statuses)) {
				echo json_encode(['flag' => 0, 'status' => 200, 'msg' => 'Invalid attempt number.', 'data' => []]);
				exit;
			}

			// Update the latest attempt row
			$this->db->set($updateData);
			if ($is_parent_level == 1) {
				$this->db->where('webshop_order_id', $order_id);
			} else {
				$this->db->where('order_id', $order_id);
			}
			$this->db->where('driver_id', $driver_id);
			$this->db->where('delivery_attempt_no', $latest_attempt);
			$this->db->where_in('delivery_status', $allowed_statuses);

			$this->db->update('b2b_orders_delivery_details');
			$affected = $this->db->affected_rows();

			if ($affected > 0) {
				echo json_encode([
					'flag' => 1,
					'status' => 200,
					'msg' => 'Failed attempt reason updated successfully.',
				]);
			} else {
				echo json_encode([
					'flag' => 0,
					'status' => 200,
					'msg' => 'Unable to update. Please check the order or attempt.',
				]);
			}

		} catch (Exception $e) {
			echo json_encode([
				'flag' => 0,
				'status' => 200,
				'msg' => 'An error occurred: ' . $e->getMessage(),
			]);
		}

		exit;
	}
	public function driver_logout() {
		$jwtToken = $this->input->post('token_id') ?? '';

		if (empty($jwtToken)) {
			echo json_encode(['flag'=>0, 'msg'=>'Token not provided']);
			exit;
		}

		$this->db->where('token', $jwtToken)
				->update('driver_tokens', ['status' => 1]); // mark inactive

		// Destroy session
		$this->session->unset_userdata(['LoginID','LoginToken','UserRole','JWTToken']);

		echo json_encode(['flag'=>1, 'status' => 200, 'msg'=>'Logged out successfully']);
		exit;
	}
	private function generateToken($length = 20)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
