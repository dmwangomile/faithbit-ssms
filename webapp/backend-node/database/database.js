const sqlite3 = require('sqlite3').verbose();
const path = require('path');

const DB_PATH = path.join(__dirname, 'faithbit_ssms.db');

let db;

function initializeDatabase() {
    db = new sqlite3.Database(DB_PATH, (err) => {
        if (err) {
            console.error('Error opening database:', err);
        } else {
            console.log('✅ Connected to SQLite database');
            createTables();
        }
    });
}

function createTables() {
    // Create tables sequentially
    db.serialize(() => {
        // Users table
        db.run(`
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                phone TEXT,
                branch_id INTEGER,
                role TEXT CHECK(role IN ('admin', 'manager', 'cashier', 'sales_rep', 'technician', 'inventory_manager', 'procurement_officer')) NOT NULL,
                status TEXT CHECK(status IN ('active', 'inactive', 'suspended')) DEFAULT 'active',
                last_login_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Branches table
        db.run(`
            CREATE TABLE IF NOT EXISTS branches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                type TEXT CHECK(type IN ('retail_shop', 'warehouse', 'service_center', 'head_office')) NOT NULL,
                address TEXT,
                city TEXT,
                region TEXT,
                country TEXT DEFAULT 'Tanzania',
                phone TEXT,
                email TEXT,
                manager_id INTEGER,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Product categories table
        db.run(`
            CREATE TABLE IF NOT EXISTS product_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Products table
        db.run(`
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sku TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                name_sw TEXT,
                description TEXT,
                description_sw TEXT,
                category_id INTEGER NOT NULL,
                brand TEXT,
                model TEXT,
                type TEXT CHECK(type IN ('product', 'service', 'kit', 'spare_part')) DEFAULT 'product',
                barcode TEXT,
                has_serial INTEGER DEFAULT 0,
                has_imei INTEGER DEFAULT 0,
                cost_price REAL DEFAULT 0.00,
                selling_price REAL DEFAULT 0.00,
                wholesale_price REAL DEFAULT 0.00,
                tax_rate REAL DEFAULT 18.00,
                reorder_level INTEGER DEFAULT 10,
                reorder_quantity INTEGER DEFAULT 50,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES product_categories(id)
            )
        `);

        // Customers table
        db.run(`
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_number TEXT UNIQUE NOT NULL,
                type TEXT CHECK(type IN ('individual', 'business')) DEFAULT 'individual',
                first_name TEXT,
                last_name TEXT,
                company_name TEXT,
                email TEXT,
                phone TEXT,
                address TEXT,
                city TEXT,
                region TEXT,
                credit_limit REAL DEFAULT 0.00,
                loyalty_points INTEGER DEFAULT 0,
                total_purchases REAL DEFAULT 0.00,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `, () => {
            // Insert initial data after all tables are created
            insertInitialData();
        });
    });
}

function insertInitialData() {
    // Check if admin user exists
    db.get('SELECT id FROM users WHERE username = ?', ['admin'], (err, row) => {
        if (err) {
            console.error('Error checking admin user:', err);
            return;
        }

        if (!row) {
            // Insert admin user (password: admin123)
            const bcrypt = require('bcryptjs');
            const password = bcrypt.hashSync('admin123', 10);
            
            db.run(`
                INSERT INTO users (username, email, password_hash, first_name, last_name, role)
                VALUES (?, ?, ?, ?, ?, ?)
            `, ['admin', 'admin@faithbit.com', password, 'System', 'Administrator', 'admin'], (err) => {
                if (err) {
                    console.error('Error creating admin user:', err);
                } else {
                    console.log('✅ Created default admin user (admin/admin123)');
                }
            });

            // Insert branches
            db.run(`
                INSERT INTO branches (code, name, type, city, region)
                VALUES ('HQ001', 'Head Office - Dodoma', 'head_office', 'Dodoma', 'Dodoma'),
                       ('DSM001', 'Dar es Salaam Shop', 'retail_shop', 'Dar es Salaam', 'Dar es Salaam')
            `);

            // Insert categories
            db.run(`
                INSERT INTO product_categories (name, description, sort_order)
                VALUES ('Mobile Phones', 'Smartphones and feature phones', 1),
                       ('Laptops & Computers', 'Desktop computers, laptops, and accessories', 2),
                       ('Accessories', 'Phone cases, chargers, cables, and other accessories', 3)
            `);

            // Insert sample products
            db.run(`
                INSERT INTO products (sku, name, name_sw, description, category_id, brand, model, barcode, cost_price, selling_price, wholesale_price)
                VALUES 
                ('PH001', 'Samsung Galaxy A54', 'Samsung Galaxy A54', 'Mid-range smartphone with excellent camera', 1, 'Samsung', 'Galaxy A54', '6201234000001', 450000, 650000, 580000),
                ('PH002', 'iPhone 13 Pro', 'iPhone 13 Pro', 'Premium smartphone with advanced features', 1, 'Apple', 'iPhone 13 Pro', '6201234000002', 1800000, 2200000, 2000000),
                ('LT001', 'Dell Inspiron 15', 'Dell Inspiron 15', 'Business laptop with Intel i5 processor', 2, 'Dell', 'Inspiron 15', '6201234000003', 850000, 1200000, 1100000),
                ('AC001', 'Phone Case Universal', 'Kifuniko cha Simu', 'Universal phone case fits most devices', 3, 'Generic', 'Universal', '6201234000006', 5000, 15000, 12000)
            `);

            // Insert sample customers
            db.run(`
                INSERT INTO customers (customer_number, type, first_name, last_name, email, phone, city, region, loyalty_points)
                VALUES 
                ('C250900001', 'individual', 'James', 'Mwalimu', 'james.mwalimu@email.com', '+255756123456', 'Dar es Salaam', 'Dar es Salaam', 150),
                ('C250900002', 'individual', 'Fatuma', 'Hassan', 'fatuma.hassan@email.com', '+255713987654', 'Dar es Salaam', 'Dar es Salaam', 80),
                ('C250900003', 'business', NULL, NULL, 'info@mwangatech.co.tz', '+255754111222', 'Dar es Salaam', 'Dar es Salaam', 500)
            `);
        }
    });
}

function getDatabase() {
    return db;
}

module.exports = {
    initializeDatabase,
    getDatabase
};