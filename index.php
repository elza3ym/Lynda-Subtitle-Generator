<?php
ini_set('display_errors', 0);
/**
 * Lynda Subtitle Generator - PHP application
 * https://github.com/qolami/Lynda-Subtitle-Generator
 * Copyright 2013 Hashem Qolami <hashem@qolami.com>
 * Version 1.0.0-rc.1
 * Released under the MIT and GPL licenses.
 */
require_once 'functions.php';

# App version
$version = '2.0.0-Beta';

if (! isset($_GET['url'])) {
    include 'inc/view.php';
    exit;
}

# Path to subtitle folder
define('DIR', 'subtitles');

# File lifetime
define('FILE_LIFETIME', 7*24*3600);

# Get transcript url
$url = $_GET['url'] or e('Insert a URL to generate subtitles', TRUE);

# API
$api = isset($_GET['api']) ? !!$_GET['api'] : FALSE;

# No time limit
@set_time_limit(0);
@ini_set("max_execution_time", 0);

# Load library
include 'lib/simple_html_dom.php';


############################################################
######                   Controller                   ######
############################################################

# Make instances
$html = new simple_html_dom;
$zip = new ZipArchive;
$OURL = $url;

function process_course($course_id, $data, $path) {
    global $zip;
    foreach ($data as $item) {
        $chapter = $path . '/'.$item["chapter"];
        foreach ($item['lessons'] as $lesson) {
            $data = getSubtitle($course_id, $lesson);
            $zip->addFromString($chapter.'/'.'#'.$lesson["number"].' - '.$lesson["title"].'.vtt', $data);
        }
    }
}
# Transcript path
$url = get_transcript($url);

# Course path
$path = get_path($url) or e("Unable to fetch transcript from: <strong><i>$url</i></strong>", TRUE);

$zip_file = $path['full'].'.zip';

# Check file: existence and lifetime
if (file_exists($zip_file) && time() - filemtime($zip_file) < FILE_LIFETIME) {
    e(get_file_address($zip_file));
}


# Get URL content
$content = get_url_content($OURL) or e("Unable to load data from: <strong><i>$url</i></strong>", TRUE);



# Load the DOM
$dom = new DOMDocument();
$internalErrors = libxml_use_internal_errors(true);
libxml_use_internal_errors($internalErrors);
$dom->loadHTML($content);
$xpath = new DOMXpath($dom);
$chs = $xpath->query('//ul[contains(@class, "course-toc")]/li[@role="presentation"]') or e("Unable to find chapters on: <strong><i>$url</i></strong>", TRUE);

$course_id = $xpath->query('//div/@data-course-id')->item(0)->nodeValue or e("Unable to find Course ID on: <strong><i>$url</i></strong>", TRUE);

if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    $out = [];
    $x = 0;
    $number = "1";
    foreach ($chs as $ch) {
        $innerHTML = $dom->saveHTML($ch);
        $domN = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $domN->loadHTML($innerHTML);
        libxml_use_internal_errors($internalErrors);
        $xpathN = new DOMXPath($domN);
        $chapterTitle = $xpathN->query("//h4")->item(0)->nodeValue;
        $out[$x]["chapter"] = trim($chapterTitle);
        $y = 0;
        foreach ($xpathN->query('//li[@data-video-id]//a') as $video) {
            $lesson_id = $video->attributes->getNamedItem("data-ga-value")->textContent;
            $out[$x]["lessons"][$y]["id"] = $lesson_id;
            $out[$x]["lessons"][$y]["title"] = trim($video->nodeValue);
            $out[$x]["lessons"][$y]["number"] = $number;
            $y++;
            $number++;
        }
        $x++;
    }
    process_course($course_id, $out, $path['course']);
    $zip->close();

    $output = array(
        'data'	=> get_file_address($zip_file),
        'err'	=> FALSE
    );

} else {
    $output = array(
        'data'	=> 'Zip compression failed!',
        'err'	=> TRUE
    );
}

# Clear DOM object
$html->clear();

# Free memory
unset($html);

e($output['data'], $output['err']);