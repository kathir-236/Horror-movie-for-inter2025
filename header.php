<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRK Institute of Technology</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ==================== RESET & THEME ==================== */
        *{ margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
        html, body { overflow-x:hidden; }

        :root {
            --bg:#ffffff;
            --text:#121212;
            --card:#f8f8f8;
            --primary:#344976;
            --nav-bg:#1d3a6b;
        }

        /* Dark Mode Class */
        body.dark {
            --bg:#0c0c0e;
            --text:#f5f5f5;
            --card:#1a1a1d;
            --primary:#4d8cff;
            --nav-bg:#0b1a33;
        }

        body { background:var(--bg); color:var(--text); transition:0.3s ease; }

        /* ==================== LAYOUT ==================== */
        .slider-wrapper { display:flex; width:200%; min-height:100vh; transition:transform .8s ease-in-out; }
        .page { width:100%; min-height:100vh; }
        .top-header img { width:100%; max-height:280px; object-fit:cover; }

        /* ==================== NAVBAR ==================== */
        nav {
            width:100%; padding:12px 25px; display:flex; justify-content:space-between; 
            align-items:center; position:sticky; top:0; z-index:999;
            background: var(--nav-bg);
        }
        .nav-left { display:flex; align-items:center; gap:12px; }
        .nav-left img { width:50px; height:50px; border-radius:50%; object-fit:cover; }
        .brand-name { font-size:20px; font-weight:600; color:#fff; }
        .nav-right { display:flex; align-items:center; gap:20px; }
        nav ul { list-style:none; display:flex; gap:20px; }
        nav ul li a { text-decoration:none; color:#ffffff; padding:6px 12px; border-radius:6px; transition:0.3s; }
        nav ul li a:hover { color:#ffcc00; }

        /* Dropdown */
        .dropdown { display:none; position:absolute; top:40px; left:0; background:var(--nav-bg); min-width:160px; border-radius:10px; padding:8px 0; }
        nav ul li:hover .dropdown { display:block; }
        .dropdown a { display:block; padding:10px 15px; color:#fff; text-decoration:none; }

        /* Buttons */
        .mode-btn {
            padding: 8px 18px; border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 12px; background: rgba(255, 255, 255, 0.18);
            color: white; cursor: pointer; transition: 0.3s;
        }
        .start-btn { 
            display:inline-block; margin-top:20px; padding:12px 25px; 
            background:var(--primary); color:white; border-radius:8px; 
            text-decoration:none; cursor:pointer; 
        }

        /* Hero & Content */
        .header { display:grid; grid-template-columns:1fr 1fr; gap:40px; padding:50px 20px; }
        .header img { width:100%; height:300px; border-radius:15px; object-fit:cover; }
        .cards-wrapper { display:flex; justify-content:center; gap:20px; flex-wrap:wrap; padding:40px 20px; }
        .card { width:260px; padding:25px; background:var(--card); border-radius:15px; text-align:center; }
        .ribbon { background:var(--primary); color:white; padding:15px; text-align:center; }

        /* Back to Top */
        .back-to-top {
            position:fixed; bottom:25px; right:25px; width:55px; height:55px;
            background:#fff; border-radius:15px; display:flex; justify-content:center;
            align-items:center; box-shadow:0 6px 20px #0004; cursor:pointer; color:#2a72ff;
            opacity:0; transition:0.3s; z-index:9999; border:none;
        }
        .back-to-top.show { opacity:1; }

        @media(max-width:850px){
            nav ul { display:none; flex-direction:column; position:absolute; top:70px; right:15px; background:var(--nav-bg); width:200px; padding:10px; border-radius:10px; }
            nav ul.show { display:flex; }
            .header { grid-template-columns:1fr; text-align:center; }
        }
    </style>
</head>
<body>

<script>
    (function() {
        const currentTheme = localStorage.getItem("theme");
        if (currentTheme === "dark") {
            document.body.classList.add("dark");
        }
    })();
</script>

<div class="slider-wrapper" id="slider">

    <div class="page">
        <div class="top-header"><img src="./image/mrklogo.jpg"></div>

        <nav>
            <div class="nav-left">
                <img src="./image/book.jpg">
                <span class="brand-name">MRK Institute</span>
            </div>
            <div class="nav-right">
                <ul id="menu">
                    <li><a href="#">Home</a></li>
                    <li><a href="#story">Our Story</a></li>
                    <li><button class="mode-btn" onclick="toggleMode()">Dark/Light</button></li>
                </ul>
                <span class="menu-btn" style="color:white; cursor:pointer;" onclick="toggleMenu()">☰</span>
            </div>
        </nav>

        <section class="header">
            <div class="header-text">
                <h1>MRK Institute of Technology</h1>
                <p>Empowering future engineers with high-quality education and hands-on training.</p>
                <a class="start-btn" onclick="goNext()">Start Learning →</a>
            </div>
            <img src="./image/06.jpg">
        </section>

        <div class="cards-wrapper">
            <div class="card"><h3>3200+ Students</h3><p>Growing community.</p></div>
            <div class="card"><h3>82% Placement</h3><p>Top recruitment.</p></div>
        </div>

        <div class="ribbon">Building Tomorrow’s Engineers Today</div>

        <footer style="padding:40px; text-align:center; background:var(--card);">
            © 2025 MRK Institute of Technology
        </footer>
    </div>
</div>

<button id="backToTop" class="back-to-top"><i class="fas fa-chevron-up"></i></button>

<script>
    // persistent Dark Mode Logic
    function toggleMode() {
        const isDark = document.body.classList.toggle("dark");
        // Save choice to localStorage
        if (isDark) {
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    }

    function toggleMenu() {
        document.getElementById("menu").classList.toggle("show");
    }

    function goNext() {
        window.location.href = "second.php"; 
    }

    // Scroll Logic
    const btt = document.getElementById("backToTop");
    window.addEventListener("scroll", () => {
        if(window.scrollY > 300) btt.classList.add("show");
        else btt.classList.remove("show");
    });
    btt.addEventListener("click", () => {
        window.scrollTo({top: 0, behavior: "smooth"});
    });
</script>

</body>
</html>