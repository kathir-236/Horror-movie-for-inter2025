<?php
session_start();

/* DB CONNECTION */
$conn = new mysqli("localhost","root","","mrkit");
if($conn->connect_error){
    die("DB Error");
}

$error = "";

/* LOGIN LOGIC */
if(isset($_POST['login'])){
// AFTER successful login


    $staff_id = trim($_POST['staff_id']);
    $email    = trim($_POST['staff_email']);
    $pass     = trim($_POST['staff_password']);
    $dept     = trim($_POST['selected_dept']);

    $stmt = $conn->prepare(
        "SELECT staff_name, staff_password, department 
         FROM staff 
         WHERE staff_id=? AND staff_email=? AND department=?"
    );
    $stmt->bind_param("sss",$staff_id,$email,$dept);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1){
        $row = $res->fetch_assoc();
        if(password_verify($pass,$row['staff_password'])){
            $_SESSION['staff_id']   = $staff_id;
            $_SESSION['dept']       = $row['department'];
            $_SESSION['staff_name'] = $row['staff_name'];
            $_SESSION['success'] = "Welcome ".$row['staff_name']."! Login Successful 🎉";
        }else{
            $error = "❌ Invalid Password";
        }
    }else{
        $error = "❌ Invalid credentials or wrong department selected";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MRK Institute | Staff Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#7b2cff;
  --secondary:#c7a4ff;
  --bg:linear-gradient(135deg,#5d0a5de1,#f5f4f453);
}
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{background:var(--bg);min-height:100vh;display:flex;flex-direction:column;}
.top-header{min-height:72px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55));color:#fff;}
.header-left{display:flex;align-items:center;gap:10px;}
.logo{width:40px;height:40px;border-radius:50%;background:#fff;padding:4px;}
.back-btn{background:transparent;border:2px solid #fff;color:#fff;padding:6px 16px;border-radius:22px;cursor:pointer;}
.title {
  text-align: center;
  margin: 2rem 1rem;
  font-size: clamp(2rem, 5vw, 3rem); /* bigger on all screens */
  color: #fff;
  font-weight: 700; /* bolder */
}

main{ flex:1; }
.container{ padding:1rem; }
.cards{ display:grid; gap:1.6rem; justify-content:center; }
.card{ position:relative; border-radius:22px; overflow:hidden; background:#fff; box-shadow:0 12px 26px rgba(123,44,255,.25); animation:float 4s ease-in-out infinite; width:100%; max-width:280px; }
@keyframes float{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-8px); } }
.card img{ width:100%; height:180px; object-fit:fill; }
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
.card:hover .card-data,.card.active .card-data{ 
  opacity:1; 
  transform:translateY(-12px); 
}
.login-btn{ 
  margin-top:.6rem; 
  padding:.5rem 1.6rem; 
  background:linear-gradient(135deg,#7b2cff,#b388ff); 
  color:#fff; 
  border-radius:22px; 
  cursor:pointer; 
}

@media(min-width:600px){ .cards{ grid-template-columns:repeat(2,280px); } }
@media(min-width:900px){ .cards{ grid-template-columns:repeat(3,280px); } }
@media(min-width:1400px){ .cards{ grid-template-columns:repeat(4,280px); } }

/* LOGIN MODAL */
.modal{ 
  position:fixed; 
  inset:0; 
  background:rgba(0,0,0,.6); display:flex; 
  justify-content:center; 
  align-items:center; 
  opacity:0; 
  pointer-events:none; 
  z-index:1000; 
}
.modal.active{ 
  opacity:1; 
  pointer-events:auto; 
}
.modal-box{ 
  background:#fff;
  padding:1.6rem; 
  width:92%; 
  max-width:360px;
  border-radius:22px; 
  transform:scale(.8); 
  transition:.35s; 
}
.modal.active .modal-box{ 
  transform:scale(1); 
}
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
.close{ 
  text-align:center;
  margin-top:1rem; 
  color:#777; 
  cursor:pointer; 
}
#error-msg{
  text-align:center; 
  color:red; 
  margin:1rem 0; 
  font-weight:600; 
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

footer{ 
  background:linear-gradient(135deg,#fefcff,#5307617c);
  text-align:center; 
  padding:1rem; 
}

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

/* H2 CHOOSE DEPT */
h2 {
  text-align: center;
  padding: 20px;
  color: #fff;
  font-weight: 700;
  font-size: clamp(2rem, 5vw, 3rem);
}

/* ===== Modern Popup ===== */
.popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.8);
  background: linear-gradient(135deg,var(--primary),var(--secondary));
  color: #fff;
  padding: 1.2rem 2rem;
  border-radius: 16px;
  box-shadow: 0 12px 30px rgba(0,0,0,.3);
  z-index: 5000;
  opacity: 0;
  pointer-events: none;
  text-align: center;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.4s ease;
}
.popup.show{
  opacity: 1;
  pointer-events: auto;
  transform: translate(-50%, -50%) scale(1);
}
.popup .close-popup{ margin-left:12px; cursor:pointer; font-weight:700; }

.dept-image{
  height:180px;
  background:linear-gradient(90deg,#f2f2f2 50%, #d9d9d9 50%);
  display:flex;
  justify-content:center;
  align-items:center;
}

.dept-content{
  text-align:center;
}

.dept-content h1{
  font-size:36px;
  font-weight:700;
  letter-spacing:1px;
}

.dept-content span{
  display:block;
  width:50px;
  height:3px;
  background:#e91e63;
  margin:6px auto 8px;
  border-radius:2px;
}

.dept-content p{
  font-size:16px;
  font-weight:500;
}

footer{ background:linear-gradient(135deg,#fefcff,#5307617c); text-align:center; padding:1rem; }
</style>
</head>

<body>

<header class="top-header">
  <div class="header-left">
    <img src="./image/book.jpg" class="logo">
    <strong>MRK Institute of Technology</strong>
  </div>
  <button class="back-btn" onclick="location.href='staffregister.php'">← Back</button>
</header>

<h2>Choose Department</h2>

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
      <div class='login-btn' onclick=\"openLogin('$code')\">Staff Login</div>
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
      <input name="staff_id" placeholder="Staff ID" required>
      <input name="staff_email" type="email" placeholder="Email" required>
      <div class="pass-box">
        <input name="staff_password" type="password" id="staffPass" placeholder="Password" required>
        <i class="fa-solid fa-eye" id="eyeIcon" onclick="togglePassword()"></i>
      </div>
      <input type="hidden" name="selected_dept" id="selectedDept">
      <button name="login">Login</button>
    </form>
    <div class="close" onclick="closeLogin()">Cancel</div>
  </div>
</div>

<!-- ERROR MODAL -->
<?php if($error): ?>
<div class="modal active">
  <div class="modal-box">
    <p style="color:red;text-align:center"><?= $error ?></p>
    <div class="close" onclick="location.href='staffdep.php'">Close</div>
  </div>
</div>
<?php endif; ?>

<!-- Modern Popup -->
<div id="successPopup" class="popup">
  <span id="popupMessage"></span>
  <span class="close-popup" onclick="closePopup()">×</span>
</div>

<button id="scrollTopBtn">↑</button>
<footer>© 2025 MRK Institute of Technology</footer>
<script>
function openLogin(d){
  document.getElementById("deptTitle").innerText=d+" Staff Login";
  document.getElementById("selectedDept").value=d;
  document.getElementById("loginModal").classList.add("active");
  document.body.style.overflow="hidden";
}
function closeLogin(){
  document.getElementById("loginModal").classList.remove("active");
  document.body.style.overflow="auto";
}

const btn=document.getElementById("scrollTopBtn");
window.onscroll=()=>btn.style.display=scrollY>200?"block":"none";
btn.onclick=()=>window.scrollTo({top:0,behavior:"smooth"});

function togglePassword(){
  const pass = document.getElementById("staffPass");
  const icon = document.getElementById("eyeIcon");
  if(pass.type === "password"){
    pass.type = "text";
    icon.classList.replace("fa-eye","fa-eye-slash");
  }else{
    pass.type = "password";
    icon.classList.replace("fa-eye-slash","fa-eye");
  }
}

// Modern popup functions
function showPopup(message){
  const popup = document.getElementById('successPopup');
  const msg = document.getElementById('popupMessage');
  msg.innerText = message;
  popup.classList.add('show');
  setTimeout(() => {
    popup.classList.remove('show');
  }, 3000);
}
function closePopup(){
  document.getElementById('successPopup').classList.remove('show');
}

// Trigger popup if PHP session success exists
<?php if(isset($_SESSION['success'])): ?>
showPopup("<?= $_SESSION['success'] ?>");
setTimeout(()=>{location.href="staff.php";}, 3000);
<?php unset($_SESSION['success']); endif; ?>
</script>

</body>
</html>
