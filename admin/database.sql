-- Create tables
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(20) NOT NULL,
    application_type VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    nik VARCHAR(16) NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('L', 'P') NOT NULL,
    marital_status VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    education VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    payment_status VARCHAR(20) DEFAULT 'waiting',
    payment_code VARCHAR(50),
    payment_amount DECIMAL(10,2),
    payment_deadline DATETIME,
    office_id VARCHAR(50),
    office_name VARCHAR(100),
    office_address TEXT,
    passport_type VARCHAR(20),
    passport_price DECIMAL(10,2),
    document_ktp TEXT,
    document_kk TEXT,
    document_birth_cert TEXT,
    document_photo TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application_number (application_number)
);

CREATE TABLE admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    application_id INT,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id)
);

CREATE TABLE user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    application_id INT,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id)
);

-- Create indexes
CREATE INDEX idx_application_number ON applications(application_number);
CREATE INDEX idx_email ON applications(email);
CREATE INDEX idx_status ON applications(status);
CREATE INDEX idx_payment_status ON applications(payment_status);
CREATE INDEX idx_created_at ON applications(created_at);
CREATE INDEX idx_user_email ON user_notifications(user_email);
