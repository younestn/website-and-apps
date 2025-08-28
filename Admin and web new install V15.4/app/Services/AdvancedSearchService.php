<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Facades\Log;
use App\Contracts\AdminServiceInterface;

class AdvancedSearchService
{
    public function getSortRecentSearchByType(object|array $searchData): array
    {
        $fallbackResults = collect();

        foreach ($searchData as $search) {
            $response = is_string($search->response) ? json_decode($search->response, true) : $search->response;

            if (is_array($response) && isset($response['priority'])) {
                $fallbackResults->push($response);
            }
        }

        $fallbackResults = $fallbackResults->sortBy('priority')->values();
       return $fallbackResults->groupBy('type')->toArray();
    
    }
}