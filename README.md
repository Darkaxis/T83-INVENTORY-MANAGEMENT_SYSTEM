# 🗃️ Inventory Management System

A modern, **multi-tenant inventory management system** built with **Laravel 11**, featuring a clean UI, real-time analytics, and full product tracking capabilities.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/github/license/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM)](LICENSE)
[![Stars](https://img.shields.io/github/stars/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM?style=social)](https://github.com/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM/stargazers)

---

## 🚀 Features

- 🧩 **Multi-Tenant Architecture** – Manage multiple stores from one platform  
- 📊 **Real-Time Dashboard** – Live insights on sales and stock  
- 📦 **Comprehensive Inventory Tracking** – Products, categories, and quantities  
- 📱 **Responsive UI** – Optimized for desktop, tablet, and mobile  
- 💰 **Sales Monitoring** – Record transactions and revenue  
- 🧾 **PDF Invoice Generation** – Professional invoices for each sale  
- 📈 **Advanced Reporting** – Analytics for smarter decisions  
- 🎨 **Theme Customization** – Per-tenant branding and styling  
- 🆘 **Support Ticket System** – Built-in help and support tools

---

## ⚙️ Quick Start

### ✅ Requirements

Ensure the following are installed on your machine:

- **PHP** 8.2+
- **Composer**
- **MySQL** 8.0+
- **Node.js** and **npm**

---

### 🧪 Installation

```bash
# Clone the repository
git clone https://github.com/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM.git

# Navigate to project directory
cd T83-INVENTORY-MANAGEMENT_SYSTEM

# Install PHP dependencies
composer install

# Install Node dependencies
npm install && npm run build

# Copy and configure environment variables
cp .env.example .env


# Run database migrations and seeders
php artisan migrate --seed

# Serve the application
php artisan serve
