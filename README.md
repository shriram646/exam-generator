# 🎓 Auto GenExam — Automatic Exam Paper Generator

<div align="center">

![License](https://img.shields.io/badge/License-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/Server-XAMPP-FB7A24?logo=apache&logoColor=white)
![Status](https://img.shields.io/badge/Status-Active-brightgreen)

**An intelligent, automated exam paper generation platform built for modern educational institutions.**

*Dynamically blueprints, compiles, and exports print-ready question papers from a structured question bank — in seconds.*

</div>

---

## 📌 Table of Contents

- [About the Project](#-about-the-project)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Getting Started](#-getting-started)
- [Usage Walkthrough](#-usage-walkthrough)
- [Security](#-security)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🚀 About the Project

**Auto GenExam** is a web-based exam automation platform that solves one of the most time-consuming tasks in academia — creating balanced, fair, and varied examination papers. 

Faculty members simply configure subjects, populate a question bank with difficulty-graded questions, and let the **automated engine** pick and assemble the perfect paper to meet any total marks requirement. Papers can then be previewed and downloaded as professional PDFs.

> Built as a Hackathon project demonstrating AI-driven algorithmic automation in educational technology.

---

## ✨ Key Features

| Feature | Description |
|--------|-------------|
| 🔐 **Secure Faculty Auth** | OTP-based 2-factor login with bcrypt password hashing |
| 📚 **Subject Management** | Add subjects with multi-unit syllabus breakdowns |
| 🗂️ **Question Bank** | Difficulty-graded (Easy / Medium / Hard) question repository |
| 🤖 **Auto Generation Engine** | Algorithm auto-selects questions to hit exact total marks target |
| 📋 **Optional Question Support** | Set sections like *"Attempt Any 2 from 3"* with custom instructions |
| 📄 **PDF Export** | Instant browser-based PDF download via `html2pdf.js` |
| 🗃️ **Papers Archive** | View, reprint, and delete all previously generated papers |
| 👤 **Faculty Profile** | Upload profile photo, view personal info |
| 👥 **Admin Panel** | Manage all registered faculty user accounts |
| ✨ **Premium UI** | Glassmorphism design, 6-color ambient glow animations, responsive layout |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x (Native, no framework) |
| **Database** | MySQL 8.x via PDO prepared statements |
| **Frontend** | HTML5 + Vanilla CSS3 + JavaScript (ES6) |
| **PDF Engine** | [html2pdf.js](https://ekoopmans.github.io/html2pdf.js/) (CDN) |
| **Icons** | [Font Awesome 6.4](https://fontawesome.com/) |
| **Fonts** | Google Fonts — *Outfit* + *Plus Jakarta Sans* |
| **Server** | Apache (XAMPP) |

---

## 📁 Project Structure

```
exam_generator/
│
├── 📄 index.php               # Login & Registration page (split-screen animated UI)
├── 📄 dashboard.php           # Main command center with stats & workflow
├── 📄 subjects.php            # Subject & Unit management CRUD
├── 📄 questions.php           # Question bank CRUD with difficulty grading
├── 📄 generate.php            # Paper configuration & auto-generation engine
├── 📄 preview.php             # Paper preview with PDF export
├── 📄 archive.php             # Generated papers archive (view/delete)
├── 📄 profile.php             # Faculty profile with avatar upload
├── 📄 admin_users.php         # Admin user management panel
├── 📄 send_otp.php            # AJAX OTP generation & dispatch
├── 📄 logout.php              # Session destroy & redirect
├── 📄 setup.php               # One-click database installation
├── 📄 get_units.php           # AJAX endpoint: fetch units by subject
├── 📄 database.sql            # Full database schema + default admin seed
│
├── 📁 includes/
│   ├── config.php             # PDO database connection + session bootstrap
│   ├── header.php             # Global sidebar, nav, top-bar, glow orb layer
│   └── footer.php            # Closing HTML tags
│
├── 📁 assets/
│   └── style.css              # Complete design system (variables, glow effects, layout)
│
└── 📁 uploads/                # Faculty profile image storage (auto-created)
```

---

## 🗄️ Database Schema

The system uses **5 core tables**:

```
┌─────────────────┐     ┌───────────────┐     ┌──────────────────┐
│     users       │     │   subjects    │     │     units        │
├─────────────────┤     ├───────────────┤     ├──────────────────┤
│ id (PK)         │     │ id (PK)       │◄────│ subject_id (FK)  │
│ fullName        │     │ name          │     │ unit_name        │
│ email (UNIQUE)  │     │ syllabus      │     └──────────────────┘
│ phone           │     │ created_at    │              │
│ password (hash) │     └───────────────┘              │
│ role            │              │                      │
│ profile_image   │              ▼                      ▼
│ created_at      │     ┌───────────────┐     ┌──────────────────┐
└─────────────────┘     │   questions   │     │generated_papers  │
                        ├───────────────┤     ├──────────────────┤
                        │ id (PK)       │     │ id (PK)          │
                        │ subject_id FK │     │ subject_id (FK)  │
                        │ unit_id FK    │     │ paper_name       │
                        │ question_text │     │ paper_data (JSON)│
                        │ difficulty    │     │ created_at       │
                        │ marks         │     └──────────────────┘
                        └───────────────┘
```

---

## ⚙️ Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP 8.x + MySQL 8.x)
- A modern web browser (Chrome, Firefox, Edge)
- Internet connection (for CDN assets)

### Installation

**1. Clone / Download the project**
```bash
# Place the project folder inside XAMPP's web root:
C:\xampp\htdocs\exam_generator\
```

**2. Start XAMPP Services**
- Open the **XAMPP Control Panel**
- Start **Apache** and **MySQL**

**3. Set Up the Database**

**Option A — Automated (Recommended):**
```
Open browser → http://localhost/exam_generator/setup.php
```
Click "Run Setup" — this creates the database and all tables automatically.

**Option B — Manual via phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Create database named `exam_generator`
3. Import `database.sql`

**4. Configure Database Connection** *(if needed)*

Edit `includes/config.php`:
```php
$host   = 'localhost';
$dbname = 'exam_generator';
$user   = 'root';       // your MySQL username
$pass   = '';           // your MySQL password
```

**5. Launch the Application**
```
http://localhost/exam_generator/
```

### Default Admin Credentials

| Field | Value |
|-------|-------|
| Email | `admin@university.edu` |
| Password | `admin123` |
| OTP | Check `send_otp.php` response (localhost demo mode displays OTP on screen) |

---

## 📖 Usage Walkthrough

```
Step 1: Register / Login
  └─ Faculty registers with access code: FACULTY2026
  └─ 2FA OTP sent to email (displayed on screen in localhost mode)

Step 2: Add Subjects & Units
  └─ Navigate: Sidebar → Subjects Panel
  └─ Add subject name, syllabus overview and unit breakdown

Step 3: Populate Question Bank
  └─ Navigate: Sidebar → Questions Bank
  └─ Add questions with: subject, unit, difficulty (Easy/Medium/Hard), marks

Step 4: Generate Paper
  └─ Navigate: Sidebar → Generator
  └─ Select subject, set total marks, exam date/time
  └─ ✅ Automatic Mode: Engine picks questions to match marks
  └─ ✅ Optional Mode: Define sections with "Attempt any N" instructions

Step 5: Preview & Export
  └─ Review the formatted paper layout
  └─ Click "Download PDF" to export a print-ready PDF

Step 6: Access Archive
  └─ Navigate: Sidebar → Generated Papers
  └─ View, reprint, or delete any previously generated paper
```

---

## 🔒 Security

| Measure | Implementation |
|---------|---------------|
| SQL Injection Prevention | PDO Prepared Statements on all queries |
| Password Security | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) |
| Session Protection | `session_status()` guard; session invalidation on logout |
| OTP Verification | Server-side session-stored OTP with email matching |
| Access Control | Session check in `header.php` redirects unauthenticated users |
| File Upload Security | MIME-type + extension whitelist for profile images |
| Faculty Access Code | Registration gated behind code `FACULTY2026` |

---

## 🎨 UI Design Highlights

- **Split-screen Login** with animated sliding forms (Login ↔ Register)
- **6 Floating Color Glow Orbs** (Indigo, Violet, Rose, Emerald, Amber, Cyan) — animated independently behind every page
- **Glassmorphism Panels** — frosted glass cards with `backdrop-filter: blur`
- **Hover Glow Effects** — cards, nav items, and buttons emit colored glows on interaction
- **Rainbow Gradient Brand** — "Auto GenExam" sidebar text uses 3-stop gradient
- **Floating Illustration** — Animated exam graphic on dashboard hero panel

---

## 🤝 Contributing

1. Fork this repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit changes: `git commit -m 'Add my feature'`
4. Push to branch: `git push origin feature/my-feature`
5. Open a Pull Request

---

## 📜 License

This project is licensed under the **MIT License**.  
Feel free to use, modify, and distribute for educational purposes.

---

## 👨‍💻 Author

Built with ❤️ for the **Hackathon 2026** by the Auto GenExam Team.

> *"Automating knowledge assessment, one paper at a time."*

---

<div align="center">

**⭐ Star this repo if it helped you!**

`Auto GenExam` • `PHP` • `MySQL` • `XAMPP` • `Hackathon 2026`

</div>
