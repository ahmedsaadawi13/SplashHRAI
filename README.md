# SplashHRAI - AI-Powered HR Management SaaS Platform

A complete multi-tenant Human Resources SaaS system combining ATS (Applicant Tracking), Employee Management, Performance Reviews, Payroll, and AI-powered features.

## 🚀 Features

### Core HR Management
- **Employee Management** - Complete employee lifecycle management
- **Department & Position Management** - Organizational structure
- **Document Management** - Employee documents and company policies
- **Multi-tenant Architecture** - Secure tenant isolation

### Applicant Tracking System (ATS)
- **Job Posting Management** - Create and manage job openings
- **Candidate Pipeline** - Track candidates through hiring stages
- **AI Resume Analysis** - Automated resume scoring and ranking
- **AI Job Description Generator** - Generate professional job descriptions
- **Interview Scheduling** - Manage interview calendar
- **Candidate Notes & Activities** - Collaboration tools

### AI-Powered Features
- **AI Resume Analyzer** - Scores and analyzes candidate resumes
- **AI Candidate Ranking** - Intelligent candidate sorting
- **AI Job Description Generator** - Creates compelling job posts
- **AI Interview Questions Generator** - Role-specific questions
- **AI Performance Review Summary** - Automated review insights
- **AI Email Generator** - Professional HR communications
- **AI Policy Generator** - Company policy templates
- **AI HR Assistant** - Answer HR-related questions

### Onboarding
- **Onboarding Templates** - Reusable workflows
- **Task Management** - Track onboarding progress
- **Document Collection** - Gather required documents
- **Automated Assignments** - Auto-assign to new hires

### Attendance & Leave
- **Attendance Tracking** - Daily check-in/check-out records
- **Leave Management** - Request and approve time off
- **Leave Types** - Configurable leave categories
- **Leave Balances** - Track accruals and usage
- **Attendance Reports** - Comprehensive analytics

### Performance Management
- **Performance Cycles** - Annual/quarterly reviews
- **Self Reviews** - Employee self-assessment
- **Manager Reviews** - Supervisor evaluations
- **360 Feedback** - Peer feedback collection
- **Goal Setting & Tracking** - OKR management
- **AI Review Summaries** - Automated insights

### Payroll
- **Payroll Periods** - Monthly/bi-weekly cycles
- **Salary Management** - Employee compensation
- **Earnings & Deductions** - Flexible components
- **Tax Calculations** - Automated tax computation
- **Payslip Generation** - PDF/HTML payslips
- **Attendance Integration** - Hours-based calculations
- **CSV Export** - Export for accounting systems

### Dashboards & Reports
- **HR Dashboard** - Real-time metrics
- **Employee Reports** - Filterable employee lists
- **ATS Analytics** - Hiring pipeline insights
- **Attendance Reports** - Presence statistics
- **Payroll Reports** - Compensation analysis
- **Custom Filters** - Advanced search options

### API & Integrations
- **REST API** - Full API access
- **API Key Authentication** - Secure access
- **Rate Limiting** - Protect against abuse
- **API Endpoints**:
  - Create Candidate
  - Create Employee
  - Record Attendance
  - Get Jobs
  - Get Employees

### Security & Compliance
- **Multi-tenant Isolation** - Complete data separation
- **CSRF Protection** - Secure forms
- **Password Hashing** - bcrypt encryption
- **Login Throttling** - Brute force protection
- **Role-Based Access** - Granular permissions
- **Activity Logging** - Audit trail
- **API Rate Limiting** - DDoS protection

### Subscription Management
- **Multiple Plans** - Starter, Professional, Enterprise
- **Quota Enforcement** - Limit employees, jobs, candidates
- **Trial Period** - 14-day free trial
- **AI Token Tracking** - Monitor AI usage

## 📋 Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP Extensions:
  - PDO
  - pdo_mysql
  - curl (for AI features)
  - fileinfo (for file uploads)
  - mbstring

## 🛠️ Installation

### 1. Clone or Download

```bash
git clone <repository-url>
cd SplashHRAI
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` file with your settings:
```
DB_HOST=localhost
DB_NAME=splashhr_ai
DB_USER=root
DB_PASS=your_password

AI_API_KEY=your_openai_api_key_here
```

### 3. Import Database

```bash
mysql -u root -p < database.sql
```

Or using phpMyAdmin:
- Create database `splashhr_ai`
- Import `database.sql`

### 4. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/
chmod -R 755 storage/cache/
```

### 5. Configure Web Server

**Apache (.htaccess included)**

Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

**Nginx**

Add to your site configuration:
```nginx
location / {
    try_files $uri $uri/ /public/index.php?$query_string;
}
```

### 6. Access Application

Navigate to: `http://localhost/SplashHRAI`

## 👤 Demo Credentials

**HR Admin:**
- Email: admin@demo.splashhr.ai
- Password: password

**HR Manager:**
- Email: manager@demo.splashhr.ai
- Password: password

**Employee:**
- Email: employee@demo.splashhr.ai
- Password: password

## 🎯 User Roles

- **platform_admin** - Platform-level administrator
- **hr_admin** - Tenant administrator (full access)
- **hr_manager** - HR manager (manage employees, approve requests)
- **hr_specialist** - HR specialist (manage data, limited approval)
- **employee** - Regular employee (view own data)
- **viewer** - Read-only access

## 🤖 AI Configuration

SplashHRAI supports OpenAI-compatible APIs for AI features.

### Configure AI API Key

In `.env`:
```
AI_PROVIDER=openai
AI_API_KEY=sk-your-api-key-here
AI_MODEL=gpt-4
AI_MAX_TOKENS=2000
AI_TEMPERATURE=0.7
```

### AI Features Available

1. **Resume Analysis** - Scores resumes 0-100
2. **Candidate Ranking** - Sorts by fit
3. **Job Description Generator** - Creates job posts
4. **Interview Questions** - Generates role-specific questions
5. **Email Generator** - Professional communications
6. **Performance Summaries** - Review insights
7. **Policy Generator** - Company policy templates
8. **HR Q&A Assistant** - Answers HR questions

**Note:** AI features work in demo mode without API key (returns simulated responses).

## 📡 API Usage

### Authentication

Include API key in header:
```bash
X-API-KEY: sk_demo_1234567890abcdef
```

### Create Candidate

```bash
POST /api/candidates/create
Content-Type: application/json

{
  "job_id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1-555-0100"
}
```

### Create Employee

```bash
POST /api/employees/create
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.smith@example.com",
  "department_id": 1,
  "position_id": 2,
  "hire_date": "2024-01-15",
  "salary": 75000
}
```

### Record Attendance

```bash
POST /api/attendance/record
Content-Type: application/json

{
  "employee_id": 1,
  "date": "2024-01-15",
  "check_in_time": "09:00:00",
  "check_out_time": "17:30:00"
}
```

### Get Jobs

```bash
GET /api/jobs?status=open
```

### Get Employees

```bash
GET /api/employees?status=active&limit=50
```

## 🔧 Testing

Run all tests:
```bash
php tests/run_all_tests.php
```

Run individual tests:
```bash
php tests/DatabaseTest.php
php tests/EmployeeTest.php
php tests/CandidateTest.php
php tests/AIHelperTest.php
```

## 📁 Project Structure

```
SplashHRAI/
├── app/
│   ├── controllers/      # MVC Controllers
│   ├── models/          # Database Models
│   ├── views/           # View Templates
│   ├── core/            # Core Framework
│   └── helpers/         # Helper Classes
├── config/
│   └── config.php       # Configuration
├── public/
│   └── index.php        # Entry Point
├── storage/
│   ├── uploads/         # File Uploads
│   ├── logs/            # Application Logs
│   └── cache/           # Cache Files
├── tests/               # Test Suite
├── database.sql         # Database Schema
├── .env.example         # Environment Template
└── README.md            # Documentation
```

## 🚀 Deployment

### Production Checklist

1. **Environment**
   - Set `APP_ENV=production` in `.env`
   - Configure proper database credentials
   - Set strong `SESSION_LIFETIME`

2. **Security**
   - Change all default passwords
   - Generate new API keys
   - Configure SSL/HTTPS
   - Set proper file permissions (755 for directories, 644 for files)

3. **Performance**
   - Enable PHP OpCache
   - Configure MySQL query cache
   - Set up log rotation

4. **Backup**
   - Schedule database backups
   - Backup `storage/uploads/` directory

### Cron Jobs

Add to crontab for automated tasks:

```cron
# Attendance sync (daily at midnight)
0 0 * * * php /path/to/SplashHRAI/scripts/attendance_sync.php

# Payroll reminder (1st of month)
0 9 1 * * php /path/to/SplashHRAI/scripts/payroll_reminder.php

# Leave balance update (monthly)
0 2 1 * * php /path/to/SplashHRAI/scripts/leave_balance_update.php
```

## 📊 Subscription Plans

### Starter - $49/month
- 50 employees
- 10 jobs
- 100 candidates
- 500 documents
- 50,000 AI tokens/month

### Professional - $99/month
- 200 employees
- 50 jobs
- 500 candidates
- 2,000 documents
- 200,000 AI tokens/month

### Enterprise - $199/month
- Unlimited employees
- Unlimited jobs
- Unlimited candidates
- Unlimited documents
- Unlimited AI tokens

## 🐛 Troubleshooting

### Database Connection Error
- Check `.env` credentials
- Ensure MySQL is running
- Verify database exists

### File Upload Fails
- Check `storage/uploads/` permissions
- Verify `MAX_UPLOAD_SIZE` in `.env`
- Check PHP `upload_max_filesize` setting

### AI Features Not Working
- Verify `AI_API_KEY` in `.env`
- Check API key validity
- Review `storage/logs/app.log` for errors
- Test with demo mode (no API key)

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Verify `storage/` is writable

## 📝 License

This project is open-source software.

## 🤝 Support

For issues and questions:
- Check documentation
- Review logs in `storage/logs/`
- Test with demo credentials

## 🔄 Updates & Roadmap

### Completed Features
- ✅ Multi-tenant architecture
- ✅ Employee management
- ✅ ATS with AI
- ✅ Performance reviews
- ✅ Payroll processing
- ✅ REST API
- ✅ Attendance tracking
- ✅ Leave management

### Future Enhancements
- 📅 Calendar integration (Google, Outlook)
- 📧 Email integration (SMTP)
- 📱 Mobile app
- 📊 Advanced analytics dashboard
- 🔔 Real-time notifications
- 💬 Internal messaging
- 📈 Advanced reporting
- 🌍 Multi-language support

## 👥 Contributing

Contributions welcome! Please follow:
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 🙏 Acknowledgments

Built with:
- PHP & MySQL
- Custom MVC Framework
- OpenAI API Integration
- Modern CSS & Vanilla JavaScript

---

**SplashHRAI** - Simplifying HR Management with AI
