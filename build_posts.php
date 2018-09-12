<?php

use src\PostBuilder;

require '_bootstrap.php';

echo "Build posts ... \n";
$postBuilder = new PostBuilder();
$files = $postBuilder->getFiles();
foreach($files as $file) {
    if(in_array($file, ['.', '..'])){ continue; }
    echo "generate - $file ... \n";
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $postBuilder->buildFromMarkdown($file);
}

echo "Job is done \n";