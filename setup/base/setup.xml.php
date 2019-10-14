<?php
/**
 * File:        setup/base/danneo.xml.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 *  Class XML  
 * -------------- */
class XML
{
    public $parser;
    public $parseout = array();
    public $total = array();
    public $cdata = '';
    public $count = 0;
 
	public function __construct()
	{
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 0);
        xml_set_character_data_handler($this->parser, 'parse_cdata');
        xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
    }

    /**
     * Parse
     */      
    public function parse($data)
    {
        if (!$data)
            return false;
        if (!is_readable($data))
            die('Not read !!!');
        $data = @file_get_contents($data);
        xml_parse($this->parser, $data);
        // $this->cdata = '';
        xml_parser_free($this->parser);
        return $this->parseout;

    }

    /**
     * Tag open
     */     
    public function tag_open($parser, $tag, $attributes)
    {
        $this->cdata = '';
        array_unshift($this->total, array('tag' => $tag, 
                                          'attributes' => $attributes, 
                                          'count' => ++$this->count
                                          ));
    }

    /**
     * Parse cdata
     */     
    public function parse_cdata($parser, $cdata)
    {
        $this->cdata .= $cdata;
    }

    /**
     * Tag close
     */     
    public function tag_close($parser, $tag)
    {
        $tags = array_shift($this->total);
        $total_array = $tags['attributes'];
        if ($tags['tag'] != $tag) {
            return;
        }

        if (trim($this->cdata) != '' || $tags['count'] == $this->count) {
            if (sizeof($total_array) == 0) {
                $total_array = $this->uunconv_cdata($this->cdata);
            } else {
                $this->get_depth($total_array, 'vals', $this->uunconv_cdata($this->cdata));
            }
        }

        if (isset($this->total[0])) {
            $this->get_depth($this->total[0]['attributes'], $tag, $total_array);
        } else {
            $this->parseout = $total_array;
        }
        $this->cdata = '';
    }

    /**
     * Get depth
     */     
	public function get_depth(&$child, $i, $value)
    {
        if (!is_array($child) || !in_array($i, array_keys($child))) {
            $child[$i] = $value;
        } elseif (is_array($child[$i]) && isset($child[$i][0])) {
            $child[$i][] = $value;
        } else {
            $child[$i] = array($child[$i]);
            $child[$i][] = $value;
        }
    }

    /**
     * Uunconv cdata
     */     
	public function uunconv_cdata($s)
    {
        $s = str_replace('<!ў|CDATA|', '<![CDATA[', $s);
        $s = str_replace('|ў]>', ']]>', $s);
        return $s;
    }
}
