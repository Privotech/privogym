# 🏋️ IRONPULSE FITNESS CLUB - Gym Website & Management System (PHP & MySQL)

A modern, full-featured **Gym Website and Fitness Club Management Web Application** built with pure **PHP 8 (PDO)**, **MySQL / MariaDB**, and vanilla responsive CSS/JavaScript.

Designed for high-performance athletic gyms, fitness centers, powerlifting clubs, and boutique fitness studios.

---

## 🌟 Highlights & Core Modules

### 1. 🌐 Public-Facing Website (Frontend)
- **High-Energy Dark Luxury UI**: Custom athletic design system with bold typography (`Teko` + `Plus Jakarta Sans`), responsive mobile navigation, and interactive animations.
- **Hero Banner**: Catchy call-to-actions, live statistics counter (1,500+ members, 25+ coaches, 48+ weekly classes), and quick pass reservations.
- **Gym Programs & Classes**: Filterable discipline catalog (Strength, HIIT, Yoga, Combat / Boxing, Crossfit, Cardio) with intensity badges and trainer tags.
- **Live Weekly Timetable**: Interactive tabbed day-of-week timetable schedule (Monday through Sunday) with studio rooms, start/end times, and booking prompts.
- **Membership Pricing Tiers**: Tiered plans (Basic Starter, Pro Fitness, Elite VIP, Annual Champion) with included perks checklists.
- **Meet the Trainers**: Certified coach profiles, biographies, credentials, experience levels, and direct coaching request buttons.
- **Client Testimonials**: Authentic athlete transformation stories with star ratings.
- **Interactive Fitness Calculator Suite (`bmi-calculator.php`)**:
  - **BMI Calculator**: Computes Body Mass Index with interactive classification status and advice.
  - **Calorie & TDEE Calculator**: Mifflin-St Jeor formula calculating daily maintenance, fat loss (-500 kcal), and muscle building (+400 kcal) calorie targets.
  - **1-Rep Max (1RM) Estimator**: Brzycki formula estimating maximum single barbell lift from reps and weight.
- **Contact & Location (`contact.php`)**: Inquiry submission form with direct database logging, facility opening hours, and address details.

---

### 2. 👤 Member Portal (`/member/`)
- **Athlete Dashboard (`member/index.php`)**: Personalized digital gym pass with membership status badge, expiration countdown, lifetime training minutes, and quick links.
- **Membership Plan Management (`member/membership.php`)**: Review active plan perks, upgrade tiers, or renew with instant checkout simulation.
- **Class Booking System (`member/book-class.php`)**: Browse weekly slots, view real-time studio capacity (e.g. 16/20 spots reserved), and reserve a spot with 1 click.
- **My Reservations (`member/my-bookings.php`)**: Manage confirmed and past bookings with one-click cancellation.
- **Personal Workout Tracker (`member/workout-tracker.php`)**: Daily gym workout log (session focus, exercises, sets, reps, duration, calories burned, and training notes).
- **Billing & Digital Invoices (`member/payments.php` & `member/invoice.php`)**: Transaction history and printable receipts with official invoice layout.
- **Profile & Security (`member/profile.php`)**: Update profile info, emergency contact, fitness goals, and change password securely.

---

### 3. 👑 Administrator & Front Desk Portal (`/admin/`)
- **Operations Dashboard (`admin/index.php`)**: Live KPI metrics (Total Members, Active Passes, All-time Revenue, Today's Bookings), quick attendance check-ins, and recent activity.
- **Member Management (`admin/members.php`, `member-add.php`, `member-view.php`)**:
  - Search by name, email, or phone with status filters (Active vs Inactive).
  - Register new members from the front desk with initial plan and payment record.
  - Full member profile inspection, attendance records, and manual plan renewals.
- **Class Bookings & Attendance (`admin/bookings.php`)**: Track studio reservations, filter by date/status, and toggle check-in (Attended / Cancelled / Confirmed).
- **Workout Classes Management (`admin/classes.php`)**: CRUD for classes, categories, duration, max capacity, and coach assignment.
- **Timetable Scheduler (`admin/schedule.php`)**: Allocate recurring weekly time slots across studio rooms.
- **Trainers Management (`admin/trainers.php`)**: Add, edit, or remove certified coaches, specialties, and contact info.
- **Membership Plans (`admin/plans.php`)**: Customize subscription pricing, duration in days, featured popular badge, and included perk lists.
- **Payments & Revenue (`admin/payments.php`)**: Financial overview, revenue breakdown (Cash vs Electronic), record offline payments, and print invoices.
- **Contact Inquiries (`admin/inquiries.php`)**: Review submitted visitor messages, toggle unread/read/replied status, and delete inquiries.
- **System Settings (`admin/settings.php`)**: Dynamic facility branding, contact phone, email, opening hours, and currency symbol.

---

## 🔒 Security & Code Standards

- **PDO Prepared Statements**: 100% parameter binding on all queries, eliminating SQL injection.
- **BCRYPT Password Hashing**: Passwords stored using `password_hash($pass, PASSWORD_BCRYPT)`.
- **Cross-Site Scripting (XSS) Prevention**: All dynamic output escaped using `htmlspecialchars()`.
- **CSRF Token Verification**: Form submissions require valid `csrf_token()`.
- **Role-Based Authorization Guards**: Strict middleware separation (`require_admin()`, `require_member()`).

---

## 🚀 Quick Setup Instructions

### Option A: 1-Click Interactive Web Installer (Recommended)

1. Open your **XAMPP Control Panel** and ensure **Apache** and **MySQL** are **Started** (green).
2. Place this project folder inside `C:\xampp\htdocs\student\`.
3. Open your web browser and navigate to:
   ```
   http://localhost/student/install.php
   ```
4. Verify the database credentials (default XAMPP uses Host: `localhost`, Port: `3306`, User: `root`, Password: empty) and click **"Install Database & Demo Data Now"**.
5. The installer will automatically create `gym_db`, configure all 11 tables, and insert complete demo data with ready-to-use accounts!

---

### Option B: Manual phpMyAdmin Import

1. Open **phpMyAdmin** in your browser at `http://localhost/phpmyadmin/`.
2. Click **New** on the left sidebar, name the database `gym_db`, and select collation `utf8mb4_unicode_ci`.
3. Click on the newly created `gym_db`, select the **Import** tab at the top.
4. Click **Choose File**, select `c:\xampp\htdocs\student\database.sql`, and click **Import**.
5. Once imported, browse to:
   ```
   http://localhost/student/index.php
   ```

---

## 🔑 Default Demo Login Credentials

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@gym.com` | `admin123` | `http://localhost/student/admin/` |
| **Member** | `john@example.com` | `member123` | `http://localhost/student/member/` |
| **Member** | `sarah@example.com` | `member123` | `http://localhost/student/member/` |
| **Member** | `alex@example.com` | `member123` | `http://localhost/student/member/` |

*(You can also use the **Quick Demo Buttons** directly on the `login.php` page to auto-fill credentials in 1 click!)*

---

## 📂 Project Directory Structure

```
c:\xampp\htdocs\student\
│
├── config/
│   ├── config.php                 # Global constants, base URL, session init & helpers
│   ├── db.php                     # PDO MySQL connection handler
│   └── auth.php                   # Authentication guards (Admin vs Member)
│
├── database.sql                   # Full SQL schema & realistic seed data
├── install.php                    # 1-Click web installer wizard
├── README.md                      # Documentation & setup guide
│
├── assets/
│   ├── css/
│   │   ├── style.css              # Main dark athletic design system
│   │   ├── admin.css              # Admin portal sidebar & management styles
│   │   └── member.css             # Member portal & digital pass styles
│   └── js/
│       └── main.js                # Interactive calculators, timetable filters, mobile navigation
│
├── includes/
│   ├── header.php                 # Public navigation bar & meta tags
│   ├── footer.php                 # Public footer with schedule & links
│   └── alerts.php                 # Session flash alert toast component
│
├── index.php                      # Public Landing Page
├── about.php                      # About facility, mission & standards
├── classes.php                    # Workout classes catalog with category filters
├── timetable.php                  # Full weekly timetable & schedule
├── trainers.php                   # Meet certified coaches & bios
├── pricing.php                    # Membership plans & FAQs
├── bmi-calculator.php             # BMI, Calorie / TDEE & 1RM calculator suite
├── contact.php                    # Contact form (logs to database) & opening hours
│
├── login.php                      # Unified login for Admin & Member
├── register.php                   # Member registration & plan selection
├── logout.php                     # Session termination
│
├── member/                        # MEMBER PORTAL
│   ├── includes/
│   │   ├── member_header.php      # Member navigation & avatar
│   │   └── member_footer.php
│   ├── index.php                  # Member Dashboard (Pass, countdown, stats, bookings)
│   ├── membership.php             # Active plan status, upgrade & renew
│   ├── book-class.php             # Class booking with real-time capacity slots
│   ├── my-bookings.php            # Active and past reservations
│   ├── workout-tracker.php        # Daily training log & volume history
│   ├── payments.php               # Payment history & receipts
│   ├── invoice.php                # Printable official receipt/invoice
│   └── profile.php                # Profile settings & password change
│
└── admin/                         # ADMIN PORTAL
    ├── includes/
    │   ├── admin_header.php       # Admin sidebar & topbar
    │   └── admin_footer.php
    ├── index.php                  # Operations dashboard with KPI metrics
    ├── members.php                # Members list, search, filter, delete
    ├── member-add.php             # Front desk member registration
    ├── member-view.php            # Member details, assign plan, logs
    ├── plans.php                  # Membership plans CRUD
    ├── classes.php                # Workout classes CRUD
    ├── schedule.php               # Timetable sessions CRUD
    ├── trainers.php               # Coaches management CRUD
    ├── bookings.php               # Booking attendance & check-ins
    ├── payments.php               # Revenue tracking & record offline cash
    ├── inquiries.php              # Website contact form messages
    └── settings.php               # Gym details, hours, currency settings
```

---

## 🛠️ Requirements

- **PHP**: 7.4 or 8.x (tested on PHP 8.2)
- **PHP Extensions**: `pdo_mysql`, `mbstring`, `session` (enabled by default in standard XAMPP)
- **Web Server**: Apache (XAMPP / WAMP / LAMP / Laragon) or PHP Built-in Server
- **Database**: MySQL 5.7+ or MariaDB 10.4+
