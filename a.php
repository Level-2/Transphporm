<?php
$html = '<!doctype html><body>Hello</body>';


$doc = new \DomDocument();
$doc->loadHtml($html,  LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

echo $doc->saveXml($doc->doctype);
