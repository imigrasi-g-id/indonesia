-- Create admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin account
-- Password: Admin123@ (hashed using password_hash)
INSERT INTO admins (email, password, name) VALUES 
('admin@mpaspor.com', '$2y$10$YourHashedPasswordHere', 'Administrator');
