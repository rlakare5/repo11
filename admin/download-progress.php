
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_type'])) {
    $download_type = $_POST['download_type'];
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . generateFilename($download_type, $_POST) . '"');
    
    // Create file pointer connected to output stream
    $output = fopen('php://output', 'w');
    
    switch($download_type) {
        case 'class':
            downloadClassProgress($output, $_POST['department'], $_POST['year']);
            break;
        case 'department':
            downloadDepartmentProgress($output, $_POST['department']);
            break;
        case 'all':
            downloadAllProgress($output);
            break;
        case 'problem_stats':
            downloadProblemStats($output);
            break;
    }
    
    fclose($output);
    exit;
}

function generateFilename($type, $data) {
    $date = date('Y-m-d_H-i-s');
    
    switch($type) {
        case 'class':
            return "class_progress_{$data['department']}_{$data['year']}_{$date}.csv";
        case 'department':
            return "department_progress_{$data['department']}_{$date}.csv";
        case 'all':
            return "all_students_progress_{$date}.csv";
        case 'problem_stats':
            return "problem_solving_stats_{$date}.csv";
        default:
            return "progress_report_{$date}.csv";
    }
}

function downloadClassProgress($output, $department, $year) {
    global $conn;
    
    // Write CSV header
    fputcsv($output, [
        'PRN',
        'Name',
        'Email',
        'Contact',
        'Department',
        'Year',
        'Total Points',
        'Class Rank',
        'Problems Solved',
        'Certifications',
        'Events Attended',
        'LinkedIn',
        'GitHub',
        'LeetCode',
        'Last Activity'
    ]);
    
    // Get students data with their progress
    $query = "SELECT 
                s.*,
                COALESCE(sp.total_points, 0) as total_points,
                dl.rank_in_class,
                COALESCE(ps_count.problems_solved, 0) as problems_solved,
                COALESCE(cert_count.certifications, 0) as certifications,
                COALESCE(event_count.events_attended, 0) as events_attended
              FROM students s
              LEFT JOIN (
                  SELECT student_id, SUM(points) as total_points 
                  FROM student_points 
                  GROUP BY student_id
              ) sp ON s.id = sp.student_id
              LEFT JOIN department_leaderboard dl ON s.id = dl.id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as problems_solved 
                  FROM problem_submissions 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) ps_count ON s.id = ps_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as certifications 
                  FROM certifications 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) cert_count ON s.id = cert_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as events_attended 
                  FROM event_participants 
                  GROUP BY student_id
              ) event_count ON s.id = event_count.student_id
              WHERE s.department = '$department' AND s.year = '$year'
              ORDER BY dl.rank_in_class ASC";
    
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['prn'],
            $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'],
            $row['email'],
            $row['contact_no'],
            $row['department'],
            $row['year'],
            $row['total_points'],
            $row['rank_in_class'] ?: 'N/A',
            $row['problems_solved'],
            $row['certifications'],
            $row['events_attended'],
            $row['linkedin_url'] ?: 'Not provided',
            $row['github_url'] ?: 'Not provided',
            $row['leetcode_url'] ?: 'Not provided',
            $row['updated_at']
        ]);
    }
}

function downloadDepartmentProgress($output, $department) {
    global $conn;
    
    // Write CSV header
    fputcsv($output, [
        'PRN',
        'Name',
        'Email',
        'Year',
        'Total Points',
        'Department Rank',
        'Class Rank',
        'Problems Solved',
        'Certifications',
        'Events Attended',
        'LinkedIn',
        'GitHub',
        'LeetCode'
    ]);
    
    // Get department ranking
    $query = "SELECT 
                s.*,
                COALESCE(sp.total_points, 0) as total_points,
                dl.rank_in_class,
                ROW_NUMBER() OVER (ORDER BY COALESCE(sp.total_points, 0) DESC) as dept_rank,
                COALESCE(ps_count.problems_solved, 0) as problems_solved,
                COALESCE(cert_count.certifications, 0) as certifications,
                COALESCE(event_count.events_attended, 0) as events_attended
              FROM students s
              LEFT JOIN (
                  SELECT student_id, SUM(points) as total_points 
                  FROM student_points 
                  GROUP BY student_id
              ) sp ON s.id = sp.student_id
              LEFT JOIN department_leaderboard dl ON s.id = dl.id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as problems_solved 
                  FROM problem_submissions 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) ps_count ON s.id = ps_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as certifications 
                  FROM certifications 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) cert_count ON s.id = cert_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as events_attended 
                  FROM event_participants 
                  GROUP BY student_id
              ) event_count ON s.id = event_count.student_id
              WHERE s.department = '$department'
              ORDER BY sp.total_points DESC";
    
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['prn'],
            $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'],
            $row['email'],
            $row['year'],
            $row['total_points'],
            $row['dept_rank'],
            $row['rank_in_class'] ?: 'N/A',
            $row['problems_solved'],
            $row['certifications'],
            $row['events_attended'],
            $row['linkedin_url'] ?: 'Not provided',
            $row['github_url'] ?: 'Not provided',
            $row['leetcode_url'] ?: 'Not provided'
        ]);
    }
}

function downloadAllProgress($output) {
    global $conn;
    
    // Write CSV header
    fputcsv($output, [
        'PRN',
        'Name',
        'Email',
        'Contact',
        'Department',
        'Year',
        'Total Points',
        'University Rank',
        'Department Rank',
        'Class Rank',
        'Problems Solved',
        'Certifications',
        'Events Attended',
        'LinkedIn',
        'GitHub',
        'LeetCode',
        'Registration Date'
    ]);
    
    // Get all students with comprehensive ranking
    $query = "SELECT 
                s.*,
                COALESCE(sp.total_points, 0) as total_points,
                dl.rank_in_class,
                ROW_NUMBER() OVER (ORDER BY COALESCE(sp.total_points, 0) DESC) as university_rank,
                ROW_NUMBER() OVER (PARTITION BY s.department ORDER BY COALESCE(sp.total_points, 0) DESC) as dept_rank,
                COALESCE(ps_count.problems_solved, 0) as problems_solved,
                COALESCE(cert_count.certifications, 0) as certifications,
                COALESCE(event_count.events_attended, 0) as events_attended
              FROM students s
              LEFT JOIN (
                  SELECT student_id, SUM(points) as total_points 
                  FROM student_points 
                  GROUP BY student_id
              ) sp ON s.id = sp.student_id
              LEFT JOIN department_leaderboard dl ON s.id = dl.id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as problems_solved 
                  FROM problem_submissions 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) ps_count ON s.id = ps_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as certifications 
                  FROM certifications 
                  WHERE status = 'approved' 
                  GROUP BY student_id
              ) cert_count ON s.id = cert_count.student_id
              LEFT JOIN (
                  SELECT student_id, COUNT(*) as events_attended 
                  FROM event_participants 
                  GROUP BY student_id
              ) event_count ON s.id = event_count.student_id
              WHERE s.is_active = 1
              ORDER BY sp.total_points DESC";
    
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['prn'],
            $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'],
            $row['email'],
            $row['contact_no'],
            $row['department'],
            $row['year'],
            $row['total_points'],
            $row['university_rank'],
            $row['dept_rank'],
            $row['rank_in_class'] ?: 'N/A',
            $row['problems_solved'],
            $row['certifications'],
            $row['events_attended'],
            $row['linkedin_url'] ?: 'Not provided',
            $row['github_url'] ?: 'Not provided',
            $row['leetcode_url'] ?: 'Not provided',
            $row['created_at']
        ]);
    }
}

function downloadProblemStats($output) {
    global $conn;
    
    // Write CSV header
    fputcsv($output, [
        'Problem Title',
        'Platform',
        'Difficulty',
        'Points',
        'Date',
        'Total Submissions',
        'Approved Submissions',
        'Rejected Submissions',
        'Pending Submissions',
        'Success Rate (%)',
        'Most Recent Submission'
    ]);
    
    // Get problem statistics
    $query = "SELECT 
                dp.*,
                COUNT(ps.id) as total_submissions,
                SUM(CASE WHEN ps.status = 'approved' THEN 1 ELSE 0 END) as approved_submissions,
                SUM(CASE WHEN ps.status = 'rejected' THEN 1 ELSE 0 END) as rejected_submissions,
                SUM(CASE WHEN ps.status = 'pending' THEN 1 ELSE 0 END) as pending_submissions,
                CASE 
                    WHEN COUNT(ps.id) > 0 
                    THEN ROUND((SUM(CASE WHEN ps.status = 'approved' THEN 1 ELSE 0 END) / COUNT(ps.id)) * 100, 2)
                    ELSE 0 
                END as success_rate,
                MAX(ps.submitted_at) as most_recent_submission
              FROM daily_problems dp
              LEFT JOIN problem_submissions ps ON dp.id = ps.problem_id
              GROUP BY dp.id
              ORDER BY dp.date DESC, dp.platform, dp.difficulty";
    
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['problem_title'],
            ucfirst($row['platform']),
            ucfirst($row['difficulty']),
            $row['points'],
            $row['date'],
            $row['total_submissions'],
            $row['approved_submissions'],
            $row['rejected_submissions'],
            $row['pending_submissions'],
            $row['success_rate'],
            $row['most_recent_submission'] ?: 'No submissions'
        ]);
    }
}
?>
