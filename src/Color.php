<?php

namespace Shura\CSS\Color;

class Color
{
    private $hsl;
    private $rgb;
    private $alpha;

    private function __construct()
    {
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

    public function __call($method, $args)
    {
        if (method_exists($this->getRgb(), $method)) {
            return call_user_func_array([$this->getRgb(), $method], $args);
        } elseif (method_exists($this->getHsl(), $method)) {
            return call_user_func_array([$this->getHsl(), $method], $args);
        }

        return call_user_func_array([$this, $method], $args);
    }

    public function getA()
    {
        return $this->getAlpha();
    }

    public function getAlpha()
    {
        return $this->alpha;
    }

    public function setA(float $value)
    {
        return $this->setAlpha($value);
    }

    public function setAlpha(float $value)
    {
        return $this->alpha = $value; // TODO: validation
    }

    public function getRgb()
    {
        if (!isset($this->rgb)) {
            $this->rgb = $this->hsl->getRgb();
        }

        return $this->rgb;
    }

    public function getHsl()
    {
        if (!isset($this->hsl)) {
            $this->hsl = $this->rgb->getHsl();
        }

        return $this->hsl;
    }

    public function toHex(bool $compress = true)
    {
        $color = sprintf("%02x%02x%02x%02x", $this->red, $this->green, $this->blue, round($this->alpha * 255));

        $color = preg_replace('/ff$/i', '', $color);

        if ($compress) {
            $color = preg_replace('/^([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3(?:([0-9a-f])\4)?$/i', '$1$2$3$4', $color);
        }

        return '#'.mb_strtoupper($color, 'UTF-8');
    }

    protected function _parse(string $str)
    {
        $str = trim(mb_strtoupper($str, 'UTF-8'));

        if (preg_match('/^#([0-9A-F]{3,4})$/i', $str, $matches)) {
            $p = array_map(function ($h) {
                return $h.$h;
            }, str_split($matches[1]));
            $this->parseHex(...$p);
        } elseif (preg_match('/^#([0-9A-F]{6}|[0-9A-F]{8})$/i', $str, $matches)) {
            $p = str_split($matches[1], 2);
            $this->parseHex(...$p);
        } elseif (preg_match('/^rgba?\(((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s*,\s*((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s*,\s*((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s*(?:,\s*((?:1(\.0*)?|0?\.[0-9])|(?:100|[1-9]?[0-9])%))?\)$/i', $str, $matches)) {
            array_shift($matches);
            $this->parseRgb(...$matches);
        } elseif (preg_match('/^rgba?\(((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s+((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s+((?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:100|[1-9]?[0-9])%)\s*(?:\/\s*((?:1(\.0*)?|0?\.[0-9])|(?:100|[1-9]?[0-9])%))?\)$/i', $str, $matches)) {
            array_shift($matches);
            $this->parseRgb(...$matches);
        } elseif (preg_match('/^hsla?\s*\(\s*((?:360|3[0-5][0-9]|[12][0-9][0-9]|[1-9]?[0-9])(?:deg)?)\s*,\s*((?:100|[1-9]?[0-9])%)\s*,\s*((?:100|[1-9]?[0-9])%)\s*(?:,\s*(((?:1(\.0*)?|0?\.[0-9])|(?:100|[1-9]?[0-9])%)))?\)/i', $str, $matches)) {
            array_shift($matches);
            var_dump($matches);
            $this->parseHsl(...$matches);
        } else {
            throw new \Exception();
        }
    }

    protected function parseAlpha($a)
    {
        if (is_numeric($a)) {
            return (float) $a;
        }

        return ((int) preg_replace('/%$/', '', $a)) / 100.0;
    }

    protected function parseHex($r, $g, $b, $a = 'FF')
    {
        return $this->parseRgba(hexdec($r), hexdec($g), hexdec($b), hexdec($a) / 255);
    }

    protected function parseRgb($r, $g, $b, $a = '1')
    {
        echo "rgba({$r}, {$g}, {$b}, {$a})\n";
        $this->rgb = new RGB((int) $r, (int) $g, (int) $b); // TODO: convert percent
        $this->setAlpha($this->parseAlpha($a));
    }

    protected function parseHsl($h, $s, $l, $a = '1')
    {
        echo "hsla({$h}, {$s}, {$l}, {$a})\n";
        // TODO: convert
        $this->setAlpha($this->parseAlpha($a));
    }

    public static function parse(string $str)
    {
        $obj = new static();
        $obj->_parse($str);

        return $obj;
    }
}
