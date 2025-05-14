# Healthcare Staffing Platform - LAMP Stack Requirements

## 1. Introduction

This document outlines the requirements for a healthcare staffing platform to be developed using a LAMP (Linux, Apache, MySQL, PHP) stack with plain JavaScript for the frontend. The platform aims to connect healthcare professionals with healthcare facilities, providing features for profile management, credential verification, shift posting and application, secure payments, and skills assessment. The platform must be mobile-responsive.

This project re-implements the functionality of a previously designed system (originally planned for Laravel/React) using a traditional LAMP stack approach.

## 2. User Roles

The platform will support the following user roles:

1.  **Healthcare Professional (Worker):** Individuals such as nurses (RN, LPN/LVN), CNAs, and other healthcare aides seeking per diem or contract work.
2.  **Facility Administrator (Company/Client):** Representatives from hospitals, nursing homes, clinics, and other healthcare facilities looking to fill shifts.
3.  **Platform Administrator (Admin):** Superusers responsible for managing the platform, users, verifying credentials, and overseeing operations.

## 3. Functional Requirements

### 3.1. User Account Management (Common for all roles)

*   **FR3.1.1:** Users shall be able to register for an account by selecting their role (Professional or Facility).
*   **FR3.1.2:** Users shall be able to log in securely using email and password.
*   **FR3.1.3:** Users shall be able to log out.
*   **FR3.1.4:** Users shall be able to reset their password via an email-based verification process.
*   **FR3.1.5:** Users shall be able to view and edit their basic profile information (name, email, phone, address).
*   **FR3.1.6:** Platform Administrators shall be able to manage user accounts (activate, deactivate, suspend, delete, change roles).

### 3.2. Healthcare Professional Features

*   **FR3.2.1: Profile Management**
    *   Professionals shall be able to create and maintain a detailed profile including:
        *   Personal information (already covered in 3.1.5).
        *   Professional summary/bio.
        *   Years of experience.
        *   Profession type (e.g., RN, CNA).
        *   Specialties.
        *   Desired hourly rate (optional).
        *   Availability calendar/schedule.
*   **FR3.2.2: Credential Management (Uploads)**
    *   Professionals shall be able to upload digital copies of their credentials, including:
        *   Professional Licenses (e.g., RN license).
        *   Certifications (e.g., BLS, ACLS).
        *   Educational documents (e.g., diplomas, degrees).
        *   Vaccination/Immunization records (e.g., COVID-19, Flu shot).
        *   Identification documents (e.g., Driver's License, Passport for I-9 verification purposes).
    *   Supported file formats for uploads: PDF, JPG/JPEG, PNG.
    *   Professionals shall be able to view their uploaded credentials and their verification status.
    *   Professionals shall receive notifications for expiring credentials.
*   **FR3.2.3: Shift Management**
    *   Professionals shall be able to browse and search for available shifts using filters (location, date, facility type, profession, pay rate).
    *   Professionals shall be able to view detailed information for each shift (facility, date/time, duration, requirements, pay).
    *   Professionals shall be able to apply for shifts.
    *   Professionals shall be able to view the status of their applications (pending, accepted, rejected, cancelled).
    *   Professionals shall be able to withdraw an application if the shift has not yet been confirmed.
    *   Professionals shall be able to view their scheduled/confirmed shifts.
    *   Professionals shall be able to check-in and check-out for shifts (potentially via a simple button click, timestamped).
*   **FR3.2.4: Skills Assessment (Covered in 3.6)
*   **FR3.2.5: Payment Management (Covered in 3.5)

### 3.3. Healthcare Facility Features

*   **FR3.3.1: Profile Management**
    *   Facilities shall be able to create and maintain a facility profile including:
        *   Facility name, type (hospital, nursing home, etc.).
        *   Address, contact information.
        *   Facility description, logo.
        *   Verification documents (e.g., business license).
*   **FR3.3.2: Shift Management**
    *   Facilities shall be able to post new shifts, specifying:
        *   Date, start time, end time, duration.
        *   Required profession (RN, CNA, etc.) and specialty.
        *   Number of positions available.
        *   Required skills and experience.
        *   Offered hourly rate.
        *   Shift description and duties.
    *   Facilities shall be able to view and manage their posted shifts (edit, cancel, view applicants).
    *   Facilities shall be able to review applications from professionals, including their profiles, credentials (verified status), and skill assessment rankings.
    *   Facilities shall be able to accept or reject applications.
    *   Facilities shall be able to communicate with applicants/confirmed professionals (basic messaging).
*   **FR3.3.3: Payment Management (Covered in 3.5)

### 3.4. Platform Administrator Features

*   **FR3.4.1: User Management (as per FR3.1.6)
*   **FR3.4.2: Credential Verification**
    *   Admins shall be able to review credentials uploaded by professionals.
    *   Admins shall be able to mark credentials as verified, rejected (with reason), or pending.
    *   Admins shall be able to manage the Texas state board license verification process (see 3.7).
*   **FR3.4.3: Shift Oversight**
    *   Admins shall be able to view all shifts posted on the platform.
    *   Admins shall be able to intervene in shift disputes if necessary.
*   **FR3.4.4: Payment Oversight (Covered in 3.5)
*   **FR3.4.5: Skills and Assessment Management (Covered in 3.6)
*   **FR3.4.6: Platform Configuration**
    *   Admins shall be able to manage platform settings (e.g., fee structures, notification templates).

### 3.5. Payment System

*   **FR3.5.1:** The platform shall support multiple payment methods for facilities to pay professionals, including:
    *   PayPal (linked to user account: @cubeloid)
    *   CashApp (linked to user account: @clairuth)
    *   Coinbase (linked to user account: cubeloid@gmail.com)
    *   Zelle (details to be confirmed for integration feasibility with PHP)
*   **FR3.5.2:** Professionals shall be able to add and manage their preferred payout methods.
*   **FR3.5.3:** The platform shall implement an escrow system:
    *   Funds from the facility for a completed shift are held in escrow for a defined period (e.g., 3 days).
    *   Funds are automatically released to the professional after the escrow period if no disputes are raised.
*   **FR3.5.4:** The platform shall facilitate dispute resolution for payments.
*   **FR3.5.5:** Users (professionals and facilities) shall be able to view their transaction history.
*   **FR3.5.6:** Platform administrators shall be able to oversee transactions and manage payment-related disputes.
*   **FR3.5.7:** The system must securely handle payment information and API keys for payment gateways.

### 3.6. Skills Assessment

*   **FR3.6.1:** The platform shall feature a skills assessment module for various healthcare professions (e.g., RN, CNA).
*   **FR3.6.2:** Assessments shall consist of multiple-choice questions or checklists relevant to the profession and skill being assessed.
*   **FR3.6.3:** Professionals shall be able to take assessments to demonstrate their proficiency.
*   **FR3.6.4:** The system shall automatically score assessments and provide a ranking (e.g., Beginner, Intermediate, Advanced, Expert) based on performance.
*   **FR3.6.5:** Assessment results and rankings shall be visible on the professional's profile (to facilities reviewing applications).
*   **FR3.6.6:** Platform administrators shall be able to create, manage, and update skills, assessment questions, and ranking criteria.

### 3.7. Texas State Board License Verification

*   **FR3.7.1:** The platform shall integrate a mechanism to verify healthcare professional licenses with the Texas state board(s) of nursing (and other relevant professions).
*   **FR3.7.2:** This verification can be attempted automatically upon license information submission or manually triggered by an administrator.
*   **FR3.7.3:** The verification status (e.g., active, expired, disciplined) shall be stored and displayed.
*   **FR3.7.4:** If direct API access to Texas state boards is not available, the system may need to guide admins to perform manual verification using the board's public website, with a feature to record the verification outcome.

### 3.8. Notifications

*   **FR3.8.1:** Users shall receive in-platform and/or email notifications for important events, such as:
    *   New shift applications (for facilities).
    *   Application status updates (for professionals).
    *   Shift confirmations and cancellations.
    *   Payment confirmations and issues.
    *   Credential expiration reminders.
    *   New messages.

## 4. Non-Functional Requirements

*   **NFR4.1: Security**
    *   All sensitive data (passwords, personal information, payment details) must be stored securely (e.g., hashed passwords, encryption for certain data if necessary).
    *   The application must be protected against common web vulnerabilities (XSS, SQL Injection, CSRF).
    *   Regular security updates for PHP, Apache, MySQL, and any third-party libraries must be considered.
    *   Compliance with relevant data privacy regulations (e.g., HIPAA considerations for handling health-related information, even if indirectly) should be maintained.
*   **NFR4.2: Performance**
    *   The platform should load pages within an acceptable timeframe (e.g., under 3-5 seconds for typical pages).
    *   Database queries should be optimized for speed.
    *   The system should handle a reasonable number of concurrent users based on expected load.
*   **NFR4.3: Usability & Accessibility**
    *   The user interface must be intuitive and easy to navigate for all user roles.
    *   The platform must be mobile-responsive, providing a good user experience on smartphones and tablets.
    *   Basic web accessibility standards (WCAG AA where feasible) should be considered.
*   **NFR4.4: Scalability**
    *   The LAMP stack architecture should be implemented in a way that allows for future scaling if user load increases (e.g., optimizing database, potential for load balancing if moving beyond shared hosting).
*   **NFR4.5: Maintainability**
    *   PHP code should be well-structured, commented, and follow good coding practices to facilitate future maintenance and updates.
    *   JavaScript code should be organized and manageable.

## 5. Technology Stack

*   **Operating System:** Linux (as part of LAMP)
*   **Web Server:** Apache
*   **Database:** MySQL
*   **Backend Programming Language:** PHP (procedural or basic OOP, without a heavy framework unless specified otherwise)
*   **Frontend:** HTML5, CSS3, Plain JavaScript (ES6+ features acceptable).
*   **Mobile Responsiveness:** Achieved through CSS media queries, flexible layouts (e.g., Flexbox, Grid).

## 6. Deployment

*   The application should be deployable on a standard LAMP hosting environment, such as HostGator shared hosting.

## 7. Future Considerations (Out of Scope for Initial Build but good to keep in mind)

*   Advanced messaging/chat system.
*   Direct calendar integrations.
*   Mobile applications (native or PWA beyond responsive web).
*   Automated invoicing.
*   Advanced reporting and analytics for facilities and admins.
