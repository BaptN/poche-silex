<?php
namespace poche;

class Functions {

    function __construct()
    {}

    public function fetchContent($url) {
        $html = file_get_contents($url);
        if (function_exists('tidy_parse_string')) {
            $tidy = tidy_parse_string($html, array(), 'UTF8');
            $tidy->cleanRepair();
            $html = $tidy->value;
        }

        $readability = new Readability($html, $url);
        $readability->debug = false;
        $readability->convertLinksToFootnotes = true;
        $result = $readability->init();
        $output = array();
        if ($result) {
            $output['title'] = $readability->getTitle()->textContent;

            $content = $readability->getContent()->innerHTML;

            // if we've got Tidy, let's clean it up for output
            if (function_exists('tidy_parse_string')) {
                $tidy = tidy_parse_string($content,
                    array('indent'=>true, 'show-body-only'=>true),
                    'UTF8');
                $tidy->cleanRepair();
                $content = $tidy->value;
            }
            $output['content'] = $content;
        } else {
            $output['title'] = 'Oops';
            $output['content'] = 'Looks like we couldn\'t find the content.';
        }

        return $output;
    }
}