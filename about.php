<?php
/**
 * IronPulse Gym & Fitness Management System
 * About Us Page
 */

$pageTitle = "About Us - Our Mission & Facility";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">About IronPulse</div>
        <h1 class="section-title">ENGINEERED FOR <span class="text-gradient">STRENGTH & LONGEVITY</span></h1>
        <p class="section-subtitle mx-auto">Discover the philosophy, standard, and vision behind the city's highest-rated training center.</p>
    </div>
</div>

<section style="padding: 80px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; margin-bottom: 80px;">
            <div>
                <div class="section-tag">Our Philosophy</div>
                <h2 style="font-family: var(--font-heading); font-size: 38px; color: #fff; margin-bottom: 20px;">
                    NO GIMMICKS. NO SHORTCUTS. <span class="text-gradient">JUST RELENTLESS RESULTS.</span>
                </h2>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 20px;">
                    Founded in 2018, IronPulse Gym was built out of frustration with commercial "big box" gyms that prioritize signups over member outcomes. We set out to engineer a community and training atmosphere where discipline meets state-of-the-art sports science.
                </p>
                <p style="color: var(--text-muted); line-height: 1.8; margin-bottom: 30px;">
                    Whether you are an aspiring powerlifter stepping up to the barbell for the first time, a runner seeking injury resilience, or an executive looking to reclaim energy, IronPulse gives you the coaching, equipment, and accountability to succeed.
                </p>
                <div style="display: flex; gap: 24px;">
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 36px; color: var(--primary);">25,000</div>
                        <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase;">Square Feet Facility</div>
                    </div>
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 36px; color: var(--primary);">100%</div>
                        <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase;">Certified Coaches</div>
                    </div>
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 36px; color: var(--primary);">24/7</div>
                        <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase;">Clean & Maintained</div>
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 36px; box-shadow: var(--shadow-lg);">
                <h3 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 18px;">Our Core Facility Pillars</h3>
                
                <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;">🏋️</div>
                    <div>
                        <h4 style="color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 4px;">Olympic & Powerlifting Rigs</h4>
                        <p style="color: var(--text-muted); font-size: 13px;">Eleiko calibrated competition plates, Texas power bars, chalk bowls, and dedicated deadlift platforms.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;">⚡</div>
                    <div>
                        <h4 style="color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 4px;">Functional Athletic Turf</h4>
                        <p style="color: var(--text-muted); font-size: 13px;">40-meter indoor turf runway for sled pushes, farmers walks, kettlebell sprints, and agility conditioning.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;">🧘</div>
                    <div>
                        <h4 style="color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 4px;">Mind & Recovery Sanctuary</h4>
                        <p style="color: var(--text-muted); font-size: 13px;">Sound-insulated studios for Vinyasa Yoga and Pilates, accompanied by dry Finnish cedar saunas.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;">🥗</div>
                    <div>
                        <h4 style="color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 4px;">Nutrition & Fuel Bar</h4>
                        <p style="color: var(--text-muted); font-size: 13px;">Fresh organic cold-pressed juices, whey & vegan protein smoothies, and certified nutritionist consultations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
