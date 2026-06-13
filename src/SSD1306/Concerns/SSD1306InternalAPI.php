<?php

namespace DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Concerns;

use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306OpCode;
use DeptOfScrapyardRobotics\Displays\SSD1306\SSD1306\Enums\SSD1306StartLineCommand;

trait SSD1306InternalAPI
{
    protected function setDisplay(bool $on): void
    {
        $on ? $this->displayOn() : $this->displayOff();
    }

    protected function command(SSD1306OpCode|SSD1306StartLineCommand $register_hex, array $command_data = []): void
    {
        $this->carrier->command($register_hex, $command_data);
    }

    protected function data(array $data): void
    {
        $this->carrier->data($data);
    }
}
