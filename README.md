# 🇳🇵 NepArt

### An Online Marketplace for Nepali Handicrafts

NepArt is a web-based e-commerce platform designed to connect local Nepali artisans with buyers through a dedicated online marketplace.

The platform allows artisans to showcase and manage their handcrafted products by adding product information, prices, quantities, and images. Buyers can browse available products, manage their shopping cart, place orders, and track their order status.

NepArt aims to promote Nepali handicrafts by providing local artisans with a digital platform to reach a wider audience while giving buyers easy access to unique and culturally significant handmade products.

---

## ✨ Features

### 🛍️ Buyer Features

- Browse available handicraft products.
- View product details, prices, and availability.
- Add products to the shopping cart.
- Update product quantities in the cart.
- Remove products from the cart.
- Place orders for available products.
- Track placed orders.
- Access a personalized buyer dashboard.

### 🎨 Artisan Features

- Register and log in as an artisan.
- Access an artisan dashboard.
- Add new handicraft products.
- Upload product images.
- Set product prices.
- Manage available product quantities.
- Update existing product information.
- Delete products.
- View incoming customer orders.
- Handle and manage product orders.

---

## 🛠️ Technologies Used

- **Frontend:** HTML, CSS
- **Backend:** PHP
- **Database:** MySQL
- **Web Server:** Apache
- **Development Environment:** XAMPP

---

## 📁 Project Structure

```text
NepArt/
│
├── assets/
│   │
│   ├── css/
│   │   ├── checkout.css
│   │   └── styleb.css
│   │
│   ├── dynamic_pictures/
│   │   └── products/
│   │       └── Product images uploaded by artisans
│   │
│   └── static_pictures/
│       │
│       ├── artisan/
│       │   └── Static artisan/product images
│       │
│       └── home/
│           └── Images used on the homepage
│
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
│
├── buyer/
│   ├── add_to_cart.php
│   ├── buyer_dashboard.php
│   ├── cart.php
│   ├── checkout.php
│   ├── get_cart_count.php
│   ├── place_order.php
│   ├── track_order.php
│   └── update_cart.php
│
├── includes/
│   └── db.php
│
├── seller/
│   ├── add_product.php
│   ├── artisan_dashboard.php
│   ├── delete_product.php
│   ├── handle_order.php
│   ├── process_add_product.php
│   ├── process_update_product.php
│   ├── update_product.php
│   └── view_orders.php
│
├── database.sql
├── index.php
│
└── README.md
```

---

## 📋 Prerequisites

Before running the project, make sure the following software is installed:

- [XAMPP](https://www.apachefriends.org/) or another PHP development environment
- PHP 7.4 or higher
- MySQL or MariaDB
- A modern web browser

> XAMPP is recommended for running the project locally.

---

# 🚀 Installation and Setup

## 1. Clone the Repository

Clone the repository using Git:

```bash
git clone <repository-url>
```

Navigate to the project directory:

```bash
cd NepArt
```

Alternatively, you can download the project as a ZIP file and extract it.

---

## 2. Move the Project to XAMPP

Move or clone the project into the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\
```

The final project location should look similar to:

```text
C:\xampp\htdocs\NepArt
```

---

## 3. Start Apache and MySQL

Open the **XAMPP Control Panel** and start the following services:

- Apache
- MySQL

Both services should be running before accessing the application.

---

## 4. Create and Import the Database

Open phpMyAdmin in your browser:

```text
http://localhost/phpmyadmin
```

Then follow these steps:

1. Click **New**.
2. Create a database with the name configured in `includes/db.php`.
3. Select the newly created database.
4. Click **Import**.
5. Select the `database.sql` file included in the project.
6. Click **Import** or **Go**.

The SQL file will create the required database tables and data for the application.

---

## 5. Configure the Database Connection

Open:

```text
includes/db.php
```

Configure your local database credentials.

For a default XAMPP installation, the configuration may look similar to:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "your_database_name";
```


Make sure the database name matches the database you created in phpMyAdmin.

---

## 6. Run the Application

After starting Apache and MySQL, open the following URL in your browser:

```text
http://localhost/NepArt/
```

You can also directly access:

```text
http://localhost/NepArt/index.php
```

---

# 📖 How to Use NepArt

## For Buyers

1. Register for an account.
2. Log in to the platform.
3. Browse available handicraft products.
4. View product details and prices.
5. Add products to the shopping cart.
6. Manage product quantities in the cart.
7. Proceed to checkout.
8. Place an order.
9. Track the status of your order.

---

## For Artisans

1. Register as an artisan.
2. Log in to the platform.
3. Access the artisan dashboard.
4. Add a new handicraft product.
5. Upload a product image.
6. Set the product price.
7. Set the available product quantity.
8. Update or delete products when required.
9. View incoming orders.
10. Manage customer orders.

---

## 📦 Product and Stock Management

Each product includes information such as its price and available quantity.

Artisans can manage their product listings and update stock availability. Buyers can purchase products based on the available quantity.

The platform is designed to support basic inventory management by ensuring products are managed according to their available stock.

---

## 🎯 Project Objective

The main objective of NepArt is to provide a digital marketplace that supports local Nepali artisans and promotes traditional and handmade products.

The platform aims to:

- Promote Nepali handicrafts.
- Increase the digital visibility of local artisans.
- Connect artisans directly with potential buyers.
- Provide buyers with access to unique handmade products.
- Simplify product listing and order management.
- Support the digital growth of local artisan businesses.

---

## 🔮 Future Improvements

Possible future enhancements include:

- Online payment gateway integration.
- Product reviews and ratings.
- Advanced product search and filtering.
- Wishlist functionality.
- Email notifications for orders.
- Improved order tracking.
- Admin dashboard.
- Sales analytics for artisans.
- Improved mobile responsiveness.
- Product recommendation system.

---

## 👥 Contributors

This project was developed collaboratively by:

- Arpan Adhikari
- Sandesh Khadka
- Sanjog Sharma
- Purnima Bhattrai

---

## 📄 License

This project is intended for educational and learning purposes.

---

## 🇳🇵 Supporting Nepali Artisans

NepArt aims to promote Nepal's rich culture, creativity, and craftsmanship by providing local artisans with a platform to showcase and sell their handcrafted products online.