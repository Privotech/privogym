<?php
/**
 * IronPulse Gym & Fitness Management System
 * Comprehensive Fitness Calculators Suite
 */

$pageTitle = "Fitness Calculators - BMI, Calorie & 1RM";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">Interactive Tools</div>
        <h1 class="section-title">FITNESS & PERFORMANCE <span class="text-gradient">CALCULATORS</span></h1>
        <p class="section-subtitle mx-auto">Use our scientifically validated formulas to assess body composition, energy expenditure, and maximum strength.</p>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <!-- 1. BMI CALCULATOR -->
        <div class="calc-box" style="margin-bottom: 60px;">
            <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px; margin-bottom: 30px;">
                <span class="badge badge-primary" style="margin-bottom: 8px;">TOOL 1</span>
                <h2 style="font-family: var(--font-heading); font-size: 32px; color: #fff;">BODY MASS INDEX (BMI) CALCULATOR</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Calculates body fat indicator based on weight and height ratio.</p>
            </div>

            <div class="calc-grid">
                <form id="bmi-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="bmi-weight">Weight (kg)</label>
                            <input type="number" step="0.5" id="bmi-weight" class="form-control" placeholder="e.g. 78" required>
                        </div>
                        <div class="form-group">
                            <label for="bmi-height">Height (cm)</label>
                            <input type="number" step="0.5" id="bmi-height" class="form-control" placeholder="e.g. 178" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="bmi-age">Age</label>
                            <input type="number" id="bmi-age" class="form-control" placeholder="e.g. 26">
                        </div>
                        <div class="form-group">
                            <label for="bmi-gender">Biological Sex</label>
                            <select id="bmi-gender" class="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Calculate BMI</button>
                </form>

                <div class="calc-result-box">
                    <div style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Result</div>
                    <div class="calc-score" id="bmi-result-score">--</div>
                    <div class="calc-status" id="bmi-result-status" style="color: var(--accent);">Enter your metrics to calculate</div>
                    <p id="bmi-result-advice" style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                        Your personalized health feedback and recommended training approach will appear here.
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. CALORIE & TDEE CALCULATOR -->
        <div class="calc-box" style="margin-bottom: 60px;">
            <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px; margin-bottom: 30px;">
                <span class="badge badge-primary" style="margin-bottom: 8px;">TOOL 2</span>
                <h2 style="font-family: var(--font-heading); font-size: 32px; color: #fff;">DAILY ENERGY & TDEE CALCULATOR</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Calculates your Basal Metabolic Rate (BMR) and daily calories needed to maintain, lose, or gain weight.</p>
            </div>

            <div class="calc-grid">
                <form id="tdee-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="tdee-gender">Gender</label>
                            <select id="tdee-gender" class="form-control" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tdee-age">Age (years)</label>
                            <input type="number" id="tdee-age" class="form-control" placeholder="e.g. 27" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="tdee-weight">Weight (kg)</label>
                            <input type="number" step="0.5" id="tdee-weight" class="form-control" placeholder="e.g. 80" required>
                        </div>
                        <div class="form-group">
                            <label for="tdee-height">Height (cm)</label>
                            <input type="number" step="0.5" id="tdee-height" class="form-control" placeholder="e.g. 180" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tdee-activity">Physical Activity Level</label>
                        <select id="tdee-activity" class="form-control">
                            <option value="1.2">Sedentary (Desk job, minimal exercise)</option>
                            <option value="1.375">Light Activity (Exercise 1-3 days/week)</option>
                            <option value="1.55" selected>Moderate Activity (Exercise 3-5 days/week)</option>
                            <option value="1.725">Heavy Training (Intense exercise 6-7 days/week)</option>
                            <option value="1.9">Athlete / Physical Labor (Twice daily training)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Calculate Daily Calorie Targets</button>
                </form>

                <div class="calc-result-box">
                    <div style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Maintenance Calories (TDEE)</div>
                    <div class="calc-score" id="tdee-result" style="font-size: 52px; color: #f97316;">--</div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; text-align: left;">
                        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 14px;">
                            <div style="font-size: 12px; font-weight: 700; color: #6ee7b7; text-transform: uppercase;">Fat Loss (-500 kcal)</div>
                            <div id="tdee-loss" style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 4px;">--</div>
                        </div>

                        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 14px;">
                            <div style="font-size: 12px; font-weight: 700; color: #93c5fd; text-transform: uppercase;">Muscle Gain (+400 kcal)</div>
                            <div id="tdee-gain" style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 4px;">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. 1-REP MAX CALCULATOR -->
        <div class="calc-box">
            <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px; margin-bottom: 30px;">
                <span class="badge badge-primary" style="margin-bottom: 8px;">TOOL 3</span>
                <h2 style="font-family: var(--font-heading); font-size: 32px; color: #fff;">1-REP MAX (1RM) STRENGTH ESTIMATOR</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Estimates your maximum single-rep barbell lift based on weight lifted for repetitions (Brzycki formula).</p>
            </div>

            <div class="calc-grid">
                <form id="onerm-form">
                    <div class="form-group">
                        <label for="onerm-weight">Weight Lifted (kg or lbs)</label>
                        <input type="number" step="0.5" id="onerm-weight" class="form-control" placeholder="e.g. 100" required>
                    </div>

                    <div class="form-group">
                        <label for="onerm-reps">Repetitions Performed (1 - 12)</label>
                        <input type="number" id="onerm-reps" class="form-control" min="1" max="15" placeholder="e.g. 5" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Estimate 1-Rep Max</button>
                </form>

                <div class="calc-result-box">
                    <div style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Estimated 1RM Peak Lift</div>
                    <div class="calc-score" id="onerm-result" style="color: #e11d48;">--</div>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 10px;">
                        Always warm up progressively before testing heavy loads and ensure a spotter is present.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
