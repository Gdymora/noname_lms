<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Inertia;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return Inertia::render('Public/Page', [
            'page' => $page
        ]);
    }
}