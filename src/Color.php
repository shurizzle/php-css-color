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
        if ($method != 'hex') {
            if (method_exists($this->getRgb(), $method)) {
                if (preg_match('/^set/', $method)) {
                    $this->hsl = null;
                }
                return call_user_func_array([$this->getRgb(), $method], $args);
            } elseif (method_exists($this->getHsl(), $method)) {
                if (preg_match('/^set/', $method)) {
                    $this->rgb = null;
                }
                return call_user_func_array([$this->getHsl(), $method], $args);
            }
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
        if ($value < 0 || $value > 1) {
            throw new InvalidArgumentException('Invalid number, value must be in the range of 0 and 1');
        }

        return $this->alpha = $value;
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

    public function getName()
    {
        return Name::fromColor($this);
    }

    public function toHex(bool $compress = true, bool $alpha = true)
    {
        if ($alpha) {
            $color = sprintf("%02x%02x%02x%02x", $this->red, $this->green, $this->blue, round($this->alpha * 255));

            $color = preg_replace('/ff$/i', '', $color);

            if ($compress) {
                $color = preg_replace('/^([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3(?:([0-9a-f])\4)?$/i', '$1$2$3$4', $color);
            }

            return '#'.mb_strtoupper($color, 'UTF-8');
        } else {
            return $this->rgb->toHex($compress);
        }
    }

    protected function _parse(string $str)
    {
        $str = trim(mb_strtolower($str, 'UTF-8'));

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
            $this->parseHsl(...$matches);
        } else {
            throw new \Exception('Couldn\'t parse color `'.$str.'\'');
        }
    }

    protected function parseAlpha($a)
    {
        if (is_numeric($a)) {
            return (float) $a;
        }

        return $this->parsePercent($a);
    }

    protected function parseHexColor($c)
    {
        if (is_numeric($c)) {
            return (int) $c;
        }

        return round($this->parsePercent($c) * 255);
    }

    protected function parseHue($h)
    {
        $h = preg_replace('/deg$/i', '', $h);
        if (is_numeric($h)) {
            return $h / 360.0;
        }

        return $this->parsePercent($h);
    }

    protected function parsePercent($p)
    {
        return ((float) preg_replace('/%$/', '', $p)) / 100.0;
    }

    protected function parseHex($r, $g, $b, $a = 'FF')
    {
        return $this->parseRgb(hexdec($r), hexdec($g), hexdec($b), hexdec($a) / 255);
    }

    protected function parseRgb($r, $g, $b, $a = '1')
    {
        $this->rgb = new RGB($this->parseHexColor($r), $this->parseHexColor($g), $this->parseHexColor($b));
        $this->setAlpha($this->parseAlpha($a));
    }

    protected function parseHsl($h, $s, $l, $a = '1')
    {
        $this->hsl = new HSL($this->parseHue($h), $this->parsePercent($s), $this->parsePercent($l));
        $this->setAlpha($this->parseAlpha($a));
    }

    public static function parse(string $str)
    {
        $str = Name::toColor($str) ?? $str;
        $obj = new static();
        $obj->_parse($str);

        return $obj;
    }

    public function getMin(bool $lts = false)
    {
        $opts = [];

        if ($lts) {
            $color = Name::fromColor($this, $lts);
            if (isset($color)) {
                $opts[] = $color;
            }
            $opts[] = $this->toHex(true, false);
            $opts[] = "rgb({$this->r},{$this->g},{$this->b})";
        } else {
            $h = round($this->h * 360);
            $s = round($this->s * 100);
            $l = round($this->l * 100);
            if ($this->a < 1) {
                if ($this->r == 0 && $this->g == 0 && $this->b == 0 && $this->a == 0) {
                    $opts[] = 'transparent';
                }
                $a = sprintf('%.2f', $this->a);
                $opts[] = "rgba({$this->r},{$this->g},{$this->b},{$a})";
                $opts[] = "hsla({$h},{$s}%,{$l}%,{$a})";
            } else {
                $color = Name::fromColor($this, $lts);
                if (isset($color)) {
                    $opts[] = $color;
                }
                $opts[] = $this->toHex(true, false);
                $opts[] = "rgb({$this->r},{$this->g},{$this->b})";
                $opts[] = "hsl({$h},{$s}%,{$l}%)";
            }
        }

        usort($opts, function ($a, $b) {
            return mb_strlen($a) - mb_strlen($b);
        });

        return $opts[0];
    }

    public function __toString()
    {
        $this->getMin();
    }
}
