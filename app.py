from flask import Flask, render_template, request, redirect, url_for, session
import pymysql
import bcrypt
import os

app = Flask(_name_)
app.secret_key = os.urandom(24)  # Secret key for session

# Database configuration
db_config = {
    'host': 'localhost',
    'user': 'your_db_username',
    'password': 'your_db_password',
    'database': 'your_db_name',
    'cursorclass': pymysql.cursors.DictCursor
}

@app.route('/admin/login', methods=['POST'])
def login():
    username = request.form.get('username')
    password = request.form.get('password')
    
    if not username or not password:
        return redirect(url_for('login_page', error='empty_fields'))
    
    try:
        connection = pymysql.connect(**db_config)
        with connection.cursor() as cursor:
            sql = "SELECT * FROM admins WHERE username = %s"
            cursor.execute(sql, (username,))
            admin = cursor.fetchone()
            
            if not admin:
                return redirect(url_for('login_page', error='invalid_credentials'))
            
            # Verify password
            if bcrypt.checkpw(password.encode('utf-8'), admin['password_hash'].encode('utf-8')):
                session['admin_id'] = admin['id']
                session['admin_username'] = admin['username']
                session['admin_logged_in'] = True
                return redirect(url_for('admin_dashboard'))
            else:
                return redirect(url_for('login_page', error='invalid_credentials'))
                
    except Exception as e:
        print(f"Database error: {e}")
        return redirect(url_for('login_page', error='server_error'))
    finally:
        connection.close()

@app.route('/admin/dashboard')
def admin_dashboard():
    if not session.get('admin_logged_in'):
        return redirect(url_for('login_page', error='not_logged_in'))
    
    return render_template('admin_dashboard.html', username=session['admin_username'])

@app.route('/admin/logout')
def logout():
    session.clear()
    return redirect(url_for('login_page', logout='success'))

if _name_ == '_main_':
    app.run(debug=True)