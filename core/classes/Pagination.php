<?php
/**
 * File:        /core/classes/Loader.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Класс Pagination
 *
 * @param name = имя файла (self)
 * @param str = дополнительные параметры
 * @param p = текущий номер страницы
 * @param count = количество элементов на страницу
 * @param exp = разрыв (...), число, по умолчанию 3
 * @return Постраничная разбивка элементов массива
 */
class Pagination
{
    function __construct($name, $str, $p, $count, $exp = false)
    {
        $this->name = $name;
        $this->str = $str;
        $this->page = isset($p) ? (int)$p : 1;
        $this->count = $count;
        $this->exp = ($exp) ? (int)$exp : 3;
    }

    function init()
    {
        $this->init = $this->page * $this->count - $this->count;
        if ($this->page < 1) {
            $this->page  = 1; $this->init = 0;
        }
        return $this->init;
    }

    function iterator($last)
    {
        $rarr = new ArrayIterator($last);
        $larr = new LimitIterator($rarr, $this->init(), $this->count);
        return $larr;
    }

    function output($elem)
    {
        global $lang, $ro;

        $this->elem = $elem;
        $this->cols = ceil($this->elem / $this->count);

        if ($this->page > $this->cols OR $this->page < 1) {
            $this->page  = 1; $this->init = 0;
        }

        if ($this->cols < 2) {
            return null;
        }

        $out = '<div class="pages">';
        $out.= '<span class="pagesrow">'.str_replace(array('{p}','{t}'),array('<strong>'.$this->page.'</strong>','<strong>'.$this->cols.'</strong>'),$lang['pagenation']).'</span>';
        if ($this->page > 1) {
            $out.='<a href="'.$ro->seo($this->name.'.php?dn='.$this->str.'&amp;p=1').'">&laquo;</a>';
            $out.='<a href="'.$ro->seo($this->name.'.php?dn='.$this->str.'&amp;p='.($this->page - 1)).'">&lsaquo;</a>';
        } else {
            $out.= ''; //$out.= '<span> &laquo; </span>';
        }

        for ($pr = '', $i = 1; $i <= $this->cols; $i ++)
        {
            if ($i == 1 OR $i == $this->cols OR abs($i - $this->page) < $this->exp) {
                $pr = ($i == $this->page) ? '<span class="pagesempty">'.$i.'</span>' : '<a href="'.$ro->seo($this->name.'.php?dn='.$this->str.'&amp;p='.$i).'">'.$i.'</a>';
            } else {
                $pr = ($pr == '<span> &#183;&#183;&#183; </span>' OR $pr == '') ? '' : '<span> &#183;&#183;&#183; </span>';
            }
            $out.= $pr;
        }

        if ($this->page < $this->cols) {
            $out.='<a href="'.$ro->seo($this->name.'.php?dn='.$this->str.'&amp;p='.($this->page + 1)).'">&rsaquo;</a>';
            $out.='<a href="'.$ro->seo($this->name.'.php?dn='.$this->str.'&amp;p='.$this->cols).'">&raquo;</a>';
        } else {
            $out.= ''; //$out.= '<span> &raquo; </span>';
        }
        $out.='</div>';
        return $out;
    }
}
