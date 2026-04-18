<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS exam_generator");
    $pdo->exec("USE exam_generator");

    // Table: users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'faculty',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Table: subjects
    $pdo->exec("CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        syllabus TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Table: units
    $pdo->exec("CREATE TABLE IF NOT EXISTS units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        unit_name VARCHAR(255) NOT NULL,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    )");

    // Table: questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        unit_id INT NOT NULL,
        question_text TEXT NOT NULL,
        difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
        marks INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
    )");

    // Table: generated_papers
    $pdo->exec("CREATE TABLE IF NOT EXISTS generated_papers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        paper_name VARCHAR(255),
        paper_data JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    )");

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h1>Database Setup Successful</h1>";
    echo "<p>Next, please <a href='index.php'>Go to Login</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
?>
