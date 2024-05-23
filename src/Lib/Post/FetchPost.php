<?php

namespace App\Lib\Post;

use App\Lib\Database\Connection;
use Dibi\Exception;

class FetchPost
{

    protected PostEnum $table = PostEnum::Table;
    protected PostEnum $pageType = PostEnum::PageType;
    protected PostEnum $postType = PostEnum::PostType;
    protected PostEnum $status = PostEnum::Status;
    protected Connection $db;

    public function __construct()
    {
        $this->db = new Connection();
    }

    /**
     * @throws Exception
     */
    public function getAllPosts(): array
    {
        $pages = $this->db->get()
            ->fetchAll(
                "SELECT * FROM {$this->table->value} WHERE post_type = ? AND post_status = ?",
                $this->pageType->value,
                $this->status->value
            );

        $posts = $this->db->get()
            ->fetchAll(
                "SELECT * FROM {$this->table->value} WHERE post_type = ? AND post_status = ?",
                $this->postType->value,
                $this->status->value
            );

        return array_merge($pages, $posts);
    }

}