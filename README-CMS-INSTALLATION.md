# JSN RENEWABLE ENERGY — BACKEND & ADMIN CMS DOCUMENTATION
**Approved Tagline:** A NEW WAY TO POWER  
**Company Name:** JSN Renewable Energy Private Limited

---

## 1. System Overview

The JSN Renewable Energy website has been extended into a complete, dynamic PHP 8+ / MySQL application equipped with a private, secure Admin Content Management System (CMS).

### **Key Features:**
* 🔒 **Private Admin Panel (`/admin/`):** Password hashing, session authentication, CSRF tokens, and rate limiting.
* ⚡ **Site Survey & Lead Management (`/admin/enquiries.php`):** Captures all lead submissions from `index.html` and `contact/index.html` directly into MySQL. Allows status updates (*New, Contacted, Follow-up, Converted, Rejected*), follow-up notes, and instant **CSV Export**.
* 📁 **Dynamic Projects CMS (`/admin/projects.php`):** Full CRUD functionality to create, edit, delete, publish, and feature solar projects with capacity (kW), system type, client name, and completion dates.
* 🖼️ **Media & Image Management (`/admin/media.php`):** Upload hero and multiple gallery images with automatic file extension checking, MIME verification, and unique filename generation.
* 🌐 **Dynamic Public Case Studies (`/projects/detail.php`):** SEO-friendly project detail pages generated dynamically from database records.
* ⚙️ **Site Settings & Verified Metrics (`/admin/settings.php`):** Manage global stats (5.85 MW, 5.10 MW, 745 kW), contact details (+91 92890 44454, info@jsnrenewable.com), and corporate address.

---

## 2. Server Requirements

* **PHP:** Version 8.0 or higher (with `pdo_mysql`, `gd`, `fileinfo`, `mbstring` extensions enabled).
* **Database:** MySQL 5.7+ or MariaDB 10.3+.
* **Web Server:** Apache (with `mod_rewrite` enabled) or Nginx on any Linux/Windows shared or VPS hosting.

---

## 3. Deployment & Installation Guide

### **Step 1: Upload Files**
Upload all website files to your web server document root (e.g., `public_html/` or `/var/www/html/`).

### **Step 2: Automated Installation (`/install.php`)**
1. Open your browser and navigate to:  
   `http://yourdomain.com/install.php`
2. Enter your MySQL server details:
   * **Database Name:** `jsn_renewable_db` (or custom name)
   * **Database Username:** `root` (or your cPanel DB user)
   * **Database Password:** (Your MySQL password)
3. Set your initial SuperAdmin account credentials:
   * **Admin Username:** `admin`
   * **Admin Email:** `info@jsnrenewable.com`
   * **Admin Password:** (Minimum 8 characters)
4. Click **Run Automated Installation**. The script will automatically create all tables, insert default projects, and initialize your SuperAdmin account.

---

## 4. Admin Panel Access

* **Admin Panel URL:** `http://yourdomain.com/admin/`
* **Default Login:** Use the credentials configured during installation.

---

## 5. Security Practices Implemented

* **Password Hashing:** Passwords are encrypted using PHP `password_hash()` with BCRYPT.
* **Prepared SQL Statements:** 100% of queries use PDO prepared statements to prevent SQL Injection.
* **CSRF Protection:** Forms utilize session-verified CSRF tokens.
* **File Upload Security:** Validates extension, MIME type, file size (<5MB), and renames uploaded files.
* **Sanitization:** All outputs use `htmlspecialchars()` against XSS.

---

© 2026 JSN Renewable Energy Private Limited. All rights reserved.
