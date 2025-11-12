
<?php
// Get filters
$department_filter = isset($_GET['department']) ? $_GET['department'] : 'all';
$year_filter = isset($_GET['year']) ? $_GET['year'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query conditions
$conditions = [];
if($department_filter !== 'all') {
    $conditions[] = "s.department = '$department_filter'";
}
if($year_filter !== 'all') {
    $conditions[] = "s.year = '$year_filter'";
}
if($status_filter !== 'all') {
    $conditions[] = "s.is_active = " . ($status_filter === 'active' ? '1' : '0');
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get students with their points and rankings
$query = "SELECT s.*, 
                 COALESCE(sp.total_points, 0) as total_points,
                 COALESCE(ps_count.problems_solved, 0) as problems_solved,
                 COALESCE(cert_count.certifications, 0) as certifications
          FROM students s
          LEFT JOIN (
              SELECT student_id, SUM(points) as total_points 
              FROM student_points 
              GROUP BY student_id
          ) sp ON s.id = sp.student_id
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
          $where_clause
          ORDER BY sp.total_points DESC";

$students_result = mysqli_query($conn, $query);
?>
                <!-- Filters -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Filter Students</h2>
                    </div>
                    <div class="section-content">
                        <form method="GET" class="filter-form">
                            <div class="filter-group">
                                <label for="department">Department:</label>
                                <select name="department" id="department">
                                    <option value="all">All Departments</option>
                                    <?php foreach($departments as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $department_filter === $key ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="year">Year:</label>
                                <select name="year" id="year">
                                    <option value="all">All Years</option>
                                    <?php foreach($years as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $year_filter === $key ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="status">Status:</label>
                                <select name="status" id="status">
                                    <option value="all">All Students</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Students (<?php echo mysqli_num_rows($students_result); ?>)</h2>
                    </div>
                    <div class="section-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>PRN</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Year</th>
                                        <th>Points</th>
                                        <th>Problems</th>
                                        <th>Certificates</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                                        <tr>
                                            <td><?php echo $student['prn']; ?></td>
                                            <td>
                                                <strong><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></strong><br>
                                                <small><?php echo $student['email']; ?></small>
                                            </td>
                                            <td><?php echo $departments[$student['department']]; ?></td>
                                            <td><?php echo $years[$student['year']]; ?></td>
                                            <td><?php echo $student['total_points']; ?></td>
                                            <td><?php echo $student['problems_solved']; ?></td>
                                            <td><?php echo $student['certifications']; ?></td>
                                            <td>
                                                <span class="status-<?php echo $student['is_active'] ? 'approved' : 'rejected'; ?>">
                                                    <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $student['is_active'] ? '0' : '1'; ?>">
                                                    <button type="submit" name="update_status" class="btn btn-small <?php echo $student['is_active'] ? 'btn-danger' : 'btn-success'; ?>">
                                                        <?php echo $student['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                                <a href="../profile.php?id=<?php echo $student['id']; ?>" class="btn btn-small btn-secondary">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
