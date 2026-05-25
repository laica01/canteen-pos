# 🍱 Mura-Mura Canteen POS — Setup Guide

## Files
| File | Description |
|------|-------------|
| `index.php` | Login & Register |
| `dashboard.php` | Student ordering page |
| `order.php` | Order processing |
| `cancel_order.php` | Cancel order + refund |
| `admin.php` | Admin dashboard |
| `staff_dashboard.php` | Staff queue view (auto-refreshes) |
| `add_product.php` | Add food items |
| `add_balance.php` | Top up student wallets |
| `complete.php` | Mark order as done |
| `db.php` | Database connection |
| `style.css` | Shared styles |
| `setup.sql` | Database schema + sample data |

## Setup Steps

1. **Import database**: Run `setup.sql` in phpMyAdmin
2. **Copy files** to your `htdocs/canteen_pos/` folder
3. **Create `images/` folder** inside `canteen_pos/`
4. **Edit `db.php`** if your MySQL credentials differ
5. Visit `http://localhost/canteen_pos/`

## Default Admin Login
- Username: `admin`
- Password: `admin123`

## Roles
- **admin** → Full access (orders, products, balance top-up)
- **staff** → See preparing orders, mark as done
- **student** → Browse menu, order, view history

## Features
- ✅ Prepaid wallet system (₱ balance)
- ✅ Insufficient balance popup (prevented at UI + server)
- ✅ 1-minute cancel window with refund
- ✅ Stock tracking (auto marks Sold Out)
- ✅ Order status: Preparing → Completed / Cancelled
- ✅ Admin stats dashboard
- ✅ Staff queue view (auto-refreshes every 30s)
- ✅ Parent top-up with quick amount buttons
- ✅ SQL injection protection (real_escape_string)
- ✅ Password hashing (bcrypt)
