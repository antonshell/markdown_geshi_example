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

    private $startBlockDelimiter = '<pre><code class="language';

    private $endBlockDelimiter = '</code></pre>';

    /**
     * Get list of markdown files
     *
     * @return array
     */
    public function getFiles(){
        $files = scandir($this->inPath);
        return $files;
    }

    /**
     * Parse markdown
     * Copy from .md to .html file
     *
     * @param $file
     */
    public function buildFromMarkdown($file)
    {
        // get source content
        $content = file_get_contents($this->inPath . '/' . $file);

        // parse markdown
        $parsedown = new \Parsedown();
        $content = $parsedown->text($content);

        // highlight code blocks with geshi
        $content = $this->replaceContentBlocks($content);

        // save to html file
        $file = str_replace('.md','.html', $file);
        file_put_contents($this->outPath . '/' . $file, $content);
    }

    /**
     * highlight code blocks with geshi
     *
     * @param $content
     * @return mixed
     */
    private function replaceContentBlocks($content){
        $newContent = $content;

        // get start positions of code blocks
        $positions = $this->getCodeBlocksPositions($content);

        // iterate code blocks
        foreach ($positions as $startPos) {
            // get end position, get code block form text
            $stripedContent = substr($content, $startPos);
            $endPos = strpos($stripedContent, $this->endBlockDelimiter);
            $replaceContent = substr($content, $startPos, $endPos + strlen($this->endBlockDelimiter));


            $block = substr($content, $startPos, $endPos);
            $startCodePos = strpos($stripedContent,'">') + 2;
            $block = substr($block, $startCodePos);
            $block = htmlspecialchars_decode($block);

            // detect code block language
            $lang = $this->getCodeBlockLanguage($replaceContent);

            // highlight code block with geshi
            $geshi = new GeSHi($block, $lang);
            $block = $geshi->parse_code();

            // replace source code with highlighted
            $newContent = str_replace($replaceContent, $block, $newContent);
        }

        return $newContent;
    }

    /**
     * Get start positions of code blocks
     *
     * @param $content
     * @return array
     */
    private function getCodeBlocksPositions($content){
        $lastPos = 0;
        $positions = [];

        while (($lastPos = strpos($content, $this->startBlockDelimiter, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($this->startBlockDelimiter);
        }

        return $positions;
    }

    /**
     * detect code block language
     *
     * @param $replaceContent
     * @return bool|string
     */
    private function getCodeBlockLanguage($replaceContent){
        $lang = self::DEFAULT_LANGUAGE;
        if(strpos($replaceContent, $this->startBlockDelimiter) === 0){
            $replaceContent = str_replace($this->startBlockDelimiter, '', $replaceContent);
            $endPos = strpos($replaceContent, '">');
            $lang = substr($replaceContent,0, $endPos);
            $lang = trim($lang,'-');
        }

        return $lang;
    }
}