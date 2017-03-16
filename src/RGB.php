<?php

namespace Shura\CSS\Color;

class RGB
{
    private $red;
    private $green;
    private $blue;

    public function __construct($r, $g, $b)
    {
        $this->setRed($r);
        $this->setGreen($g);
        $this->setBlue($b);
    }

    public function __get($name)
    {
        $method = 'get'.Str::studly($name);
        return call_user_func([$this, $method]);
    }

    public function __set($name, $value)
    {
        $method = 'set'.Str::studly($name);
        return call_user_func([$this, $method], $value);
    }

    public function getR()
    {
        return $this->getRed();
    }

    public function getG()
    {
        return $this->getGreen();
    }

    public function getB()
    {
        return $this->getBlue();
    }

    public function getRed()
    {
        return $this->red;
    }

    public function getGreen()
    {
        return $this->green;
    }

    public function getBlue()
    {
        return $this->blue;
    }

    public function setR(int $value)
    {
        return $this->setRed($value);
    }

    public function setG(int $value)
    {
        return $this->setGreen($value);
    }

    public function setB(int $value)
    {
        return $this->setBlue($value);
    }

    public function setRed(int $value)
    {
        $this->red = $value; // TODO: validate
    }

    public function setGreen(int $value)
    {
        $this->green = $value; // TODO: validate
    }

    public function setBlue(int $value)
    {
        $this->blue = $value; // TODO: validate
    }

    public function getHsl(bool $compress = true)
    {
        return $this->toHSL($compress);
    }

    public function toHSL()
    {
        $rd = (float) ($this->red / 255.0);
        $gd = (float) ($this->green / 255.0);
        $bd = (float) ($this->blue / 255.0);
        $max = max($rd, $gd, $bd);
        $min = min($rd, $gd, $bd);
        $h = $s = null;
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? ($d / (2 - $max - $min)) : ($d / ($max + $min));
            if ($max == $rd) {
                $h = ($gd - $bd) / $d + ($gd < $bd ? 6 : 0);
            } elseif ($max == $gd) {
                $h = ($bd - $rd) / $d + 2;
            } elseif ($max == $bd) {
                $h = ($rd - $gd) / $d + 4;
            }
            $h /= 6;
        }

        return new HSL($h, $s, $l);
    }

    public function toHex(bool $compress = true)
    {
        $color = sprintf("%02x%02x%02x%02x", $this->red, $this->green, $this->blue);

        if ($compress) {
            $color = preg_replace('/^([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3$/i', '$1$2$3', $color);
        }

        return '#'.mb_strtoupper($color, 'UTF-8');
    }
}
