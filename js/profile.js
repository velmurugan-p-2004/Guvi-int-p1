$(document).ready(function() {
    // Client-side version for static hosting (no PHP backend)
    const API_BASE_URL = './';
    let currentUser = null;
    let sessionToken = null;
    let isEditMode = false;
    
    // Check authentication
    function checkAuth() {
        sessionToken = localStorage.getItem('session_token');
        const userData = localStorage.getItem('user_data');
        
        if (!sessionToken || !userData) {
            window.location.href = 'login.html';
            return false;
        }
        
        try {
            currentUser = JSON.parse(userData);
            $('#welcomeUser').text(`Welcome, ${currentUser.username}`);
            return true;
        } catch (e) {
            localStorage.clear();
            window.location.href = 'login.html';
            return false;
        }
    }
    
    // Initialize page
    if (!checkAuth()) {
        return;
    }
    
    // Show alert function
    function showAlert(message, type = 'info') {
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
    
    // Load user profile (client-side version)
    function loadProfile() {
        try {
            // Get profile from localStorage
            const profiles = JSON.parse(localStorage.getItem('profiles') || '[]');
            const userProfile = profiles.find(p => p.user_id === currentUser.id);
            
            if (userProfile) {
                populateProfileView(userProfile);
                populateProfileForm(userProfile);
            } else {
                // Profile doesn't exist yet - create default profile
                const defaultProfile = {
                    user_id: currentUser.id,
                    username: currentUser.username,
                    email: currentUser.email,
                    full_name: '',
                    bio: '',
                    location: '',
                    website: '',
                    updated_at: new Date().toISOString()
                };
                populateProfileView(defaultProfile);
                populateProfileForm(defaultProfile);
            }
        } catch (error) {
            showAlert('Failed to load profile data', 'danger');
        }
    }
    
    // Populate profile view
    function populateProfileView(profile) {
        $('#viewFirstName').text(profile.first_name || 'Not provided');
        $('#viewLastName').text(profile.last_name || 'Not provided');
        $('#viewAge').text(profile.age || 'Not provided');
        $('#viewDob').text(profile.date_of_birth || 'Not provided');
        $('#viewContact').text(profile.contact || 'Not provided');
        $('#viewAddress').text(profile.address || 'Not provided');
        $('#viewCity').text(profile.city || 'Not provided');
        $('#viewCountry').text(profile.country || 'Not provided');
        $('#viewBio').text(profile.bio || 'Not provided');
    }
    
    // Populate profile form
    function populateProfileForm(profile) {
        $('#firstName').val(profile.first_name || '');
        $('#lastName').val(profile.last_name || '');
        $('#age').val(profile.age || '');
        $('#dob').val(profile.date_of_birth || '');
        $('#contact').val(profile.contact || '');
        $('#address').val(profile.address || '');
        $('#city').val(profile.city || '');
        $('#country').val(profile.country || '');
        $('#bio').val(profile.bio || '');
    }
    
    // Toggle edit mode
    function toggleEditMode() {
        isEditMode = !isEditMode;
        
        if (isEditMode) {
            $('#profileView').addClass('d-none');
            $('#profileEdit').removeClass('d-none').addClass('fade-in');
            $('#editToggleBtn').text('Cancel Edit').removeClass('btn-outline-primary').addClass('btn-outline-secondary');
        } else {
            $('#profileEdit').addClass('d-none');
            $('#profileView').removeClass('d-none').addClass('fade-in');
            $('#editToggleBtn').text('Edit Profile').removeClass('btn-outline-secondary').addClass('btn-outline-primary');
        }
    }
    
    // Show loading state for save button
    function setSaveLoadingState(isLoading) {
        const $saveBtn = $('#saveBtn');
        const $spinner = $('#saveSpinner');
        
        if (isLoading) {
            $saveBtn.prop('disabled', true);
            $spinner.removeClass('d-none');
        } else {
            $saveBtn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    }
    
    // Save profile
    function saveProfile() {
        const formData = {
            first_name: $('#firstName').val().trim(),
            last_name: $('#lastName').val().trim(),
            age: parseInt($('#age').val()) || 0,
            date_of_birth: $('#dob').val(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim(),
            city: $('#city').val().trim(),
            country: $('#country').val().trim(),
            bio: $('#bio').val().trim()
        };
        
        setSaveLoadingState(true);
        
        // Client-side profile save using localStorage
        setTimeout(() => {
            try {
                // Get existing profiles
                let profiles = JSON.parse(localStorage.getItem('profiles') || '[]');
                
                // Find and update user's profile
                const profileIndex = profiles.findIndex(p => p.user_id === currentUser.id);
                
                const updatedProfile = {
                    user_id: currentUser.id,
                    username: currentUser.username,
                    email: currentUser.email,
                    full_name: (formData.first_name + ' ' + formData.last_name).trim(),
                    age: formData.age,
                    date_of_birth: formData.date_of_birth,
                    contact: formData.contact,
                    address: formData.address,
                    city: formData.city,
                    country: formData.country,
                    bio: formData.bio,
                    updated_at: new Date().toISOString()
                };
                
                if (profileIndex >= 0) {
                    profiles[profileIndex] = updatedProfile;
                } else {
                    profiles.push(updatedProfile);
                }
                
                localStorage.setItem('profiles', JSON.stringify(profiles));
                
                showAlert('Profile updated successfully!', 'success');
                populateProfileView(updatedProfile);
                toggleEditMode();
                
            } catch (error) {
                showAlert('Failed to update profile. Please try again.', 'danger');
            }
            
            setSaveLoadingState(false);
        }, 1000); // Simulate network delay
    }
    
    // Logout function (client-side version)
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Clear all localStorage data
            localStorage.removeItem('session_token');
            localStorage.removeItem('user_data');
            window.location.href = 'login.html';
        }
    }
    
    // Event handlers
    $('#editToggleBtn').on('click', function() {
        if (isEditMode) {
            loadProfile(); // Reload original data
        }
        toggleEditMode();
    });
    
    $('#cancelBtn').on('click', function() {
        loadProfile(); // Reload original data
        toggleEditMode();
    });
    
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        saveProfile();
    });
    
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        logout();
    });
    
    // Form validation
    $('#age').on('input', function() {
        const age = parseInt($(this).val());
        if (age && (age < 1 || age > 120)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#contact').on('input', function() {
        const contact = $(this).val().trim();
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (contact && !phoneRegex.test(contact.replace(/[\s\-\(\)]/g, ''))) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Load profile data on page load
    loadProfile();
    
    // Prevent form submission on Enter key in text inputs (except textarea)
    $('#profileForm input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
        }
    });
});