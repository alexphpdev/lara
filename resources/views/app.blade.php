<!DOCTYPE html>
<html>
<head>
	<title>Test</title>
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap-select/bootstrap-select.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.css">

	<script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="/lib/jquery-ui/jquery-ui.js"></script>
	<script type="text/javascript" src="/lib/moment/moment.js"></script>
	<script type="text/javascript" src="/lib/moment/locales/ru.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/js/transition.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/js/collapse.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/script.js"></script>
	<script type="text/javascript" src="/lib/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap-select/bootstrap-select.min.js"></script>
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
	@yield('content')

	<img id="preloader" src="/img/loading.gif">
	<div id="dark_bg"></div>
</body>
</html>