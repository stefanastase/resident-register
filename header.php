<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<script src="https://kit.fontawesome.com/007729e881.js" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdn.jsdelivr.net/jquery.shadow-animation/1/mainfile"></script>
<script src="js/jquery.animate-textshadow.js"></script>

<div id="header">
	<a href="index.php">Evidența Populației</a>
	<div id="left">
	<a href="#" id="show_menu"><i class="fas fa-bars"></i></a>
	</div>
	<div id="right">
	<!--- Verificam daca utilizatorul este conectat. --->
	<?php if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { ?>
			<a href="login.php"><i class="fas fa-sign-in-alt"></i>
			</a>
		<?php } else if(isset($_SESSION["username"])){ ?>
			<?php echo $_SESSION["username"]?>&nbsp<a href="logout.php"><i class="fas fa-sign-out-alt"></i></i>
			</a>
		<?php } ?>
	</div>
</div>
<div id="tint"></div>
<div id="menu">
<ul>
	<li id="home_li"><a href="home.php"><i class="fas fa-home"></i></a></li>
	<li><a href="persoane.php"><i class="fas fa-users"></i></a></li>
	<li><a href="acte.php"><i class="fas fa-id-card"></i></a></li>
	<li><a href="impozite.php"><i class="fas fa-receipt"></i></a></li>
	<li><a href="proprietati.php"><i class="fas fa-building"></i></a></li>
	<li><a href="programari.php"><i class="fas fa-calendar-check"></i></a></li>
	<!--- Verificam daca utilizatorul este administratorul. --->
	<?php if(isset($_SESSION["username"]) && $_SESSION["username"] == "admin") {?>
	<li id="admin_li"><a href="admin.php"><i class="fas fa-user-tie"></i></a></li>
	<?php } ?>
</ul> 
</div>
<br>
<script>
	$(document).ready(function(){
		var menuShown = false;
		$("#show_menu").click(function(){
    		$("#menu").slideToggle(100);
			menuShown = !menuShown;
			if(menuShown == true){
				$("#tint").animate({
					backgroundColor: 'rgba(0, 0, 0, 0.5)', 
					display: 'block'}, 100);
			}
			else if(menuShown == false){
				$("#tint").animate({
					backgroundColor: 'rgba(0, 0, 0, 0)', 
					display: 'none'}, 100);
			}
  		});
		$("#header").mouseenter(function(){
			$("#header").animate({
				color: 'black',
				backgroundColor: 'rgba(255, 255, 255, 1.0)',
				boxShadow: '0px 5px 3px -3px rgba(73, 73, 73, 0.6)',
				textShadow: '0px 0px 0px rgba(24, 24, 24, 0)'
				}, 200);
			$("#header a").animate({color: 'black'}, 200);
		});
		$("#header").mouseleave(function(){
			if(menuShown == false){
				$("#header").animate({
					color: 'white',
					backgroundColor: 'rgba(0, 0, 0, 0)',
					boxShadow: '0px 5px 3px -3px rgba(73, 73, 73, 0.0)',
					textShadow: '0px 1px 2px rgba(24, 24, 24, 1)'
					}, 200);
				$("#header a").animate({color: 'white'}, 200);
				}
			else{
				$("#header").animate({
				color: 'black',
				backgroundColor: 'rgba(255, 255, 255, 1.0)',
				boxShadow: '0px 5px 3px -3px rgba(73, 73, 73, 0.6)',
				textShadow: '0px 0px 0px rgba(24, 24, 24, 0)'
				}, 200);
				$("#header a").animate({color: 'black'}, 200);				
			}
		});
	});
</script>
