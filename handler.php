<?php
// ================= DB CONNECTION =================
require_once 'db1.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= STAFF-ONLY ACCESS =================
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// ================= UPLOAD SETTINGS =================
$upload_dir = 'books/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$error = '';

if (isset($_POST['upload'])) {
    $book_title = trim($_POST['book_title'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    $youtube_link = trim($_POST['youtube_link'] ?? '');
    $uploaded_by = $_SESSION['staff_name'] ?? $_SESSION['staff_email'] ?? $_SESSION['staff_id'];

    // File info
    $file_tmp = $_FILES['book_file']['tmp_name'] ?? '';
    $file_name = basename($_FILES['book_file']['name'] ?? '');
    $thumb_uploaded = isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK;

    // Validation: Title, file, subject code pattern
    if (!$book_title || !$file_name) {
        $error = "Title and file are required.";
    } elseif ($subject_code && !preg_match('/^[A-Za-z]{2,3}\d{4}$/', $subject_code)) {
        $error = "Invalid subject code (e.g., CS1010, ECE2045).";
    }

    if (empty($error)) {
        // Rename file to prevent overwrite
        $unique_filename = time() . '_' . $file_name;
        $destination = $upload_dir . $unique_filename;

        if (move_uploaded_file($file_tmp, $destination)) {
            // Handle optional thumbnail
            $thumb_dest = NULL;
            if ($thumb_uploaded) {
                $thumb_tmp = $_FILES['thumbnail_file']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['thumbnail_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png'])) {
                    $thumb_dest = $upload_dir . pathinfo($unique_filename, PATHINFO_FILENAME) . '_thumb.' . ($ext==='jpeg'?'jpg':$ext);
                    move_uploaded_file($thumb_tmp, $thumb_dest);
                }
            }

            // Insert into database
            $sql = "INSERT INTO books (title, subject_code, youtube_link, uploaded_by, filename, thumbnail) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $book_title, $subject_code, $youtube_link, $uploaded_by, $unique_filename, $thumb_dest);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: staff.php?status=uploaded");
                exit();
            } else {
                $error = "Database error: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "File upload failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Plan Upload</title>
<style>
.modal-overlay {
    position: fixed; top:0; left:0; width:100vw; height:100vh;
    background:rgba(0,0,0,0.25); display:flex; align-items:center; justify-content:center;
    z-index:9999; display:none;
}
.modal-box {
    background:#fff; color:#222; padding:28px 22px; border-radius:10px; box-shadow:0 6px 32px rgba(0,0,0,0.18);
    min-width:260px; max-width:90vw; text-align:center; font-size:17px; position:relative;
}
.modal-close {
    margin-top:18px; background:#007bff; color:#fff; border:none; padding:8px 22px; border-radius:6px; font-weight:600; cursor:pointer;
}
.modal-close:hover { background:#0056b3; }
</style>
</head>
<body>
<!-- Modal -->
<div id="modalMessage" class="modal-overlay">
    <div class="modal-box">
        <span id="modalMessageText"></span>
        <button onclick="closeModal()" class="modal-close">OK</button>
    </div>
</div>
<script>
function showModal(msg) {
    document.getElementById('modalMessageText').textContent = msg;
    document.getElementById('modalMessage').style.display='flex';
}
function closeModal() {
    document.getElementById('modalMessage').style.display='none';
}
<?php if(!empty($error)){
    echo 'document.addEventListener("DOMContentLoaded", function(){ showModal(' . json_encode($error) . '); });';
} ?>
</script>
</body>
</html>
