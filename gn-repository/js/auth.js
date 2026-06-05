// Import data (in actual implementation, you might use modules)
// For now, we'll assume data.js is loaded before this file

// Handle student login
document.addEventListener('DOMContentLoaded', function() {
    const studentLoginForm = document.getElementById('student-login-form');
    if (studentLoginForm) {
        studentLoginForm.addEventListener('submit', handleStudentLogin);
    }
    
    const facultyLoginForm = document.getElementById('faculty-login-form');
    if (facultyLoginForm) {
        facultyLoginForm.addEventListener('submit', handleFacultyLogin);
    }
    
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegistration);
    }
    
    // Setup semester options when page loads
    setupSemesterOptions();
    
    // Also set up when modal is opened (in case DOM elements weren't ready)
    const registerModal = document.getElementById('registerModal');
    if (registerModal) {
        registerModal.addEventListener('shown.bs.modal', function() {
            setupSemesterOptions();
        });
    }
});

function handleStudentLogin(e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    // Demo authentication - in production, this would be a server request
    if (email && password) {
        // Create student user object
        const user = {
            name: "Rahul Sharma",
            role: "student",
            college: "GNIT",
            details: "CSE • 3rd Year • Sem 5",
            branch: "CSE",
            year: "3",
            semester: "5",
            studentId: "GNIT2023001",
            email: email
        };
        
        // Store user in session storage
        sessionStorage.setItem('currentUser', JSON.stringify(user));
        
        // Redirect to student dashboard
        window.location.href = 'student-dashboard.html';
    } else {
        alert('Please enter email and password');
    }
}

function handleFacultyLogin(e) {
    e.preventDefault();
    const email = document.getElementById('faculty-email').value;
    const password = document.getElementById('faculty-password').value;
    
    // Demo authentication
    if (email && password) {
        let user;
        
        // Check if this is a demo admin login
        if (email.includes('admin')) {
            user = {
                name: "Admin User",
                role: "admin",
                college: "GNIT",
                details: "System Administrator",
                email: email
            };
        } 
        // Check if this is a demo faculty login
        else if (email === 'faculty@gnit.ac.in' && password === 'password') {
            user = {
                name: "Dr. Faculty",
                role: "faculty",
                college: "GNIT",
                details: "Faculty Member",
                email: email,
                department: "CSE",
                subject: "Data Structures"
            };
        }
        // Try to get from localStorage (for registered faculty)
        else {
            const storedFaculty = localStorage.getItem(`faculty_${email}`);
            if (storedFaculty) {
                const facultyData = JSON.parse(storedFaculty);
                if (facultyData.password === password) {
                    user = {
                        name: facultyData.name,
                        role: "faculty",
                        college: "GNIT",
                        details: facultyData.details,
                        email: email,
                        department: facultyData.department,
                        subject: facultyData.subject,
                        facultyId: facultyData.facultyId
                    };
                }
            }
        }
        
        if (user) {
            sessionStorage.setItem('currentUser', JSON.stringify(user));
            window.location.href = 'faculty-dashboard.html';
        } else {
            alert('Invalid email or password');
        }
    } else {
        alert('Please enter email and password');
    }
}

function handleRegistration(e) {
    e.preventDefault();
    
    const fname = document.getElementById('reg-fname').value;
    const lname = document.getElementById('reg-lname').value;
    const studentId = document.getElementById('reg-student-id').value;
    const email = document.getElementById('reg-email').value;
    const branch = document.getElementById('reg-branch').value;
    const year = document.getElementById('reg-year').value;
    const semester = document.getElementById('reg-semester').value;
    const password = document.getElementById('reg-password').value;
    
    // Validate all fields
    if (!fname || !lname || !studentId || !email || !branch || !year || !semester || !password) {
        alert('Please fill in all fields!');
        return;
    }
    
    // Create student user
    const user = {
        name: `${fname} ${lname}`,
        role: "student",
        studentId: studentId,
        email: email,
        branch: branch,
        year: year,
        semester: semester,
        college: "GNIT",
        details: `${branch} • Year ${year} • Sem ${semester}`,
        password: password // In production, this should be hashed
    };
    
    // Store in localStorage (in production, send to server)
    localStorage.setItem(`student_${email}`, JSON.stringify(user));
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
    if (modal) {
        modal.hide();
    }
    
    alert('Registration successful! Please login with your credentials.');
    
    // Reset form
    document.getElementById('register-form').reset();
}

function setupSemesterOptions() {
    const yearSelect = document.getElementById('reg-year');
    const semesterSelect = document.getElementById('reg-semester');
    
    if (yearSelect && semesterSelect) {
        // Remove existing event listener to avoid duplicates
        yearSelect.removeEventListener('change', updateSemesterOptions);
        yearSelect.addEventListener('change', updateSemesterOptions);
        
        // Trigger change event if year is already selected
        if (yearSelect.value) {
            // Create a synthetic event
            const event = new Event('change');
            yearSelect.dispatchEvent(event);
        }
    }
}

function updateSemesterOptions() {
    const year = this.value;
    const semesterSelect = document.getElementById('reg-semester');
    
    if (!semesterSelect) return;
    
    // Clear existing options
    semesterSelect.innerHTML = '<option value="" disabled selected>Select Semester</option>';
    
    // Check if yearSemesterMap exists (from data.js)
    if (typeof yearSemesterMap !== 'undefined' && year && yearSemesterMap[year]) {
        const semesters = yearSemesterMap[year];
        semesters.forEach(sem => {
            const option = document.createElement('option');
            option.value = sem;
            option.textContent = `Semester ${sem}`;
            semesterSelect.appendChild(option);
        });
    } else {
        console.log('yearSemesterMap not found or year invalid');
        // Fallback semester options if map doesn't exist
        if (year === '1') {
            addSemesterOptions(semesterSelect, ['1', '2']);
        } else if (year === '2') {
            addSemesterOptions(semesterSelect, ['3', '4']);
        } else if (year === '3') {
            addSemesterOptions(semesterSelect, ['5', '6']);
        } else if (year === '4') {
            addSemesterOptions(semesterSelect, ['7', '8']);
        }
    }
}

function addSemesterOptions(selectElement, semesters) {
    semesters.forEach(sem => {
        const option = document.createElement('option');
        option.value = sem;
        option.textContent = `Semester ${sem}`;
        selectElement.appendChild(option);
    });
}

// Check if user is logged in and redirect if not
function requireAuth(allowedRoles = ['student', 'faculty', 'admin']) {
    const userJson = sessionStorage.getItem('currentUser');
    
    if (!userJson) {
        window.location.href = 'index.html';
        return null;
    }
    
    const user = JSON.parse(userJson);
    
    if (!allowedRoles.includes(user.role)) {
        // Redirect to appropriate dashboard based on role
        if (user.role === 'student') {
            window.location.href = 'student-dashboard.html';
        } else if (user.role === 'faculty') {
            window.location.href = 'faculty-dashboard.html';
        } else if (user.role === 'admin') {
            window.location.href = 'admin-dashboard.html';
        } else {
            window.location.href = 'index.html';
        }
        return null;
    }
    
    return user;
}

function logout() {
    sessionStorage.removeItem('currentUser');
    window.location.href = 'index.html';
}