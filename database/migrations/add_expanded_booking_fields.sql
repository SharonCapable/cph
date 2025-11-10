-- Migration: Add expanded booking fields for visa letters and detailed guest information
-- Run this migration to add new fields to the bookings table

ALTER TABLE bookings
-- Guest Information
ADD COLUMN guest_full_name VARCHAR(255) AFTER phone,
ADD COLUMN guest_email VARCHAR(255) AFTER guest_full_name,
ADD COLUMN guest_nationality VARCHAR(100) AFTER guest_email,
ADD COLUMN guest_date_of_birth DATE AFTER guest_nationality,
ADD COLUMN guest_gender ENUM('male', 'female', 'other') AFTER guest_date_of_birth,
ADD COLUMN guest_passport_number VARCHAR(100) AFTER guest_gender,
ADD COLUMN guest_address TEXT AFTER guest_passport_number,

-- Travel Information
ADD COLUMN purpose_of_visit TEXT AFTER guest_address,
ADD COLUMN arrival_date DATE AFTER purpose_of_visit,
ADD COLUMN arrival_flight VARCHAR(100) AFTER arrival_date,
ADD COLUMN departure_date DATE AFTER arrival_flight,
ADD COLUMN departure_flight VARCHAR(100) AFTER departure_date,

-- Emergency Contact
ADD COLUMN emergency_contact_name VARCHAR(255) AFTER departure_flight,
ADD COLUMN emergency_contact_relationship VARCHAR(100) AFTER emergency_contact_name,
ADD COLUMN emergency_contact_phone VARCHAR(20) AFTER emergency_contact_relationship,
ADD COLUMN emergency_contact_email VARCHAR(255) AFTER emergency_contact_phone,

-- Declaration and Agreement
ADD COLUMN terms_accepted BOOLEAN DEFAULT 0 AFTER emergency_contact_email,
ADD COLUMN signature_data TEXT AFTER terms_accepted,
ADD COLUMN signature_date TIMESTAMP NULL AFTER signature_data,

-- Visa/Foreigner status
ADD COLUMN is_foreigner BOOLEAN DEFAULT 0 AFTER signature_date,
ADD COLUMN requires_visa_letter BOOLEAN DEFAULT 0 AFTER is_foreigner,

-- PDF Documents
ADD COLUMN booking_letter_path VARCHAR(255) AFTER requires_visa_letter,
ADD COLUMN visa_letter_path VARCHAR(255) AFTER booking_letter_path;

-- Add indexes for common queries
CREATE INDEX idx_is_foreigner ON bookings(is_foreigner);
CREATE INDEX idx_guest_nationality ON bookings(guest_nationality);
