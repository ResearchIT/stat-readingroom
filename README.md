# stat-readingroom
Source of https://statserve2.stat.iastate.edu/departmentAccess/readingRoom/

https://issues.its.iastate.edu/browse/IOPSINF-813

Existing app will be migrated to an OpenShift instance.
Existing DB table will be migrated to mariadb on dbX.las.

* This code has been modified to use Python Data Objects (PDO).
* The phpMyEdit class has been replaced with a version that supports PDOs, available from https://github.com/superfunnl/phpMyEdit-PHP7.0.git.
* PHPMailer is used to send mail instead of the stock mail() call as PHPMailer supports SMTP and can talk directly to mailhub.
