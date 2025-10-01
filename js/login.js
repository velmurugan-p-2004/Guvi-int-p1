$(document).ready(function() {
    const API_BASE_URL = 'php/';
    
    // Check if user is already logged in
    if (localStorage.getItem('session_token')) {
        window.location.href = 'profile.html';
        return;
    }
    
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
        const password = $('#password').val();
        
        if (!username) {
            showAlert('Please enter your username or email');
            return false;
        }
        
        if (!password) {
            showAlert('Please enter your password');
            return false;
        }
        
        return true;
    }
    
    // Show loading state
    function setLoadingState(isLoading) {
        const $submitBtn = $('#loginBtn');
        const $spinner = $('#loginSpinner');
        
        if (isLoading) {
            $submitBtn.prop('disabled', true);
            $spinner.removeClass('d-none');
        } else {
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    }
    
    // Handle form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const formData = {
            username: $('#username').val().trim(),
            password: $('#password').val()
        };
        
        setLoadingState(true);
        
        $.ajax({
            url: API_BASE_URL + 'login.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    // Store session data in localStorage
                    localStorage.setItem('session_token', response.session_token);
                    localStorage.setItem('user_data', JSON.stringify(response.user));
                    
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect to profile page after 1 second
                    setTimeout(() => {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    showAlert(response.message || 'Login failed');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Login failed. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                } else if (xhr.status === 401) {
                    errorMessage = 'Invalid username or password.';
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
    
    // Clear validation states on input
    $('#username, #password').on('input', function() {
        $(this).removeClass('is-invalid');
    });
    
    // Handle Enter key press
    $(document).on('keypress', function(e) {
        if (e.which === 13 && $('#loginForm').is(':visible')) {
            $('#loginForm').submit();
        }
    });
});