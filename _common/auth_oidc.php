<?php
//
// auth_oidc.php - OIDC auth code for Okta
//
// Jason White (jdwhite@iastate.edu)
//
// Based heavily on sample code from https://github.com/aaronpk/quick-php-authentication
//
// The following environment variables are used by auth_oidc:
//
//   CLIENT_ID - An arbitrary string provided by the Okta admins.
//
//   CLIENT_SECRET - An arbirary string provided by Okta admins. Keep this safe.
//
//   METADATA_URL - URL containing metadata specific to your application.
//
//   REDIRECT_URI - The callback URL Okta will use after successful authentication.
//
// USAGE:
//
//   include_once($_SERVER['DOCUMENT_ROOT']. "/auth_oidc.php");
//
// Before calling or referencing any protected code:
//
//	 auth_oidc($_SERVER['PHP_SELF']);
//
// At which point useful information can be referenced via the _SESSION['profile'] hash.
//

function auth_oidc($callback_url = null)
//
// Handle authentication and logout.
//
{
	session_start();

	error_log("callback_url='{$callback_url}'");

	if (!empty($callback_url)) {
		error_log("Setting POST_AUTH_URL to $callback_url");
		$_SESSION['POST_AUTH_URL'] = $callback_url;
	}

	$CLIENT_ID = getenv('CLIENT_ID');
	$CLIENT_SECRET = getenv('CLIENT_SECRET');
	$METADATA_URL = getenv('METADATA_URL');
	$REDIRECT_URI = getenv('REDIRECT_URI');

	empty($CLIENT_ID)     && die("CLIENT_ID is not set.");
	empty($CLIENT_SECRET) && die("CLIENT_SECRET is not set.");
	empty($METADATA_URL)  && die("METADATA_URL is not set.");
	empty($REDIRECT_URI)  && die("REDIRECT_URI is not set.");

	$METADATA = http($METADATA_URL);

	//
	// LOGOUT. Zorch session variables. Redir to Okta logout endpoint.
	//
	if (isset($_GET['logout'])) {
		error_log("Logging out.");
		unset($_SESSION['username']);
		unset($_SESSION['sub']);
		$logout_url = $METADATA->end_session_endpoint;

		header("Location: {$logout_url}");
		die();
	}

	//
	// LOGGED IN, so just return.
	//
	if (isset($_SESSION['sub'])) {
		error_log("Already logged in.");
		return;
	}

	//
	// NOT LOGGED IN - Perform authenticaiton.
	//
	
	//error_log(print_r($_GET, TRUE));

	if (!isset($_GET['code'])) {
		//
		// Start the authentication process by redirecting to Okta.
		//
		error_log("No auth code; starting authentication process");
		$_SESSION['state'] = bin2hex(random_bytes(5));
		$_SESSION['code_verifier'] = bin2hex(random_bytes(50));
		$code_challenge = base64_urlencode(hash('sha256', $_SESSION['code_verifier'], true));

		$authorize_url = $METADATA->authorization_endpoint.'?'.http_build_query([
			'response_type' => 'code',
			'client_id' => $CLIENT_ID,
			'redirect_uri' => $REDIRECT_URI,
			'state' => $_SESSION['state'],
			'scope' => 'openid profile',
			'code_challenge' => $code_challenge,
			'code_challenge_method' => 'S256',
		]);

		header("Location: $authorize_url");
		die();

	} else {
		//
		// Received authorization code from Okta via callback.
		//
		error_log("Have auth code; starting auth verification process");

		// Verify 'state' parameter to help prevent forgery.
		if ($_SESSION['state'] != $_GET['state']) {
			die('Authorization server returned an invalid state parameter');
		}

		if (isset($_GET['error'])) {
			die('Authorization server returned an error: '.htmlspecialchars($_GET['error']));
		}

		// Fetch access token using authorization code.
		$response = http($METADATA->token_endpoint, [
			'grant_type' => 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri' => $REDIRECT_URI,
			'client_id' => $CLIENT_ID,
			'client_secret' => $CLIENT_SECRET,
			'code_verifier' => $_SESSION['code_verifier'],
		]);

		if (!isset($response->access_token)) {
			die('Error fetching access token');
		}

		// Fetch user information using access token.
		$userinfo = http($METADATA->userinfo_endpoint, [
			'access_token' => $response->access_token,
		]);

		error_log(print_r($userinfo, TRUE));

		// Set some useful session variables.
		if ($userinfo->sub) {
			$_SESSION['sub'] = $userinfo->sub;
			$_SESSION['username'] = $userinfo->preferred_username;
			$_SESSION['profile'] = $userinfo;

			header("Location: ". $_SESSION['POST_AUTH_URL']);
		    die();
		}

		die('Invalid data from userinfo endpoint');
	}
}

// Base64-urlencoding is a simple variation on base64-encoding.
// Instead of +/ we use -_, and the trailing = are removed.
function base64_urlencode($string) {
	return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
}

function http($url, $params=false) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($params) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	}
	return json_decode(curl_exec($ch));
}

?>
