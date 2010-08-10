<?php

/* #INFO############################################
Author: Igor Feghali
(c) 2003-2005, ifeghali@interveritas.net
----------------------------------------------------
Based on PHP-Chart Version 0.0.3
(c) 1999-2002, chris.huebsch@informatik.tu-chemnitz.de
----------------------------------------------------
################################################# */

/* #FUNCTIONS#######################################
DrawBullet
MakeLinePointChart
MakeBarChart
################################################# */

/* #2DO#############################################
FIX: LineShowTotal
################################################# */

class chart
{

	var $Title;
	var $SubTitle;
	var $Width					= 320;
	var $Height				= 240;
	var $ShowBullets;

	var $LineShowCaption;
	var $LineShowTotal;		// REQUIRES LineShowCaption TO BE TRUE
	var $LineCaption;
	var $LineCount;

	var $xCount;
	var $xCaption;
	var $xShowValue;
	var $xValue;
	var $xShowGrid;
	var $xTriggerGrid			= 1; // TO BE IMPLEMENTED YET

	var $yCount;
	var $yCaption;
	var $yShowValue;
	var $yShowGrid;
	var $yTriggerGrid			= 1;

	var $DataDecimalPlaces;
	var $DataMax;
	var $DataMin;
	var $DataShowValue;
	var $DataValue;

	function MakeLinePointChart()
	{
		// #DEFINITIONS#####################################
		$chartx = 5;
		$charty = 5;
		$chartw = ($this->Width)-10;
		$charth = ($this->Height)-10;

		$im = imagecreate($this->Width, $this->Height);

		$black = ImageColorAllocate($im, 0, 0, 0);
		$white = ImageColorAllocate($im, 255, 255, 255);

		imagesetstyle($im, array($black, $black, $black, $white, $white, $white));

		$colors[0] = ImageColorAllocate($im, 100, 149, 237);
		$colors[1] = ImageColorAllocate($im, 240, 128, 128);
		$colors[2] = ImageColorAllocate($im, 50, 205, 50);
		$colors[3] = ImageColorAllocate($im, 255, 215, 0);
		$colors[4] = ImageColorAllocate($im, 131, 111, 255);
		$colors[5] = ImageColorAllocate($im, 144, 238, 144);
		$colors[6] = ImageColorAllocate($im, 70, 130, 180);
		$colors[7] = ImageColorAllocate($im, 244, 164, 96);
		$colors[8] = ImageColorAllocate($im, 139, 121, 94);
		$colors[9] = ImageColorAllocate($im, 190, 190, 190);

		$font1 = 5;
		$ifh1 = ImageFontHeight($font1);
		$ifw1 = ImageFontWidth($font1);
		$font2 = 4;
		$ifh2 = ImageFontHeight($font2);
		$ifw2 = ImageFontWidth($font2);
		$font3 = 3;
		$ifh3 = ImageFontHeight($font3);
		$ifw3 = ImageFontWidth($font3);
		// #################################################

		ImageFill($im, 0, 0, $white);

		// #DRAWING CAPTIONS################################
		if ($this->Title)
		{
			$len = strlen($this->Title) * $ifw1;
			ImageString($im, $font1, ($this->Width - $len) / 2, $charty, $this->Title, $black);
			ImageLine($im, ($this->Width - $len) / 2, $charty+$ifh1, ($this->Width + $len) / 2, $charty+$ifh1, $black);

			$charty += $ifh1 + 5;
			$charth -= $ifh1 + 5;
		}

		if ($this->SubTitle)
		{
			// GET A LITTLE NEAR TO TITLE
			$charty -= 3;
			$charth += 3;

			$len = strlen($this->SubTitle) * $ifw3;
			ImageString($im, $font3, ($this->Width - $len) / 2, $charty, $this->SubTitle, $black);

			$charty += $ifh3 + 5;
			$charth -= $ifh3 + 5;
		}

		if ($this->xCaption)
		{
			$len = strlen($this->xCaption) * $ifw3;
			ImageString($im, $font3, ($this->Width - $len) / 2, $this->Height - $ifh3 - 5, $this->xCaption, $black);

			$charth -= $ifh3 + 5;
		}

		if ($this->yCaption)
		{
			$len = strlen($this->yCaption) * $ifw3;
			ImageStringUp($im, $font3, $chartx, ($this->Height + $len) / 2, $this->yCaption, $black);

			$chartx += $ifh3 + 15;
			$chartw -= $ifh3 + 15;
		}

		if ($this->LineShowCaption)
		{
			$xDraw = $chartx;

			foreach ($this->LineCaption as $k => $value)
			{
				if (($this->LineShowTotal) && (is_array($this->DataValue[$k])))
					$value .= ":" . number_format(array_sum($this->DataValue[$k]),0,",",".");

				$value .= " | ";

				ImageString($im, $font2, $xDraw, $this->Height - $ifh2, $value, $colors[$k]);

				$xDraw += strlen($value) * $ifw2;
				$charth -= $ifh2 + 5;
			}
		}
		// #################################################

		/* X,Y AXIS MUST BE RESIZED TO FIT THE LONGEST CAPTION
		BEFORE WE START DRAWING ON THEM */

		// #RESERVING SPACE FOR X VALUES####################
		if (($this->xCount) && ($this->xShowValue))
		{
			$xValueMaxLen = 0;
			if (is_array($this->xValue[0]))
				foreach ($this->xValue[0] as $value)
					$xValueMaxLen = max($xValueMaxLen, strlen($value));

			if (is_array($this->xValue[1]))
				foreach ($this->xValue[1] as $value)
					$xValueMaxLen = max($xValueMaxLen, strlen($value));
					
			$charth -= $xValueMaxLen * $ifw3 + 5;
		}
		// #################################################

		// #RESERVING SPACE FOR Y VALUES####################
		if ($this->yCount)
		{
			$yValueMaxLen = 0;
			$yScale = Array();

			$valueInc = (($this->DataMax)-($this->DataMin)) / $this->yCount;
			$value = $this->DataMin;

			for ($i = 0; $i <= $this->yCount; $i++)
			{
				if (isset($this->DataDecimalPlaces))
					$str = number_format($value, $this->DataDecimalPlaces, ',', '');
				else
					$str = $value;

				$yValueMaxLen = max($yValueMaxLen, strlen($str));
		
				$yAxis[] = $str;
				
				$value += $valueInc;
			}

			// THE SPACE SHOULD BE RESERVED ONLY IF WE ARE GOING TO PRINT THE VALUES			
			if ($this->yShowValue)
			{
				$chartx += $yValueMaxLen * $ifw3 + 5;
				$chartw -= $yValueMaxLen * $ifw3 + 5;
			}
		}
		// #################################################

		// #DRAWING VALUES AT X AXIS########################
		if ($this->xCount)
		{
			$xdelta = $chartw / $this->xCount;

			for ($i = 0; $i < $this->xCount; $i++)
			{
				$xoff = $chartx + ($xdelta * $i) - ($ifh3 / 2);

				if ($this->xShowValue)
				{
					if ($this->xValue[0][$i])
						ImageStringUp($im, $font3, $xoff, $charty + $charth + 5 + (strlen($this->xValue[0][$i]))*$ifw3, $this->xValue[0][$i], $colors[0]);
					if ($this->xValue[1][$i])
						ImageStringUp($im, $font3, $xoff + $ifh3, $charty + $charth + 5 + (strlen($this->xValue[1][$i]))*$ifw3, $this->xValue[1][$i], $colors[1]);
				}

				if (($this->yShowGrid) && !($i % $this->yTriggerGrid))
				{
					$xoff += $ifh3 / 2;
					ImageLine($im, $xoff, $charty, $xoff, $charty + $charth, IMG_COLOR_STYLED);
				}
			}
		}
		// #################################################

		// #DRAWING VALUES AT Y AXIS########################
		if ($this->yCount)
		{
			$yInc = $charth / $this->yCount;
			$yDraw = $charty;
			
			foreach ($yAxis as $value)
			{
				if ($this->yShowValue)
					ImageString($im, $font3, $chartx - 5 - strlen($value) * $ifw3, $yDraw + $charth - $ifh3 / 2, $value, $black);

				// TO BE IMPLEMENTED YET
				//if (($this->xShowGrid) && !($i % $this->xTriggerGrid))
				if ($this->xShowGrid)
					ImageLine($im, $chartx, $yDraw + $charth, $chartx + $chartw, $yDraw + $charth, IMG_COLOR_STYLED);

				$yDraw -= $yInc;
			}
		}
		// #################################################

		// #DRAWING AXIS####################################
		ImageLine($im, $chartx-2, $charty + $charth, $chartx + $chartw, $charty + $charth, $black);
		ImageLine($im, $chartx, $charty, $chartx, $charty + $charth + 2, $black);
		// #################################################

		// #DRAWING DATA####################################
		$ycaption = "";
		$len = 0;
		if (($this->xCount) && ($this->DataMax))
		{
			for ($j = 0;$j < $this->LineCount; $j++)
			{
				$xold = $yold = -1;
				for ($i = 0; $i < $this->xCount; $i++)
				{
					$caption = $this->DataValue[$j][$i];
					$total += $caption;

					$xoff = $chartx + $i * $xdelta;
					$top = ($charty + $charth) - ( ($this->DataValue[$j][$i] - $this->DataMin) / ($this->DataMax - $this->DataMin)) * $charth;

					if ($xold != -1)
						ImageLine($im, $xold, $yold, $xoff, $top, $colors[$j]);

					if ($this->ShowBullets)
						$this->DrawBullet($im, $xoff, $top, $j, $colors[$j]);

					if ($this->DataShowValue)
					{
						if ($j % 2)
						{
							$ycaption .= "ImageFilledRectangle(" . '$im' . ",$xoff+5,$top-$ifh3,$xoff+5+strlen(\"$caption\")*$ifw3,$top,$white);";
							$ycaption .= "ImageString(" . '$im' . ", $font3, $xoff+5,$top-$ifh3,\"$caption\"," . '$colors[' . $j . ']' . ");";
						}
						else
						{
							$ycaption .= "ImageFilledRectangle(" . '$im' . ",$xoff-5-strlen(\"$caption\")*$ifw3,$top-$ifh3,$xoff-5,$top,$white);";
							$ycaption .= "ImageString(" . '$im' . ", $font3, $xoff-5-strlen(\"$caption\")*$ifw3,$top-$ifh3,\"$caption\"," . '$colors[' . $j . ']' . ");";
						}
					}
					$xold = $xoff;
					$yold = $top;
				}
			}
		}
		if ($ycaption) eval($ycaption);
		// #################################################

		ImagePNG($im);
		ImageDestroy($im);
	}

	function MakeBarChart()
	{
		// #DEFINITIONS#####################################
		$chartx = 5;
		$charty = 5;
		$chartw = ($this->Width)-10;
		$charth = ($this->Height)-10;

		$im = imagecreate($this->Width, $this->Height);

		$black = ImageColorAllocate($im, 0, 0, 0);
		$white = ImageColorAllocate($im, 255, 255, 255);

		imagesetstyle($im, array($black, $black, $black, $white, $white, $white));

		$colors[0] = ImageColorAllocate($im, 100, 149, 237);
		$colors[1] = ImageColorAllocate($im, 240, 128, 128);
		$colors[2] = ImageColorAllocate($im, 50, 205, 50);
		$colors[3] = ImageColorAllocate($im, 255, 215, 0);
		$colors[4] = ImageColorAllocate($im, 131, 111, 255);
		$colors[5] = ImageColorAllocate($im, 144, 238, 144);
		$colors[6] = ImageColorAllocate($im, 70, 130, 180);
		$colors[7] = ImageColorAllocate($im, 244, 164, 96);
		$colors[8] = ImageColorAllocate($im, 139, 121, 94);
		$colors[9] = ImageColorAllocate($im, 190, 190, 190);

		$font1 = 5;
		$ifh1 = ImageFontHeight($font1);
		$ifw1 = ImageFontWidth($font1);
		$font2 = 4;
		$ifh2 = ImageFontHeight($font2);
		$ifw2 = ImageFontWidth($font2);
		$font3 = 3;
		$ifh3 = ImageFontHeight($font3);
		$ifw3 = ImageFontWidth($font3);
		// #################################################

		ImageFill($im, 0, 0, $white);

		// #DRAWING CAPTIONS################################
		if ($this->Title)
		{
			$len = strlen($this->Title) * $ifw1;
			ImageString($im, $font1, ($this->Width - $len) / 2, $charty, $this->Title, $black);
			ImageLine($im, ($this->Width - $len) / 2, $charty+$ifh1, ($this->Width + $len) / 2, $charty+$ifh1, $black);

			$charty += $ifh1 + 5;
			$charth -= $ifh1 + 5;
		}

		if ($this->SubTitle)
		{
			// GET A LITTLE NEAR TO TITLE
			$charty -= 3;
			$charth += 3;

			$len = strlen($this->SubTitle) * $ifw3;
			ImageString($im, $font3, ($this->Width - $len) / 2, $charty, $this->SubTitle, $black);

			$charty += $ifh3 + 5;
			$charth -= $ifh3 + 5;
		}

		if ($this->xCaption)
		{
			$len = strlen($this->xCaption) * $ifw3;
			ImageString($im, $font3, ($this->Width - $len) / 2, $this->Height - $ifh3 - 5, $this->xCaption, $black);

			$charth -= $ifh3 + 5;
		}

		if ($this->yCaption)
		{
			$len = strlen($this->yCaption) * $ifw3;
			ImageStringUp($im, $font3, $chartx, ($this->Height + $len) / 2, $this->yCaption, $black);

			$chartx += $ifh3 + 15;
			$chartw -= $ifh3 + 15;
		}

		// #################################################

		/* X,Y AXIS MUST BE RESIZED TO FIT THE LONGEST CAPTION
		BEFORE WE START DRAWING ON THEM */

		// #RESERVING SPACE FOR X VALUES####################
		if (($this->xCount) && ($this->xShowValue))
		{
			$xValueMaxLen = 0;
			if (is_array($this->xValue))
				foreach ($this->xValue as $value)
					$xValueMaxLen = max($xValueMaxLen, strlen($value));

			$charth -= $xValueMaxLen * $ifw3 + 5;
		}
		// #################################################

		// #RESERVING SPACE FOR Y VALUES####################
		if ($this->yCount)
		{
			$yValueMaxLen = 0;
			$yScale = Array();

			$valueInc = (($this->DataMax)-($this->DataMin)) / $this->yCount;
			$value = $this->DataMin;

			for ($i = 0; $i <= $this->yCount; $i++)
			{
				if (isset($this->DataDecimalPlaces))
					$str = number_format($value, $this->DataDecimalPlaces, ',', '');
				else
					$str = $value;

				$yValueMaxLen = max($yValueMaxLen, strlen($str));
		
				$yAxis[] = $str;
				
				$value += $valueInc;
			}

			// THE SPACE SHOULD BE RESERVED ONLY IF WE ARE GOING TO PRINT THE VALUES			
			if ($this->yShowValue)
			{
				$chartx += $yValueMaxLen * $ifw3 + 5;
				$chartw -= $yValueMaxLen * $ifw3 + 5;
			}
		}
		// #################################################

		// #DRAWING VALUES AT X AXIS########################
		if ($this->xCount)
		{
			$xdelta = $chartw / ( (3 * $this->xCount) + 1 );
			if ($xdelta > 15) $xdelta = 15; // WE DONT WANT BAR WIDTH BIGGER THAN 30px

			for ($i = 0; $i < $this->xCount; $i++)
			{
				if ($this->xShowValue)
				{
					$xoff = $chartx + ($xdelta * (3*$i + 2)) - ($ifh3 / 2);

					if ($this->xValue[$i])
						ImageStringUp($im, $font3, $xoff, $charty + $charth + 5 + (strlen($this->xValue[$i]))*$ifw3, $this->xValue[$i], $black);
				}

				if (($this->yShowGrid) && !($i % $this->yTriggerGrid))
				{
					$xoff += $ifh3 / 2;
					ImageLine($im, $xoff, $charty, $xoff, $charty + $charth, IMG_COLOR_STYLED);
				}
			}
		}
		// #################################################

		// #DRAWING VALUES AT Y AXIS########################
		if ($this->yCount)
		{
			$yInc = $charth / $this->yCount;
			$yDraw = $charty;
			
			foreach ($yAxis as $value)
			{
				if ($this->yShowValue)
					ImageString($im, $font3, $chartx - 5 - strlen($value) * $ifw3, $yDraw + $charth - $ifh3 / 2, $value, $black);

				// TO BE IMPLEMENTED YET
				//if (($this->xShowGrid) && !($i % $this->xTriggerGrid))
				if ($this->xShowGrid)
					ImageLine($im, $chartx, $yDraw + $charth, $chartx + $chartw, $yDraw + $charth, IMG_COLOR_STYLED);

				$yDraw -= $yInc;
			}
		}
		// #################################################

		// #DRAWING AXIS####################################
		ImageLine($im, $chartx-2, $charty + $charth, $chartx + $chartw, $charty + $charth, $black);
		ImageLine($im, $chartx, $charty, $chartx, $charty + $charth + 2, $black);
		// #################################################

		// #DRAWING DATA####################################
		if ($this->xCount)
		{
			$j = 0;
			for ($i = 0; $i < $this->xCount; $i++)
			{
				$xoff = $chartx + ($i * 3 + 1) * $xdelta;
				$barh = ($this->DataValue[$i] / $this->DataMax) * $charth;
				$top = ($charty + $charth) - $barh;

				ImageFilledRectangle($im, ($xoff-1), ($top-1), ($xoff + 2 * $xdelta), $charty + $charth, $black); // SHADOW
				ImageFilledRectangle($im, $xoff, $top, ($xoff + 2 * $xdelta)-5, $charty + $charth - 2, $colors[$j]); // COLOUR BAR

				$len = (strlen($this->DataValue[$i]) * $ifw3);

				if (($this->DataShowValue) && ($len < $barh))
					ImageStringUp($im, $font3, $xoff, $top + $len, $this->DataValue[$i], $white);

				$j = ($j+1) % 9;
			}
		}
		// #################################################

		ImagePNG($im);
		ImageDestroy($im);
	}

	function DrawBullet($image, $x, $y, $type, $color)
	{
		switch ($type)
		{
			case 0:
			case 5:
				for ($i = 0; $i < 8; $i++)
				ImageArc($image, $x, $y, $i, $i, 0, 359, $color);
				break;
			case 1:
			case 6:
				ImageFilledRectangle($image, $x-3, $y-3, $x + 3, $y + 3, $color);
				break;
			case 2:
			case 7:
				ImageFilledRectangle($image, $x-1, $y-4, $x + 1, $y + 4, $color);
				ImageFilledRectangle($image, $x-4, $y-1, $x + 4, $y + 1, $color);
				break;
			case 3:
			case 8:
				$points[0] = $x;
				$points[1] = $y-4;
				$points[2] = $x + 4;
				$points[3] = $y;
				$points[4] = $x;
				$points[5] = $y + 4;
				$points[6] = $x-4;
				$points[7] = $y;
				ImageFilledPolygon($image, $points, 4, $color);
				break;
			case 4:
			case 9:
				$points[0] = $x;
				$points[1] = $y-4;
				$points[2] = $x + 4;
				$points[3] = $y + 4;
				$points[4] = $x-4;
				$points[5] = $y + 4;
				ImageFilledPolygon($image, $points, 3, $color);
				break;
			default: ;
		}
		return;
	}

}

?>
