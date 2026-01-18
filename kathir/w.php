<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Navigation Bar</title>
	<style type="text/css">
		*{
			text-decoration: none;
		}
		.navbar{
			background: crimson; font-family: calibri; padding-right: 15px;padding-left: 15px;
		}
		.navdiv{
			display: flex; align-items: center; justify-content: space-between;
		}
		.logo a{
			font-size: 35px; font-weight: 600; color: white;
		}
		li{
			list-style: none; display: inline-block;
		}
		li a{
			color: white; font-size: 18px; font-weight: bold; margin-right: 25px;
		}
		button{
			background-color: black; margin-left: 10px; border-radius: 10px; padding: 10px; width: 90px;
		}
		button a{
			color: white; font-weight: bold; font-size: 15px;
		}

        .search{
    width: 330px;
    float: left;
    margin-left: 270px;
}

.srch{
    font-family: 'Times New Roman';
    width: 200px;
    height: 40px;
    background: transparent;
    border: 1px solid #ff0000ff;
    margin-top: 13px;
    color: #fefefe;
    border-right: none;
    font-size: 16px;
    float: left;
    padding: 10px;
    border-bottom-left-radius: 5px;
    border-top-left-radius: 5px;
}

.btn{
    width: 100px;
    height: 40px;
    background: #ff0000;
    border: 2px solid #ff7200;
    margin-top: 13px;
    color: #fff;
    font-size: 15px;
    border-bottom-right-radius: 5px;
    border-bottom-right-radius: 5px;
    transition: 0.2s ease;
    cursor: pointer;
}
.btn:hover{
    color: #000;
}

.btn:focus{
    outline: none;
}

.srch:focus{
    outline: none;
}

	</style>
</head>
<body>
	<nav class="navbar">
		<div class="navdiv">
			<div class="logo"><a href="#">GHOST play</a> </div>
			<ul>
				<li><a href="index.php">Home</a></li>
				<li><a href="show.php">Shows</a></li>
                <li><a href="#">Language</a></li>
				<li><a href="#">Dowload</a></li>
				<button><a href="login.php">SignIn</a></button>
			</ul>
             <div class="search">
                <input class="srch" type="search" name="" placeholder="Type To text">
                <a href="#"> <button class="btn">Search</button></a>
            </div>
		</div>

	</nav>
</body>
</html>
