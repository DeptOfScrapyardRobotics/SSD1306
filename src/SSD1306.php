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

    /**
     * Write bytes packed per formatSpec() into the rectangle at (x, y). The
     * bytes are the same in every addressing mode; the panel positions and
     * orders them for the mode it is in. y and height round to 8-row pages.
     */
    public function transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null): void
    {
        $width = $frame_width ?? $this->width();
        $height = $frame_height ?? $this->height();
        $bytes = array_values($raw_data);

        match ($this->getAddressingMode()) {
            SSD1306AddressingMode::HORIZONTAL_ADDRESSING_MODE => $this->transmitWindow($origin_x, $origin_y, $width, $height, $bytes),
            SSD1306AddressingMode::VERTICAL_ADDRESSING_MODE => $this->transmitWindow($origin_x, $origin_y, $width, $height, $this->columnMajor($bytes, $width)),
            SSD1306AddressingMode::PAGE_ADDRESSING_MODE => $this->transmitPages($origin_x, $origin_y, $width, $height, $bytes),
            SSD1306AddressingMode::INVALID => throw SSD1306Exception::invalidAddressingMode(SSD1306AddressingMode::INVALID->name),
        };
    }

    /** Horizontal and vertical modes: one column/page window, one stream. */
    protected function transmitWindow(int $x, int $y, int $width, int $height, array $bytes): void
    {
        $this->setAddressWindow($x, $y, $width, $height);
        $this->transport()->data($bytes);
    }

    /** Page mode: the chip ignores the window, so place and send each page on its own. */
    protected function transmitPages(int $x, int $y, int $width, int $height, array $bytes): void
    {
        $first_page = $y >> 3;
        $last_page = ($y + $height - 1) >> 3;

        foreach (array_chunk($bytes, max(1, $width)) as $offset => $row) {
            if ($first_page + $offset > $last_page) {
                break;
            }

            $this->setPagePosition($x, $first_page + $offset);
            $this->transport()->data($row);
        }
    }

    /**
     * Rows of pages (page-major) → columns of pages (column-major), the order
     * the RAM pointer walks in vertical mode.
     *
     * @param  list<int>  $bytes
     * @return list<int>
     */
    protected function columnMajor(array $bytes, int $width): array
    {
        $rows = array_chunk($bytes, max(1, $width));
        $out = [];

        for ($column = 0; $column < $width; $column++) {
            foreach ($rows as $row) {
                if (isset($row[$column])) {
                    $out[] = $row[$column];
                }
            }
        }

        return $out;
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

    /** How transmit() wants its bytes packed. The same in every addressing mode. */
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
        return new FormatSpec(
            PixelFormat::MONO_VERTICAL_PAGE,
            BitDepth::B1,
            ScanDirection::TOP_TO_BOTTOM,
            BitOrder::LSB_FIRST,
            page_axis: PageAxis::VERTICAL,
        );
    }
}