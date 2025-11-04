-- CirclePoint Homes Database Schema
-- Run this file to set up all required tables

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id VARCHAR(32) PRIMARY KEY,
    manager_id VARCHAR(32) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    property_type ENUM('apartment', 'house', 'condo', 'villa', 'studio') NOT NULL,
    status ENUM('available', 'rented', 'maintenance', 'pending') DEFAULT 'available',

    -- Location
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),

    -- Property details
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    square_feet INT,
    furnished BOOLEAN DEFAULT 0,
    pets_allowed BOOLEAN DEFAULT 0,
    parking BOOLEAN DEFAULT 0,

    -- Pricing
    price_per_month DECIMAL(10, 2) NOT NULL,
    security_deposit DECIMAL(10, 2),

    -- Amenities (JSON array)
    amenities TEXT,

    -- Images
    featured_image VARCHAR(255),

    -- SEO
    slug VARCHAR(255) UNIQUE,

    -- Metadata
    views INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_price (price_per_month),
    INDEX idx_bedrooms (bedrooms),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id VARCHAR(32) PRIMARY KEY,
    property_id VARCHAR(32) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property manager applications
CREATE TABLE IF NOT EXISTS property_manager_applications (
    id VARCHAR(32) PRIMARY KEY,
    user_id VARCHAR(32) NOT NULL,

    -- Company/Personal info
    company_name VARCHAR(255),
    business_type ENUM('individual', 'company', 'agency') NOT NULL,
    phone VARCHAR(20) NOT NULL,

    -- Address
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),

    -- Business details
    years_experience INT,
    number_of_properties INT,
    description TEXT,
    website VARCHAR(255),

    -- Verification documents (JSON array of file paths)
    documents TEXT,

    -- Application status
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by VARCHAR(32),
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User favorites
CREATE TABLE IF NOT EXISTS favorites (
    id VARCHAR(32) PRIMARY KEY,
    user_id VARCHAR(32) NOT NULL,
    property_id VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id),
    INDEX idx_user (user_id),
    INDEX idx_property (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings/Inquiries
CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(32) PRIMARY KEY,
    property_id VARCHAR(32) NOT NULL,
    user_id VARCHAR(32) NOT NULL,

    -- Booking details
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,

    -- Pricing
    total_price DECIMAL(10, 2) NOT NULL,

    -- Contact info
    message TEXT,
    phone VARCHAR(20),

    -- Status
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',

    -- Manager response
    manager_response TEXT,
    responded_at TIMESTAMP NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_dates (check_in, check_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews (optional for future)
CREATE TABLE IF NOT EXISTS reviews (
    id VARCHAR(32) PRIMARY KEY,
    property_id VARCHAR(32) NOT NULL,
    user_id VARCHAR(32) NOT NULL,
    booking_id VARCHAR(32),

    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,

    -- Manager response
    manager_response TEXT,
    responded_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log (for admin tracking)
CREATE TABLE IF NOT EXISTS activity_log (
    id VARCHAR(32) PRIMARY KEY,
    user_id VARCHAR(32),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id VARCHAR(32),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
