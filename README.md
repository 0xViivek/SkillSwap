# 🔄 SkillSwap — Student Skill Exchange Platform

> A web-based platform where students list skills they can **teach** and skills they want to **learn**. The system finds **compatible students** and enables skill-exchange requests.

---

## 💡 Core Feature — Two-Way Skill Matching

```
Student A teaches: C++, HTML       Student B teaches: Python, Java
Student A wants:   Python, Git     Student B wants:   C++

→ A wants Python  ✓  B teaches Python
→ B wants C++     ✓  A teaches C++

MUTUAL MATCH = 100%
```

The matching algorithm is rule-based, transparent, and explainable — not a black-box AI.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Backend | PHP 8+ |
| Server | Apache via XAMPP |
| Storage | Flat `.txt` files (pipe-delimited) |
| Version control | Git + GitHub |

---

## 📁 Project Structure

```
SkillSwap/
├── index.php           Landing page
├── login.php           Student / admin login
├── register.php        Student registration
├── logout.php          Session destroy
├── dashboard.php       Student dashboard
├── profile.php         Edit profile
├── skills.php          Add / remove skills
├── matches.php         View skill matches
├── requests.php        Send / accept / reject requests
│
├── admin/
│   ├── dashboard.php   Admin stats
│   ├── users.php       Manage students
│   └── requests.php    View all requests
│
├── includes/
│   ├── functions.php   ← ALL data layer functions (single source of truth)
│   ├── auth.php        Session helpers
│   ├── header.php      Shared nav
│   └── footer.php      Shared footer
│
├── css/style.css
├── js/script.js
│
└── data/               Flat-file storage
    ├── users.txt
    ├── skills.txt
    ├── user_skills.txt
    └── requests.txt
```

---

## 🚀 Local Setup (XAMPP)

```bash
# 1. Clone the repo
git clone https://github.com/0xViivek/SkillSwap.git

# 2. Move into XAMPP's web root
#    Copy/move the SkillSwap folder to:
#    C:\xampp\htdocs\SkillSwap\

# 3. Start Apache in XAMPP Control Panel

# 4. Open in browser
http://localhost/SkillSwap/

# 5. (First time) Verify data layer
http://localhost/SkillSwap/test_functions.php
# All tests should show ✅ PASS — then delete the file!
```

---

## 🔑 Demo Accounts (seed data)

| Role | Email | Password |
|---|---|---|
| Admin | admin@skillswap.com | password |
| Student | vivek@gmail.com | password |
| Student | rahul@gmail.com | password |

---

## 🌿 Branch Strategy

| Branch | Owner | Purpose |
|---|---|---|
| `main` | Member 1 | Production-ready code only |
| `feature/auth` | Member 2 | Login, Register, Logout |
| `feature/skills` | Member 3 | Profile, Skills CRUD |
| `feature/matching` | Member 1 | Matching engine, Match page |
| `feature/exchange` | Member 4 | Requests (send/accept/reject) |
| `feature/ui-admin` | Member 5 | Landing page, CSS, Admin pages |

**Workflow:**
```bash
git pull origin main            # always before starting work
git checkout feature/your-branch
# ... code ...
git add .
git commit -m "feat: description"
git push origin feature/your-branch
# open Pull Request → Member 1 reviews → merge to main
```

---

## 📅 4-Day Plan

| Day | Goal |
|---|---|
| Day 1 | Foundation: auth, sessions, profile, skills CRUD, .txt layer |
| Day 2 | Matching engine, match score, match results page |
| Day 3 | Exchange requests, admin dashboard |
| Day 4 | 🔒 Feature freeze — bug fix, UI polish, demo prep, PPT |

---

## 👥 Team

| Member | Role | Module |
|---|---|---|
| Member 1 | Project Lead | Architecture · Matching · Integration |
| Member 2 | Auth | Login · Register · Sessions |
| Member 3 | Skills | Profile · Skills CRUD · Browse |
| Member 4 | Exchange | Requests · Accept/Reject |
| Member 5 | UI / Admin | CSS · Landing · Admin pages |

---

## 🔮 Future Scope

- MySQL/MariaDB database migration
- Real-time chat between matched students
- Email notifications
- Ratings & reviews after exchanges
- AI-powered skill recommendations
- Mobile application
- College-wide deployment

---

## 📄 License

Academic project — built during a 15-day internship program.
