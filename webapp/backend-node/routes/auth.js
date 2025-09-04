const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { getDatabase } = require('../database/database');

const router = express.Router();
const JWT_SECRET = process.env.JWT_SECRET || 'faithbit-jwt-secret-key';
const JWT_EXPIRY = process.env.JWT_EXPIRY || '24h';

// Login endpoint
router.post('/login', async (req, res) => {
    try {
        const { username, password } = req.body;

        if (!username || !password) {
            return res.status(400).json({
                success: false,
                message: 'Username and password are required'
            });
        }

        const db = getDatabase();
        
        // Find user by username or email
        db.get(`
            SELECT u.*, b.name as branch_name, b.code as branch_code
            FROM users u
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE (u.username = ? OR u.email = ?) AND u.status = 'active'
        `, [username, username], (err, user) => {
            if (err) {
                console.error('Database error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Database error'
                });
            }

            if (!user) {
                return res.status(401).json({
                    success: false,
                    message: 'Invalid credentials'
                });
            }

            // Verify password
            if (!bcrypt.compareSync(password, user.password_hash)) {
                return res.status(401).json({
                    success: false,
                    message: 'Invalid credentials'
                });
            }

            // Generate JWT token
            const tokenPayload = {
                id: user.id,
                username: user.username,
                email: user.email,
                role: user.role,
                branch_id: user.branch_id
            };

            const accessToken = jwt.sign(tokenPayload, JWT_SECRET, { 
                expiresIn: JWT_EXPIRY 
            });

            const refreshToken = jwt.sign(
                { id: user.id, type: 'refresh' }, 
                JWT_SECRET, 
                { expiresIn: '30d' }
            );

            // Update last login
            db.run(`
                UPDATE users 
                SET last_login_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            `, [user.id]);

            // Return response
            res.json({
                success: true,
                message: 'Login successful',
                data: {
                    user: {
                        id: user.id,
                        username: user.username,
                        email: user.email,
                        first_name: user.first_name,
                        last_name: user.last_name,
                        full_name: `${user.first_name} ${user.last_name}`,
                        role: user.role,
                        branch_id: user.branch_id,
                        branch: user.branch_id ? {
                            id: user.branch_id,
                            name: user.branch_name,
                            code: user.branch_code
                        } : null,
                        permissions: getRolePermissions(user.role)
                    },
                    tokens: {
                        access_token: accessToken,
                        refresh_token: refreshToken,
                        token_type: 'Bearer',
                        expires_in: 86400 // 24 hours in seconds
                    }
                }
            });
        });

    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({
            success: false,
            message: 'Internal server error'
        });
    }
});

// Get current user endpoint
router.get('/me', authenticateToken, (req, res) => {
    const db = getDatabase();
    
    db.get(`
        SELECT u.*, b.name as branch_name, b.code as branch_code
        FROM users u
        LEFT JOIN branches b ON u.branch_id = b.id
        WHERE u.id = ? AND u.status = 'active'
    `, [req.user.id], (err, user) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Database error'
            });
        }

        if (!user) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        res.json({
            success: true,
            data: {
                id: user.id,
                username: user.username,
                email: user.email,
                first_name: user.first_name,
                last_name: user.last_name,
                full_name: `${user.first_name} ${user.last_name}`,
                role: user.role,
                branch_id: user.branch_id,
                branch: user.branch_id ? {
                    id: user.branch_id,
                    name: user.branch_name,
                    code: user.branch_code
                } : null,
                permissions: getRolePermissions(user.role)
            }
        });
    });
});

// Logout endpoint
router.post('/logout', authenticateToken, (req, res) => {
    // In a real app, you might want to blacklist the token
    res.json({
        success: true,
        message: 'Logged out successfully'
    });
});

// Middleware to authenticate JWT token
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({
            success: false,
            message: 'Access token required'
        });
    }

    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) {
            return res.status(403).json({
                success: false,
                message: 'Invalid or expired token'
            });
        }

        req.user = decoded;
        next();
    });
}

// Get role permissions
function getRolePermissions(role) {
    const permissions = {
        'admin': ['*'],
        'manager': [
            'branch.*', 'sales.*', 'service.*', 'inventory.*', 
            'customer.*', 'product.*', 'report.*', 'dashboard.*'
        ],
        'cashier': [
            'pos.*', 'customer.view', 'customer.create', 'product.view', 
            'payment.*', 'inventory.view'
        ],
        'sales_rep': [
            'sales.*', 'customer.*', 'product.view', 'inventory.view',
            'quote.*', 'order.*'
        ],
        'technician': [
            'service.*', 'customer.view', 'product.view', 'inventory.view'
        ],
        'inventory_manager': [
            'inventory.*', 'product.*', 'procurement.*', 'supplier.*',
            'warehouse.*', 'report.inventory'
        ],
        'procurement_officer': [
            'procurement.*', 'supplier.*', 'product.view', 'inventory.view',
            'report.procurement'
        ]
    };

    return permissions[role] || [];
}

module.exports = { router, authenticateToken };