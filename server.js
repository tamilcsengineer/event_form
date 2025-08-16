const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');
const path = require('path');

const app = express();

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
    secret: 'your_secret_key',
    resave: false,
    saveUninitialized: true,
    cookie: { secure: false } // Set to true if using HTTPS
}));

// Database connection
const dbConfig = {
    host: 'localhost',
    user: 'your_db_username',
    password: 'your_db_password',
    database: 'your_db_name'
};

// Login route
app.post('/admin/login', async (req, res) => {
    const { username, password } = req.body;
    
    try {
        const connection = await mysql.createConnection(dbConfig);
        
        // Check admin credentials
        const [rows] = await connection.execute(
            'SELECT * FROM admins WHERE username = ?', 
            [username]
        );
        
        if (rows.length === 0) {
            return res.redirect('/login?error=invalid_credentials');
        }
        
        const admin = rows[0];
        const passwordMatch = await bcrypt.compare(password, admin.password_hash);
        
        if (!passwordMatch) {
            return res.redirect('/login?error=invalid_credentials');
        }
        
        // Login successful
        req.session.adminId = admin.id;
        req.session.adminUsername = admin.username;
        req.session.adminLoggedIn = true;
        
        res.redirect('/admin/dashboard');
    } catch (error) {
        console.error(error);
        res.redirect('/login?error=server_error');
    }
});

// Admin dashboard route
app.get('/admin/dashboard', (req, res) => {
    if (!req.session.adminLoggedIn) {
        return res.redirect('/login?error=not_logged_in');
    }
    
    res.sendFile(path.join(__dirname, 'admin_dashboard.html'));
});

// Logout route
app.get('/admin/logout', (req, res) => {
    req.session.destroy(err => {
        if (err) {
            return res.redirect('/admin/dashboard?error=logout_failed');
        }
        res.redirect('/login?logout=success');
    });
});

// Serve static files
app.use(express.static('public'));

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(Serverrunningonport${PORT});
});