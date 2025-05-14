# MySQL Database Schema - LAMP Healthcare Staffing Platform

## 1. Introduction

This document describes the MySQL database schema for the LAMP stack healthcare staffing platform. The schema is designed to support all functional requirements outlined in the `lamp_stack_requirements.md` document, including user management, credential storage, shift operations, payments, and skills assessments.

## 2. Schema Overview

The database consists of the following primary tables. All tables will use the InnoDB storage engine to support foreign key constraints and transactions.

## 3. Table Definitions

### 3.1. `users`

Stores information for all user types (Healthcare Professionals, Facility Admins, Platform Admins).

| Column Name         | Data Type                                       | Constraints                                  | Description                                      |
|---------------------|-------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                | `INT UNSIGNED`                                  | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier for the user.                  |
| `email`             | `VARCHAR(255)`                                  | `NOT NULL`, `UNIQUE`                         | User's email address (used for login).           |
| `password_hash`     | `VARCHAR(255)`                                  | `NOT NULL`                                   | Hashed password.                                 |
| `user_type`         | `ENUM('professional', 'facility', 'admin')`   | `NOT NULL`                                   | Type of user.                                    |
| `full_name`         | `VARCHAR(255)`                                  | `NOT NULL`                                   | User's full name.                                |
| `phone_number`      | `VARCHAR(20)`                                   | `NULL`                                       | User's phone number.                             |
| `address_street`    | `VARCHAR(255)`                                  | `NULL`                                       | Street address.                                  |
| `address_city`      | `VARCHAR(100)`                                  | `NULL`                                       | City.                                            |
| `address_state`     | `VARCHAR(100)`                                  | `NULL`                                       | State/Province.                                  |
| `address_zip_code`  | `VARCHAR(20)`                                   | `NULL`                                       | Postal/ZIP code.                                 |
| `profile_image_path`| `VARCHAR(255)`                                  | `NULL`                                       | Path to user's profile image.                    |
| `status`            | `ENUM('active', 'inactive', 'suspended')`       | `NOT NULL`, `DEFAULT 'active'`               | Account status.                                  |
| `email_verified_at` | `TIMESTAMP`                                     | `NULL`                                       | Timestamp when email was verified.               |
| `password_reset_token`| `VARCHAR(100)`                                  | `NULL`, `UNIQUE`                             | Token for password reset.                        |
| `password_reset_expires_at`| `TIMESTAMP`                               | `NULL`                                       | Expiration time for password reset token.        |
| `created_at`        | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of account creation.                   |
| `updated_at`        | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |

### 3.2. `healthcare_professionals`

Stores specific details for users with the 'professional' role.

| Column Name             | Data Type                               | Constraints                                  | Description                                      |
|-------------------------|-----------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                          | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `user_id`               | `INT UNSIGNED`                          | `NOT NULL`, `UNIQUE`                         | Foreign key referencing `users.id`.              |
| `professional_summary`  | `TEXT`                                  | `NULL`                                       | Bio or summary.                                  |
| `years_experience`      | `TINYINT UNSIGNED`                      | `NULL`                                       | Years of professional experience.                |
| `profession_type`       | `VARCHAR(100)`                          | `NULL`                                       | E.g., RN, CNA, LPN.                              |
| `specialties`           | `TEXT`                                  | `NULL`                                       | Comma-separated list or JSON of specialties.     |
| `desired_hourly_rate`   | `DECIMAL(10, 2)`                        | `NULL`                                       | Desired hourly pay rate.                         |
| `availability_details`  | `TEXT`                                  | `NULL`                                       | Details about availability (e.g., JSON schedule).|
| `background_check_status`| `ENUM('pending', 'passed', 'failed')`| `NULL`                                       | Status of background check.                      |
| `created_at`            | `TIMESTAMP`                             | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of record creation.                    |
| `updated_at`            | `TIMESTAMP`                             | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                         |                                         | `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |

### 3.3. `credentials`

Stores uploaded documents for healthcare professionals.

| Column Name         | Data Type                                                       | Constraints                                  | Description                                      |
|---------------------|-----------------------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                | `INT UNSIGNED`                                                  | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `professional_user_id` | `INT UNSIGNED`                                               | `NOT NULL`                                   | Foreign key referencing `users.id` (of professional). |
| `credential_type`   | `ENUM('license', 'certification', 'education', 'vaccination', 'identification', 'other')` | `NOT NULL`                                   | Type of credential.                              |
| `title`             | `VARCHAR(255)`                                                  | `NOT NULL`                                   | Name or title of the credential.                 |
| `issuing_authority` | `VARCHAR(255)`                                                  | `NULL`                                       | Authority that issued the credential.            |
| `credential_number` | `VARCHAR(100)`                                                  | `NULL`                                       | License or certificate number.                   |
| `issue_date`        | `DATE`                                                          | `NULL`                                       | Date credential was issued.                      |
| `expiration_date`   | `DATE`                                                          | `NULL`                                       | Date credential expires.                         |
| `document_path`     | `VARCHAR(255)`                                                  | `NOT NULL`                                   | Server path to the uploaded document file.       |
| `verification_status`| `ENUM('pending', 'verified', 'rejected', 'expired')`         | `NOT NULL`, `DEFAULT 'pending'`              | Verification status.                             |
| `verification_notes`| `TEXT`                                                          | `NULL`                                       | Notes from admin regarding verification.         |
| `verified_by_admin_id`| `INT UNSIGNED`                                               | `NULL`                                       | Admin user who verified (references `users.id`). |
| `verified_at`       | `TIMESTAMP`                                                     | `NULL`                                       | Timestamp of verification.                       |
| `uploaded_at`       | `TIMESTAMP`                                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of upload.                             |
|                     |                                                                 | `FOREIGN KEY (professional_user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |
|                     |                                                                 | `FOREIGN KEY (verified_by_admin_id) REFERENCES users(id) ON DELETE SET NULL` |                                                  |

### 3.4. `facilities`

Stores information about healthcare facilities.

| Column Name             | Data Type                                  | Constraints                                  | Description                                      |
|-------------------------|--------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                             | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `user_id`               | `INT UNSIGNED`                             | `NOT NULL`, `UNIQUE`                         | Foreign key referencing `users.id` (facility admin). |
| `facility_name`         | `VARCHAR(255)`                             | `NOT NULL`                                   | Name of the facility.                            |
| `facility_type`         | `VARCHAR(100)`                             | `NULL`                                       | E.g., Hospital, Nursing Home.                    |
| `facility_license_number`| `VARCHAR(100)`                          | `NULL`                                       | Facility's license number.                       |
| `tax_id`                | `VARCHAR(100)`                             | `NULL`                                       | Facility's tax identification number.            |
| `contact_person_name`   | `VARCHAR(255)`                             | `NULL`                                       | Primary contact person at the facility.          |
| `contact_person_email`  | `VARCHAR(255)`                             | `NULL`                                       | Email of the contact person.                     |
| `contact_person_phone`  | `VARCHAR(20)`                              | `NULL`                                       | Phone number of the contact person.              |
| `website_url`           | `VARCHAR(255)`                             | `NULL`                                       | Facility's website.                              |
| `description`           | `TEXT`                                     | `NULL`                                       | Description of the facility.                     |
| `logo_image_path`       | `VARCHAR(255)`                             | `NULL`                                       | Path to facility's logo.                         |
| `verification_status`   | `ENUM('pending', 'verified', 'rejected')`  | `NOT NULL`, `DEFAULT 'pending'`              | Facility verification status.                    |
| `created_at`            | `TIMESTAMP`                                | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of record creation.                    |
| `updated_at`            | `TIMESTAMP`                                | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                         |                                            | `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |

### 3.5. `shifts`

Stores information about shifts posted by facilities.

| Column Name             | Data Type                                     | Constraints                                  | Description                                      |
|-------------------------|-----------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                                | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier for the shift.                 |
| `facility_id`           | `INT UNSIGNED`                                | `NOT NULL`                                   | Foreign key referencing `facilities.id`.         |
| `title`                 | `VARCHAR(255)`                                | `NOT NULL`                                   | Title or name for the shift.                     |
| `description`           | `TEXT`                                        | `NULL`                                       | Detailed description of the shift.               |
| `required_profession`   | `VARCHAR(100)`                                | `NOT NULL`                                   | E.g., RN, CNA.                                   |
| `required_specialty`    | `VARCHAR(100)`                                | `NULL`                                       | Specific specialty required.                     |
| `required_experience_years`| `TINYINT UNSIGNED`                       | `NULL`                                       | Minimum years of experience required.            |
| `required_skills`       | `TEXT`                                        | `NULL`                                       | Comma-separated list or JSON of required skills. |
| `start_datetime`        | `DATETIME`                                    | `NOT NULL`                                   | Start date and time of the shift.                |
| `end_datetime`          | `DATETIME`                                    | `NOT NULL`                                   | End date and time of the shift.                  |
| `hourly_rate`           | `DECIMAL(10, 2)`                              | `NOT NULL`                                   | Offered pay rate per hour.                       |
| `positions_available`   | `TINYINT UNSIGNED`                            | `NOT NULL`, `DEFAULT 1`                      | Number of professionals needed for this shift.   |
| `status`                | `ENUM('open', 'filled', 'cancelled', 'completed')` | `NOT NULL`, `DEFAULT 'open'`                 | Current status of the shift.                     |
| `address_street`        | `VARCHAR(255)`                                | `NULL`                                       | Shift location (if different from facility).     |
| `address_city`          | `VARCHAR(100)`                                | `NULL`                                       |                                                  |
| `address_state`         | `VARCHAR(100)`                                | `NULL`                                       |                                                  |
| `address_zip_code`      | `VARCHAR(20)`                                 | `NULL`                                       |                                                  |
| `notes`                 | `TEXT`                                        | `NULL`                                       | Additional notes for the shift.                  |
| `created_at`            | `TIMESTAMP`                                   | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of shift creation.                     |
| `updated_at`            | `TIMESTAMP`                                   | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                         |                                               | `FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE` |                                                  |

### 3.6. `shift_applications`

Tracks applications made by professionals for shifts.

| Column Name             | Data Type                                               | Constraints                                  | Description                                      |
|-------------------------|---------------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                                          | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `shift_id`              | `INT UNSIGNED`                                          | `NOT NULL`                                   | Foreign key referencing `shifts.id`.             |
| `professional_user_id`  | `INT UNSIGNED`                                          | `NOT NULL`                                   | Foreign key referencing `users.id`.              |
| `application_status`    | `ENUM('pending', 'accepted', 'rejected', 'cancelled_by_prof', 'cancelled_by_fac', 'completed')` | `NOT NULL`, `DEFAULT 'pending'`              | Status of the application.                       |
| `application_message`   | `TEXT`                                                  | `NULL`                                       | Message from professional with application.      |
| `facility_response_message`| `TEXT`                                               | `NULL`                                       | Message from facility regarding application.     |
| `applied_at`            | `TIMESTAMP`                                             | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of application.                        |
| `responded_at`          | `TIMESTAMP`                                             | `NULL`                                       | Timestamp when facility responded.               |
| `check_in_time`         | `TIMESTAMP`                                             | `NULL`                                       | Professional's check-in time for the shift.      |
| `check_out_time`        | `TIMESTAMP`                                             | `NULL`                                       | Professional's check-out time for the shift.     |
| `professional_rating_of_facility`| `TINYINT UNSIGNED`                               | `NULL`                                       | Rating (1-5) given by professional.              |
| `professional_review_of_facility`| `TEXT`                                           | `NULL`                                       | Review by professional.                          |
| `facility_rating_of_professional`| `TINYINT UNSIGNED`                               | `NULL`                                       | Rating (1-5) given by facility.                  |
| `facility_review_of_professional`| `TEXT`                                           | `NULL`                                       | Review by facility.                              |
|                         |                                                         | `FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE` |                                                  |
|                         |                                                         | `FOREIGN KEY (professional_user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |
|                         |                                                         | `UNIQUE KEY (shift_id, professional_user_id)` | Prevent duplicate applications.                  |

### 3.7. `skills`

Stores a list of predefined skills.

| Column Name   | Data Type      | Constraints                                  | Description                                      |
|---------------|----------------|----------------------------------------------|--------------------------------------------------|
| `id`          | `INT UNSIGNED` | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier for the skill.                 |
| `skill_name`  | `VARCHAR(255)` | `NOT NULL`, `UNIQUE`                         | Name of the skill.                               |
| `description` | `TEXT`         | `NULL`                                       | Description of the skill.                        |
| `category`    | `VARCHAR(100)` | `NULL`                                       | Category of the skill (e.g., clinical, technical).|
| `created_at`  | `TIMESTAMP`    | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of creation.                           |

### 3.8. `professional_skills`

Links healthcare professionals to skills and their proficiency.

| Column Name             | Data Type                                       | Constraints                                  | Description                                      |
|-------------------------|-------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                                  | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `professional_user_id`  | `INT UNSIGNED`                                  | `NOT NULL`                                   | Foreign key referencing `users.id`.              |
| `skill_id`              | `INT UNSIGNED`                                  | `NOT NULL`                                   | Foreign key referencing `skills.id`.             |
| `proficiency_level`     | `ENUM('beginner', 'intermediate', 'advanced', 'expert')` | `NULL`                                   | Professional's self-assessed or verified proficiency. |
| `is_verified_by_assessment`| `BOOLEAN`                                  | `NOT NULL`, `DEFAULT FALSE`                  | Whether proficiency was verified by assessment.  |
| `last_assessed_at`      | `TIMESTAMP`                                     | `NULL`                                       | Timestamp of last assessment for this skill.     |
|                         |                                                 | `FOREIGN KEY (professional_user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |
|                         |                                                 | `FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE` |                                                  |
|                         |                                                 | `UNIQUE KEY (professional_user_id, skill_id)` | Prevent duplicate skill entries per professional.|

### 3.9. `skill_assessments`

Stores questions and structure for skill assessments.

| Column Name         | Data Type      | Constraints                                  | Description                                      |
|---------------------|----------------|----------------------------------------------|--------------------------------------------------|
| `id`                | `INT UNSIGNED` | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier for the assessment template.   |
| `skill_id`          | `INT UNSIGNED` | `NOT NULL`                                   | Foreign key referencing `skills.id` this assessment is for. |
| `assessment_title`  | `VARCHAR(255)` | `NOT NULL`                                   | Title of the assessment.                         |
| `assessment_type`   | `ENUM('multiple_choice', 'checklist')` | `NOT NULL`                               | Type of assessment.                              |
| `questions_data`    | `JSON`         | `NOT NULL`                                   | JSON structure containing questions, options, correct answers. |
| `passing_score_percentage` | `TINYINT UNSIGNED` | `NOT NULL`, `DEFAULT 70`                 | Minimum percentage to pass.                      |
| `created_by_admin_id`| `INT UNSIGNED`| `NULL`                                       | Admin who created/updated (references `users.id`).|
| `created_at`        | `TIMESTAMP`    | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of creation.                           |
| `updated_at`        | `TIMESTAMP`    | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                     |                | `FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE` |                                                  |
|                     |                | `FOREIGN KEY (created_by_admin_id) REFERENCES users(id) ON DELETE SET NULL` |                                                  |

### 3.10. `professional_assessment_attempts`

Stores results of assessment attempts by professionals.

| Column Name             | Data Type      | Constraints                                  | Description                                      |
|-------------------------|----------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED` | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `assessment_id`         | `INT UNSIGNED` | `NOT NULL`                                   | Foreign key referencing `skill_assessments.id`.  |
| `professional_user_id`  | `INT UNSIGNED` | `NOT NULL`                                   | Foreign key referencing `users.id`.              |
| `score_achieved`        | `DECIMAL(5,2)` | `NOT NULL`                                   | Percentage score achieved.                       |
| `passed`                | `BOOLEAN`      | `NOT NULL`                                   | Whether the professional passed.                 |
| `ranking_achieved`      | `ENUM('beginner', 'intermediate', 'advanced', 'expert', 'failed')` | `NOT NULL`                   | Ranking based on score.                          |
| `answers_data`          | `JSON`         | `NULL`                                       | JSON structure of professional's answers.        |
| `attempted_at`          | `TIMESTAMP`    | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of attempt.                            |
|                         |                | `FOREIGN KEY (assessment_id) REFERENCES skill_assessments(id) ON DELETE CASCADE` |                                                  |
|                         |                | `FOREIGN KEY (professional_user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |

### 3.11. `payment_methods`

Stores preferred payment methods for users (primarily professionals for payouts).

| Column Name             | Data Type                                       | Constraints                                  | Description                                      |
|-------------------------|-------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                                  | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `user_id`               | `INT UNSIGNED`                                  | `NOT NULL`                                   | Foreign key referencing `users.id`.              |
| `method_type`           | `ENUM('paypal', 'cashapp', 'coinbase', 'zelle', 'bank_transfer')` | `NOT NULL`                   | Type of payment method.                          |
| `account_details_json`  | `JSON`                                          | `NOT NULL`                                   | JSON storing account specifics (e.g., email for PayPal, $cashtag, wallet address, bank details). |
| `is_default`            | `BOOLEAN`                                       | `NOT NULL`, `DEFAULT FALSE`                  | Whether this is the default payout method.       |
| `is_verified`           | `BOOLEAN`                                       | `NOT NULL`, `DEFAULT FALSE`                  | If the payment method details are verified.      |
| `created_at`            | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of creation.                           |
| `updated_at`            | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                         |                                                 | `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |

### 3.12. `payments`

Tracks payment transactions.

| Column Name             | Data Type                                       | Constraints                                  | Description                                      |
|-------------------------|-------------------------------------------------|----------------------------------------------|--------------------------------------------------|
| `id`                    | `INT UNSIGNED`                                  | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier for the payment.               |
| `shift_application_id`  | `INT UNSIGNED`                                  | `NOT NULL`                                   | Foreign key referencing `shift_applications.id`. |
| `payer_facility_id`     | `INT UNSIGNED`                                  | `NOT NULL`                                   | Facility making the payment (references `facilities.id`). |
| `payee_professional_id` | `INT UNSIGNED`                                  | `NOT NULL`                                   | Professional receiving payment (references `users.id`). |
| `amount`                | `DECIMAL(10, 2)`                                | `NOT NULL`                                   | Amount of the payment.                           |
| `platform_fee`          | `DECIMAL(10, 2)`                                | `NOT NULL`, `DEFAULT 0.00`                   | Fee charged by the platform.                     |
| `final_payout_amount`   | `DECIMAL(10, 2)`                                | `NOT NULL`                                   | Amount paid to professional after fees.          |
| `payment_method_used_id`| `INT UNSIGNED`                                  | `NULL`                                       | Professional's payment method used for payout (references `payment_methods.id`). |
| `status`                | `ENUM('pending', 'in_escrow', 'processing', 'completed', 'failed', 'refunded', 'disputed')` | `NOT NULL`   | Status of the payment.                           |
| `transaction_id_gateway`| `VARCHAR(255)`                                  | `NULL`                                       | Transaction ID from the payment gateway.         |
| `escrow_release_at`     | `TIMESTAMP`                                     | `NULL`                                       | Scheduled time for escrow release.               |
| `paid_at`               | `TIMESTAMP`                                     | `NULL`                                       | Timestamp when payment was completed/released.   |
| `notes`                 | `TEXT`                                          | `NULL`                                       | Notes related to the payment.                    |
| `created_at`            | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of payment record creation.            |
| `updated_at`            | `TIMESTAMP`                                     | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Timestamp of last update.                        |
|                         |                                                 | `FOREIGN KEY (shift_application_id) REFERENCES shift_applications(id) ON DELETE CASCADE` |                                                  |
|                         |                                                 | `FOREIGN KEY (payer_facility_id) REFERENCES facilities(id) ON DELETE CASCADE` |                                                  |
|                         |                                                 | `FOREIGN KEY (payee_professional_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |
|                         |                                                 | `FOREIGN KEY (payment_method_used_id) REFERENCES payment_methods(id) ON DELETE SET NULL` |                                                  |

### 3.13. `notifications` (Optional but Recommended)

Stores notifications for users.

| Column Name     | Data Type      | Constraints                                  | Description                                      |
|-----------------|----------------|----------------------------------------------|--------------------------------------------------|
| `id`            | `INT UNSIGNED` | `PRIMARY KEY`, `AUTO_INCREMENT`              | Unique identifier.                               |
| `user_id`       | `INT UNSIGNED` | `NOT NULL`                                   | User to whom notification is addressed.          |
| `message`       | `TEXT`         | `NOT NULL`                                   | Notification content.                            |
| `link_url`      | `VARCHAR(255)` | `NULL`                                       | URL to navigate to from notification.            |
| `is_read`       | `BOOLEAN`      | `NOT NULL`, `DEFAULT FALSE`                  | Whether the notification has been read.          |
| `created_at`    | `TIMESTAMP`    | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP`      | Timestamp of creation.                           |
|                 |                | `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` |                                                  |

## 4. Relationships Summary (ERD Conceptual)

*   `users` is central, linked one-to-one with `healthcare_professionals` or `facilities` based on `user_type`.
*   `healthcare_professionals` have many `credentials`, many `professional_skills` (through `skills` table), many `shift_applications`, and many `professional_assessment_attempts`.
*   `facilities` post many `shifts`.
*   `shifts` have many `shift_applications`.
*   `shift_applications` lead to one `payment` (potentially).
*   `skills` are linked to `skill_assessments` (one skill can have one current assessment template).
*   `users` can have many `payment_methods` and many `notifications`.

## 5. Indexes

Appropriate indexes will be created on foreign key columns and columns frequently used in `WHERE` clauses or `JOIN` conditions to optimize query performance. For example:

*   `users.email`
*   `healthcare_professionals.user_id`
*   `credentials.professional_user_id`
*   `facilities.user_id`
*   `shifts.facility_id`, `shifts.start_datetime`
*   `shift_applications.shift_id`, `shift_applications.professional_user_id`
*   `payments.shift_application_id`
*   etc.

## 6. Data Integrity

*   Foreign key constraints will be used to maintain referential integrity.
*   `NOT NULL` constraints will be applied where appropriate.
*   Application-level validation will supplement database constraints.

## 7. Future Considerations

*   Tables for messaging between users.
*   Audit trail tables for critical actions.
*   Configuration tables for platform settings.

This schema provides a solid foundation for the healthcare staffing platform. Further refinements may occur during the development process.
