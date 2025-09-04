-- FaithBit Sales & Service Management System Database Schema
-- Created: 2025-09-03
-- Version: 1.0

SET FOREIGN_KEY_CHECKS = 0;

-- Users and Authentication
CREATE TABLE `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `branch_id` INT,
    `role` ENUM('admin', 'manager', 'cashier', 'sales_rep', 'technician', 'inventory_manager', 'procurement_officer') NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `last_login_at` DATETIME,
    `password_reset_token` VARCHAR(255),
    `auth_key` VARCHAR(32),
    `access_token` TEXT,
    `refresh_token` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_branch_id` (`branch_id`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branches/Locations
CREATE TABLE `branches` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('retail_shop', 'warehouse', 'service_center', 'head_office') NOT NULL,
    `address` TEXT,
    `city` VARCHAR(100),
    `region` VARCHAR(100),
    `country` VARCHAR(100) DEFAULT 'Tanzania',
    `phone` VARCHAR(20),
    `email` VARCHAR(255),
    `manager_id` INT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`),
    INDEX `idx_manager_id` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Categories
CREATE TABLE `product_categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `parent_id` INT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `image_url` VARCHAR(500),
    `sort_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_parent_id` (`parent_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `product_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE `products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(200) NOT NULL,
    `name_sw` VARCHAR(200), -- Swahili name
    `description` TEXT,
    `description_sw` TEXT, -- Swahili description
    `category_id` INT NOT NULL,
    `brand` VARCHAR(100),
    `model` VARCHAR(100),
    `type` ENUM('product', 'service', 'kit', 'spare_part') DEFAULT 'product',
    `unit_of_measure` VARCHAR(20) DEFAULT 'piece',
    `barcode` VARCHAR(100),
    `qr_code` VARCHAR(500),
    `weight` DECIMAL(10,3),
    `dimensions` VARCHAR(100),
    `color` VARCHAR(50),
    `warranty_months` INT DEFAULT 0,
    `has_serial` BOOLEAN DEFAULT FALSE,
    `has_imei` BOOLEAN DEFAULT FALSE,
    `track_expiry` BOOLEAN DEFAULT FALSE,
    `cost_price` DECIMAL(15,2) DEFAULT 0.00,
    `selling_price` DECIMAL(15,2) DEFAULT 0.00,
    `wholesale_price` DECIMAL(15,2) DEFAULT 0.00,
    `minimum_price` DECIMAL(15,2) DEFAULT 0.00,
    `tax_rate` DECIMAL(5,2) DEFAULT 18.00,
    `reorder_level` INT DEFAULT 10,
    `reorder_quantity` INT DEFAULT 50,
    `is_active` BOOLEAN DEFAULT TRUE,
    `images` JSON,
    `attributes` JSON,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_brand` (`brand`),
    INDEX `idx_barcode` (`barcode`),
    FULLTEXT KEY `ft_name_description` (`name`, `description`),
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Kits/Bundles
CREATE TABLE `product_kits` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `parent_product_id` INT NOT NULL,
    `child_product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_kit_components` (`parent_product_id`, `child_product_id`),
    FOREIGN KEY (`parent_product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`child_product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers
CREATE TABLE `customers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `customer_number` VARCHAR(20) NOT NULL UNIQUE,
    `type` ENUM('individual', 'business') DEFAULT 'individual',
    `first_name` VARCHAR(100),
    `last_name` VARCHAR(100),
    `company_name` VARCHAR(200),
    `email` VARCHAR(255),
    `phone` VARCHAR(20),
    `phone2` VARCHAR(20),
    `date_of_birth` DATE,
    `gender` ENUM('male', 'female', 'other'),
    `preferred_language` ENUM('en', 'sw') DEFAULT 'en',
    `address` TEXT,
    `city` VARCHAR(100),
    `region` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `country` VARCHAR(100) DEFAULT 'Tanzania',
    `tax_number` VARCHAR(50),
    `credit_limit` DECIMAL(15,2) DEFAULT 0.00,
    `credit_terms` INT DEFAULT 0, -- days
    `loyalty_points` INT DEFAULT 0,
    `total_purchases` DECIMAL(15,2) DEFAULT 0.00,
    `last_purchase_date` DATE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_customer_number` (`customer_number`),
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phone`),
    INDEX `idx_company_name` (`company_name`),
    FULLTEXT KEY `ft_customer_search` (`first_name`, `last_name`, `company_name`, `email`, `phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers
CREATE TABLE `suppliers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `supplier_number` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(200) NOT NULL,
    `contact_person` VARCHAR(100),
    `email` VARCHAR(255),
    `phone` VARCHAR(20),
    `address` TEXT,
    `city` VARCHAR(100),
    `country` VARCHAR(100),
    `tax_number` VARCHAR(50),
    `payment_terms` INT DEFAULT 30, -- days
    `currency` VARCHAR(3) DEFAULT 'TZS',
    `rating` DECIMAL(3,2) DEFAULT 0.00,
    `is_active` BOOLEAN DEFAULT TRUE,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_supplier_number` (`supplier_number`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Warehouses
CREATE TABLE `warehouses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `branch_id` INT NOT NULL,
    `type` ENUM('main', 'retail', 'transit', 'quarantine') DEFAULT 'main',
    `address` TEXT,
    `manager_id` INT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`),
    INDEX `idx_branch_id` (`branch_id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Stock
CREATE TABLE `inventory_stock` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `warehouse_id` INT NOT NULL,
    `bin_location` VARCHAR(50),
    `quantity_on_hand` INT DEFAULT 0,
    `quantity_reserved` INT DEFAULT 0,
    `quantity_available` INT GENERATED ALWAYS AS (`quantity_on_hand` - `quantity_reserved`) STORED,
    `cost_price` DECIMAL(15,2) DEFAULT 0.00,
    `last_movement_date` DATETIME,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_product_warehouse` (`product_id`, `warehouse_id`),
    INDEX `idx_warehouse_id` (`warehouse_id`),
    INDEX `idx_quantity_available` (`quantity_available`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Serial/IMEI Tracking
CREATE TABLE `product_serials` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `warehouse_id` INT NOT NULL,
    `serial_number` VARCHAR(100) NOT NULL,
    `imei_number` VARCHAR(20),
    `batch_number` VARCHAR(50),
    `expiry_date` DATE,
    `manufacture_date` DATE,
    `supplier_id` INT,
    `purchase_order_id` INT,
    `cost_price` DECIMAL(15,2),
    `status` ENUM('available', 'reserved', 'sold', 'damaged', 'returned') DEFAULT 'available',
    `sold_to_customer_id` INT,
    `sold_date` DATETIME,
    `warranty_start_date` DATE,
    `warranty_end_date` DATE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_serial` (`serial_number`),
    UNIQUE KEY `uk_imei` (`imei_number`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_warehouse_id` (`warehouse_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expiry_date` (`expiry_date`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`sold_to_customer_id`) REFERENCES `customers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Quotes
CREATE TABLE `sales_quotes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `quote_number` VARCHAR(20) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `sales_rep_id` INT NOT NULL,
    `branch_id` INT NOT NULL,
    `quote_date` DATE NOT NULL,
    `valid_until` DATE NOT NULL,
    `status` ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'converted') DEFAULT 'draft',
    `subtotal` DECIMAL(15,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'TZS',
    `notes` TEXT,
    `terms_conditions` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_quote_number` (`quote_number`),
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_sales_rep_id` (`sales_rep_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_quote_date` (`quote_date`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`sales_rep_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Orders
CREATE TABLE `sales_orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_number` VARCHAR(20) NOT NULL UNIQUE,
    `quote_id` INT,
    `customer_id` INT NOT NULL,
    `sales_rep_id` INT NOT NULL,
    `branch_id` INT NOT NULL,
    `order_date` DATE NOT NULL,
    `required_date` DATE,
    `promised_date` DATE,
    `status` ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    `subtotal` DECIMAL(15,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
    `shipping_amount` DECIMAL(15,2) DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'TZS',
    `payment_terms` INT DEFAULT 0,
    `shipping_address` TEXT,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_sales_rep_id` (`sales_rep_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_order_date` (`order_date`),
    FOREIGN KEY (`quote_id`) REFERENCES `sales_quotes`(`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`sales_rep_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document line items (shared by quotes, orders, invoices)
CREATE TABLE `document_line_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `document_type` ENUM('quote', 'order', 'invoice', 'credit_note') NOT NULL,
    `document_id` INT NOT NULL,
    `line_number` INT NOT NULL,
    `product_id` INT NOT NULL,
    `description` VARCHAR(500),
    `quantity` DECIMAL(10,3) NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
    `tax_rate` DECIMAL(5,2) DEFAULT 18.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `line_total` DECIMAL(15,2) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_document` (`document_type`, `document_id`),
    INDEX `idx_product_id` (`product_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Work Orders
CREATE TABLE `service_work_orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `work_order_number` VARCHAR(20) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `device_serial` VARCHAR(100),
    `device_imei` VARCHAR(20),
    `product_id` INT,
    `problem_description` TEXT NOT NULL,
    `problem_description_sw` TEXT,
    `diagnosis` TEXT,
    `resolution` TEXT,
    `technician_id` INT,
    `branch_id` INT NOT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `status` ENUM('received', 'diagnosed', 'waiting_parts', 'in_progress', 'completed', 'delivered', 'cancelled') DEFAULT 'received',
    `job_type` ENUM('warranty', 'paid_repair', 'consultation', 'installation', 'maintenance') NOT NULL,
    `estimated_hours` DECIMAL(5,2),
    `actual_hours` DECIMAL(5,2),
    `labor_rate` DECIMAL(10,2) DEFAULT 0.00,
    `parts_cost` DECIMAL(15,2) DEFAULT 0.00,
    `labor_cost` DECIMAL(15,2) DEFAULT 0.00,
    `total_cost` DECIMAL(15,2) DEFAULT 0.00,
    `customer_approval_required` BOOLEAN DEFAULT FALSE,
    `customer_approved` BOOLEAN DEFAULT FALSE,
    `customer_approved_at` DATETIME,
    `sla_hours` INT DEFAULT 48,
    `received_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `started_at` DATETIME,
    `completed_at` DATETIME,
    `delivered_at` DATETIME,
    `warranty_start_date` DATE,
    `warranty_end_date` DATE,
    `customer_signature` TEXT,
    `technician_notes` TEXT,
    `images` JSON,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_work_order_number` (`work_order_number`),
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_technician_id` (`technician_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_device_serial` (`device_serial`),
    INDEX `idx_device_imei` (`device_imei`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Transactions
CREATE TABLE `payment_transactions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `transaction_number` VARCHAR(50) NOT NULL UNIQUE,
    `reference_type` ENUM('invoice', 'order', 'work_order', 'pos_sale') NOT NULL,
    `reference_id` INT NOT NULL,
    `customer_id` INT,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'TZS',
    `payment_method` ENUM('cash', 'mpesa', 'tigo_pesa', 'airtel_money', 'bank_transfer', 'card', 'credit') NOT NULL,
    `provider_reference` VARCHAR(100),
    `phone_number` VARCHAR(20),
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    `gateway_response` JSON,
    `processed_at` DATETIME,
    `branch_id` INT NOT NULL,
    `processed_by` INT,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_transaction_number` (`transaction_number`),
    INDEX `idx_reference` (`reference_type`, `reference_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_provider_reference` (`provider_reference`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs
CREATE TABLE `audit_logs` (
    `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(50) NOT NULL,
    `record_id` INT,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_table_name` (`table_name`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;