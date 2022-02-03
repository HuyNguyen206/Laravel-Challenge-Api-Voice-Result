<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoiceRequest;
use App\Models\Question;
use App\Models\Voice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VoiceController extends Controller
{
    public function voice(VoiceRequest $request){
        $question=Question::find($request->post('question_id'));
        $userId = auth()->id();

        if (!$question) {
            return response()->json([
                'status'=> Response::HTTP_CREATED,
                'message'=>'not found question ..'
            ]);
        }

        if ($question->user_id==$userId) {
            return response()->json([
                'status' => 500,
                'message' => 'The user is not allowed to vote to your question'
            ]);
        }

        //check if user voted
        $voice=Voice::where([
            ['user_id','=',$userId],
            ['question_id','=',$request->post('question_id')]
        ])->first();
        if ($voice) {
            if ($voice->value==$request->post('value')) {
                return response()->json([
                    'status' => 500,
                    'message' => 'The user is not allowed to vote more than once'
                ]);
            }else {
                $voice->update([
                    'value'=>$request->post('value')
                ]);
                return response()->json([
                    'status'=>201,
                    'message'=>'update your voice'
                ]);
            }
        }

        $question->voice()->create([
            'user_id'=>$userId,
            'value'=>$request->post('value')
        ]);

        return response()->json([
            'status'=>200,
            'message'=>'Voting completed successfully'
        ]);
    }
}
