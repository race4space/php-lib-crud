<?php
namespace phpcrud;
class ThemeData{
  function __construct() {
    $this->str_border_color='border-primary';
    $this->str_border_size='';
    $this->str_border_rounded='rounded';
    $this->str_theme_color="dark";
    $this->str_text_color="text-white";
    $this->str_bg_color="bg-".$this->str_theme_color;
    //$this->str_border="border";
    $this->str_border="";
    $this->str_shadow="shadow";
    $this->str_class=$this->str_bg_color.' '.$this->str_text_color.' '.$this->str_border_rounded.' '.$this->str_border.' '.$this->str_border_color.' '.$this->str_shadow;
  }
}
?>
