<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Validator;

trait GeneralTrait{
    
    public function returnError($msg="Error"){
        return response()->json([
            'status' => false,
            'msg' => $msg,
        ]);
    }

    public function returnSuccess($msg ="Success"){
        return response()->json([
            'status' => true,
            'msg' => $msg,
        ]);
    }

    public function returnData($key="key", $value="value", $msg ="Data"){
        return response()->json([
            'status' => true,
            'msg' => $msg,
            $key => $value,
        ]);
    }

    public function returnValidationError($validator){
        return $this->returnError($validator->errors()->first());
    }
}