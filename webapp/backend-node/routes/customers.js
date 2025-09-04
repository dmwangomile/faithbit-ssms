const express = require('express');
const { getDatabase } = require('../database/database');
const { authenticateToken } = require('./auth');

const router = express.Router();

// Get all customers
router.get('/', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { search, type, page = 1, per_page = 20 } = req.query;
    
    let query = `
        SELECT * FROM customers
        WHERE is_active = 1
    `;
    let params = [];

    // Add search filters
    if (search) {
        query += ` AND (first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ? OR customer_number LIKE ? OR email LIKE ? OR phone LIKE ?)`;
        const searchTerm = `%${search}%`;
        params.push(searchTerm, searchTerm, searchTerm, searchTerm, searchTerm, searchTerm);
    }

    if (type) {
        query += ` AND type = ?`;
        params.push(type);
    }

    // Add ordering
    query += ` ORDER BY 
        CASE 
            WHEN type = 'individual' THEN first_name || ' ' || last_name
            ELSE company_name
        END ASC
    `;

    // Add pagination
    const offset = (page - 1) * per_page;
    query += ` LIMIT ? OFFSET ?`;
    params.push(parseInt(per_page), offset);

    db.all(query, params, (err, customers) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        // Format customer data
        const formattedCustomers = customers.map(customer => ({
            ...customer,
            full_name: customer.type === 'business' ? 
                customer.company_name : 
                `${customer.first_name || ''} ${customer.last_name || ''}`.trim(),
            display_name: customer.type === 'business' ? 
                customer.company_name : 
                `${customer.first_name || ''} ${customer.last_name || ''}`.trim() || customer.customer_number
        }));

        // Get total count for pagination
        let countQuery = `SELECT COUNT(*) as total FROM customers WHERE is_active = 1`;
        let countParams = [];

        if (search) {
            countQuery += ` AND (first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ? OR customer_number LIKE ? OR email LIKE ? OR phone LIKE ?)`;
            const searchTerm = `%${search}%`;
            countParams.push(searchTerm, searchTerm, searchTerm, searchTerm, searchTerm, searchTerm);
        }

        if (type) {
            countQuery += ` AND type = ?`;
            countParams.push(type);
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
                message: 'Customers retrieved successfully',
                data: formattedCustomers,
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

// Get customer by ID
router.get('/:id', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { id } = req.params;

    db.get(`
        SELECT * FROM customers
        WHERE id = ? AND is_active = 1
    `, [id], (err, customer) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        if (!customer) {
            return res.status(404).json({
                success: false,
                message: 'Customer not found'
            });
        }

        // Format customer data
        const formattedCustomer = {
            ...customer,
            full_name: customer.type === 'business' ? 
                customer.company_name : 
                `${customer.first_name || ''} ${customer.last_name || ''}`.trim(),
            display_name: customer.type === 'business' ? 
                customer.company_name : 
                `${customer.first_name || ''} ${customer.last_name || ''}`.trim() || customer.customer_number
        };

        res.json({
            success: true,
            message: 'Customer retrieved successfully',
            data: formattedCustomer
        });
    });
});

// Create new customer
router.post('/', authenticateToken, (req, res) => {
    const db = getDatabase();
    const {
        type = 'individual', first_name, last_name, company_name,
        email, phone, address, city, region, credit_limit = 0,
        loyalty_points = 0
    } = req.body;

    // Generate customer number
    const generateCustomerNumber = () => {
        const prefix = 'C';
        const date = new Date();
        const year = date.getFullYear().toString().substr(2);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const random = Math.floor(Math.random() * 9999) + 1;
        return `${prefix}${year}${month}${String(random).padStart(4, '0')}`;
    };

    const customerNumber = generateCustomerNumber();

    db.run(`
        INSERT INTO customers (
            customer_number, type, first_name, last_name, company_name,
            email, phone, address, city, region, credit_limit, loyalty_points
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [
        customerNumber, type, first_name, last_name, company_name,
        email, phone, address, city, region, credit_limit, loyalty_points
    ], function(err) {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        // Get the created customer
        db.get(`
            SELECT * FROM customers WHERE id = ?
        `, [this.lastID], (err, customer) => {
            if (err) {
                console.error('Database error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Customer created but error retrieving data'
                });
            }

            const formattedCustomer = {
                ...customer,
                full_name: customer.type === 'business' ? 
                    customer.company_name : 
                    `${customer.first_name || ''} ${customer.last_name || ''}`.trim(),
                display_name: customer.type === 'business' ? 
                    customer.company_name : 
                    `${customer.first_name || ''} ${customer.last_name || ''}`.trim() || customer.customer_number
            };

            res.status(201).json({
                success: true,
                message: 'Customer created successfully',
                data: formattedCustomer
            });
        });
    });
});

// Update customer
router.put('/:id', authenticateToken, (req, res) => {
    const db = getDatabase();
    const { id } = req.params;
    const updates = req.body;

    // Remove protected fields
    delete updates.id;
    delete updates.customer_number;
    delete updates.created_at;

    // Build update query
    const fields = Object.keys(updates);
    if (fields.length === 0) {
        return res.status(400).json({
            success: false,
            message: 'No fields to update'
        });
    }

    const setClause = fields.map(field => `${field} = ?`).join(', ');
    const values = fields.map(field => updates[field]);
    values.push(id);

    db.run(`
        UPDATE customers 
        SET ${setClause}, updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND is_active = 1
    `, values, function(err) {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        if (this.changes === 0) {
            return res.status(404).json({
                success: false,
                message: 'Customer not found'
            });
        }

        // Get updated customer
        db.get(`SELECT * FROM customers WHERE id = ?`, [id], (err, customer) => {
            if (err) {
                console.error('Database error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Customer updated but error retrieving data'
                });
            }

            const formattedCustomer = {
                ...customer,
                full_name: customer.type === 'business' ? 
                    customer.company_name : 
                    `${customer.first_name || ''} ${customer.last_name || ''}`.trim(),
                display_name: customer.type === 'business' ? 
                    customer.company_name : 
                    `${customer.first_name || ''} ${customer.last_name || ''}`.trim() || customer.customer_number
            };

            res.json({
                success: true,
                message: 'Customer updated successfully',
                data: formattedCustomer
            });
        });
    });
});

module.exports = router;