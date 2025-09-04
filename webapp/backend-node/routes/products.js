const express = require('express');
const { getDatabase } = require('../database/database');
const { authenticateToken } = require('./auth');

const router = express.Router();

// Search products (for POS and sales) - must come before /:id
router.get('/search', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { q: query, limit = 10 } = req.query;

    if (!query || query.length < 2) {
        return res.status(400).json({
            success: false,
            message: 'Search query must be at least 2 characters'
        });
    }

    const searchTerm = `%${query}%`;
    
    db.all(`
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.is_active = 1 
        AND (p.name LIKE ? OR p.name_sw LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ? OR p.brand LIKE ?)
        ORDER BY 
            CASE 
                WHEN p.name LIKE ? THEN 1
                WHEN p.sku LIKE ? THEN 2
                WHEN p.barcode LIKE ? THEN 3
                ELSE 4
            END,
            p.name ASC
        LIMIT ?
    `, [
        searchTerm, searchTerm, searchTerm, searchTerm, searchTerm,
        `${query}%`, `${query}%`, `${query}%`,
        parseInt(limit)
    ], (err, products) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        const results = products.map(product => ({
            id: product.id,
            sku: product.sku,
            name: product.name,
            name_sw: product.name_sw,
            brand: product.brand,
            model: product.model,
            barcode: product.barcode,
            selling_price: product.selling_price,
            wholesale_price: product.wholesale_price,
            tax_rate: product.tax_rate,
            has_serial: product.has_serial,
            has_imei: product.has_imei,
            type: product.type,
            category_name: product.category_name
        }));

        res.json({
            success: true,
            message: 'Search results retrieved successfully',
            data: results
        });
    });
});

// Get product by barcode - must come before /:id
router.get('/by-barcode', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { barcode } = req.query;

    if (!barcode) {
        return res.status(400).json({
            success: false,
            message: 'Barcode is required'
        });
    }

    db.get(`
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.barcode = ? AND p.is_active = 1
    `, [barcode], (err, product) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        if (!product) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        res.json({
            success: true,
            message: 'Product found',
            data: {
                id: product.id,
                sku: product.sku,
                name: product.name,
                name_sw: product.name_sw,
                description: product.description,
                brand: product.brand,
                model: product.model,
                barcode: product.barcode,
                selling_price: product.selling_price,
                wholesale_price: product.wholesale_price,
                tax_rate: product.tax_rate,
                has_serial: product.has_serial,
                has_imei: product.has_imei,
                type: product.type,
                category_name: product.category_name
            }
        });
    });
});

// Get all products
router.get('/', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { search, category_id, type, brand, page = 1, per_page = 20 } = req.query;
    
    let query = `
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.is_active = 1
    `;
    let params = [];

    // Add search filters
    if (search) {
        query += ` AND (p.name LIKE ? OR p.name_sw LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ? OR p.brand LIKE ?)`;
        const searchTerm = `%${search}%`;
        params.push(searchTerm, searchTerm, searchTerm, searchTerm, searchTerm);
    }

    if (category_id) {
        query += ` AND p.category_id = ?`;
        params.push(category_id);
    }

    if (type) {
        query += ` AND p.type = ?`;
        params.push(type);
    }

    if (brand) {
        query += ` AND p.brand LIKE ?`;
        params.push(`%${brand}%`);
    }

    // Add ordering
    query += ` ORDER BY p.name ASC`;

    // Add pagination
    const offset = (page - 1) * per_page;
    query += ` LIMIT ? OFFSET ?`;
    params.push(parseInt(per_page), offset);

    db.all(query, params, (err, products) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        // Get total count for pagination
        let countQuery = `
            SELECT COUNT(*) as total
            FROM products p
            WHERE p.is_active = 1
        `;
        let countParams = [];

        if (search) {
            countQuery += ` AND (p.name LIKE ? OR p.name_sw LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ? OR p.brand LIKE ?)`;
            const searchTerm = `%${search}%`;
            countParams.push(searchTerm, searchTerm, searchTerm, searchTerm, searchTerm);
        }

        if (category_id) {
            countQuery += ` AND p.category_id = ?`;
            countParams.push(category_id);
        }

        if (type) {
            countQuery += ` AND p.type = ?`;
            countParams.push(type);
        }

        if (brand) {
            countQuery += ` AND p.brand LIKE ?`;
            countParams.push(`%${brand}%`);
        }

        db.get(countQuery, countParams, (err, countResult) => {
            if (err) {
                console.error('Count error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Database error'
                });
            }

            const totalCount = countResult.total;
            const totalPages = Math.ceil(totalCount / per_page);

            res.json({
                success: true,
                message: 'Products retrieved successfully',
                data: products,
                pagination: {
                    total_count: totalCount,
                    page_count: totalPages,
                    current_page: parseInt(page),
                    per_page: parseInt(per_page)
                }
            });
        });
    });
});

// Get product by ID
router.get('/:id', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { id } = req.params;

    db.get(`
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
    `, [id], (err, product) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        if (!product) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        res.json({
            success: true,
            message: 'Product retrieved successfully',
            data: product
        });
    });
});

// Create new product
router.post('/', authenticateToken, (req, res) => {
    const db = getDatabase();
    const {
        sku, name, name_sw, description, description_sw, category_id,
        brand, model, type = 'product', barcode, has_serial = 0, has_imei = 0,
        cost_price = 0, selling_price = 0, wholesale_price = 0, tax_rate = 18,
        reorder_level = 10, reorder_quantity = 50
    } = req.body;

    if (!sku || !name || !category_id) {
        return res.status(400).json({
            success: false,
            message: 'SKU, name, and category are required'
        });
    }

    db.run(`
        INSERT INTO products (
            sku, name, name_sw, description, description_sw, category_id,
            brand, model, type, barcode, has_serial, has_imei,
            cost_price, selling_price, wholesale_price, tax_rate,
            reorder_level, reorder_quantity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [
        sku, name, name_sw, description, description_sw, category_id,
        brand, model, type, barcode, has_serial, has_imei,
        cost_price, selling_price, wholesale_price, tax_rate,
        reorder_level, reorder_quantity
    ], function(err) {
        if (err) {
            console.error('Database error:', err);
            if (err.code === 'SQLITE_CONSTRAINT') {
                return res.status(400).json({
                    success: false,
                    message: 'SKU already exists'
                });
            }
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        // Get the created product
        db.get(`
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            WHERE p.id = ?
        `, [this.lastID], (err, product) => {
            if (err) {
                console.error('Database error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Product created but error retrieving data'
                });
            }

            res.status(201).json({
                success: true,
                message: 'Product created successfully',
                data: product
            });
        });
    });
});

module.exports = router;