<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //cargamos el middleware para no usar en el idex y show
    //Esto sirve para excluir la funciones de los controllers que hagamos
    public function __construct()
    {
        $this->middleware('apiAuth', ['except'=>['index','show']]);
    }
    public function index(){
        $categories = Category::all();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    //mostar los datos por id
    public function show($id){
        $category = Category::find($id);
        if(is_object($category)){
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ]);
        }else{
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoría no existe',
            ]);
        }
    }

    //crear una nueva categoría
    public function store(Request $request){
        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //validar información
        if(!empty($params_array)){
            $validate= Validator::make($params_array, [
                'nombre'=> 'required'
            ]);

            //guardar la categoria
            if($validate->fails()){
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validate->errors()
                ], 400);
            }else{
                $category =new  Category();
                $category->nombre = $params_array['nombre'];
                $category->save();
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ], 200);
            }
        }else{
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria',
            ], 400);
        }
    }

    //actualizar una categoría
    public function update(Request $request, $id){
        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //validar información
        if(!empty($params_array)){
            $validate= Validator::make($params_array, [
                'nombre'=> 'required'
            ]);

            //quitar lo que nos quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //actualizar la categoria
            if($validate->fails()){
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validate->errors()
                ], 400);
            }else{
                $category = Category::find($id);
                if(is_object($category)){
                    $category->nombre = $params_array['nombre'];
                    $category->save();
                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'category' => $category
                    ], 200);
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'La categoría no existe',
                    ], 404);
                }
            }
        }else{
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria',
            ], 400);
        }
    }
}
