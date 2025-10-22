<?php
// This directory is protected by .htaccess
// Access denied
http_response_code(403);
header('HTTP/1.0 403 Forbidden');
die('403 Forbidden');

