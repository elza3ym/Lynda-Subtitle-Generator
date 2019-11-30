<?php
# Custom output
function e($msg, $err=FALSE)
{
	global $api;
	if ($api == TRUE) {
		$key = !!$err ? 'error' : 'success';
		$data[$key] = $msg;
		header('Content-Type: application/json');
		echo json_encode($data);
	} else {
		header('Content-Type: text/plain');
		echo $msg;
	}
	exit;
}



function get_transcript($url)
{
	$url = urldecode(trim($url));
	$pattern[0]		= "#^(.+).html$#i";
	$replacement[0]	= "$1/transcript";

	$pattern[1]		= "#^(.+)(\d)/?$#i";
	$replacement[1]	= "$1$2/transcript";

	$pattern[2]		= "#^(.+)(/transcript)$#i";
	$replacement[2]	= "$1$2";

	return preg_replace($pattern, $replacement, $url);
}

function get_path($url)
{
	$arr = array('root' => rtrim(DIR, '/'));

	if ( @preg_match('#^https?://(www.)?lynda.com(.+)/transcript$#i', $url, $param) ) {
		$param = explode('/', $param[2]);
		$arr['course'] = trim($param[1], '-')."-$param[2]";

	} else {
		// # local address
		// $param = end(explode('/', $url));
		// $arr['course'] = basename($param, strrchr($param, '.'));
		return FALSE;
	}

	$arr['full'] = $arr['root'].'/'.$arr['course'];
	return $arr;
}

function get_url_content($url)
{

	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cookie: ".file_get_contents('cookie.txt')
        ));
		$output = curl_exec($ch);
		curl_close($ch);
	} else {
		$output = @file_get_contents($url) or FALSE;
	}
	return $output;
}

function str_pure($str)
{
	return trim(str_replace(array('\\','/',':','*','?','"','<','>','|'), '', $str));
}

function to_srt($data, $path, $title)
{
	global $zip;

	if (function_exists('mb_convert_encoding')) {
		$data = mb_convert_encoding($data, 'UTF-8', 'HTML-ENTITIES');
	}
	
	$zip->addFromString($path.'/'.$title.'.srt', $data);
}

function parse_chapter_name($ch, $i)
{
	$i = $i<10?"0$i":$i;
	return "$i. " . preg_replace("/^\d+\. /", '', $ch);
}

function getSubtitle($course_id, $lesson) {
    $vtt = get_url_content("https://www.lynda.com/ajax/player/transcript?courseId=".$course_id."&videoId=".$lesson['id']);
    return $vtt;
}
function process_chapter($e, $path, $chno)
{
	$chapter = $e->find('span.chTitle', 0)->plaintext;
	$sections = $e->find('tr.showToggleDeltails');

	$chapter = parse_chapter_name($chapter, $chno);

	$dir = $path .'/'. str_pure($chapter);

	$j = 1;
	foreach ($sections as $section) {
		$num = $j<10?"0$j":$j;
		$title = "$num. ".$section->find('a', 0)->plaintext;
		$rows = $section->find('td.tC');
		$rows_num = count($rows);
		$sub = '';

		for ($i = 0; $i < $rows_num;) {
			$start = $rows[$i]->plaintext;

			// $lms   = explode(':', $rows[$i+1]->plaintext);
			// $lsec  = $lms[1] - 1;
			// $lsec  = $lsec<10?"0$lsec":$lsec;
			// $end   = "$lms[0]:$lsec";

			// Lynda.com has changed its transcript page structure
			if ($i == $rows_num - 1) {
				$lms  = explode(':', $start);
				$lmin = $lms[0] + 1;
				$lmin = $lmin<10?"0$lmin":$lmin;
				$end  = "$lmin:$lms[1]";
			} else {
				$lms   = explode(':', $rows[$i+1]->plaintext);
				if ($lms[1]>0) {
					$lsec = $lms[1] - 1;
				} else {
					$lsec = '59';
					$lms[0]--; 
					$lms[0] = $lms[0]<10?"0$lms[0]":$lms[0];
				}
				$lsec   = $lsec<10?"0$lsec":$lsec;
				$end    = "$lms[0]:$lsec";
			}

			$text  = preg_replace("/\s+/", ' ', trim($rows[$i]->next_sibling()->plaintext));
			$i++;
			$sub .= "$i".PHP_EOL;
			$sub .= "00:{$start},000 --> 00:{$end},990".PHP_EOL;
			$sub .= "$text".PHP_EOL.PHP_EOL;
		}
		
		to_srt( $sub, $dir, str_pure($title) );
		$j++;
	}
}

function get_file_address($filename)
{
	$pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
	$addr = substr($_SERVER['REQUEST_URI'], 0, $pos - 1);
	if ($pos = strrpos($addr, '.php')) {
		$addr = substr($addr, 0, strrpos($addr, basename($addr)));
	}
	return $addr . $filename;
}

function VTT_to_SRT($vtt) {
    // Get file paths
    $webVttFile = $vtt;

// Read the WebVTT file content into an array of lines
    $lines  = preg_split("/((\r?\n)|(\r\n?))/", $webVttFile);


// Convert all timestamp lines
// The first timestamp line is 3
    $length = count($lines);

    for ($index = 3; $index < $length; $index++)
    {
        // A line is a timestamp line if the second line above it is an empty line
        if ( trim($lines[$index - 2]) === '' )
        {
            $lines[$index] = str_replace('.', ',', $lines[$index]);
        }
    }

// Remove 2 first lines of WebVTT format
    unset($lines[0]);
    unset($lines[1]);

// Concatenate all other lines into the result file
    return implode('', $lines);

}