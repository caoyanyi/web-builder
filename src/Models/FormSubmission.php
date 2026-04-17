<?php

namespace App\Models;

class FormSubmission
{
    public $id;
    public $project_id;
    public $project_name;
    public $project_type;
    public $page_name;
    public $page_title;
    public $source;
    public $form_data = [];
    public $field_meta = [];
    public $meta = [];
    public $submitted_at;
    public $created_at;

    private const STORAGE_DIR = __DIR__ . '/../../database';
    private const STORAGE_FILE = __DIR__ . '/../../database/form_submissions.json';

    public function save()
    {
        $records = self::readStorage();
        $now = date('Y-m-d H:i:s');

        if (!$this->id) {
            $this->id = self::getNextId($records);
            $this->created_at = $now;
        }

        if (!$this->created_at) {
            $this->created_at = $now;
        }

        if (!$this->submitted_at) {
            $this->submitted_at = $now;
        }

        $records[$this->id] = $this->toArray();
        self::writeStorage($records);

        return $this->id;
    }

    public function delete(): bool
    {
        $records = self::readStorage();

        if (!isset($records[$this->id])) {
            return false;
        }

        unset($records[$this->id]);
        self::writeStorage($records);

        return true;
    }

    public static function find($id): ?self
    {
        $records = self::readStorage();

        if (!isset($records[$id])) {
            return null;
        }

        return self::fromArray($records[$id]);
    }

    public static function all(array $filters = []): array
    {
        $records = array_values(self::readStorage());
        $records = array_filter($records, static function (array $record) use ($filters) {
            if (isset($filters['project_id']) && $filters['project_id'] !== '' && (string) ($record['project_id'] ?? '') !== (string) $filters['project_id']) {
                return false;
            }

            if (isset($filters['project_name']) && $filters['project_name'] !== '' && (string) ($record['project_name'] ?? '') !== (string) $filters['project_name']) {
                return false;
            }

            if (isset($filters['page_name']) && $filters['page_name'] !== '' && (string) ($record['page_name'] ?? '') !== (string) $filters['page_name']) {
                return false;
            }

            return true;
        });

        usort($records, static function (array $a, array $b) {
            return strcmp((string) ($b['submitted_at'] ?? ''), (string) ($a['submitted_at'] ?? ''));
        });

        return array_map(
            static function (array $record) {
                return self::fromArray($record);
            },
            $records
        );
    }

    public static function clear(array $filters = []): int
    {
        $records = self::readStorage();
        $removedCount = 0;

        foreach ($records as $id => $record) {
            if (!self::matchesFilters($record, $filters)) {
                continue;
            }

            unset($records[$id]);
            $removedCount += 1;
        }

        self::writeStorage($records);

        return $removedCount;
    }

    public function toArray(): array
    {
        return [
            'id' => (int) $this->id,
            'project_id' => $this->project_id !== null ? (int) $this->project_id : null,
            'project_name' => $this->project_name ?: '未命名项目',
            'project_type' => $this->project_type ?: 'h5',
            'page_name' => $this->page_name ?: 'index',
            'page_title' => $this->page_title ?: '首页',
            'source' => $this->source ?: 'unknown',
            'form_data' => is_array($this->form_data) ? $this->form_data : [],
            'field_meta' => is_array($this->field_meta) ? $this->field_meta : [],
            'meta' => is_array($this->meta) ? $this->meta : [],
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
        ];
    }

    public static function fromArray(array $data): self
    {
        $record = new self();
        $record->id = isset($data['id']) ? (int) $data['id'] : null;
        $record->project_id = isset($data['project_id']) && $data['project_id'] !== '' ? (int) $data['project_id'] : null;
        $record->project_name = $data['project_name'] ?? '未命名项目';
        $record->project_type = $data['project_type'] ?? 'h5';
        $record->page_name = $data['page_name'] ?? 'index';
        $record->page_title = $data['page_title'] ?? '首页';
        $record->source = $data['source'] ?? 'unknown';
        $record->form_data = isset($data['form_data']) && is_array($data['form_data']) ? $data['form_data'] : [];
        $record->field_meta = isset($data['field_meta']) && is_array($data['field_meta']) ? $data['field_meta'] : [];
        $record->meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
        $record->submitted_at = $data['submitted_at'] ?? null;
        $record->created_at = $data['created_at'] ?? null;

        return $record;
    }

    private static function ensureStorageReady(): void
    {
        if (!is_dir(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0777, true);
        }

        if (!file_exists(self::STORAGE_FILE)) {
            file_put_contents(self::STORAGE_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private static function readStorage(): array
    {
        self::ensureStorageReady();

        $content = file_get_contents(self::STORAGE_FILE);
        $records = json_decode($content, true);

        return is_array($records) ? $records : [];
    }

    private static function writeStorage(array $records): void
    {
        self::ensureStorageReady();

        file_put_contents(
            self::STORAGE_FILE,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private static function getNextId(array $records): int
    {
        if (empty($records)) {
            return 1;
        }

        $ids = array_map('intval', array_keys($records));
        return max($ids) + 1;
    }

    private static function matchesFilters(array $record, array $filters): bool
    {
        if (isset($filters['project_id']) && $filters['project_id'] !== '' && (string) ($record['project_id'] ?? '') !== (string) $filters['project_id']) {
            return false;
        }

        if (isset($filters['project_name']) && $filters['project_name'] !== '' && (string) ($record['project_name'] ?? '') !== (string) $filters['project_name']) {
            return false;
        }

        if (isset($filters['page_name']) && $filters['page_name'] !== '' && (string) ($record['page_name'] ?? '') !== (string) $filters['page_name']) {
            return false;
        }

        return true;
    }
}
