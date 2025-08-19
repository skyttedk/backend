<?php

namespace GFUnit\lister\reportcenter;

class ReportResult
{
    private array $data;
    private array $columns;
    private string $title;
    private string $defaultFormat;

    public function __construct(
        array $data,
        array $columns,
        string $title,
        string $defaultFormat = 'csv'
    ) {
        $this->data = $data;
        $this->columns = $columns;
        $this->title = $title;
        $this->defaultFormat = $defaultFormat;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }
}
