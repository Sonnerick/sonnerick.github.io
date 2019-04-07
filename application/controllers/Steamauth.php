<?php
defined('BASEPATH') OR exit('No direct script access allowed');

ob_start();
/* Ругается, что сессия уже активна! Видимо она инициализируется в ion_auth
 * session_start();
*/

class Steamauth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		log_message('debug', '=== Construct :: Try to load OpenID_Model3');
		$this->load->library('Openid_model3');
		log_message('debug', '=== Construct :: OpenID_Model3 loaded');
	}

	function logoutbutton() {
		echo "<form action='' method='get'><button name='logout' type='submit'>Logout</button></form>"; //logout button
	}

	function loginbutton($buttonstyle = "square") {
		$button['rectangle'] = "01";
		$button['square'] = "02";
		$button = "<a href='?login'><img src='https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_".$button[$buttonstyle].".png'></a>";
		
		echo $button;
	}
	
	
	public function demo()
	{
		$this->load->view('demo');
	}
	
	public function login()
	{
	}
	
	/* Ссылка http://stin.loc/steamauth */
	public function index()
	{
		log_message('debug', 'Index function begin');
		if (isset($_GET['login']))
		{
			log_message('debug', '=== SteamAuth/Index Controller :: if (isset($_GET[login]))');
			try 
			{
				log_message('debug', '=== SteamAuth/Index Controller :: try');
				$openid = new Openid_model3();
				
				if(!$openid->mode)
				{
					$openid->identity = 'https://steamcommunity.com/openid';
					header('Location: ' . $openid->authUrl());
				} elseif ($openid->mode == 'cancel') {
					echo 'User has canceled authentication!';
				} else {
					if($openid->validate()) {
						$id = $openid->identity;
						$ptn = "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
						preg_match($ptn, $id, $matches);
						
						$_SESSION['steamid'] = $matches[1];
						if (!headers_sent()) {
							/* Ссылка для перехода после авторизации в Steam */
							header('Location: '.'demo');
							exit;
						} else {
							?>
							<script type="text/javascript">
								window.location.href="<?=$steamauth['loginpage']?>";
							</script>
							<noscript>
								<meta http-equiv="refresh" content="0;url=<?=$steamauth['loginpage']?>" />
							</noscript>
							<?php
							exit;
						}
					}
					else
					{
						echo "User is not logged in.\n";
					}
				}
			} catch(ErrorException $e) {
				echo $e->getMessage();
			}
		}
		else
		{
			echo "False in login IF";
		}

		if (isset($_GET['logout'])){
			require 'SteamConfig.php';
			session_unset();
			session_destroy();
			header('Location: '.$steamauth['logoutpage']);
			exit;
		}

		if (isset($_GET['update'])){
			unset($_SESSION['steam_uptodate']);
			require 'userInfo.php';
			header('Location: '.$_SERVER['PHP_SELF']);
			exit;
		}
	}

	// Version 4.0
}
?>
