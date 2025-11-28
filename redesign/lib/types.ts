// API Response Types
export interface ApiResponse<T> {
  success: boolean
  data?: T
  error?: string
  message?: string
}

// Property Types
export interface Property {
  id: string
  title: string
  description: string
  location: string
  address: string
  bedrooms: number
  bathrooms: number
  price_per_month: number
  security_deposit: number
  property_type: string
  furnishing_status: string
  available_from: string
  status: 'available' | 'occupied' | 'maintenance'
  amenities: string[]
  images: PropertyImage[]
  manager_id: string
  created_at: string
  updated_at: string
}

export interface PropertyImage {
  id: string
  property_id: string
  image_path: string
  is_primary: boolean
  display_order: number
}

// Booking Types
export interface Booking {
  id: string
  property_id: string
  user_id: string
  check_in: string
  check_out: string
  guests: number
  total_price: number
  phone: string
  message?: string
  guest_full_name: string
  guest_email: string
  guest_nationality: string
  guest_date_of_birth: string
  guest_gender: string
  guest_passport_number: string
  guest_address: string
  purpose_of_visit: string
  arrival_date: string
  arrival_flight?: string
  departure_date: string
  departure_flight?: string
  emergency_contact_name: string
  emergency_contact_relationship: string
  emergency_contact_phone: string
  emergency_contact_email?: string
  terms_accepted: boolean
  signature_data: string
  signature_date: string
  is_foreigner: boolean
  requires_visa_letter: boolean
  status: 'pending' | 'approved' | 'rejected' | 'cancelled'
  booking_letter_path?: string
  visa_letter_path?: string
  created_at: string
  updated_at: string
}

export interface BookingFormData {
  property_id: string
  check_in: string
  check_out: string
  guests: number
  phone: string
  message?: string
  guest_full_name: string
  guest_email: string
  guest_nationality: string
  guest_date_of_birth: string
  guest_gender: string
  guest_passport_number: string
  guest_address: string
  purpose_of_visit: string
  arrival_date: string
  arrival_flight?: string
  departure_date: string
  departure_flight?: string
  emergency_contact_name: string
  emergency_contact_relationship: string
  emergency_contact_phone: string
  emergency_contact_email?: string
  terms_accepted: boolean
  signature_data: string
  is_foreigner: boolean
  requires_visa_letter: boolean
}

// User Types
export interface User {
  id: string
  email: string
  full_name: string
  phone: string
  role: 'super_admin' | 'property_manager' | 'guest'
  status: 'active' | 'inactive'
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface SignupData {
  email: string
  password: string
  full_name: string
  phone: string
  role?: string
}

// Application Types
export interface Application {
  id: string
  full_name: string
  email: string
  phone: string
  company_name?: string
  properties_count: number
  experience_years: number
  message: string
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  updated_at: string
}
