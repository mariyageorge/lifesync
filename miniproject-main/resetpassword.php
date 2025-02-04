<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LIFE-SYNC</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6F4E37;
            --secondary-color: #8B4513;
            --accent-color: #D2691E;
            --card-bg: #F4ECD8;
            --bg-color: #F5DEB3;
            --text-primary: #3E2723;
            --text-secondary: #5D4037;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
        }

        .main-header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .reset-container {
            background-color: var(--card-bg);
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin: 50px auto;
        }

        .reset-container h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .input-field {
            margin-bottom: 20px;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid var(--secondary-color);
            border-radius: 8px;
            background-color: white;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
        }

       
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: var(--secondary-color);
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            color: var(--primary-color);
            font-size: 14px;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .success-message, .error-message {
            font-size: 14px;
            margin-top: 10px;
        }

        .success-message {
            color: #4caf50;
        }

        .error-message {
            color: #ff4c4c;
        }

      
    </style>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-container">
                <div class="d-flex align-items-center">
                    <a class="logo" href="index.php">
                        <div class="logo-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <span class="logo-text d-none d-sm-inline">LIFE-SYNC</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="reset-container">
            <h2>Reset Your Password</h2>
            <form id="resetForm">
            <p>Please enter your new password below to securely reset your account access.</p> 

                <input type="password" id="newPassword" class="input-field" placeholder="Enter new password" required>
                <input type="password" id="confirmPassword" class="input-field" placeholder="Confirm new password" required>
                
             

                <button type="submit" class="btn-submit">Reset Password</button>
            </form>

            <p class="error-message" id="errorMessage"></p>
            <p class="success-message" id="successMessage"></p>

            <div class="links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        const resetForm = document.getElementById('resetForm');
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const passwordStrengthMeter = document.getElementById('passwordStrengthMeter');

        // Password strength and validation requirements
        const validatePassword = (password) => {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                specialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            // Update requirement indicators
            document.getElementById('lengthReq').style.color = requirements.length ? '#4caf50' : '#ff4c4c';
            document.getElementById('uppercaseReq').style.color = requirements.uppercase ? '#4caf50' : '#ff4c4c';
            document.getElementById('numberReq').style.color = requirements.number ? '#4caf50' : '#ff4c4c';
            document.getElementById('specialCharReq').style.color = requirements.specialChar ? '#4caf50' : '#ff4c4c';

            // Calculate password strength
            const strengthScore = 
                (requirements.length ? 1 : 0) + 
                (requirements.uppercase ? 1 : 0) + 
                (requirements.number ? 1 : 0) + 
                (requirements.specialChar ? 1 : 0);

            // Update strength meter
            passwordStrengthMeter.style.width = `${strengthScore * 25}%`;
            if (strengthScore <= 1) {
                passwordStrengthMeter.classList.remove('strength-medium', 'strength-strong');
                passwordStrengthMeter.classList.add('strength-weak');
            } else if (strengthScore <= 3) {
                passwordStrengthMeter.classList.remove('strength-weak', 'strength-strong');
                passwordStrengthMeter.classList.add('strength-medium');
            } else {
                passwordStrengthMeter.classList.remove('strength-weak', 'strength-medium');
                passwordStrengthMeter.classList.add('strength-strong');
            }

            return Object.values(requirements).every(req => req);
        };

        // Real-time password validation
        newPasswordInput.addEventListener('input', () => {
            validatePassword(newPasswordInput.value);
        });

        resetForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Reset previous messages
            errorMessage.textContent = "";
            successMessage.textContent = "";

            // Validate password
            if (!validatePassword(newPassword)) {
                errorMessage.textContent = "Password does not meet requirements!";
                return;
            }

            // Check password match
            if (newPassword !== confirmPassword) {
                errorMessage.textContent = "Passwords do not match!";
                return;
            }

            // Successful password reset logic would go here
            successMessage.textContent = "Password successfully reset!";
            
            // Optional: Clear form fields
            newPasswordInput.value = "";
            confirmPasswordInput.value = "";
        });
    </script>
</body>
</html>