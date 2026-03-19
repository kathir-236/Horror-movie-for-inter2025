<?php
session_start();

/* DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "fmrkit");
if ($conn->connect_error) die("Database Connection Failed");

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: dep.php");
    exit();
}
$user_id = $_SESSION['user_id'];

/* ROLE-BASED ACCESS CONTROL: Block delete attempts from students */
if(isset($_POST['delete_material'])){
    die("❌ Access Denied! Only staff can delete materials.");
}

/* FETCH STUDENT DATA */
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
if (!$student) die("Student data not found");

/* SAFE FIELD MAPPING */
$student_name = $student['student_name'] ?? $student['name'] ?? 'Student';
$register_no  = $student['student_regno'] ?? 'N/A';
$department   = $student['student_dept'] ?? 'N/A';
$email        = $student['student_email'] ?? $student['email'] ?? 'N/A';

/* CHANGE PROFILE PHOTO */
if (isset($_POST['change_photo']) && !empty($_FILES['photo']['name'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (in_array($imageType, $allowed) && move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        $update = $conn->prepare("UPDATE students SET photo=? WHERE id=?");
        $update->bind_param("si", $targetFile, $user_id);
        $update->execute();
        header("Location: student.php");
        exit();
    }
}
$photo = !empty($student['photo']) ? $student['photo'] : 'https://via.placeholder.com/150';

/* FETCH MATERIALS */
$department = $student['student_dept'];

// ================= Fetch uploaded materials =================
$materials = [];
$plans = [];
$sql = "SELECT id,title,semester,content_type,pdf_file,image_base64,youtube_link,
        subject_name,subject_code,department
        FROM materials
        WHERE department = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $department);
$stmt->execute();
$res = $stmt->get_result();

if($res){
    while($row = $res->fetch_assoc()){
        $ct = $row['content_type'];
        switch(trim($ct)){
            case 'Book': case 'Books': $key = '📘 Book'; break;
            case 'Notes': case 'Note': $key = '📝 Note'; break;
            case 'Question Paper': case 'Question Papers': $key = '❓ Question Paper'; break;
            case 'Lab Manual': case 'Lab Manuals': $key = '🧪 Lab Manual'; break;
            case 'Lesson Plan': case 'Lesson Plans': $key = '📅 Lesson Plan'; break;
            default: $key = $ct;
        }
        $sem_raw = trim($row['semester']);
        $sem_map = ['I'=>'1','II'=>'2','III'=>'3','IV'=>'4','V'=>'5','VI'=>'6','VII'=>'7','VIII'=>'8','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8'];
        $sem_num = $sem_map[$sem_raw] ?? (is_numeric($sem_raw)?$sem_raw:999);

        $item = [
            'id'=>$row['id'],
            'name'=>$row['title'],
            'semester'=>$row['semester'],
            'sem_num'=>$sem_num,
            'pdf'=>$row['pdf_file'],
            'img'=>$row['image_base64'],
            'youtube'=>$row['youtube_link'],
            'subject_name'=>$row['subject_name'],
            'subject_code'=>$row['subject_code'],
            'department'=>$row['department']
        ];
        $materials[$key][] = $item;
    }
}

// sort each category by semester number
function sem_to_num($s){ $s = trim((string)$s); $m=['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8]; return $m[strtoupper($s)] ?? (is_numeric($s)?(int)$s:999); }
foreach($materials as $cat=>&$items){ usort($items, function($a,$b){ $na=sem_to_num($a['sem_num'] ?? $a['semester']); $nb=sem_to_num($b['sem_num'] ?? $b['semester']); return $na <=> $nb; }); } unset($items);


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | College Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif;}
html,body{height:100%;scroll-behavior:smooth;}
body{display:flex;flex-direction:column;}
.header-banner img{width:100%;height:auto;object-fit:cover;}

/* Navbar */
.navbar-custom{
    background:#3b003b;
    padding:12px 20px;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:2000;
    gap:10px;
}
.menu-toggle{display:none;font-size:26px;cursor:pointer;}
.nav-left{display:flex;gap:10px;}
.nav-item{padding:8px 12px;border-radius:8px;cursor:pointer;white-space:nowrap;}
.nav-item:hover{background:rgba(255,255,255,0.2);}

/* Search Bar */
.search-wrapper { position: relative; display:flex; align-items:center; width:180px; }
.search-wrapper input { padding: 6px 12px 6px 32px; border-radius:6px; border:none; width:100%; }
.search-wrapper .search-icon { position:absolute; left:10px; font-size:14px; color:#666; cursor:pointer; transition:color 0.2s; }
.search-wrapper .search-icon:hover { color:#000; }

/* Profile */
.right-area{display:flex;align-items:center;gap:15px;}
.profile-area{display:flex;align-items:center;gap:10px;cursor:pointer;}
.profile-area img{width:42px;height:42px;border-radius:50%;border:2px solid white;}
.profile-menu{position:absolute;right:20px;top:75px;width:180px;background:white;border-radius:12px;display:none;z-index:2000;}
.profile-menu li{padding:12px;cursor:pointer;}
.profile-menu li:hover{background:#eee;}
.logout{color:red;}
.profile-popup{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:25px;border-radius:15px;text-align:center;display:none;z-index:3000;width:90%;max-width:350px;}
.profile-popup img{width:100px;height:100px;border-radius:50%;border:3px solid #3b003b;}

/* Semester Pills */
.semester-bar{
    position:sticky; top:60px; z-index:1500; padding:10px;
    background:#e9eff7; display:flex; flex-wrap:wrap; gap:10px;
    justify-content:left; align-items:center; border-bottom:1px solid #ccc;
}
.sem-pill{
    padding:8px 16px; border:1px solid #9aa7b6; border-radius:20px;
    background:#f8fbff; cursor:pointer; font-size:14px; white-space:nowrap;
}
.sem-pill.active{ background:#1f2d3d; color:white; }

/* Cards */
.container{max-width:1200px;margin:auto;padding:20px;}
section{margin-bottom:50px;padding:60px 20px;border-radius:12px;}
section h2, section h3{margin-bottom:15px;}
.cards-wrapper{display:flex;overflow-x:auto;scroll-behavior:smooth;gap:20px;position:relative;}
.card{min-width:240px;background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,0.08);overflow:hidden;transition:0.3s;flex-shrink:0;}
.card:hover{transform:translateY(-6px);box-shadow:0 14px 32px rgba(0,0,0,0.12);}
.card-top{background:#eef3fb;padding:30px;text-align:center;}
.card-top img{width:60px;}
.card-body{padding:15px;text-align:center;}
.card-body h5{margin:6px 0;}
.card-body p{margin:2px 0;font-size:13px;color:#666;}
.scroll-btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(31,45,61,0.8);color:white;border:none;padding:10px;border-radius:50%;cursor:pointer;z-index:10;}
.scroll-btn.left{left:-15px;}
.scroll-btn.right{right:-15px;}
.scroll-btn:hover{background:rgba(31,45,61,1);}

/* Footer */
footer{margin-top:auto;background:#6e1245;color:white;text-align:center;padding:15px 10px;}

/* Responsive */
@media(max-width:768px){
    .menu-toggle{display:block;}
    .nav-left{display:none;flex-direction:column;background:#3b003b;position:absolute;top:60px;left:0;width:100%;max-height:calc(100vh - 60px);overflow-y:auto;}
    .nav-left.active{display:flex;}
    .nav-item{padding:12px;text-align:center;}
    .profile-name{display:none;}
    .search-wrapper{width:90%;margin:10px auto;}
}
@media(max-width:480px){
    .card{min-width:160px;}
}
/* Section Colors */
#home { background: #fdf6e3; }       /* light yellow */
#books { background: #e0f7fa; }      /* light cyan */
#notes { background: #fff3e0; }      /* light orange */
#questions { background: #f3e5f5; }  /* light purple */
#labs { background: #e8f5e9; }       /* light green */

/* Responsive */
@media(max-width:768px){
    .menu-toggle{display:block;}
    .nav-left{display:none;flex-direction:column;background:#3b003b;position:absolute;top:60px;left:0;width:100%;max-height:calc(100vh - 60px);overflow-y:auto;}
    .nav-left.active{display:flex;}
    .nav-item{padding:12px;text-align:center;}
    .profile-name{display:none;}
    .search-wrapper{width:90%;margin:10px auto;}
}
@media(max-width:480px){
    .card{min-width:160px;}
}
/* ===== SECTION BASE STYLE ===== */
section{
    margin-bottom:60px;
    padding:30px 15px;
    border-radius:18px;
}

/* Section headings */
section h2{
    font-size:26px;
    font-weight:600;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

/* ===== SECTION COLORS ===== */
#books{
    background:linear-gradient(135deg,#e0f2ff,#f8fbff);
}
#notes{
    background:linear-gradient(135deg,#fff0dc,#fffaf2);
}
#questions{
    background:linear-gradient(135deg,#f3e8ff,#fbf7ff);
}
#labs{
    background:linear-gradient(135deg,#e6f7ec,#f5fff8);
}

/* ===== CARD CONTAINER ===== */
.cards-container{
    position:relative;
    padding:10px 5px;
}

/* Horizontal scroll */
.cards-wrapper{
    display:flex;
    gap:22px;
    overflow-x:auto;
    padding-bottom:10px;
    scroll-behavior:smooth;
}
.cards-wrapper::-webkit-scrollbar{height:6px;}
.cards-wrapper::-webkit-scrollbar-thumb{
    background:#c5cfe0;
    border-radius:6px;
}

/* ===== CARD DESIGN ===== */
.card{
    min-width:260px;
    background:#ffffff;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    overflow:hidden;
    transition:all 0.35s ease;
    border:1px solid #eef2f7;
}

.card:hover{
    transform:translateY(-8px) scale(1.02);
    box-shadow:0 18px 40px rgba(0,0,0,0.15);
}

/* Card top */
.card-top{
    background:linear-gradient(135deg,#eef3ff,#f8faff);
    padding:25px;
    text-align:center;
}

.card-top img{
    width:70px;
    height:70px;
    object-fit:contain;
}

/* Card body */
.card-body{
    padding:18px;
    text-align:center;
}

.card-body h4{
    font-size:17px;
    font-weight:600;
    margin-bottom:6px;
    color:#1f2d3d;
}

.card-body p{
    font-size:13px;
    color:#666;
    margin-bottom:6px;
}

/* Open PDF button */
.card-body a.btn{
    margin-top:8px;
    border-radius:20px;
    font-size:13px;
}

/* Tag */
.tag{
    display:inline-block;
    margin-top:10px;
    padding:5px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:500;
    background:#eef2ff;
    color:#1f2d3d;
}

/* ===== SCROLL BUTTONS ===== */
.scroll-btn{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background:#1f2d3d;
    color:#fff;
    border:none;
    width:42px;
    height:42px;
    border-radius:50%;
    cursor:pointer;
    opacity:0.85;
    transition:0.3s;
    z-index:20;
}

.scroll-btn:hover{
    opacity:1;
    transform:translateY(-50%) scale(1.1);
}

.scroll-btn.left{left:-12px;}
.scroll-btn.right{right:-12px;}

/* ===== MOBILE RESPONSIVE ===== */
@media(max-width:768px){
    section{padding:20px 10px;}
    section h2{font-size:22px;}
    .card{min-width:200px;}
}

@media(max-width:480px){
    .card{min-width:170px;}
}

</style>
</head>
<body>

<div class="header-banner"><img src="./image/mrklogo.jpg" alt="Logo"></div>

<nav class="navbar-custom">
    <i class="fa fa-bars menu-toggle" id="menuBtn"></i>
    <div class="nav-left" id="navLeft">
        <div class="nav-item" onclick="scrollToSection('home')"><i class="fa fa-home"></i> Home</div>
        <div class="nav-item" onclick="scrollToSection('books')"><i class="fa fa-book"></i> Book</div>
        <div class="nav-item" onclick="scrollToSection('notes')"><i class="fa fa-sticky-note"></i> Notes</div>
        <div class="nav-item" onclick="scrollToSection('questions')"><i class="fa fa-file-alt"></i> Question Bank</div>
        <div class="nav-item" onclick="scrollToSection('labs')"><i class="fa fa-flask"></i> Lab Manuals</div>
    </div>

    <div class="right-area">
        <div class="search-wrapper">
            <i class="fa fa-search search-icon" id="searchIcon"></i>
            <input type="text" id="searchInput" placeholder="Search materials...">
        </div>
        <div class="profile-area" id="profileBtn">
            <img src="<?php echo $photo; ?>" alt="Profile">
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
    <img src="<?php echo $photo; ?>" alt="Profile">
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

<!-- Semester Pills -->
<div class="semester-bar">
    <div class="sem-pill active" onclick="filterSemester('all', this)">All Semesters</div>
    <?php for($i=1;$i<=8;$i++): ?>
        <div class="sem-pill" onclick="filterSemester('<?php echo $i;?>', this)">Semester <?php echo $i;?></div>
    <?php endfor; ?>
</div>

<!-- Home Section -->
<section id="home">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>👋</h2>
        <p>Dashboard Overview & Quick Links</p>
    <p style="font-size:16px;font-weight:600;color:#3b003b">
        Department: <?php echo htmlspecialchars($department); ?>
    </p>
   
    </div>
</section>


<div class="container">
<?php 
$sections = ['books'=>'📘 Books','notes'=>'📝 Notes','questions'=>'❓ Question Papers','labs'=>'🧪 Lab Manuals'];
foreach($sections as $id=>$title):
?>
<section id="<?php echo $id; ?>">
    <h2><?php echo $title;?></h2>
    <div class="cards-container">
        <button class="scroll-btn left" type="button" onclick="scrollCards(this,'left')"><i class="fa fa-chevron-left"></i></button>
        <div class="cards-wrapper">
            <?php 
            $typeKey = ucfirst(rtrim($title,'s'));
            if(isset($materials[$typeKey])):
                foreach($materials[$typeKey] as $item): ?>
                    <div class="card" data-sem="<?php echo $item['sem_num']; ?>">
                        <div class="card-top">
                            <?php if(!empty($item['img'])): ?>
                                <img src="<?php echo $item['img']; ?>" alt="Cover" style="max-width:100%;height:120px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                <img src="/fmrkit/uploads/default_thumbnail.jpeg" alt="MRK Thumbnail" style="max-width:100%;height:120px;object-fit:contain;border-radius:8px;">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h4><?php echo htmlspecialchars($item['name'] ?? 'Untitled'); ?></h4>
                            <p><?php echo htmlspecialchars($item['department'] ?? 'CSE'); ?> • Semester <?php echo htmlspecialchars($item['semester']); ?></p>
                            <?php if(!empty($item['pdf'])): ?>
                                <p><a href="uploads/<?php echo htmlspecialchars($item['pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF</a></p>
                            <?php endif; ?>
                            <span class="tag"><?php echo $typeKey; ?></span>
                        </div>
                    </div>
                <?php endforeach;
            endif; ?>
        </div>
        <button class="scroll-btn right" type="button" onclick="scrollCards(this,'right')"><i class="fa fa-chevron-right"></i></button>
    </div>
</section>
<?php endforeach; ?>
</div>

<footer>© 2025 MRK Institute of Technology | All Rights Reserved</footer>

<script>
const menuBtn = document.getElementById('menuBtn');
const navLeft = document.getElementById('navLeft');
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');
const viewAccount = document.getElementById('viewAccount');
const closePopup = document.getElementById('closePopup');
const profilePopup = document.getElementById('profilePopup');
const changePhotoBtn = document.getElementById('changePhotoBtn');
const photoInput = document.getElementById('photoInput');
const photoForm = document.getElementById('photoForm');

menuBtn.onclick = ()=> navLeft.classList.toggle("active");
profileBtn.onclick = ()=> profileMenu.style.display = profileMenu.style.display==="block"?"none":"block";
document.addEventListener("click", e=>{ if(!profileMenu.contains(e.target)&&!profileBtn.contains(e.target)) profileMenu.style.display="none"; });
viewAccount.onclick = ()=> profilePopup.style.display="block";
closePopup.onclick = ()=> profilePopup.style.display="none";
changePhotoBtn.onclick = ()=> photoInput.click();
photoInput.onchange = ()=>{ if(photoInput.files.length>0) photoForm.submit(); };
document.querySelector(".logout").onclick = ()=> window.location.replace("dep.php");

function scrollToSection(id){ const section=document.getElementById(id); if(section) section.scrollIntoView({behavior:'smooth'}); }

function filterSemester(sem, btn){
    document.querySelectorAll(".sem-pill").forEach(p=>p.classList.remove("active"));
    btn.classList.add("active");
    document.querySelectorAll(".card").forEach(c=>{
        const title = c.querySelector('h4').textContent.toLowerCase();
        const query = document.getElementById('searchInput').value.toLowerCase();
        c.style.display = ((sem==='all'||c.dataset.sem===sem) && title.includes(query)) ? 'block' : 'none';
    });
}

function scrollCards(btn,direction){
    const container=btn.parentElement.querySelector('.cards-wrapper');
    const scrollAmount=300;
    container.scrollBy({left:direction==='left'?-scrollAmount:scrollAmount, behavior:'smooth'});
}

const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    const activeSem = document.querySelector(".sem-pill.active").textContent.includes('All') ? 'all' : document.querySelector(".sem-pill.active").textContent.replace('Semester ','');
    document.querySelectorAll('.card').forEach(card => {
        const title = card.querySelector('h4').textContent.toLowerCase();
        card.style.display = ((activeSem==='all' || card.dataset.sem===activeSem) && title.includes(query)) ? 'block' : 'none';
    });
});

const searchIcon = document.getElementById('searchIcon');
searchIcon.onclick = () => {
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input'));
};
</script>
</body>
</html>
  