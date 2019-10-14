<?php
/**
 * File:        setup/base/danneo.template.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 *  Class Template  
 * --------------- */
class Template
{
    public $manuale = array();

	public function __construct()
	{
	}

    /**
     * Create
     */     
    public function create($tpl)
    {
        if (in_array($tpl, $this->manuale)) {
            $contents = $manuale[$tpl];
        } else {
            $contents = @file_get_contents("template/" . $tpl . ".tpl");
            $mtpl = str_replace('/', '_', $tpl);
            $manuale[$mtpl] = $contents;
        }
        return (empty($contents)) ? "<strong>" . $tpl . ".tpl</strong> Not found !<br />" : $contents;
    }

    /**
     * Parse
     */    
    public function parse($carray, $contents)
    {
        global $lang;

        $newkey = $newval = array();
        foreach ($carray as $key => $value) 
		{
            $newkey[$key] = "{" . $key . "}";
            $newval[$key] = $value;
        }
        return str_replace($newkey, $newval, $contents);
    }

    /**
     * Parseprint
     */     
	public function parseprint($carray, $contents)
    {
        global $lang;
        foreach ($carray as $key => $value) {
            $contents = str_replace("{" . $key . "}", $value, $contents);
        }
        print $contents;
    }

    /**
     * Content
     */    
	public function content($files)
    {
        return file_get_contents($files);
    }

    /**
     * Closeprint
     */    
	public function tableprint($variable, $width)
	{
		$table = array();
        $table[1] = "<table class=\"tables\">";
        $table[2] = "<tr>";
        $table[3] = "<td width=\"".$width."%\">";
        $table[4] = "</td>";
        $table[5] = "</tr>";
        $table[6] = "</table>";
        if (is_array($variable)) {
            foreach ($variable as $key) {
                print $table[$key];
            }
        } else {
            print $table[$variable];
        }
    }
}
