   <div class="profile-popup" id="profilePopup">
    <img src="<?php echo $photo; ?>">
    <h3><?php echo htmlspecialchars($student_name); ?></h3>
    <p><b>Register No:</b> <?php echo htmlspecialchars($register_no); ?></p>
    <p><b>Department:</b> <?php echo htmlspecialchars($department); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
    <button class="btn btn-dark mt-2" id="closePopup">Close</button>

<?php
session_start();

/* DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "fmrkit");
if ($conn->connect_error) {
    die("Database Connection Failed");
}

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: dep.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FETCH STUDENT DATA (SAFE) */
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student data not found");
}

/* SAFE FIELD MAPPING */
$student_name = $student['student_name']
             ?? $student['name']
             ?? 'Student';

$register_no  = $student['student_regno'] ?? 'N/A';
$department   = $student['student_dept'] ?? 'N/A';

$email        = $student['student_email']
             ?? $student['email']
             ?? 'N/A';
 /* CHANGE PROFILE PHOTO */
if (isset($_POST['change_photo'])) {

    if (!empty($_FILES['photo']['name'])) {

        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;

        $imageType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($imageType, $allowed)) {

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {

                $update = $conn->prepare(
                    "UPDATE students SET photo=? WHERE id=?"
                );
                $update->bind_param("si", $targetFile, $user_id);
                $update->execute();

                header("Location:student.php");
                exit();
            }
        }
    }
}
$photo = !empty($student['photo'])
       ? $student['photo']
       : 'https://via.placeholder.com/150';


?>

<?php
require_once 'db1.php';

/* ================= FETCH DATA ================= */
$result = mysqli_query($link, "SELECT * FROM books ORDER BY semester,id DESC");

$materials = [
    'Book' => [],
    'Notes' => [],
    'Question' => [],
    'Lab' => []
];

while ($row = mysqli_fetch_assoc($result)) {
    $materials[$row['doc_type']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | College Portal</title>

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ICONS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif;}
html,body{height:100%;}
body{display:flex;flex-direction:column;}

.header-banner img{width:100%;}

.navbar-custom{
    background:#3b003b;
    padding:12px 20px;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:relative;
}

.menu-toggle{display:none;font-size:26px;cursor:pointer;}

.nav-left{display:flex;gap:10px;}
.nav-item{padding:8px 12px;border-radius:8px;cursor:pointer;}
.nav-item:hover{background:rgba(255,255,255,0.2);}

.right-area{display:flex;align-items:center;gap:15px;}

.search-box{
    background:white;
    border-radius:25px;
    padding:6px 14px;
    display:flex;
    align-items:center;
}
.search-box input{border:none;outline:none;}

.profile-area{display:flex;align-items:center;gap:10px;cursor:pointer;}
.profile-area img{width:42px;height:42px;border-radius:50%;border:2px solid white;}

.profile-menu{
    position:absolute;
    right:20px;
    top:75px;
    width:180px;
    background:white;
    border-radius:12px;
    display:none;
    z-index:2000;
}
.profile-menu li{padding:12px;cursor:pointer;}
.profile-menu li:hover{background:#eee;}
.logout{color:red;}

.profile-popup{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:white;
    padding:25px;
    border-radius:15px;
    text-align:center;
    display:none;
    z-index:3000;
}

.profile-popup img{
    width:100px;
    height:100px;
    border-radius:50%;
    border:3px solid #3b003b;
}

@media(max-width:768px){
    .menu-toggle{display:block;}
    .nav-left{
        display:none;
        flex-direction:column;
        background:#3b003b;
        position:absolute;
        top:70px;
        left:0;
        width:100%;
    }
    .nav-left.active{display:flex;}
    .profile-name{display:none;}
}

footer{margin-top:auto;background:#6e1245;color:white;text-align:center;}
</style>
</head>

<body>

<div class="header-banner">
    <img src="./image/mrklogo.jpg">
</div>

<nav class="navbar-custom">
    <i class="fa fa-bars menu-toggle" id="menuBtn"></i>

    <div class="nav-left" id="navLeft">
 <div class="nav-item" onclick="location.href='home.html'"><i class="fa fa-home"></i> Home</div>
        <div class="nav-item" onclick="location.href='books.html'"><i class="fa fa-book"></i> Book</div>
        <div class="nav-item" onclick="location.href='notes.html'"><i class="fa fa-sticky-note"></i> Notes</div>
        <div class="nav-item" onclick="location.href='questionsbank.html'"><i class="fa fa-file-alt"></i> Question Bank</div>
        <div class="nav-item" onclick="location.href='labmanuals.html'"><i class="fa fa-flask"></i> Lab Manuals</div>
    </div>

    <div class="right-area">
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class="fa fa-search text-dark"></i>
        </div>

        <div class="profile-area" id="profileBtn">
            <img src="<?php echo $photo; ?>">
            <span class="profile-name"><?php echo htmlspecialchars($student_name); ?></span>
        </div>
    </div>
</nav>

<ul class="profile-menu" id="profileMenu">
    <li id="viewAccount"><i class="fa fa-user"></i> My Account</li>
    <li id="changePhotoBtn"><i class="fa fa-image"></i> Change Photo</li>
    <li class="logout"><i class="fa fa-sign-out-alt"></i> Logout</li>
</ul>


    <div class="profile-popup" id="profilePopup">
    <img src="<?php echo $photo; ?>">
    <h3><?php echo htmlspecialchars($student_name); ?></h3>
    <p><b>Register No:</b> <?php echo htmlspecialchars($register_no); ?></p>
    <p><b>Department:</b> <?php echo htmlspecialchars($department); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
    <button class="btn btn-dark mt-2" id="closePopup">Close</button>
</div>

<form id="photoForm" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="file" name="photo" id="photoInput" accept="image/*">
    <input type="hidden" name="change_photo" value="1">
</form>



<footer class="py-3">
    © 2025 MRK Institute of Technology | All Rights Reserved
</footer>

<script>
menuBtn.onclick = () => navLeft.classList.toggle("active");

profileBtn.onclick = () => {
    profileMenu.style.display =
        profileMenu.style.display === "block" ? "none" : "block";
};

document.addEventListener("click", e => {
    if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
        profileMenu.style.display = "none";
    }
});

viewAccount.onclick = () => profilePopup.style.display = "block";
closePopup.onclick = () => profilePopup.style.display = "none";

/* CHANGE PHOTO */
changePhotoBtn.onclick = () => photoInput.click();

photoInput.onchange = () => {
    if (photoInput.files.length > 0) {
        photoForm.submit();
    }
};

/* LOGOUT */
document.querySelector(".logout").onclick = () =>
    window.location.replace("dep.php");
</script>


</body>
</html>
