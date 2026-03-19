<?php
session_start();

/* DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "mrkit");
if ($conn->connect_error) {
    die("Database Connection Failed");
}

$error = "";

/* LOGIN PROCESS */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {

    $email    = $_POST['student_email'];
    $password = $_POST['student_password'];
    $dept     = $_POST['selected_dept'];

    $stmt = $conn->prepare(
        "SELECT id, student_name, student_password, student_dept 
         FROM students 
         WHERE student_email = ? AND student_dept = ?"
    );
    $stmt->bind_param("ss", $email, $dept);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['student_password'])) {
            $error = "❌ Invalid email or password";
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['student_name'];
            $_SESSION['dept']      = $user['student_dept'];
            $_SESSION['success']   = "✅ Welcome, ".$user['student_name']."!";
        }
    } else {
        $error = "❌ Invalid email, password, or department";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MRK Institute | Student Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#7b2cff;
  --bg:linear-gradient(135deg,#5d0a5de1,#f5f4f453);
}
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif}
body{background:var(--bg);min-height:100vh;display:flex;flex-direction:column}

/* HEADER */
.top-header{
  min-height:72px;
  padding:12px 16px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55));
  color:#fff;
}
.header-left{display:flex;align-items:center;gap:10px}
.logo{width:40px;height:40px;border-radius:50%;background:#fff;padding:4px}
.back-btn{background:transparent;border:2px solid #fff;color:#fff;padding:6px 16px;border-radius:22px;cursor:pointer}

/* TITLE */
.title{
  text-align:center;
  margin:2rem 1rem;
  font-size:clamp(2rem,5vw,3rem);
  color:#fff;
  font-weight:700;
}

/* CARD GRID */
.container{padding:1rem}
.cards{
  display:grid;
  gap:1.6rem;
  justify-content:center;
}
@media(min-width:600px){.cards{grid-template-columns:repeat(2,280px)}}
@media(min-width:900px){.cards{grid-template-columns:repeat(3,280px)}}
@media(min-width:1400px){.cards{grid-template-columns:repeat(4,280px)}}

/* CARD */
.card{
  position:relative;
  border-radius:22px;
  overflow:hidden;
  background:#fff;
  box-shadow:0 12px 26px rgba(123,44,255,.25);
  animation:float 4s ease-in-out infinite;
  width:100%;
  max-width:280px;
}
@keyframes float{
  0%,100%{transform:translateY(0)}
  50%{transform:translateY(-8px)}
}

/* DEPARTMENT IMAGE STYLE */
.dept-image{
  height:180px;
  background:linear-gradient(90deg,#f1f1f1 50%,#d9d9d9 50%);
  display:flex;
  justify-content:center;
  align-items:center;
}
.dept-content{text-align:center}
.dept-content h1{
  font-size:36px;
  font-weight:700;
  letter-spacing:1px;
}
.dept-content span{
  display:block;
  width:48px;
  height:3px;
  background:#e91e63;
  margin:6px auto 8px;
  border-radius:2px;
}
.dept-content p{
  font-size:15px;
  font-weight:500;
}

/* CARD DATA */
.card-data{
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  margin:auto;
  width:88%;
  background:#fff;
  padding:1rem;
  border-radius:16px;
  text-align:center;
  box-shadow:0 10px 25px rgba(0,0,0,.2);
  opacity:0;
  transform:translateY(30px);
  transition:.35s;
}
.card:hover .card-data{
  opacity:1;
  transform:translateY(-12px);
}
.login-btn{
  padding:.5rem 1.6rem;
  background:linear-gradient(135deg,#7b2cff,#b388ff);
  color:#fff;
  border-radius:22px;
  cursor:pointer;
}

/* MODALS */
.modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.6);
  display:flex;
  justify-content:center;
  align-items:center;
  opacity:0;
  pointer-events:none;
  transition:.3s;
  z-index:1000;
}
.modal.active{opacity:1;pointer-events:auto}
.modal-box{
  background:#fff;
  padding:1.6rem;
  width:90%;
  max-width:360px;
  border-radius:22px;
  transform:scale(.8);
  transition:.35s;
}
.modal.active .modal-box{transform:scale(1)}
.modal-box input{
  width:100%;
  padding:.7rem;
  margin:.5rem 0;
  border-radius:12px;
  border:1px solid #ccc;
}
.modal-box button{
  width:100%;
  padding:.7rem;
  background:linear-gradient(135deg,#7b2cff,#b388ff);
  color:#fff;
  border:none;
  border-radius:22px;
  margin-top:.8rem;
}
.close{text-align:center;margin-top:1rem;color:#777;cursor:pointer}
.pass-box{position:relative}
.pass-box i{
  position:absolute;
  right:15px;
  top:50%;
  transform:translateY(-50%);
  cursor:pointer;
}
/* SCROLL TOP BUTTON */
#scrollTopBtn{ 
  position:fixed; 
  right:18px;
  bottom:22px;
  width:48px;
  height:48px; 
  border-radius:50%; 
  border:none; 
  cursor:pointer; 
  font-size:22px;
  color:#fff; 
  background:linear-gradient(135deg,#7b2cff,#b388ff); 
  box-shadow:0 10px 25px rgba(0,0,0,.35); 
  display:none; 
  z-index:3000; 
  transition:.3s; 
}
#scrollTopBtn:hover{ transform:scale(1.1); }
@media(max-width:600px){ #scrollTopBtn{ width:42px; height:42px; font-size:18px; } }
footer{text-align:center;padding:1rem;background:linear-gradient(135deg,#fefcff,#5307617c)}
</style>
</head>

<body>

<header class="top-header">
  <div class="header-left">
    <img src="./image/book.jpg" class="logo">
    <strong>MRK Institute of Technology</strong>
  </div>
  <button class="back-btn" onclick="history.back()">← Back</button>
</header>

<div class="title">Choose Department</div>

<?php if($error): ?>
<div class="modal active">
  <div class="modal-box">
    <p style="color:red;text-align:center;font-weight:600"><?= $error ?></p>
    <div class="close" onclick="this.closest('.modal').classList.remove('active')">Close</div>
  </div>
</div>
<?php endif; ?>

<div class="container">
<div class="cards">
<?php
$departments = [
  "CSE"   => "Computer Science Engineering",
  "ECE"   => "Electronics & Communication Engineering",
  "EEE"   => "Electrical & Electronics Engineering",
  "MECH"  => "Mechanical Engineering",
  "CIVIL" => "Civil Engineering",
  "AIDS"  => "Artificial Intelligence & Data Science",
  "AIML"  => "Artificial Intelligence & Machine Learning",
  "IT"    => "Information Technology",
  "BME"   => "Biomedical Engineering"
];

foreach($departments as $code => $name){
  echo "
  <div class='card'>
    <div class='dept-image'>
      <div class='dept-content'>
        <h1>$code</h1>
        <span></span>
        <p>$name</p>
      </div>
    </div>

    <div class='card-data'>
      <div class='login-btn' onclick=\"openLogin('$code')\">Student Login</div>
    </div>
  </div>";
}
?>
</div>
</div>

<!-- LOGIN MODAL -->
<div class="modal" id="loginModal">
  <div class="modal-box">
    <h3 id="deptTitle"></h3>
    <form method="POST">
      <input type="email" name="student_email" placeholder="Email" required>
      <div class="pass-box">
        <input type="password" name="student_password" id="password" placeholder="Password" required>
        <i class="fa-solid fa-eye" onclick="togglePassword()"></i>
      </div>
      <input type="hidden" name="selected_dept" id="selectedDeptInput">
      <button name="login">Login</button>
    </form>
    <div class="close" onclick="closeLogin()">Cancel</div>
  </div>
</div>

<button id="scrollTopBtn">↑</button>
<?php if(!empty($_SESSION['success'])): ?>
<div class="modal active">
  <div class="modal-box">
    <p style="text-align:center;font-weight:600"><?= $_SESSION['success'] ?></p>
  </div>
</div>
<script>
setTimeout(()=>location.href="student.php",2000);
</script>
<?php unset($_SESSION['success']); endif; ?>

<script>
function openLogin(dept){
  document.getElementById("deptTitle").innerText = dept+" Student Login";
  document.getElementById("selectedDeptInput").value = dept;
  document.getElementById("loginModal").classList.add("active");
}
function closeLogin(){
  document.getElementById("loginModal").classList.remove("active");
}
function togglePassword(){
  const p=document.getElementById("password");
  p.type = p.type==="password" ? "text" : "password";
}
const btn=document.getElementById("scrollTopBtn");
window.onscroll=()=>btn.style.display=scrollY>200?"block":"none";
btn.onclick=()=>window.scrollTo({top:0,behavior:"smooth"});
</script>

</body>
</html>
