-- ========================================================
-- IronPulse Gym & Fitness Management System
-- Complete Database Schema & Seed Data
-- ========================================================

CREATE DATABASE IF NOT EXISTS `gym_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gym_db`;

-- Drop existing tables if re-importing
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `workout_logs`;
DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `class_bookings`;
DROP TABLE IF EXISTS `class_schedules`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `trainers`;
DROP TABLE IF EXISTS `memberships`;
DROP TABLE IF EXISTS `membership_plans`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Users Table (Admin & Members)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  `gender` ENUM('male', 'female', 'other') DEFAULT 'other',
  `dob` DATE DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `address` TEXT DEFAULT NULL,
  `emergency_contact` VARCHAR(150) DEFAULT NULL,
  `fitness_goals` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Membership Plans Table
-- --------------------------------------------------------
CREATE TABLE `membership_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `duration_days` INT NOT NULL DEFAULT 30,
  `price` DECIMAL(10, 2) NOT NULL,
  `badge` VARCHAR(50) DEFAULT NULL,
  `features_json` TEXT NOT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Memberships Table (User Subscription History)
-- --------------------------------------------------------
CREATE TABLE `memberships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `amount_paid` DECIMAL(10, 2) NOT NULL,
  `payment_status` ENUM('paid', 'pending', 'cancelled') NOT NULL DEFAULT 'paid',
  `status` ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Trainers Table
-- --------------------------------------------------------
CREATE TABLE `trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `specialty` VARCHAR(150) NOT NULL,
  `bio` TEXT DEFAULT NULL,
  `experience_years` INT DEFAULT 3,
  `email` VARCHAR(120) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT 'default-trainer.png',
  `social_instagram` VARCHAR(100) DEFAULT NULL,
  `social_linkedin` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Classes Table
-- --------------------------------------------------------
CREATE TABLE `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `category` ENUM('Strength', 'Cardio', 'HIIT', 'Yoga & Flexibility', 'Combat / Boxing', 'Crossfit') NOT NULL DEFAULT 'Strength',
  `trainer_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 60,
  `capacity` INT NOT NULL DEFAULT 20,
  `intensity` ENUM('Beginner', 'Intermediate', 'Advanced', 'All Levels') NOT NULL DEFAULT 'All Levels',
  `image` VARCHAR(255) DEFAULT 'class-default.jpg',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Class Schedules Table
-- --------------------------------------------------------
CREATE TABLE `class_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room` VARCHAR(80) DEFAULT 'Main Studio A',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Class Bookings Table
-- --------------------------------------------------------
CREATE TABLE `class_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `schedule_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `booking_date` DATE NOT NULL,
  `status` ENUM('confirmed', 'attended', 'cancelled') NOT NULL DEFAULT 'confirmed',
  `booked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Payments Table
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `membership_id` INT DEFAULT NULL,
  `transaction_ref` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('Cash', 'Credit Card', 'PayPal', 'Stripe', 'Bank Transfer') NOT NULL DEFAULT 'Credit Card',
  `status` ENUM('completed', 'pending', 'failed') NOT NULL DEFAULT 'completed',
  `notes` VARCHAR(255) DEFAULT NULL,
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. Workout Logs Table
-- --------------------------------------------------------
CREATE TABLE `workout_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `workout_date` DATE NOT NULL,
  `workout_type` VARCHAR(100) NOT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 45,
  `calories_burned` INT DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 10. Inquiries Table (Website Contact Form)
-- --------------------------------------------------------
CREATE TABLE `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 11. System Settings Table
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('gym_name', 'IRONPULSE FITNESS CLUB'),
('gym_tagline', 'Forge Strength. Shatter Limits. Elevate Your Life.'),
('gym_phone', '+1 (555) 234-5678'),
('gym_email', 'contact@ironpulsefitness.com'),
('gym_address', '742 Fitness Boulevard, Metro City, NY 10001'),
('currency', '$'),
('hours_weekday', '05:00 AM - 11:00 PM'),
('hours_weekend', '07:00 AM - 09:00 PM');

-- Users (passwords hashed with BCRYPT: admin123 and member123)
-- Hash for 'admin123': $2y$10$vK3FzX1B7hOqvj9r1Xh9heO9sV.a3p2b/7Z1pI8V5VkWuS3w8lQ0m
-- Hash for 'member123': $2y$10$T87sPqR9vLw9lP2fS5XgveoJzO1W6rB7/N2V8Gk9cK9tV9r6dC1mS
-- We will use freshly verified hashes:
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `gender`, `dob`, `address`, `emergency_contact`, `fitness_goals`, `status`) VALUES
(1, 'Administrator', 'admin@gym.com', '$2y$10$Z3U6185GzH5OQW376yXfUOUqZ4fH9nJv6E1VzI0aU6q6P6Y.mUu1e', '+1 (555) 000-1111', 'admin', 'other', '1988-05-12', 'Gym Management Office', 'Sarah Admin (+1 555-000-2222)', 'Maintain operational excellence and guide all members.', 'active'),
(2, 'John Anderson', 'john@example.com', '$2y$10$gXqK7j21vLhJ5nQk2F1KNu9eR5aE6qW1zI8mU0bY4tP3yU2w0oD3S', '+1 (555) 345-6789', 'member', 'male', '1995-08-20', '124 Elm Street, Metro City', 'Emily Anderson (+1 555-345-0000)', 'Build lean muscle mass and improve 5K running time.', 'active'),
(3, 'Sarah Mitchell', 'sarah@example.com', '$2y$10$gXqK7j21vLhJ5nQk2F1KNu9eR5aE6qW1zI8mU0bY4tP3yU2w0oD3S', '+1 (555) 456-7890', 'member', 'female', '1992-03-15', '45 Park Avenue, Metro City', 'David Mitchell (+1 555-456-1111)', 'Weight loss, core stability, and cardio endurance.', 'active'),
(4, 'Alex Rivera', 'alex@example.com', '$2y$10$gXqK7j21vLhJ5nQk2F1KNu9eR5aE6qW1zI8mU0bY4tP3yU2w0oD3S', '+1 (555) 567-8901', 'member', 'male', '1998-11-04', '88 Sunset Blvd, Metro City', 'Maria Rivera (+1 555-567-2222)', 'Powerlifting total 500kg & athletic agility.', 'active');

-- Membership Plans
INSERT INTO `membership_plans` (`id`, `name`, `description`, `duration_days`, `price`, `badge`, `features_json`, `is_popular`, `status`) VALUES
(1, 'Basic Starter', 'Perfect for individuals starting their fitness journey with essential gym floor access.', 30, 29.99, 'Great Value', '["Full Gym Floor Access", "Standard Cardio & Free Weights", "Locker Room & Shower Access", "1 Fitness Assessment Session", "Mobile App Access"]', 0, 'active'),
(2, 'Pro Fitness', 'Our most popular plan designed for serious results with all classes and guest passes.', 30, 59.99, 'Most Popular', '["All Basic Plan Perks", "Unlimited Group Fitness Classes", "Sauna & Steam Room Access", "2 Guest Passes per Month", "Custom Workout & Nutrition Plan", "Free Towel Service"]', 1, 'active'),
(3, 'Elite VIP', 'Ultimate performance package with dedicated personal coaching and VIP perks.', 30, 99.99, 'All Inclusive', '["All Pro Plan Perks", "4 Personal Training Sessions / mo", "Unlimited Hydromassage & Recovery", "Priority Class Booking", "Personal Locker Rental", "20% Discount at Pro Shop & Smoothie Bar"]', 0, 'active'),
(4, 'Annual Champion', 'Full year of unlimited Pro tier access with 2 months free and exclusive gym merch.', 365, 499.00, 'Best Long-Term', '["12 Months Pro Access (Save $220)", "Unlimited Group Classes & Sauna", "6 Personal Training Sessions", "Free IronPulse Gear Kit", "Unlimited Guest Passes"]', 0, 'active');

-- Trainers
INSERT INTO `trainers` (`id`, `name`, `specialty`, `bio`, `experience_years`, `email`, `phone`, `photo`, `social_instagram`, `social_linkedin`, `status`) VALUES
(1, 'Marcus "Titan" Vance', 'Strength & Hypertrophy Coach', 'Former competitive powerlifter with over a decade of coaching athletes to break personal records safely.', 11, 'marcus@ironpulsefitness.com', '+1 (555) 888-1001', 'trainer-1.jpg', '@marcustitan_fit', 'marcus-vance-cpt', 'active'),
(2, 'Elena Rostova', 'Yoga, Mobility & Flexibility', 'Certified 500-hour RYT instructor focused on mindful alignment, core restoration, and athletic recovery.', 8, 'elena@ironpulsefitness.com', '+1 (555) 888-1002', 'trainer-2.jpg', '@elena_movement', 'elena-rostova-yoga', 'active'),
(3, 'David Kim', 'HIIT, Crossfit & Calisthenics', 'High-energy coach specializing in functional biomechanics, explosive athleticism, and conditioning.', 6, 'david@ironpulsefitness.com', '+1 (555) 888-1003', 'trainer-3.jpg', '@dkim_fitness', 'david-kim-trainer', 'active'),
(4, 'Maya Johnson', 'Boxing, Kickboxing & Combat', 'Golden Gloves finalist dedicated to delivering high-octane combat conditioning and self-defense skills.', 7, 'maya@ironpulsefitness.com', '+1 (555) 888-1004', 'trainer-4.jpg', '@mayaboxing_nyc', 'maya-johnson-cpt', 'active');

-- Classes
INSERT INTO `classes` (`id`, `name`, `category`, `trainer_id`, `description`, `duration_minutes`, `capacity`, `intensity`, `image`, `status`) VALUES
(1, 'Heavy Duty Powerlifting', 'Strength', 1, 'Master the squat, bench, and deadlift with technical barbell coaching and progressive overload.', 60, 16, 'Advanced', 'class-strength.jpg', 'active'),
(2, 'High-Octane HIIT Circuit', 'HIIT', 3, 'Heart-pounding interval training alternating battle ropes, kettlebells, rowing, and bodyweight blasts.', 45, 20, 'Intermediate', 'class-hiit.jpg', 'active'),
(3, 'Power Vinyasa Yoga', 'Yoga & Flexibility', 2, 'Dynamic breath-to-movement flow designed to enhance joint mobility, core stability, and mental clarity.', 60, 25, 'All Levels', 'class-yoga.jpg', 'active'),
(4, 'Cardio Strike Kickboxing', 'Combat / Boxing', 4, 'Intense heavy-bag combos, footwork drills, and core conditioning set to high-energy rhythm.', 50, 18, 'All Levels', 'class-boxing.jpg', 'active'),
(5, 'Functional Cross-Conditioning', 'Crossfit', 3, 'A demanding blend of olympic lifts, plyometrics, and gymnastics for ultimate athletic capability.', 55, 15, 'Advanced', 'class-crossfit.jpg', 'active'),
(6, 'Core & Sculpt Pilates', 'Yoga & Flexibility', 2, 'Low-impact, high-burn mat work focusing on deep pelvic stability, posture, and muscular endurance.', 50, 22, 'Beginner', 'class-pilates.jpg', 'active');

-- Class Schedules
INSERT INTO `class_schedules` (`id`, `class_id`, `day_of_week`, `start_time`, `end_time`, `room`) VALUES
-- Monday
(1, 1, 'Monday', '07:00:00', '08:00:00', 'Iron Arena (Room A)'),
(2, 2, 'Monday', '09:00:00', '09:45:00', 'HIIT Zone'),
(3, 3, 'Monday', '17:30:00', '18:30:00', 'Zen Studio B'),
(4, 4, 'Monday', '19:00:00', '19:50:00', 'Combat Ring'),
-- Tuesday
(5, 5, 'Tuesday', '06:30:00', '07:25:00', 'Crossfit Rig'),
(6, 6, 'Tuesday', '10:00:00', '10:50:00', 'Zen Studio B'),
(7, 1, 'Tuesday', '18:00:00', '19:00:00', 'Iron Arena (Room A)'),
-- Wednesday
(8, 2, 'Wednesday', '07:00:00', '07:45:00', 'HIIT Zone'),
(9, 3, 'Wednesday', '12:00:00', '13:00:00', 'Zen Studio B'),
(10, 4, 'Wednesday', '18:30:00', '19:20:00', 'Combat Ring'),
-- Thursday
(11, 1, 'Thursday', '08:00:00', '09:00:00', 'Iron Arena (Room A)'),
(12, 5, 'Thursday', '17:30:00', '18:25:00', 'Crossfit Rig'),
(13, 6, 'Thursday', '19:00:00', '19:50:00', 'Zen Studio B'),
-- Friday
(14, 2, 'Friday', '07:30:00', '08:15:00', 'HIIT Zone'),
(15, 4, 'Friday', '18:00:00', '18:50:00', 'Combat Ring'),
(16, 3, 'Friday', '19:15:00', '20:15:00', 'Zen Studio B'),
-- Saturday
(17, 5, 'Saturday', '08:30:00', '09:25:00', 'Crossfit Rig'),
(18, 2, 'Saturday', '10:00:00', '10:45:00', 'HIIT Zone'),
(19, 3, 'Saturday', '11:15:00', '12:15:00', 'Zen Studio B');

-- Active Memberships
INSERT INTO `memberships` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `amount_paid`, `payment_status`, `status`) VALUES
(1, 2, 2, CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, 59.99, 'paid', 'active'),
(2, 3, 3, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 25 DAY, 99.99, 'paid', 'active'),
(3, 4, 4, CURDATE() - INTERVAL 45 DAY, CURDATE() + INTERVAL 320 DAY, 499.00, 'paid', 'active');

-- Payments
INSERT INTO `payments` (`id`, `user_id`, `membership_id`, `transaction_ref`, `amount`, `payment_method`, `status`, `notes`, `payment_date`) VALUES
(1, 2, 1, 'TXN-2026-90112', 59.99, 'Credit Card', 'completed', 'Pro Fitness Monthly Subscription', NOW() - INTERVAL 10 DAY),
(2, 3, 2, 'TXN-2026-90113', 99.99, 'PayPal', 'completed', 'Elite VIP Monthly Subscription', NOW() - INTERVAL 5 DAY),
(3, 4, 3, 'TXN-2026-90114', 499.00, 'Credit Card', 'completed', 'Annual Champion Full Year Plan', NOW() - INTERVAL 45 DAY);

-- Sample Bookings
INSERT INTO `class_bookings` (`id`, `schedule_id`, `user_id`, `booking_date`, `status`, `booked_at`) VALUES
(1, 1, 2, CURDATE(), 'confirmed', NOW() - INTERVAL 1 DAY),
(2, 2, 3, CURDATE(), 'confirmed', NOW() - INTERVAL 2 DAY),
(3, 4, 4, CURDATE(), 'confirmed', NOW() - INTERVAL 1 DAY),
(4, 3, 2, CURDATE() - INTERVAL 3 DAY, 'attended', NOW() - INTERVAL 4 DAY);

-- Workout Logs for members
INSERT INTO `workout_logs` (`id`, `user_id`, `workout_date`, `workout_type`, `duration_minutes`, `calories_burned`, `notes`) VALUES
(1, 2, CURDATE() - INTERVAL 2 DAY, 'Upper Body Hypertrophy', 60, 420, 'Bench Press 85kg 4x8, Incline DB Press 30kg 3x10, Barbell Rows 75kg 4x10. Felt strong!'),
(2, 2, CURDATE() - INTERVAL 1 DAY, 'Cardio & Abs', 45, 380, 'Treadmill interval sprints 25 min, hanging leg raises, cable woodchoppers.'),
(3, 3, CURDATE() - INTERVAL 2 DAY, 'Power Vinyasa Yoga', 60, 290, 'Full hip opening flow and arm balances. Great breathing rhythm.'),
(4, 4, CURDATE() - INTERVAL 1 DAY, 'Deadlift & Squat Heavy', 75, 550, 'Squat 160kg 5x5, Deadlift 190kg 3x3. Personal record on deadlift!');

-- Sample Inquiries
INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
(1, 'Michael Scott', 'mscott@dunder.com', '+1 (555) 123-4567', 'Corporate Membership Rates', 'Hi team, do you offer discount packages for companies with 15+ employees wanting gym access?', 'unread'),
(2, 'Jessica Alba', 'jessica@example.com', '+1 (555) 789-0123', 'Private Yoga Consultation', 'Interested in scheduling a 1-on-1 session with Elena Rostova for prenatal mobility work.', 'read');
