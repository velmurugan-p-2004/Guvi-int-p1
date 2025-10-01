$(document).ready(function() {
    const API_BASE_URL = 'php/';
    
    // Show alert function
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
    
    // Form validation
    function validateForm() {
        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (username.length < 3) {
            showAlert('Username must be at least 3 characters long');
            return false;
        }
        
        if (!isValidEmail(email)) {
            showAlert('Please enter a valid email address');
            return false;
        }
        
        if (password.length < 6) {
            showAlert('Password must be at least 6 characters long');
            return false;
        }
        
        if (password !== confirmPassword) {
            showAlert('Passwords do not match');
            return false;
        }
        
        return true;
    }
    
    // Email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Show loading state
    function setLoadingState(isLoading) {
        const $submitBtn = $('#registerBtn');
        const $spinner = $('#registerSpinner');
        
        if (isLoading) {
            $submitBtn.prop('disabled', true);
            $spinner.removeClass('d-none');
        } else {
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    }
    
    // Handle form submission
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const formData = {
            username: $('#username').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val()
        };
        
        setLoadingState(true);
        
        $.ajax({
            url: API_BASE_URL + 'register.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showAlert('Registration successful! You can now login.', 'success');
                    $('#registerForm')[0].reset();
                    
                    // Redirect to login page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    showAlert(response.message || 'Registration failed');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Registration failed. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                
                showAlert(errorMessage);
            },
            complete: function() {
                setLoadingState(false);
            }
        });
    });
    
    // Real-time validation feedback
    $('#username').on('input', function() {
        const username = $(this).val().trim();
        if (username.length > 0 && username.length < 3) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#email').on('input', function() {
        const email = $(this).val().trim();
        if (email.length > 0 && !isValidEmail(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#password').on('input', function() {
        const password = $(this).val();
        if (password.length > 0 && password.length < 6) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
        
        // Check confirm password match
        const confirmPassword = $('#confirmPassword').val();
        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                $('#confirmPassword').addClass('is-invalid');
            } else {
                $('#confirmPassword').removeClass('is-invalid');
            }
        }
    });
    
    $('#confirmPassword').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });
});