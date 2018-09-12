<?php

namespace src;

use GeSHi;

/**
 * Class PostBuilder
 * @package App\Service
 */
class PostBuilder
{
    const DEFAULT_LANGUAGE = 'php';

    private $inPath = __DIR__ . '/../posts';

    private $outPath = __DIR__ . '/../build/posts';

    private $startBLockDelimiter = '<pre><code class="language';

    private $endBlockDelimiter = '</code></pre>';

    /**
     * @return array
     */
    public function getFiles(){
        $files = scandir($this->inPath);
        return $files;
    }

    /**
     * @param $file
     */
    public function buildFromMarkdown($file)
    {
        $content = file_get_contents($this->inPath . '/' . $file);
        $parsedown = new \Parsedown();

        $content = $parsedown->text($content);
        $content = $this->replaceContentBlocks($content);

        $file = str_replace('.md','.html', $file);
        file_put_contents($this->outPath . '/' . $file, $content);
    }

    /**
     * @param $content
     * @return mixed
     */
    private function replaceContentBlocks($content){
        $newContent = $content;
        $positions = $this->getCodeBlocksPositions($content);

        foreach ($positions as $startPos) {
            $stripedContent = substr($content, $startPos);
            $endPos = strpos($stripedContent, $this->endBlockDelimiter);
            $replaceContent = substr($content, $startPos, $endPos + strlen($this->endBlockDelimiter));

            $block = substr($content, $startPos, $endPos);
            $startCodePos = strpos($stripedContent,'">') + 2;
            $block = substr($block, $startCodePos);
            $block = htmlspecialchars_decode($block);

            $lang = $this->getCodeBlockLanguage($replaceContent);

            $geshi = new GeSHi($block, $lang);
            $block = $geshi->parse_code();

            $newContent = str_replace($replaceContent, $block, $newContent);
        }

        return $newContent;
    }

    /**
     * @param $content
     * @return array
     */
    private function getCodeBlocksPositions($content){
        $lastPos = 0;
        $positions = [];

        while (($lastPos = strpos($content, $this->startBLockDelimiter, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($this->startBLockDelimiter);
        }

        return $positions;
    }

    /**
     * @param $replaceContent
     * @return bool|string
     */
    private function getCodeBlockLanguage($replaceContent){
        $lang = self::DEFAULT_LANGUAGE;
        if(strpos($replaceContent, $this->startBLockDelimiter) === 0){
            $replaceContent = str_replace($this->startBLockDelimiter, '', $replaceContent);
            $endPos = strpos($replaceContent, '">');
            $lang = substr($replaceContent,0, $endPos);
            $lang = trim($lang,'-');
        }

        return $lang;
    }
}