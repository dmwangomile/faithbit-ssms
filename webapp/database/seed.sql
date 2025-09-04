-- FaithBit SSMS Seed Data
-- Sample data for development and testing

-- Insert branches
INSERT INTO `branches` (`code`, `name`, `type`, `address`, `city`, `region`, `country`, `phone`, `email`, `is_active`) VALUES
('HQ001', 'Head Office - Dodoma', 'head_office', 'Plot 123, Uhuru Street', 'Dodoma', 'Dodoma', 'Tanzania', '+255123456789', 'hq@faithbit.com', 1),
('DSM001', 'Dar es Salaam Retail Shop', 'retail_shop', 'Plot 456, Kariakoo Street', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', '+255987654321', 'dsm@faithbit.com', 1),
('ARU001', 'Arusha Service Center', 'service_center', 'Plot 789, Sokoine Road', 'Arusha', 'Arusha', 'Tanzania', '+255555666777', 'arusha@faithbit.com', 1),
('MWZ001', 'Mwanza Warehouse', 'warehouse', 'Industrial Area, Block A', 'Mwanza', 'Mwanza', 'Tanzania', '+255444333222', 'mwanza@faithbit.com', 1);

-- Insert admin user
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `branch_id`, `role`, `status`, `auth_key`) VALUES
('admin', 'admin@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'System', 'Administrator', '+255700000001', 1, 'admin', 'active', '1234567890abcdef1234567890abcdef');

-- Insert sample users
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `branch_id`, `role`, `status`, `auth_key`) VALUES
('manager1', 'manager@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'John', 'Mwalimu', '+255700000002', 2, 'manager', 'active', '2345678901bcdef12345678901bcdef1'),
('cashier1', 'cashier@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'Mary', 'Juma', '+255700000003', 2, 'cashier', 'active', '3456789012cdef123456789012cdef12'),
('tech1', 'technician@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'Peter', 'Mwangi', '+255700000004', 3, 'technician', 'active', '456789013def1234567890123def123'),
('sales1', 'sales@faithbit.com', '$2y$13$nJ5lZVDhU8W6ARs8gG5Ry.p7ZGtRq5zA3mQGJYM7RnXDR4RnZGRy.', 'Grace', 'Komba', '+255700000005', 2, 'sales_rep', 'active', '56789014ef12345678901234ef1234');

-- Insert product categories
INSERT INTO `product_categories` (`name`, `description`, `sort_order`, `is_active`) VALUES
('Mobile Phones', 'Smartphones and feature phones', 1, 1),
('Laptops & Computers', 'Desktop computers, laptops, and accessories', 2, 1),
('Tablets', 'Android and iOS tablets', 3, 1),
('Accessories', 'Phone cases, chargers, cables, and other accessories', 4, 1),
('Audio & Video', 'Headphones, speakers, and audio equipment', 5, 1),
('Smart Watches', 'Fitness trackers and smartwatches', 6, 1),
('Gaming', 'Gaming consoles and accessories', 7, 1),
('Home Appliances', 'TVs, refrigerators, and other home appliances', 8, 1);

-- Insert sample products
INSERT INTO `products` (`sku`, `name`, `name_sw`, `description`, `description_sw`, `category_id`, `brand`, `model`, `type`, `barcode`, `has_serial`, `has_imei`, `cost_price`, `selling_price`, `wholesale_price`, `tax_rate`, `reorder_level`, `reorder_quantity`, `is_active`) VALUES
('PH001', 'Samsung Galaxy A54', 'Samsung Galaxy A54', 'Mid-range smartphone with excellent camera', 'Simu ya kati yenye kamera nzuri', 1, 'Samsung', 'Galaxy A54', 'product', '6201234000001', 1, 1, 450000, 650000, 580000, 18.00, 5, 20, 1),
('PH002', 'iPhone 13 Pro', 'iPhone 13 Pro', 'Premium smartphone with advanced features', 'Simu ya hali ya juu yenye vipengele vya kisasa', 1, 'Apple', 'iPhone 13 Pro', 'product', '6201234000002', 1, 1, 1800000, 2200000, 2000000, 18.00, 3, 10, 1),
('LT001', 'Dell Inspiron 15', 'Dell Inspiron 15', 'Business laptop with Intel i5 processor', 'Kompyuta ya biashara yenye kichakataji cha Intel i5', 2, 'Dell', 'Inspiron 15 3520', 'product', '6201234000003', 1, 0, 850000, 1200000, 1100000, 18.00, 2, 10, 1),
('LT002', 'HP Pavilion Gaming', 'HP Pavilion Gaming', 'Gaming laptop with dedicated graphics', 'Kompyuta ya michezo yenye vipimo maalum', 2, 'HP', 'Pavilion Gaming 15', 'product', '6201234000004', 1, 0, 1200000, 1600000, 1450000, 18.00, 2, 8, 1),
('TB001', 'iPad Air', 'iPad Air', 'Versatile tablet for work and entertainment', 'Kibao cha kazi na burudani', 3, 'Apple', 'iPad Air 5th Gen', 'product', '6201234000005', 1, 0, 800000, 1100000, 1000000, 18.00, 3, 12, 1),
('AC001', 'Phone Case Universal', 'Kifuniko cha Simu', 'Universal phone case fits most devices', 'Kifuniko cha simu kinachofaa vifaa vingi', 4, 'Generic', 'Universal', 'product', '6201234000006', 0, 0, 5000, 15000, 12000, 18.00, 50, 100, 1),
('AC002', 'USB-C Fast Charger', 'Chaja ya USB-C', '65W fast charger with USB-C cable', 'Chaja ya haraka ya 65W na kebo ya USB-C', 4, 'Anker', 'PowerPort III', 'product', '6201234000007', 0, 0, 25000, 45000, 38000, 18.00, 20, 50, 1),
('SV001', 'Phone Repair Service', 'Huduma ya Ukarabati wa Simu', 'Professional phone repair service', 'Huduma ya kitaalamu ya ukarabati wa simu', 1, 'FaithBit', 'Repair Service', 'service', '', 0, 0, 0, 50000, 45000, 18.00, 0, 0, 1);

-- Insert warehouses
INSERT INTO `warehouses` (`code`, `name`, `branch_id`, `type`, `manager_id`, `is_active`) VALUES
('WH001', 'Head Office Warehouse', 1, 'main', 1, 1),
('WH002', 'DSM Retail Store', 2, 'retail', 2, 1),
('WH003', 'Arusha Service Center Stock', 3, 'retail', 4, 1),
('WH004', 'Mwanza Main Warehouse', 4, 'main', 1, 1);

-- Insert inventory stock
INSERT INTO `inventory_stock` (`product_id`, `warehouse_id`, `bin_location`, `quantity_on_hand`, `quantity_reserved`, `cost_price`) VALUES
(1, 2, 'A1-01', 15, 0, 450000),
(2, 2, 'A1-02', 8, 0, 1800000),
(3, 2, 'B2-01', 6, 0, 850000),
(4, 2, 'B2-02', 4, 0, 1200000),
(5, 2, 'C3-01', 10, 0, 800000),
(6, 2, 'D4-01', 150, 0, 5000),
(7, 2, 'D4-02', 75, 0, 25000),
(1, 1, 'MAIN-A', 50, 0, 450000),
(2, 1, 'MAIN-B', 25, 0, 1800000),
(3, 1, 'MAIN-C', 30, 0, 850000);

-- Insert sample customers
INSERT INTO `customers` (`customer_number`, `type`, `first_name`, `last_name`, `company_name`, `email`, `phone`, `address`, `city`, `region`, `preferred_language`, `credit_limit`, `loyalty_points`, `is_active`) VALUES
('C250900001', 'individual', 'James', 'Mwalimu', NULL, 'james.mwalimu@email.com', '+255756123456', 'Sinza, Plot 45', 'Dar es Salaam', 'Dar es Salaam', 'en', 500000, 150, 1),
('C250900002', 'individual', 'Fatuma', 'Hassan', NULL, 'fatuma.hassan@email.com', '+255713987654', 'Mikocheni, House 12', 'Dar es Salaam', 'Dar es Salaam', 'sw', 300000, 80, 1),
('C250900003', 'business', NULL, NULL, 'Mwanga Technologies Ltd', 'info@mwangatech.co.tz', '+255754111222', 'Msimbazi Street, Building 5', 'Dar es Salaam', 'Dar es Salaam', 'en', 2000000, 500, 1),
('C250900004', 'individual', 'Peter', 'Komba', NULL, 'peter.komba@gmail.com', '+255788555666', 'Mwenge, Plot 78', 'Dar es Salaam', 'Dar es Salaam', 'en', 1000000, 220, 1),
('C250900005', 'business', NULL, NULL, 'Kilimanjaro Trading Co', 'sales@kilitrading.com', '+255767333444', 'Industrial Area, Warehouse 3', 'Arusha', 'Arusha', 'en', 5000000, 1200, 1);

-- Insert sample suppliers
INSERT INTO `suppliers` (`supplier_number`, `name`, `contact_person`, `email`, `phone`, `address`, `city`, `country`, `payment_terms`, `rating`, `is_active`) VALUES
('SUP001', 'Samsung East Africa Ltd', 'John Smith', 'orders@samsung.ea.com', '+254712345678', 'Westlands, Nairobi', 'Nairobi', 'Kenya', 30, 4.5, 1),
('SUP002', 'Apple Distribution Partner', 'Sarah Johnson', 'distribution@apple-ea.com', '+254798765432', 'Upper Hill, Nairobi', 'Nairobi', 'Kenya', 15, 4.8, 1),
('SUP003', 'Dell Technologies Africa', 'Michael Brown', 'channel@dell.africa.com', '+27115556789', 'Johannesburg', 'Johannesburg', 'South Africa', 45, 4.2, 1),
('SUP004', 'Local Accessories Supplier', 'Ahmed Mohamed', 'ahmed@accessories.co.tz', '+255754987123', 'Kariakoo, Dar es Salaam', 'Dar es Salaam', 'Tanzania', 7, 3.8, 1),
('SUP005', 'Anker East Africa', 'Lisa Wang', 'orders@anker.ea.com', '+254733222111', 'Mombasa Road, Nairobi', 'Nairobi', 'Kenya', 21, 4.6, 1);

-- Insert sample product serials for items that have serial numbers
INSERT INTO `product_serials` (`product_id`, `warehouse_id`, `serial_number`, `imei_number`, `supplier_id`, `cost_price`, `status`, `warranty_start_date`, `warranty_end_date`) VALUES
(1, 2, 'SG54001234567890', '123456789012345', 1, 450000, 'available', '2025-09-01', '2026-09-01'),
(1, 2, 'SG54001234567891', '123456789012346', 1, 450000, 'available', '2025-09-01', '2026-09-01'),
(1, 2, 'SG54001234567892', '123456789012347', 1, 450000, 'available', '2025-09-01', '2026-09-01'),
(2, 2, 'IP13001122334455', '987654321098765', 2, 1800000, 'available', '2025-09-01', '2026-09-01'),
(2, 2, 'IP13001122334456', '987654321098766', 2, 1800000, 'available', '2025-09-01', '2026-09-01'),
(3, 2, 'DL15556677889900', NULL, 3, 850000, 'available', '2025-09-01', '2026-09-01'),
(3, 2, 'DL15556677889901', NULL, 3, 850000, 'available', '2025-09-01', '2026-09-01'),
(4, 2, 'HP15778899001122', NULL, 3, 1200000, 'available', '2025-09-01', '2026-09-01'),
(5, 2, 'IPD5889900112233', NULL, 2, 800000, 'available', '2025-09-01', '2026-09-01');

-- Update branch managers
UPDATE `branches` SET `manager_id` = 2 WHERE `id` = 2;
UPDATE `branches` SET `manager_id` = 4 WHERE `id` = 3;