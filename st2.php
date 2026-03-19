<?php
session_start();

/* ================= DB CONNECTION ================= */
$conn = new mysqli("localhost","root","","fmrkit");
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
$stmt = $conn->prepare("SELECT staff_email, department, photo FROM staff WHERE staff_id=? LIMIT 1");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$stmt->bind_result($db_email, $db_dept, $db_photo);
$stmt->fetch();
$stmt->close();

$staff_email = $staff_email ?: ($db_email ?? 'N/A');
$staff_dept  = $db_dept ?? '';
$staff_photo = $staff_photo ?: ($db_photo ?? 'https://via.placeholder.com/150');

/* ================= PHOTO UPLOAD ================= */
if(isset($_POST['change_photo']) && isset($_FILES['photo'])){
    if($_FILES['photo']['error'] === 0){
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext,['jpg','jpeg','png','webp'])){
            if(!is_dir("uploads")) mkdir("uploads",0777,true);
            $path = "uploads/staff_{$staff_id}_".time().".$ext";
            if(move_uploaded_file($_FILES['photo']['tmp_name'],$path)){
                $stmt = $conn->prepare("UPDATE staff SET photo=? WHERE staff_id=?");
                $stmt->bind_param("ss",$path,$staff_id);
                $stmt->execute();
                $_SESSION['staff_photo'] = $path;
                header("Location: staff.php");
                exit();
            }
        }
    }
}

/* ================= DELETE MATERIAL (STAFF + DEPARTMENT SAFE) ================= */
if(isset($_POST['delete_material'])){
    $mid = intval($_POST['material_id']);

    $stmt = $conn->prepare(
        "SELECT pdf_file FROM materials WHERE id=? AND department=?"
    );
    $stmt->bind_param("is",$mid,$staff_dept);
    $stmt->execute();
    $stmt->bind_result($pdf);
    $stmt->fetch();
    $stmt->close();

    if($pdf && file_exists("uploads/".$pdf)){
        unlink("uploads/".$pdf);
    }

    $stmt = $conn->prepare(
        "DELETE FROM materials WHERE id=? AND department=?"
    );
    $stmt->bind_param("is",$mid,$staff_dept);
    $stmt->execute();
    $stmt->close();

    header("Location: staff.php?deleted=1");
    exit();
}

/* ================= FETCH MATERIALS (OWN DEPARTMENT ONLY) ================= */
$materials = [];
$plans = [];

$sql = "SELECT id,title,semester,content_type,pdf_file,image_base64,youtube_link,
               subject_name,subject_code,department
        FROM materials
        WHERE department = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $staff_dept);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()){

    $type = trim($row['content_type']);
    switch($type){
        case 'Book':
        case 'Books': $key='📘 Books'; break;
        case 'Notes': $key='📝 Notes'; break;
        case 'Question Paper': $key='❓ Questions'; break;
        case 'Lab Manual': $key='🧪 Labs'; break;
        case 'Lesson Plan': $key='📅 Plan'; break;
        default: continue 2;
    }

    $sem_map=['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8];
    $sem_num = $sem_map[$row['semester']] ?? $row['semester'];

    $item = [
        'id'=>$row['id'],
        'name'=>$row['title'],
        'semester'=>$row['semester'],
        'sem_num'=>$sem_num,
        'pdf'=>$row['pdf_file'],
        'img'=>$row['image_base64']
    ];

    if($key==='📅 Plan') $plans[]=$item;
    $materials[$key][]=$item;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="container mt-4">
    <h2>Welcome, <?=htmlspecialchars($staff_name)?> 👋</h2>
    <p><strong>Department:</strong> <?=htmlspecialchars($staff_dept)?></p>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Material deleted successfully</div>
    <?php endif; ?>

    <?php foreach($materials as $section=>$items): ?>
        <h3 class="mt-4"><?=$section?></h3>
        <div class="row">
            <?php foreach($items as $m): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6><?=$m['name']?></h6>
                        <p>Semester <?=$m['semester']?></p>

                        <?php if($m['pdf']): ?>
                            <a href="uploads/<?=$m['pdf']?>" target="_blank" class="btn btn-sm btn-primary">Open PDF</a>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return confirm('Delete this material?')">
                            <input type="hidden" name="material_id" value="<?=$m['id']?>">
                            <button name="delete_material" class="btn btn-sm btn-danger mt-2">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
