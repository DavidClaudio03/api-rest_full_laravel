<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;

class PruebasController extends Controller
{
    public function index()
    {
        $titulo = 'Personalidades';
        $personalidades = ['Sincero', 'Amable', 'Curioso'];
        return view('pruebas.index', ['titulo' => $titulo, 'personalidades' => $personalidades]);
    }

    public function testOrm()
    {
        $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>" . $post->titulo . "</h1>";
            echo "<sapn>{$post->user->name}</sapn>";
            echo "<br>";
            echo "<h2><sapn>{$post->category->nombre}</sapn></h2>";
            echo "<p>" . $post->contenido . "</p>";
        }
        die();
    }
}
