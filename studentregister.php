<?php
// ================= DATABASE CONNECTION =================
$conn = new mysqli("localhost", "root", "", "mrkit");
if ($conn->connect_error) {
    die("Database Connection Failed");
}

$popup = "";
$redirect = "";

// ================= REGISTRATION LOGIC =================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = $_POST['student_name'];
    $email    = $_POST['student_email'];
    $password = password_hash($_POST['student_password'], PASSWORD_DEFAULT);
    $batch    = $_POST['student_batch'];
    $dept     = $_POST['student_dept'];
    $semester = $_POST['student_semester'];
    $regno    = $_POST['student_regno'];

    // Check email exists
    $check = $conn->prepare("SELECT id FROM students WHERE student_email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $popup = "❌ Email already registered!";
    } else {

       $stmt = $conn->prepare("
    INSERT INTO students 
        (student_name, student_email, student_password, student_batch, student_dept, student_semester, student_regno)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $name,
    $email,
    $password,
    $batch,
    $dept,
    $semester,
    $regno
);





        if ($stmt->execute()) {
            $popup = "✅ Registration Successful! Redirecting...";
            $redirect = "dep.php"; // NEXT PAGE
        } else {
            $popup = "❌ Registration Failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration</title>

<!-- FONT AWESOME ICONS -->
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
    padding:12px 45px 12px 12px;
    border-radius:10px;
    border:none;
    margin-bottom:16px;
}
.row{display:flex;gap:16px}
.col{flex:1}
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

/* PASSWORD ICON */
.pass-box{position:relative}
.pass-box i{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#333;
    font-size:18px;
}

/* POPUP MODAL */
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
    max-width:340px;
    box-shadow:0 15px 40px rgba(0,0,0,0.3);
    animation:pop 0.3s ease;
}
.popup-box h3{margin-bottom:20px}
.popup-box button{
    padding:10px 22px;
    border:none;
    background:#1a73e8;
    color:#fff;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}
@keyframes pop{
    from{transform:scale(0.8);opacity:0}
    to{transform:scale(1);opacity:1}
}

@media(max-width:600px){
    .row{flex-direction:column}
}
</style>
</head>

<body>

<?php if($popup!=""): ?>
<div class="popup-overlay" id="popup">
    <div class="popup-box">
        <h3><?php echo $popup; ?></h3>
        <button onclick="closePopup()">OK</button>
    </div>
</div>

<?php if($redirect!=""): ?>
<script>
setTimeout(function(){
    window.location.href = "<?php echo $redirect; ?>";
}, 2000);
</script>
<?php endif; ?>

<?php endif; ?>

<div class="glass-box">
<h2>Student Register</h2>

<form method="POST">

<label>Full Name</label>
<input type="text" name="student_name" required>

<label>Email</label>
<input type="email" name="student_email" required>

<label>Password</label>
<div class="pass-box">
    <input type="password" name="student_password" id="password" required>
    <i class="fa-solid fa-eye" id="eyeIcon" onclick="togglePassword()"></i>
</div>

<div class="row">
<div class="col">
<label>Batch</label>
<select name="student_batch" required>
<option value="">Select</option>
<option>2020-2024</option>
<option>2021-2025</option>
<option>2022-2026</option>
</select>
</div>

<div class="col">
<label>Department</label>
<select name="student_dept" required>
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
</div>
</div>

<label>Semester</label>
<select name="student_semester" required>
<option value="">Select</option>
<option>1st Semester</option>
<option>2nd Semester</option>
<option>3rd Semester</option>
<option>4th Semester</option>
<option>5th Semester</option>
<option>6th Semester</option>
<option>7th Semester</option>
<option>8th Semester</option>
</select>

<label>Reg. No</label>
<input type="text" name="student_regno" required>

<button type="submit">Register</button>

<p style="text-align:center;margin-top:12px">
Already have an account?
<a href="dep.php" style="font-weight:600">Login here</a>
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
