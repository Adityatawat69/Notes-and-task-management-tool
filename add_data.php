<?php
session_start(); 
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username']; // Get the current logged-in user's username
    $note = $_POST['note'] ?? null;
    $task = $_POST['task'] ?? null;

    // Check if the user's data already exists
    $stmt = $conn->prepare("SELECT * FROM data WHERE username = ?");
    if ($stmt === false) {
        die("Error preparing SELECT statement: " . $conn->error); // Add error checking
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If data exists, update the note and task for the user
        $update_stmt = $conn->prepare("UPDATE data SET note = ?, task = ? WHERE username = ?");
        if ($update_stmt === false) {
            die("Error preparing UPDATE statement: " . $conn->error); // Add error checking
        }
        $update_stmt->bind_param("sss", $note, $task, $username);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // If no data exists, insert a new row
        $insert_stmt = $conn->prepare("INSERT INTO data (username, note, task) VALUES (?, ?, ?)");
        if ($insert_stmt === false) {
            die("Error preparing INSERT statement: " . $conn->error); // Add error checking
        }
        $insert_stmt->bind_param("sss", $username, $note, $task);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the dashboard after saving
    header("Location: dashboard.php");
    exit();
}
