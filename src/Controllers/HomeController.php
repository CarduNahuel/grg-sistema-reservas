<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $restaurantModel = new \App\Models\Restaurant();
        $featuredRestaurants = $restaurantModel->getActive();
        
        // Limit to 6 for homepage
        $featuredRestaurants = array_slice($featuredRestaurants, 0, 6);

        $this->view('home.index', [
            'title' => 'Inicio - GRG',
            'restaurants' => $featuredRestaurants
        ]);
    }

    public function about()
    {
        $this->view('home.about', [
            'title' => 'Acerca de GRG'
        ]);
    }
}
