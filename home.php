<?php
session_start();

/*
Assuming after login you store:
$_SESSION['user_id']
$_SESSION['student_name']
*/


?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MRK Institute of Technology</title>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ==================== RESET ==================== */
*{
    margin:0; padding:0; box-sizing:border-box;
    font-family:"Poppins",sans-serif;
}
html, body { overflow-x:hidden; }

/* ==================== THEME ==================== */
:root{
    --bg:#ffffff;
    --text:#121212;
    --card:#f8f8f8;
    --primary:#344976;
    --nav-blur:rgba(255,255,255,0.6);
}
.dark{
    --bg:#0c0c0e;
    --text:#f5f5f5;
    --card:#1a1a1d;
    --primary:#4d8cff;
    --nav-blur:rgba(0,0,0,0.4);
}

body{
    background:var(--bg);
    color:var(--text);
    transition:0.3s ease;
}

/* ==================== SLIDER ==================== */
.slider-wrapper{
    display:flex;
    width:200%;
    min-height:100vh;
    transition:transform .8s ease-in-out;
}
.page{
    width:100%;
    min-height:100vh;
}

/* ==================== TOP HEADER ==================== */
.top-header img{
    width:100%;
    max-height:280px;
    object-fit:cover;
}

/* ==================== NAVBAR ==================== */
/* ==================== NAVBAR ==================== */
nav{
    width:100%;
    padding:12px 25px;
    display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; z-index:999;

    /* NEW NAVBAR COLOR */
    background:#1d3a6b;
}

.dark nav{
    background:#0b1a33;
}

.nav-left{
    display:flex; align-items:center; gap:12px;
}
.nav-left img{
    width:50px; height:50px; border-radius:50%; object-fit:cover;
}
.brand-name{
    font-size:20px; font-weight:600; color:#fff;
}

.nav-right{
    display:flex; align-items:center; gap:20px;
}

nav ul{
    list-style:none;
    display:flex;
    gap:20px;
}

nav ul li a{
    text-decoration:none;
    color:#ffffff;
    padding:6px 12px;
    border-radius:6px;
    position:relative;
    transition:0.3s ease;
}

/* underline slide effect */
nav ul li a::after {
    content:"";
    position:absolute;
    left:0;
    bottom:-3px;
    width:0%;
    height:2px;

    /* NEW UNDERLINE COLOR */
    background:#ffcc00;
    transition:0.3s ease;
}

nav ul li a:hover::after {
    width:100%;
}

nav ul li a:hover {
    color:#ffcc00;
}

/* dropdown */
nav ul li{
    position:relative;
}
.dropdown{
    display:none;
    position:absolute;
    top:40px; left:0;

    /* MATCH NAV COLOR */
    background:#1d3a6b;
    min-width:160px;
    border-radius:10px;
    padding:8px 0;
}
.dropdown a{
    display:block;
    padding:10px 15px;
    color:#fff;
}
nav ul li:hover .dropdown{
    display:block;
}

/* MOBILE NAV */
.menu-btn{
    font-size:26px;
    color:#fff;
    display:none;
    cursor:pointer;
}
@media(max-width:850px){
    nav ul{
        position:absolute;
        top:70px; right:15px;
        width:220px;
        flex-direction:column;
        padding:15px;
        background:#1d3a6b;
        border-radius:12px;
        display:none;
    }
    nav ul.show{ display:flex; }
    .menu-btn{ display:block; }
}

.mode-btn {
    padding: 8px 18px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);

    color: white;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s ease;
}

.mode-btn:hover {
    background: rgba(255, 255, 255, 0.30);
    transform: translateY(-2px);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
}

/* Dark Mode Button Color */
.dark .mode-btn {
    background: rgba(0, 0, 0, 0.35);
    border-color: rgba(255, 255, 255, 0.25);
    color: #fff;
}

.dark .mode-btn:hover {
    background: rgba(0, 0, 0, 0.55);
}



/* ==================== HERO ==================== */
.header{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
    padding:50px 20px;
}
.header img{
    width:100%;
    height:300px;
    border-radius:15px;
    object-fit:cover;
}
.header-text h1{
    font-size:40px;
    font-weight:700;
}
.header-text p{
    margin-top:15px;
    font-size:18px;
    line-height:1.6;
}

/* Start Button */
.start-btn{
    display:inline-block;
    margin-top:20px;
    padding:12px 25px;
    background:var(--primary);
    color:white;
    border-radius:8px;
    font-size:18px;
    text-decoration:none;
    transition:0.3s;
    cursor:pointer;
}
.start-btn:hover{
    background:#003ed1;
    transform:translateY(-4px);
}

/* ==================== RESPONSIVE HERO ==================== */
@media(max-width:900px){
    .header{
        grid-template-columns:1fr;
        text-align:center;
    }
    .header img{
        height:220px;
        margin-top:20px;
    }
    .header-text h1{ font-size:30px; }
    .header-text p{ font-size:16px; }
}

/* ==================== STORY ==================== */
.story{
    padding:60px 20px;
    text-align:center;
}
.story h2{
    font-size:32px;
    margin-bottom:20px;
}
.story p{
    max-width:800px;
    margin:auto;
    font-size:18px;
}

/* ==================== CARDS ==================== */
.cards-wrapper{
    display:flex;
    justify-content:center;
    gap:20px;
    flex-wrap:wrap;
    padding:40px 20px;
}
.card{
    width:260px;
    padding:25px;
    background:var(--card);
    border-radius:15px;
    text-align:center;
    box-shadow:0 4px 12px #0002;
}

/* RESPONSIVE CARDS */
@media(max-width:700px){
    .card{
        width:90%;
        max-width:350px;
    }
}

/* ==================== RIBBON ==================== */
.ribbon{
    background:var(--primary);
    color:white;
    padding:15px;
    font-size:20px;
    text-align:center;
}

/* ==================== TESTIMONIALS ==================== */
.testimonials{
    padding:50px 20px;
    text-align:center;
}
.testimonial-box{
    margin-top:30px;
    display:flex;
    justify-content:center;
    gap:20px;
    flex-wrap:wrap;
}
.testimonial{
    width:300px;
    background:var(--card);
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px #0002;
}
@media(max-width:750px){
    .testimonial{
        width:90%;
        max-width:350px;
    }
}

/* ==================== FOOTER ==================== */
footer{
    padding:25px;
    margin-top:40px;
    background:var(--card);
    text-align:center;
}
@media(max-width:600px){
    footer{
        font-size:14px;
    }
}

/* ==================== BACK TO TOP ==================== */
.back-to-top{
    position:fixed;
    bottom:25px; right:25px;
    width:55px; height:55px;
    background:#fff;
    border-radius:15px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:20px;
    box-shadow:0 6px 20px #0004;
    cursor:pointer;
    color:#2a72ff;
    opacity:0;
    pointer-events:none;
    transition:0.3s;
    z-index:9999;
}
.back-to-top.show{
    opacity:1;
    pointer-events:auto;
}
@media(max-width:600px){
    .back-to-top{
        width:45px; height:45px;
        font-size:16px;
    }
}

/* ==================== PAGE 2 ==================== */
.page2{
    padding:40px;
    text-align:center;
    background:#f5f5f5;
}
.dark .page2{
    background:#1a1a1d;
}

.back-btn{
    margin-top:20px;
    padding:12px 22px;
    border:none;
    border-radius:10px;
    background:#222;
    color:white;
    cursor:pointer;
    font-size:18px;
}
.dark .back-btn{
    background:white;
    color:black;
}

</style>
</head>

<body>

<div class="slider-wrapper" id="slider">

<!-- ==================== PAGE 1 ==================== -->
<div class="page">

    <!-- HEADER IMAGE -->
    <div class="top-header">
        <img src="./image/mrklogo.jpg">
    </div>

    <!-- NAVBAR -->
    <nav>
        <div class="nav-left">
            <img src="./image/book.jpg">
            <span class="brand-name">MRK Institute of Technology</span>
        </div>

        <div class="nav-right">
            <ul id="menu">
                <li><a href="#">Home</a></li>

                <li>
                    <a href="#">Programs ▼</a>
                    <div class="dropdown">
                        <a href="#">Web Development</a>
                        <a href="#">Cyber Security</a>
                        <a href="#">AI & ML</a>
                    </div>
                </li>

                <li><a href="#story">Our Story</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

            <span class="menu-btn" onclick="toggleMenu()">☰</span>
            <button class="mode-btn" onclick="toggleMode()">Dark/Light</button>
        </div>
    </nav>

    <!-- HERO -->
    <section class="header">
        <div class="header-text">
            <h1>MRK Institute of Technology</h1>
            <p>
                MKIT College is a modern institution dedicated to providing world-class education
                and preparing students for future careers in technology.
                With experienced faculty, advanced labs, and an industry-focused curriculum, we help students build strong skills for a successful future. Our mission is to inspire learning, innovation, and professional growth.
                Empowering future engineers with high-quality education and hands-on training.
            </p>

          <a class="start-btn" onclick="goNext()">Start Learning →</a>

        </div>

        <img src="./image/06.jpg">
    </section>

    <!-- STORY -->
    <section class="story" id="story">
        <h2>Our Story</h2>
        <p>
            MRK Institute of Technology is committed to excellence in education, innovation, and
            hands-on learning. We empower students to become leaders in the tech industry.
        </p>
    </section>

    <!-- CARDS -->
    <div class="cards-wrapper">
        <div class="card"><h3>3200+ Students</h3><p>A growing learning community.</p></div>
        <div class="card"><h3>82% Placement</h3><p>Top companies recruit here.</p></div>
        <div class="card"><h3>10+ Programs</h3><p>Industry-ready curriculum.</p></div>
    </div>

    <!-- RIBBON -->
    <div class="ribbon">Building Tomorrow’s Engineers Today</div>

    <!-- TESTIMONIALS -->
    <section class="testimonials">
        <h2>What Students Say</h2>
        <div class="testimonial-box">
            <div class="testimonial">“Amazing labs & faculty.”</div>
            <div class="testimonial">“Great placement support.”</div>
            <div class="testimonial">“A perfect place for innovation.”</div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="contact">
        © 2025 MRK Institute of Technology — All Rights Reserved
    </footer>

    <!-- BACK TO TOP -->
    <button id="backToTop" class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>

</div>

<!-- ==================== PAGE 2 ==================== -->
<div class="page page2">
    <h1>Welcome to Page 2</h1>
    <p>You reached Page 2 with a smooth right-slide animation!</p>

    <button class="back-btn" onclick="goBack()">← Back</button>
</div>

</div>

<!-- ==================== SCRIPT ==================== -->
<script>
function toggleMode(){ document.body.classList.toggle("dark"); }
function toggleMenu(){ document.getElementById("menu").classList.toggle("show"); }

function goNext(){ document.getElementById("slider").style.transform="translateX(-50%)"; }
function goBack(){ document.getElementById("slider").style.transform="translateX(0%)"; }

/* BACK TO TOP */
const backToTopBtn=document.getElementById("backToTop");
window.addEventListener("scroll", ()=>{
    if(window.scrollY>300){
        backToTopBtn.classList.add("show");
    } else {
        backToTopBtn.classList.remove("show");
    }
});
backToTopBtn.addEventListener("click", ()=>{
    window.scrollTo({top:0, behavior:"smooth"});
});
function goNext() {
    window.location.href = "second.php"; 
}

</script>

</body>
</html>

