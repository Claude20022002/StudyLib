<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\YouTubeService;
use Illuminate\Http\JsonResponse;

class YoutubeRecommendationController extends Controller
{
    public function __construct(
        private readonly YouTubeService $youtube,
    ) {}

    public function index(Module $module): JsonResponse
    {
        return response()->json($this->youtube->forModule($module));
    }
}
