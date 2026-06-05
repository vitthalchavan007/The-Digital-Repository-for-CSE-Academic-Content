// Faculty Dashboard Logic

let currentUser = null;
let facultyUploads = [];

document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    currentUser = requireAuth(['faculty', 'admin']);
    if (!currentUser) return;
    
    document.getElementById('faculty-name').textContent = currentUser.name;
    
    setupUploadForm();
    loadFacultyUploads();
});

function setupUploadForm() {
    const yearSelect = document.getElementById('up-year');
    const semesterSelect = document.getElementById('up-semester');
    const subjectSelect = document.getElementById('up-subject');
    const typeSelect = document.getElementById('up-type');
    
    yearSelect.addEventListener('change', function() {
        const year = this.value;
        semesterSelect.innerHTML = '<option value="" disabled selected>Select Semester</option>';
        
        if (year && yearSemesterMap[year]) {
            yearSemesterMap[year].forEach(sem => {
                const option = document.createElement('option');
                option.value = sem;
                option.textContent = `Semester ${sem}`;
                semesterSelect.appendChild(option);
            });
        }
    });
    
    semesterSelect.addEventListener('change', function() {
        const branch = document.getElementById('up-branch').value;
        const semester = this.value;
        
        subjectSelect.innerHTML = '<option value="" disabled selected>Select Subject</option>';
        
        if (branch && semester && branchSubjects[branch] && branchSubjects[branch][semester]) {
            branchSubjects[branch][semester].forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.name;
                option.textContent = `${subject.code} - ${subject.name}`;
                option.setAttribute('data-code', subject.code);
                subjectSelect.appendChild(option);
            });
        }
    });
    
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        document.getElementById('url-field').style.display = type === 'video' ? 'block' : 'none';
        document.getElementById('file-field').style.display = type === 'video' ? 'none' : 'block';
    });
    
    document.getElementById('up-file').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Select File';
        document.querySelector('#file-field .file-input-label span').textContent = fileName;
    });
    
    document.getElementById('uploadForm').addEventListener('submit', handleUpload);
}

function handleUpload(e) {
    e.preventDefault();
    
    const type = document.getElementById('up-type').value;
    const branch = document.getElementById('up-branch').value;
    const year = document.getElementById('up-year').value;
    const semester = document.getElementById('up-semester').value;
    const subject = document.getElementById('up-subject').value;
    const subjectOption = document.getElementById('up-subject').selectedOptions[0];
    const subjectCode = subjectOption ? subjectOption.getAttribute('data-code') : '';
    
    if (!branch || !year || !semester || !subject) {
        alert('Please fill all academic details.');
        return;
    }
    
    const newItem = {
        id: Date.now(),
        title: document.getElementById('up-title').value,
        subject: subject,
        type: type,
        url: document.getElementById('up-url').value,
        date: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        college: "GNIT",
        author: currentUser.name,
        description: document.getElementById('up-description').value || '',
        branch: branch,
        year: year,
        semester: semester,
        subjectCode: subjectCode
    };
    
    // Add to appropriate database
    if (type === 'syllabus') {
        syllabusData.unshift(newItem);
    } else if (type === 'pyq') {
        pyqData.unshift(newItem);
    } else {
        db.unshift(newItem);
    }
    
    // Close modal and reset
    bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
    document.getElementById('uploadForm').reset();
    
    alert(`Content uploaded successfully for ${branch} Year ${year} Sem ${semester}!`);
    
    // Refresh uploads list
    loadFacultyUploads();
}

function loadFacultyUploads() {
    const allUploads = [
        ...db.filter(item => item.author === currentUser.name),
        ...syllabusData.filter(item => item.author === currentUser.name),
        ...pyqData.filter(item => item.author === currentUser.name)
    ];
    
    facultyUploads = allUploads;
    document.getElementById('total-uploads').textContent = allUploads.length;
    
    const recentContainer = document.getElementById('recent-uploads');
    
    if (allUploads.length === 0) {
        recentContainer.innerHTML = '<p class="text-center text-white-50 py-4">No uploads yet. Click "Upload Content" to get started.</p>';
        return;
    }
    
    const recent = allUploads.slice(0, 5);
    
    let html = '<table class="table table-dark table-hover">';
    html += '<thead><tr><th>Title</th><th>Type</th><th>Branch</th><th>Year/Sem</th><th>Date</th></tr></thead><tbody>';
    
    recent.forEach(item => {
        html += `<tr>
            <td>${item.title}</td>
            <td><span class="badge bg-primary">${item.type}</span></td>
            <td>${item.branch}</td>
            <td>Y${item.year} S${item.semester}</td>
            <td>${item.date}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    recentContainer.innerHTML = html;
}

function viewMyUploads() {
    const allUploads = facultyUploads;
    
    if (allUploads.length === 0) {
        alert('You haven\'t uploaded any content yet.');
        return;
    }
    
    const modalHTML = `
        <div class="modal fade" id="uploadsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content custom-modal">
                    <div class="modal-header border-0">
                        <h5 class="fw-bold">My Uploads (${allUploads.length})</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Branch</th>
                                        <th>Year/Sem</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${allUploads.map(item => `
                                        <tr>
                                            <td>${item.title}</td>
                                            <td><span class="badge bg-primary">${item.type}</span></td>
                                            <td>${item.branch}</td>
                                            <td>Y${item.year} S${item.semester}</td>
                                            <td>${item.subject}</td>
                                            <td>${item.date}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('uploadsModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    new bootstrap.Modal(document.getElementById('uploadsModal')).show();
}

function viewStats() {
    const notes = facultyUploads.filter(item => item.type === 'note').length;
    const videos = facultyUploads.filter(item => item.type === 'video').length;
    const assignments = facultyUploads.filter(item => item.type === 'assignment').length;
    const syllabus = facultyUploads.filter(item => item.type === 'syllabus').length;
    const pyqs = facultyUploads.filter(item => item.type === 'pyq').length;
    
    const branchStats = {};
    facultyUploads.forEach(item => {
        if (item.branch) {
            branchStats[item.branch] = (branchStats[item.branch] || 0) + 1;
        }
    });
    
    alert(`Upload Statistics:
    
Total Uploads: ${facultyUploads.length}
• Notes: ${notes}
• Videos: ${videos}
• Assignments: ${assignments}
• Syllabus: ${syllabus}
• PYQs: ${pyqs}

By Branch:
${Object.entries(branchStats).map(([b, c]) => `• ${b}: ${c}`).join('\n')}`);
}
function setupUploadForm() {
    const yearSelect = document.getElementById('up-year');
    const semesterSelect = document.getElementById('up-semester');
    const subjectSelect = document.getElementById('up-subject');
    const typeSelect = document.getElementById('up-type');
    const fileInput = document.getElementById('up-file');
    const fileLabel = document.getElementById('file-label');
    const fileLabelText = document.getElementById('file-label-text');
    
    // Year change handler
    yearSelect.addEventListener('change', function() {
        const year = this.value;
        semesterSelect.innerHTML = '<option value="" disabled selected>Select Semester</option>';
        
        if (year && yearSemesterMap[year]) {
            yearSemesterMap[year].forEach(sem => {
                const option = document.createElement('option');
                option.value = sem;
                option.textContent = `Semester ${sem}`;
                semesterSelect.appendChild(option);
            });
        }
    });
    
    // Semester change handler
    semesterSelect.addEventListener('change', function() {
        const branch = document.getElementById('up-branch').value;
        const semester = this.value;
        
        subjectSelect.innerHTML = '<option value="" disabled selected>Select Subject</option>';
        
        if (branch && semester && branchSubjects[branch] && branchSubjects[branch][semester]) {
            branchSubjects[branch][semester].forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.name;
                option.textContent = `${subject.code} - ${subject.name}`;
                option.setAttribute('data-code', subject.code);
                subjectSelect.appendChild(option);
            });
        }
    });
    
    // Type change handler
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        const urlField = document.getElementById('url-field');
        const fileField = document.getElementById('file-field');
        
        if (type === 'video') {
            urlField.style.display = 'block';
            fileField.style.display = 'none';
            fileInput.removeAttribute('required');
        } else {
            urlField.style.display = 'none';
            fileField.style.display = 'block';
            fileInput.setAttribute('required', 'required');
        }
    });
    
    // File input change handler - FIXED
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file (PDF, DOC, PPT)';
            fileLabelText.textContent = fileName;
            
            if (e.target.files[0]) {
                fileLabel.classList.add('has-file');
                
                // Check file size (50MB limit)
                const fileSize = e.target.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 50) {
                    alert('File size exceeds 50MB limit. Please choose a smaller file.');
                    fileInput.value = '';
                    fileLabelText.textContent = 'Choose file (PDF, DOC, PPT)';
                    fileLabel.classList.remove('has-file');
                }
            } else {
                fileLabel.classList.remove('has-file');
            }
        });
    }
    
    // Drag and drop functionality
    if (fileLabel) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileLabel.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileLabel.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileLabel.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            fileLabel.classList.add('dragover');
        }
        
        function unhighlight() {
            fileLabel.classList.remove('dragover');
        }
        
        fileLabel.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                const fileName = files[0].name;
                fileLabelText.textContent = fileName;
                fileLabel.classList.add('has-file');
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        }
    }
    
    // Form submit handler
    document.getElementById('uploadForm').addEventListener('submit', handleUpload);
}