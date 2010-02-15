<?php
include 'ndocs.class.php';
header('Content-type: text/plain; charset=utf-8');

$content = file_get_contents('./test.class.php');
$sections = NDocs::get_comment_sections($content);

foreach ($sections as $section)
{
	$headlines = NDocs::get_headlines($section);

	foreach ($headlines as $headline)
	{
		$content = NDocs::parse_headline($headline, $section);
		print_r($content);
	}

	echo "\n------------------------------------------------------------------------------------------------------------------------------------\n\n";
}
