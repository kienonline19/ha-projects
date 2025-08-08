CREATE DATABASE IF NOT EXISTS student_qa_system;

USE student_qa_system;

CREATE TABLE
    users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module_name VARCHAR(100) NOT NULL,
        module_code VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        user_id INT NOT NULL,
        module_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
    );

CREATE TABLE
    contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM ('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    );

INSERT INTO
    users (username, email, password)
VALUES
    (
        'admin',
        'admin@student.ac.uk',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ),
    (
        'john_doe',
        'john@student.ac.uk',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ),
    (
        'jane_smith',
        'jane@student.ac.uk',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    );

INSERT INTO
    modules (module_name, module_code)
VALUES
    ('Web Programming 1', 'COMP1841'),
    ('Database Systems', 'COMP1842'),
    ('Software Engineering', 'COMP1843'),
    ('Computer Networks', 'COMP1844'),
    ('Mobile Development', 'COMP1845');

INSERT INTO
    posts (title, content, user_id, module_id)
VALUES
    (
        'Need help with PHP PDO connection',
        'I am having trouble connecting to MySQL database using PDO. Can someone help me with the syntax?',
        2,
        1
    ),
    (
        'CSS Grid vs Flexbox',
        'What are the main differences between CSS Grid and Flexbox? When should I use each one?',
        3,
        1
    ),
    (
        'Database normalization question',
        'Can someone explain 3NF with a simple example?',
        2,
        2
    );