<?php

namespace Database\Seeders\Support;

/**
 * Draws simple, clean SVG product illustrations for the sample catalogue.
 * Each device type is tinted with the brand colour and labelled with the brand name.
 */
class ProductArt
{
    /** Views used to build a small photo gallery for each sample product */
    public const VIEWS = ['main', 'tinted', 'detail'];

    public static function svg(string $type, string $accent, string $brand, string $variant = 'dark', string $view = 'main'): string
    {
        $brand = htmlspecialchars($brand, ENT_XML1);
        $body = match ($type) {
            'laptop' => self::laptop($accent, $brand, $variant),
            'desktop' => self::desktop($accent, $brand),
            'monitor' => self::monitor($accent, $brand),
            'gpu' => self::gpu($accent, $brand, $variant),
            'ssd' => self::ssd($accent, $brand),
            'ram' => self::ram($accent, $brand),
            'printer' => self::printer($accent, $brand, $variant),
            'bullet' => self::bullet($accent, $brand),
            'dome' => self::dome($accent, $brand),
            'router' => self::router($accent, $brand),
            'nvr' => self::nvr($accent, $brand),
            'keyboard' => self::keyboard($accent, $brand),
            'mouse' => self::mouse($accent, $brand),
            'headset' => self::headset($accent, $brand),
            'ups' => self::ups($accent, $brand),
            default => throw new \InvalidArgumentException("Unknown art type: {$type}"),
        };

        $shadow = '<ellipse cx="300" cy="500" rx="230" ry="16" fill="#0f172a" opacity="0.08"/>';
        $scene = match ($view) {
            // Brand-tinted backdrop
            'tinted' => "<rect width=\"600\" height=\"600\" fill=\"{$accent}\" opacity=\"0.08\"/>"
                ."<circle cx=\"300\" cy=\"290\" r=\"230\" fill=\"#ffffff\" opacity=\"0.6\"/>{$shadow}{$body}",
            // Close-up on the centre of the product
            'detail' => '<rect width="600" height="600" fill="#f1f5f9"/>'
                ."<g transform=\"translate(300 300) scale(1.8) translate(-300 -300)\">{$body}</g>",
            default => "{$shadow}{$body}",
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="screen" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$accent}"/>
      <stop offset="1" stop-color="#0f172a"/>
    </linearGradient>
    <linearGradient id="metal" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#f1f5f9"/>
      <stop offset="1" stop-color="#cbd5e1"/>
    </linearGradient>
    <linearGradient id="dark" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#334155"/>
      <stop offset="1" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
{$scene}
</svg>
SVG;
    }

    private static function text(int $x, int $y, string $brand, int $size, string $fill): string
    {
        return "<text x=\"{$x}\" y=\"{$y}\" font-family=\"Arial, Helvetica, sans-serif\" font-weight=\"700\" font-size=\"{$size}\" text-anchor=\"middle\" fill=\"{$fill}\" letter-spacing=\"1\">{$brand}</text>";
    }

    private static function laptop(string $c, string $b, string $variant): string
    {
        $lid = $variant === 'silver' ? '#94a3b8' : '#1e293b';
        $base = $variant === 'silver' ? 'url(#metal)' : 'url(#dark)';
        $keys = $variant === 'silver' ? '#94a3b8' : '#475569';
        $glow = $variant === 'silver' ? '' : "<rect x=\"150\" y=\"418\" width=\"300\" height=\"3\" rx=\"1.5\" fill=\"{$c}\" opacity=\"0.9\"/>";
        $text = self::text(300, 242, $b, 34, '#ffffff');

        return <<<SVG
  <rect x="120" y="110" width="360" height="250" rx="16" fill="{$lid}"/>
  <rect x="134" y="124" width="332" height="214" rx="6" fill="url(#screen)"/>
  <circle cx="300" cy="117" r="2.5" fill="#0f172a"/>
  <path d="M80 368 H520 L552 452 Q555 466 540 466 H60 Q45 466 48 452 Z" fill="{$base}"/>
  <rect x="250" y="368" width="100" height="8" rx="4" fill="#64748b" opacity="0.6"/>
  <path d="M120 390 H480 M112 404 H488 M104 418 H496" stroke="{$keys}" stroke-width="7" stroke-linecap="round" stroke-dasharray="14 6"/>
  <rect x="250" y="432" width="100" height="22" rx="5" fill="{$keys}" opacity="0.5"/>
  {$glow}
  {$text}
SVG;
    }

    private static function desktop(string $c, string $b): string
    {
        $text = self::text(394, 452, $b, 13, '#cbd5e1');

        return <<<SVG
  <rect x="190" y="80" width="230" height="400" rx="16" fill="url(#dark)"/>
  <rect x="204" y="96" width="156" height="368" rx="8" fill="#0f172a" stroke="{$c}" stroke-opacity="0.6" stroke-width="2"/>
  <g fill="none" stroke="{$c}" stroke-width="6">
    <circle cx="282" cy="170" r="44"/><circle cx="282" cy="280" r="44"/><circle cx="282" cy="390" r="44"/>
  </g>
  <g fill="{$c}" opacity="0.35">
    <circle cx="282" cy="170" r="30"/><circle cx="282" cy="280" r="30"/><circle cx="282" cy="390" r="30"/>
  </g>
  <g fill="#1e293b"><circle cx="282" cy="170" r="10"/><circle cx="282" cy="280" r="10"/><circle cx="282" cy="390" r="10"/></g>
  <rect x="372" y="96" width="36" height="368" rx="6" fill="#1e293b"/>
  <rect x="388" y="110" width="4" height="120" rx="2" fill="{$c}"/>
  <circle cx="390" cy="252" r="7" fill="#334155" stroke="{$c}" stroke-width="2"/>
  {$text}
  <rect x="200" y="480" width="40" height="10" rx="3" fill="#334155"/><rect x="370" y="480" width="40" height="10" rx="3" fill="#334155"/>
SVG;
    }

    private static function monitor(string $c, string $b): string
    {
        $text = self::text(300, 268, $b, 34, '#ffffff');

        return <<<SVG
  <rect x="60" y="100" width="480" height="296" rx="14" fill="#1e293b"/>
  <rect x="72" y="112" width="456" height="256" rx="4" fill="url(#screen)"/>
  <rect x="72" y="374" width="456" height="14" fill="#0f172a"/>
  <path d="M276 396 H324 L334 468 H266 Z" fill="url(#dark)"/>
  <rect x="190" y="464" width="220" height="16" rx="8" fill="url(#dark)"/>
  {$text}
SVG;
    }

    private static function gpu(string $c, string $b, string $variant): string
    {
        $fans = $variant === 'triple' ? [150, 290, 430] : [190, 390];
        $fanSvg = '';
        foreach ($fans as $x) {
            $fanSvg .= "<circle cx=\"{$x}\" cy=\"286\" r=\"62\" fill=\"#0b1220\" stroke=\"#475569\" stroke-width=\"4\"/>"
                ."<circle cx=\"{$x}\" cy=\"286\" r=\"48\" fill=\"none\" stroke=\"{$c}\" stroke-width=\"3\" stroke-dasharray=\"18 10\"/>"
                ."<circle cx=\"{$x}\" cy=\"286\" r=\"18\" fill=\"#1e293b\" stroke=\"{$c}\" stroke-width=\"3\"/>";
        }
        $text = self::text(300, 236, $b, 16, $c);

        return <<<SVG
  <rect x="50" y="206" width="490" height="164" rx="18" fill="url(#dark)"/>
  <path d="M60 222 H530" stroke="{$c}" stroke-width="3" opacity="0.8"/>
  {$fanSvg}
  <rect x="530" y="194" width="16" height="196" rx="3" fill="#94a3b8"/>
  <rect x="140" y="370" width="250" height="18" rx="2" fill="#d4a017"/>
  <rect x="90" y="370" width="36" height="12" rx="2" fill="#d4a017"/>
  {$text}
SVG;
    }

    private static function ssd(string $c, string $b): string
    {
        $text = self::text(300, 312, $b, 30, '#ffffff');

        return <<<SVG
  <rect x="80" y="236" width="440" height="128" rx="10" fill="#0f172a"/>
  <rect x="140" y="250" width="320" height="100" rx="6" fill="{$c}"/>
  <rect x="140" y="326" width="320" height="24" fill="#0f172a" opacity="0.25"/>
  {$text}
  <rect x="44" y="256" width="40" height="88" rx="3" fill="#d4a017"/>
  <rect x="44" y="290" width="40" height="8" fill="#0f172a"/>
  <circle cx="514" cy="300" r="14" fill="#cbd5e1"/><circle cx="514" cy="300" r="6" fill="#64748b"/>
  <text x="300" y="342" font-family="Arial, Helvetica, sans-serif" font-size="12" text-anchor="middle" fill="#ffffff" opacity="0.85">NVMe M.2</text>
SVG;
    }

    private static function ram(string $c, string $b): string
    {
        $text = self::text(300, 318, $b, 26, '#ffffff');

        return <<<SVG
  <rect x="60" y="206" width="480" height="28" rx="8" fill="{$c}" opacity="0.9"/>
  <rect x="60" y="226" width="480" height="128" rx="6" fill="url(#dark)"/>
  <path d="M60 262 L160 240 H440 L540 262" fill="none" stroke="{$c}" stroke-width="3" opacity="0.7"/>
  {$text}
  <rect x="72" y="354" width="456" height="22" fill="#d4a017"/>
  <rect x="280" y="354" width="14" height="22" fill="#ffffff"/>
SVG;
    }

    private static function printer(string $c, string $b, string $variant): string
    {
        $tanks = $variant === 'ink'
            ? '<rect x="496" y="250" width="30" height="96" rx="6" fill="#e2e8f0"/>'
                .'<rect x="500" y="262" width="5" height="78" fill="#06b6d4"/><rect x="507" y="270" width="5" height="70" fill="#ec4899"/>'
                .'<rect x="514" y="258" width="5" height="82" fill="#facc15"/><rect x="521" y="266" width="3" height="74" fill="#0f172a"/>'
            : '';
        $text = self::text(190, 300, $b, 22, $c);

        return <<<SVG
  <path d="M200 100 H400 V200 H200 Z" fill="#ffffff" stroke="#e2e8f0" stroke-width="3"/>
  <path d="M220 130 H380 M220 150 H360 M220 170 H370" stroke="#cbd5e1" stroke-width="5" stroke-linecap="round"/>
  <rect x="100" y="180" width="400" height="200" rx="24" fill="url(#metal)"/>
  <rect x="100" y="180" width="400" height="44" rx="20" fill="#e2e8f0"/>
  <rect x="370" y="244" width="96" height="46" rx="8" fill="#0f172a"/>
  <rect x="380" y="254" width="50" height="26" rx="3" fill="{$c}" opacity="0.8"/>
  <circle cx="448" cy="267" r="7" fill="#22c55e"/>
  {$text}
  <rect x="150" y="380" width="300" height="34" rx="8" fill="#94a3b8"/>
  <rect x="190" y="340" width="220" height="56" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
  {$tanks}
SVG;
    }

    private static function bullet(string $c, string $b): string
    {
        $text = self::text(330, 274, $b, 24, $c);

        return <<<SVG
  <rect x="470" y="300" width="40" height="150" rx="10" fill="url(#metal)"/>
  <path d="M478 330 L400 300 L392 322 L470 352 Z" fill="url(#metal)"/>
  <circle cx="396" cy="312" r="16" fill="#e2e8f0" stroke="#cbd5e1" stroke-width="3"/>
  <path d="M130 196 H430 Q470 196 470 246 Q470 296 430 296 H130 Z" fill="url(#metal)" stroke="#cbd5e1" stroke-width="2"/>
  <path d="M96 182 Q96 170 110 170 H400 Q414 170 418 184 L420 196 H96 Z" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
  <rect x="96" y="196" width="50" height="100" rx="10" fill="#0f172a"/>
  <rect x="88" y="206" width="12" height="80" rx="4" fill="#1e293b"/>
  <circle cx="122" cy="246" r="20" fill="#1e3a8a"/><circle cx="116" cy="240" r="6" fill="#93c5fd" opacity="0.8"/>
  <path d="M220 196 V296" stroke="#cbd5e1" stroke-width="2"/>
  {$text}
SVG;
    }

    private static function dome(string $c, string $b): string
    {
        $text = self::text(300, 228, $b, 22, $c);

        return <<<SVG
  <rect x="140" y="190" width="320" height="56" rx="16" fill="url(#metal)" stroke="#cbd5e1" stroke-width="2"/>
  <path d="M170 246 Q170 410 300 410 Q430 410 430 246 Z" fill="#1e293b"/>
  <path d="M188 250 Q190 386 300 390 Q410 386 412 250 Z" fill="#0f172a" opacity="0.9"/>
  <circle cx="300" cy="320" r="44" fill="#111827" stroke="#334155" stroke-width="6"/>
  <circle cx="300" cy="320" r="22" fill="#1e3a8a"/><circle cx="292" cy="312" r="7" fill="#93c5fd" opacity="0.8"/>
  <path d="M215 270 Q236 260 254 266" stroke="#ffffff" stroke-width="6" stroke-linecap="round" opacity="0.25"/>
  {$text}
SVG;
    }

    private static function router(string $c, string $b): string
    {
        $text = self::text(300, 372, $b, 24, '#ffffff');

        return <<<SVG
  <g fill="url(#dark)">
    <rect x="140" y="130" width="18" height="200" rx="9" transform="rotate(-12 149 330)"/>
    <rect x="236" y="110" width="18" height="220" rx="9"/>
    <rect x="346" y="110" width="18" height="220" rx="9"/>
    <rect x="442" y="130" width="18" height="200" rx="9" transform="rotate(12 451 330)"/>
  </g>
  <path d="M90 330 H510 Q530 330 530 350 V420 Q530 440 510 440 H90 Q70 440 70 420 V350 Q70 330 90 330 Z" fill="url(#dark)"/>
  {$text}
  <g fill="{$c}"><circle cx="190" cy="404" r="5"/><circle cx="216" cy="404" r="5"/><circle cx="242" cy="404" r="5"/><circle cx="268" cy="404" r="5"/></g>
  <circle cx="420" cy="404" r="5" fill="#22c55e"/>
SVG;
    }

    private static function nvr(string $c, string $b): string
    {
        $text = self::text(170, 318, $b, 22, '#ffffff');

        return <<<SVG
  <path d="M90 250 H510 L530 230 H110 Z" fill="#475569"/>
  <rect x="70" y="250" width="460" height="120" rx="10" fill="url(#dark)"/>
  {$text}
  <g fill="#0f172a" stroke="#334155" stroke-width="2">
    <rect x="290" y="276" width="96" height="68" rx="4"/><rect x="398" y="276" width="96" height="68" rx="4"/>
  </g>
  <g fill="{$c}"><circle cx="110" cy="346" r="5"/><circle cx="130" cy="346" r="5"/></g>
  <circle cx="150" cy="346" r="5" fill="#22c55e"/>
  <rect x="100" y="370" width="30" height="10" rx="3" fill="#334155"/><rect x="470" y="370" width="30" height="10" rx="3" fill="#334155"/>
SVG;
    }

    private static function keyboard(string $c, string $b): string
    {
        $keys = '';
        for ($row = 0; $row < 4; $row++) {
            for ($col = 0; $col < 12; $col++) {
                $x = 92 + $col * 36 + ($row % 2) * 8;
                $y = 250 + $row * 34;
                $keys .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"28\" height=\"26\" rx=\"5\" fill=\"#334155\"/>";
            }
        }
        $text = self::text(140, 236, $b, 14, $c);

        return <<<SVG
  <rect x="70" y="220" width="460" height="200" rx="18" fill="url(#dark)"/>
  {$keys}
  <rect x="200" y="386" width="200" height="24" rx="5" fill="#334155"/>
  <rect x="70" y="414" width="460" height="6" rx="3" fill="{$c}" opacity="0.8"/>
  {$text}
SVG;
    }

    private static function mouse(string $c, string $b): string
    {
        $text = self::text(300, 400, $b, 18, '#cbd5e1');

        return <<<SVG
  <path d="M300 120 C390 120 420 200 420 300 C420 420 370 470 300 470 C230 470 180 420 180 300 C180 200 210 120 300 120 Z" fill="url(#dark)"/>
  <path d="M300 122 V250" stroke="#0f172a" stroke-width="4"/>
  <rect x="288" y="170" width="24" height="52" rx="12" fill="#475569" stroke="{$c}" stroke-width="3"/>
  <path d="M190 330 Q300 360 410 330" stroke="{$c}" stroke-width="4" fill="none" opacity="0.8"/>
  {$text}
SVG;
    }

    private static function headset(string $c, string $b): string
    {
        $text = self::text(300, 160, $b, 18, $c);

        return <<<SVG
  <path d="M150 330 Q150 130 300 130 Q450 130 450 330" fill="none" stroke="url(#dark)" stroke-width="30" stroke-linecap="round"/>
  <path d="M150 330 Q150 130 300 130 Q450 130 450 330" fill="none" stroke="{$c}" stroke-width="4" opacity="0.6"/>
  <rect x="104" y="290" width="96" height="150" rx="40" fill="url(#dark)"/>
  <rect x="400" y="290" width="96" height="150" rx="40" fill="url(#dark)"/>
  <rect x="124" y="310" width="56" height="110" rx="26" fill="{$c}" opacity="0.35"/>
  <rect x="420" y="310" width="56" height="110" rx="26" fill="{$c}" opacity="0.35"/>
  <path d="M150 420 Q170 480 240 470" stroke="#334155" stroke-width="8" fill="none" stroke-linecap="round"/>
  <circle cx="246" cy="470" r="10" fill="#334155"/>
  {$text}
SVG;
    }

    private static function ups(string $c, string $b): string
    {
        $text = self::text(300, 420, $b, 22, '#cbd5e1');

        return <<<SVG
  <rect x="200" y="110" width="200" height="360" rx="14" fill="url(#dark)"/>
  <rect x="226" y="150" width="148" height="74" rx="6" fill="#0f172a" stroke="#334155" stroke-width="3"/>
  <rect x="238" y="162" width="124" height="50" rx="3" fill="{$c}" opacity="0.75"/>
  <text x="300" y="196" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="700" text-anchor="middle" fill="#ffffff">230V</text>
  <circle cx="300" cy="290" r="26" fill="#1e293b" stroke="{$c}" stroke-width="4"/>
  <path d="M300 276 V292" stroke="{$c}" stroke-width="4" stroke-linecap="round"/>
  <path d="M230 350 H370 M230 366 H370 M230 382 H370" stroke="#334155" stroke-width="5" stroke-linecap="round"/>
  {$text}
SVG;
    }
}
