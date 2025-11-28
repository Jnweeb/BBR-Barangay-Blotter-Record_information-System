<?php
// Change this to any password you want to hash
$password = "admin123";

// Generate a secure hash using PASSWORD_DEFAULT (bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Output the hash
echo "Password: " . $password . "<br>";
echo "Hashed: " . $hashed_password;
?>
