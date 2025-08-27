<?php

namespace App\Models;

class Project
{
    public $id;
    public $name;
    public $type;
    public $config;
    public $created_at;
    public $updated_at;

    private static $projects = [];
    private static $nextId = 1;

    public function save()
    {
        if (!$this->id) {
            $this->id = self::$nextId++;
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');
        
        self::$projects[$this->id] = clone $this;
        return $this->id;
    }

    public function delete()
    {
        if (isset(self::$projects[$this->id])) {
            unset(self::$projects[$this->id]);
            return true;
        }
        return false;
    }

    public static function find($id)
    {
        return isset(self::$projects[$id]) ? self::$projects[$id] : null;
    }

    public static function all()
    {
        return array_values(self::$projects);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'config' => json_decode($this->config, true),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
