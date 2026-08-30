<!-- register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }

        /* Navigation styles */
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

        /* Container styles */
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            max-width: 900px;
            margin: 20px auto;
        }

        .left-panel {
            background: #666;
            color: white;
            padding: 40px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .right-panel {
            padding: 40px;
            width: 60%;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            font-size: 14px;
            color: #666;
            background: transparent;
        }

        select {
            cursor: pointer;
            text-transform: uppercase;
        }

        input::placeholder {
            color: #999;
            text-transform: uppercase;
            font-size: 12px;
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
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #a31010;
        }

        .signin-link {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .signin-button {
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .signin-button:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Style for the role select dropdown */
        select option {
            padding: 12px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php 
    session_start(); 
    include "../includes/db.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $role = $_POST["role"];

        $query = "SELECT * FROM User WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email already registered!');</script>";
        } else {
            $query = "INSERT INTO User (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
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
            <p>If you already have an account, just sign in.</p>
            <a href="login.php" class="signin-link">
                <div class="signin-button">SIGN IN</div>
            </a>
        </div>
        <div class="right-panel">
            <h2>Create your Account</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" required placeholder="NAME">
                </div>
                <div class="form-group">
                    <input type="email" name="email" required placeholder="EMAIL">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="PASSWORD">
                </div>
                <div class="form-group">
                    <select name="role" required>
                        <option value="" disabled selected>SELECT ROLE</option>
                        <option value="buyer">BUYER</option>
                        <option value="artisan">ARTISAN</option>
                    </select>
                </div>
                <button type="submit">SIGN UP</button>
            </form>
        </div>
    </div>
</body>
</html>