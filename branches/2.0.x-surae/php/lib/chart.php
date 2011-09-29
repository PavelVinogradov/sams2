<?php

# Copyright (C) 1999 Lars Magne Ingebrigtsen
#
# Chart is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# Chart is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Chart; see the file COPYING.  If not, write to the
# Free Software Foundation, Inc., 59 Temple Place - Suite 330,
# Boston, MA 02111-1307, USA.

require('lib/rgb.php');

# If debugging is switched on, caching is switched off.
$chart_debug = false;

$chart_cache_directory = "./tmp";

class chart {
  var $background_color = "white";
  var $x_size, $y_size;
  var $plots = array();
  var $image;
  var $left_margin = 30, $right_margin = 10, $top_margin = 20, $bottom_margin = 23;
  var $margin_color = "white";
  var $border_color = "black", $border_width = 1;
  var $title_text = array(), $title_where = array(), $title_color = array();
  var $axes = "xy", $axes_color = "black";
  var $grid_color = array(230, 230, 230), $grid_under_plot = 1;
  var $tick_distance = 25;
  var $x_ticks = false, $x_ticks_format;
  var $scale = "linear";
  var $cache = false;
  var $x_label = false, $y_label = false;
  var $font = 2, $font_type = "internal", $font_name = 2;
  var $y_min = array(false), $y_max = array(false),
    $x_min = array(false), $x_max = array(false);
  var $frame = false;
  var $expired = false;
  var $marked_grid_point = false, $marked_grid_color = false;

  function mark_grid ($point = 0, $color = "red") {
    $this->marked_grid_point = $point;
    $this->marked_grid_color = $color;
  }

  function set_grid_color ($color = false, $grid_under = true) {
      if ($color)
    $this->grid_color = $color;
      $this->grid_under_plot = $grid_under;
  }

  function set_expired ($expired) {
    $this->expired = $expired;
  }

  function set_margins ($left = 30, $right = 10, 
            $top = 20, $bottom = 23) {
    $this->left_margin = $left;
    $this->right_margin = $right;
    $this->top_margin = $top;
    $this->bottom_margin = $bottom;
  }

  function set_tick_distance ($distance) {
    $this->tick_distance = $distance;
  }

  function set_labels ($x = false, $y = false) {
    $this->x_label = $x;
    $this->y_label = $y;
  }

  function chart ($x, $y, $cache = false) {
    # If this image has already been cached, then we just spew
    # it out and exit.
    if ($cache)
      $this->get_cache($cache);
    # If not, we initialize this object and allow execution to continue.
    $this->x_size = $x;
    $this->y_size = $y;
  }

  function get_cache ($file) {
    global $chart_debug, $chart_cache_directory;
    $file = $chart_cache_directory . "/" . $file;
    # There probably is a security problem hereabouts.  Just
    # transforming all ".."'s into "__" and "//"'s into "/_" will 
    # probably help, though.
    while (ereg("[.][.]", $file)) 
      $file = ereg_replace("[.][.]", "__", $file);
    while (ereg("//", $file)) 
      $file = ereg_replace("//", "/_", $file);
    $this->cache = $file;
    if (file_exists($file) && ! $chart_debug) {
      if ($file = fopen($file, "r")) {
    $this->headers();
    fpassthru($file);
    exit;
      } 
    }
    return false;
  }
  
  function put_cache ($image) {
    $file = $this->cache;
    if (file_exists($file))
      unlink($file);
    $this->make_directory(dirname($file));
    imagegif($image, $file);
    imagedestroy($image);
    if ($file = fopen($file, "r")) {
      $this->headers();
      fpassthru($file);
      exit;
    } 
    return true;
  }
  
  function make_directory ($file) {
    while (! (file_exists($file))) {
      $dirs[] = $file;
      $file = dirname($file);
    }
    for ($i = sizeof($dirs)-1; $i>=0; $i--) 
      mkdir($dirs[$i], 0777);
  }

  function set_border ($color = "black", $width = 1) {
    $this->border_color = $color;
    $this->border_width = $width;
  }

  function set_background_color ($color, $margin_color = false) {
    $this->background_color = $color;
    if ($margin_color)
      $this->margin_color = $margin_color;
  }

  function set_x_ticks ($ticks, $format = "date") {
    $this->x_ticks = $ticks;
    $this->x_ticks_format = $format;
  }

  function set_frame ($frame = true) {
    $this->frame = $frame;
  }

  function set_font ($font, $type = 0) {
      $this->font_name = $font;
      $this->font_type = $type;
  }

  function set_title ($title, $color = "black", $where = "center") {
    $this->title_text[] = $title;
    $this->title_where[] = $where;
    $this->title_color[] = $color;
  }

  function set_axes ($which = "xy", $color = "black") {
    $this->axes = $which;
    $this->axes_color = $where;
  }

  function plot ($c1, $c2 = false, $color = false, $style = false,
         $to_color = false, $gradient_param = false) 
  {
    $plot = new plot($c1, $c2);
    if ($color)
      $plot->set_color($color);
    if ($to_color)
      $plot->set_gradient_color($to_color, $gradient_param);
    if ($style)
      $plot->set_style($style);
    $this->plots[] = &$plot;
    return $plot;
  }

  function splot ($plot) {
    $this->plots[] = &$plot;
  }

  function stroke ($callback = false) {
    $xs = $this->x_size;
    $ys = $this->y_size;

    # Load the font for this chart.
    if ($this->font_type == "type1") {
    $this->font = imagepsloadfont($this->font_name);
    } elseif ($this->font_type == "ttf") {
    $this->font = imagettfloadfont($this->font_name);
    } else {
    $this->font = $this->font_name;
    }

    if ($xs == 0 || $ys == 0) {
      php3_error(E_ERROR, "Invalid X or Y sizes: (%s, %s)", 
         $xs, $ys);
    }
    $im = imagecreate($xs, $ys);
    $this->image = $im;
    $bgcolor = $this->allocate_color($this->background_color);
    imagefilledrectangle($im, 0, 0, $xs, $ys, $bgcolor);

    list ($xmin, $xmax) = $this->get_extrema(2);
    list ($ymin, $ymax) = $this->get_extrema(1);
    $grace = ($ymax-$ymin)*0.01;
    $ymin -= $grace;
    $ymax += $grace;

    if (! is_array($this->y_min))
      $ymin = $this->y_min;
    if (! is_array($this->y_max))
      $ymax = $this->y_max;
    if (! is_array($this->x_min))
      $xmin = $this->x_min;
    if (! is_array($this->x_max))
      $xmax = $this->x_max;

    if ($ymax == $ymin) {
      $ymax *= 1.01;
      $ymin *= 0.99;
    }
    if ($xmax == $xmin) 
      $xmax++;
    if ($ymax == $ymin) 
      $ymax++;

    $xoff = $this->left_margin;
    $yoff = $this->top_margin;
    $width = $xs - $this->left_margin - $this->right_margin;
    $height = $ys - $this->top_margin - $this->bottom_margin;

    $axes_color = $this->allocate_color($this->axes_color);

    if ($this->grid_under_plot) {
        # Draw the grid and the axes.
    $this->draw_y_axis($im, $ymin, $ymax, $xs, $ys, $height, $yoff, false,
               $axes_color);
    $this->draw_x_axis($im, $xmin, $xmax, $xs, $ys, $width, $xoff, false,
               $axes_color);
    }

    # Go through all the plots and stroke them.
    if ($callback != false) {
      $callback($im, $xmin, $xmax, $ymin, $ymax,
        $xoff, $yoff, $width, $height);
    } else {
      for ($i = 0; $i < sizeof($this->plots); $i++) {
    $plot = $this->plots[$i];
    $plot->stroke($im, $xmin, $xmax, $ymin, $ymax,
              $xoff, $yoff, $width, $height);
      }
    }

    if (! $this->grid_under_plot) {
        # Draw the grid and the axes.
    $this->draw_y_axis($im, $ymin, $ymax, $xs, $ys, $height, $yoff, false,
               $axes_color);
    $this->draw_x_axis($im, $xmin, $xmax, $xs, $ys, $width, $xoff, false,
               $axes_color);
    }

    # The plotting may have plotted outside of the allocated
    # "framed" area (if autoscaling is not in use), so we
    # blank out the surrounding area.
    $margin = $this->allocate_color($this->margin_color);
    imagefilledrectangle($im, 0, 0, $xs, $this->top_margin-1, $margin);
    imagefilledrectangle($im, $xs-$this->right_margin+1, $this->top_margin-1,
             $xs, $ys, $margin);
    imagefilledrectangle($im, 0, $ys-$this->bottom_margin+1, $xs, $ys, 
             $margin);
    imagefilledrectangle($im, 0, 0, $this->left_margin-1, $ys, $margin);
    
    if (! $this->frame) {
      imageline($im, $this->left_margin, $this->top_margin, 
        $this->left_margin, $ys-$this->bottom_margin+3, $axes_color);
      imageline($im, $this->left_margin-3, $ys-$this->bottom_margin,
        $xs-$this->right_margin, $ys-$this->bottom_margin,
        $axes_color);
    } else {
      imagerectangle($im, $this->left_margin, $this->top_margin, 
             $xs-$this->right_margin, $ys-$this->bottom_margin, 
             $this->allocate_color($this->border_color));
    }

    # Put the text onto the axes.
    $this->draw_y_axis($im, $ymin, $ymax, $xs, $ys, $height, $yoff, true,
               $axes_color);
    $this->draw_x_axis($im, $xmin, $xmax, $xs, $ys, $width, $xoff, true,
               $axes_color);

    $title_color = $this->allocate_color("black");

    # Draw the labels, if any.
    if ($this->y_label) 
      imagestringup($im, $this->font, 5,
            $ys/2+$this->string_pixels($this->y_label)/2,
            $this->y_label, $title_color);
    if ($this->x_label) 
      imagestring($im, $this->font,
          $xs/2-$this->string_pixels($this->x_label)/2,
          $ys-20, $this->x_label, $title_color);

    # Draw the boorder.
    if ($this->border_color) 
      imagerectangle($im, 0, 0, $xs-1, $ys-1, 
             $this->allocate_color($this->border_color));

    # Draw the title.
    for ($i=0; $i<sizeof($this->title_text); $i++) {
      if (!strcmp($this->title_where[$i], "center")) {
    $tx = $xs/2 - $this->string_pixels($this->title_text[$i])/2;
      } else {
    $tx = 0;
      }
      if ($this->font_type == "type1") {
      imagepstext ($im, $this->title_text[$i], $this->font, 12, 
               $this->allocate_color($this->title_color[$i]),
               $this->allocate_color("white"),
               $tx, 15);
      } elseif ($this->font_type == "internal") {
      imagestring($im, $this->font, $tx, 5, $this->title_text[$i], 
              $this->allocate_color($this->title_color[$i]));
      }
    }

    if ($this->cache) 
      $this->put_cache($im);
    
    $this->headers();
    imagegif($im);

    imagedestroy($im);
    return true;
  }

  function headers () 
  {
    if ($this->expired) 
      {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
      }
    header("Content-type: image/gif");
  }

  function datadatetotime ($datatime) {
    if (ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $datatime, $regs)) { 
      return mktime (1, 0, 0,
             $regs[2], $regs[3], $regs[1]);
    }
    return 0;
  }

  function shortnorwegiandate ($time) {
    return date("d.m.y", $time);
  }

  function draw_x_axis ($im, $xmin, $xmax, $xs, $ys, $width,
            $xoff, $do_text, $axes_color) {
    $grid_color = $this->allocate_color($this->grid_color);
    if (!(strcmp($this->axes, "x")) || !(strcmp($this->axes, "xy"))) {
      $ticks = $this->get_ticks($xmin, $xmax);
      
      $step = ceil(sizeof($ticks)/
           (($xs-$this->left_margin-$this->right_margin)/
            70));
      for ($i = 0; $i < sizeof($ticks); $i += 1) {
    $x = floor($ticks[$i]);
    $xt = $xoff + ($x - $xmin) / ($xmax - $xmin) * $width;
    if ($do_text) {
      if (($i % $step) == 0) {
        if ($this->x_ticks) {
          if (!strcmp($this->x_ticks_format, "date")) {
        $text = $this->shortnorwegiandate
          ($this->datadatetotime($this->x_ticks[$x]));
          } elseif (!strcmp($this->x_ticks_format, "time")) {
        $text = $this->x_ticks["$x"];
        $text = substr($text, 0, 2) . ":" . substr($text, 2, 2);
          }
        } else {
          $text = $x;
        }
        if ($this->font_type == "type1") {
        imagepstext ($im, $text, $this->font, 10, 
                 $axes_color,
                 $this->allocate_color("white"),
                 $xt-(strlen($text)*6/2)+3,
                 $ys-$this->bottom_margin+15,
                 0, 0, 0, 16);
        } elseif ($this->font_type == "internal") {
        imagestring($im, $this->font, $xt-(strlen($text)*6/2),
                $ys-$this->bottom_margin+5, $text, $axes_color);
        }
        imageline($im, $xt, $ys-$this->bottom_margin, 
              $xt, $ys-$this->bottom_margin+3, $axes_color);
      } else {
        imageline($im, $xt, $ys-$this->bottom_margin, 
              $xt, $ys-$this->bottom_margin+1, $axes_color);
      }
    } else {
      imageline($im, $xt, $this->top_margin, 
            $xt, $ys-$this->bottom_margin-1, $grid_color);
    }
      }

      for ($x = $this->left_margin; $x < $xs-$this->right_margin; 
       $x += $this->tick_distance) {
      }
    }      
  }

  function draw_y_axis ($im, $ymin, $ymax, $xs, $ys,
            $height, $yoff, $do_text, $axes_color) {
    $grid_color = $this->allocate_color($this->grid_color);
    # Compute the Y axis.
    if (!(strcmp($this->axes, "y")) || !(strcmp($this->axes, "xy"))) {
      $ticks = $this->get_ticks($ymin, $ymax);

      $step = ceil(sizeof($ticks)/
           (($ys-$this->top_margin-$this->bottom_margin)/
            $this->tick_distance));
      for ($i = 0; $i < sizeof($ticks); $i += 1) {
    $y = $ticks[$i];
    $yt = $yoff + $height - (($y*1.0 - $ymin) / ($ymax - $ymin) * $height);

    if ($do_text) {
      if (!($i%$step)) {
        if ($y == 0) 
          $yst = 0;
        elseif (abs($y) < 1) 
          $yst = sprintf("%.2f", $y);
        elseif (!($y % 1000000)) 
          $yst = sprintf("%sM",  $y / 1000000);
        elseif (!($y % 1000))
          $yst = sprintf("%sk",  $y / 1000);
        else
          $yst = $y;
        
        if ($this->font_type == "type1") {
        imagepstext ($im, $yst, $this->font, 10, 
                 $axes_color,
                 $this->allocate_color("white"),
                 $this->left_margin-3-strlen($yst)*6, $yt+4,
                 0, 0, 0, 16);
        } elseif ($this->font_type == "internal") {
        imagestring($im, $this->font,
                $this->left_margin-3-strlen($yst)*6, $yt-7, $yst,
                $axes_color);
        }
        imageline($im, $this->left_margin-3, $yt, 
              $this->left_margin, $yt, $axes_color);
      } else {
        imageline($im, $this->left_margin-1, $yt, 
            $this->left_margin, $yt, $axes_color);
      }
    } else {
      imageline ($im, $this->left_margin+1, $yt, 
             $xs-$this->right_margin, $yt, $grid_color);
    }
      }

      if (! $do_text && $this->marked_grid_color) {
    $y = $this->marked_grid_point;
    $yt = $yoff + $height - (($y*1.0 - $ymin) / ($ymax - $ymin) * $height);
    imageline ($im, $this->left_margin+1, $yt, 
           $xs-$this->right_margin, $yt, 
           $this->allocate_color($this->marked_grid_color));
      }
    }
  }

  function string_pixels ($string) {
    return strlen($string)*6;
  }

  function get_ticks ($min, $max) {

    $diff = abs($max-$min);
    # Compute the "reasonable" distance between the tick marks,
    # independent of the size of the chart.
    if ($diff > 5000) 
      $even = pow(10, floor(log10($diff/2)));
    elseif ($diff > 500) 
      $even = 100;
    elseif ($diff > 50)
      $even = 10;
    elseif ($diff > 25) 
      $even = 2;
    elseif ($diff > 5) 
      $even = 1;
    else
      $even = .1;

    if ($min < 0) 
      $start = floor($min*100) + $even*100-(floor($min*100)%($even*100))
    - $even*100;
    else 
      $start = floor($min*100 +
             $even*100-(floor($min*100)%($even*100)));
    for ($elem = $start, $i = 0;
     $elem < $max*100; $elem += (int) floor($even*100), $i++) {
      $ticks[$i] = $elem/100;
      if ($i > 1000)
    return $ticks;
    }

    return $ticks;
  }

  function set_scale ($type = "linear") {
    $this->scale = $type;
  }

  function allocate_color($color) {
    return rgb_allocate($this->image, $color);
  }

  function get_extrema ($dim) {
    for ($i = 0; $i < sizeof($this->plots); $i++) {
      $plot = $this->plots[$i];
      list ($mi, $ma) = $plot->get_extrema($dim);
      if (! isset($max))
    $max = $mi;
      if (! isset($min)) 
    $min = $ma;
      if ($ma > $max)
    $max = $ma;
      if ($mi < $min)
    $min = $mi;
    }
    return array($min, $max);
  }

}

class plot {
  var $coords;
  var $color = "black", $to_color = "black", $gradient_param = 0;
  var $style = "lines";
  var $dimension = 1;

  function plot ($c1, $c2) {
    $this->coords[] = $c1;
    $this->coords[] = $c2;
    if ($c2 == 0) {
      $this->dimension = 1;
    } else {
      $this->dimension = 2;
    }
    return true;
  }

  function set_color ($color) {
    $this->color = $color;
    return true;
  }

  function set_gradient_color ($to_color, $param = 0) {
    $this->to_color = $to_color;
    $this->gradient_param = $param;
  }

  function get_color () {
    return $this->color;
  }

  function set_style ($style) {
    $this->style = $style;
  }

  function set_dimension ($dim) {
    $this->dimension = $dim;
  }

  function get_extrema ($dim) {
    if ($dim > $this->dimension ||
    ($dim == 2 && !strcmp($this->style, "fill")))
      return array(0, sizeof($this->coords[0])-1);
    
    $arr = $this->coords[$dim-1];
    for ($j = 0; $j < sizeof($arr); $j++) {
      if ((! is_string($arr[$j])) || (strcmp($arr[$j], "noplot"))) {
    if (! isset($max))
      $max = $arr[$j];
    if (! isset($min)) 
      $min = $arr[$j];
    if ($arr[$j] > $max)
      $max = $arr[$j];
    if ($arr[$j] < $min)
      $min = $arr[$j];
      }
    }
    return array($min, $max);
  }

  function stroke ($im, $xmin, $xmax, $ymin, $ymax, $xoff, $yoff,
           $width, $height) {
    $color = rgb_allocate($im, $this->color);
    $style = $this->style;
    $ycoords = $this->coords[0];
    $end = sizeof($ycoords);
    if (!strcmp($style, "points"))
      $style = 1;
    elseif (!strcmp($style, "lines"))
      $style = 2;
    elseif (!strcmp($style, "impulse"))
      $style = 3;
    elseif (!strcmp($style, "circle"))
      $style = 4;
    elseif (!strcmp($style, "cross"))
      $style = 5;
    elseif (!strcmp($style, "fill")) {
    $style = 6;
    $this->dimension = 1;
    } elseif (!strcmp($style, "square"))
      $style = 7;
    elseif (!strcmp($style, "gradient")) {
    # Calculate the gradient.
    $style = 8;
    $gradient_style = $this->gradient_param&1;
    $gradient_updown = $this->gradient_param&2;
    $gradient_direction = $this->gradient_param&4;
    if ($gradient_direction == 0) {
        $fcol = rgb_color($this->color);
        $tcol = rgb_color($this->to_color);
    } else {
        $tcol = rgb_color($this->color);
        $fcol = rgb_color($this->to_color);
    }
    # We use at most 220 different colors.
    $numcols = 110;

    $rfactor = ($tcol[0]-$fcol[0]) / $numcols;
    $gfactor = ($tcol[1]-$fcol[1]) / $numcols;
    $bfactor = ($tcol[2]-$fcol[2]) / $numcols;

    $h = $height+2;
    $col_factor = $numcols/$h;
    $prev = -1;
    for ($i = 0; $i < $h; $i++) {
        $num = floor($col_factor*($h-$i));

        $rnum = floor($fcol[0] + $num * $rfactor);
        $gnum = floor($fcol[1] + $num * $gfactor);
        $bnum = floor($fcol[2] + $num * $bfactor);

        if ($num == $prev) {
        $colors[$i] = $col;
        } else {
        $col = rgb_allocate($im, sprintf("#%02x%02x%02x", 
                         $rnum, $gnum, $bnum));
        $colors[$i] = $col;
        }
        $prev = $num;
    }
    }

    for ($i = 0; $i < $end; $i++) {
      $y = $ycoords[$i];
      if ((! is_string($y)) || (strcmp($y, "noplot"))) {
    if ($this->dimension == 1) 
      $x = $i;
    else 
      $x = $this->coords[1][$i];
    
    $xt = $xoff + ($x - $xmin) / ($xmax - $xmin) * $width;
    $yt = $yoff + $height - (($y*1.0 - $ymin) / ($ymax - $ymin) * $height);
    
    if (! isset($pxt))
      $pxt = $xt;
    if (! isset($pyt))
      $pyt = $yt;

    if ($style == 1) 
      imageline($im, $xt, $yt, $xt, $yt, $color);
    elseif ($style == 2)
      imageline($im, $pxt, $pyt, $xt, $yt, $color);
    elseif ($style == 3)
      imageline($im, $xt, $yoff+$height, $xt, $yt, $color);
    elseif ($style == 4)
      imagearc($im, $xt, $yt, 10, 10, 0, 360, $color);
    elseif ($style == 5) {
      imageline($im, $xt-5, $yt-5, $xt+5, $yt+5, $color);
      imageline($im, $xt+5, $yt-5, $xt-5, $yt+5, $color);
    } elseif ($style == 6) {
        if (! isset($poyt))
          $poyt = $oyt;
        $oyt = $yoff + $height - 
          (($this->coords[1][$i]*1.0 - $ymin) / ($ymax - $ymin) * $height);
        for ($j = $pxt; $j <= $xt; $j++) 
          imageline($im, $j, $oyt, $j, $yt, $color);
        $poyt = $oyt;
    } elseif ($style == 7) {
      imageline($im, $pxt, $pyt, $pxt, $yt, $color);
      imageline($im, $pxt, $yt, $xt, $yt, $color);
      } elseif ($style == 8) {
      // color-impulse

      // We plot down from the value to the bottom of the chart.
      // There might be several pixels width of stuff to be plotted,
      // so we first calculate the gradient of the top of the chart
      // between the two points.  So the top of the "color-impulse"
      // chart will resemble the "lines" chart, not the "square"
      // chart.

      if ($xt == $pxt) {
          $b = 0;
      } else {
          $b = ($yt - $pyt) / ($xt - $pxt);
      }
      $a = $yt - $b * $xt;

      for ($x = $pxt; $x <= $xt; $x++) {
          $firsty = (int)round($a + $b * $x);
          if ($gradient_updown == 0) {
          for ($y = (int)round($a + $b * $x); $y < $yoff+$height; $y++) {
              if ($gradient_style == 1) {
              imagesetpixel($im, $x, $y, $colors[$y-$firsty]);
              } else {
              imagesetpixel($im, $x, $y, $colors[$y-$yoff]);
              }
          }
          } else {
          for ($y = (int)round($a + $b * $x); $y > $yoff; $y--) {
              if ($gradient_style == 1) {
              imagesetpixel($im, $x, $y, $colors[$firsty-$y]);
              } else {
              imagesetpixel($im, $x, $y, $colors[$y-$yoff]);
              }
          }
          }
      }
      }

    $pxt = $xt;
    $pyt = $yt;
      }
      
    }
    return($color);
  }

}



class BAR
{

//Размер изображения
VAR $W;
VAR $H;
//Псевдо-глубинаграфика
VAR $DX;
VAR $DY;

//Отступы
VAR $MB=20;//Нижний
VAR $ML=10;//Левый
VAR $M=5;//Верхнийи правый отступы. Они меньше,так как там нет текста

//Ширина одного символа
VAR $LW;
VAR $USERS=Array();  
VAR $SIZE=Array(); 

 
//Массив $DATA["x"] содержит подписи по оси "X"

VAR $mascount;
VAR $DATA=Array();
  
  function BAR($width, $height, $dx, $dy, $size, $hit, $mcount, $users)
    {
       
       $this->W=$width;
       $this->H=$height;
       $this->DX=$dx;
       $this->DY=$dy;
       $this->LW=imagefontwidth(2);   
       
       for($i=0;$i<$mcount;$i++)
         {
           $this->DATA[0][]=$size[$i];
           $this->DATA[1][]=$hit[$i];
           $this->DATA["x"][]=$i+1;
           //$this->DATA["x"][]=$users[$i];
         }
       
    }
  
//Функция вывода псевдо-трехмерного куба###########################

//$im-идентификатор изображения
//$x,$y-координаты верхнего левого угла куба
//$w-ширина куба
//$h-высота куба
//$dx-смещение задней грани куба по оси X
//$dy-смещение задней грани куба по оси Y
//$c1,$c2,c3-цвета видимых граней куба

  function imagebar($im,$x,$y,$w,$h,$dx,$dy,$c1,$c2,$c3)
    {

     if($dx>0)
       {
        imagefilledpolygon($im, Array($x,$y-$h,$x+$w,$y-$h,$x+$w+$dx,$y-$h-$dy,$x+$dx,$y-$dy-$h),4,$c1);
        imagefilledpolygon($im, Array($x+$w,$y-$h,$x+$w,$y,$x+$w+$dx,$y-$dy,$x+$w+$dx,$y-$dy-$h),4,$c3);
       }

      imagefilledrectangle($im,$x,$y-$h,$x+$w,$y,$c2);
    }
  
  function CreateBars()
    {
    
        //Подсчитаем количество элементов (столбиков) на графике
       $count=count($this->DATA[0]);
       if(count($this->DATA[1])>$count)$count=count($this->DATA[1]);
//       if(count($this->DATA[2])>$count)$count=count($this->DATA[2]);

       //Подсчитаем максимальное значение
       $max=0;
       for($i=0;$i<$count;$i++)
         {
           $max=$max<$this->DATA[0][$i]?$this->DATA[0][$i]:$max;
           $max=$max<$this->DATA[1][$i]?$this->DATA[1][$i]:$max;
//           $max=$max<$this->DATA[2][$i]?$this->DATA[2][$i]:$max;
         }

       //Увеличим максимальное значение на 10% (для того,чтобы столбик,
       //соответствующий максимальному значение не упирался в в границу
       //графика
       $max=intval($max+($max/10));

       //Работа с изображением############################################

       //Создадим изображения
       $im=imagecreate($this->W,$this->H);
       
       //Задаем основные цвета

       //Цвет фона(белый)
       $bg[0]=imagecolorallocate($im,255,255,255);

       //Цвет задней грани графика (светло-серый)
       $bg[1]=imagecolorallocate($im,231,231,231);

       //Цвет левойграни графика(серый)
       $bg[2]=imagecolorallocate($im,212,212,212);

       //Цвет сетки(серый,темнее)
       $c=imagecolorallocate($im,184,184,184);

       //Цветтекста(темно-серый)
       $text=imagecolorallocate($im,136,136,136);

       //Цвета для столбиков
       $bar[2][0]=imagecolorallocate($im,255,128,234);
       $bar[2][1]=imagecolorallocate($im,222,95,201);
       $bar[2][2]=imagecolorallocate($im,191,65,170);
       $bar[0][0]=imagecolorallocate($im,222,214,0);
       $bar[0][1]=imagecolorallocate($im,181,187,65);
       $bar[0][2]=imagecolorallocate($im,161,155,0);
       $bar[1][0]=imagecolorallocate($im,128,234,255);
       $bar[1][1]=imagecolorallocate($im,95,201,222);
       $bar[1][2]=imagecolorallocate($im,65,170,191);

       //Количество подписей и горизонтальных линий
       //сетки по осиY.
       $county=10;

       //Подравняем левую границу с учетом ширины подписей по осиY
       $text_width=strlen($max)*$this->LW;
       $this->ML+=$text_width;

       //Вывод фона графика
       imageline($im,$this->ML,$this->M+$this->DY,$this->ML,$this->H-$this->MB,$c);
       imageline($im,$this->ML,$this->M+$this->DY,$this->ML+$this->DX,$this->M,$c);
       imageline($im,$this->ML,$this->H-$this->MB,$this->ML+$this->DX,$this->H-$this->MB-$this->DY,$c);
       imageline($im,$this->ML,$this->H-$this->MB,$this->W-$this->M-$this->DX,$this->H-$this->MB,$c);
       imageline($im,$this->W-$this->M-$this->DX,$this->H-$this->MB,$this->W-$this->M,$this->H-$this->MB-$this->DY,$c);

       imagefilledrectangle($im,$this->ML+$this->DX,$this->M,$this->W-$this->M,$this->H-$this->MB-$this->DY,$bg[1]);
       imagerectangle($im,$this->ML+$this->DX,$this->M,$this->W-$this->M,$this->H-$this->MB-$this->DY,$c);

       imagefill($im,$this->ML+1,$this->H/2,$bg[2]);

       //Вывод неизменяемой сетки (горизонтальные линии на
       //нижней грани и вертикальные линии сетки на левой
       //грани
       for($i=1;$i<3;$i++)
         {
           imageline($im,$this->ML+$i*intval($this->DX/3),$this->M+$this->DY-$i*intval($this->DY/3),$this->ML+$i*intval($this->DX/3),$this->H-$this->MB-$i*intval($this->DY/3),$c);
           imageline($im,$this->ML+$i*intval($this->DX/3),$this->H-$this->MB-$i*intval($this->DY/3),$this->W-$this->M-$this->DX+$i*intval($this->DX/3),$this->H-$this->MB-$i*intval($this->DY/3),$c);
         }

       //Пересчитаем размеры графика с учетом подписей и отступов
       $RW=$this->W-$this->ML-$this->M-$this->DX;
       $RH=$this->H-$this->MB-$this->M-$this->DY;

       //Координаты нулевой точки графика
       $X0=$this->ML+$this->DX;
       $Y0=$this->H-$this->MB-$this->DY;

       //Вывод изменяемой сетки (вертикальные линии сетки на нижней грани графика
       //и вертикальные линии на задней грани графика)
       for($i=0;$i<$count;$i++)
         {
           imageline($im,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count)-$this->DX,$Y0+$this->DY,$c);
           imageline($im,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count),$Y0-$RH,$c);
         }

       //Горизонтальные линии сетки заднейи левой граней.
       $step=$RH/$county;
       for($i=0;$i<=$county;$i++)
         {
           imageline($im,$X0,$Y0-$step*$i,$X0+$RW,$Y0-$step*$i,$c);
           imageline($im,$X0,$Y0-$step*$i,$X0-$this->DX,$Y0-$step*$i+$this->DY,$c);
           imageline($im,$X0-$this->DX,$Y0-$step*$i+$this->DY,$X0-$this->DX-($this->ML-$text_width)/4,$Y0-$step*$i+$this->DY,$text);
         }

       //Вывод кубов для всех трех рядов
       for($i=0;$i<$count;$i++)
         $this->imagebar($im,$X0+$i*($RW/$count)+4-1*intval($this->DX/3),$Y0+1*intval($this->DY/3),intval($RW/$count)-4,$RH/$max*$this->DATA[0][$i],intval($this->DX/3)-5,intval($this->DY/3)-3,$bar[2][0],$bar[2][1],$bar[2][2]);
//       for($i=0;$i<$count;$i++)
//         $this->imagebar($im,$X0+$i*($RW/$count)+4-1*intval($this->DX/3),$Y0+1*intval($this->DY/3),intval($RW/$count)-4,$RH/$max*$this->DATA[0][$i],intval($this->DX/3)-5,intval($this->DY/3)-3,$bar[0][0],$bar[0][1],$bar[0][2]);

       for($i=0;$i<$count;$i++)
         $this->imagebar($im,$X0+$i*($RW/$count)+4-2*intval($this->DX/3),$Y0+2*intval($this->DY/3),intval($RW/$count)-4,$RH/$max*$this->DATA[1][$i],intval($this->DX/3)-5,intval($this->DY/3)-3,$bar[1][0],$bar[1][1],$bar[1][2]);

       //for($i=0;$i<$count;$i++)
       //  $this->imagebar($im,$X0+$i*($RW/$count)+4-3*intval($this->DX/3),$Y0+3*intval($this->DY/3),intval($RW/$count)-4,$RH/$max*$this->DATA[2][$i],intval($this->DX/3)-5,intval($this->DY/3)-3,$bar[2][0],$bar[2][1],$bar[2][2]);

       //Вывод подписей по оси Y
       for($i=1;$i<=$county;$i++)
         {
           $str=intval(($max/$county)*$i);
           imagestring($im,2,$X0-$this->DX-strlen($str)*$this->LW-$this->ML/4-2,$Y0+$this->DY-$step*$i-imagefontheight(2)/2,$str,$text);
         }

       //Вывод подписей по осиX
       $prev=100000;
       $twidth=$this->LW*strlen($this->DATA["x"][0])+6;
       $i=$X0+$RW-$this->DX;

       while($i>$X0-$this->DX)
         {
           if($prev-$twidth>$i)
             {
               $drawx=$i+1-($RW/$count)/2;
               if($drawx>$X0-$this->DX)
	         {
                   $str=$this->DATA["x"][round(($i-$X0+$this->DX)/($RW/$count))-1];
                   imageline($im,$drawx,$Y0+$this->DY,$i+1-($RW/$count)/2,$Y0+$this->DY+5,$text);
                   imagestring($im,2,$drawx+1-(strlen($str)*$this->LW)/2,$Y0+$this->DY+7,$str,$text);
                 }
               $prev=$i;
             }
           if($count>0)
             $i-=$RW/$count;
         }

       header("Content-Type:image/png");

       //Генерация изображения
       ImagePNG($im);

       imagedestroy($im);
   
    
    }

}





class CIRCLE3D
{
 //Размер изображения
 VAR $W;
 VAR $H;

 VAR $VALUES=Array();
 VAR $LEGEND=Array();
 VAR $COLORS=Array();
 VAR $SHADOWS=Array();
 
 VAR $ucount;
 VAR $bgcolor;

  function CIRCLE3D($width, $height, $size, $mcount, $users)
    {
       
       $this->W=$width;
       $this->H=$height;
       $this->ucount=$mcount;
       for($i=0;$i<$mcount;$i++)
         {
           $this->VALUES[$i]=$size[$i];
           $this->LEGEND[$i]=$users[$i];
         }
       
    }
  function ShowCircle()
    {
      header("Content-Type: image/png");
      $im=ImageCreate($this->W,$this->H);

      $this->bgcolor=ImageColorAllocate($im,255,255,255);
      
      for($i=0;$i<$this->ucount;$i++)
        {
          $r=mt_rand(90,240);
          $g=mt_rand(90,240);
          $b=mt_rand(90,240);
          $this->COLORS[$i] = imagecolorallocate($im, $r, $g, $b);
          $this->SHADOWS[$i] = imagecolorallocate($im, $r-40, $g-40, $b-40);
        }
	
      $this->Diagramm($im,$this->VALUES,$this->LEGEND);
      ImagePNG($im);

    }

  // $im - идентификатор изображения
  // $VALUES - массив со значениями
  // $LEGEND - массив с подписями
  function Diagramm($im,$VALUES,$LEGEND) 
    {
	//GLOBAL $COLORS,$SHADOWS;

	$black=ImageColorAllocate($im,0,0,0);

	// Получим размеры изображения
	$W=ImageSX($im);                 
	$H=ImageSY($im);

	// Вывод легенды #####################################

	// Посчитаем количество пунктов, от этого зависит высота легенды
	//$legend_count=count($LEGEND);
	$legend_count=$this->ucount;

	// Посчитаем максимальную длину пункта, от этого зависит ширина легенды
	$max_length=0;
	foreach($LEGEND as $v) if ($max_length<strlen($v)) $max_length=strlen($v);

	// Номер шрифта, котором мы будем выводить легенду
	$FONT=2;
	$font_w=ImageFontWidth($FONT);
	$font_h=ImageFontHeight($FONT);

	// Вывод прямоугольника - границы легенды ----------------------------

	$l_width=($font_w*$max_length)+$font_h+10+5+10;
	$l_height=$font_h*$legend_count+10+10;

	
	// Получим координаты верхнего левого угла прямоугольника - границы легенды
	$l_x1=$W-10-$l_width;
	$l_y1=($H-$l_height)/2;

	// Выводя прямоугольника - границы легенды
	//ImageRectangle($im, $l_x1, $l_y1, $l_x1+$l_width, $l_y1+$l_height, $black);

	// Вывод текст легенды и цветных квадратиков
	$text_x=$l_x1+10+5+$font_h;
	$square_x=$l_x1+10;
	$y=$l_y1+10;

	$i=0;
	foreach($LEGEND as $v) 
	  {
	    $dy=$y+($i*$font_h);
	    ImageString($im, $FONT, $text_x, $dy, $v, $black);
	    ImageFilledRectangle($im,$square_x+1,$dy+1,$square_x+$font_h-1,$dy+$font_h-1, $this->COLORS[$i]);
	    ImageRectangle($im,$square_x+1,$dy+1,$square_x+$font_h-1,$dy+$font_h-1, $black);
	    $i++;
	  }

	// Вывод круговой диаграммы ----------------------------------------

	$total=array_sum($VALUES);
	$anglesum=$angle=Array(0);
	$i=1;

	
	// Расчет углов
	while ($i<count($VALUES)) 
	  {
	    $part=$VALUES[$i-1]/$total;
	    $angle[$i]=floor($part*360);
	    $anglesum[$i]=array_sum($angle);
	    $i++;
	  }
	$anglesum[]=$anglesum[0];

	// Расчет диаметра
//	$diametr=$l_x1-10-10;
	$diametr=$l_x1-10-10;

	// Расчет координат центра эллипса
	$circle_x=($diametr/2)+10;
	$circle_y=$H/2-10;

	// Поправка диаметра, если эллипс не помещается по высоте
	if ($diametr>($H*2)-10-10) $diametr=($H*2)-20-20-40;

	// Вывод тени
	for ($j=20;$j>0;$j--)
		for ($i=0;$i<count($anglesum)-1;$i++)
			ImageFilledArc($im,$circle_x,$circle_y+$j, $diametr,$diametr/2, $anglesum[$i],$anglesum[$i+1], $this->SHADOWS[$i],IMG_ARC_PIE);

	// Вывод круговой диаграммы
	for ($i=0;$i<count($anglesum)-1;$i++)
		ImageFilledArc($im,$circle_x,$circle_y, $diametr,$diametr/2, $anglesum[$i],$anglesum[$i+1], $this->COLORS[$i],IMG_ARC_PIE);
    }

}





?>
