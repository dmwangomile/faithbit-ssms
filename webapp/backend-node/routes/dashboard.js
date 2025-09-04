const express = require('express');
const { getDatabase } = require('../database/database');
const { authenticateToken } = require('./auth');

const router = express.Router();

// Get dashboard statistics
router.get('/stats', authenticateToken, (req, res) => {
    const db = getDatabase();
    
    // Get various statistics in parallel
    const stats = {};
    let completedQueries = 0;
    const totalQueries = 5;

    function checkComplete() {
        completedQueries++;
        if (completedQueries === totalQueries) {
            res.json({
                success: true,
                message: 'Dashboard statistics retrieved successfully',
                data: stats
            });
        }
    }

    // Get total products
    db.get('SELECT COUNT(*) as total FROM products WHERE is_active = 1', (err, result) => {
        if (err) {
            console.error('Database error:', err);
        } else {
            stats.total_products = result.total;
        }
        checkComplete();
    });

    // Get total customers
    db.get('SELECT COUNT(*) as total FROM customers WHERE is_active = 1', (err, result) => {
        if (err) {
            console.error('Database error:', err);
        } else {
            stats.total_customers = result.total;
        }
        checkComplete();
    });

    // Get low stock products
    db.get(`
        SELECT COUNT(*) as total 
        FROM products 
        WHERE is_active = 1 AND reorder_level > 0
    `, (err, result) => {
        if (err) {
            console.error('Database error:', err);
        } else {
            stats.low_stock_products = result.total;
        }
        checkComplete();
    });

    // Get products by category
    db.all(`
        SELECT c.name, COUNT(p.id) as count
        FROM product_categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        WHERE c.is_active = 1
        GROUP BY c.id, c.name
        ORDER BY count DESC
    `, (err, results) => {
        if (err) {
            console.error('Database error:', err);
        } else {
            stats.products_by_category = results;
        }
        checkComplete();
    });

    // Get recent activities (mock data for now)
    setTimeout(() => {
        stats.recent_activities = [
            {
                id: 1,
                type: 'product_added',
                message: 'New product Samsung Galaxy A54 added to inventory',
                user: 'Admin User',
                timestamp: new Date().toISOString()
            },
            {
                id: 2,
                type: 'customer_registered',
                message: 'New customer James Mwalimu registered',
                user: 'Sales Rep',
                timestamp: new Date(Date.now() - 3600000).toISOString()
            },
            {
                id: 3,
                type: 'sale_completed',
                message: 'Sale completed for customer Fatuma Hassan',
                user: 'Cashier',
                timestamp: new Date(Date.now() - 7200000).toISOString()
            }
        ];
        checkComplete();
    }, 100);
});

// Get sales summary (mock data)
router.get('/sales-summary', authenticateToken, (req, res) => {
    // In a real application, this would fetch actual sales data
    const salesSummary = {
        today: {
            sales: 15,
            revenue: 1500000,
            customers: 8
        },
        this_week: {
            sales: 75,
            revenue: 8500000,
            customers: 35
        },
        this_month: {
            sales: 320,
            revenue: 25000000,
            customers: 150
        },
        top_products: [
            { name: 'Samsung Galaxy A54', sales: 12, revenue: 7800000 },
            { name: 'iPhone 13 Pro', sales: 5, revenue: 11000000 },
            { name: 'Dell Inspiron 15', sales: 3, revenue: 3600000 },
            { name: 'Phone Case Universal', sales: 25, revenue: 375000 }
        ],
        daily_sales: [
            { date: '2025-08-28', sales: 12, revenue: 1200000 },
            { date: '2025-08-29', sales: 15, revenue: 1800000 },
            { date: '2025-08-30', sales: 10, revenue: 950000 },
            { date: '2025-08-31', sales: 18, revenue: 2100000 },
            { date: '2025-09-01', sales: 14, revenue: 1650000 },
            { date: '2025-09-02', sales: 16, revenue: 1750000 },
            { date: '2025-09-03', sales: 15, revenue: 1500000 }
        ]
    };

    res.json({
        success: true,
        message: 'Sales summary retrieved successfully',
        data: salesSummary
    });
});

// Get inventory summary
router.get('/inventory-summary', authenticateToken, (req, res) => {
    const db = getDatabase();
    
    // Get inventory statistics
    db.all(`
        SELECT 
            p.name,
            p.sku,
            p.reorder_level,
            p.selling_price,
            COALESCE(SUM(i.quantity_on_hand), 0) as total_stock,
            COALESCE(SUM(i.quantity_reserved), 0) as reserved_stock,
            (COALESCE(SUM(i.quantity_on_hand), 0) - COALESCE(SUM(i.quantity_reserved), 0)) as available_stock
        FROM products p
        LEFT JOIN inventory_stock i ON p.id = i.product_id
        WHERE p.is_active = 1
        GROUP BY p.id, p.name, p.sku, p.reorder_level, p.selling_price
        HAVING available_stock <= p.reorder_level AND p.reorder_level > 0
        ORDER BY available_stock ASC
        LIMIT 10
    `, (err, lowStockProducts) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        // Calculate total inventory value (mock calculation)
        const inventorySummary = {
            total_products: 0,
            total_value: 0,
            low_stock_count: lowStockProducts.length,
            low_stock_products: lowStockProducts,
            stock_alerts: lowStockProducts.map(product => ({
                product_name: product.name,
                sku: product.sku,
                available_stock: product.available_stock,
                reorder_level: product.reorder_level,
                status: product.available_stock === 0 ? 'out_of_stock' : 'low_stock',
                urgency: product.available_stock === 0 ? 'critical' : 'warning'
            }))
        };

        // Get total products and estimate value
        db.get(`
            SELECT 
                COUNT(*) as total_products,
                SUM(selling_price) as estimated_value
            FROM products 
            WHERE is_active = 1
        `, (err, totals) => {
            if (err) {
                console.error('Database error:', err);
            } else {
                inventorySummary.total_products = totals.total_products;
                inventorySummary.estimated_total_value = totals.estimated_value;
            }

            res.json({
                success: true,
                message: 'Inventory summary retrieved successfully',
                data: inventorySummary
            });
        });
    });
});

module.exports = router;