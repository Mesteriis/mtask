<?
ini_set("display_errors", 1);
error_reporting(E_ERROR && E_WARNING);

require_once(dirname(__FILE__) . '/app/lib/mtask.php');

?><!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Document</title>
		<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdn.datatables.net/1.10.12/css/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="/css/template_styles.css">

		<script type="text/javascript" src="//yastatic.net/jquery/3.1.0/jquery.min.js"></script>
		<script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap4.min.js"></script>
		<script type="text/javascript" src="/js/template.js"></script>
	</head>
	<body>
	<header>
		<div class="container">
			<div class="row">
				<p class="col-md-4 logo">
					MTask
				</p>
			</div>
		</div>
	</header>

	<section class="content">
		<div class="container">
			<div class="row">
				<div class="col-md-12">

