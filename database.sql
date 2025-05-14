-- Create database structure for Healthcare Staffing Platform
-- Compatible with MySQL 5.7+

-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS healthcare_professionals;
DROP TABLE IF EXISTS facility_types;
DROP TABLE IF EXISTS facilities;
DROP TABLE IF EXISTS credentials;
DROP TABLE IF EXISTS credential_verifications;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS professional_skills;
DROP TABLE IF EXISTS skill_assessments;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS shift_applications;
DROP TABLE IF EXISTS payment_methods;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS notifications;
SET FOREIGN_KEY_CHECKS = 1;

-- Create users table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    user_type ENUM('admin', 'professional', 'facility') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    profile_image VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    zip VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Create healthcare_professionals table
CREATE TABLE healthcare_professionals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    license_number VARCHAR(100) NULL,
    license_state VARCHAR(50) NULL,
    license_expiration DATE NULL,
    profession VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NULL,
    years_experience INT UNSIGNED NULL,
    bio TEXT NULL,
    hourly_rate DECIMAL(10, 2) NULL,
    availability_status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    background_check_status ENUM('pending', 'passed', 'failed') NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create facility_types table
CREATE TABLE facility_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create facilities table
CREATE TABLE facilities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    facility_type_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    license_number VARCHAR(100) NULL,
    tax_id VARCHAR(100) NULL,
    contact_person VARCHAR(255) NULL,
    contact_email VARCHAR(255) NULL,
    contact_phone VARCHAR(20) NULL,
    website VARCHAR(255) NULL,
    description TEXT NULL,
    logo VARCHAR(255) NULL,
    verification_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_type_id) REFERENCES facility_types(id)
);

-- Create credentials table
CREATE TABLE credentials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    professional_id BIGINT UNSIGNED NOT NULL,
    credential_type ENUM('license', 'certification', 'education', 'immunization', 'identification', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuing_authority VARCHAR(255) NULL,
    credential_number VARCHAR(100) NULL,
    issue_date DATE NULL,
    expiration_date DATE NULL,
    document_path VARCHAR(255) NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (professional_id) REFERENCES healthcare_professionals(id) ON DELETE CASCADE
);

-- Create credential_verifications table
CREATE TABLE credential_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    credential_id BIGINT UNSIGNED NOT NULL,
    verified_by BIGINT UNSIGNED NULL,
    verification_method ENUM('manual', 'automated', 'third_party') NOT NULL,
    verification_date TIMESTAMP NULL,
    verification_result ENUM('verified', 'rejected', 'pending') NOT NULL,
    verification_notes TEXT NULL,
    verification_document VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create skills table
CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NOT NULL,
    assessment_type ENUM('quiz', 'checklist', 'document', 'reference') NOT NULL DEFAULT 'quiz',
    passing_score INT UNSIGNED NOT NULL DEFAULT 70,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create professional_skills table
CREATE TABLE professional_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    professional_id BIGINT UNSIGNED NOT NULL,
    skill_id BIGINT UNSIGNED NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    verified_by BIGINT UNSIGNED NULL,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (professional_id) REFERENCES healthcare_professionals(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create skill_assessments table
CREATE TABLE skill_assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    skill_id BIGINT UNSIGNED NOT NULL,
    professional_id BIGINT UNSIGNED NOT NULL,
    score INT UNSIGNED NULL,
    passed BOOLEAN NULL,
    assessment_date TIMESTAMP NULL,
    completion_time INT UNSIGNED NULL COMMENT 'Time in seconds',
    answers JSON NULL,
    feedback TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES healthcare_professionals(id) ON DELETE CASCADE
);

-- Create shifts table
CREATE TABLE shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    facility_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    required_profession VARCHAR(100) NOT NULL,
    required_specialty VARCHAR(100) NULL,
    required_experience INT UNSIGNED NULL,
    required_credentials JSON NULL,
    required_skills JSON NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    status ENUM('open', 'filled', 'cancelled', 'completed') NOT NULL DEFAULT 'open',
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    zip VARCHAR(20) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);

-- Create shift_applications table
CREATE TABLE shift_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shift_id BIGINT UNSIGNED NOT NULL,
    professional_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    application_message TEXT NULL,
    response_message TEXT NULL,
    applied_at TIMESTAMP NOT NULL,
    responded_at TIMESTAMP NULL,
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    rating INT UNSIGNED NULL,
    review TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES healthcare_professionals(id) ON DELETE CASCADE
);

-- Create payment_methods table
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    method_type ENUM('paypal', 'cashapp', 'coinbase', 'zelle', 'bank_account', 'credit_card') NOT NULL,
    account_name VARCHAR(255) NULL,
    account_email VARCHAR(255) NULL,
    account_phone VARCHAR(20) NULL,
    account_identifier VARCHAR(255) NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create payments table
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shift_application_id BIGINT UNSIGNED NOT NULL,
    payer_id BIGINT UNSIGNED NOT NULL,
    payee_id BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10, 2) NOT NULL,
    fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'in_escrow', 'completed', 'refunded', 'disputed', 'cancelled') NOT NULL,
    transaction_id VARCHAR(255) NULL,
    escrow_release_date TIMESTAMP NULL,
    payment_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shift_application_id) REFERENCES shift_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (payer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);

-- Create messages table
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create notifications table
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data JSON NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default facility types
INSERT INTO facility_types (name, description, created_at, updated_at) VALUES
('Hospital', 'General or specialized hospital facilities', NOW(), NOW()),
('Nursing Home', 'Long-term care facilities for elderly or disabled individuals', NOW(), NOW()),
('Assisted Living', 'Residential facilities providing assistance with daily activities', NOW(), NOW()),
('Rehabilitation Center', 'Facilities focused on physical rehabilitation services', NOW(), NOW()),
('Clinic', 'Outpatient medical facilities', NOW(), NOW()),
('Home Health Agency', 'Agencies providing healthcare services at patients\' homes', NOW(), NOW()),
('Hospice', 'Facilities providing end-of-life care', NOW(), NOW()),
('Mental Health Facility', 'Facilities specializing in mental health services', NOW(), NOW());

-- Insert default skills
INSERT INTO skills (name, description, category, assessment_type, passing_score, status, created_at, updated_at) VALUES
('Medication Administration', 'Safe administration of medications including oral, injectable, and IV medications.', 'clinical', 'quiz', 80, 'active', NOW(), NOW()),
('Vital Signs Monitoring', 'Accurate measurement and recording of vital signs including blood pressure, pulse, respiration, and temperature.', 'clinical', 'checklist', 70, 'active', NOW(), NOW()),
('IV Therapy', 'Initiation and maintenance of intravenous therapy, including central lines.', 'clinical', 'quiz', 85, 'active', NOW(), NOW()),
('Wound Care', 'Assessment and treatment of various wound types, including dressing changes and infection prevention.', 'clinical', 'quiz', 75, 'active', NOW(), NOW()),
('Electronic Health Records', 'Proficiency in using electronic health record systems for documentation.', 'technical', 'checklist', 70, 'active', NOW(), NOW()),
('Basic Life Support (BLS)', 'Certification in basic life support techniques.', 'certification', 'document', 100, 'active', NOW(), NOW()),
('Advanced Cardiac Life Support (ACLS)', 'Certification in advanced cardiac life support.', 'certification', 'document', 100, 'active', NOW(), NOW()),
('Patient Assessment', 'Comprehensive assessment of patient condition and needs.', 'clinical', 'quiz', 75, 'active', NOW(), NOW()),
('Infection Control', 'Knowledge and application of infection prevention and control measures.', 'clinical', 'quiz', 80, 'active', NOW(), NOW()),
('Communication Skills', 'Effective communication with patients, families, and healthcare team members.', 'soft_skills', 'checklist', 70, 'active', NOW(), NOW());

-- Insert admin user
INSERT INTO users (name, email, password, user_type, status, created_at, updated_at) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW(), NOW());
