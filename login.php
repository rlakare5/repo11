<?php
session_start();
include 'includes/config.php';

// Check if already logged in
if(isLoggedIn()) {
    redirect('index.php');
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prn = sanitize($_POST['prn']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    
    // Validate inputs
    if(empty($prn) || empty($password) || empty($role)) {
        setAlert('error', 'All fields are required');
    } else {
        // Check user credentials based on role
        $table = '';
        switch($role) {
            case 'student':
                $table = 'students';
                break;
            case 'hod':
                $table = 'hods';
                break;
            case 'dean':
                $table = 'deans';
                break;
            case 'admin':
                $table = 'admins';
                break;
            default:
                setAlert('error', 'Invalid role selected');
                break;
        }
        
        if(!empty($table)) {
            $query = "SELECT * FROM $table WHERE ";
            
            if($role === 'student') {
                $query .= "prn = '$prn'";
            } else {
                $query .= "username = '$prn'";
            }
            
            $result = mysqli_query($conn, $query);
            
            if(mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);
                
                if($password === $user['password']) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $role;
                    
                    if($role === 'student') {
                        $_SESSION['student_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['profile_image'] = $user['profile_image'];
                        $_SESSION['department'] = $user['department'];
                        $_SESSION['year'] = $user['year'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['prn']= $user['prn'];
                       
                    } else {
                        $_SESSION['admin_name'] = $user['name'];
                        
                        if($role === 'hod') {
                            $_SESSION['department'] = $user['department'];
                        }
                    }
                    
                    // Redirect based on role
                    if($role === 'student') {
                        redirect('index.php');
                    } else {
                        redirect('admin/dashboard.php');
                    }
                } else {
                    setAlert('error', 'Invalid password');
                }
            } else {
                setAlert('error', 'User not found');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Login</h1>
                <p>Access your DSC account</p>
            </div>
            
            <?php displayAlert(); ?>
            
            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="role">Login As</label>
                    <select id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="hod">HOD</option>
                        <option value="dean">Dean</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prn" id="id-label">PRN</label>
                    <input type="text" id="prn" name="prn" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </div>
                
                <div class="login-help" id="login-help">
                    <p>Default password for students: [FirstName]@123</p>
                    <p>Forgot password? Contact your administrator.</p>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Change label based on role selection
        document.getElementById('role').addEventListener('change', function() {
            const label = document.getElementById('id-label');
            const loginHelp = document.getElementById('login-help');
            
            if (this.value === 'student') {
                label.textContent = 'PRN';
                loginHelp.style.display = 'block';
            } else {
                label.textContent = 'Username';
                loginHelp.style.display = 'none';
            }
        });
    </script>
</body>
</html>