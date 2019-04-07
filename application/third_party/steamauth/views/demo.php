<?php
    //require ('steamauth/steamauth.php');  
	unset($_SESSION['steam_uptodate']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SteamAuth Demo</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .table {
            table-layout: fixed;
            word-wrap: break-word;
        }
    </style>
  </head>
  
  
  <body style="background-color: #EEE;">
    <div class="container" style="margin-top: 30px; margin-bottom: 30px; padding-bottom: 10px; background-color: #FFF;">
		<h1>SteamAuth Demo</h1>
		<span class="small pull-left" style="padding-right: 10px;">for SteamAuth 3.2</span>
		<hr>
		<?php
			if(!isset($_SESSION['steamid']))
			{
				echo "<div style='margin: 30px auto; text-align: center;'>Welcome Guest! Please log in!<br>";
				//loginbutton();
				echo "</div>";
			} else
			{
				//include ('userInfo.php');
				if (empty($_SESSION['steam_uptodate']) or empty($_SESSION['steam_personaname'])) {
					//require 'SteamConfig.php';
					log_message('debug', '=== SteamAPIKey :: '.$this->config->item('apikey'));
					$dbg = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.$this->config->item('apikey')."&steamids=".$_SESSION['steamid'];
					$url = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$this->config->item('apikey')."&steamids=".$_SESSION['steamid']);
					log_message('debug', '=== SteamRequest Link $dbg :: '.$dbg);
					$content = json_decode($url, true);
					/* DEBUG */
					foreach ($content['response']['players'][0] as $line)
							log_message('debug', '=== SteamStream $content :: '.$line);
							
					$_SESSION['steam_steamid'] 					= $content['response']['players'][0]['steamid'];
					$_SESSION['steam_communityvisibilitystate'] = $content['response']['players'][0]['communityvisibilitystate'];
					$_SESSION['steam_profilestate'] 			= $content['response']['players'][0]['profilestate'];
					$_SESSION['steam_personaname'] 				= $content['response']['players'][0]['personaname'];
					//$_SESSION['steam_lastlogoff'] 				= $content['response']['players'][0]['lastlogoff'];
					if (isset($content['response']['players'][0]['lastlogoff']))
					{ 
						$_SESSION['steam_lastlogoff'] = $content['response']['players'][0]['lastlogoff'];
					}
					else
					{
						$_SESSION['steam_lastlogoff'] = "No LastLogOffInfo";
					}
					$_SESSION['steam_profileurl'] 				= $content['response']['players'][0]['profileurl'];
					$_SESSION['steam_avatar'] 					= $content['response']['players'][0]['avatar'];
					$_SESSION['steam_avatarmedium'] 			= $content['response']['players'][0]['avatarmedium'];
					$_SESSION['steam_avatarfull'] 				= $content['response']['players'][0]['avatarfull'];
					$_SESSION['steam_personastate'] 			= $content['response']['players'][0]['personastate'];
					if (isset($content['response']['players'][0]['realname']))
					{ 
						$_SESSION['steam_realname'] = $content['response']['players'][0]['realname'];
					}
					else
					{
						$_SESSION['steam_realname'] = "Real name not given";
					}
					$_SESSION['steam_primaryclanid'] = $content['response']['players'][0]['primaryclanid'];
					$_SESSION['steam_timecreated'] = $content['response']['players'][0]['timecreated'];
					$_SESSION['steam_uptodate'] = time();
				}

				$steamprofile['steamid'] = $_SESSION['steam_steamid'];
				$steamprofile['communityvisibilitystate'] = $_SESSION['steam_communityvisibilitystate'];
				$steamprofile['profilestate'] = $_SESSION['steam_profilestate'];
				$steamprofile['personaname'] = $_SESSION['steam_personaname'];
				$steamprofile['lastlogoff'] = $_SESSION['steam_lastlogoff']; //'No such item in array anymore???';
				$steamprofile['profileurl'] = $_SESSION['steam_profileurl'];
				$steamprofile['avatar'] = $_SESSION['steam_avatar'];
				$steamprofile['avatarmedium'] = $_SESSION['steam_avatarmedium'];
				$steamprofile['avatarfull'] = $_SESSION['steam_avatarfull'];
				$steamprofile['personastate'] = $_SESSION['steam_personastate'];
				$steamprofile['realname'] = $_SESSION['steam_realname'];
				$steamprofile['primaryclanid'] = $_SESSION['steam_primaryclanid'];
				$steamprofile['timecreated'] = $_SESSION['steam_timecreated'];
				$steamprofile['uptodate'] = $_SESSION['steam_uptodate'];

				// Version 4.0
				
		?>	
				<div style='float:left;'>
					<a href='https://github.com/SmItH197/SteamAuthentication'>
						<button class='btn btn-success' style='margin: 2px 3px;' type='button'>GitHub Repo</button>
					</a>
					<a href='https://github.com/SmItH197/SteamAuthentication/releases'>
						<button class='btn btn-warning' style='margin: 2px 3px;' type='button'>Download</button>
					</a>
				</div>
				<br>
				<br>
				<h4 style='margin-bottom: 3px; float:left;'>Steam WebAPI-Output:</h4><span style='float:right;'></span>
				<table class='table table-striped'>
					<tr>
						<td><b>Variable name</b></td>
						<td><b>Value</b></td>
						<td><b>Description</b></td>
					</tr>
					<tr>
						<td>$steamprofile['steamid']</td>
						<td><?=$steamprofile['steamid']?></td>
						<td>SteamID64 of the user</td>
					</tr>
					<tr>
						<td>$steamprofile['communityvisibilitystate']</td>
						<td><?=$steamprofile['communityvisibilitystate']?></td>
						<td>1 - Account not visible; 3 - Account is public (Depends on the relationship of your account to the others)</td>
					</tr>
					<tr>
						<td>$steamprofile['profilestate']</td>
						<td><?=$steamprofile['profilestate']?></td>
						<td>1 - The user has a Steam Community profile; 0 - if not</td>
					</tr>
					<tr>
						<td>$steamprofile['personaname']</td>
						<td><?=$steamprofile['personaname']?></td>
						<td>Public name of the user</td>
					</tr>
					<tr>
						<td>$steamprofile['lastlogoff']</td>
						<td><?=$steamprofile['lastlogoff']?></td>
						<td>
							<a href='http://www.unixtimestamp.com/' target='_blank'>Unix timestamp</a> of the user's last logoff
						</td>
					</tr>
					<tr>
						<td>$steamprofile['profileurl']</td>
						<td><?=$steamprofile['profileurl']?></td>
						<td>Link to the user's profile</td>
					</tr>
					<tr>
						<td>$steamprofile['avatar']</td>
						<td>
							<img src='<?=$steamprofile['avatar']?>'><br>
							<?=$steamprofile['avatar']?>
						</td>
						<td>Address of the user's 32x32px avatar</td>
					</tr>
					<tr>
						<td>$steamprofile['avatarmedium']</td>
						<td>
							<img src='<?=$steamprofile['avatarmedium']?>'><br>
							<?=$steamprofile['avatarmedium']?>
						</td>
						<td>Address of the user's 64x64px avatar</td>
					</tr>
					<tr>
						<td>$steamprofile['avatarfull']</td>
						<td>
							<img src='<?=$steamprofile['avatarfull']?>'><br>
							<?=$steamprofile['avatarfull']?>
						</td>
						<td>Address of the user's 184x184px avatar</td>
					</tr>
					<tr>
						<td>$steamprofile['personastate']</td>
						<td><?=$steamprofile['personastate']?></td>
						<td>0 - Offline, 1 - Online, 2 - Busy, 3 - Away, 4 - Snooze, 5 - looking to trade, 6 - looking to play</td>
					</tr>
					<tr>
						<td>$steamprofile['realname']</td>
						<td><?=$steamprofile['realname']?></td>
						<td>"Real" name</td>
					</tr>
					<tr>
						<td>$steamprofile['primaryclanid']</td>
						<td><?=$steamprofile['primaryclanid']?></td>
						<td>The ID of the user's primary group</td>
					</tr>
					<tr>
						<td>$steamprofile['timecreated']</td>
						<td><?=$steamprofile['timecreated']?>
						</td>
						<td>
							<a href='http://www.unixtimestamp.com/' target='_blank'>Unix timestamp</a> for the time the user's account was created
						</td>
					</tr>
					<tr>
						<td>$steamprofile['uptodate']</td>
						<td><?=$steamprofile['uptodate']?></td>
						<td>
							<a href='http://www.unixtimestamp.com/' target='_blank'>Unix timestamp</a> for the time the user's account information was last updated
						</td>
					</tr>
				</table>
				<?php
				}    
		?>
		<hr>
		<div class="pull-right">
			<i>This page is powered by <a href="http://steampowered.com">Steam</a></i>
		</div>
		<a href="https://github.com/SmItH197/SteamAuthentication">GitHub Repo</a><br>
		Demo page by <a href="https://github.com/blackcetha" target="_blank">BlackCetha</a>
	</div>
	
	<!--Version 4.0--> 
  </body>
</html>
