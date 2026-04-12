<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class PlanningController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/planning');
            $planning = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $planning = [];
        }

        return view('admin.planning.index', [
            'planning' => $planning,
            'page_title' => 'Planning Global',
            'page_subtitle' => 'Vue d\'ensemble des événements et formations'
        ]);
    }
}