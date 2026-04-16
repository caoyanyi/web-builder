<?php

namespace App\Models;

class Project
{
    public $id;
    public $name;
    public $type;
    public $config = [];
    public $created_at;
    public $updated_at;

    private const STORAGE_DIR = __DIR__ . '/../../database';
    private const STORAGE_FILE = __DIR__ . '/../../database/projects.json';

    public function save()
    {
        $projects = self::readStorage();
        $now = date('Y-m-d H:i:s');

        if (!$this->id) {
            $this->id = self::getNextId($projects);
            $this->created_at = $now;
        }

        if (!$this->created_at) {
            $this->created_at = $now;
        }

        $this->updated_at = $now;
        $projects[$this->id] = $this->toArray();

        self::writeStorage($projects);

        return $this->id;
    }

    public function delete()
    {
        $projects = self::readStorage();

        if (!isset($projects[$this->id])) {
            return false;
        }

        unset($projects[$this->id]);
        self::writeStorage($projects);

        return true;
    }

    public static function find($id)
    {
        $projects = self::readStorage();

        if (!isset($projects[$id])) {
            return null;
        }

        return self::fromArray($projects[$id]);
    }

    public static function all()
    {
        $projects = self::readStorage();
        krsort($projects);

        return array_map(
            function ($project) {
                return self::fromArray($project);
            },
            array_values($projects)
        );
    }

    public function toArray()
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'config' => is_array($this->config) ? $this->config : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function fromArray(array $data)
    {
        $project = new self();
        $project->id = isset($data['id']) ? (int) $data['id'] : null;
        $project->name = $data['name'] ?? '未命名项目';
        $project->type = $data['type'] ?? 'h5';
        $project->config = isset($data['config']) && is_array($data['config']) ? $data['config'] : [];
        $project->created_at = $data['created_at'] ?? null;
        $project->updated_at = $data['updated_at'] ?? null;

        return $project;
    }

    private static function ensureStorageReady()
    {
        if (!is_dir(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0777, true);
        }

        if (!file_exists(self::STORAGE_FILE)) {
            file_put_contents(self::STORAGE_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private static function readStorage()
    {
        self::ensureStorageReady();

        $content = file_get_contents(self::STORAGE_FILE);
        $projects = json_decode($content, true);

        return is_array($projects) ? $projects : [];
    }

    private static function writeStorage(array $projects)
    {
        self::ensureStorageReady();

        file_put_contents(
            self::STORAGE_FILE,
            json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private static function getNextId(array $projects)
    {
        if (empty($projects)) {
            return 1;
        }

        $ids = array_map('intval', array_keys($projects));

        return max($ids) + 1;
    }
}
