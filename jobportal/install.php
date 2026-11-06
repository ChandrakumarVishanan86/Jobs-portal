<?php
/**
 * One-time demo seeder. Run AFTER importing database/schema.sql:
 *   http://localhost/jobportal/install.php
 * DELETE THIS FILE when finished.
 */
require __DIR__ . '/config/config.php';
header('Content-Type: text/plain; charset=utf-8');

$pdo = Database::conn();
if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    exit("Already seeded (users exist). Delete install.php.\n");
}

$mkUser = function (string $name, string $email, string $pass, string $role) use ($pdo): int {
    $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)')
        ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
    return (int)$pdo->lastInsertId();
};

$mkUser('Site Admin', 'admin@jobportal.test', 'Admin@123', 'admin');
$mkUser('Demo Candidate', 'candidate@jobportal.test', 'Candidate@123', 'candidate');

$companies = [
    ['Lanka Tech Solutions', 'Colombo', 'employer1@jobportal.test'],
    ['Ceylon Finance PLC',   'Kandy',   'employer2@jobportal.test'],
    ['Island Hospitality',   'Galle',   'employer3@jobportal.test'],
];
$companyIds = [];
foreach ($companies as [$cname, $loc, $email]) {
    $uid = $mkUser($cname . ' HR', $email, 'Employer@123', 'employer');
    $pdo->prepare('INSERT INTO companies (user_id,name,location,description) VALUES (?,?,?,?)')
        ->execute([$uid, $cname, $loc, 'Demo company.']);
    $companyIds[] = (int)$pdo->lastInsertId();
}

$jobs = [
    [0, 'Senior PHP Developer', 'Colombo', 'IT & Software', 'Full-time', 250000, 400000],
    [0, 'Junior Web Developer', 'Colombo', 'IT & Software', 'Full-time', 80000, 120000],
    [0, 'DevOps Engineer', 'Remote', 'IT & Software', 'Remote', 300000, 450000],
    [0, 'UI/UX Designer', 'Kandy', 'Design', 'Contract', 120000, 180000],
    [1, 'Accounts Executive', 'Kandy', 'Finance', 'Full-time', 70000, 100000],
    [1, 'Credit Analyst', 'Colombo', 'Finance', 'Full-time', 150000, 220000],
    [1, 'Marketing Intern', 'Kandy', 'Marketing', 'Internship', 25000, 40000],
    [1, 'Digital Marketing Manager', 'Colombo', 'Marketing', 'Full-time', 180000, 260000],
    [2, 'Front Office Associate', 'Galle', 'Hospitality', 'Full-time', 45000, 65000],
    [2, 'Head Chef', 'Galle', 'Hospitality', 'Full-time', 160000, 230000],
    [2, 'Part-time Barista', 'Colombo', 'Hospitality', 'Part-time', 30000, 45000],
    [2, 'Customer Support Agent', 'Remote', 'Customer Service', 'Remote', 60000, 90000],
    [0, 'QA Automation Engineer', 'Colombo', 'IT & Software', 'Full-time', 140000, 210000],
    [1, 'Internal Auditor', 'Colombo', 'Finance', 'Full-time', 200000, 300000],
];
$ins = $pdo->prepare("INSERT INTO jobs (company_id,title,description,location,category,job_type,salary_min,salary_max,status,created_at)
                      VALUES (?,?,?,?,?,?,?,?,'approved', NOW() - INTERVAL ? HOUR)");
foreach ($jobs as $i => [$c, $title, $loc, $cat, $type, $min, $max]) {
    $ins->execute([$companyIds[$c], $title, "<p>We are hiring a <strong>$title</strong>. Join our growing team.</p>", $loc, $cat, $type, $min, $max, $i * 7 + 2]);
}

echo "Seeded.\n\nAdmin:     admin@jobportal.test / Admin@123\nCandidate: candidate@jobportal.test / Candidate@123\nEmployer:  employer1@jobportal.test / Employer@123\n\nNow DELETE install.php.\n";
