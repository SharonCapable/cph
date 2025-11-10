-- Migration: Add expanded booking fields (Version 2 - More Robust)
-- This version adds columns in smaller batches for easier debugging

-- Guest Information
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_full_name VARCHAR(255) AFTER phone;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_email VARCHAR(255) AFTER guest_full_name;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_nationality VARCHAR(100) AFTER guest_email;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_date_of_birth DATE AFTER guest_nationality;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_gender ENUM('male', 'female', 'other') AFTER guest_date_of_birth;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_passport_number VARCHAR(100) AFTER guest_gender;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_address TEXT AFTER guest_passport_number;

-- Travel Information
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS purpose_of_visit TEXT AFTER guest_address;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS arrival_date DATE AFTER purpose_of_visit;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS arrival_flight VARCHAR(100) AFTER arrival_date;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS departure_date DATE AFTER arrival_flight;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS departure_flight VARCHAR(100) AFTER departure_date;

-- Emergency Contact
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(255) AFTER departure_flight;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS emergency_contact_relationship VARCHAR(100) AFTER emergency_contact_name;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(20) AFTER emergency_contact_relationship;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS emergency_contact_email VARCHAR(255) AFTER emergency_contact_phone;

-- Declaration and Agreement
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS terms_accepted BOOLEAN DEFAULT 0 AFTER emergency_contact_email;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS signature_data TEXT AFTER terms_accepted;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS signature_date TIMESTAMP NULL AFTER signature_data;

-- Visa/Foreigner status
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS is_foreigner BOOLEAN DEFAULT 0 AFTER signature_date;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS requires_visa_letter BOOLEAN DEFAULT 0 AFTER is_foreigner;

-- PDF Documents
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS booking_letter_path VARCHAR(255) AFTER requires_visa_letter;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS visa_letter_path VARCHAR(255) AFTER booking_letter_path;

-- Add indexes (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_is_foreigner ON bookings(is_foreigner);
CREATE INDEX IF NOT EXISTS idx_guest_nationality ON bookings(guest_nationality);
