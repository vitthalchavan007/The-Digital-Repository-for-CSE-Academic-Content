// All branch subjects data
const branchSubjects = {
    'CSE': {
        '1': [
            { code: 'CS101', name: 'Engineering Mathematics-I' },
            { code: 'CS102', name: 'Engineering Physics' },
            { code: 'CS103', name: 'Programming in C' }
        ],
        '2': [
            { code: 'CS201', name: 'Engineering Mathematics-II' },
            { code: 'CS202', name: 'Digital Electronics' },
            { code: 'CS203', name: 'Data Structures' }
        ],
        '3': [
            { code: 'CS301', name: 'Applied Mathematics-3' },
            { code: 'CS302', name: 'Object Oriented Programming With Java' },
            { code: 'CS303', name: 'Operating System' },
            { code: 'CS304', name: 'Computer Architecture & Digital System' }
        ],
        '4': [
            { code: 'CS401', name: 'Discrete Mathematics & Graph Theory' },
            { code: 'CS402', name: 'Data Structure & Program Design' },
            { code: 'CS403', name: 'Theory of Computation' },
            { code: 'CS404', name: 'Computer Networks' }
        ],
        '5': [
            { code: 'CS501', name: 'Artificial Intelligence' },
            { code: 'CS502', name: 'Design & Analysis of Algorithm' },
            { code: 'CS503', name: 'Software Engineering & Project Management' }
        ],
        '6': [
            { code: 'CS601', name: 'Compiler Design' },
            { code: 'CS602', name: 'Internet of Things' }
        ],
        '7': [
            { code: 'CS701', name: 'Cryptography & Network Security' },
            { code: 'CS702', name: 'Optimization Techniques' }
        ],
        '8': [
            { code: 'CS801', name: 'Project Work' }
        ]
    },
    'IT': {
        '1': [
            { code: 'IT101', name: 'Engineering Mathematics-I' },
            { code: 'IT102', name: 'Programming Fundamentals' }
        ],
        '2': [
            { code: 'IT201', name: 'Data Structures & Algorithms' },
            { code: 'IT202', name: 'Computer Networks' }
        ],
        '3': [
            { code: 'IT301', name: 'Applied Mathematics-3' },
            { code: 'IT302', name: 'Programming Logic & Design using C' }
        ],
        '4': [
            { code: 'IT401', name: 'Discrete Mathematics & Graph Theory' },
            { code: 'IT402', name: 'Data Structure & Program Design' }
        ],
        '5': [
            { code: 'IT501', name: 'Software Engineering & Project Management' },
            { code: 'IT502', name: 'Design and Analysis of Algorithm' }
        ],
        '6': [
            { code: 'IT601', name: 'Database Management System' },
            { code: 'IT602', name: 'Artificial Intelligence & Machine Learning' }
        ],
        '7': [
            { code: 'IT701', name: 'Data Warehousing & Mining' },
            { code: 'IT702', name: 'Cryptography & Network Security' }
        ],
        '8': [
            { code: 'IT801', name: 'Program Elective-6' }
        ]
    },
    'AIDS': {
        '1': [
            { code: 'AIDS101', name: 'Engineering Mathematics-I' },
            { code: 'AIDS102', name: 'Programming Fundamentals' }
        ],
        '2': [
            { code: 'AIDS201', name: 'Engineering Mathematics-II' },
            { code: 'AIDS202', name: 'Basic Electronics' }
        ],
        '3': [
            { code: 'AIDS301', name: 'Discrete Mathematics and Graph Theory' },
            { code: 'AIDS302', name: 'Operating System' }
        ],
        '4': [
            { code: 'AIDS401', name: 'Introduction to AI' },
            { code: 'AIDS402', name: 'Theory of Computation' }
        ],
        '5': [
            { code: 'AIDS501', name: 'Data Mining' },
            { code: 'AIDS502', name: 'Machine Learning Techniques' }
        ],
        '6': [
            { code: 'AIDS601', name: 'Computer Communication Network' },
            { code: 'AIDS602', name: 'Deep Learning' }
        ],
        '7': [
            { code: 'AIDS701', name: 'Modern Computer Design' },
            { code: 'AIDS702', name: 'Digital Signal & Image Processing' }
        ],
        '8': [
            { code: 'AIDS801', name: 'Elective-4' }
        ]
    },
    'AIML': {
        '1': [
            { code: 'AIML101', name: 'Engineering Mathematics' },
            { code: 'AIML102', name: 'Programming for AI' }
        ],
        '2': [
            { code: 'AIML201', name: 'Probability & Statistics' },
            { code: 'AIML202', name: 'Data Structures & Algorithms' }
        ],
        '3': [
            { code: 'AIML301', name: 'Machine Learning' },
            { code: 'AIML302', name: 'Database Management' }
        ],
        '4': [
            { code: 'AIML401', name: 'Deep Learning' },
            { code: 'AIML402', name: 'Natural Language Processing' }
        ],
        '5': [
            { code: 'AIML501', name: 'Reinforcement Learning' },
            { code: 'AIML502', name: 'Advanced Computer Vision' }
        ],
        '6': [
            { code: 'AIML601', name: 'AI Ethics' },
            { code: 'AIML602', name: 'Robotics & AI' }
        ],
        '7': [
            { code: 'AIML701', name: 'Advanced Deep Learning' },
            { code: 'AIML702', name: 'AI in Healthcare' }
        ],
        '8': [
            { code: 'AIML801', name: 'Major Project' }
        ]
    },
    'CE': {
        '1': [
            { code: 'CE101', name: 'Engineering Mathematics-I' },
            { code: 'CE102', name: 'Engineering Mechanics' }
        ],
        '2': [
            { code: 'CE201', name: 'Engineering Mathematics-II' },
            { code: 'CE202', name: 'Building Materials' }
        ],
        '3': [
            { code: 'CE301', name: 'Applied Maths-3' },
            { code: 'CE302', name: 'Fluid Mechanics' }
        ],
        '4': [
            { code: 'CE401', name: 'Concrete Technology' },
            { code: 'CE402', name: 'Structural Analysis' }
        ],
        '5': [
            { code: 'CE501', name: 'Hydraulic Engineering' },
            { code: 'CE502', name: 'Reinforced Cement Concrete Designs' }
        ],
        '6': [
            { code: 'CE601', name: 'Advanced Structural Design' },
            { code: 'CE602', name: 'Construction Management' }
        ],
        '7': [
            { code: 'CE701', name: 'Advanced Civil Engineering-1' }
        ],
        '8': [
            { code: 'CE801', name: 'Major Project' }
        ]
    },
    'ME': {
        '1': [
            { code: 'ME101', name: 'Engineering Mathematics-I' },
            { code: 'ME102', name: 'Engineering Physics' }
        ],
        '2': [
            { code: 'ME201', name: 'Engineering Mathematics-II' },
            { code: 'ME202', name: 'Engineering Mechanics' }
        ],
        '3': [
            { code: 'ME301', name: 'Applied Mathematics-3' },
            { code: 'ME302', name: 'Manufacturing Process' }
        ],
        '4': [
            { code: 'ME401', name: 'Machining Process' },
            { code: 'ME402', name: 'Hydraulic Machines' }
        ],
        '5': [
            { code: 'ME501', name: 'Heat Transfer' },
            { code: 'ME502', name: 'Energy Conversion-1' }
        ],
        '6': [
            { code: 'ME601', name: 'Mechanical Engineering Elective-1' }
        ],
        '7': [
            { code: 'ME701', name: 'Advanced Mechanical Engineering-1' }
        ],
        '8': [
            { code: 'ME801', name: 'Industrial Engineering' }
        ]
    },
    'ECE': {
        '1': [
            { code: 'EC101', name: 'Engineering Mathematics-I' },
            { code: 'EC102', name: 'Engineering Physics' }
        ],
        '2': [
            { code: 'EC201', name: 'Engineering Mathematics-II' },
            { code: 'EC202', name: 'Digital Electronics' }
        ],
        '3': [
            { code: 'EC301', name: 'Applied Mathematics-3' },
            { code: 'EC302', name: 'Electronic Devices & Circuits' }
        ],
        '4': [
            { code: 'EC401', name: 'Digital Signal Processing' },
            { code: 'EC402', name: 'Microprocessors & Microcontrollers' }
        ],
        '5': [
            { code: 'EC501', name: 'VLSI Design' },
            { code: 'EC502', name: 'Digital Communication' }
        ],
        '6': [
            { code: 'EC601', name: 'Wireless Communication' },
            { code: 'EC602', name: 'Optical Communication' }
        ],
        '7': [
            { code: 'EC701', name: 'Advanced Communication Systems' }
        ],
        '8': [
            { code: 'EC801', name: 'Project Work' }
        ]
    }
};

// Sample resources data
let db = [
    { id: 1, title: "Array & Linked Lists", subject: "Data Structures", type: "note", date: "Oct 20", college: "GNIT", author: "Prof. Rao", branch: "CSE", year: "2", semester: "3", subjectCode: "CS203" },
    { id: 2, title: "Normalization Forms", subject: "DBMS", type: "video", url: "https://www.youtube.com/embed/UrYXOV_TDjo", date: "Oct 22", college: "GNIT", author: "Dr. Singh", branch: "CSE", year: "3", semester: "5", subjectCode: "CS405" },
    { id: 3, title: "React Hooks Assignment", subject: "Web Dev", type: "assignment", date: "Oct 25", college: "GNIT", author: "Prof. Lee", branch: "CSE", year: "3", semester: "5", subjectCode: "CS504" },
    { id: 4, title: "Neural Networks Intro", subject: "AI", type: "video", url: "https://www.youtube.com/embed/aircAruvnKk", date: "Oct 26", college: "GNIT", author: "Dr. AI", branch: "CSE", year: "3", semester: "5", subjectCode: "CS501" },
    { id: 5, title: "Cyber Threats 2024", subject: "Cyber Security", type: "note", date: "Oct 28", college: "GNIT", author: "Prof. Hack", branch: "CSE", year: "4", semester: "7", subjectCode: "CS701" },
    { id: 9, title: "Laplace Transforms", subject: "Applied Mathematics-3", type: "note", date: "Nov 10", college: "GNIT", author: "Prof. Math", branch: "CSE", year: "3", semester: "3", subjectCode: "CS301" },
    { id: 10, title: "Java OOP Tutorial", subject: "Object Oriented Programming With Java", type: "video", url: "https://www.youtube.com/embed/xk4_1vDrzzo", date: "Nov 12", college: "GNIT", author: "Prof. Java", branch: "CSE", year: "3", semester: "3", subjectCode: "CS302" },
    { id: 11, title: "Operating System Fundamentals", subject: "Operating System", type: "note", date: "Nov 15", college: "GNIT", author: "Prof. OS", branch: "CSE", year: "3", semester: "3", subjectCode: "CS303" },
    { id: 21, title: "Mathematics Fundations for Data Science", subject: "Mathematics Fundations for Data Science", type: "note", date: "Dec 10", college: "GNIT", author: "Prof. Math", branch: "CSEDS", year: "3", semester: "3", subjectCode: "CSEDS301" },
    { id: 22, title: "Object Oriented Programming Tutorial", subject: "Object Oriented Programming", type: "video", url: "https://www.youtube.com/embed/example4", date: "Dec 12", college: "GNIT", author: "Prof. OOP", branch: "CSEDS", year: "3", semester: "3", subjectCode: "CSEDS302" },
    { id: 27, title: "Applied Mathematics-3: Advanced Calculus", subject: "Applied Mathematics-3", type: "note", date: "Jan 5", college: "GNIT", author: "Prof. Sharma", branch: "IT", year: "2", semester: "3", subjectCode: "IT301" },
    { id: 28, title: "C Programming Complete Guide", subject: "Programming Logic & Design using C", type: "note", date: "Jan 8", college: "GNIT", author: "Prof. Kumar", branch: "IT", year: "2", semester: "3", subjectCode: "IT302" },
    { id: 33, title: "Discrete Mathematics Complete Notes", subject: "Discrete Mathematics & Graph Theory", type: "note", date: "Feb 1", college: "GNIT", author: "Prof. Discrete", branch: "IT", year: "2", semester: "4", subjectCode: "IT401" },
    { id: 34, title: "Data Structures Algorithms Tutorial", subject: "Data Structure & Program Design", type: "video", url: "https://www.youtube.com/embed/example8", date: "Feb 3", college: "GNIT", author: "Prof. Algo", branch: "IT", year: "2", semester: "4", subjectCode: "IT402" },
    { id: 41, title: "Discrete Mathematics Complete Notes", subject: "Discrete Mathematics and Graph Theory", type: "note", date: "Mar 1", college: "GNIT", author: "Prof. Math", branch: "AIDS", year: "2", semester: "3", subjectCode: "AIDS301" },
    { id: 42, title: "OS Process Management Tutorial", subject: "Operating System", type: "video", url: "https://www.youtube.com/embed/example12", date: "Mar 3", college: "GNIT", author: "Prof. OS", branch: "AIDS", year: "2", semester: "3", subjectCode: "AIDS302" },
    { id: 43, title: "Computer Architecture Complete Guide", subject: "Computer Architecture & Organization", type: "note", date: "Mar 5", college: "GNIT", author: "Prof. Architecture", branch: "AIDS", year: "2", semester: "3", subjectCode: "AIDS303" },
    { id: 44, title: "Data Structures Algorithms", subject: "Data Structures", type: "video", url: "https://www.youtube.com/embed/example13", date: "Mar 8", college: "GNIT", author: "Prof. DS", branch: "AIDS", year: "2", semester: "3", subjectCode: "AIDS304" }
];

// Syllabus data
let syllabusData = [
    { id: 101, title: "CSE 3rd Year Syllabus 2024", subject: "Computer Science", type: "syllabus", date: "Updated: Oct 2024", college: "GNIT", pdf: "#", semesters: ["Sem 5", "Sem 6"], branch: "CSE", year: "3" },
    { id: 102, title: "DBMS Complete Syllabus", subject: "DBMS", type: "syllabus", date: "Updated: Sep 2024", college: "GNIT", pdf: "#", semesters: ["Sem 5"], branch: "CSE", year: "3" },
    { id: 103, title: "AI & ML Syllabus", subject: "AI", type: "syllabus", date: "Updated: Nov 2024", college: "GNIT", pdf: "#", semesters: ["Sem 7", "Sem 8"], branch: "CSE", year: "4" }
];

// PYQ data
let pyqData = [
    { id: 201, title: "2023 CSE Semester Papers", subject: "Computer Science", type: "pyq", date: "Dec 2023", college: "GNIT", year: "2023", semesters: ["Sem 5", "Sem 6"], solutions: true, branch: "CSE", academicYear: "3" },
    { id: 202, title: "DBMS Previous Year Questions", subject: "DBMS", type: "pyq", date: "May 2023", college: "GNIT", year: "2023", semesters: ["Sem 5"], solutions: true, branch: "CSE", academicYear: "3" }
];

// Result data
let resultData = [
    { id: 301, title: "Semester 5 Results 2024", subject: "Computer Science", type: "result", date: "Dec 2024", college: "GNIT", semester: "5", grade: "A+", cgpa: "9.2", branch: "CSE", year: "3" },
    { id: 302, title: "Mid Term Results - AI", subject: "Artificial Intelligence", type: "result", date: "Nov 2024", college: "GNIT", semester: "5", grade: "A", cgpa: "8.5", branch: "CSE", year: "3" }
];

// Attendance data
let attendanceData = [
    { id: 401, title: "Attendance Summary - Sem 5", subject: "Computer Science", type: "attendance", date: "Current", college: "GNIT", semester: "5", percentage: "92%", status: "Good", branch: "CSE", year: "3" },
    { id: 402, title: "AI Attendance Record", subject: "Artificial Intelligence", type: "attendance", date: "Nov 2024", college: "GNIT", semester: "5", percentage: "88%", status: "Satisfactory", branch: "CSE", year: "3" }
];

// Year to Semester mapping
const yearSemesterMap = {
    '1': ['1', '2'],
    '2': ['3', '4'],
    '3': ['5', '6'],
    '4': ['7', '8']
};