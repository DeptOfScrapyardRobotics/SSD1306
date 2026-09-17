<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306;

use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\BitOrder;
use Surface\Contracts\Framebuffers\PageAxis;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\PixelFormat;
use GeneralPurposeIO\IntegratedCircuits\Bootable;
use Surface\Contracts\Framebuffers\ScanDirection;
use Surface\Contracts\Framebuffers\FormatSpecification;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use DeptOfScrapyardRobotics\Displays\SSD1306\Concerns\SSD1306Bootstrap;
use DeptOfScrapyardRobotics\Displays\SSD1306\Enums\SSD1306AddressingMode;
use DeptOfScrapyardRobotics\Displays\SSD1306\Transports\SSD1306DataTransport;

class SSD1306 extends Bootable implements DisplayPanel, FormatSpecification
{
    use SSD1306Bootstrap;

    protected FormatSpec $format_spec;

    public function __construct(
        protected readonly SSD1306DataTransport $transport,
        protected SSD1306Configuration $props,
        bool $boot_now = false,
    ) {
        $this->format_spec = $this->generateFormatSpec();
        
        parent::__construct($boot_now);
    }

    public function width(): int
    {
        return $this->props->get('width');
    }

    public function height(): int
    {
        return $this->props->get('height');
    }

    public function transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null): void
    {
        $width = $frame_width ?? $this->width();
        $height = $frame_height ?? $this->height();

        $this->setAddressWindow($origin_x, $origin_y, $width, $height);
        $this->transport()->data($raw_data);
    }

    public function transport(): SSD1306DataTransport
    {
        return $this->transport;
    }

    /** Release DC and RST on SPI; the bus connection belongs to its driver and stays open. */
    public function close(): void
    {
        $this->transport->close();
    }

    public function config(): SSD1306Configuration
    {
        return $this->props;
    }

    /** How the panel wants its bytes packed, for the addressing mode it is in. Set at boot. */
    public function formatSpec(): FormatSpec
    {
        return $this->format_spec;
    }

    public function setFormatSpec(FormatSpec $format_spec): void
    {
        $this->format_spec = $format_spec;
    }

    public function generateFormatSpec(): FormatSpec
    {
        /** @var SSD1306AddressingMode $addressing_mode */
        $addressing_mode = $this->props->get('addressing_mode');
        return match ($addressing_mode) {
            SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE,
            SSD1306AddressingMode::PAGE_ADDRESSING_MODE => new FormatSpec(
                PixelFormat::MONO_VERTICAL_PAGE,
                BitDepth::B1,
                ScanDirection::TOP_TO_BOTTOM,
                BitOrder::LSB_FIRST,
                page_axis: PageAxis::VERTICAL,
            ),
            default => throw SSD1306Exception::unsupportedAddressingModeForFormatSpec($addressing_mode->name),
        };
    }
}