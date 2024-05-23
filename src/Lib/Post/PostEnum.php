<?php

namespace App\Lib\Post;

enum PostEnum: string
{

    case Table = "wp_posts";
    case PostType = "post";
    case PageType = "page";
    case Status = "publish";

}
