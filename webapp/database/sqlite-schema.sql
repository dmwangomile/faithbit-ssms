-- FaithBit SSMS SQLite Schema for Development
-- Simplified schema for demonstration without Docker

-- Users and Authentication
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    branch_id INTEGER,
    role TEXT CHECK(role IN ('admin', 'manager', 'cashier', 'sales_rep', 'technician', 'inventory_manager', 'procurement_officer')) NOT NULL,
    status TEXT CHECK(status IN ('active', 'inactive', 'suspended')) DEFAULT 'active',
    last_login_at DATETIME,
    auth_key VARCHAR(32),
    access_token TEXT,
    refresh_token TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Branches/Locations
CREATE TABLE branches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type TEXT CHECK(type IN ('retail_shop', 'warehouse', 'service_center', 'head_office')) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Tanzania',
    phone VARCHAR(20),
    email VARCHAR(255),
    manager_id INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Product Categories
CREATE TABLE product_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    parent_id INTEGER,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id)
);

-- Products
CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    name_sw VARCHAR(200),
    description TEXT,
    description_sw TEXT,
    category_id INTEGER NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    type TEXT CHECK(type IN ('product', 'service', 'kit', 'spare_part')) DEFAULT 'product',
    unit_of_measure VARCHAR(20) DEFAULT 'piece',
    barcode VARCHAR(100),
    qr_code VARCHAR(500),
    weight DECIMAL(10,3),
    dimensions VARCHAR(100),
    color VARCHAR(50),
    warranty_months INTEGER DEFAULT 0,
    has_serial INTEGER DEFAULT 0,
    has_imei INTEGER DEFAULT 0,
    track_expiry INTEGER DEFAULT 0,
    cost_price DECIMAL(15,2) DEFAULT 0.00,
    selling_price DECIMAL(15,2) DEFAULT 0.00,
    wholesale_price DECIMAL(15,2) DEFAULT 0.00,
    minimum_price DECIMAL(15,2) DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    reorder_level INTEGER DEFAULT 10,
    reorder_quantity INTEGER DEFAULT 50,
    is_active INTEGER DEFAULT 1,
    images TEXT, -- JSON
    attributes TEXT, -- JSON
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Customers
CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_number VARCHAR(20) NOT NULL UNIQUE,
    type TEXT CHECK(type IN ('individual', 'business')) DEFAULT 'individual',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    company_name VARCHAR(200),
    email VARCHAR(255),
    phone VARCHAR(20),
    phone2 VARCHAR(20),
    date_of_birth DATE,
    gender TEXT CHECK(gender IN ('male', 'female', 'other')),
    preferred_language TEXT CHECK(preferred_language IN ('en', 'sw')) DEFAULT 'en',
    address TEXT,
    city VARCHAR(100),
    region VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Tanzania',
    tax_number VARCHAR(50),
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    credit_terms INTEGER DEFAULT 0,
    loyalty_points INTEGER DEFAULT 0,
    total_purchases DECIMAL(15,2) DEFAULT 0.00,
    last_purchase_date DATE,
    is_active INTEGER DEFAULT 1,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);

-- Warehouses
CREATE TABLE warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    branch_id INTEGER NOT NULL,
    type TEXT CHECK(type IN ('main', 'retail', 'transit', 'quarantine')) DEFAULT 'main',
    address TEXT,
    manager_id INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- Inventory Stock
CREATE TABLE inventory_stock (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    warehouse_id INTEGER NOT NULL,
    bin_location VARCHAR(50),
    quantity_on_hand INTEGER DEFAULT 0,
    quantity_reserved INTEGER DEFAULT 0,
    cost_price DECIMAL(15,2) DEFAULT 0.00,
    last_movement_date DATETIME,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    UNIQUE(product_id, warehouse_id)
);

-- Insert sample data
INSERT INTO branches (code, name, type, city, region, is_active) VALUES
('HQ001', 'Head Office - Dodoma', 'head_office', 'Dodoma', 'Dodoma', 1),
('DSM001', 'Dar es Salaam Retail Shop', 'retail_shop', 'Dar es Salaam', 'Dar es Salaam', 1);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, branch_id, role, auth_key) VALUES
('admin', 'admin@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'System', 'Administrator', 1, 'admin', '1234567890abcdef1234567890abcdef');

-- Insert sample categories
INSERT INTO product_categories (name, description, sort_order) VALUES
('Mobile Phones', 'Smartphones and feature phones', 1),
('Laptops & Computers', 'Desktop computers, laptops, and accessories', 2),
('Accessories', 'Phone cases, chargers, cables, and other accessories', 3);

-- Insert sample products
INSERT INTO products (sku, name, name_sw, description, category_id, brand, model, barcode, cost_price, selling_price, wholesale_price) VALUES
('PH001', 'Samsung Galaxy A54', 'Samsung Galaxy A54', 'Mid-range smartphone with excellent camera', 1, 'Samsung', 'Galaxy A54', '6201234000001', 450000, 650000, 580000),
('PH002', 'iPhone 13 Pro', 'iPhone 13 Pro', 'Premium smartphone with advanced features', 1, 'Apple', 'iPhone 13 Pro', '6201234000002', 1800000, 2200000, 2000000),
('AC001', 'Phone Case Universal', 'Kifuniko cha Simu', 'Universal phone case fits most devices', 3, 'Generic', 'Universal', '6201234000006', 5000, 15000, 12000);

-- Insert warehouse
INSERT INTO warehouses (code, name, branch_id, type) VALUES
('WH001', 'Main Warehouse', 1, 'main'),
('WH002', 'DSM Store', 2, 'retail');

-- Insert inventory
INSERT INTO inventory_stock (product_id, warehouse_id, bin_location, quantity_on_hand, cost_price) VALUES
(1, 2, 'A1-01', 15, 450000),
(2, 2, 'A1-02', 8, 1800000),
(3, 2, 'D4-01', 150, 5000);

-- Insert sample customers
INSERT INTO customers (customer_number, type, first_name, last_name, email, phone, city, region, loyalty_points) VALUES
('C250900001', 'individual', 'James', 'Mwalimu', 'james.mwalimu@email.com', '+255756123456', 'Dar es Salaam', 'Dar es Salaam', 150),
('C250900002', 'individual', 'Fatuma', 'Hassan', 'fatuma.hassan@email.com', '+255713987654', 'Dar es Salaam', 'Dar es Salaam', 80),
('C250900003', 'business', NULL, NULL, 'info@mwangatech.co.tz', '+255754111222', 'Dar es Salaam', 'Dar es Salaam', 500);