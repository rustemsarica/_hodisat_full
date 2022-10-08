<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

use App\Models\Follow;

class FollowCollection extends ResourceCollection
{
    public function toArray($request)
    {
        if(auth()->user()){
            return [
            'data' => $this->collection->map(function($data) {

                if($data->logo!=null || $data->logo != ''){
                    $seller_logo=uploaded_asset($data->logo);
                    if($seller_logo==null || $seller_logo==''){
                        $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                    }
                }else{
                   $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                }
                
                $is_in_following=false;
                $follows = Follow::where(['user_id'=>auth()->user()->id,'followed_user_id'=>$data->user_id])->count();
                if($follows>0){
                    $is_in_following=true;
                }
                 
                return [
                    "id" =>  $data->id,
                    "user_id" =>  $data->user_id,
                    "name" =>  $data->name,
                    "logo" => $seller_logo,
                    "slug" => $data->slug,
                    "is_in_following" => $is_in_following               
                ];
            })
        ];
        }else{
            return [
                'data' => $this->collection->map(function($data) {
    
                    if($data->logo!=null || $data->logo != ''){
                        $seller_logo=uploaded_asset($data->logo);
                        if($seller_logo==null || $seller_logo==''){
                            $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                        }
                    }else{
                       $seller_logo='https://hodisat.com/public/assets/img/avatar-place.png'; 
                    }
                    
                    $is_in_following=false;
                                         
                    return [
                        "id" =>  $data->id,
                        "user_id" =>  $data->user_id,
                        "name" =>  $data->name,
                        "logo" => $seller_logo,
                        "slug" => $data->slug,
                        "is_in_following" => $is_in_following               
                    ];
                })
            ];
        }
        
        
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
