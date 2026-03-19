<?php
session_start();

/* ================= DB CONNECTION ================= */
$conn = new mysqli("localhost","root","","mrkit");
if($conn->connect_error){
    die("Database Error: " . $conn->connect_error);
}

/* ================= LOGIN PROTECTION ================= */
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

/* ================= SESSION DATA ================= */
$staff_id    = $_SESSION['staff_id'];
$staff_name  = $_SESSION['staff_name'] ?? 'Staff';
$staff_email = $_SESSION['staff_email'] ?? null;
$staff_photo = $_SESSION['staff_photo'] ?? 'https://via.placeholder.com/150';

/* ================= FETCH STAFF DATA ================= */
$stmt = $conn->prepare("
    SELECT id,title,semester,content_type,pdf_file,image_base64,
           youtube_link,subject_name,subject_code,department
    FROM materials
    WHERE department = ?
    ORDER BY id DESC
");
$stmt->bind_param("s", $staff_dept);
$stmt->execute();
$res = $stmt->get_result();

$stmt = $conn->prepare("SELECT staff_email, department, photo FROM staff WHERE staff_id=? LIMIT 1");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$stmt->bind_result($db_email, $db_dept, $db_photo);
$stmt->fetch();
$stmt->close();

if (!$staff_email) $staff_email = $db_email ?? 'N/A';
$staff_dept  = $db_dept ?? 'N/A';
if(!$staff_photo) $staff_photo = $db_photo ?? 'https://via.placeholder.com/150';

/* ================= PHOTO UPLOAD ================= */
if(isset($_POST['change_photo']) && isset($_FILES['photo'])){
    $file = $_FILES['photo'];
    if($file['error'] === 0){
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(in_array($ext,['jpg','jpeg','png','webp'])){
            if(!is_dir("uploads")) mkdir("uploads",0777,true);
            $path = "uploads/staff_".$staff_id."_".time().".".$ext;
            if(move_uploaded_file($file['tmp_name'],$path)){
                $stmt = $conn->prepare("UPDATE staff SET photo=? WHERE staff_id=?");
                $stmt->bind_param("ss",$path,$staff_id);
                $stmt->execute();
                $_SESSION['staff_photo']=$path;
                header("Location: staff.php");
                exit();
            }
        }
    }
}

// ================= Delete material handler (STAFF ONLY) =================
// ================= Delete material handler (STAFF ONLY) =================
if(isset($_POST['delete_material']) && !empty($_POST['material_id'])){
    if(!isset($_SESSION['staff_id'])){
        die("❌ Unauthorized! Only staff can delete materials.");
    }

    $mid = intval($_POST['material_id']);

    // fetch filename to unlink
    $stmt = $conn->prepare("SELECT pdf_file FROM materials WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $stmt->bind_result($pdf_file);
    $stmt->fetch();
    $stmt->close();

    if(!empty($pdf_file)){
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $pdf_file;
        if(file_exists($path)) @unlink($path);
    }

    $stmt = $conn->prepare("DELETE FROM materials WHERE id=?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $stmt->close();

    // ✅ Set session flag
    $_SESSION['material_deleted'] = true;

    header("Location: staff.php"); // redirect to staff.php without query
    exit();
}


// ================= Fetch staff names =================
$staff_names = [];
$stmt = $conn->prepare("SELECT staff_id, staff_name FROM staff");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staff_names[$row['staff_id']] = $row['staff_name'];
}
$stmt->close();

// ================= Fetch uploaded materials =================
$materials = [];
$plans = [];
$sql = "SELECT id,title,semester,content_type,pdf_file,image_base64,youtube_link,subject_name,subject_code,department,staff_id FROM materials ORDER BY id DESC";
$res = $conn->query($sql);
if($res){
    while($row = $res->fetch_assoc()){
        $ct = $row['content_type'];
        switch(trim($ct)){
            case 'Book':
            case 'Books':
                $key = '📘 Book';
                break;
            case 'Notes':
            case 'Note':
                $key = '📝 Note';
                break;
            case 'Question Paper':
            case 'Question Papers':
                $key = '❓ Question';
                break;
            case 'Lab Manual':
            case 'Lab Manuals':
                $key = '🧪 Lab';
                break;
            case 'Lesson Plan':
            case 'Lesson Plans':
                $key = '📅 Plan';
                break;
            default:
                $key = $ct;
        }
        $sem_raw = trim($row['semester']);
        $sem_map = [
            'I'=>'1','II'=>'2','III'=>'3','IV'=>'4','V'=>'5','VI'=>'6','VII'=>'7','VIII'=>'8',
            '1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8'
        ];
        $sem_num = $sem_map[$sem_raw] ?? $sem_raw;

        $item = [
            'id' => $row['id'],
            'name' => $row['title'],
            'semester' => $row['semester'],
            'sem_num' => $sem_num,
            'pdf' => $row['pdf_file'],
            'img' => $row['image_base64'],
            'youtube' => $row['youtube_link'],
            'subject_name' => $row['subject_name'],
            'subject_code' => $row['subject_code'],
            'department' => $row['department'],
            'staff_id' => $row['staff_id']
        ];
        if(stripos($ct,'plan') !== false) $plans[] = $item;
        $materials[$key][] = $item;
    }
}

// Normalize and sort materials by semester number (1..8)
$semester_order = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII'];
function sem_to_num($s){
    $s = trim((string)$s);
    $map = ['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8];
    return $map[strtoupper($s)] ?? (is_numeric($s)?(int)$s:999);
}

foreach($materials as $cat => &$items){
    usort($items, function($a,$b){
        $na = sem_to_num($a['sem_num'] ?? $a['semester'] ?? '');
        $nb = sem_to_num($b['sem_num'] ?? $b['semester'] ?? '');
        if($na === $nb) return 0;
        return ($na < $nb) ? -1 : 1;
    });
}
unset($items);

usort($plans, function($a,$b){
    $na = sem_to_num($a['sem_num'] ?? $a['semester'] ?? '');
    $nb = sem_to_num($b['sem_num'] ?? $b['semester'] ?? '');
    if($na === $nb) return 0;
    return ($na < $nb) ? -1 : 1;
});


// ================= Fetch ALL Departments =================
$departments = [];

$res = $conn->query("
    SELECT DISTINCT department 
    FROM materials 
    WHERE department IS NOT NULL 
      AND department != ''
    ORDER BY department
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $departments[] = $row['department'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Dashboard</title>


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

/* Section Colors */
#home { background: #fdf6e3; }
#books { background: #e0f7fa; }
#notes { background: #fff3e0; }
#questions { background: #f3e5f5; }
#labs { background: #e8f5e9; }
#plan { background: #f0f4c3; }

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
    object-fit:fill;
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
} /* ==== DELETE CARD POPUP ==== */
.popup-overlay{
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.popup-box{
    width: 320px;
    background: #fff;
    padding: 22px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: pop 0.2s ease;
}

.popup-box h4{
    margin-bottom: 8px;
}

.popup-box p{
    color: #555;
    margin-bottom: 18px;
}

.popup-actions{
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-ok{
    background: #1976d2;
    color: #fff;
    border: none;
    padding: 6px 16px;
    border-radius: 20px;
}

.btn-cancel{
    background: #eee;
    border: none;
    padding: 6px 14px;
    border-radius: 20px;
}

@keyframes pop{
    from{ transform:scale(0.9); opacity:0; }
    to{ transform:scale(1); opacity:1; }
}
.toast-success{
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-20px);
    background: #dff3ea;
    color: #155724;
    padding: 14px 22px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid #b7e4d4;
    opacity: 0;
    pointer-events: none;
    transition: all 0.4s ease;
    z-index: 10000;
}

.toast-success.show{
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.toast-success i{
    color: #28a745;
    font-size: 18px;
}
/* ===== Student View Department Dropdown ===== */
.student-view {
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dropdown-icon {
    cursor: pointer;
    font-size: 14px;
}

.dept-dropdown {
    position: absolute;
    top: 42px;
    left: 0;
    background: #fff;
    color: #333;
    min-width: 220px;
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    display: none;
    z-index: 3000;
}

.dept-dropdown div {
    padding: 10px 14px;
    cursor: pointer;
    font-size: 14px;
}

.dept-dropdown div:hover {
    background: #f2f2f2;
}



</style>
</head>

<body>

<div class="header-banner"><img src="./image/mrklogo.jpg" alt="Logo"></div>

<nav class="navbar-custom">
    <i class="fa fa-bars menu-toggle" id="menuBtn"></i>
    <div class="nav-left" id="navLeft">
        <div class="nav-item" onclick="scrollToSection('home')"><i class="fa fa-home"></i> Home</div>
        <div class="nav-item" onclick="scrollToSection('books')"><i class="fa fa-book"></i> Books</div>
        <div class="nav-item" onclick="scrollToSection('notes')"><i class="fa fa-sticky-note"></i> Notes</div>
        <div class="nav-item" onclick="scrollToSection('questions')"><i class="fa fa-file-alt"></i> Question Bank</div>
        <div class="nav-item" onclick="scrollToSection('labs')"><i class="fa fa-flask"></i> Lab Manuals</div>
        <div class="nav-item" onclick="scrollToSection('plan')"><i class="fa fa-calendar-alt"></i> Plan</div>
 <div class="nav-item dropdown student-view">

    <!-- DIRECT CLICK -->
    <span onclick="window.location.href='student.php'">
        <i class="fa fa-user-graduate"></i> Student View
    </span>

    <!-- DROPDOWN ICON -->
    <i class="fa fa-caret-down ms-1 dropdown-icon"
       onclick="toggleDeptDropdown(event)"></i>

    <!-- DROPDOWN LIST -->
    <div class="dept-dropdown" id="deptDropdown">
        <?php foreach($departments as $dept): ?>
            <div onclick="openStudentView('<?= htmlspecialchars($dept) ?>')">
                <?= htmlspecialchars($dept) ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

        <div class="nav-item" onclick="window.location.href='upload.php'"><i class="fa fa-upload"></i> Upload</div>
    </div>

    <div class="right-area">
        <div class="search-wrapper">
            <i class="fa fa-search search-icon" id="searchIcon"></i>
            <input type="text" id="searchInput" placeholder="Search materials...">
        </div>
        <div class="profile-area" id="profileBtn">
            <img src="<?php echo htmlspecialchars($staff_photo); ?>" alt="Profile">
            <span class="profile-name"><?php echo htmlspecialchars($staff_name); ?></span>
        </div>
    </div>
</nav>

<ul class="profile-menu" id="profileMenu">
    <li id="viewAccount"><i class="fa fa-user"></i> My Account</li>
    <li id="changePhotoBtn"><i class="fa fa-image"></i> Change Photo</li>
    <li class="logout"><i class="fa fa-sign-out-alt"></i> Logout</li>
</ul>

<div class="profile-popup" id="profilePopup">
    <img src="<?=htmlspecialchars($staff_photo)?>">
    <h4><?=htmlspecialchars($staff_name)?></h4>
    <p>ID: <?=$staff_id?></p>
    <p>Email: <?=$staff_email?></p>
    <p>Dept: <?=$staff_dept?></p>
    <button class="btn btn-dark" id="closePopupBtn">Close</button>
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
<?php if(isset($_SESSION['material_deleted']) && $_SESSION['material_deleted']): ?>
<div id="successToast" class="toast-success">
    <i class="fa fa-check-circle"></i>
    <span>Material deleted successfully.</span>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    showSuccessToast();
});
</script>

<?php 
// ✅ Clear the session flag so it only shows once
unset($_SESSION['material_deleted']); 
endif; ?>

<section id="home">
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?>👋</h2>
        <p>Dashboard Overview & Quick Links</p>
        <p style="font-size:16px;font-weight:600;color:#3b003b">
            Department: <?php echo htmlspecialchars($staff_dept); ?>
        </p>
        
    </div>
</section>

<div class="container">
<?php 
foreach(['books'=>'📘 Books','notes'=>'📝 Notes','questions'=>'❓ Questions','labs'=>'🧪 Labs','plan'=>'📅 Plan'] as $k=>$t): ?>
<section id="<?=$k?>" style="position:relative;">
    <h3><?=$t?></h3>
    <button class="scroll-btn left" onclick="scrollCards(this,'left')"><i class="fa fa-chevron-left"></i></button>
    <button class="scroll-btn right" onclick="scrollCards(this,'right')"><i class="fa fa-chevron-right"></i></button>
    <div class="cards-wrapper">
        <?php
        if($k === 'plan'){
            foreach($plans as $m): ?>
            <div class="card" data-sem="<?=$m['sem_num']?>" data-staff="<?=$m['staff_id']?>">
                <div class="card-top">
                    <?php if(!empty($m['img']) && file_exists($m['img'])): ?>
    <img src="<?= htmlspecialchars($m['img']) ?>"
         style="width:100%;height:120px;object-fit:cover;border-radius:10px;">
<?php else: ?>
    <img src="image/thumbnail.jpeg"
         style="width:100%;height:120px;object-fit:contain;">
<?php endif; ?>
                </div>
                <div class="card-body">
                    <h5><?=$m['name']?></h5>

                    <p class="mb-1">
                        <strong>Semester:</strong>
                        <?= htmlspecialchars($m['semester']) ?>
                    </p>
                    <p class="mb-1">
                        <strong>Subject Code:</strong>
                        <?= htmlspecialchars($m['subject_code'] ?? 'N/A') ?>
                    </p>

                  

                    <p class="mb-2 text-muted" style="font-size:13px;">
                      <p>👤 <?= htmlspecialchars($m['staff_name']) ?></p>
                    </p>
                    <?php if(!empty($m['pdf'])): ?>
                        <p><a href="uploads/<?=$m['pdf']?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf"></i> Open PDF</a></p>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Delete this material?');" style="display:inline">
                        <input type="hidden" name="material_id" value="<?=$m['id']?>">
                        <input type="hidden" name="delete_material" value="1">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; 
        } else {
            foreach($materials[ucfirst(rtrim($t,'s'))] ?? [] as $m): ?>
            <div class="card" data-sem="<?=$m['sem_num']?>" data-staff="<?=$m['staff_id']?>">
                <div class="card-top">
                    <?php if(!empty($m['img']) && file_exists($m['img'])): ?>
    <img src="<?= htmlspecialchars($m['img']) ?>"
         style="width:100%;height:120px;object-fit:cover;border-radius:10px;">
<?php else: ?>
    <img src="image/thumbnail.jpeg"
         style="width:100%;height:120px;object-fit:contain;">
<?php endif; ?>
                </div>
                <div class="card-body">
                    <h5><?=$m['name']?></h5>
                    <p class="mb-1">
                        <strong>Semester:</strong>
                        <?= htmlspecialchars($m['semester']) ?>
                    </p>
                    <p class="mb-1">
                        <strong>Subject Code:</strong>
                        <?= htmlspecialchars($m['subject_code'] ?? 'N/A') ?>
                    </p>


                    <p class="mb-2 text-muted" style="font-size:13px;">
                        👤 Uploaded by: <?= htmlspecialchars($staff_names[$m['staff_id']] ?? 'Staff') ?>
                    </p>
                    <?php if(!empty($m['pdf'])): ?>
                    <p><a href="uploads/<?=$m['pdf']?>" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF</a></p>
                    <?php endif; ?>
                  
                     <button class="btn btn-danger btn-sm"
                     onclick="showDeletePopup(<?= $m['id'] ?>)">
                     Delete
                     </button>

                </div>
                
            </div>
        <?php endforeach; } ?>
    </div>
    
</section>
<?php endforeach; ?>

</div>
<div id="deletePopup" class="popup-overlay">
  <div class="popup-box">

    <h4>Confirm Delete</h4>
    <p>Delete this material?</p>

    <div class="popup-actions">
      <button onclick="closePopup()" class="btn-cancel">Cancel</button>
      <button onclick="confirmDelete()" class="btn-ok">OK</button>
    </div>

  </div>
</div>

<footer>© 2025 MRK Institute of Technology</footer>

<script>
const menuBtn = document.getElementById('menuBtn');
const navLeft = document.getElementById('navLeft');
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');
const viewAccount = document.getElementById('viewAccount');
const profilePopup = document.getElementById('profilePopup');
const closePopupBtn = document.getElementById('closePopupBtn');
const changePhotoBtn = document.getElementById('changePhotoBtn');
const photoInput = document.getElementById('photoInput');
const photoForm = document.getElementById('photoForm');
const searchInput = document.getElementById('searchInput');
const searchIcon = document.getElementById('searchIcon');

// Menu toggle
menuBtn.onclick = ()=> navLeft.classList.toggle("active");

// Profile menu toggle
profileBtn.onclick = ()=> profileMenu.style.display = profileMenu.style.display==="block"?"none":"block";
document.addEventListener("click", e=>{ 
    if(!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) profileMenu.style.display="none"; 
});

// Profile popup
viewAccount.onclick = ()=> profilePopup.style.display="block";
closePopupBtn.onclick = ()=> profilePopup.style.display="none";

// Change photo
changePhotoBtn.onclick = ()=> photoInput.click();
photoInput.onchange = ()=>{ if(photoInput.files.length>0) photoForm.submit(); };

// Logout
document.querySelector(".logout").onclick = ()=> window.location.replace("staffdep.php");

// Scroll to section
function scrollToSection(id){ const section=document.getElementById(id); if(section) section.scrollIntoView({behavior:'smooth'}); }

// Combined filter function
function filterCards(sem, staff, btn = null){
    if(btn){
        document.querySelectorAll(".sem-pill").forEach(p=>p.classList.remove("active"));
        btn.classList.add("active");
    }
    const query = searchInput.value.toLowerCase();
    const selectedStaff = staff !== 'all' ? staff : document.getElementById('staffSelect').value;
    document.querySelectorAll('.card').forEach(card => {
        const title = card.querySelector('h5').textContent.toLowerCase();
        const cardSem = card.dataset.sem;
        const cardStaff = card.dataset.staff;
        const semMatch = sem === 'all' || cardSem === sem;
        const staffMatch = selectedStaff === 'all' || cardStaff === selectedStaff;
        const searchMatch = title.includes(query);
        card.style.display = (semMatch && staffMatch && searchMatch) ? 'block' : 'none';
    });
}

// Search filter
searchInput.addEventListener('input', () => {
    const activeSemElem = document.querySelector(".sem-pill.active");
    const activeSem = activeSemElem.textContent.includes('All') ? 'all' : activeSemElem.textContent.replace('Semester ','');
    filterCards(activeSem, 'all');
});

// Reset search
searchIcon.onclick = () => {
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input'));
};

// Horizontal scroll buttons
function scrollCards(btn,direction){
    const container = btn.parentElement.querySelector('.cards-wrapper');
    const scrollAmount = 300;
    container.scrollBy({left: direction==='left' ? -scrollAmount : scrollAmount, behavior:'smooth'});
}

// Disable forward button in Chrome
(function() {
    // Push a state to create history entry
    history.pushState(null, null, location.href);

    // Listen for popstate events (back/forward button presses)
    window.addEventListener('popstate', function(event) {
        // Push another state to prevent forward navigation
        history.pushState(null, null, location.href);
    });

    // Override pushState and replaceState to maintain the lock
    const originalPushState = history.pushState;
    const originalReplaceState = history.replaceState;

    history.pushState = function(state, title, url) {
        originalPushState.apply(history, arguments);
        // Re-establish the forward prevention after any navigation
        setTimeout(() => {
            history.pushState(null, null, location.href);
        }, 0);
    };

    history.replaceState = function(state, title, url) {
        originalReplaceState.apply(history, arguments);
        // Re-establish the forward prevention after any navigation
        setTimeout(() => {
            history.pushState(null, null, location.href);
        }, 0);
    };
})();
// Removed stray defaults and imageBase64 handlers (not used on this page)
function openYoutube(url){
    let videoId = '';

    // handle all YouTube formats
    if(url.includes('youtu.be/')){
        videoId = url.split('youtu.be/')[1];
    } else if(url.includes('watch?v=')){
        videoId = url.split('watch?v=')[1].split('&')[0];
    }

    if(videoId){
        document.getElementById('youtubeFrame').src =
            'https://www.youtube.com/embed/' + videoId + '?autoplay=1';

        new bootstrap.Modal(document.getElementById('youtubeModal')).show();
    }
}

// Stop video when modal closes
document.getElementById('youtubeModal')
?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('youtubeFrame').src = '';
});

let deleteId = null;

function showDeletePopup(id){
    deleteId = id;
    document.getElementById('deletePopup').style.display = 'flex';
}

function closePopup(){
    document.getElementById('deletePopup').style.display = 'none';
    deleteId = null;
}

function confirmDelete(){
    if(!deleteId) return;

    const form = document.createElement('form');
    form.method = 'POST';

    form.innerHTML = `
      <input type="hidden" name="material_id" value="${deleteId}">
      <input type="hidden" name="delete_material" value="1">
    `;

    document.body.appendChild(form);
    form.submit();
}

function showSuccessToast(){
    const toast = document.getElementById('successToast');
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Auto-refresh the page every 30 seconds (30000 milliseconds)
setInterval(() => {
    location.reload();
}, 30000);

</script>
<script>
function toggleDeptDropdown(e){
    e.stopPropagation();
    const dd = document.getElementById('deptDropdown');
    dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
}

// close dropdown on outside click
document.addEventListener('click', () => {
    const dd = document.getElementById('deptDropdown');
    if(dd) dd.style.display = 'none';
});

// open student view with department
function openStudentView(dept){
    window.location.href = "student.php?dept=" + encodeURIComponent(dept);
}


searchInput.addEventListener('input', () => {
    const active = document.querySelector(".sem-pill.active");
    const sem = active.textContent.includes("All")
        ? "all"
        : active.textContent.replace("Semester ","");

    filterSemester(sem, active);
});
// Dynamically add department to URL without reloading
(function() {
    const dept = "<?= addslashes($staff_dept) ?>"; // PHP variable
    if(dept && dept !== 'N/A'){
        const url = new URL(window.location);
        url.searchParams.set('dept', dept); // set ?dept=DepartmentName
        window.history.replaceState({}, '', url);
    }
})();

function filterSemester(sem, btn){
    // active pill
    document.querySelectorAll(".sem-pill")
        .forEach(p => p.classList.remove("active"));
    btn.classList.add("active");

    const query = document
        .getElementById('searchInput')
        .value
        .toLowerCase();

    document.querySelectorAll(".card").forEach(card => {
        const title = card
            .querySelector("h5")
            .textContent
            .toLowerCase();

        const cardSem = card.dataset.sem;

        const semMatch = (sem === 'all' || cardSem === sem);
        const searchMatch = title.includes(query);

        card.style.display = (semMatch && searchMatch)
            ? "block"
            : "none";
    });
}
</script>
</body>
</html>
