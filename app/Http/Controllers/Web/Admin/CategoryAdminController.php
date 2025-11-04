<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryAdminController extends Controller
{
    use Utils;
    
    protected $categoryService;
    
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    
    public function index(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        // Récupérer toutes les catégories
        $viewData['categories'] = $this->categoryService->getAllCategories();
        
        // Obtenir les statistiques d'utilisation pour chaque catégorie
        foreach ($viewData['categories'] as &$category) {
            $category->task_count = DB::table('category_task')
                ->where('category_id', $category->id)
                ->count();
                
            $category->user_count = DB::table('category_user')
                ->where('category_id', $category->id)
                ->count();
        }
        
        $this->setViewData($request, $viewData);
        
        return view('admin.categories', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Gestion des catégories', 
            'pagecardtilte' => 'Liste des catégories',
        ]);
    }
    
    public function create(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        $viewData['category'] = new Category();
        
        $this->setViewData($request, $viewData);
        
        return view('admin.category_form', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Nouvelle catégorie', 
            'pagecardtilte' => '',
        ]);
    }
    
    public function store(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        $request->validate([
            'name' => 'required|max:255|unique:categories',
            'description' => 'nullable',
        ]);
        
        $categoryId = $this->getId();
        
        Category::create([
            'id' => $categoryId,
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('admin.categories')->with([
            'message' => 'Catégorie créée avec succès',
            'type' => 'success'
        ]);
    }
    
    public function edit(Request $request, $id)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        $category = $this->categoryService->getCategoryById($id);
        
        if (!$category) {
            return redirect()->route('admin.categories')->with([
                'message' => 'Catégorie non trouvée',
                'type' => 'danger'
            ]);
        }
        
        $viewData['category'] = $category;
        
        $this->setViewData($request, $viewData);
        
        return view('admin.category_form', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Modifier la catégorie', 
            'pagecardtilte' => '',
        ]);
    }
    
    // Les autres méthodes du contrôleur (update, destroy, etc.)
    // ...
    
    private function isConnected()
    {
        return (\Auth::viaRemember() || \Auth::check());
    }

    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function setViewData(Request &$request, &$viewData)
    {
        $viewData['uri'] = \Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }
}