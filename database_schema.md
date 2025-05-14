# Healthcare Staffing Platform Database Schema

## Overview
This document outlines the database schema for the healthcare staffing platform. The schema is designed to support all core functionality including user management, credential verification, shift management, payment processing, and skills assessment.

## Entity Relationship Diagram (Conceptual)

```
Users (Healthcare Professionals & Facility Staff)
  ↓
  ├── Credentials ←→ CredentialVerifications
  ↓
  ├── HealthcareProfessionals ←→ Skills ←→ SkillAssessments
  ↓                              ↓
  ├── Facilities ←→ FacilityTypes
  ↓         ↓
  └── Shifts ←→ ShiftApplications
       ↓
       └── Payments ←→ PaymentMethods
```

## Database Tables

### Users
Stores basic information for all users of the system.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| email | VARCHAR(255) | UNIQUE, NOT NULL | User's email address |
| password_hash | VARCHAR(255) | NOT NULL | Hashed password |
| first_name | VARCHAR(100) | NOT NULL | User's first name |
| last_name | VARCHAR(100) | NOT NULL | User's last name |
| phone_number | VARCHAR(20) | NOT NULL | User's phone number |
| address | TEXT | NULL | User's address |
| city | VARCHAR(100) | NULL | User's city |
| state | VARCHAR(50) | NULL | User's state/province |
| zip_code | VARCHAR(20) | NULL | User's postal code |
| country | VARCHAR(100) | DEFAULT 'USA' | User's country |
| profile_image_url | VARCHAR(255) | NULL | URL to profile image |
| user_type | ENUM | NOT NULL | 'professional', 'facility', 'admin' |
| status | ENUM | NOT NULL, DEFAULT 'pending' | 'pending', 'active', 'suspended', 'inactive' |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |
| last_login | TIMESTAMP | NULL | Last login timestamp |
| two_factor_enabled | BOOLEAN | DEFAULT FALSE | Whether 2FA is enabled |
| two_factor_secret | VARCHAR(255) | NULL | Secret for 2FA if enabled |

### HealthcareProfessionals
Extends Users table with healthcare professional specific information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| user_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to Users table |
| professional_type | ENUM | NOT NULL | 'RN', 'LPN', 'CNA', 'CMA', etc. |
| years_experience | INTEGER | NOT NULL | Years of professional experience |
| bio | TEXT | NULL | Professional biography |
| hourly_rate_min | DECIMAL(10,2) | NULL | Minimum hourly rate |
| availability_status | ENUM | NOT NULL, DEFAULT 'available' | 'available', 'unavailable', 'limited' |
| rating | DECIMAL(3,2) | DEFAULT 0 | Average rating (0-5) |
| total_shifts_completed | INTEGER | DEFAULT 0 | Total number of shifts completed |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Facilities
Extends Users table with healthcare facility specific information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| user_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to Users table |
| facility_name | VARCHAR(255) | NOT NULL | Name of the facility |
| facility_type_id | UUID | FOREIGN KEY (FacilityTypes.id), NOT NULL | Reference to FacilityTypes table |
| description | TEXT | NULL | Facility description |
| website | VARCHAR(255) | NULL | Facility website URL |
| latitude | DECIMAL(10,8) | NULL | Geographical latitude |
| longitude | DECIMAL(11,8) | NULL | Geographical longitude |
| rating | DECIMAL(3,2) | DEFAULT 0 | Average rating (0-5) |
| total_shifts_posted | INTEGER | DEFAULT 0 | Total number of shifts posted |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### FacilityTypes
Lookup table for facility types.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| name | VARCHAR(100) | UNIQUE, NOT NULL | Type name (e.g., 'Senior Living', 'Hospital') |
| description | TEXT | NULL | Type description |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Credentials
Stores credential information for healthcare professionals.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| professional_id | UUID | FOREIGN KEY (HealthcareProfessionals.id), NOT NULL | Reference to HealthcareProfessionals table |
| credential_type | ENUM | NOT NULL | 'license', 'certification', 'id', 'vaccination', 'background_check' |
| credential_name | VARCHAR(255) | NOT NULL | Name of credential |
| credential_number | VARCHAR(100) | NULL | Identifier number of credential |
| issuing_authority | VARCHAR(255) | NOT NULL | Authority that issued the credential |
| issue_date | DATE | NOT NULL | Date credential was issued |
| expiration_date | DATE | NOT NULL | Date credential expires |
| document_url | VARCHAR(255) | NOT NULL | URL to stored document |
| verification_status | ENUM | NOT NULL, DEFAULT 'pending' | 'pending', 'verified', 'rejected', 'expired' |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### CredentialVerifications
Tracks verification history for credentials.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| credential_id | UUID | FOREIGN KEY (Credentials.id), NOT NULL | Reference to Credentials table |
| verified_by | UUID | FOREIGN KEY (Users.id), NULL | Reference to admin who verified |
| verification_date | TIMESTAMP | NULL | When verification occurred |
| verification_method | ENUM | NULL | 'manual', 'automated', 'third_party' |
| verification_notes | TEXT | NULL | Notes about verification |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Skills
Lookup table for professional skills.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| name | VARCHAR(100) | UNIQUE, NOT NULL | Skill name |
| description | TEXT | NULL | Skill description |
| category | VARCHAR(100) | NOT NULL | Skill category |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### ProfessionalSkills
Junction table linking professionals to skills with proficiency levels.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| professional_id | UUID | FOREIGN KEY (HealthcareProfessionals.id), NOT NULL | Reference to HealthcareProfessionals table |
| skill_id | UUID | FOREIGN KEY (Skills.id), NOT NULL | Reference to Skills table |
| proficiency_level | ENUM | NOT NULL, DEFAULT 'beginner' | 'beginner', 'intermediate', 'advanced', 'expert' |
| years_experience | INTEGER | DEFAULT 0 | Years of experience with this skill |
| is_verified | BOOLEAN | DEFAULT FALSE | Whether skill has been verified |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### SkillAssessments
Stores results of skill assessments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| professional_id | UUID | FOREIGN KEY (HealthcareProfessionals.id), NOT NULL | Reference to HealthcareProfessionals table |
| skill_id | UUID | FOREIGN KEY (Skills.id), NOT NULL | Reference to Skills table |
| assessment_date | TIMESTAMP | NOT NULL | When assessment was taken |
| score | DECIMAL(5,2) | NOT NULL | Assessment score |
| max_score | DECIMAL(5,2) | NOT NULL | Maximum possible score |
| percentile | DECIMAL(5,2) | NULL | Percentile ranking among peers |
| assessment_version | VARCHAR(50) | NOT NULL | Version of assessment taken |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Shifts
Stores information about shifts posted by facilities.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| facility_id | UUID | FOREIGN KEY (Facilities.id), NOT NULL | Reference to Facilities table |
| title | VARCHAR(255) | NOT NULL | Shift title |
| description | TEXT | NULL | Shift description |
| required_role | ENUM | NOT NULL | 'RN', 'LPN', 'CNA', etc. |
| start_time | TIMESTAMP | NOT NULL | Shift start time |
| end_time | TIMESTAMP | NOT NULL | Shift end time |
| hourly_rate | DECIMAL(10,2) | NOT NULL | Pay rate per hour |
| status | ENUM | NOT NULL, DEFAULT 'open' | 'open', 'filled', 'in_progress', 'completed', 'cancelled' |
| is_urgent | BOOLEAN | DEFAULT FALSE | Whether shift is urgent |
| required_skills | TEXT | NULL | JSON array of required skill IDs |
| min_experience_years | INTEGER | DEFAULT 0 | Minimum years of experience required |
| max_applicants | INTEGER | DEFAULT 10 | Maximum number of applicants |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### ShiftApplications
Tracks applications from professionals for shifts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| shift_id | UUID | FOREIGN KEY (Shifts.id), NOT NULL | Reference to Shifts table |
| professional_id | UUID | FOREIGN KEY (HealthcareProfessionals.id), NOT NULL | Reference to HealthcareProfessionals table |
| application_status | ENUM | NOT NULL, DEFAULT 'pending' | 'pending', 'accepted', 'rejected', 'withdrawn' |
| application_date | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | When application was submitted |
| notes | TEXT | NULL | Application notes |
| check_in_time | TIMESTAMP | NULL | When professional checked in |
| check_out_time | TIMESTAMP | NULL | When professional checked out |
| facility_rating | INTEGER | NULL | Rating given to facility (1-5) |
| facility_review | TEXT | NULL | Review given to facility |
| professional_rating | INTEGER | NULL | Rating given to professional (1-5) |
| professional_review | TEXT | NULL | Review given to professional |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### PaymentMethods
Stores payment method information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| user_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to Users table |
| method_type | ENUM | NOT NULL | 'paypal', 'cashapp', 'coinbase', 'bank_account', 'credit_card' |
| account_identifier | VARCHAR(255) | NOT NULL | Encrypted account identifier |
| is_default | BOOLEAN | DEFAULT FALSE | Whether this is the default payment method |
| nickname | VARCHAR(100) | NULL | User-defined nickname for payment method |
| status | ENUM | NOT NULL, DEFAULT 'active' | 'active', 'inactive', 'pending_verification' |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Payments
Tracks payments for completed shifts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| shift_application_id | UUID | FOREIGN KEY (ShiftApplications.id), NOT NULL | Reference to ShiftApplications table |
| amount | DECIMAL(10,2) | NOT NULL | Payment amount |
| currency | VARCHAR(3) | NOT NULL, DEFAULT 'USD' | Currency code |
| status | ENUM | NOT NULL, DEFAULT 'pending' | 'pending', 'in_escrow', 'completed', 'failed', 'refunded' |
| payment_method_id | UUID | FOREIGN KEY (PaymentMethods.id), NULL | Reference to PaymentMethods table |
| transaction_fee | DECIMAL(10,2) | DEFAULT 0 | Platform transaction fee |
| payer_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to paying user |
| payee_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to receiving user |
| payment_date | TIMESTAMP | NULL | When payment was processed |
| escrow_release_date | TIMESTAMP | NULL | When payment was released from escrow |
| external_transaction_id | VARCHAR(255) | NULL | ID from external payment processor |
| notes | TEXT | NULL | Payment notes |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Messages
Stores communication between users.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| sender_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to sending user |
| recipient_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to receiving user |
| related_shift_id | UUID | FOREIGN KEY (Shifts.id), NULL | Reference to related shift if applicable |
| message_text | TEXT | NOT NULL | Message content |
| is_read | BOOLEAN | DEFAULT FALSE | Whether message has been read |
| read_at | TIMESTAMP | NULL | When message was read |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

### Notifications
Stores system notifications for users.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique identifier |
| user_id | UUID | FOREIGN KEY (Users.id), NOT NULL | Reference to Users table |
| notification_type | ENUM | NOT NULL | 'shift', 'payment', 'message', 'credential', 'system' |
| title | VARCHAR(255) | NOT NULL | Notification title |
| message | TEXT | NOT NULL | Notification content |
| is_read | BOOLEAN | DEFAULT FALSE | Whether notification has been read |
| read_at | TIMESTAMP | NULL | When notification was read |
| related_entity_type | VARCHAR(50) | NULL | Type of related entity |
| related_entity_id | UUID | NULL | ID of related entity |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE | Record update timestamp |

## Indexes

- Users: email, user_type, status
- HealthcareProfessionals: professional_type, availability_status, rating
- Facilities: facility_type_id, rating
- Credentials: professional_id, credential_type, expiration_date, verification_status
- Shifts: facility_id, required_role, start_time, status, is_urgent
- ShiftApplications: shift_id, professional_id, application_status
- Payments: shift_application_id, status, payment_date
- Messages: sender_id, recipient_id, is_read
- Notifications: user_id, is_read, notification_type

## Security Considerations

1. **Encryption**:
   - Sensitive data like payment information must be encrypted at rest
   - Personal identifiable information should be encrypted
   - Credential documents should be stored securely with access controls

2. **Access Control**:
   - Implement row-level security for multi-tenant data
   - Ensure users can only access their own data
   - Admin roles should have appropriate access restrictions

3. **Audit Trails**:
   - Maintain logs for all credential verifications
   - Track all payment status changes
   - Log administrative actions on user data

4. **HIPAA Compliance**:
   - Ensure all healthcare data storage meets HIPAA requirements
   - Implement appropriate data retention and deletion policies
   - Maintain backup and recovery procedures

## Data Relationships

1. **Users to Roles**:
   - One-to-one relationship between Users and either HealthcareProfessionals or Facilities
   - Admin users exist only in the Users table

2. **Professionals to Credentials**:
   - One-to-many relationship between HealthcareProfessionals and Credentials
   - Each credential has one verification history

3. **Facilities to Shifts**:
   - One-to-many relationship between Facilities and Shifts
   - Each shift can have multiple applications

4. **Shifts to Payments**:
   - One-to-one relationship between ShiftApplications and Payments
   - Each payment is associated with exactly one completed shift application

5. **Users to Skills**:
   - Many-to-many relationship between HealthcareProfessionals and Skills through ProfessionalSkills
   - Each professional can have multiple skill assessments

## Migration Strategy

1. Create base tables (Users, FacilityTypes, Skills)
2. Create extended profile tables (HealthcareProfessionals, Facilities)
3. Create credential and verification tables
4. Create shift and application tables
5. Create payment and financial tables
6. Create communication tables
7. Add indexes and constraints
8. Implement encryption for sensitive fields
