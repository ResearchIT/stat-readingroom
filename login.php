<?php
//
// login.php - Handle auth callbacks.
//

//
// This program receives the post-authentication callback form Okta,
// verifies the authenticity of the information provided, sets
// some session variables, and redirects to the original pre-authentication 
// calling page.
//

include_once($_SERVER['DOCUMENT_ROOT']. "/auth_oidc.php");
auth_oidc();

print "You should never see this.";

die();

