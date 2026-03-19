<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Montserrat", sans-serif;
            background: url("https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1400&q=80")
                center/cover no-repeat fixed;
        }

        /* ---------------- NAVBAR ---------------- */
        .navbar {
            width: 100%;
            padding: 12px 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;

            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(8px);
        }

        .navbar img {
            height: 40px;
            width: auto;
        }

        .back-btn {
            padding: 8px 17px;
            font-size: 14px;
            border: 2px solid #fff;
            background: transparent;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: white;
            color: #000;
        }

        /* NAVBAR LEFT SIDE (Logo + Title) */
.nav-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Circle Logo */
.logo-circle {
    height: 45px;
    width: 45px;
    border-radius: 50%;
    object-fit: cover;

    border: 2px solid white;     /* white border */
    background: white;            /* keeps circle clean */
}

/* White text for college name */
.brand-name {
    color: white;
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

        /* ---------------- OVERLAY ---------------- */
        .overlay {
            min-height: 100vh;
            width: 100%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(5px);

            padding-top: 120px;
            padding-left: clamp(15px, 4vw, 40px);
            padding-right: clamp(15px, 4vw, 40px);

            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .title {
            color: white;
            font-size: clamp(26px, 4vw, 40px);
            font-weight: 600;
            text-align: center;
            margin-bottom: clamp(25px, 6vw, 40px);
        }

        /* ---------------- CARD CONTAINER ---------------- */
        .card-container {
            max-width: 1300px;

            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;

            gap: clamp(12px, 3vw, 28px);
        }

        /* ---------------- CARD ---------------- */
        .card {
            flex: 1 1 clamp(260px, 30%, 350px);
            max-width: 100%;

            background: linear-gradient(135deg,
                    rgba(0, 132, 255, 0.6),
                    rgba(0, 162, 255, 0.85));

            backdrop-filter: blur(15px);
            padding: clamp(20px, 4vw, 35px);
            border-radius: 20px;
            text-align: center;
            color: #fff;

            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
            transition: 0.35s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45);
        }

        .card i {
            font-size: clamp(35px, 5vw, 48px);
            margin-bottom: clamp(10px, 2vw, 18px);
        }

        .card h2 {
            font-size: clamp(18px, 3vw, 24px);
            margin-bottom: clamp(10px, 1.5vw, 15px);
            font-weight: 600;
        }

        .card p {
            font-size: clamp(13px, 2.4vw, 15px);
            margin-bottom: clamp(15px, 3vw, 22px);
            line-height: 1.6;
        }

        /* ---------------- BUTTON ---------------- */
        .btn {
            text-decoration: none;
            color: white;
            border: 2px solid #fff;
            padding: clamp(8px, 2vw, 12px) clamp(18px, 3vw, 28px);
            border-radius: 50px;
            font-size: clamp(14px, 2.3vw, 16px);
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }

        .btn:hover {
            background: white;
            color: #007BFF;
        }

        /* ---------------- MOBILE FIXES ---------------- */
        @media (max-width: 480px) {
            .navbar {
                padding: 10px 15px;
            }

            .navbar img {
                height: 34px;
            }

            .back-btn {
                padding: 6px 14px;
                font-size: 13px;
            }
        }


        /* ================= DARK MODE ================= */
body.dark {
    background: #0c0c0e;
}

/* Dark overlay */
body.dark .overlay {
    background: rgba(0, 0, 0, 0.75);
}

/* Dark navbar */
body.dark .navbar {
    background: rgba(10, 15, 30, 0.7);
}

/* Dark cards */
body.dark .card {
    background: linear-gradient(135deg,
        rgba(40, 40, 40, 0.9),
        rgba(20, 20, 20, 0.95));
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.8);
}

/* Card button dark */
body.dark .btn {
    border-color: #4d8cff;
    color: #4d8cff;
}
body.dark .btn:hover {
    background: #4d8cff;
    color: #fff;
}

/* Back button dark */
body.dark .back-btn {
    border-color: #aaa;
    color: #fff;
}
body.dark .back-btn:hover {
    background: #fff;
    color: #000;
}

    </style>

</head>

<body  class="<?php echo $themeClass; ?>">

    <!-- NAVBAR -->
   <div class="navbar">
    <div class="nav-left">
        <img src="./image/book.jpg" alt="College Logo" class="logo-circle">
        <span class="brand-name">MRK Institute of Technology</span>
    </div>

    <button class="back-btn" onclick="goNext()">← Back</button>
</div>


    <div class="overlay">

        <h1 class="title">Welcome to the Portal</h1>

        <div class="card-container">

            <div class="card">
                <i class="fa-solid fa-user-graduate"></i>
                <h2>Student</h2>
                <p>Access classes, notes, assignments and your personal dashboard.</p>
                <a href="studentregister.php" class="btn">Register →</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-chalkboard-user"></i>
                <h2>Staff</h2>
                <p>Upload materials, manage coursework and view student performance.</p>
                <a href="staffregister.php" class="btn">Register →</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-user-shield"></i>
                <h2>Admin</h2>
                <p>Full control: users, settings, management, reports & permissions.</p>
                <a href="adminlogin.php" class="btn">Login →</a>
            </div>

        </div>

    </div>

<script src=disable_back.js>
    
</script>
<script>
    function goNext() {
        window.location.href = "home.php"; 
    }
  // Add this to second.php to automatically apply the theme

    (function() {
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark");
        }
    })();


document.querySelectorAll(".one-click").forEach(btn => {
    btn.addEventListener("click", function (e) {
        if (this.classList.contains("clicked")) {
            e.preventDefault(); // stop second click
            return false;
        }

        this.classList.add("clicked");
        this.innerText = "Please wait...";
        this.style.pointerEvents = "none";
        this.style.opacity = "0.6";
    });
});
</script>


</body>

</html>