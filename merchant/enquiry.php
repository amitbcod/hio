<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// echo __DIR__;
// exit;


// require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
// require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
// require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';


header("Content-Type: application/json");

// CORS headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "statusCode" => 405,
        "error" => [
            "type" => "NOT_ALLOWED",
            "description" => "Method not allowed. Must be POST"
        ]
    ]);
    exit;
}

// Try to read raw JSON
$rawData = file_get_contents("php://input");
$input   = json_decode($rawData, true);

// If JSON not sent, fall back to $_POST
if (!$input || empty($input)) {
    $input = $_POST;
}

// Still no data?
if (empty($input)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No input received"]);
    exit;
}

// DB credentials
$host     = "localhost";
$username = "whusovzv_ymstoreuser";
$password = "-wln]6jtSPt3";
$dbname   = "whusovzv_ymstore";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Collect data (example values, replace with $_POST or $input as needed)
$publication_name = $input['publication_name'] ?? '';
$vendor_name      = $input['vendor_name'] ?? '';
$email            = $input['email'] ?? '';
$password_hash    = $input['password'] ?? md5('Merchant@yellowmarket1');
$phone_no         = $input['phone_no'] ?? '';
$merchant_cat_id  = $input['merchant_cat_id'];
$created_at       = strtotime(date('Y-m-d H:i:s'));

// Escape strings to prevent SQL injection

// Build the SQL query
$sql = "INSERT INTO publisher (publication_name, vendor_name, email, password, phone_no, merchant_cat, created_at) 
    VALUES ('$publication_name', '$vendor_name', '$email', '$password_hash', '$phone_no', '$merchant_cat_id', '$created_at')";
// echo $sql;die;
// Execute the query
if ($conn->query($sql) === TRUE) {
   
    $insertId = $conn->insert_id;

    // PHPMailer configuration
// Load CI Email library
$this->load->library('email');

// SMTP configuration
$config = [
    'protocol'  => 'smtp',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'yellowmarketmu@gmail.com',
    'smtp_pass' => 'qkctneqedqoujxfp', // Gmail App Password
    'smtp_crypto' => 'tls',  // or use 'ssl' with port 465
    'mailtype'  => 'html',
    'charset'   => 'utf-8',
    'newline'   => "\r\n",
    'crlf'      => "\r\n",
];

$this->email->initialize($config);

// Email details
$this->email->from('yellowmarketmu@gmail.com', 'Yellow Markets');
$this->email->to('snehals@bcod.co.in');
$this->email->subject('Test Email');
$this->email->message('Hello! This is a test email using CodeIgniter Email library (without PHPMailer).');

// Send & check
if ($this->email->send()) {
    echo json_encode(["status" => "success", "message" => "Email sent"]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => $this->email->print_debugger()
    ]);
}
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
