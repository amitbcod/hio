    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Password strength validator
        function validatePassword(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[\W_]/.test(password)
            };
            return requirements;
        }

        // Update password strength display
        function updatePasswordStrength(inputId, strengthId) {
            const password = document.getElementById(inputId).value;
            const requirements = validatePassword(password);
            const strengthDiv = document.getElementById(strengthId);

            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }

            let html = '<div class="password-strength">';
            html += '<strong>Password Requirements:</strong>';
            html += '<div class="strength-item ' + (requirements.length ? 'valid' : 'invalid') + '">';
            html += '<i class="fas fa-' + (requirements.length ? 'check' : 'times') + '"></i>';
            html += 'At least 8 characters</div>';

            html += '<div class="strength-item ' + (requirements.uppercase ? 'valid' : 'invalid') + '">';
            html += '<i class="fas fa-' + (requirements.uppercase ? 'check' : 'times') + '"></i>';
            html += 'One uppercase letter</div>';

            html += '<div class="strength-item ' + (requirements.lowercase ? 'valid' : 'invalid') + '">';
            html += '<i class="fas fa-' + (requirements.lowercase ? 'check' : 'times') + '"></i>';
            html += 'One lowercase letter</div>';

            html += '<div class="strength-item ' + (requirements.number ? 'valid' : 'invalid') + '">';
            html += '<i class="fas fa-' + (requirements.number ? 'check' : 'times') + '"></i>';
            html += 'One number</div>';

            html += '<div class="strength-item ' + (requirements.special ? 'valid' : 'invalid') + '">';
            html += '<i class="fas fa-' + (requirements.special ? 'check' : 'times') + '"></i>';
            html += 'One special character</div></div>';

            strengthDiv.innerHTML = html;
        }

        // Check password match
        function checkPasswordMatch(passwordId, confirmId, matchId) {
            const password = document.getElementById(passwordId).value;
            const confirm = document.getElementById(confirmId).value;
            const matchDiv = document.getElementById(matchId);

            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }

            if (password === confirm) {
                matchDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check-circle"></i> Passwords match</div>';
            } else {
                matchDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle"></i> Passwords do not match</div>';
            }
        }

        // Toggle password visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Toggle owner fields
        function toggleOwnerFields() {
            const isOwnerNo = document.getElementById('owner_no').checked;
            const ownerFieldsSection = document.getElementById('owner-fields-section');
            
            if (isOwnerNo) {
                ownerFieldsSection.style.display = 'block';
            } else {
                ownerFieldsSection.style.display = 'none';
            }
        }

        // Form validation
        function validateSignupForm(event) {
            event.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const requirements = validatePassword(password);
            
            // Check all requirements
            if (!requirements.length || !requirements.uppercase || !requirements.lowercase || 
                !requirements.number || !requirements.special) {
                alert('Password does not meet all requirements');
                return false;
            }
            
            // Check password match
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }
            
            // Check terms
            const agreeTerms = document.getElementById('agree_terms').checked;
            if (!agreeTerms) {
                alert('You must agree to the terms and conditions');
                return false;
            }
            
            // If all valid, submit form
            document.getElementById('signup-form').submit();
        }
    </script>
</body>
</html>
