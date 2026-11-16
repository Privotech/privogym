/**
 * IronPulse Fitness & Gym Management System
 * Interactive Client-Side JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // Mobile Navigation Toggle
    // -------------------------------------------------------------
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    }

    // -------------------------------------------------------------
    // Timetable Day Tab Filter
    // -------------------------------------------------------------
    const dayTabs = document.querySelectorAll('.day-tab');
    const timetableCards = document.querySelectorAll('.timetable-card');

    if (dayTabs.length > 0) {
        dayTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                dayTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const selectedDay = tab.getAttribute('data-day');
                timetableCards.forEach(card => {
                    const cardDay = card.getAttribute('data-day');
                    if (selectedDay === 'all' || cardDay === selectedDay) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // -------------------------------------------------------------
    // Class Category Filter (on classes.php)
    // -------------------------------------------------------------
    const catButtons = document.querySelectorAll('.cat-filter-btn');
    const classCards = document.querySelectorAll('.class-filter-item');

    if (catButtons.length > 0) {
        catButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                catButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const cat = btn.getAttribute('data-category');
                classCards.forEach(item => {
                    const itemCat = item.getAttribute('data-category');
                    if (cat === 'all' || itemCat === cat) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // -------------------------------------------------------------
    // BMI Calculator
    // -------------------------------------------------------------
    const bmiForm = document.getElementById('bmi-form');
    if (bmiForm) {
        bmiForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const weight = parseFloat(document.getElementById('bmi-weight').value);
            const height = parseFloat(document.getElementById('bmi-height').value);
            const resultScore = document.getElementById('bmi-result-score');
            const resultStatus = document.getElementById('bmi-result-status');
            const resultAdvice = document.getElementById('bmi-result-advice');

            if (!weight || !height || height <= 0 || weight <= 0) {
                alert('Please enter valid height and weight values.');
                return;
            }

            // Height in meters
            const heightInMeters = height / 100;
            const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);

            let status = '';
            let color = '';
            let advice = '';

            if (bmi < 18.5) {
                status = 'Underweight';
                color = '#38bdf8';
                advice = 'Focus on nutrient-dense calorie surplus and progressive hypertrophy weight training.';
            } else if (bmi >= 18.5 && bmi <= 24.9) {
                status = 'Healthy Weight (Optimal)';
                color = '#10b981';
                advice = 'Outstanding! Maintain your current routine with balanced resistance and cardiovascular training.';
            } else if (bmi >= 25 && bmi <= 29.9) {
                status = 'Overweight';
                color = '#f59e0b';
                advice = 'Incorporate 3-4 HIIT or cardio sessions weekly combined with a moderate 300-500 kcal deficit.';
            } else {
                status = 'Obese';
                color = '#ef4444';
                advice = 'We recommend consulting our certified personal trainers for a personalized low-impact regimen.';
            }

            resultScore.textContent = bmi;
            resultScore.style.color = color;
            resultStatus.textContent = status;
            resultStatus.style.color = color;
            if (resultAdvice) resultAdvice.textContent = advice;
        });
    }

    // -------------------------------------------------------------
    // Calorie / TDEE Calculator
    // -------------------------------------------------------------
    const tdeeForm = document.getElementById('tdee-form');
    if (tdeeForm) {
        tdeeForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const gender = document.getElementById('tdee-gender').value;
            const age = parseInt(document.getElementById('tdee-age').value);
            const weight = parseFloat(document.getElementById('tdee-weight').value);
            const height = parseFloat(document.getElementById('tdee-height').value);
            const activity = parseFloat(document.getElementById('tdee-activity').value);

            // Mifflin-St Jeor Equation
            let bmr = 0;
            if (gender === 'male') {
                bmr = (10 * weight) + (6.25 * height) - (5 * age) + 5;
            } else {
                bmr = (10 * weight) + (6.25 * height) - (5 * age) - 161;
            }

            const tdee = Math.round(bmr * activity);
            const lossTarget = Math.round(tdee - 500);
            const gainTarget = Math.round(tdee + 400);

            const tdeeResult = document.getElementById('tdee-result');
            const lossResult = document.getElementById('tdee-loss');
            const gainResult = document.getElementById('tdee-gain');

            if (tdeeResult) tdeeResult.textContent = tdee.toLocaleString() + ' kcal';
            if (lossResult) lossResult.textContent = lossTarget.toLocaleString() + ' kcal';
            if (gainResult) gainResult.textContent = gainTarget.toLocaleString() + ' kcal';
        });
    }

    // -------------------------------------------------------------
    // 1-Rep Max (1RM) Calculator
    // -------------------------------------------------------------
    const oneRmForm = document.getElementById('onerm-form');
    if (oneRmForm) {
        oneRmForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const weight = parseFloat(document.getElementById('onerm-weight').value);
            const reps = parseInt(document.getElementById('onerm-reps').value);

            if (reps < 1 || reps > 30) {
                alert('Reps must be between 1 and 30 for accurate calculation.');
                return;
            }

            // Brzycki formula: 1RM = weight / (1.0278 - 0.0278 * reps)
            let oneRm = 0;
            if (reps === 1) {
                oneRm = weight;
            } else {
                oneRm = Math.round(weight / (1.0278 - (0.0278 * reps)));
            }

            const scoreEl = document.getElementById('onerm-result');
            if (scoreEl) scoreEl.textContent = oneRm + ' kg / lbs';
        });
    }

    // -------------------------------------------------------------
    // Auto Dismiss Flash Alerts
    // -------------------------------------------------------------
    const flashAlerts = document.querySelectorAll('.flash-alert');
    if (flashAlerts.length > 0) {
        setTimeout(() => {
            flashAlerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    }
});
