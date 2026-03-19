<?php
session_start();

/* ================= DATABASE CONNECTION ================= */
$conn = new mysqli("localhost", "root", "", "mrkit");
if ($conn->connect_error) {
    die("Database Connection Failed");
}

$popup = "";
$success = false;

/* ================= REGISTRATION LOGIC ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name  = trim($_POST['staff_name']);
    $email = trim($_POST['staff_email']);
    $dept  = strtoupper(trim($_POST['staff_department']));
    $rawPass = $_POST['staff_password'];

    if (strlen($rawPass) < 6) {
        $popup = "❌ Password must be at least 6 characters!";
    } else {

        $pass = password_hash($rawPass, PASSWORD_DEFAULT);

        /* ===== CHECK EMAIL ===== */
        $check = $conn->prepare("SELECT id FROM staff WHERE staff_email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $popup = "❌ Email already registered!";
        } else {

            /* ===== STAFF ID GENERATION ===== */
            $basePrefix = "MRKIT";
            $baseNumber = 8224;

            $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE department=? ORDER BY id DESC LIMIT 1" );
            $stmt->bind_param("s", $dept);
            $stmt->execute();
            $stmt->bind_result($lastId);

            $nextNumber = 1;
            if ($stmt->fetch()) {
                preg_match('/'.$baseNumber.'(\d+)/', $lastId, $matches);
                if (!empty($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }
            $stmt->close();

            $staffId = $basePrefix . "-" . $dept . "-" . $baseNumber . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

            /* ===== INSERT STAFF ===== */
            $insert = $conn->prepare(
                "INSERT INTO staff (staff_id, staff_name, staff_email, staff_password, department)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $insert->bind_param("sssss", $staffId, $name, $email, $pass, $dept);

            if ($insert->execute()) {
                $popup = "
                    ✅ Registration Successful!<br><br>
                    <b>Staff ID:</b> $staffId<br>
                    <b>Name:</b> $name
                ";
                $success = true;
            } else {
                $popup = "❌ Registration Failed!";
            }
            $insert->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Registration</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Arial;
    min-height:100vh;
    background:url('./image/06.jpg') center/cover no-repeat fixed;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}
.glass-box{
    width:100%;
    max-width:550px;
    background:rgba(255,255,255,0.15);
    padding:35px;
    border-radius:22px;
    backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,0.35);
    box-shadow:0 10px 40px rgba(0,0,0,0.35);
}
h2{text-align:center;margin-bottom:25px}
label{font-size:14px;font-weight:600}
input,select{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    margin-bottom:16px;
}
button{
    width:100%;
    padding:14px;
    background:#1a73e8;
    border:none;
    border-radius:12px;
    color:#fff;
    font-size:18px;
    cursor:pointer;
}
button:hover{background:#0d4fa6}

/* POPUP */
.popup-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:999;
}
.popup-box{
    background:#fff;
    padding:30px;
    border-radius:18px;
    text-align:center;
    max-width:360px;
    box-shadow:0 15px 40px rgba(0,0,0,0.3);
}
.popup-box b{color:#1a73e8}
.popup-box button{
    padding:10px 22px;
    border:none;
    background:#1a73e8;
    color:#fff;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}

/* PASSWORD ICON */
.pass-box{position:relative}
.pass-box i{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}
</style>
</head>

<body>

<?php if($popup!=""): ?>
<div class="popup-overlay">
    <div class="popup-box">
        <h3><?= $popup ?></h3>

        <?php if($success): ?>
            <button onclick="goToLogin()">Go to Login</button>
        <?php else: ?>
            <button onclick="closePopup()">OK</button>
        <?php endif; ?>
    </div>
</div>

<script>
function goToLogin(){
    window.location.href = "staffdep.php";
}
function closePopup(){
    document.querySelector('.popup-overlay').style.display='none';
}
</script>
<?php endif; ?>

<div class="glass-box">
<h2>Staff Registration</h2>

<form method="POST">

<label>Full Name</label>
<input type="text" name="staff_name" required>

<label>Email</label>
<input type="email" name="staff_email" required>

<label>Password</label>
<div class="pass-box">
    <input type="password" name="staff_password" id="password" required>
    <i class="fa-solid fa-eye" id="eyeIcon" onclick="togglePassword()"></i>
</div>

<label>Department</label>
<select name="staff_department" required>
<option value="">Select</option>
<option>CSE</option>
<option>ECE</option>
<option>EEE</option>
<option>MECH</option>
<option>CIVIL</option>
<option>AIDS</option>
<option>AIML</option>
<option>IT</option>
<option>BME</option>
</select>

<button type="submit">Register</button>

<p style="text-align:center;margin-top:12px">
Already have an account?
<a href="staffdep.php" style="font-weight:600">Login here</a>
</p>


</form>
</div>

    


<script>
function togglePassword(){
    const pass = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if(pass.type === "password"){
        pass.type = "text";
        icon.classList.replace("fa-eye","fa-eye-slash");
    }else{
        pass.type = "password";
        icon.classList.replace("fa-eye-slash","fa-eye");
    }
}
</script>

</body>
</html>
