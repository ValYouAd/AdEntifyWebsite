<?php $pattern = '/(?:^|\s)(\#\w+)/';
preg_match_all($pattern, 'hello you #sea #valras', $matches, PREG_OFFSET_CAPTURE);
foreach($matches[0] as $match) {
    echo str_replace('#', '', $match[0]);
}
