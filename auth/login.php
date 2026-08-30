<!-- login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }

        .main-nav {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 10px 0;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-left {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 50px;
            margin-right: 10px;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        /* Login container styles */
        .container {
            display: flex;
            min-height: calc(100vh - 70px); /* Accounting for nav height */
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            max-width: 900px;
            margin: 20px auto;
        }

        .left-panel {
            padding: 40px;
            width: 60%;
        }

        .right-panel {
            background: #666;
            color: white;
            padding: 40px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            font-size: 14px;
            color: #666;
        }

        input::placeholder {
            color: #999;
            text-transform: uppercase;
            font-size: 12px;
        }

        .forgot-password {
            text-align: right;
            font-size: 12px;
            color: #999;
            text-decoration: none;
            margin-top: 5px;
            display: block;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #b91111;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
        }

        button:hover {
            background: #a31010;
        }

        .signup-link {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .signup-button {
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    include "../includes/db.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $query = "SELECT * FROM User WHERE email = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            if ($_SESSION["role"] == "artisan") {
                header("Location: ../seller/artisan_dashboard.php");
            } else {
                header("Location: ../buyer/buyer_dashboard.php");
            }            
            exit();
        } else {
            echo "<script>alert('Invalid email or password!');</script>";
        }
    }
    ?>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <img src="../assets/static_pictures/artisan/download.png" alt="Logo" class="logo">
                <span class="brand-name">NepArt Creations</span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="left-panel">
            <h2>Welcome Back</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" required placeholder="EMAIL">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="PASSWORD">
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit">SIGN IN</button>
            </form>
        </div>
        <div class="right-panel">
            <p>Don't have an account? Please Sign up!</p>
            <a href="register.php" class="signup-link">
                <div class="signup-button">SIGN UP</div>
            </a>
        </div>
    </div>
</body>
</html>