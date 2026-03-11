<?php

$root = __DIR__ . '/../app/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach($iterator as $file){
    if($file->isFile()){
        $path = $file->getPathname();
        if(substr($path, -4) === '.php'){
            // skip inc, css, js, pdf folders
            if(strpos($path, DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR) !== false) continue;
            if(strpos($path, DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR) !== false) continue;
            if(strpos($path, DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR) !== false) continue;
            if(strpos($path, DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR) !== false) continue;
            $files[] = $path;
        }
    }
}

$modified = [];
foreach($files as $file){
    $orig = file_get_contents($file);
    $content = $orig;

    // Skip binary-like files
    if($content === false) continue;

    // 1) Wrap tables with div.table-responsive (avoid double wrap)
    $tableRegex = '/<table(\\s[^>]*)?>/i';
    preg_match_all($tableRegex, $content, $matches, PREG_OFFSET_CAPTURE);
    $offsetAdjust = 0;
    foreach($matches[0] as $m){
        $startPos = $m[1] + $offsetAdjust;
        // Check previous 200 chars for table-responsive
        $checkStart = max(0, $startPos - 200);
        $prev = substr($content, $checkStart, $startPos - $checkStart);
        if(strpos($prev, 'table-responsive') !== false) continue;
        // Insert wrapper before <table
        $content = substr_replace($content, '<div class="table-responsive">', $startPos, 0);
        $offsetAdjust += strlen('<div class="table-responsive">');
    }
    // Close wrappers after </table>
    $closeRegex = '/<\/table>/i';
    preg_match_all($closeRegex, $content, $closes, PREG_OFFSET_CAPTURE);
    $offsetAdjust = 0;
    foreach($closes[0] as $c){
        $pos = $c[1] + strlen($c[0]) + $offsetAdjust;
        // Check following 40 chars to avoid double closing
        $next = substr($content, $pos, 40);
        if(strpos($next, '</div>') !== false && strpos($next, 'table-responsive') !== false) continue;
        $content = substr_replace($content, '</div>', $pos, 0);
        $offsetAdjust += strlen('</div>');
    }

    // 2) Add class form-row to <form> elements if not present
    $content = preg_replace_callback('/<form(\\s[^>]*)?>/i', function($m){
        $tag = $m[0];
        if(stripos($tag, 'class=') !== false){
            // add form-row if not present
            if(stripos($tag, 'form-row') !== false) return $tag;
            return preg_replace('/class=(\"|\')(.*?)\1/i', 'class="$2 form-row"', $tag);
        }else{
            // insert class
            return preg_replace('/<form/i', '<form class="form-row"', $tag);
        }
    }, $content);

    if($content !== $orig){
        // backup
        copy($file, $file.'.bak');
        file_put_contents($file, $content);
        $modified[] = $file;
    }
}

echo "Modified files:\n";
foreach($modified as $m) echo $m . "\n";

?>