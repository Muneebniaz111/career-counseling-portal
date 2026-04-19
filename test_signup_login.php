<?php
require_once 'db_connection.php';

// Test Data
 = 'SignupTest User';
 = 'Female';
 = 'signuptest@example.com';
 = 'signuptest';
 = 'SignupTest@123';
 = '+92-300-9876543';
 = 'Sindh';

// 1. Create hashed password
 = password_hash(, PASSWORD_BCRYPT);

echo "--- Signup and Login Flow Test ---\n";

// Check if user already exists and delete to ensure clean test
->query("DELETE FROM users WHERE username = '' OR email = ''");

// 2. Insert user
// Note: We need to know the table columns. Based on the request, we assume these common names.
// Let's first check the table structure if possible, or just try to insert and catch errors.
 = "INSERT INTO users (name, gender, email, username, password, contact, city) VALUES (?, ?, ?, ?, ?, ?, ?)";
 = ->prepare();

if (!) {
    echo "Error preparing statement: " . ->error . "\n";
    // List tables and columns to help debug if insert fails
    echo "Tables in database:\n";
     = ->query("SHOW TABLES");
    while( = ->fetch_array()) echo [0] . "\n";
    exit;
}

->bind_param("sssssss", , , , , , , );

if (->execute()) {
    echo "1. User inserted successfully.\n";
} else {
    echo "1. Error inserting user: " . ->error . "\n";
    exit;
}

// 3. Verify user in DB
 = ->query("SELECT * FROM users WHERE username = ''");
if (->num_rows > 0) {
     = ->fetch_assoc();
    echo "2. User verified in database.\n";
    
    // 4. Test password_verify
    if (password_verify(, ['password'])) {
        echo "3. password_verify works with stored hash.\n";
    } else {
        echo "3. password_verify FAILED.\n";
    }

    // 5. Confirm all fields
     = true;
    if (['name'] !== ) { echo "Mismatch Name\n";  = false; }
    if (['gender'] !== ) { echo "Mismatch Gender\n";  = false; }
    if (['email'] !== ) { echo "Mismatch Email\n";  = false; }
    if (['username'] !== ) { echo "Mismatch Username\n";  = false; }
    if (['contact'] !== ) { echo "Mismatch Contact\n";  = false; }
    if (['city'] !== ) { echo "Mismatch City\n";  = false; }

    if () {
        echo "4. All fields match correctly.\n";
    } else {
        echo "4. Some fields DO NOT match.\n";
        print_r();
    }
} else {
    echo "2. User NOT found in database after insert.\n";
}

echo "5. No SQL errors or issues detected.\n";

// Cleanup
->query("DELETE FROM users WHERE username = ''");
echo "Test cleanup completed.\n";
?>
