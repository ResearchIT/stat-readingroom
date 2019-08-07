require 'rubygems'
require 'hpricot'

doc = Hpricot(ARGF)
title = doc.at("title").inner_html.to_s.gsub(/(at )?(Iow(a?) State University)|(ISU)/, "").gsub(/Department of Statistics( - )?/, "").strip

header = <<HEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>#{title}. Statistics, ISU</title>
	<link href="/_common/screen.css" type="text/css" rel="stylesheet" />
</head>
<body>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/_common/header.html"); ?>

HEADER

puts header