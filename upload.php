<?php
session_start();

/* ================= DB CONNECTION ================= */
$conn = new mysqli("localhost", "root", "", "mrkit");
if ($conn->connect_error) {
    die("Database Error");
}

/* ================= STAFF AUTH CHECK ================= */
if (!isset($_SESSION['staff_id'])) {
    die("❌ Unauthorized access");
}
$staff_id   = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

/* ================= HANDLE FORM ================= */
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $staff_id   = $_SESSION['staff_id'];
    $staff_name = $_SESSION['staff_name']; // must exist
    $title        = $_POST["title"];
    $subject_name = $_POST["subject_name"];
    $subject_code = $_POST["subject_code"];
    $department   = $_POST["department"];
    $regulations  = $_POST["regulations"];
    $semester     = $_POST["semester"];
    $content_type = $_POST["content_type"];
    $youtube_link = $_POST["youtube_link"] ?? null;

    /* ===== PDF UPLOAD ===== */
    if (!is_dir("uploads")) mkdir("uploads", 0755, true);

    $pdf_name = time() . "_" . basename($_FILES["pdf"]["name"]);
    move_uploaded_file($_FILES["pdf"]["tmp_name"], "uploads/" . $pdf_name);

    /* ===== IMAGE UPLOAD ===== */
    if (!is_dir("uploads/images")) mkdir("uploads/images", 0755, true);
    $image_name = "default.png";

    if (!empty($_FILES["image"]["name"])) {
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/images/" . $image_name);
    }

    /* ================= INSERT (SINGLE, CORRECT) ================= */
    /* ================= INSERT (CORRECTED) ================= */
$stmt = $conn->prepare("
    INSERT INTO materials 
    (staff_id, title, subject_name, subject_code, department, regulations, semester, content_type, pdf_file, image_base64, youtube_link, upload_time)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

// The first parameter should be a string of data types (i - integer, s - string)
// You have 11 bind variables (excluding NOW() which is handled by MySQL)
$stmt->bind_param(
    "issssssssss",  // 11 characters: i for staff_id (integer), s for all strings
    $staff_id,      // i - integer
    $title,         // s - string
    $subject_name,  // s - string
    $subject_code,  // s - string
    $department,    // s - string
    $regulations,   // s - string
    $semester,      // s - string
    $content_type,  // s - string
    $pdf_name,      // s - string
    $image_name,    // s - string
    $youtube_link   // s - string
);

$stmt->execute();
$stmt->close();
    $success = true;
    // after successful insert
$_SESSION['upload_success'] = "Material uploaded successfully!";
header("Location: upload.php");   // stay here to show popup
exit();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📚 Student Materials Upload</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#667eea,#764ba2);
    min-height:100vh;
}
.container{
    max-width:900px;
    margin:100px auto;
    background:#fff;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,.2);
    overflow:hidden;
}
.header{
    background:#1f2d3d;
    color:#fff;
    padding:30px;
    text-align:center;
}
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    padding:30px;
}
label{font-weight:600}
input,select{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:2px solid #ddd;
}
input[type=file]{border:2px dashed #667eea}
button{
    grid-column:span 2;
    padding:18px;
    background:#27ae60;
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:20px;
    font-weight:700;
    cursor:pointer;
}
.preview img{
    max-width:180px;
    margin-top:10px;
    border-radius:12px;
}
.message{
    margin:20px;
    padding:15px;
    text-align:center;
    background:#d4edda;
    color:#155724;
    font-weight:700;
    border-radius:10px;
}
@media(max-width:768px){
    .form-grid{grid-template-columns:1fr}
    button{grid-column:span 1}
}
/* ===== FIXED TOP HEADER ===== */
.top-header{
position:fixed;         /* FIXED */
top:0;
left:0;
width:100%;
height:72px;
padding:12px 20px;
display:flex;
align-items:center;
justify-content:space-between;
background:linear-gradient(rgba(0,0,0,.65), rgba(0,0,0,.65));
color:#fff;
z-index:1000;
}

/* HEADER CONTENT */
.header-left{
display:flex;
align-items:center;
gap:12px;
}
.logo{
width:42px;
height:42px;
border-radius:50%;
background:#fff;
padding:5px;
object-fit:contain;
}
.back-btn{
background:transparent;
border:2px solid #fff;
color:#fff;
padding:8px 18px;
border-radius:22px;
font-size:15px;
cursor:pointer;
transition:.3s;
}
.back-btn:hover{
background:#fff;
color:#000;
}

/* MOBILE HEADER */
@media(max-width:600px){
.top-header{
flex-direction:column;
height:auto;
padding:12px;
gap:8px;
}
}

/* ===== PAGE WRAPPER ===== */
.page{
padding-top:90px;   /* PUSH CONTENT BELOW FIXED HEADER */
padding-left:20px;
padding-right:20px;
padding-bottom:20px;
}

/* SweetAlert Toast Animation */
.swal2-show {
    animation: slideIn 0.4s ease-out;
}

.swal2-hide {
    animation: slideOut 0.3s ease-in;
}

@keyframes slideIn {
    from { transform: translateX(120px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to   { transform: translateX(120px); opacity: 0; }
}

</style>
</head>

<body>
<header class="top-header">
  <div class="header-left">
    <img src="./image/book.jpg" class="logo">
    <strong>MRK Institute of Technology</strong>
  </div>
  <button class="back-btn" onclick="goBack()">← Back</button>
</header> 

<div class="container">
<div class="header">
<h1>📚 Upload Student Material</h1>
</div>



<form method="POST" enctype="multipart/form-data">
<div class="form-grid">

<div>
<label>Title *</label>
<input type="text" name="title" required>
</div>

<div>
<label>Subject Name *</label>
<input type="text" name="subject_name" required>
</div>

<div>
<label>Subject Code *</label>
<input type="text" name="subject_code" required>
</div>

<div>
<label>Department *</label>
<select name="department" required>
<option>CSE</option><option>IT</option>
<option>ECE</option><option>EEE</option>
<option>MECH</option><option>CIVIL</option>
</select>
</div>

<div>
<label>Regulation *</label>
<input type="text" name="regulations" required>
</div>

<div>
<label>Semester *</label>
<select name="semester" required>
<option>I</option><option>II</option><option>III</option>
<option>IV</option><option>V</option>
<option>VI</option><option>VII</option><option>VIII</option>
</select>
</div>

<div>
<label>Content Type *</label>
<select name="content_type" required>
<option>Book</option>
<option>Notes</option>
<option>Question Paper</option>
<option>Lab Manual</option>
<option>Lesson Plan</option>
</select>
</div>

<div>
<label>YouTube Link</label>
<input type="url" name="youtube_link">
</div>

<div>
<label>PDF File *</label>
<input type="file" name="pdf" accept=".pdf" required>
</div>

<div>
<label>Cover Image</label>
<input type="file" name="image" accept="image/*">
<div class="preview">
<img id="previewImg">
</div>
</div>

<input type="hidden" name="image_base64" id="imageBase64">

<button>🚀 Upload</button>
</div>
</form>
</div>
<?php if(isset($_SESSION['upload_success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Uploaded 🎉',
    text: '<?php echo $_SESSION['upload_success']; ?>',
    toast: true,
    position: 'top-end',
    timer: 2500,              // ⏱ show for 2.5 seconds
    showConfirmButton: false,

    showClass: {
        popup: 'swal2-show'
    },
    hideClass: {
        popup: 'swal2-hide'
    },

    willClose: () => {
        window.location.href = "staff.php";   // ✅ AUTO REDIRECT
    }
});
</script>
<?php unset($_SESSION['upload_success']); ?>
<?php endif; ?>



<script>
const DEFAULT_IMAGE = "./image/mrk-logo.png";
let imageBase64 = "";

fetch(DEFAULT_IMAGE)
.then(r=>r.blob())
.then(b=>{
    const fr = new FileReader();
    fr.onload = ()=>{
        imageBase64 = fr.result;
        imageBase64Field.value = imageBase64;
        previewImg.src = imageBase64;
    };
    fr.readAsDataURL(b);
});

imageInput.onchange = e=>{
    const fr = new FileReader();
    fr.onload = ()=>{
        imageBase64 = fr.result;
        imageBase64Field.value = imageBase64;
        previewImg.src = imageBase64;
    };
    fr.readAsDataURL(e.target.files[0]);
};

const imageBase64Field = document.getElementById("imageBase64");
function goBack(){
history.length>1?history.back():location.href="staff.php";
}

</script>

</body>
</html>
