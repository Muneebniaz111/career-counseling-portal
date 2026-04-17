<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// CORS: Only allow same-origin or specific trusted domains
$allowed_origins = [
    'https://portal.example.com',
    'https://www.portal.example.com',
    $_SERVER['HTTP_HOST'] ?? ''  // Allow current host during development
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins) || stripos($origin, 'localhost') !== false || stripos($origin, '127.0.0.1') !== false) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

$response = array('success' => false, 'message' => '');

try {
    // 1. VALIDATE REQUEST METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // 2. VALIDATE CSRF TOKEN (if user is logged in)
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    if ($user_id) {
        $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        if (!validateCsrfToken($csrf_token)) {
            throw new Exception("CSRF validation failed");
        }
    }

    // 3. SANITIZE AND VALIDATE INPUT
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $subject = trim(isset($_POST['subject']) ? $_POST['subject'] : '');
    $message = trim(isset($_POST['message']) ? $_POST['message'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');

    // Remove null bytes and control characters
    $name = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $name);
    $email = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $email);
    $subject = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $subject);
    $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $message);
    $phone = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $phone);

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new Exception("Please fill in all required fields.");
    }
    
    // Validate name (2-100 characters)
    if (strlen($name) < 2 || strlen($name) > 100) {
        throw new Exception("Name must be 2-100 characters long.");
    }
    
    // Validate email
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email address format.");
    }
    
    // Validate subject (3-255 characters)
    if (strlen($subject) < 3 || strlen($subject) > 255) {
        throw new Exception("Subject must be 3-255 characters long.");
    }
    
    // Validate message (10-5000 characters)
    if (strlen($message) < 10 || strlen($message) > 5000) {
        throw new Exception("Message must be 10-5000 characters long.");
    }

    // Validate phone (optional but if provided, validate format)
    if (!empty($phone)) {
        if (!preg_match('/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $phone)) {
            throw new Exception("Invalid phone number format.");
        }
    }

    // 4. CHECK DATABASE CONNECTION
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // 5. CHECK IF TABLE EXISTS
    try {
        $table_check = $conn->query("SELECT 1 FROM contact_messages LIMIT 1");
        if ($table_check === false) {
            throw new Exception("Contact messages table not found.");
        }
    } catch (mysqli_sql_exception $e) {
        throw new Exception("Database schema error");
    }

    // 6. CHECK WHICH COLUMNS ARE AVAILABLE
    $available_columns = ['name', 'email', 'subject', 'message', 'phone'];
    $has_user_id = false;
    $has_status = false;
    
    try {
        $result = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='contact_messages' AND TABLE_SCHEMA=DATABASE()");
        if ($result) {
            while ($col = $result->fetch_assoc()) {
                $col_name = $col['COLUMN_NAME'];
                if ($col_name === 'user_id') $has_user_id = true;
                if ($col_name === 'status') $has_status = true;
            }
        }
    } catch (Exception $e) {
        // Default to assuming columns exist
        $has_user_id = true;
        $has_status = true;
    }

    // 7. BUILD AND EXECUTE INSERT STATEMENT
    $bind_types = '';
    $bind_values = [];

    // Prepare the parameters in correct order
    if ($has_user_id) {
        $bind_types .= 'i';
        $bind_values[] = $user_id;
    }
    
    $bind_types .= 'sssss'; // name, email, subject, message, phone
    $bind_values[] = $name;
    $bind_values[] = $email;
    $bind_values[] = $subject;
    $bind_values[] = $message;
    $bind_values[] = $phone;

    if ($has_status) {
        $bind_types .= 's';
        $bind_values[] = 'open';
    }

    // Build column list
    $insert_columns = [];
    if ($has_user_id) $insert_columns[] = 'user_id';
    $insert_columns[] = 'name';
    $insert_columns[] = 'email';
    $insert_columns[] = 'subject';
    $insert_columns[] = 'message';
    $insert_columns[] = 'phone';
    if ($has_status) {
        $insert_columns[] = 'status';
        $insert_columns[] = 'created_at';
    }

    // Build placeholders
    $placeholders = array_fill(0, count($insert_columns), '?');
    
    // Handle created_at specially
    if ($has_status && in_array('created_at', $insert_columns)) {
        $last_key = array_key_last($placeholders);
        $placeholders[$last_key] = 'NOW()';
    }

    // Build INSERT statement
    $insert_sql = "INSERT INTO contact_messages (" . implode(',', $insert_columns) . ") VALUES (" . implode(',', $placeholders) . ")";

    // Prepare and execute statement
    $stmt = $conn->prepare($insert_sql);
    
    if ($stmt === false) {
        error_log("Statement preparation failed: " . $conn->error);
        throw new Exception("An error occurred while processing your request. Please try again later.");
    }
    
    // Bind parameters
    if (!empty($bind_values)) {
        $bind_vars = [];
        foreach ($bind_values as &$value) {
            $bind_vars[] = &$value;
        }
        unset($value);
        
        if (!$stmt->bind_param($bind_types, ...$bind_vars)) {
            error_log("Parameter binding failed: " . $stmt->error);
            throw new Exception("An error occurred while processing your request. Please try again later.");
        }
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Execute error: " . $stmt->error);
        throw new Exception("An error occurred while submitting your contact form. Please try again later.");
    }
    
    $response['success'] = true;
    $response['message'] = "Thank you for contacting us! We'll get back to you soon.";
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    // Log full error details internally (not exposed to client)
    error_log("Contact form error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

