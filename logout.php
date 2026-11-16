<?php
/**
 * IronPulse Gym & Fitness Management System
 * Logout Handler
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';

logout_user();
flash('info', 'You have been signed out successfully. See you at the gym!');
redirect('login.php');
