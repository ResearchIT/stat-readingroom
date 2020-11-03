# stat-readingroom
Source of https://statserve2.stat.iastate.edu/departmentAccess/readingRoom/

https://issues.its.iastate.edu/browse/IOPSINF-813

Existing app will be migrated to an OpenShift instance.
Existing DB table will be migrated to mariadb on dbX.las.

* This code has been modified to use Python Data Objects (PDO).
* The phpMyEdit class has been replaced with a version that supports PDOs, available from https://github.com/superfunnl/phpMyEdit-PHP7.0.git.
* PHPMailer is used to send mail instead of the stock mail() call as PHPMailer supports SMTP and can talk directly to mailhub.

## OpenShift Configuration

The following environment variables must be created and defined under Applications > Deployments > Environment in the OpenShift project.

* `DB_HOST` - FQDN of the database server.
* `DB_NAME` - name of the database.
* `DB_USER` - user to authenticate to the database as.
* `DB_PASS` - password used to authenticate `DB_USER`.
* `DEBUG` - if non-empty, disables sending of email.
* `CLIENT_ID` - the OAuth2 Client ID used for Okta OIDC auth.
* `CLIENT_SECRET` - the OAuth2 Client Secret used for Okta OIDC auth.
* `METADATA_URL` - URL of the OpenID configuration file (`https://iastate.okta.com/oauth2/default/.well-known/openid-configuration`)
* `REDIRECT_URI` - URI for Okta to redirect the client browser to after authentication. This program (`login.php`) validates the login parameters from Okta, sets session variables, then redirects back to the original calling page. (`https://_server.fqdn_/login.php`)
* `ADMIN_USERS` - a comma-separated list of NetIDs that can perform administrative functions such as patron/book management and "emailing scofflaws".
