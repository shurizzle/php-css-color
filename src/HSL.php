<?php

namespace Shura\CSS\Color;

class HSL
{
    private $hue;
    private $saturation;
    private $lightness;

    public function __construct($h, $s, $l)
    {
        $this->setHue($h);
        $this->setSaturation($s);
        $this->setLightness($l);
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

    public function getH()
    {
        return $this->getHue();
    }

    public function getS()
    {
        return $this->getSaturation();
    }

    public function getL()
    {
        return $this->getLightness();
    }

    public function getHue()
    {
        return $this->hue;
    }

    public function getSaturation()
    {
        return $this->saturation;
    }

    public function getLightness()
    {
        return $this->lightness;
    }

    public function setH($value)
    {
        return $this->setHue($value);
    }

    public function setS($value)
    {
        return $this->setSaturation($value);
    }

    public function setL($value)
    {
        return $this->setLightness($value);
    }

    public function setHue(float $value)
    {
        $this->hue = $value; // TODO: validation
    }

    public function setSaturation(float $value)
    {
        $this->saturation = $value; // TODO: validation
    }

    public function setLightness(float $value)
    {
        $this->lightness = $value; // TODO: validation
    }

    public function getRgb()
    {
        return $this->toRGB();
    }

    public function getHex(bool $compress = true)
    {
        return $this->toHex($compress);
    }

    public function toRGB()
    {
        $r = $g = $b = null;

        if ($this->saturation == 0) {
            $r = $g = $b = $this->lightness;
        } else {
            $q = ($this->lightness <= 0.5) ? ($this->lightness * (1 + $this->saturation)) : ($this->lightness + $this->saturation - $this->lightness * $this->saturation);
            $p = 2 * $this->lightness - $q;

            $r = $this->hue2rgb($p, $q, $this->hue + 1/3.0);
            $g = $this->hue2rgb($p, $q, $this->hue);
            $b = $this->hue2rgb($p, $q, $this->hue - 1/3.0);
        }

        return new RGB($r * 255, $g * 255, $b * 255);
    }

    protected function hue2rgb($p, $q, $t)
    {
        if ($t < 0) {
            ++$t;
        }
        if ($t > 1) {
            --$t;
        }

        if (($t * 6) < 1) {
            return $p + ($q - $p) * $t * 6;
        }
        if (($t * 2) < 1) {
            return $q;
        }
        if (($t * 3) < 2) {
            return $p + ($q - $p) * (2/3.0 - $t) * 6;
        }

        return $p;
    }

    public function toHex(bool $compress = true)
    {
        return $this->toRGB()->toHex($compress);
    }
}
