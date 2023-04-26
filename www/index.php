<?php

require_once(__DIR__ . "/../vendor/autoload.php");

$title = "FCM TEST ".date("Y-m-d H:i:s");

?>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
<script src="//www.gstatic.com/firebasejs/7.0.0/firebase-app.js"></script>
<script src="//www.gstatic.com/firebasejs/7.0.0/firebase-messaging.js"></script>
<script src="//www.gstatic.com/firebasejs/7.0.0/firebase-analytics.js"></script>
<script src="/firebase-messaging-worker-install.js?<?php echo time(); ?>"></script>
<script>
    console.log('Set timer 3000 for requesting permission');
    setTimeout(function () {
        requestPushPermission();
    }, 3000);
</script>
<h1><?php echo $title; ?></h1>
</body>
</html>

