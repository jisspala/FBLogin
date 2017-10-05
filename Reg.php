<?php
error_log("fb started");
if(isset($_GET['error']))
{
	if($_GET['error']=='access_denied')
    {
			// User don't give permission
    }
}
require_once 'fbConfig.php';
if(isset($accessToken))
{
	if(isset($_SESSION['facebook_access_token']))
    {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
    else
    {
		// Put short-lived access token in session
		$_SESSION['facebook_access_token'] = (string) $accessToken;
		
	  	// OAuth 2.0 client handler helps to manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();
		
		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
	try
    {
		$profileRequest = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
		$user_info = $profileRequest->getGraphNode()->asArray();
	} 
    catch(FacebookResponseException $e)
    {
		error_log('FB Graph returned an error: ' . $e->getMessage());
		session_destroy();
		// Redirect user back to app login page
		//header("Location: ./");
		exit;
	} 
    catch(FacebookSDKException $e) {
		error_log('FB Facebook SDK returned an error: ' . $e->getMessage());
		exit;
	}
    if(isset($user_info['email']))
    {
        // success
        print_r($user_info);
		// Download Fb prof Image by curl				
        $ch = curl_init ("http://graph.facebook.com/".$user_info['id']."/picture?type=large");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER  => true,CURLOPT_FOLLOWLOCATION  => true,));
        $rawdata1=curl_exec ($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close ($ch);
        $ch2 = curl_init ($redirectUrl);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_BINARYTRANSFER,1);
        $rawdata=curl_exec ($ch2);
        curl_close ($ch2);
        $aid = md5(rand(0,999));
        $path_to_save="";
        $fp = fopen("profImag.jpg",'w'); // create a new file profImag.jpg and write to it , the get raw data from FB.
        fwrite($fp, $rawdata);
        fclose($fp);
	$logoutURL = $helper->getLogoutUrl($accessToken, $redirectURL.'logout.php');
	
    }

    else
    {
        // Get login url
        $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
        {
            header("Location:".$loginURL.""); // go to fb pafe

        }
        //$output = '<a href="'.htmlspecialchars($loginURL).'"><img src="images/fblogin-btn.png"></a>';
    }
}
?>
