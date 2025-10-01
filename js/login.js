$(document).ready(function() {
    // Client-side version for static hosting (no PHP backend)
    const API_BASE_URL = './';
    
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
        
        // Client-side login using localStorage (for static hosting)
        setTimeout(() => {
            try {
                // Get users from localStorage
                const users = JSON.parse(localStorage.getItem('users') || '[]');
                
                // Find user by username or email
                const user = users.find(u => 
                    (u.username === formData.username || u.email === formData.username) &&
                    atob(u.password) === formData.password
                );
                
                if (user) {
                    // Generate a simple session token
                    const sessionToken = 'token_' + user.id + '_' + Date.now();
                    
                    // Store session data in localStorage
                    localStorage.setItem('session_token', sessionToken);
                    localStorage.setItem('user_data', JSON.stringify({
                        id: user.id,
                        username: user.username,
                        email: user.email
                    }));
                    
                    // Store session in localStorage sessions
                    let sessions = JSON.parse(localStorage.getItem('sessions') || '[]');
                    sessions.push({
                        token: sessionToken,
                        user_id: user.id,
                        created_at: new Date().toISOString()
                    });
                    localStorage.setItem('sessions', JSON.stringify(sessions));
                    
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect to profile page after 1 second
                    setTimeout(() => {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    showAlert('Invalid username or password.');
                }
            } catch (error) {
                showAlert('Login failed. Please try again.');
            }
            
            setLoadingState(false);
        }, 1000); // Simulate network delay
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