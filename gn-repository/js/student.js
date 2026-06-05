// Student Dashboard Logic

let currentUser = null;
let currentFilter = 'all';
let currentView = 'overview';
let userNotes = [];

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    currentUser = requireAuth(['student']);
    if (!currentUser) return;
    
    // Initialize AOS
    AOS.init({ duration: 800, once: true });
    
    // Update UI with user info
    updateUserInfo();
    
    // Setup subject filter based on student's semester
    updateSubjectFilter();
    
    // Show overview by default
    showOverview();
    
    // Load user notes from localStorage
    loadSavedNotes();
});

function updateUserInfo() {
    document.getElementById('nav-user-name').textContent = currentUser.name;
    document.getElementById('dash-name').textContent = currentUser.name.split(' ')[0];
    document.getElementById('dash-details').textContent = currentUser.details;
    
    const semesterInfo = document.getElementById('semesterFilterText');
    semesterInfo.innerHTML = `You are viewing resources for <span class="text-primary">${currentUser.branch}</span> • Year ${currentUser.year} • Semester ${currentUser.semester}`;
}

function updateSubjectFilter() {
    const subjectFilter = document.getElementById('subjectFilter');
    subjectFilter.innerHTML = '<option value="all">All Subjects</option>';
    
    if (currentUser && branchSubjects[currentUser.branch] && branchSubjects[currentUser.branch][currentUser.semester]) {
        const subjects = branchSubjects[currentUser.branch][currentUser.semester];
        subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject.name;
            option.textContent = subject.name;
            subjectFilter.appendChild(option);
        });
    }
}

function getFilteredResources() {
    let filtered = [];
    const allResources = [...db, ...syllabusData, ...pyqData, ...resultData, ...attendanceData];
    
    // Filter by student's branch, year, and semester
    filtered = allResources.filter(item => {
        const matchesBranch = !item.branch || item.branch === currentUser.branch;
        const matchesYear = !item.year || item.year === currentUser.year || item.academicYear === currentUser.year;
        const matchesSemester = !item.semester || item.semester === currentUser.semester;
        const matchesSemesterArray = !item.semesters || item.semesters.includes(`Sem ${currentUser.semester}`);
        
        return matchesBranch && matchesYear && (matchesSemester || matchesSemesterArray);
    });
    
    return filtered;
}

function showOverview(el) {
    if (el) updateActiveNav(el);
    currentView = 'overview';
    currentFilter = 'all';
    renderGrid();
}

function filterResources(type, el) {
    if (el) updateActiveNav(el);
    currentView = 'resources';
    currentFilter = type;
    renderGrid();
}

function showSyllabusSection(el) {
    if (el) updateActiveNav(el);
    currentView = 'syllabus';
    renderSyllabusContent();
}

function showPYQSection(el) {
    if (el) updateActiveNav(el);
    currentView = 'pyq';
    renderPYQContent();
}

function showResultSection(el) {
    if (el) updateActiveNav(el);
    currentView = 'result';
    renderResultContent();
}

function showAttendanceSection(el) {
    if (el) updateActiveNav(el);
    currentView = 'attendance';
    renderAttendanceContent();
}

function updateActiveNav(el) {
    document.querySelectorAll('.sidebar-link').forEach(link => link.classList.remove('active'));
    el.classList.add('active');
}

function handleSearch() {
    if (currentView === 'syllabus') {
        renderSyllabusContent();
    } else if (currentView === 'pyq') {
        renderPYQContent();
    } else if (currentView === 'result') {
        renderResultContent();
    } else if (currentView === 'attendance') {
        renderAttendanceContent();
    } else {
        renderGrid();
    }
}

function handleSubjectFilter() {
    handleSearch();
}

function renderGrid() {
    const grid = document.getElementById('resourceGrid');
    const subject = document.getElementById('subjectFilter').value;
    const search = document.getElementById('searchBar').value.toLowerCase();
    
    grid.innerHTML = "";

    let allResources = getFilteredResources();
    
    if (currentFilter !== 'all') {
        allResources = allResources.filter(item => item.type === currentFilter);
    }
    
    const filtered = allResources.filter(item => {
        const matchSub = subject === 'all' || item.subject === subject;
        const matchSearch = item.title.toLowerCase().includes(search) || 
                           item.subject.toLowerCase().includes(search);
        return matchSub && matchSearch;
    });

    if (filtered.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center text-white-50 mt-5">
            <h4><i class="fas fa-ghost mb-3"></i><br>No resources found for your semester.</h4>
        </div>`;
        return;
    }

    filtered.forEach((item, index) => {
        let coverClass = 'cover-pdf';
        let icon = 'fa-file-pdf';
        let btnText = 'Download';
        let action = `alert('Downloading...')`;
        
        if(item.type === 'video') { 
            coverClass = 'cover-video'; 
            icon = 'fa-play'; 
            btnText = 'Watch';
            action = `playVideo('${item.url}')`;
        } else if(item.type === 'assignment') { 
            coverClass = 'cover-assign'; 
            icon = 'fa-clipboard-list';
            btnText = 'View Task';
        } else if(item.type === 'syllabus') {
            coverClass = 'cover-syllabus';
            icon = 'fa-book';
            btnText = 'View Syllabus';
            action = `downloadSyllabus('${item.pdf}')`;
        } else if(item.type === 'pyq') {
            coverClass = 'cover-pyq';
            icon = 'fa-file-contract';
            btnText = 'View Questions';
            action = `viewPYQ(${item.id})`;
        } else if(item.type === 'result') {
            coverClass = 'cover-syllabus';
            icon = 'fa-chart-line';
            btnText = 'View Result';
            action = `viewResult(${item.id})`;
        } else if(item.type === 'attendance') {
            coverClass = 'cover-assign';
            icon = 'fa-calendar-check';
            btnText = 'View Attendance';
            action = `viewAttendance(${item.id})`;
        }

        const delay = index * 100;

        grid.innerHTML += `
        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="resource-card">
                <div class="card-cover ${coverClass}"></div>
                <div class="card-icon-float">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="p-4 pt-5 mt-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-25">${item.subject}</span>
                        <small class="text-white-50" style="font-size:0.7rem;">${item.date}</small>
                    </div>
                    <h5 class="fw-bold text-white mb-1 text-truncate">${item.title}</h5>
                    <small class="text-white-50 d-block mb-4">
                        By ${item.author || 'Faculty'}
                        ${item.grade ? `<br>Grade: ${item.grade} | CGPA: ${item.cgpa}` : ''}
                        ${item.percentage ? `<br>Attendance: ${item.percentage}` : ''}
                    </small>
                    
                    <div class="d-grid">
                        <button class="btn btn-sm btn-outline-glow" onclick="${action}">
                            ${btnText}
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
}

function renderSyllabusContent() {
    const grid = document.getElementById('resourceGrid');
    const search = document.getElementById('searchBar').value.toLowerCase();
    const subject = document.getElementById('subjectFilter').value;
    
    let allSyllabus = getFilteredResources().filter(item => item.type === 'syllabus');
    
    const filtered = allSyllabus.filter(item => {
        const matchSearch = item.title.toLowerCase().includes(search) || 
                           item.subject.toLowerCase().includes(search);
        const matchSubject = subject === 'all' || item.subject === subject;
        return matchSearch && matchSubject;
    });
    
    renderGenericCards(filtered, 'syllabus');
}

function renderPYQContent() {
    const grid = document.getElementById('resourceGrid');
    const search = document.getElementById('searchBar').value.toLowerCase();
    const subject = document.getElementById('subjectFilter').value;
    
    let allPYQ = getFilteredResources().filter(item => item.type === 'pyq');
    
    const filtered = allPYQ.filter(item => {
        const matchSearch = item.title.toLowerCase().includes(search) || 
                           item.subject.toLowerCase().includes(search);
        const matchSubject = subject === 'all' || item.subject === subject;
        return matchSearch && matchSubject;
    });
    
    renderGenericCards(filtered, 'pyq');
}

function renderResultContent() {
    const grid = document.getElementById('resourceGrid');
    const search = document.getElementById('searchBar').value.toLowerCase();
    const subject = document.getElementById('subjectFilter').value;
    
    let allResults = getFilteredResources().filter(item => item.type === 'result');
    
    const filtered = allResults.filter(item => {
        const matchSearch = item.title.toLowerCase().includes(search) || 
                           item.subject.toLowerCase().includes(search);
        const matchSubject = subject === 'all' || item.subject === subject;
        return matchSearch && matchSubject;
    });
    
    renderGenericCards(filtered, 'result');
}

function renderAttendanceContent() {
    const grid = document.getElementById('resourceGrid');
    const search = document.getElementById('searchBar').value.toLowerCase();
    const subject = document.getElementById('subjectFilter').value;
    
    let allAttendance = getFilteredResources().filter(item => item.type === 'attendance');
    
    const filtered = allAttendance.filter(item => {
        const matchSearch = item.title.toLowerCase().includes(search) || 
                           item.subject.toLowerCase().includes(search);
        const matchSubject = subject === 'all' || item.subject === subject;
        return matchSearch && matchSubject;
    });
    
    renderGenericCards(filtered, 'attendance');
}

function renderGenericCards(items, type) {
    const grid = document.getElementById('resourceGrid');
    grid.innerHTML = '';
    
    if (items.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center text-white-50 mt-5">
            <h4><i class="fas fa-ghost mb-3"></i><br>No ${type} found for your semester.</h4>
        </div>`;
        return;
    }
    
    items.forEach((item, index) => {
        let coverClass, icon, badge;
        
        switch(type) {
            case 'syllabus':
                coverClass = 'cover-syllabus';
                icon = 'fa-book';
                badge = '<span class="badge bg-primary">Syllabus</span>';
                break;
            case 'pyq':
                coverClass = 'cover-pyq';
                icon = 'fa-file-contract';
                badge = `<span class="badge ${item.solutions ? 'bg-success' : 'bg-warning'}">
                    ${item.solutions ? 'With Solutions' : 'Questions Only'}</span>`;
                break;
            case 'result':
                coverClass = 'cover-syllabus';
                icon = 'fa-chart-line';
                badge = `<span class="badge ${item.grade === 'A+' ? 'bg-warning' : 'bg-success'}">
                    ${item.grade}</span>`;
                break;
            case 'attendance':
                coverClass = 'cover-assign';
                icon = 'fa-calendar-check';
                badge = `<span class="badge ${item.percentage >= 90 ? 'bg-success' : 'bg-warning'}">
                    ${item.percentage}</span>`;
                break;
        }
        
        grid.innerHTML += `
        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="${index * 100}">
            <div class="resource-card">
                <div class="card-cover ${coverClass}"></div>
                <div class="card-icon-float">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="p-4 pt-5 mt-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-secondary">${item.subject}</span>
                        ${badge}
                    </div>
                    <h5 class="fw-bold text-white mb-2">${item.title}</h5>
                    <small class="text-white-50 d-block mb-3">
                        ${item.date}
                        ${item.grade ? `<br>CGPA: ${item.cgpa}` : ''}
                        ${item.percentage ? `<br>Status: ${item.status}` : ''}
                        ${item.semesters ? `<br>Semesters: ${item.semesters.join(', ')}` : ''}
                    </small>
                    
                    <div class="d-grid">
                        <button class="btn btn-sm btn-outline-glow" onclick="view${type.charAt(0).toUpperCase() + type.slice(1)}(${item.id})">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
}

// Helper functions
function playVideo(url) {
    document.getElementById('videoFrame').src = url;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
}

document.getElementById('videoModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('videoFrame').src = "";
});

function downloadSyllabus(url) {
    alert('Downloading syllabus...');
}

function viewPYQ(id) {
    const pyq = pyqData.find(item => item.id === id);
    if (pyq) {
        alert(`Viewing PYQ: ${pyq.title}\nYear: ${pyq.year}\nSemesters: ${pyq.semesters.join(', ')}`);
    }
}

function viewResult(id) {
    const result = resultData.find(item => item.id === id);
    if (result) {
        alert(`Result: ${result.title}\nGrade: ${result.grade}\nCGPA: ${result.cgpa}`);
    }
}

function viewAttendance(id) {
    const attendance = attendanceData.find(item => item.id === id);
    if (attendance) {
        alert(`Attendance: ${attendance.title}\nPercentage: ${attendance.percentage}\nStatus: ${attendance.status}`);
    }
}

// Notes functionality
function openNotesEditor() {
    new bootstrap.Modal(document.getElementById('notesModal')).show();
    loadNotes();
}

function saveNote() {
    const title = document.getElementById('note-title').value || 'Untitled Note';
    const content = document.getElementById('note-content').value;
    
    if (!content.trim()) {
        alert('Please enter some content for your note.');
        return;
    }
    
    const newNote = {
        id: Date.now(),
        title: title,
        content: content,
        date: new Date().toLocaleDateString()
    };
    
    userNotes.push(newNote);
    localStorage.setItem(`notes_${currentUser.studentId}`, JSON.stringify(userNotes));
    
    document.getElementById('note-title').value = '';
    document.getElementById('note-content').value = '';
    
    loadNotes();
    alert('Note saved successfully!');
}

function loadNotes() {
    const savedNotes = localStorage.getItem(`notes_${currentUser.studentId}`);
    userNotes = savedNotes ? JSON.parse(savedNotes) : [];
    
    const container = document.getElementById('saved-notes');
    container.innerHTML = '';
    
    if (userNotes.length === 0) {
        container.innerHTML = '<p class="text-center text-white-50">No saved notes yet.</p>';
        return;
    }
    
    userNotes.forEach((note, index) => {
        container.innerHTML += `
        <div class="enhanced-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="fw-bold">${note.title}</h6>
                <div>
                    <small class="text-white-50 me-2">${note.date}</small>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <p class="text-white-70 mt-2">${note.content.substring(0, 100)}${note.content.length > 100 ? '...' : ''}</p>
            <button class="btn btn-sm btn-outline-primary" onclick="editNote(${index})">
                <i class="fas fa-edit me-1"></i> Edit
            </button>
        </div>`;
    });
}

function loadSavedNotes() {
    const saved = localStorage.getItem(`notes_${currentUser.studentId}`);
    userNotes = saved ? JSON.parse(saved) : [];
}

function deleteNote(id) {
    if (confirm('Delete this note?')) {
        userNotes = userNotes.filter(note => note.id !== id);
        localStorage.setItem(`notes_${currentUser.studentId}`, JSON.stringify(userNotes));
        loadNotes();
    }
}

function editNote(index) {
    const note = userNotes[index];
    document.getElementById('note-title').value = note.title;
    document.getElementById('note-content').value = note.content;
    
    userNotes.splice(index, 1);
    localStorage.setItem(`notes_${currentUser.studentId}`, JSON.stringify(userNotes));
    loadNotes();
}