<?php
// Database configuration
$host = 'localhost';
$dbname = 'cleantech_db';
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Validate and sanitize input
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_no = trim($_POST['contact_no'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($contact_no)) {
            $errors[] = 'Contact number is required';
        }
        
        if (empty($service)) {
            $errors[] = 'Please select a service';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required';
        }
        
        // If validation fails
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }
        
        // Prepare SQL statement
        $sql = "INSERT INTO contact_requests (name, email, contact_no, service, message, created_at) 
                VALUES (:name, :email, :contact_no, :service, :message, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':contact_no', $contact_no, PDO::PARAM_STR);
        $stmt->bindParam(':service', $service, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        // Execute query
        if ($stmt->execute()) {
            // Optional: Send email notification
            $to = "cleantechsurat@gmail.com"; // Your email
            $subject = "New Contact Request from " . $name;
            $email_message = "Name: $name\n";
            $email_message .= "Email: $email\n";
            $email_message .= "Contact: $contact_no\n";
            $email_message .= "Service: $service\n";
            $email_message .= "Message: $message\n";
            
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            
            // Uncomment the line below to enable email notifications
            // mail($to, $subject, $email_message, $headers);
            
            echo json_encode([
                'success' => true,
                'message' => 'Your request has been submitted successfully!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to submit request. Please try again.'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }
    
} catch (PDOException $e) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error. Please try again later.'
    ]);
}
?>